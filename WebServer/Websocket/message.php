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
 * Date: 2016/12/6
 * Time: 14:56
 */
use Workerman\Connection\TcpConnection;

/**
 * 用户登录
 *
 * @param TcpConnection $connection
 * @param stdClass      $data
 */
function mLogin(TcpConnection $connection, stdClass $data) {
}

/**
 * 用户注销
 *
 * @param TcpConnection $connection
 * @param stdClass      $data
 */
function mLogout(TcpConnection $connection, stdClass $data) {
}

/**
 * 用户注册
 *
 * @param TcpConnection $connection
 * @param stdClass      $data
 */
function mRegister(TcpConnection $connection, stdClass $data) {
}

/**
 * 代码评判
 *
 * @param TcpConnection $connection
 * @param stdClass      $data
 */
function mJudge(TcpConnection $connection, stdClass $data) {
}

/**
 * 添加问题
 *
 * @param TcpConnection $connection
 * @param stdClass      $data
 */
function mAddQuestion(TcpConnection $connection, stdClass $data) {
}
