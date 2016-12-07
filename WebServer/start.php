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
 * Time: 13:46
 */
require __DIR__.'/Workerman/Autoloader.php';
require __DIR__.'/common.php';
require_once __DIR__.'/config.php';

use Workerman\Worker;

//守护进程模式
Worker::$daemonize = true;
//日志
if(!is_dir(CONFIG['stdout'])) {
    mkdir(CONFIG['stdout'], 0775, true);
}
if(!is_dir(CONFIG['log'])) {
    mkdir(CONFIG['log'], 0775, true);
}
Worker::$stdoutFile = CONFIG['stdout'].'std_'.date('Y-m-d').'.log';
Worker::$logFile = CONFIG['log'].'workerman.log';

//加载常量
$JUDGE_STATUS = parseJsonConstant('judge_status');
$MESSAGE_TYPE = parseJsonConstant('message_type');
$MESSAGE_CODE = parseJsonConstant('message_code');

//加载服务
if(CONFIG['server']['websocket']) {
    require __DIR__.'/Websocket/websocket.php';
}
if(CONFIG['server']['delivery']) {
    require __DIR__.'/Delivery/delivery.php';
}
if(CONFIG['server']['judgeServer']) {
    require __DIR__.'/JudgeServer/judgeServer.php';
}

//启动所有服务
Worker::runAll();
