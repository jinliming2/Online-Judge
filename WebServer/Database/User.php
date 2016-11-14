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
 * Date: 2016/11/9
 * Time: 20:34
 */


namespace Database;

use MongoDB\BSON\ObjectID;
use MongoDB\Driver\BulkWrite;
use MongoDB\Driver\Exception\RuntimeException;
use MongoDB\Driver\Query;


/**
 * Class User
 * @package Database
 */
final class User extends Database {
    private static $obj = null;
    private static $table = 'users';

    /**
     * Question constructor.
     */
    protected function __construct() {
        parent::__construct();
        User::$table = Database::$database.'.'.User::$table;
    }

    /**
     * @return User
     */
    public static function getInstance() {
        if(User::$obj == null) {
            User::$obj = new User();
        }
        return User::$obj;
    }

    /**
     * @param string $username
     * @param string $password
     * @param string $name
     *
     * @return ObjectID|False
     * @throws RuntimeException
     */
    public function register($username, $password, $name) {
        $bulk = new BulkWrite();
        $insert = $bulk->insert([
            'username' => $username,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'name'     => $name,
            'su'       => false
        ]);
        $result = Database::$connection->executeBulkWrite(User::$table, $bulk);
        if($result->getInsertedCount() > 0) {
            return $insert;
        }
        return false;
    }

    /**
     * @param string $username
     * @param string $password
     *
     * @return \stdClass|null
     * @throws RuntimeException
     */
    public function getOne($username, $password) {
        $query = new Query(['username' => $username]);
        $rows = Database::$connection->executeQuery(User::$table, $query)->toArray();
        if(count($rows) > 0) {
            if(password_verify($password, $rows[0]->password)) {
                unset($rows[0]->password);
                $rows[0]->token = $this->generateToken($username);
                $bulk = new BulkWrite();
                $bulk->update(['_id' => $rows[0]->_id], ['$set' => [
                    'token' => $rows[0]->token
                ]]);
                Database::$connection->executeBulkWrite(User::$table, $bulk);
                return $rows[0];
            }
        }
        return null;
    }

    /**
     * @param string $token
     *
     * @return \stdClass|null
     * @throws RuntimeException
     */
    public function getOneByToken($token) {
        $query = new Query(['token' => $token], [
            'projection' => ['password' => 0]
        ]);
        $rows = Database::$connection->executeQuery(User::$table, $query)->toArray();
        if(count($rows) > 0) {
            return $rows[0];
        }
        return null;
    }

    /**
     * @param string $token
     *
     * @throws RuntimeException
     */
    public function logOut($token) {
        $bulk = new BulkWrite();
        $bulk->update(['token' => $token], ['$unset' => [
            'token' => null
        ]]);
        Database::$connection->executeBulkWrite(User::$table, $bulk);
    }

    /**
     * @param string $username
     *
     * @return string
     */
    private function generateToken($username) {
        $a = [
            'a', 'b', 'c', 'd', 'e', 'f', 'g',
            'h', 'i', 'j', 'k', 'l', 'm', 'n',
            'o', 'p', 'q', 'r', 's', 't',
            'u', 'v', 'w', 'x', 'y', 'z',
            'A', 'B', 'C', 'D', 'E', 'F', 'G',
            'H', 'I', 'J', 'K', 'L', 'M', 'N',
            'O', 'P', 'Q', 'R', 'S', 'T',
            'U', 'V', 'W', 'X', 'Y', 'Z',
            ',', '.', '/', '<', '>', '?',
            ';', ':', '\'', '"', '[', ']', '{', '}',
            '\\', '|', '`', '~', '-', '_', '=', '+',
            '1', '2', '3', '4', '5', '6', '7', '8', '9', '0',
            '!', '@', '#', '$', '%', '^', '&', '*', '(', ')'
        ];
        usort($a, function() {
            return random_int(-1, 1);
        });
        return $username.md5(sha1(join($a).microtime(true).$username).$username);
    }
}
