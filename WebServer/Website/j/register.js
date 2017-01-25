/*
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
 * Created by Liming on 2016/12/29.
 */
"use strict";
(() => {
    let username = [document.getElementById('username'), document.getElementById('tip_username')];
    let password = [document.getElementById('password'), document.getElementById('tip_password')];
    let password2 = [document.getElementById('password2'), document.getElementById('tip_password2')];
    let name = [document.getElementById('name'), document.getElementById('tip_name')];
    let email = [document.getElementById('email'), document.getElementById('tip_email')];
    let intro = [document.getElementById('intro')];
    let form = [username, password, password2, name, email, intro];
    username[0].check = () => {
        if(username[0].value == '') {
            return;
        }
        request('GET', '/validate.php?type=username&value=' + username[0].value, null, (e) => {
            let data = JSON.parse(e.responseText);
            if(data.code == 0) {
                username[0].setCustomValidity('');
                username[0].parentNode.classList.remove('error');
                username[1].classList.add('checked');
                username[1].innerHTML = '用户名可以使用！';
            } else {
                username[0].setCustomValidity(data.message);
                username[0].parentNode.classList.add('error');
                username[1].classList.remove('checked');
                username[1].innerHTML = data.message;
            }
        }, () => {
            username[0].setCustomValidity('');
            username[0].parentNode.classList.remove('error');
            username[1].classList.remove('checked');
            username[1].innerHTML = '';
        });
    };
    password[0].check = () => {};
    password2[0].check = () => {
        if(password[0].value !== password2[0].value) {
            password2[0].setCustomValidity('两次输入的密码不一致！');
            password2[0].parentNode.classList.add('error');
            password2[1].innerHTML = '两次输入的密码不一致！';
        } else {
            password2[0].setCustomValidity('');
            password2[0].parentNode.classList.remove('error');
            password2[1].innerHTML = '';
        }
    };
    name[0].check = () => {
    };
    email[0].check = () => {
    };
    intro[0].check = () => {};
    for(let f of form) {
        f[0].onblur = (e) => {
            e.target.check && e.target.check();
        };
    }
    document.getElementById('submit').addEventListener('click', () => {
        for (let f of form) {
            f[0].check();
        }
    });
})();
