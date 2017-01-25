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
 * Date: 2016/12/10
 * Time: 16:19
 */
use Constant\DELIVERY_MESSAGE;
use Workerman\Connection\AsyncTcpConnection;
use Workerman\Connection\TcpConnection;

/**
 * 获取可用服务数
 *
 * @param TcpConnection $connection
 */
function mAvailable(TcpConnection $connection) {
    $connection->send($connection->worker->available);
}

/**
 * 申请服务
 *
 * @param TcpConnection $connection
 */
function mRequest(TcpConnection $connection) {
    //寻找可用服务器
    $index = $connection->worker->index;
    do {
        if($connection->worker->servers[$connection->worker->index]['available'] > 0) {
            $connection->worker->servers[$connection->worker->index]['available']--;
            $connection->server = $connection->worker->servers[$connection->worker->index]['address'];
            $connection->send(json_encode([
                'code'    => DELIVERY_MESSAGE::REQUEST_SUCCEED,
                'type'    => DELIVERY_MESSAGE::REQUEST
            ]));
            return;
        }
        ++$connection->worker->index;
        if($connection->worker->index >= $connection->worker->server_count) {
            $connection->worker->index = 0;
        }
    } while($index != $connection->worker->index);
    $connection->send(json_encode([
        'code'    => DELIVERY_MESSAGE::REQUEST_FAILED,
        'type'    => DELIVERY_MESSAGE::REQUEST
    ]));
}

/**
 * 开始服务
 *
 * @param TcpConnection $connection
 * @param stdClass      $data
 */
function mJudge(TcpConnection $connection, stdClass $data) {
    if(!isset($connection->server)) {
        return;
    }
    $task = new AsyncTcpConnection('text://'.$connection->server);
    $task->onMessage = function(AsyncTcpConnection $task, string $task_result) use ($connection) {
        $task->close();
        $task_result = json_decode($task_result);
        $ret = [
            'code' => DELIVERY_MESSAGE::JUDGE_SUCCEED,
            'type' => DELIVERY_MESSAGE::JUDGE,
            'result' => $task_result->result
        ];
        if(isset($task_result->info)) {
            $ret['info'] = $task_result->info;
        }
        $connection->send(json_encode($ret));
    };
    $task->send(json_encode([$data->data]));
    $task->connect();
}
