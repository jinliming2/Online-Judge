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
 * Created by Liming on 2017/2/6.
 */
"use strict";
(() => {
    document.getElementById('btnUser').addEventListener('click', () => {
        location.href = '?c=user';
    });
    document.getElementById('btnQuestion').addEventListener('click', () => {
        location.href = '?c=question';
    });

    //全选/全不选
    let selectAll = document.getElementById('selectAll');
    selectAll && selectAll.addEventListener('change', (e) => {
        document.querySelectorAll('input[type=checkbox]').forEach((checkbox) => {
            checkbox.checked = e.target.checked;
        });
    });

    //批量封禁
    let btnBan = document.getElementById('btnBan');
    btnBan && btnBan.addEventListener('click', () => {
        selectAll.checked = false;
        let checkbox = document.querySelectorAll('input:checked');
        let data = {
            c: 'ban_batch',
            ids: []
        };
        for(let cb of checkbox) {
            cb.dataset.id && (data.ids[data.ids.length] = cb.dataset.id);
        }
        formRequest('POST', null, data);
    });

    //按钮冒泡事件
    let table = document.getElementById('table');
    table && table.addEventListener('click', (e) => {
        if(e.target.dataset.c && e.target.dataset.id) {
            if(e.target.dataset.c == 'reset') {
                let newPassword, _newPassword;
                newPassword = prompt('请设置一个新的密码：');
                if(!newPassword) {
                    return;
                }
                if(newPassword.length < 8) {
                    alert('密码长度必须大于8！');
                    return;
                }
                _newPassword = prompt('请再输入一次以确认输入无误：');
                if(!_newPassword) {
                    return;
                }
                if(newPassword) {
                    if(newPassword != _newPassword) {
                        alert('两次输入不一致！修改失败！', 'e');
                        return;
                    }
                    request('POST', location.href, 'c=reset&id=' + e.target.dataset.id + '&p=' + newPassword, (res) => {
                        let data = JSON.parse(res.responseText);
                        if(data && data.hasOwnProperty('code')) {
                            if(data.code == 0) {
                                alert('修改成功！', 'c');
                            } else {
                                alert('修改失败！', 'e');
                            }
                        } else {
                            alert('未知错误，请重试！', 'e');
                        }
                    }, () => {
                        alert('网络错误，请重试！', 'e');
                    });
                }
            } else if(e.target.dataset.c == 'ban') {
                request('POST', location.href, 'c=ban&id=' + e.target.dataset.id, (res) => {
                    let data = JSON.parse(res.responseText);
                    if(data && data.hasOwnProperty('code')) {
                        if(data.code == 0) {
                            alert('操作成功！', 'c');
                            e.target.dataset.c = 'unban';
                            e.target.innerHTML = '解封';
                        } else {
                            alert('操作失败！', 'e');
                        }
                    } else {
                        alert('未知错误，请重试！', 'e');
                    }
                }, () => {
                    alert('网络错误，请重试！', 'e');
                });
            } else if(e.target.dataset.c == 'unban') {
                request('POST', location.href, 'c=unban&id=' + e.target.dataset.id, (res) => {
                    let data = JSON.parse(res.responseText);
                    if(data && data.hasOwnProperty('code')) {
                        if(data.code == 0) {
                            alert('操作成功！', 'c');
                            e.target.dataset.c = 'ban';
                            e.target.innerHTML = '封禁';
                        } else {
                            alert('操作失败！', 'e');
                        }
                    } else {
                        alert('未知错误，请重试！', 'e');
                    }
                }, () => {
                    alert('网络错误，请重试！', 'e');
                });
            } else if(e.target.dataset.c == 'modify') {
                popWindow(800, 600, '修改问题', '/new_question.php?id=' + e.target.dataset.id, true);
            } else if(e.target.dataset.c == 'delete') {
                formRequest('POST', null, {
                    c: 'delete',
                    id: e.target.dataset.id
                });
            } else if(e.target.dataset.c == 'test_case') {
                popWindow(1024, 768, '测试用例', '/test_case.php?id=' + e.target.dataset.id, true);
            }
        }
    });

    //ID/账号/用户名/标题/添加者查找
    let txtSearch = document.getElementById('txtSearch');
    txtSearch && txtSearch.addEventListener('keypress', (e) => {
        if(e.keyCode == 13) {
            location.href = '?c=' + getQuery('c') + (txtSearch.value.length > 0 ? '&search=' + txtSearch.value : '');
        }
    });

    //新建问题
    let btnInsert = document.getElementById('btnInsert');
    btnInsert && btnInsert.addEventListener('click', () => {
        popWindow(800, 600, '新建问题', '/new_question.php', true);
    });
})();
