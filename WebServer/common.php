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
 * Time: 20:25
 */
use Workerman\Connection\TcpConnection;

/**
 * 格式化Json常量
 *
 * @param string $node
 *
 * @return stdClass
 */
function parseJsonConstant(string $node) {
    $json = json_decode(file_get_contents(__DIR__.'/Constant/constant.json'));
    $ret = new stdClass();
    foreach($json->$node as $k => $v) {
        $v = $v[0];
        $ret->$v = $k;
    }
    return $ret;
}

/**
 * 输出日志
 *
 * @param mixed  $message
 * @param string $type
 */
function logs($message, string $type = ' ') {
    if(!is_string($message)) {
        $message = (string)$message;
    }
    $message = str_replace("\n", "\n                        ", $message);
    if($type == '') {
        $type = ' ';
    } elseif(strlen($type) > 1) {
        $type = substr($type, 0, 1);
    }
    echo date('Y-m-d H:i:s').'  '.$type.'  '.$message."\n";
}

/**
 * 连接心跳
 *
 * @param TcpConnection $connection
 */
function heartBeat(TcpConnection $connection) {
    $connection->lastMessageTime = time();
}

/**
 * 取当前时间戳（13位）
 * @return string
 */
function timestamp() {
    list($t1, $t2) = explode(' ', microtime());
    return $t2.ceil(($t1 * 1000));
}
