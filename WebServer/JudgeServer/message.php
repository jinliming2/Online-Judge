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
use Judge\CJudger;
use Judge\CppJudger;
use Judge\JavaJudger;
use Judge\Judger;
use Workerman\Connection\AsyncTcpConnection;
use Workerman\Connection\TcpConnection;

/**
 * 处理进程
 *
 * @param TcpConnection $connection
 * @param stdClass      $data
 */
function mProcess(TcpConnection $connection, stdClass $data) {
    $data->temp_path = createDirectory($data->temp_path);
    if(loadTestCase($data->qid)) {
        $connection->send(json_encode(getJudger($data)->start()));
    } else {
        global $JUDGE_STATUS;
        $connection->send(json_encode([
            'result' => $JUDGE_STATUS->UnknownError
        ]));
    }
    clean($data->temp_path);
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
 * 清理环境
 *
 * @param string $temp_path
 */
function clean(string $temp_path) {
    if(strpos($temp_path, CONFIG['judgeServer']['tmp']) === 0) {  //如果没有找到是返回False
        system('rm -rf '.$temp_path);
    }
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
            return new CJudger($judger_info);
        case 'C++':
            return new CppJudger($judger_info);
        case 'JAVA':
            return new JavaJudger($judger_info);
        default:
            return null;
    }
}

/**
 * 加载并缓存或更新测试用例
 *
 * @param string $qid
 *
 * @return bool
 */
function loadTestCase(string $qid) {
    //目录
    foreach(['in', 'out', 'version'] as $t) {
        if(!is_dir(CONFIG['judgeServer'][$t])) {
            mkdir(CONFIG['judgeServer'][$t], 0775, true);
        }
    }
    //获取版本
    $ch = curl_init('http://'.CONFIG['judgeServer']['testCase'].'/version/'.$qid);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $version = curl_exec($ch);
    curl_close($ch);
    //检查版本
    if(
        (!file_exists(CONFIG['judgeServer']['in'].$qid)) ||
        (!file_exists(CONFIG['judgeServer']['out'].$qid)) ||
        (!file_exists(CONFIG['judgeServer']['version'].$qid)) ||
        $version != file_get_contents(CONFIG['judgeServer']['version'].$qid)
    ) {
        //下载
        foreach(['in', 'out'] as $t) {
            $file = fopen(CONFIG['judgeServer'][$t].$qid, 'w');
            $ch = curl_init('http://'.CONFIG['judgeServer']['testCase'].'/'.$t.'/'.$qid);
            curl_setopt($ch, CURLOPT_FILE, $file);
            curl_exec($ch);
            curl_close($ch);
            fclose($file);
        }
        $v = explode("\n", $version);
        if(trim($v[0]) != md5_file(CONFIG['judgeServer']['in'].$qid)) {
            logs('Cannot download test case: in (md5 verified failed), id: '.$qid, 'E');
            return false;
        }
        if(trim($v[1]) != md5_file(CONFIG['judgeServer']['out'].$qid)) {
            logs('Cannot download test case: out (md5 verified failed), id: '.$qid, 'E');
            return false;
        }
        file_put_contents(CONFIG['judgeServer']['version'].$qid, $version);
    }
    return true;
}
