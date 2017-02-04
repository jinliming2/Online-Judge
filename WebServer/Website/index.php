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
* Time: 13:32
*/
require_once __DIR__.'/../Workerman/Autoloader.php';
require '../Template/constant.php';
use Database\Question;
?>
<!DOCTYPE html>
<html lang="zh-cmn-Hans">
<head>
    <?php include '../Template/head.html'; ?>
    <link rel="stylesheet" href="/c/index.css">
    <title>Online Judge - Home</title>
</head>
<body>
<?php include '../Template/title.php'; ?>
<?php
$questions = Question::getInstance()->getList([], 0, 100);
?>
<div id="questions">
    <?php
    foreach($questions as $question){
        ?>
    <a href="question.php?id=<?= $question->_id ?>" target="_blank" class="question">
        <span class="date"><?= date('Y-m-d H:i:s', $question->add_time / 1000) ?></span>
        <span class="title"><?= $question->title ?></span>
        <?php
        if(isset($question->tags)) {
            foreach($question->tags as $tag) {
                ?>
        <span class="tag"><?= $tag ?></span>
                <?php
            }
        }
        ?>
    </a>
        <?php
    }
    ?>
</div>
<?php include '../Template/footer.html'; ?>
<script src="/j/websocket.js"></script>
<script src="/j/index.js"></script>
</body>
</html>
