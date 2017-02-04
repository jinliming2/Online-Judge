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
 * Created by Liming on 2017/1/25.
 */
"use strict";
class Message {
    constructor() {
        this.ready = null;
        this._connected = false;
        this._callbackList = [];
        this._events = {
            'open': [],
            'close': [],
            'error': []
        };
        this._type = {
            'Error': [],
            'Login': [],
            'Logout': [],
            'Judge': [],
            'JudgeResult': []
        };
        this._connection = new WebSocket('ws://' + window.location.hostname + ':8080');
        this._connection.onopen = (e) => {
            this.ready = false;
            this._connected = true;
            for(let event of this._events.open) {
                event.call(this, e);
            }
        };
        this._connection.onclose = (e) => {
            this.ready = false;
            this._connected = false;
            for(let event of this._events.close) {
                event.call(this, e);
            }
        };
        this._connection.onerror = (e) => {
            this.ready = false;
            this._connected = false;
            for(let event of this._events.error) {
                event.call(this, e);
            }
        };
        this._connection.onmessage = (e) => {
            try {
                let data = JSON.parse(e.data);
                if(data.hasOwnProperty('_t')) {
                    let flag = true;
                    for(let callback of this._callbackList) {
                        if(callback[0] == data._t) {
                            callback[1].call(this, data);
                            flag = false;
                            break;
                        }
                    }
                    if(flag && data.hasOwnProperty('code') && data.hasOwnProperty('type')) {
                        for(let type of this._type[constant['message_type'][data.type][0]]) {
                            type.call(this, data);
                        }
                    }
                } else {
                }
            } catch(exception) {
            }
        };
    }

    addEvent(event, callback) {
        this._events[event][this._events[event].length] = callback;
        if(event == 'open' && this._connected) {
            callback.call(this);
        }
    }

    addType(type, callback) {
        this._type[type][this._type[type].length] = callback;
    }

    login(token) {
        if(this.ready === false) {
            this._connection.send(JSON.stringify({
                'type': constantIndex(constant['message_type'], 'Login'),
                'token': token
            }));
        }
    }

    sendMessage(message, callback) {
        if(this.ready) {
            message._t || (message._t = Date.now());
            callback && (this._callbackList[this._callbackList.length] = [message._t, callback]);
            this._connection.send(JSON.stringify(message));
            return true;
        } else {
            alert('通讯服务还没有登录，请尝试刷新页面！', 'e');
            return false;
        }
    }
}

(() => {
    let heartBeat = -1;
    let connect = () => {
        window.messageServer = new Message();
        window.messageServer.addEvent('open', (e) => {
            heartBeat = setInterval(() => {
                window.messageServer.sendMessage({'heart-beat': 'heart-beat'});
            }, 240e3);
            let token = getCookie('token');
            if(token) {
                window.messageServer.login(token);
            }
        });
        window.messageServer.addEvent('close', (e) => {
            clearInterval(heartBeat);
            alert('与服务器的连接中断！', 'w');
            setTimeout(() => {
                connect();
            }, 3e3);
        });
        window.messageServer.addEvent('error', (e) => {
            alert('无法与服务器建立连接！', 'e');
        });
        window.messageServer.addType('Error', (msg) => {
        });
        window.messageServer.addType('Login', (msg) => {
            switch(constant['message_code'][msg.code][0]) {
                case 'Success':
                    window.messageServer.ready = true;
                    alert('登录成功！');
                    break;
                case 'LogonTimeout':
                    window.messageServer.ready = false;
                    alert('由于在其他地方登录，当前Session已失效，请注销并重新登录！', 'e');
                    break;
            }
        });
        window.messageServer.addType('Logout', (msg) => {
            alert('当前用户已注销！将断开服务连接！');
        });
        window.messageServer.addType('Judge', (msg) => {
            if(msg.hasOwnProperty('code')) {
                switch(constant['message_code'][msg.code][0]) {
                    case 'StartJudging':
                        alert('已开始测试代码！', 'i', msg.id);
                        break;
                }
            }
        });
        window.messageServer.addType('JudgeResult', (msg) => {
            if(msg.hasOwnProperty('code')) {
                let _m = '', _t;
                switch(constant['judge_status'][msg.code][0]) {
                    case 'Accepted':
                        _m = '运行完成，答案正确！';
                        _t = 'c';
                        break;
                    case 'CompileError':
                        _m = '编译未通过！请确认编程语言是否选择正确，并检查代码是否存在错误！';
                        _t = 'e';
                        break;
                    case 'CompileTimeout':
                        _m = '编译超时！请联系管理员解决！';
                        _t = 'w';
                        break;
                    case 'WrongAnswer':
                        _m = '运行完成，但是答案貌似不对呢！输出一定要按照要求的格式哦！一点点不同都不行！';
                        _t = 'e';
                        break;
                    case 'RuntimeError':
                        _m = '运行过程中出现了错误！要检查一下是不是有没有捕获的异常，比如数组越界非法访问等！';
                        _t = 'e';
                        break;
                    case 'Timeout':
                        _m = '不小心运行超时了！是不是出现了死循环？再优化优化代码吧，有时暴力破解是行不通的哦！';
                        _t = 'w';
                        break;
                    case 'UnknownError':
                        _m = '未知错误！';
                        _t = 'w';
                        break;
                }
                if(msg.hasOwnProperty('info')) {
                    _m += '<br>' + msg.info.replace(/\n/g, '<br>');
                }
                alert(_m, _t, msg.id);
            }
        });
    };
    connect();
})();
