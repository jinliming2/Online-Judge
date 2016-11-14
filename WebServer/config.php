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
 * Date: 2016/11/4
 * Time: 14:39
 */

define('CONFIG', [
    'process count'     => 3,  //进程数，>=1
    'sub process count' => 10,  //各进程允许的子进程数，>=1
    'stdout file'       => __DIR__.'/log/',  //运行日志存储目录，以‘/’结尾
    'log file'          => __DIR__.'/log/',  //WorkerMan日志存储目录，以‘/’结尾
    'judge temp'        => __DIR__.'/tmp/',  //评测时的临时文件目录，以‘/’结尾
    'test case'         => __DIR__.'/test_case/',  //测试用例存储目录，以‘/’结尾
    'answer'            => __DIR__.'/answer/',  //对应测试用例答案的存储目录，以‘/’结尾
    'mongoDB'           => 'mongodb://localhost:27017',  //MongoDB数据库链接
]);
