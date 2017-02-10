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
require __DIR__.'/../Template/constant.php';
use Database\Question;

$page = empty($_GET['page']) ? 0 : intval($_GET['page']) - 1;
$pageSize = 100;
$_maxPage = ceil(Question::getInstance()->getCount([]) / $pageSize);
if($_maxPage > 0 && $page >= $_maxPage) {
    header('Location: ?page='.$_maxPage, true, 301);
    die;
}
?>
<!DOCTYPE html>
<html lang="zh-cmn-Hans">
<head>
    <?php include '../Template/head.html'; ?>
    <link rel="stylesheet" href="/c/index.css">
    <title>Home - Online Judge</title>
</head>
<body>
<?php include '../Template/title.php'; ?>
<?php
$questions = Question::getInstance()->getList([], $page * $pageSize, $pageSize);
?>
<div id="questions">
    <?php
    foreach($questions as $question) {
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
<div class="pages">
    <?php
    if($page > 0) {
        ?>
    <a class="button" href="?page=<?= $page ?>">上一页</a>
        <?php
    }
    if($page < $_maxPage - 1) {
        ?>
    <a class="button" href="?page=<?= $page + 2 ?>">下一页</a>
        <?php
    }
    ?>
</div>
<?php include '../Template/footer.html'; ?>
<script src="/j/websocket.js"></script>
<script src="/j/index.js"></script>
</body>
</html>
