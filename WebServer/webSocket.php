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

use Constant\MESSAGE_CODE;
use Constant\MESSAGE_TYPE;

require_once 'Workerman/Autoloader.php';
require_once 'config.php';
require_once 'common.php';

//心跳包间隔（秒）
define('HEARTBEAT_TIME', 300);

//守护进程模式
Worker::$daemonize = true;
//日志
mkdir(CONFIG['stdout file'], 0660, true);
mkdir(CONFIG['log file'], 0660, true);
Worker::$stdoutFile = CONFIG['stdout file'].'ws_'.date('Y-m-d').'.log';
Worker::$logFile = CONFIG['log file'].'workerman.log';

$worker = new Worker('websocket://[::]:8080');

//同时服务进程数
$worker->count = CONFIG['process count'];

/**
 * 启动监听
 *
 * @param Worker $worker
 */
$worker->onWorkerStart = function($worker) {
    //线程们
    $worker->thread_pool = [];
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
        foreach($worker->thread_pool as $i => $thread) {
            if($thread->isRunning()) {
                ++$index;
            } else if($thread->result === null) {
                if($index < CONFIG['thread count']) {
                    ++$index;
                    $thread->start();
                }
            } else {
                //TODO: Update result to database
                unset($worker->thread_pool[$i]);
            }
        }
        $arr = [];
        foreach($worker->thread_pool as $thread) {
            $arr[] = $thread;
        }
        $worker->thread_pool = $arr;
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
};

/**
 * 收到消息
 *
 * @param $connection Workerman\Connection\TcpConnection
 * @param $data       string 数据
 */
$worker->onMessage = function($connection, $data) {
    $data = json_decode($data);
    switch($data['code']) {
        case MESSAGE_TYPE::JUDGE:
            $judge = new Judge($data['qid'], $data['language'], $data['code']);
            try {
                $judge->save();
                $thread = $judge->start($connection->worker->id, $connection->getRemoteIp());
                $connection->worker->thread_pool[] = $thread;
                $connection->send(json_encode([
                    'code' => MESSAGE_CODE::SUCCESS
                ]));
            } catch (Exception\UnknownLanguageException $e) {
                $connection->send(json_encode([
                    'code' => $e->getCode(),
                    'message' => $e->getMessage()
                ]));
            }
            break;
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

Worker::runAll();
