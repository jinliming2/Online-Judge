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
 * Date: 2016/11/3
 * Time: 19:24
 */
use Workerman\Worker;
use Workerman\Lib\Timer;
use Judge\Judge;
use Database\User;
use Database\Question;
use Database\Result;

use Constant\MESSAGE_CODE;
use Constant\MESSAGE_TYPE;

use Exception\QuestionDoesNotExistException;
use Exception\TestCaseCountException;
use Exception\UnknownLanguageException;

require_once 'Workerman/Autoloader.php';
require_once 'config.php';
require_once 'common.php';

//心跳包间隔（秒）
define('HEARTBEAT_TIME', 300);

$worker = new Worker('websocket://[::]:8080');

//同时服务进程数
$worker->count = CONFIG['process count'];

/**
 * 启动监听
 *
 * @param Worker $worker
 */
$worker->onWorkerStart = function($worker) {
    //进程们
    $worker->process_pool = [];
    //心跳检测
    Timer::add(60, function() use ($worker) {
        $time_now = time();
        foreach($worker->connections as $connection) {
            if(empty($connection->lastMessageTime)) {
                $connection->lastMessageTime = $time_now;
                continue;
            }
            if($time_now - $connection->lastMessageTime > HEARTBEAT_TIME) {
                $connection->close();
            }
        }
    });
    //任务处理
    Timer::add(5, function() use ($worker) {
        $index = 0;
        $flag = false;
        foreach($worker->process_pool as $i => $process) {
            if(!$process->started) {
                if($index < CONFIG['sub process count']) {
                    ++$index;
                    $process->run();
                }
            } elseif($process->finished == false) {
                ++$index;
            } else {
                unset($worker->process_pool[$i]);
                $flag = true;
            }
        }
        if($flag) {
            $arr = [];
            foreach($worker->process_pool as $process) {
                $arr[] = $process;
            }
            $worker->process_pool = $arr;
        }
    });
    logs('WebSocket server now listening on port 8080.');
};

/**
 * 重新加载所有子进程
 *
 * @param $worker
 */
$worker->onWorkerReload = function($worker) {
    logs('WebSocket server now reloading.');
};

/**
 * 停止服务
 *
 * @param $worker
 */
$worker->onWorkerStop = function($worker) {
    logs('WebSocket server now stopped.', 1);
};

/**
 * 建立连接
 *
 * @param $connection
 */
$worker->onConnect = function($connection) {
    /**
     * @param $connection Workerman\Connection\TcpConnection
     * @param $http_header
     */
    $connection->onWebSocketConnect = function($connection, $http_header) {
        //连接验证
        if(false) {
            $connection->close();
        }
    };
    $connection->closed = false;
    $connection->onClose = function($connection) {
        $connection->closed = true;
    };
};

/**
 * 收到消息
 *
 * @param $connection Workerman\Connection\TcpConnection
 * @param $data       string 数据
 */
