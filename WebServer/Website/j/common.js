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
const CLOSE_BUTTON = '<svg viewBox="0 0 16 16" width="16px" height="16px" xmlns="http://www.w3.org/2000/svg"><path d="M2 2L14 14M2 14L14 2"></path></svg>';
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
let getQuery = (name) => {
    let result = location.search.match(new RegExp('[\?\&]' + name + '=([^\&]+)', 'i'));
    if(result == null || result.length < 1) {
        return '';
    }
    return result[1];
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
let popWindow = (width, height, title, content, iFrame = false) => {
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
    btnClose.innerHTML = CLOSE_BUTTON;
    title_div.appendChild(btnClose);
    let body;
    if(iFrame) {
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
let request = (method, url, data = null, success = null, error = null, complete = null) => {
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
    if(method == 'POST') {
        xmlHttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    }
    xmlHttp.send(data);
};
let alert = (message, type = 'info', title = 'Message', time = 10000) => {
    //init
    let container = document.getElementById('common-alert-container');
    if(!container) {
        container = document.createElement('div');
        container.id = 'common-alert-container';
        document.body.appendChild(container);
    }

    let closeEvent = (c) => {
        clearTimeout(c.dataset.id);
        c.classList.add('common-alert-message-close');
        setTimeout(() => {
            container.removeChild(c);
        }, 300);
    };
    let m = document.createElement('div');
    m.classList.add('common-alert-message');
    let title_div = document.createElement('div');
    title_div.classList.add('common-alert-title');
    type[0] == 'c' && title_div.classList.add('common-alert-title-congratulation');
    type[0] == 'e' && title_div.classList.add('common-alert-title-error');
    type[0] == 'w' && title_div.classList.add('common-alert-title-warning');
    title_div.innerHTML = title;
    let btnClose = document.createElement('span');
    btnClose.classList.add('common-alert-btnClose');
    btnClose.innerHTML = CLOSE_BUTTON;
    btnClose.addEventListener('click', () => {
        closeEvent(m);
    });
    title_div.appendChild(btnClose);
    let body = document.createElement('div');
    body.classList.add('common-alert-body');
    body.innerHTML = message;
    m.appendChild(title_div);
    m.appendChild(body);
    container.appendChild(m);
    m.dataset.id = setTimeout(() => {
        closeEvent(m);
    }, time);
};
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
