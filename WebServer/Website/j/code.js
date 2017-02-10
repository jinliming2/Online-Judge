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
 * Created by Liming on 2017/2/5.
 */
"use strict";
if(document.getElementById('editor')) {
    //编辑器初始化
    let editor = ace.edit('editor');
    editor.setTheme("ace/theme/monokai");
    editor.setFontSize('16px');
    editor.setOption("vScrollBarAlwaysVisible", true);
    editor.$blockScrolling = Infinity;
    editor.setReadOnly(true);
    editor.session.setMode(constant.language_type[getQuery('language')][1]);

    //代码下载保存功能
    document.getElementById('download').addEventListener('click', () => {
        let a = document.createElement('a');
        let file = new Blob([editor.getValue()], {type: 'plain/text'});
        let event = document.createEvent('MouseEvents');
        event.initEvent('click', false, false);
        a.download = parent.document.title + '.' + constant.language_type[getQuery('language')][6];
        a.href = URL.createObjectURL(file);
        a.dispatchEvent(event);
    });
}
