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
 * Time: 16:00
 */
use Constant\DELIVERY_MESSAGE;
use Workerman\Connection\TcpConnection;
use Workerman\Worker;

require __DIR__.'/message.php';

$worker = new Worker('text://'.CONFIG['delivery']['listen']);
$worker->count = 1;

/**
 * 启动服务
 *
 * @param Worker $worker
 */
$worker->onWorkerStart = function(Worker $worker) {
    $worker->maxium = 0;
    $worker->servers = [];
    foreach(CONFIG['delivery']['serverList'] as $server) {
        $worker->servers[] = [
            'address' => $server['address'],
            'available' => $server['process']
        ];
        $worker->maxium += $server['process'];
    }
    $worker->server_count = count($worker->servers);
    $worker->available = $worker->maxium;
    $worker->index = 0;
    if($worker->server_count > 0) {
        logs('Delivery server now listening on '.CONFIG['delivery']['listen']);
    } else {
        logs('Delivery server start failed because of there is no judge server available.', 'E');
        Worker::stopAll();
    }
};

/**
 * 服务平滑重启
 *
 * @param Worker $worker
 */
$worker->onWorkerReload = function(Worker $worker) {
    logs('Delivery server now reloading.', 'C');
};

/**
 * 停止服务
 *
 * @param Worker $worker
 */
$worker->onWorkerStop = function(Worker $worker) {
    logs('Delivery server now stopped.', 'W');
};

/**
 * 客户端建立连接
 *
 * @param TcpConnection $connection
 */
$worker->onConnect = function(TcpConnection $connection) {
};

/**
 * 消息处理
 *
 * @param TcpConnection $connection
 * @param string        $data
 */
$worker->onMessage = function(TcpConnection $connection, string $data) {
    $d = json_decode($data);
    try {
        switch($d->type) {
            case DELIVERY_MESSAGE::AVAILABLE:  //可用服务数
                mAvailable($connection);
                break;
            case DELIVERY_MESSAGE::REQUEST:  //请求服务
                mRequest($connection);
                break;
            case DELIVERY_MESSAGE::JUDGE:  //开始
                mJudge($connection, $d);
                break;
        }
    } catch (Exception $e) {
        logs(
            'Delivery server '.
            $data."\n".
            $e->getCode().' '.
            $e->getMessage()."\n".
            $e->getLine().' of '.
            $e->getFile()."\n".
            $e->getTraceAsString()
            , 'E'
        );
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
    logs('Delivery server '.$connection->getRemoteIp().':'.$connection->getRemotePort().' Error: '.$code.' '.$msg, 'E');
};
