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
 * Created by Liming on 2017/2/18.
 */
"use strict";
(() => {
    //输入输出统计
    let input = document.getElementById('input');
    let output = document.getElementById('output');
    let iSpan = document.getElementById('totalIn');
    let oSpan = document.getElementById('totalOut');
    let btnSubmit1 = document.getElementById('submit1');
    let btnSubmit2 = document.getElementById('submit2');
    let ioStatistics = () => {
        let statistics = (value, span) => {
            let lines, total, flag;
            lines = value.split('\n');
            total = 0;
            flag = true;
            for(let line of lines) {
                if(line.trim() != '') {
                    if(flag) {
                        total++;
                        flag = false;
                    }
                } else {
                    flag = true;
                }
            }
            return span.innerHTML = total;
        };
        let i = statistics(input.value, iSpan);
        let o = statistics(output.value, oSpan);
        btnSubmit1.disabled = i != o;
    };
    input.addEventListener('input', ioStatistics);
    output.addEventListener('input', ioStatistics);

    //编辑器初始化
    let editor = ace.edit('editor');
    editor.setTheme("ace/theme/monokai");
    editor.setFontSize('16px');
    editor.setOption("vScrollBarAlwaysVisible", true);
    editor.$blockScrolling = Infinity;

    //代码下载保存功能
    document.getElementById('download').addEventListener('click', () => {
        let a = document.createElement('a');
        let file = new Blob([editor.getValue()], {type: 'plain/text'});
        let event = document.createEvent('MouseEvents');
        event.initEvent('click', false, false);
        a.download = document.title + '.' + constant.language_type[language.value][6];
        a.href = URL.createObjectURL(file);
        a.dispatchEvent(event);
    });

    //切换编程语言修改模板
    let language = document.getElementById('language');
    language.innerHTML = '';
    for(let i = 0; i < constant.language_type.length; ++i) {
        let option = document.createElement('option');
        option.value = i;
        option.innerHTML = constant.language_type[i][0];
        language.appendChild(option);
    }
    let change = () => {
        setCookie('_language', language.value, 2592000000);
        editor.session.setMode(constant.language_type[language.value][1]);
        editor.focus();
    };
    let _language = getCookie('_language');
    if(_language) {
        language.value = _language;
    } else {
        language.value = 0;
        setCookie('_language', 0, 2592000000);
    }
    change();
    editor.setValue(constant.language_type[language.value][2]);
    editor.navigateTo(constant.language_type[language.value][3], constant.language_type[language.value][4]);
    editor.selection.selectTo(constant.language_type[language.value][3], constant.language_type[language.value][5]);
    editor.focus();
    language.addEventListener('change', change);

    //提交
    btnSubmit1.addEventListener('click', () => {
    });
    btnSubmit2.addEventListener('click', () => {
    });

    //加载测试用例
    let list = document.getElementById('list');
    let lastI = 0, lastO = 0, loading = false;
    let loadList = () => {
        if(loading) {
            return;
        }
        loading = messageServer.sendMessage({
            'type': constantIndex(constant['message_type'], 'TestCase'),
            'code': constantIndex(constant['message_code'], 'FetchTestCase'),
            'qid': getQuery('id'),
            'i': lastI,
            'o': lastO
        }, (e) => {
            if(e.hasOwnProperty('message')) {
                lastI = e.message.i;
                lastO = e.message.o;
                for(let tc of e.message.data) {
                    let row = list.insertRow();
                    row.insertCell().innerHTML = tc.i;
                    row.insertCell().innerHTML = tc.o;
                    let btnDelete = document.createElement('button');
                    btnDelete.dataset.il = tc.il;
                    btnDelete.dataset.ol = tc.ol;
                    row.insertCell().appendChild(btnDelete);
                }
            }
            loading = false;
        });
    };
    let startTry = () => {
        setTimeout(() => {
            if(messageServer.ready) {
                loadList();
            } else startTry();
        }, 1e3);
    };
    startTry();
    document.addEventListener('scroll', () => {
        if(document.body.scrollTop + document.body.offsetHeight >= document.body.scrollHeight - 50) {
            loadList();
        }
    });
})();
