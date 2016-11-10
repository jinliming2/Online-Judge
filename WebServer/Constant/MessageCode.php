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
 * Time: 13:51
 */


namespace Constant;


/**
 * Class MESSAGE_CODE
 * @package Constant
 */
abstract class MESSAGE_CODE {
    const UNKNOWN_ERROR = -1;
    const SUCCESS = 0;
    const UNKNOWN_LANGUAGE = 1;
    const TEST_CASE_COUNT_ERROR = 2;
    const NEED_RE_LOGIN = 3;
    const USERNAME_PASSWORD_ERROR = 4;
    const NEED_LOGIN = 5;
}
