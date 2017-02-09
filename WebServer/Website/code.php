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
 * Date: 2017/2/5
 * Time: 21:25
 */
require_once __DIR__.'/../Workerman/Autoloader.php';
require __DIR__.'/../Template/constant.php';
use Database\Result;
use MongoDB\BSON\ObjectID;

if(!isset($_SESSION['user']->_id)) {
    header('403 Forbidden', true, 403);
    die;
}
if(empty($_GET['id'])) {
    header('Location: /', true, 301);
    die;
}
try {
    $rid = new ObjectID($_GET['id']);
} catch (Exception $e) {
    header('403 Forbidden', true, 403);
    die;
}
$result = Result::getInstance()->getUserResult($rid);
if($result === false) {
    header('404 Not Found', true, 404);
    die;
}
if($result->uid != $_SESSION['user']->_id) {
    header('403 Forbidden', true, 403);
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
    <link rel="stylesheet" href="/c/code.css">
    <title>View Code - Online Judge</title>
</head>
<body>
<pre id="editor"><?= htmlspecialchars($result->code) ?></pre>
<div class="flex">
    <button id="download" type="button">下载代码</button>
</div>
<?php include '../Template/footer.html'; ?>
<script src="/j/ace/src-min-noconflict/ace.js"></script>
<script src="/j/code.js"></script>
</body>
</html>

