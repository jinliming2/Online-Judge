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
 * Date: 2017/2/18
 * Time: 16:14
 */
require_once __DIR__.'/../Workerman/Autoloader.php';
require __DIR__.'/../Template/constant.php';
use Database\Question;
use MongoDB\BSON\ObjectID;

if(empty($_SESSION['user']->su) || !$_SESSION['user']->su) {
    header('Location: /', true, 301);
    die;
}
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
    <script>
        if(parent && parent == window) {
            location.href = '/';
        }
    </script>
    <link rel="stylesheet" href="/c/test_case.css">
    <title>Test Case - Online Judge</title>
</head>
<body>
<header id="title"><?= $question->title ?></header>
<aside><?= $question->description ?></aside>
<hr>
<nav class="flex">
    <label for="RInsert" role="button">添加测试用例</label>
    <label for="RGenerate" role="button">批量生成测试用例</label>
</nav>
<input id="RInsert" type="radio" name="add">
<div>
    <div class="flex">
        <div class="line">
            <label for="input">输入</label>
            <textarea id="input" placeholder="测试用例输入内容，允许多行，但不允许空行。空行代表多个测试用例输入分隔。"><?= isset($description) ? $description : '' ?></textarea>
        </div>
        <div class="line">
            <label for="output">输出</label>
            <textarea id="output" placeholder="测试用例输出内容，允许多行，但不允许空行。空行代表多个测试用例输出分隔。"><?= isset($description) ? $description : '' ?></textarea>
        </div>
    </div>
    <div class="tip">当前 <span id="totalIn">0</span> 个输入用例，<span id="totalOut">0</span> 个输出用例。</div>
    <div class="line">
        <button id="submit1" type="submit">提交</button>
    </div>
</div>
<input id="RGenerate" type="radio" name="add">
<div>
    <div id="setting" class="flex">
        <span>
            <label for="language">代码语言：</label>
            <select id="language"></select>
            <span id="language_tip"><i>请正确选择代码语言，选择错误将可能会导致在线编译出错！</i></span>
        </span>
        <span>
            <button id="download" type="button">代码不会自动保存，请手动保存代码</button>
        </span>
    </div>
    <pre id="editor"></pre>
    <div class="flex">
        <button id="submit2" type="submit">提交</button>
    </div>
</div>
<hr>
<table id="list">
    <tr>
        <th class="table-left">输入</th>
        <th class="table-left">输出</th>
        <th>删除</th>
    </tr>
</table>
<?php include '../Template/footer.html'; ?>
<script src="/j/websocket.js"></script>
<script src="/j/ace/src-min-noconflict/ace.js"></script>
<script src="/j/test_case.js"></script>
</body>
</html>

