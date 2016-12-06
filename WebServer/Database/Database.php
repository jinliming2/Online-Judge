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
 * Time: 15:10
 */


namespace Database;
require_once __DIR__.'/../config.php';
use MongoDB\Driver\Manager;

/**
 * Class Database
 * @package Database
 */
abstract class Database {
    /**
     * 数据库名
     * @var string
     */
    protected static $database = 'Judge';
    /**
     * 数据库连接
     * @var Manager
     */
    protected static $connection = null;

    /**
     * Database constructor.
     */
    protected function __construct() {
        if(self::$connection == null) {
            self::$connection = new Manager(CONFIG['websocket']['mongoDB']);
        }
    }

    /**
     * @return Database
     */
    public static abstract function getInstance();
}
