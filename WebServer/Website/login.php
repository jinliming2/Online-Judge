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
 * Time: 15:13
 */
require_once __DIR__.'/../Workerman/Autoloader.php';
require __DIR__.'/../Template/constant.php';
use Database\User;

if(IS_POST) {
    $username = isset($_POST['username']) ? $_POST['username'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $user = User::getInstance()->login($username, $password);
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
        if(isset($user) && $user !== false && (!isset($user->ban) || !$user->ban)) {
            $user->_id = (string)$user->_id;
            $token = $user->token;
            $_SESSION['user'] = $user;
            setcookie('token', $token, null, null, null, IS_HTTPS, false);
            ?>
        parent.location.reload(true);
        parent.closePopWindow && parent.closePopWindow();
            <?php
        }
        ?>
    </script>
    <link rel="stylesheet" href="/c/login.css">
    <title>Login - Online Judge</title>
</head>
<body>
<form method="post">
    <div class="line">
        <label for="username">用户名</label>
        <input id="username" type="text" name="username" required autofocus placeholder="用户名" autocomplete="off"<?= isset($username) ? ' value="'.$username.'"' : '' ?>>
    </div>
    <div class="line">
        <label for="password">密码</label>
        <input id="password" type="password" name="password" required minlength="8" placeholder="密码">
        <div id="tip"><?= isset($user) && $user === false ? '用户名或密码错误！' : (isset($user) && isset($user->ban) && $user->ban ? '该账号已被限制登录，请联系管理员解决！' : '') ?></div>
    </div>
    <div class="line">
        <button id="submit" type="submit">登录</button>
    </div>
</form>
<?php include '../Template/footer.html'; ?>
</body>
</html>
