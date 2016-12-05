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
use Workerman\Connection\AsyncTcpConnection;
use Workerman\Lib\Timer;
use Workerman\Worker;

//心跳包间隔（秒）
define('HEARTBEAT_TIME', 300);
$worker = new Worker('websocket://'.CONFIG['websocket']['listen']);
//同时服务进程数
$worker->count = CONFIG['websocket']['process'];

/** 启动服务
 *
 * @param Worker $worker
 */
$worker->onWorkerStart = function(Worker $worker) use ($MESSAGE_CODE) {
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
    Timer::add(5, function() use ($worker, $MESSAGE_CODE) {
        $count = count($worker->process_pool);
        if($count > 0) {
            //查询可用服务数
            $task = new AsyncTcpConnection('text://'.CONFIG['websocket']['delivery']);
            $task->onMessage = function(AsyncTcpConnection $task, string $message) use ($worker, $count, $MESSAGE_CODE) {
                $task->close();
                //创建服务请求
                $count = min($count, intval($message));
                for($i = 0; $i < $count; ++$i) {
                    $task = new AsyncTcpConnection('text://'.CONFIG['websocket']['delivery']);
                    $task->task = array_shift($worker->process_pool);
                    $task->onMessage = function(AsyncTcpConnection $task, string $message) use ($MESSAGE_CODE) {
                        $message = json_decode($message);
                        if($message->code == DELIVERY_MESSAGE::REQUEST_SUCCEED) {
                            //请求服务成功，发送任务
                            $task->send(json_encode([
                                'code' => DELIVERY_MESSAGE::JUDGE,
                                'data' => $task->task->judge_info
                            ]));
                        } elseif($message->code == DELIVERY_MESSAGE::JUDGE_SUCCEED) {
                            $task->close();
                            //任务完成
                            //TODO: 更新结果到数据库
                            if($task->task->client->alive) {
                                //回应客户端
                                $task->task->client->send(json_encode([
                                    'code'   => $MESSAGE_CODE->JudgeResult,
                                    'id'     => (string)$task->task->rid,
                                    'result' => $message->result
                                ]));
                            }
                        }
                    };
                    $task->send(json_encode(['code' => DELIVERY_MESSAGE::REQUEST]));
                    $task->connect();
                }
            };
            $task->send(json_encode(['code' => DELIVERY_MESSAGE::AVAILABLE]));
            $task->connect();
        }
    });
};
