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
    'server'      => [  //服务类型，用于分布式，不同用途服务器启动不同服务
        'websocket'   => true,
        'delivery'    => true,
        'judgeServer' => true
    ],
    'stdout'      => __DIR__.'/log/',  //运行时日志存储目录，以‘/’结尾
    'log'         => __DIR__.'/log/',  //Workerman日志存储目录，以‘/’结尾
    'websocket'   => [  //用户服务
        'process'  => 3,  //进程数
        'listen'   => '[::]:8080',  //监听端口
        'delivery' => '[::1]:9999',  //判题分配服务地址
        'interval' => 10,  //用户提交评判请求最短时间间隔（秒）
        'mongoDB'  => 'mongodb://localhost:27017',  //MongoDB数据库连接
        'channel'  => [  //进程通信服务
            'listen' => '[::1]',  //监听地址
            'port'   => 2206  //监听端口
        ]
    ],
    'delivery'    => [  //判题分配服务
        'listen'     => '[::1]:9999',  //监听端口
        'serverList' => [  //判题服务器列表
            [
                'address' => '[::1]:6666',  //判题服务器地址
                'process' => 10  //判题服务器进程数
            ]
        ]
    ],
    'judgeServer' => [  //判题服务
        'process' => 10,  //进程数
        'in'      => __DIR__.'/in/',  //测试用例存储目录，以‘/’结尾
        'out'     => __DIR__.'/out/',  //测试用例对应答案存储目录，以‘/’结尾
        'tmp'     => __DIR__.'/tmp/',  //判题临时文件存储目录，以‘/’结尾
        'listen'  => '[::1]:6666',  //监听端口
    ]
]);
