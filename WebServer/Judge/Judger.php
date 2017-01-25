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
 * Date: 2016/12/10
 * Time: 21:52
 */


namespace Judge;
require_once __DIR__.'/../config.php';
use stdClass;

/**
 * Class Judger
 * @package Judge
 */
abstract class Judger {
    /**
     * 测试用例文件路径
     * @var string
     */
    protected $in;
    /**
     * 测试校验文件路径
     * @var string
     */
    protected $out;
    /**
     * 代码文件文件名
     * @var string
     */
    protected $filename = 'Main';
    /**
     * 时间限制（second(s)）(单个测试用例)
     * @var float
     */
    protected $time_limit = 1;
    /**
     * 内存限制（KB）
     * @var int
     */
    protected $memory_limit = 65536;

    /**
     * Judger constructor.
     *
     * @param array $judge_info
     */
    public function __construct(stdClass $judge_info) {
        $this->in = CONFIG['judgeServer']['in'].$judge_info->qid;
        $this->out = CONFIG['judgeServer']['out'].$judge_info->qid;
        $this->filename = $judge_info->temp_path.$this->filename;
        $this->time_limit = $judge_info->time;
        $this->memory_limit = $judge_info->memory;
        //将代码保存到文件
        file_put_contents($this->filename, $judge_info->code);
    }

    /**
     * 测试命令行
     * @return string
     */
    protected abstract function command();

    /**
     * 开始测试
     * @return array
     */
    public function start() {
        $result = [];
        exec($this->command(), $result);  //阻塞执行
        return $this->validate($result);
    }

    /**
     * 验证结果
     *
     * @param array $result
     *
     * @return array
     */
    protected function validate(array $result) {
        global $JUDGE_STATUS;
        $answer = preg_split('/[\r\n]+/', trim(file_get_contents($this->out)));
        $last = $result[count($result) - 1];
        if($last == 'Compile Error') {
            return [
                'result' => $JUDGE_STATUS->CompileError,
                'info'   => join("\n", $result)
            ];
        }
        if($last == 'Compile Time Out') {
            return [
                'result' => $JUDGE_STATUS->CompileTimeout
            ];
        }
        if($last == 'Time Out') {
            return [
                'result' => $JUDGE_STATUS->Timeout
            ];
        }
        if($last == 'Runtime Error') {
            return [
                'result' => $JUDGE_STATUS->RuntimeError
            ];
        }
        $wa = false;
        for($i = count($result) - 2, $j = count($answer) - 1; $i >= 0 && $j >= 0; --$i, --$j) {
            while($result[$i] == '<<entering SECCOMP mode>>') {
                --$i;
            }
            if($result[$i] != $answer[$j]) {
                $wa = true;
                break;
            }
        }
        return $wa || $j >= 0 ? [
            'result' => $JUDGE_STATUS->WrongAnswer
        ] : [
            'result' => $JUDGE_STATUS->Accepted
        ];
    }
}
