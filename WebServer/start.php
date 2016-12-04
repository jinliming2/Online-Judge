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
use Workerman\Worker;

require_once 'Workerman/Autoloader.php';
require_once 'config.php';

//守护进程模式
Worker::$daemonize = true;
//日志
if(!is_dir(CONFIG['stdout file'])) {
    mkdir(CONFIG['stdout file'], 0775, true);
}
if(!is_dir(CONFIG['log file'])) {
    mkdir(CONFIG['log file'], 0775, true);
}
Worker::$stdoutFile = CONFIG['stdout file'].'ws_'.date('Y-m-d').'.log';
Worker::$logFile = CONFIG['log file'].'workerman.log';

require_once 'judgeServer.php';
require_once 'webSocket.php';

Worker::runAll();
