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
 * Date: 2016/11/27
 * Time: 13:57
 */
use Workerman\Worker;
use Constant\MESSAGE_CODE;
use Database\Result;
use Judge\JudgeProcess;

require_once 'Workerman/Autoloader.php';
require_once 'config.php';

$worker = new Worker('text://[::1]:8888');  //Listen on localhost

//同时服务进程数
$worker->count = CONFIG['process count'] * CONFIG['sub process count'];

/**
 * 启动监听
 *
 * @param Worker $worker
 */
$worker->onWorkerStart = function($worker) {
    logs('Judge server now listening on local port 8080.');
};

/**
 * 重新加载所有子进程
 *
 * @param $worker
 */
$worker->onWorkerReload = function($worker) {
    logs('Judge server now reloading.');
};

/**
 * 停止服务
 *
 * @param $worker
 */
$worker->onWorkerStop = function($worker) {
    logs('Judge server now stopped.', 1);
};

/**
 * 收到消息
 *
 * @param $connection Workerman\Connection\TcpConnection
 * @param $data       string 数据
 */
$worker->onMessage = function($connection, $data) {
    try {
        $judge = JudgeProcess::getJudger(json_decode($data));
        $result = $judge->start();
        $connection->send(json_encode([
            'code'   => MESSAGE_CODE::SUCCESS,
            'result' => $result
        ]));
    } catch (Exception $e) {
        logs(
            'Data:'.$data.
            $e->getCode().' '.
            $e->getMessage().'\n'.
            $e->getLine().' of '.
            $e->getFile().'\n'.
            $e->getTraceAsString()
            , 2
        );
        $connection->send(json_encode([
            'code' => MESSAGE_CODE::UNKNOWN_ERROR
        ]));
    }
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
