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
 * Date: 2016/12/29
 * Time: 15:14
 */
require_once __DIR__.'/../Workerman/Autoloader.php';
require '../Template/constant.php';
use Database\User;

if(IS_POST) {
    $username = isset($_POST['username']) ? $_POST['username'] : false;
    $password = isset($_POST['password']) ? $_POST['password'] : false;
    $name = isset($_POST['name']) ? $_POST['name'] : false;
    if($username && User::getInstance()->usernameExists($username)) {
        $username = false;
        $_username = '用户名已被注册，请尝试使用其他用户名！';
    }
    if($username && $password && $name) {
        $data = [];
        if(!empty($_POST['email'])) {
            $data['email'] = $_POST['email'];
        }
        if(!empty($_POST['intro'])) {
            $data['intro'] = $_POST['intro'];
        }
        $uid = User::getInstance()->register($username, $password, $name, $data);
    }
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
        <?php
        if(isset($uid)) {
            if($uid === false) {
                ?>
        alert('注册失败，请重试！');
                <?php
            } elseif(isset($username) && isset($password)) {
                $user = User::getInstance()->login($username, $password);
                $token = '';
                if($user !== false) {
                    $user->_id = (string)$user->_id;
                    $token = $user->token;
                    $_SESSION['user'] = $user;
                    setcookie('token', $token, null, null, null, IS_HTTPS, false);
                }
                ?>
        parent.location.reload(true);
        parent.closePopWindow && parent.closePopWindow();
                <?php
            }
        }
        ?>
    </script>
    <link rel="stylesheet" href="/c/register.css">
    <title>Register - Home</title>
</head>
<body>
<form method="post">
    <div class="line<?= (isset($username) && $username === false) ? ' error' : '' ?>">
        <label for="username">用户名</label>
        <input id="username" type="text" name="username" required autofocus placeholder="用户名" autocomplete="off"<?= isset($username) ? ' value="'.$username.'"' : '' ?>>
        <div id="tip_username"><?= isset($_username) ? $_username : '' ?></div>
    </div>
    <div class="line<?= (isset($password) && $password === false) ? ' error' : '' ?>">
        <label for="password">密码</label>
        <input id="password" type="password" name="password" required minlength="8" placeholder="密码">
        <div id="tip_password"><?= isset($_password) ? $_password : '' ?></div>
    </div>
    <div class="line">
        <label for="password2">确认密码</label>
        <input id="password2" type="password" required minlength="8" placeholder="确认密码">
        <div id="tip_password2"></div>
    </div>
    <div class="line<?= (isset($name) && $name === false) ? ' error' : '' ?>">
        <label for="name">昵称</label>
        <input id="name" type="text" name="name" required placeholder="昵称"<?= isset($name) ? ' value="'.$name.'"' : '' ?>>
        <div id="tip_name"><?= isset($_name) ? $_name : '' ?></div>
    </div>
    <div class="line">
        <label for="email">电子邮箱</label>
        <input id="email" type="email" name="email" placeholder="电子邮箱"<?= isset($_POST['email']) ? ' value="'.$_POST['email'].'"' : '' ?>>
        <div id="tip_email"></div>
    </div>
    <div class="line">
        <label for="intro">个人介绍</label>
        <textarea id="intro" name="intro" placeholder="个人介绍"><?= isset($_POST['intro']) ? $_POST['intro'] : '' ?></textarea>
    </div>
    <div class="line">
        <button id="submit" type="submit">注册</button>
    </div>
</form>
<?php include '../Template/footer.html'; ?>
<script src="/j/register.js"></script>
</body>
</html>
