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
 * Date: 2016/11/6
 * Time: 20:12
 */


namespace Database;

use Constant\JUDGE_RESULT;
use Constant\LANGUAGE_TYPE;
use MongoDB\BSON\ObjectID;
use MongoDB\Driver\BulkWrite;
use MongoDB\Driver\Exception\RuntimeException;
use MongoDB\Driver\Query;


/**
 * Class Result
 * @package Database
 */
final class Result extends Database {
    private static $obj = null;
    private static $table = 'results';

    /**
     * Result constructor.
     */
    protected function __construct() {
        parent::__construct();
        Result::$table = Database::$database.'.'.Result::$table;
    }

    /**
     * @return Result
     */
    public static function getInstance() {
        if(Result::$obj == null) {
            Result::$obj = new Result();
        }
        return Result::$obj;
    }

    /**
     * @param ObjectID|string $uid
     * @param ObjectID|string $qid
     * @param string          $code
     * @param LANGUAGE_TYPE   $language
     *
     * @return ObjectID|False rid on success, or false on error
     * @throws RuntimeException
     */
    public function add($uid, $qid, $code, $language) {
        list($t1, $t2) = explode(' ', microtime());
        $bulk = new BulkWrite();
        $insert = $bulk->insert([
            'uid'      => $uid,
            'qid'      => $qid,
            'language' => $language,
            'code'     => $code,
            'time'     => $t2.round($t1 * 1000),
            'result'   => JUDGE_RESULT::WAITING_FOR_JUDGE
        ]);
        $result = Database::$connection->executeBulkWrite(Result::$table, $bulk);
        if($result->getInsertedCount() > 0) {
            return $insert;
        }
        return false;
    }

    /**
     * @param ObjectID|string $rid
     * @param JUDGE_RESULT|int    $result
     *
     * @throws \InvalidArgumentException
     * @throws RuntimeException
     */
    public function update($rid, $result) {
        if(is_string($rid)) {
            $rid = new ObjectID($rid);
        }
        if(!($rid instanceof ObjectID)) {
            throw new \InvalidArgumentException;
        }
        $bulk = new BulkWrite();
        $bulk->update(['_id' => $rid], ['$set' => [
            'result' => $result
        ]]);
        Database::$connection->executeBulkWrite(Result::$table, $bulk);
    }

    /**
     * @param ObjectID|string $rid
     *
     * @return \stdClass|null
     * @throws \InvalidArgumentException
     * @throws RuntimeException
     */
    public function getOne($rid) {
        if(is_string($rid)) {
            $rid = new ObjectID($rid);
        }
        if(!($rid instanceof ObjectID)) {
            throw new \InvalidArgumentException;
        }
        $query = new Query(['_id' => $rid]);
        $rows = Database::$connection->executeQuery(Result::$table, $query)->toArray();
        if(count($rows) > 0) {
            return $rows[0];
        }
        return null;
    }

    /**
     * @param ObjectID|string $uid
     * @param ObjectID|string $qid
     *
     * @return array
     * @throws \InvalidArgumentException
     */
    public function getResults($uid, $qid) {
        if(is_string($uid)) {
            $uid = new ObjectID($uid);
        }
        if(!($uid instanceof ObjectID)) {
            throw new \InvalidArgumentException;
        }
        if(is_string($qid)) {
            $qid = new ObjectID($qid);
        }
        if(!($qid instanceof ObjectID)) {
            throw new \InvalidArgumentException;
        }
        $query = new Query(['uid' => $uid, 'qid' => $qid]);
        $rows = Database::$connection->executeQuery(Result::$table, $query)->toArray();
        return $rows;
    }
}
