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
 * Date: 2017/1/18
 * Time: 16:17
 */
require __DIR__.'/../Workerman/Autoloader.php';
require __DIR__.'/../config.php';
use Database\User;

define('IS_HTTPS', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') || $_SERVER['SERVER_PORT'] == 443);
session_start();
if(isset($_SESSION['user'])) {
    User::getInstance()->logOut($_SESSION['user']->token);

    //通过Workerman Channel通讯客户向Websocket发送注销消息，断开Websocket连接
    $socket = socket_create(AF_INET6, SOCK_STREAM, SOL_TCP);
    if(socket_connect($socket, CONFIG['websocket']['channel']['address'], CONFIG['websocket']['channel']['port'])) {
        $data = serialize([
            'type'     => 'publish',
            'channels' => (array)'logout',
            'data'     => $_SESSION['user']->token
        ]);
        $data = pack('N', 4 + strlen($data)).$data;
        socket_write($socket, $data);
        socket_close($socket);
    }
    unset($_SESSION['user']);
}
setcookie('token', '', -1, null, null, IS_HTTPS, false);
header('Location: '.(isset($_GET['url']) ? $_GET['url'] : '/'), true, 301);
