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
 * Date: 2017/2/6
 * Time: 18:07
 */
require_once __DIR__.'/../Workerman/Autoloader.php';
require __DIR__.'/../Template/constant.php';
use Database\User;
?>
<!DOCTYPE html>
<html lang="zh-cmn-Hans">
<head>
    <?php include '../Template/head.html'; ?>
    <link rel="stylesheet" href="/c/manager.css">
    <title>Online Judge - Manager</title>
</head>
<body>
<?php include '../Template/title.php'; ?>
<nav class="flex">
    <button id="btnUser">用户管理</button>
    <button id="btnQuestion">问题管理</button>
</nav>
<?php
if(!empty($_GET['c']) && $_GET['c'] == 'user') {
    $users = User::getInstance()->getList([], 0, 100);
    ?>
<div id="control">
    <button id="btnDelete">批量删除选中用户</button>
    <input id="txtSearch" placeholder="查找 id/账号">
</div>
<table>
    <tr>
        <th><input type="checkbox"></th>
        <th>id</th>
        <th>账号</th>
        <th>用户名</th>
        <th>通过率</th>
        <th>重置密码</th>
        <th>封禁</th>
    </tr>
    <?php
    foreach($users as $user) {
        if(isset($user->totalPass)) {
            $pass = $user->totalPass;
        } else {
            $pass = 0;
        }
        if(isset($user->totalSubmit)) {
            $submit = $user->totalSubmit;
        } else {
            $submit = 0;
        }
        ?>
    <tr>
        <td><input type="checkbox" data-id="<?= $user->_id ?>"></td>
        <td><?= $user->_id ?></td>
        <td><?= $user->username ?></td>
        <td><?= $user->name ?></td>
        <td><?= $submit == 0 ? 0 : round($pass / $submit * 100, 3) ?>%</td>
        <td><button data-id="<?= $user->_id ?>" data-c="reset">重置</button></td>
        <td><button data-id="<?= $user->_id ?>" data-c="<?= isset($user->ban) && $user->ban ? 'unban' : 'ban' ?>"><?= isset($user->ban) && $user->ban ? '解封' : '封禁' ?></button></td>
    </tr>
        <?php
    }
    ?>
</table>
    <?php
} elseif(!empty($_GET['c']) && $_GET['c'] == 'question') {
    ?>
    <?php
}
?>
<?php include '../Template/footer.html'; ?>
<script src="/j/websocket.js"></script>
<script src="/j/manager.js"></script>
</body>
</html>
