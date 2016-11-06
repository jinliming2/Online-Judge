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
 * Time: 15:28
 */


namespace Judge;

use Constant\JUDGE_RESULT;

/**
 * Class Judger
 * @package Judge
 */
abstract class Judger {
    protected $test_case = 'test_case.txt';
    protected $filename = 'main';
    protected $answer;
    protected $time_limit;
    protected $memory_limit;

    /**
     * Judger constructor.
     *
     * @param string $temp_path File path
     * @param string $code
     * @param array  $question
     */
    public function __construct($temp_path, $code, $question) {
        $this->filename = $temp_path.$this->filename;
        file_put_contents($this->filename, $code);
        $this->test_case = $question['test_case'];
        $this->answer = preg_split('/[\r\n]+/', $question['answer']);
        $this->time_limit = $question['time_limit'];
        $this->memory_limit = $question['memory_limit'];
    }

    /**
     * 开始测试
     */
    public function start() {
        $result = [];
        exec($this->command(), $result);  //阻塞执行
        return $this->validate($result);
    }

    /**
     * 测试命令行
     * @return string
     */
    protected abstract function command();

    /**
     * 验证结果
     *
     * @param $result
     *
     * @return int
     */
    protected function validate($result) {
        $last = $result[count($result) - 1];
        if($last == 'Compile Error') {
            return JUDGE_RESULT::COMPILE_ERROR;
        }
        if($last == 'Time Out') {
            return JUDGE_RESULT::TIME_OUT;
        }
        if(count($result) - 1 != count($this->answer)) {
            return JUDGE_RESULT::WRONG_ANSWER;
        }
        $wa = false;
        for($i = count($result) - 2; $i >= 0; --$i) {
            if($result[$i] != $this->answer[$i]) {
                $wa = true;
                break;
            }
        }
        if($wa) {
            return JUDGE_RESULT::WRONG_ANSWER;
        }
        return JUDGE_RESULT::ACCEPTED;
    }
}
