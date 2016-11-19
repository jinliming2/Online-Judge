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
 * Time: 16:10
 */


namespace Judge;


class CppJudge extends Judger {
    protected $filename = 'main.cpp';

    /**
     * 测试命令行
     * @return string
     */
    protected function command() {
        return sprintf(
            'docker run -v %s:/mnt/main.cpp:ro -i --rm --net none liming/cpp -t %d -m %d < %s 2>/dev/null',
            $this->filename,
            $this->time_limit,
            $this->memory_limit,
            $this->test_case
        );
    }
}
