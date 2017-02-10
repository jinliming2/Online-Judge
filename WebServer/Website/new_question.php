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
 * Date: 2017/2/9
 * Time: 18:12
 */
require_once __DIR__.'/../Workerman/Autoloader.php';
require __DIR__.'/../Template/constant.php';
use Database\Question;
use MongoDB\BSON\ObjectID;

if(empty($_SESSION['user']->su) || !$_SESSION['user']->su) {
    header('Location: /', true, 301);
    die;
}

if(IS_POST) {
    if(!empty($_GET['id'])) {
        Question::getInstance()->modify(new ObjectID($_GET['id']), $_POST);
    } else {
        $title = $_POST['title'];
        $description = $_POST['description'];
        $adder = $_POST['adder'];
        unset($_POST['title']);
        unset($_POST['description']);
        unset($_POST['adder']);
        Question::getInstance()->add($title, $description, $adder, $_POST);
    }
    echo <<<EOF
<!DOCTYPE html>
<html lang="zh-cmn-Hans">
<head>
    <?php include '../Template/head.html'; ?>
    <script>
        if(parent && parent == window) {
            location.href = '/';
        }
        parent.location.reload(true);
        parent.closePopWindow && parent.closePopWindow();
    </script>
    <title>Success - Add New Question - Online Judge</title>
</head>
<body>操作成功！</body>
</html>
EOF;
    exit;
}

$adder = $_SESSION['user']->name;
if(!empty($_GET['id'])) {
    $question = Question::getInstance()->getOne(new ObjectID($_GET['id']));
    $title = $question->title;
    $description = $question->description;
    $adder = $question->adder;
    $add_time = $question->add_time;
    $tags = $question->tags;
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
    <link rel="stylesheet" href="/c/new_question.css">
    <title>Add New Question - Online Judge</title>
</head>
<body>
<form method="post">
    <div class="line">
        <label for="title">标题</label>
        <input id="title" type="text" name="title" required autofocus placeholder="标题" autocomplete="off"<?= isset($title) ? ' value="'.$title.'"' : '' ?>>
    </div>
    <div class="line">
        <label for="description">详细描述</label>
        <textarea id="description" name="description" required placeholder="详细描述（允许使用除了script之外的任意html标签！，比如使用<br>换行，使用<style>指定样式之类。）"><?= isset($description) ? $description : '' ?></textarea>
    </div>
    <div class="line">
        <label for="adder">添加者</label>
        <input id="adder" type="text" name="adder" readonly placeholder="添加者" value="<?= $adder ?>">
    </div>
    <div id="tags">
        <label>标签</label>
        <button id="add_tag" type="button">添加</button>
        <?php
        if(isset($tags)) {
            foreach($tags as $tag) {
                ?>
        <div class="lines">
            <input type="text" name="tags[]" required placeholder="标签" value="<?= $tag ?>"><button type="button">删除</button>
        </div>
                <?php
            }
        }
        ?>
    </div>
    <?php
    if(isset($add_time)) {
        ?>
    <div class="line">
        <label for="add_time">添加时间</label>
        <input id="add_time" type="text" readonly placeholder="添加时间" value="<?= date('Y-m-d H:i:s', $add_time / 1000) ?>">
    </div>
        <?php
    }
    ?>
    <div class="line">
        <button id="submit" type="submit">提交</button>
    </div>
</form>
<?php include '../Template/footer.html'; ?>
<script src="/j/new_question.js"></script>
</body>
</html>


