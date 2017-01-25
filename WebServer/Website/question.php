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
 * Date: 2017/1/17
 * Time: 16:35
 */
require_once __DIR__.'/../Workerman/Autoloader.php';
require '../Template/constant.php';
use Database\Question;
use MongoDB\BSON\ObjectID;

if(empty($_GET['id'])) {
    header('Location: /', true, 301);
    die;
}
try {
    $qid = new ObjectID($_GET['id']);
} catch (Exception $e) {
    header('403 Forbidden', true, 403);
    die;
}
$question = Question::getInstance()->getOne($qid);
if($question === false) {
    header('404 Not Found', true, 404);
    die;
}
?>
<!DOCTYPE html>
<html lang="zh-cmn-Hans">
<head>
    <?php include '../Template/head.html'; ?>
    <link rel="stylesheet" href="/c/question.css">
    <title><?= $question->title ?> - Online Judge - Question</title>
</head>
<body>
<?php include '../Template/title.php'; ?>
<header id="title"><?= $question->title ?></header>
<div id="info" class="flex">
    <?php
    if(isset($question->tags)) {
        ?>
    <span><?php
        foreach($question->tags as $tag) {
            ?><span class="tag"><?= $tag ?></span><?php
        }
        ?></span>
        <?php
    }
    ?>
    <span><?= $question->time ?>s / <?= $question->memory ?>KB</span>
    <span><?= date('Y-m-d H:i:s', $question->add_time / 1000) ?></span>
    <span><?= $question->adder ?></span>
</div>
<aside><?= $question->description ?></aside>
<hr>
<?php
if(isset($_SESSION['user']->_id)) {
    ?>
<div id="setting" class="flex">
    <span>
        <label for="language">代码语言：</label>
        <select id="language"></select>
        <span id="language_tip"><i>请正确选择代码语言，选择错误将可能会导致在线编译出错！</i></span>
    </span>
    <span>
        <button id="download">保存代码</button>
    </span>
</div>
<pre id="editor"></pre>
<div class="flex">
    <button id="submit" type="submit">提交</button>
</div>
    <?php
} else {
    ?>
<div id="login_tip">登录后可以提交代码进行评判</div>
    <?php
}
?>
<?php include '../Template/footer.html'; ?>
<script src="/j/ace/src-min-noconflict/ace.js"></script>
<script src="/j/question.js"></script>
</body>
</html>
