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
 * Date: 2016/12/5
 * Time: 14:52
 */
use Constant\DELIVERY_MESSAGE;
use Database\Result;
use Workerman\Connection\AsyncTcpConnection;
use Workerman\Connection\TcpConnection;
use Workerman\Lib\Timer;
use Workerman\Worker;

require __DIR__.'/../Channel/src/Client.php';
require __DIR__.'/../Channel/src/Server.php';
require __DIR__.'/message.php';

//Workerman 通讯服务
$channel_server = new Channel\Server(CONFIG['websocket']['channel']['listen'], CONFIG['websocket']['channel']['port']);

//心跳包间隔（秒）
define('HEARTBEAT_TIME', 300);
$worker = new Worker('websocket://'.CONFIG['websocket']['listen']);
//同时服务进程数
$worker->count = CONFIG['websocket']['process'];

/**
 * 启动服务
 *
 * @param Worker $worker
 */
$worker->onWorkerStart = function(Worker $worker) use ($MESSAGE_TYPE, $MESSAGE_CODE) {
    //任务队列
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
    Timer::add(5, function() use ($worker, $MESSAGE_TYPE, $MESSAGE_CODE) {
        $count = count($worker->process_pool);
        if($count > 0) {
            //查询可用服务数
            $task = new AsyncTcpConnection('text://'.CONFIG['websocket']['delivery']);
            $task->onMessage = function(AsyncTcpConnection $task, string $message) use ($worker, $count, $MESSAGE_TYPE, $MESSAGE_CODE) {
                $task->close();
                //创建服务请求
                $count = min($count, intval($message));
                for($i = 0; $i < $count; ++$i) {
                    $task = new AsyncTcpConnection('text://'.CONFIG['websocket']['delivery']);
                    $task->task = array_shift($worker->process_pool);
                    $task->onMessage = function(AsyncTcpConnection $task, string $message) use ($worker, $MESSAGE_TYPE, $MESSAGE_CODE) {
                        $message = json_decode($message);
                        if($message->code == DELIVERY_MESSAGE::REQUEST_SUCCEED) {
                            //请求服务成功，发送任务
                            $task->send(json_encode([
                                'type' => DELIVERY_MESSAGE::JUDGE,
                                'data' => $task->task->judge_info
                            ]));
                            //向用户发送开始消息
                            Channel\Client::publish('message', [
                                'user'    => (string)$task->task->uid,
                                'message' => [
                                    'code'    => $MESSAGE_CODE->StartJudging,
                                    'type'    => $MESSAGE_TYPE->JudgeResult,
                                    'message' => 'Start Judging',
                                    'id'      => (string)$task->task->rid,
                                    '_t'      => timestamp()
                                ]
                            ]);
                        } elseif($message->code == DELIVERY_MESSAGE::JUDGE_SUCCEED) {
                            $task->close();
                            //任务完成
                            Result::getInstance()->updateResult($task->task->rid, $message->result);
                            //向用户发送结果
                            $ret = [
                                'code'    => $message->result,
                                'type'    => $MESSAGE_TYPE->JudgeResult,
                                'message' => 'Judge Result',
                                'id'      => (string)$task->task->rid,
                                '_t'      => timestamp()
                            ];
                            if(isset($message->info)) {
                                $ret['info'] = $message->info;
                            }
                            Channel\Client::publish('message', [
                                'user'    => (string)$task->task->uid,
                                'message' => $ret
                            ]);
                        } elseif($message->code == DELIVERY_MESSAGE::REQUEST_FAILED) {
                            $task->close();
                            //请求任务失败，回到队伍前排继续
                            array_unshift($worker->process_pool, $task->task);
                        }
                    };
                    $task->send(json_encode(['type' => DELIVERY_MESSAGE::REQUEST]));
                    $task->connect();
                }
            };
            $task->send(json_encode(['type' => DELIVERY_MESSAGE::AVAILABLE]));
            $task->connect();
        }
    });
    //Workerman 通讯客户
    Channel\Client::connect(CONFIG['websocket']['channel']['listen'], CONFIG['websocket']['channel']['port']);
    //广播消息
    Channel\Client::on('broadcast', function($event_data) use ($worker) {
        foreach($worker->connections as $connection) {
            if($connection->alive) {
                heartBeat($connection);
                $connection->send(json_encode($event_data));
            }
        }
    });
    //指定用户消息
    Channel\Client::on('message', function(array $event_data) use ($worker) {
        foreach($worker->connections as $connection) {
            if(!$connection->alive) {
                continue;
            }
            if(isset($connection->user_info) && $event_data['user'] == $connection->user_info->_id) {
                heartBeat($connection);
                $connection->send(json_encode($event_data['message']));
            }
        }
    });
    //用户注销
    Channel\Client::on('logout', function(string $token) use ($worker, $MESSAGE_TYPE) {
        foreach($worker->connections as $connection) {
            if($connection->user_info->token == $token) {
                $connection->send(json_encode([
                    'type'    => $MESSAGE_TYPE->Logout,
                    'message' => 'Logged out.',
                    '_t'      => timestamp()
                ]));
                $connection->close();
            }
        }
    });
    logs('Websocket server ['.$worker->id.'] now listening on '.CONFIG['websocket']['listen']);
};

