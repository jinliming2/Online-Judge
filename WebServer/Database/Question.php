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
 * Date: 2016/12/9
 * Time: 14:12
 */


namespace Database;

use MongoDB\BSON\ObjectID;
use MongoDB\Driver\BulkWrite;
use MongoDB\Driver\Command;
use MongoDB\Driver\Query;
use stdClass;
require_once __DIR__.'/../common.php';

/**
 * Class Question
 * @package Database
 */
class Question extends Database {
    /**
     * 单例对象
     * @var Question
     */
    private static $obj = null;
    /**
     * 表名
     * @var string
     */
    private static $table = 'questions';

    /**
     * Question constructor.
     */
    protected function __construct() {
        parent::__construct();
        $this->tableName = parent::$database.'.'.self::$table;
    }

    /**
     * @return Question
     */
    public static function getInstance() {
        if(self::$obj == null) {
            self::$obj = new self();
        }
        return self::$obj;
    }

    /** 增 */
    /**
     * 添加问题
     *
     * @param string $title
     * @param string $description
     * @param string $username
     * @param array  $data
     *
     * @return ObjectID|false
     */
    public function add(string $title, string $description, string $username, array $data = []) {
        $bulk = new BulkWrite();
        $insert = $bulk->insert(array_merge([
            'title'       => $title,
            'description' => $description,
            'adder'       => $username,
            'add_time'    => timestamp()
        ], $data));
        $result = parent::$connection->executeBulkWrite($this->tableName, $bulk);
        if($result->getInsertedCount() > 0) {
            return $insert;
        }
        return false;
    }

    /** 删 */
    /**
     * 删除
     *
     * @param ObjectID $id
     */
    public function delete(ObjectID $id) {
        $bulk = new BulkWrite();
        $bulk->delete(['_id' => $id], ['limit' => true]);
        parent::$connection->executeBulkWrite($this->tableName, $bulk);
    }

    /** 改 */
    /**
     * 修改
     *
     * @param ObjectID $id
     * @param array    $data
     */
    public function modify(ObjectID $id, array $data) {
        if(isset($data['_id'])) {
            unset($data['_id']);
        }
        $bulk = new BulkWrite();
        $bulk->update(['_id' => $id], ['$set' => $data]);
        parent::$connection->executeBulkWrite($this->tableName, $bulk);
    }

    /**
     * 修改 - 删除字段
     *
     * @param ObjectID $id
     * @param array    $columns
     */
    public function modify_unset(ObjectID $id, array $columns) {
        $arr = [];
        foreach($columns as $column) {
            if($column != 'title' && $column != 'description' && $column != 'adder' && $column != 'add_time') {
                $arr[$column] = null;
            }
        }
        $bulk = new BulkWrite();
        $bulk->update(['_id' => $id], ['$unset' => $arr]);
        parent::$connection->executeBulkWrite($this->tableName, $bulk);
    }

    /** 查 */
    /**
     * 获取问题
     *
     * @param ObjectID $id
     *
     * @return stdClass|false
     */
    public function getOne(ObjectID $id) {
        $query = new Query(['_id' => $id]);
        $rows = parent::$connection->executeQuery($this->tableName, $query)->toArray();
        if(count($rows) > 0) {
            if(!isset($rows[0]->time)) {
                $rows[0]->time = 1;
            }
            if(!isset($rows[0]->memory)) {
                $rows[0]->memory = 65536;
            }
            return $rows[0];
        }
        return false;
    }

    /**
     * 获取问题
     *
     * @param array $condition
     * @param int   $start
     * @param int   $count
     *
     * @return array
     */
    public function getList(array $condition, int $start = 0, int $count = 0) {
        $query = new Query($condition, [
            'skip'  => $start,
            'limit' => $count
        ]);
        $rows = parent::$connection->executeQuery($this->tableName, $query)->toArray();
        unset($row);
        foreach($rows as &$row) {
            if(!isset($row->time)) {
                $row->time = 1;
            }
            if(!isset($row->memory)) {
                $row->memory = 65536;
            }
        }
        unset($row);
        return $rows;
    }

    /**
     * 取总记录数
     *
     * @param array $condition
     *
     * @return int
     */
    public function getCount(array $condition) {
        $command = new Command(['count' => self::$table, 'query' => $condition]);
        $result = parent::$connection->executeCommand(parent::$database, $command);
        return $result->toArray()[0]->n;
    }
}
