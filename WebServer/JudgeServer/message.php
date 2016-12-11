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
 * Time: 21:39
 */
use Judge\CppJudger;
use Judge\Judger;
use Workerman\Connection\TcpConnection;

/**
 * 处理进程
 *
 * @param TcpConnection $connection
 * @param stdClass      $data
 */
function mProcess(TcpConnection $connection, stdClass $data) {
    $data->temp_path = createDirectory($data->temp_path);
    $connection->send(json_encode(getJudger($data)->start()));
}

/**
 * 创建临时文件夹
 *
 * @param string $temp_path
 *
 * @return string
 */
function createDirectory(string $temp_path) {
    $temp_path = CONFIG['judgeServer']['tmp'].$temp_path;
    while(is_dir($temp_path)) {
        $temp_path .= 'n';
    }
    $temp_path .= '/';
    mkdir($temp_path, 0775, true);
    return $temp_path;
}

/**
 * Judger 工厂
 *
 * @param stdClass $judger_info
 *
 * @return Judger|null
 */
function getJudger(stdClass $judger_info) {
    global $LANGUAGE_TYPE;
    switch($LANGUAGE_TYPE[$judger_info->language]) {
        case 'C':
            return null;  //TODO: C Judger
        case 'CPP':
            return new CppJudger($judger_info);
        case 'JAVA':
            return null;  //TODO: JAVA Judger
        default:
            return null;
    }
}
