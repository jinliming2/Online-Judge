<?php
/**
 * Copyright 2017 Liming Jin
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 * Created by Liming
 * Date: 2016/12/6
 * Time: 14:56
 */
use Database\Question;
use Database\Result;
use Database\User;
use MongoDB\BSON\ObjectID;
use Workerman\Connection\TcpConnection;

/**
 * 用户登录
 *
 * @param TcpConnection $connection
 * @param stdClass      $data
 */
function mLogin(TcpConnection $connection, stdClass $data) {
    global $MESSAGE_TYPE, $MESSAGE_CODE;
    $user = null;
    if(!isset($data->token)) {
        return;
    }
    $user = User::getInstance()->login($data->token);
    if($user === false || (isset($user->ban) && $user->ban)) {
        $connection->send(json_encode([
            'code'    => $MESSAGE_CODE->LogonTimeout,
            'type'    => $MESSAGE_TYPE->Login,
            'message' => 'Logon Timeout',
            '_t'      => $data->_t
        ]));
    } else {
        $connection->user_info = $user;
        $connection->send(json_encode([
            'code'    => $MESSAGE_CODE->Success,
            'type'    => $MESSAGE_TYPE->Login,
            'message' => 'Success',
            'token'   => $user->token,
            '_t'      => $data->_t
        ]));
    }
}

/**
 * 代码评判
 *
 * @param TcpConnection $connection
 * @param stdClass      $data
 *
 * @throws Exception
 */
function mJudge(TcpConnection $connection, stdClass $data) {
    global $MESSAGE_TYPE, $MESSAGE_CODE, $LANGUAGE_TYPE;
    //请求频率
    $time = time();
    if(isset($connection->lastJudgeTime)) {
        $interval = $time - $connection->lastJudgeTime;
        $connection->lastJudgeTime = $time;
        if($interval <= CONFIG['websocket']['interval']) {
            $connection->send(json_encode([
                'code'    => $MESSAGE_CODE->TooFrequent,
                'type'    => $MESSAGE_TYPE->Judge,
                'message' => 'Request Too Frequent',
                '_t'      => $data->_t
            ]));
            return;
        }
    } else {
        $connection->lastJudgeTime = $time;
    }
    //登录检查
    if(!isset($connection->user_info)) {
        $connection->send(json_encode([
            'code'    => $MESSAGE_CODE->AccessDeny,
            'type'    => $MESSAGE_TYPE->Judge,
            'message' => 'Need Login First',
            '_t'      => $data->_t
        ]));
        return;
    }
    //请求检查
    if(!isset($data->qid) || !isset($data->language) || !isset($data->source_code)) {
        return;
    }
    if(!is_integer($data->language) || $data->language < 0 || $data->language >= count($LANGUAGE_TYPE)) {
        return;
    }
    //取问题信息
    $question = Question::getInstance()->getOne(new ObjectID($data->qid));
    if($question === null) {
        return;
    }
    //创建评测记录
    $result = Result::getInstance()->add($connection->user_info->_id, $question->_id, $data->language, $data->source_code);
    if($result === false) {
        throw new Exception('Cannot create result record.');
    }
    $ex = str_replace(['\\', '/', ':', '*', '?', '"', '<', '>', '|'], '', $connection->worker->id.'-'.$connection->getRemoteIp().'-');
    //评测信息
    $connection->worker->process_pool[] = [
        'rid'        => $result,
        'uid'        => $connection->user_info->_id,
        'start_time' => timestamp(),
        'judge_info' => [
            'qid'       => (string)$question->_id,
            'time'      => $question->time,
            'memory'    => $question->memory,
            'language'  => $data->language,
            'temp_path' => uniqid($ex, true),
            'code'      => $data->source_code
        ]
    ];
    $connection->send(json_encode([
        'code'    => $MESSAGE_CODE->Success,
        'type'    => $MESSAGE_TYPE->Judge,
        'message' => 'Success',
        'id'      => (string)$result,
        '_t'      => $data->_t
    ]));
}

/**
 * 测试用例服务
 *
 * @param TcpConnection $connection
 * @param stdClass      $data
 */
function mTestCase(TcpConnection $connection, stdClass $data) {
    global $MESSAGE_TYPE, $MESSAGE_CODE;
    //登录及管理员身份检查
    if(!isset($connection->user_info) || !$connection->user_info->su) {
        $connection->send(json_encode([
            'code'    => $MESSAGE_CODE->AccessDeny,
            'type'    => $MESSAGE_TYPE->TestCase,
            'message' => 'Permission deny!',
            '_t'      => $data->_t
        ]));
        return;
    }
    //请求检查
    if(!isset($data->qid) || !isset($data->code)) {
        return;
    }
    if($data->code == $MESSAGE_CODE->FetchTestCase) {
        //获取测试用例列表
        $ret = ['i' => 0, 'o' => 0, 'data' => []];
        if(file_exists(CONFIG['websocket']['in'].$data->qid) && file_exists(CONFIG['websocket']['out'].$data->qid)) {
            $startI = empty($data->i) ? 0 : $data->i;
            $startO = empty($data->o) ? 0 : $data->o;
            $file_in = fopen(CONFIG['websocket']['in'].$data->qid, 'r');
            $file_out = fopen(CONFIG['websocket']['out'].$data->qid, 'r');
            fseek($file_in, $startI);
            fseek($file_out, $startO);
            for($i = 0; $i < 100; ++$i) {
                $ret_tmp = ['i' => '', 'o' => '', 'il' => -1, 'ol' => -1];
                $ret_tmp['il'] = ftell($file_in);
                while(($line = fgets($file_in)) && strlen($line) > 0) {
                    $ret_tmp['i'] .= $line . "\n";
                }
                $ret_tmp['ol'] = ftell($file_out);
                while(($line = fgets($file_out)) && strlen($line) > 0) {
                    $ret_tmp['o'] .= $line . "\n";
                }
                $ret['data'][] = $ret_tmp;
            }
            $ret['i'] = ftell($file_in);
            $ret['o'] = ftell($file_out);
            fclose($file_in);
            fclose($file_out);
        }
        $connection->send(json_encode([
            'code'    => $MESSAGE_CODE->FetchTestCase,
            'type'    => $MESSAGE_TYPE->TestCase,
            'message' => $ret,
            '_t'      => $data->_t
        ]));
    } elseif($data->code == $MESSAGE_CODE->AddTestCase) {
        //根据代码重新生成测试用例
        //TODO: 根据代码生成测试用例
    } elseif($data->code == $MESSAGE_CODE->InsertTestCase) {
        //添加测试用例
        //TODO: 添加测试用例
    } else return;
}
