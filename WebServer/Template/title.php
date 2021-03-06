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
 * Time: 17:35
 */
require_once __DIR__.'/../Workerman/Autoloader.php';
use Database\User;
use MongoDB\BSON\ObjectID;

?>
<header id="header">
    <div id="header_top" class="flex">
        <div id="header_top_left">Online Judge</div>
        <div id="header_top_right" class="flex">
            <?php
            if(isset($_SESSION['user']->_id)) {
                ?>
            <div id="logout" class="header_button">注销</div>
                <?php
                if(isset($_SESSION['user']->su) && $_SESSION['user']->su) {
                    ?>
            <a class="header_button" href="/manager.php" target="_blank">管理</a>
                    <?php
                }
            } else {
                ?>
            <div id="login" class="header_button">登录</div>
            <div id="register" class="header_button">注册</div>
                <?php
            }
            ?>
        </div>
    </div>
</header>
<?php
if(isset($_SESSION['user']->_id)) {
    $history = User::getInstance()->getHistory(new ObjectID($_SESSION['user']->_id));
    ?>
<div id="information" class="flex">
    <span><?= $_SESSION['user']->name ?><?= $_SESSION['user']->su ? '[管理员]' : '' ?></span>
    <span>已解决 <?= $history->pass ?> 个问题</span>
    <span>解决效率：<?= $history->submit == 0 ? 0 : round($history->pass / $history->submit * 100, 3) ?>%</span>
</div>
    <?php
}
?>
