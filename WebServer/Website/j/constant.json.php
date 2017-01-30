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
 * Date: 2017/1/30
 * Time: 15:21
 */
$file = __DIR__.'/../../Constant/constant.json';
$last = gmdate('D, d M Y H:i:s T', filemtime($file));
if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
    if($last == $_SERVER['HTTP_IF_MODIFIED_SINCE']) {
        header('Last-Modified: '.$last, true, 304);
        die;
    }
}
header('Content-Type:application/javascript; charset=utf-8', true);
header('Last-Modified: '.$last, true, 200);
?>
let constant = <?php include $file; ?>;
