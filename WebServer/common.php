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

/**
 * 日志格式
 *
 * @param $msg
 * @param int [$type] 0 = Normal, 1 = Warning, 2 = Error
 */
function logs($msg, $type = 0) {
    echo date('Y-m-d H:i:s').'  '.($type == 1 ? 'W' : $type == 2 ? 'E' : ' ').'  '.$msg."\n";
}
