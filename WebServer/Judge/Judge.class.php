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

class Judge {
    private $language;
    private $code;
    private $question;
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
     */
    public function start($id = '', $ip = '') {
        include_once '../config.php';
        $path = uniqid(CONFIG['judge temp'].$id.'-'.$ip.'-', true);
        while(is_dir($path)) {
            $path .= 'n';
        }
        $path .= '/';
        mkdir($path, 0666, true);
        $judge = $this->getJudger($path, $this->code, $this->question);
        $this->result = $judge->start();
    }

    /**
     * 保存代码
     */
    public function saveCode() {
        //TODO: Save code to database
    }

    /**
     * 保存测试结果
     */
    public function saveResult() {
        //TODO: Save result to database
    }

    /**
     * 获取问题信息
     *
     * @param $question_id
     *
     * @return array
     */
    private function getQuestion($question_id) {
        //TODO: Get question information from database
        return [
            'test_case'    => '',
            'answer'       => '',
            'time_limit'   => '',
            'memory_limit' => ''
        ];
    }

    /**
     * @param $temp_path
     * @param $code
     * @param $question
     *
     * @return Judger
     *
     */
    private function getJudger($temp_path, $code, $question) {
        switch($this->language) {
            case LANGUAGE_TYPE::C:
                break;
            case LANGUAGE_TYPE::CPP:
                return new CppJudge($temp_path, $code, $question);
            case LANGUAGE_TYPE::JAVA:
                break;
        }
        return null;
    }
}
