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
 * Date: 2016/12/11
 * Time: 14:47
 */
require_once __DIR__.'/../Workerman/Autoloader.php';
use Database\User;

define('WEBP', strpos($_SERVER['HTTP_ACCEPT'], 'image/webp') === false ? '' : '.webp');
define('IS_GET', $_SERVER['REQUEST_METHOD'] == 'GET');
define('IS_POST', $_SERVER['REQUEST_METHOD'] == 'POST');
define('IS_HTTPS', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') || $_SERVER['SERVER_PORT'] == 443);
session_start();
if(isset($_SESSION['user']->_id)) {
    if(!isset($_COOKIE['token'])) {
        setcookie('token', $_SESSION['user']->token, null, null, null, IS_HTTPS, false);
    }
} elseif(isset($_COOKIE['token'])) {
    $user = User::getInstance()->login($_COOKIE['token']);
    if($user && (!isset($user->ban) || !$user->ban)) {
        $user->_id = (string)$user->_id;
        $_SESSION['user'] = $user;
    }
}
if(!isset($_SESSION['user']->_id)) {
    setcookie('token', '', -1, null, null, IS_HTTPS, false);
}
