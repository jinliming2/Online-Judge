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
 * Date: 2016/11/7
 * Time: 20:42
 */


namespace Judge;

use Constant\LANGUAGE_TYPE;
use Constant\MESSAGE_CODE;
use Database\Result;
use Exception\CannotCreateProcessException;
use Workerman\Connection\AsyncTcpConnection;

/**
 * Class JudgeProcess
 * @package Judge
 */
class JudgeProcess {
    public $rid;
    public $started = false;
    public $finished = false;  //False: Processing, True: Finished success, Null: Finished failed
    private $judger_info;

    /**
     * JudgeProcess constructor.
     *
     * @param LANGUAGE_TYPE $language
     * @param string        $temp_path
     * @param string        $code
     * @param array         $question
     */
    public function __construct($language, $temp_path, $code, $question) {
        $this->judger_info = [
            'language'  => $language,
            'temp_path' => $temp_path,
            'code'      => $code,
            'question'  => $question,
        ];
    }

    /**
     * @throws CannotCreateProcessException
     */
    public function run() {
        $task_connection = new AsyncTcpConnection('text://[::1]:8888');
        /**
         * @param AsyncTcpConnection $task_connection
         * @param string $task_result
         */
        $task_connection->onMessage = function($task_connection, $task_result) {
            $data = json_decode($task_result);
            $this->clean();
            if($data->code == MESSAGE_CODE::SUCCESS) {
                Result::getInstance()->update($this->rid, $data->result);
                $this->finished = true;
            } else {
                $this->finished = null;
            }
            $task_connection->close();
        };
        $task_connection->send(json_encode($this->judger_info));
        $task_connection->connect();
        $this->started = true;
    }

    /**
     * @param \stdClass $judger_info
     *
     * @return Judger
     */
    public static function getJudger($judger_info) {
        switch($judger_info->language) {
            case LANGUAGE_TYPE::C:
                return null;  //TODO: C Judger
            case LANGUAGE_TYPE::CPP:
                return new CppJudge(
                    $judger_info->temp_path,
                    $judger_info->code,
                    $judger_info->question
                );
            case LANGUAGE_TYPE::JAVA:
                return null;  //TODO: JAVA Judger
            default:
                return null;
        }
    }

    /**
     * 清理环境
     */
    public function clean() {
        require_once __DIR__.'/../config.php';
        if(strpos($this->judger_info['temp_path'], CONFIG['judge temp']) === 0) {  //如果没有找到是返回False
            system('rm -rf '.$this->judger_info['temp_path']);
        }
    }
}
