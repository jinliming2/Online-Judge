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

use Exception\QuestionDoesNotExistException;
use Exception\UnknownLanguageException;

/**
 * Class Judge
 * @package Judge
 */
class Judge {
    public $question;
    private $language;
    private $code;

    /**
     * Judge constructor.
     *
     * @param $question_id
     * @param $language
     * @param $code
     *
     * @throws QuestionDoesNotExistException
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
     * @return JudgeProcess
     * @throws UnknownLanguageException
     */
    public function start($id = '', $ip = '') {
        switch($this->language) {
            case LANGUAGE_TYPE::C:
            case LANGUAGE_TYPE::CPP:
            case LANGUAGE_TYPE::JAVA:
                break;
            default:
                throw new UnknownLanguageException;
        }
        include_once __DIR__.'/../config.php';
        $ex = str_replace(['\\', '/', ':', '*', '?', '"', '<', '>', '|'], '', $id.'-'.$ip.'-');
        $temp_path = uniqid($ex, true);
        return new JudgeProcess($this->language, $temp_path, $this->code, $this->question);
    }

    /**
     * 获取问题信息
     *
     * @param $question_id
     *
     * @return array
     * @throws QuestionDoesNotExistException
     */
    private function getQuestion($question_id) {
        $q = Question::getInstance()->getOne($question_id);
        if($q === null) {
            throw new QuestionDoesNotExistException;
        }
        return [
            'id'           => $q->_id,
            'test_case'    => $q->test,
            'answer'       => trim(file_get_contents($q->answer)),
            'time_limit'   => isset($q->time) ? $q->time : 1.0,
            'memory_limit' => isset($q->memory) ? $q->memory : 65536
        ];
    }
}
