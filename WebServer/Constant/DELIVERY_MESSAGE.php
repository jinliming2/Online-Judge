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
 * Date: 2016/12/5
 * Time: 16:37
 */


namespace Constant;

/**
 * Class DELIVERY_MESSAGE
 * @package Constant
 */
abstract class DELIVERY_MESSAGE {
    const AVAILABLE = 1;
    const REQUEST = 2;
    const JUDGE = 3;

    const REQUEST_SUCCEED = 4;
    const JUDGE_SUCCEED = 5;
}
