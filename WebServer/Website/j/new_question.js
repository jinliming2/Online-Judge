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
 * Created by Liming on 2017/2/9.
 */
"use strict";
(() => {
    //标签
    let tags = document.getElementById('tags');
    tags.addEventListener('click', (e) => {
        if(e.target.id == 'add_tag') {
            let lines = document.createElement('div');
            lines.classList.add('lines');
            let input = document.createElement('input');
            input.type = 'text';
            input.name = 'tags[]';
            input.required = true;
            input.placeholder = '标签';
            let button = document.createElement('button');
            button.type = 'button';
            button.innerHTML = '删除';
            lines.appendChild(input);
            lines.appendChild(document.createTextNode(''));
            lines.appendChild(button);
            tags.appendChild(lines);
        } else if(e.target.type == 'button') {
            tags.removeChild(e.target.parentElement);
        }
    });
})();
