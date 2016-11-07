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
 * Time: 13:34
 */


namespace Judge;


use Constant\LANGUAGE_TYPE;

use Database\Question;
use Exception;

/**
 * Class Judge
 * @package Judge
 */
class Judge {
    private $language;
    private $code;
    private $question;
    private $temp_path = '';
    public $result = [];

    /**
     * Judge constructor.
     *
     * @param $question_id
     * @param $language
     * @param $code
     */
    public function __construct($question_id, $language, $code) {
        $this->question = $this->getQuestion($question_id);
        $this->language = $language;
        $this->code = $code;
    }

    /**
     * 开始测试
     *
     * @param string $id Process ID
     * @param string $ip Remote Ip
     *
     * @throws Exception\UnknownLanguageException
     */
    public function start($id = '', $ip = '') {
        include_once __DIR__.'../config.php';
        $this->temp_path = uniqid(CONFIG['judge temp'].$id.'-'.$ip.'-', true);
        while(is_dir($this->temp_path)) {
            $this->temp_path .= 'n';
        }
        $this->temp_path .= '/';
        mkdir($this->temp_path, 0666, true);
        try {
            $judge = $this->getJudger($this->code, $this->question);
            $this->result = $judge->start();
        } catch (Exception\UnknownLanguageException $e) {
            $this->clean();
            throw $e;
        }
    }

    /**
     * 保存测试结果
     */
    public function save() {
        //TODO: Save code and result to database
    }

    /**
     * 获取问题信息
     *
     * @param $question_id
     *
     * @return array
     */
    private function getQuestion($question_id) {
        $q = Question::getInstance()->getOne($question_id);
        return [
            'test_case'    => $q['test'],
            'answer'       => file_get_contents($q['answer']),
            'time_limit'   => $q['time'],
            'memory_limit' => $q['memory']
        ];
    }

    /**
     * @param $code
     * @param $question
     *
     * @return Judger
     * @throws Exception\UnknownLanguageException
     */
    private function getJudger($code, $question) {
        switch($this->language) {
            case LANGUAGE_TYPE::C:
                return null;  //TODO: C Judger
            case LANGUAGE_TYPE::CPP:
                return new CppJudge($this->temp_path, $code, $question);
            case LANGUAGE_TYPE::JAVA:
                return null;  //TODO: JAVA Judger
            default:
                throw new Exception\UnknownLanguageException;
        }
    }

    /**
     * 清理环境
     */
    public function clean() {
        if($this->temp_path != '') {
            system('rm -rf '.$this->temp_path);
            $this->temp_path = '';
        }
    }
}
