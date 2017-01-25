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
 * Date: 2016/12/30
 * Time: 14:59
 */
require_once __DIR__.'/../Workerman/Autoloader.php';
use Database\User;

header('Content-type: application/json');
if(isset($_REQUEST['type']) && isset($_REQUEST['value'])) {
    switch($_REQUEST['type']) {
        case 'username':
            if(User::getInstance()->usernameExists($_REQUEST['value'])) {
                echo json_encode([
                    'code'    => 1,
                    'message' => '用户名已被注册，请尝试使用其他用户名！'
                ]);
            } else {
                echo json_encode([
                    'code'    => 0,
                    'message' => 'success'
                ]);
            }
            break;
    }
} else {
    echo json_encode([
        'code'    => -1,
        'message' => 'error'
    ]);
}