/**
 * 服务平滑重启
 *
 * @param Worker $worker
 */
$worker->onWorkerReload = function(Worker $worker) {
    logs('WebSocket server ['.$worker->id.'] now reloading.', 'C');
};

/**
 * 停止服务
 *
 * @param Worker $worker
 */
$worker->onWorkerStop = function(Worker $worker) {
    logs('WebSocket server ['.$worker->id.'] now stopped.', 'W');
};

/**
 * 客户端建立连接
 *
 * @param TcpConnection $connection
 */
$worker->onConnect = function(TcpConnection $connection) {
    $connection->onWebSocketConnect = function(TcpConnection $connection, string $http_header) {
        //连接验证
        if(false) {
            $connection->close();
        }
    };
    heartBeat($connection);
    //客户端连接存活状态
    $connection->alive = true;
    $connection->onClose = function(TcpConnection $connection) {
        $connection->alive = false;
    };
};

/**
 * 消息处理
 *
 * @param TcpConnection $connection
 * @param string        $data
 */
$worker->onMessage = function(TcpConnection $connection, string $data) use ($MESSAGE_TYPE, $MESSAGE_CODE) {
    heartBeat($connection);
    $d = json_decode($data);
    if(is_null($d) || !isset($d->type)) {
        $connection->send(json_encode([
            'code'     => -1024,
            'message0' => '(╯▔＾▔)╯︵ ┻━┻',
            'message1' => '┬─┬ ノ(▔ - ▔ノ)',
            'message2' => '(╯°Д°)╯︵ ┻━┻'
        ]));
        return;
    }
    if(!isset($d->_t)) {
        $d->_t = timestamp();
    }
    try {
        switch($d->type) {
            case $MESSAGE_TYPE->Login:  //用户登录
                mLogin($connection, $d);
                break;
            case $MESSAGE_TYPE->Judge:  //代码评判
                mJudge($connection, $d);
                break;
            default:
                $connection->send(json_encode([
                    'code'    => $MESSAGE_CODE->AccessDeny,
                    'type'    => $MESSAGE_TYPE->Error,
                    'message' => 'Access Deny',
                    '_t'      => $d->_t
                ]));
                break;
        }
    } catch (Exception $e) {
        logs(
            'WebSocket server ['.$connection->worker->id.'] '.
            $data."\n".
            $e->getCode().' '.
            $e->getMessage()."\n".
            $e->getLine().' of '.
            $e->getFile()."\n".
            $e->getTraceAsString()
            , 'E'
        );
        $connection->send(json_encode([
            'code'    => $MESSAGE_CODE->ServiceUnavailable,
            'type'    => $MESSAGE_TYPE->Error,
            'message' => 'Service Unavailable',
            '_t'      => $d->_t
        ]));
    }
};

/**
 * 连接断开
 *
 * @param TcpConnection $connection
 */
$worker->onClose = function(TcpConnection $connection) {
};

/**
 * 出错
 *
 * @param TcpConnection $connection
 * @param int           $code
 * @param string        $msg
 */
$worker->onError = function(TcpConnection $connection, int $code, string $msg) {
    logs('WebSocket server ['.$connection->worker->id.'] '.$connection->getRemoteIp().':'.$connection->getRemotePort().' Error: '.$code.' '.$msg, 'E');
};
