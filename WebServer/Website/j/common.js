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
 * Created by Liming on 2016/12/11.
 */
"use strict";
let constant = null;
let ajax = (method, url, data, success, error, complete) => {
    data = data || null;
    let xmlHttp = new XMLHttpRequest();
    xmlHttp.onreadystatechange = function() {
        if(xmlHttp.readyState == 4) {
            if(xmlHttp.status == 200) {
                success && success(xmlHttp);
            } else {
                error && error(xmlHttp);
            }
            complete && complete(xmlHttp);
        }
    };
    xmlHttp.open(method, url, true);
    if(method == 'POST') {
        xmlHttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    }
    xmlHttp.send(data);
};
let setCookie = (key, value, time) => {
    let exp = new Date(Date.now() + time);
    document.cookie = key + '=' + encodeURIComponent(value) + ";expires=" + exp.toGMTString();
};
let getCookie = (key) => {
    let arr, reg = new RegExp('(^| )' + key + '=([^;]*)(;|$)');
    if(arr = document.cookie.match(reg)) {
        return decodeURIComponent(arr[2]);
    } else {
        return null;
    }
};
let constantIndex = (arr, str) => {
    for(let i = 0; i < arr.length; ++i) {
        if(arr[i][0] == str) {
            return i;
        }
    }
};
(() => {
    window.popWindow = (width, height, title, content, iFrame = false) => {
        window.closePopWindow && window.closePopWindow();
        let background = document.createElement('div');
        background.classList.add('pop_window_background');
        background.classList.add('flex');
        let dialog = document.createElement('div');
        dialog.classList.add('dialog');
        dialog.style.width = width + 'px';
        dialog.style.height = height + 'px';
        let title_div = document.createElement('div');
        title_div.classList.add('dialog_title');
        title_div.innerHTML = title;
        let btnClose = document.createElement('span');
        btnClose.classList.add('dialog_btnClose');
        btnClose.innerHTML = '<svg viewBox="0 0 16 16" width="16px" height="16px" xmlns="http://www.w3.org/2000/svg"><path d="M2 2L14 14M2 14L14 2"></path></svg>';
        title_div.appendChild(btnClose);
        let body;
        if (iFrame) {
            body = document.createElement('iframe');
            body.src = content;
        } else {
            body = document.createElement('div');
            body.innerHTML = content;
        }
        body.classList.add('dialog_body');
        body.style.height = height - 43 + 'px';
        dialog.appendChild(title_div);
        dialog.appendChild(body);
        background.appendChild(dialog);
        document.body.appendChild(background);
        window.closePopWindow = () => {
            window.closePopWindow = undefined;
            background.style.backgroundColor = 'rgba(0, 0, 0, 0)';
            dialog.classList.add('dialog_close');
            setTimeout(() => {
                document.body.removeChild(background);
            }, 300);
        };
        btnClose.addEventListener('click', window.closePopWindow);
    };
    window.request = (method, url, data = null, success = null, error = null, complete = null) => {
        let xmlHttp = new XMLHttpRequest();
        xmlHttp.onreadystatechange = () => {
            if(xmlHttp.readyState == 4) {
                if(xmlHttp.status == 200) {
                    success && success(xmlHttp);
                } else {
                    error && error(xmlHttp);
                }
                complete && complete(xmlHttp);
            }
        };
        xmlHttp.open(method, url, true);
        if(method == "POST") {
            xmlHttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        }
        xmlHttp.send(data);
    };
})();
(() => {
    let buttons = [document.getElementById('login'), document.getElementById('register'), document.getElementById('logout')];
    buttons[0] && buttons[0].addEventListener('click', () => {
        popWindow(600, 230, '登录', '/login.php', true);
    });
    buttons[1] && buttons[1].addEventListener('click', () => {
        popWindow(600, 560, '注册', '/register.php', true);
    });
    buttons[2] && buttons[2].addEventListener('click', () => {
        location.replace('/logout.php?url=' + encodeURIComponent(location.href.replace(/^https?:\/\/[^/]+/, '')));
    });
})();
(() => {
    ajax('GET', '/j/constant.json', null, (xmlHttp) => {
        try {
            constant = JSON.parse(xmlHttp.responseText);
        } catch (e) {
            console.error('Load Constant Failed.');
        }
    });
})();
