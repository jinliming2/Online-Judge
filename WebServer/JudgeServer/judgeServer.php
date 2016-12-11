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
use Workerman\Connection\TcpConnection;
use Workerman\Worker;

require __DIR__.'/message.php';

$worker = new Worker('text://'.CONFIG['judgeServer']['listen']);
//同时服务进程数
$worker->count = CONFIG['judgeServer']['process'];

/**
 * 启动服务
 *
 * @param Worker $worker
 */
$worker->onWorkerStart = function(Worker $worker) {
    logs('Judge server ['.$worker->id.'] now listening on '.CONFIG['judgeServer']['listen']);
};

/**
 * 服务平滑重启
 *
 * @param Worker $worker
 */
$worker->onWorkerReload = function(Worker $worker) {
    logs('Judge server ['.$worker->id.'] now reloading.', 'C');
};

/**
 * 停止服务
 *
 * @param Worker $worker
 */
$worker->onWorkerStop = function(Worker $worker) {
    logs('Judge server ['.$worker->id.'] now stopped.', 'W');
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
$worker->onMessage = function(TcpConnection $connection, string $data) use ($JUDGE_STATUS) {
    $data = json_decode($data);
    try {
        mProcess($connection, $data);
    } catch (Exception $e) {
        logs(
            'Judge server ['.$connection->worker->id.'] '.
            $e->getCode().' '.
            $e->getMessage()."\n".
            $e->getLine().' of '.
            $e->getFile()."\n".
            $e->getTraceAsString()
            , 'E'
        );
        $connection->send(json_encode([
            'result' => $JUDGE_STATUS->UnknownError
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
    logs('Judge server ['.$connection->worker->id.'] '.$connection->getRemoteIp().':'.$connection->getRemotePort().' Error: '.$code.' '.$msg, 'E');
};