$worker->onMessage = function($connection, $data) {
    $connection->lastMessageTime = time();
    $data = json_decode($data);
    if(is_null($data) || !isset($data->code)) {
        $connection->send(json_encode([
            'code'     => -1024,
            'message0' => '(╯▔＾▔)╯︵ ┻━┻',
            'message1' => '┬─┬ ノ(▔ - ▔ノ)',
            'message2' => '(╯°Д°)╯︵ ┻━┻'
        ]));
        return;
    }
    try {
        switch($data->code) {
            case MESSAGE_TYPE::LOGIN:
                if(isset($data->token)) {
                    $user = User::getInstance()->getOneByToken($data->token);
                    if($user === null) {
                        $connection->send(json_encode([
                            'code' => MESSAGE_CODE::NEED_RE_LOGIN
                        ]));
                    } else {
                        $connection->user_info = $user;
                        $connection->send(json_encode([
                            'code' => MESSAGE_CODE::SUCCESS
                        ]));
                    }
                } elseif(isset($data->username) && isset($data->password)) {
                    $user = User::getInstance()->getOne($data->username, $data->password);
                    if($user === null) {
                        $connection->send(json_encode([
                            'code' => MESSAGE_CODE::USERNAME_PASSWORD_ERROR
                        ]));
                    } else {
                        $connection->user_info = $user;
                        $connection->send(json_encode([
                            'code'  => MESSAGE_CODE::SUCCESS,
                            'token' => $user->token
                        ]));
                    }
                } else {
                    $connection->send(json_encode([
                        'code' => MESSAGE_CODE::UNKNOWN_ERROR
                    ]));
                }
                break;
            case MESSAGE_TYPE::LOG_OUT:
                if(isset($connection->user_info)) {
                    User::getInstance()->logOut($connection->user_info->token);
                    $connection->send(json_encode([
                        'code' => MESSAGE_CODE::SUCCESS
                    ]));
                } else {
                    $connection->send(json_encode([
                        'code' => MESSAGE_CODE::UNKNOWN_ERROR
                    ]));
                }
                break;
            case MESSAGE_TYPE::REGISTER:
                if(isset($connection->user_info)) {
                    $connection->send(json_encode([
                        'code' => MESSAGE_CODE::NEED_MORE_INFORMATION
                    ]));
                    break;
                }
                if(!isset($data->username) || !isset($data->password) || !isset($data->name)) {
                    $connection->send(json_encode([
                        'code' => MESSAGE_CODE::NEED_MORE_INFORMATION
                    ]));
                    break;
                }
                if(User::getInstance()->usernameExists($data->username)) {
                    $connection->send(json_encode([
                        'code' => MESSAGE_CODE::USERNAME_ALREADY_EXIST
                    ]));
                    break;
                }
                $id = User::getInstance()->register($data->username, $data->password, $data->name);
                if($id === false) {
                    $connection->send(json_encode([
                        'code' => MESSAGE_CODE::UNKNOWN_ERROR
                    ]));
                    break;
                }
                //TODO: Auto Log in
                $connection->send(json_encode([
                    'code' => MESSAGE_CODE::SUCCESS
                ]));
                break;
            case MESSAGE_TYPE::JUDGE:
                if(!isset($connection->user_info)) {
                    $connection->send(json_encode([
                        'code' => MESSAGE_CODE::NEED_LOGIN
                    ]));
                    break;
                }
                if(!isset($data->qid) || !isset($data->language) || !isset($data->source_code)) {
                    $connection->send(json_encode([
                        'code' => MESSAGE_CODE::NEED_MORE_INFORMATION
                    ]));
                    break;
                }
                try {
                    $judge = new Judge($data->qid, $data->language, $data->source_code);
                } catch (QuestionDoesNotExistException $e) {
                    $connection->send(json_encode([
                        'code'    => $e->getCode(),
                        'message' => $e->getMessage()
                    ]));
                    break;
                }
                $result = Result::getInstance()
                    ->add($connection->user_info->_id, $judge->question['id'], $data->source_code, $data->language);
                if($result === false) {
                    $connection->send(json_encode([
                        'code' => MESSAGE_CODE::UNKNOWN_ERROR
                    ]));
                    break;
                }
                try {
                    $process = $judge->start($connection->worker->id, $connection->getRemoteIp());
                    $process->rid = $result;
                    $process->client = $connection;
                    $connection->worker->process_pool[] = $process;
                    $connection->send(json_encode([
                        'code' => MESSAGE_CODE::SUCCESS,
                        'id'   => $result
                    ]));
                } catch (UnknownLanguageException $e) {
                    $connection->send(json_encode([
                        'code'    => $e->getCode(),
                        'message' => $e->getMessage()
                    ]));
                }
                break;
            case MESSAGE_TYPE::ADD_QUESTION:
                if(!isset($connection->user_info)) {
                    $connection->send(json_encode([
                        'code' => MESSAGE_CODE::NEED_LOGIN
                    ]));
                    break;
                }
                if(!$connection->user_info->su) {
                    $connection->send(json_encode([
                        'code' => MESSAGE_CODE::ACCESS_DENY
                    ]));
                    break;
                }
                if(!isset($data->title) || !isset($data->description) || !isset($data->test_case) || !isset($data->answer)) {
                    $connection->send(json_encode([
                        'code' => MESSAGE_CODE::NEED_MORE_INFORMATION
                    ]));
                    break;
                }
                if(!is_array($data->test_case) || !is_array($data->answer)) {
                    $connection->send(json_encode([
                        'code' => MESSAGE_CODE::PARAMETER_ERROR
                    ]));
                    break;
                }
                $d = [
                    'title'       => $data->title,
                    'description' => $data->description
                ];
                if(isset($data->memory_limit)) {
                    $d['memory'] = $data->memory_limit;
                }
                if(isset($data->time_limit)) {
                    $d['time'] = $data->time_limit;
                }
                if(isset($data->data)) {
                    $d['data'] = $data->data;
                }
                try {
                    $result = Question::getInstance()->add($d, $data->test_case, $data->answer);
                    if($result === false) {
                        $connection->send(json_encode([
                            'code' => MESSAGE_CODE::UNKNOWN_ERROR
                        ]));
                        break;
                    }
                    $connection->send(json_encode([
                        'code' => MESSAGE_CODE::SUCCESS,
                        'id'   => (string)$result
                    ]));
                } catch (TestCaseCountException $e) {
                    $connection->send(json_encode([
                        'code'    => $e->getCode(),
                        'message' => $e->getMessage()
                    ]));
                }
                break;
        }
    } catch (Exception $e) {
        logs(
            $e->getCode().' '.
            $e->getMessage().'\n'.
            $e->getLine().' of '.
            $e->getFile().'\n'.
            $e->getTraceAsString()
            , 2
        );
        $connection->send(json_encode([
            'code'    => $e->getCode(),
            'message' => 'error'
        ]));
    }
};

/**
 * 连接断开
 *
 * @param $connection
 */
$worker->onClose = function($connection) {
};

/**
 * 出错
 *
 * @param $connection
 * @param $code
 * @param $msg
 */
$worker->onError = function($connection, $code, $msg) {
    logs($code.' '.$msg, 2);
};
