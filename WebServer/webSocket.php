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

require_once 'Workerman/Autoloader.php';
include_once 'common.php';

//心跳包间隔（秒）
define('HEARTBEAT_TIME', 300);

//守护进程模式
Worker::$daemonize = true;
//日志
Worker::$stdoutFile = __DIR__.'/log/ws_'.date('Y-m-d').'.log';
Worker::$logFile = __DIR__.'/log/workerman.log';

$worker = new Worker('websocket://[::]:8080');

//同时服务进程数
$worker->count = 3;

/**
 * 启动监听
 *
 * @param $worker
 */
$worker->onWorkerStart = function($worker) {
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
    log('WebSocket server now listening on port 8080.');
};

/**
 * 重新加载所有子进程
 *
 * @param $worker
 */
$worker->onWorkerReload = function($worker) {
    log('WebSocket server now reloading.');
};

/**
 * 停止服务
 *
 * @param $worker
 */
$worker->onWorkerStop = function($worker) {
    log('WebSocket server now stopped.', 1);
};

/**
 * 建立连接
 *
 * @param $connection
 */
$worker->onConnect = function($connection) {
    $connection->onWebSocketConnect = function($connection, $http_header) {
        //连接验证
        if(false) {
            $connection->close();
        }
    };
};

/**
 * 收到消息
 *
 * @param $connection
 * @param $data string 数据
 */
$worker->onMessage = function($connection, $data) {
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
    log($code.' '.$msg, 2);
};

Worker::runAll();
