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
 * Time: 14:42
 */


namespace Database;

use MongoDB\BSON\ObjectID;
use MongoDB\Driver\BulkWrite;
use MongoDB\Driver\Query;

/**
 * Class Result
 * @package Database
 */
class Result extends Database {
    /**
     * 单例对象
     * @var Result
     */
    private static $obj = null;
    /**
     * 表名
     * @var string
     */
    private static $table = 'results';

    /**
     * Result constructor.
     */
    protected function __construct() {
        parent::__construct();
        self::$table = parent::$database.'.'.self::$table;
    }

    /**
     * @return Result
     */
    public static function getInstance() {
        if(self::$obj == null) {
            self::$obj = new self();
        }
        return self::$obj;
    }

    /** 增 */
    /**
     * 添加评测记录
     *
     * @param ObjectID $uid
     * @param ObjectID $qid
     * @param int      $language
     * @param string   $code
     * @param array    $data
     *
     * @return ObjectID|false
     */
    public function add(ObjectID $uid, ObjectID $qid, int $language, string $code, array $data = []) {
        global $JUDGE_STATUS;
        $bulk = new BulkWrite();
        $insert = $bulk->insert(array_merge([
            'uid'      => $uid,
            'qid'      => $qid,
            'language' => $language,
            'code'     => $code,
            'time'     => timestamp(),
            'result'   => $JUDGE_STATUS->WaitingForJudge
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
     * 更新测试结果
     *
     * @param ObjectID $rid
     * @param int      $result
     */
    public function updateResult(ObjectID $rid, int $result) {
        $bulk = new BulkWrite();
        $bulk->update(['_id' => $rid], ['$set' => [
            'result' => $result
        ]]);
        parent::$connection->executeBulkWrite(self::$table, $bulk);
    }

    /** 查 */
    /**
     * 获取用户提交历史
     * @param ObjectID $uid
     * @param ObjectID $qid
     *
     * @return array
     */
    public function getQuestionResult(ObjectID $uid, ObjectID $qid) {
        $query = new Query(['uid' => $uid, 'qid' => $qid], [
            'sort' => ['time' => -1]
        ]);
        return parent::$connection->executeQuery(self::$table, $query)->toArray();
    }
}
