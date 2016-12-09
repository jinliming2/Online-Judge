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
    if($user === null) {
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
            'code'    => $MESSAGE_CODE::AccessDeny,
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
        'judge_info' => [
            'qid'       => $question->_id,
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
