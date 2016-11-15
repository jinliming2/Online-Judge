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
use Database\Result;
use Exception\CannotCreateProcessException;
use Workerman\Worker;

/**
 * Class JudgeProcess
 * @package Judge
 */
class JudgeProcess {
    public $pid = -1;
    public $rid;
    public $finished = false;
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
        $pid = pcntl_fork();
        if($pid < 0) {
            throw new CannotCreateProcessException;
        } elseif($pid == 0) {  //In child process
            $judge = $this->getJudger();
            $result = $judge->start();
            Result::getInstance()->update($this->rid, $result);
            $this->clean();
            Worker::stopAll();
        } else {
            $this->pid = $pid;
        }
    }

    /**
     * @return Judger
     */
    private function getJudger() {
        switch($this->judger_info['language']) {
            case LANGUAGE_TYPE::C:
                return null;  //TODO: C Judger
            case LANGUAGE_TYPE::CPP:
                return new CppJudge(
                    $this->judger_info['temp_path'],
                    $this->judger_info['code'],
                    $this->judger_info['question']
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
