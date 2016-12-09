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
 * Time: 15:22
 */


namespace Database;

use MongoDB\BSON\ObjectID;
use MongoDB\Driver\BulkWrite;
use MongoDB\Driver\Query;
use stdClass;

/**
 * Class User
 * @package Database
 */
class User extends Database {
    /**
     * 单例对象
     * @var User
     */
    private static $obj = null;
    /**
     * 表名
     * @var string
     */
    private static $table = 'users';

    /**
     * User constructor.
     */
    protected function __construct() {
        parent::__construct();
        self::$table = parent::$database.'.'.self::$table;
    }

    /**
     * @return User
     */
    public static function getInstance() {
        if(self::$obj == null) {
            self::$obj = new self();
        }
        return self::$obj;
    }

    /**
     * 生成随机Token
     *
     * @param string $username
     *
     * @return string
     */
    private function generateToken(string $username) {
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

    /** 增 */
    /**
     * 注册
     *
     * @param string $username
     * @param string $password
     * @param string $name
     * @param array  $data
     *
     * @return ObjectID|false
     */
    public function register(string $username, string $password, string $name, array $data = []) {
        if($this->usernameExists($username)) {
            return false;
        }
        $bulk = new BulkWrite();
        $insert = $bulk->insert(array_merge([
            'username' => $username,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'name'     => $name,
            'su'       => false
        ], $data));
        $result = parent::$connection->executeBulkWrite(self::$table, $bulk);
        if($result->getInsertedCount() > 0) {
            return $insert;
        }
        return false;
    }

    /** 删 */

    /** 改 */
    /**
     * 注销
     *
     * @param string $token
     */
    public function logOut(string $token) {
        $bulk = new BulkWrite();
        $bulk->update(['token' => $token], ['$unset' => [
            'token' => null
        ]]);
        parent::$connection->executeBulkWrite(self::$table, $bulk);
    }

    /**
     * 修改资料
     *
     * @param ObjectID $user_id
     * @param array    $data
     */
    public function modify(ObjectID $user_id, array $data) {
        if(isset($data['_id'])) {
            unset($data['_id']);
        }
        $bulk = new BulkWrite();
        $bulk->update(['_id' => $user_id], ['$set' => $data]);
        parent::$connection->executeBulkWrite(self::$table, $bulk);
    }

    /**
     * 修改资料 - 删除字段
     *
     * @param ObjectID $user_id
     * @param array    $columns 字段列表，一维string数组，字段名
     */
    public function modify_unset(ObjectID $user_id, array $columns) {
        $arr = [];
        foreach($columns as $column) {
            if($column != 'username' && $column != 'password' && $column != 'name' && $column != 'su') {
                $arr[$column] = null;
            }
        }
        $bulk = new BulkWrite();
        $bulk->update(['_id' => $user_id], ['$unset' => $arr]);
        parent::$connection->executeBulkWrite(self::$table, $bulk);
    }

    /** 查 */
    /**
     * 用户名是否存在
     *
     * @param string $username
     *
     * @return bool
     */
    public function usernameExists(string $username) {
        $query = new Query(['username' => $username]);
        $rows = parent::$connection->executeQuery(self::$table, $query)->toArray();
        return count($rows) > 0;
    }

    /**
     * 登录
     *
     * @param string $account  用户名/Token
     * @param string $password 密码，当$account为Token时此值置null
     *
     * @return stdClass|null
     */
    public function login(string $account, string $password = null) {
        if(is_null($password)) {
            $query = new Query(['token' => $account], [
                'projection' => ['password' => 0]
            ]);
        } else {
            $query = new Query(['username' => $account]);
        }
        $rows = parent::$connection->executeQuery(self::$table, $query)->toArray();
        if(count($rows) > 0) {
            if(is_null($password)) {
                return $rows[0];
            }
            if(password_verify($password, $rows[0]->password)) {
                unset($rows[0]->password);
                $rows[0]->token = $this->generateToken($account);
                $this->modify($rows[0]->_id, ['token' => $rows[0]->token]);
                return $rows[0];
            }
        }
        return null;
    }
}
