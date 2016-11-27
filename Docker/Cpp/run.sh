#!/bin/bash
# Copyright 2017 Liming Jin
#
# Licensed under the Apache License, Version 2.0 (the "License");
# you may not use this file except in compliance with the License.
# You may obtain a copy of the License at
#
#     http://www.apache.org/licenses/LICENSE-2.0
#
# Unless required by applicable law or agreed to in writing, software
# distributed under the License is distributed on an "AS IS" BASIS,
# WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
# See the License for the specific language governing permissions and
# limitations under the License.

timeout 30s g++ /mnt/main.cpp -o ~/jail/main.out -ldl /jail.so
rc=$?
if [ ${rc} -eq 124 ]; then
    echo "Compile Time Out"
elif [ ${rc} -ne 0 ]; then
    echo "Compile Error"
else
    t=1.000
    m=65536
    while getopts "t:m:" arg; do
        case ${arg} in
            t)
                t=$OPTARG;;
            m)
                m=$OPTARG;;
            *);;
        esac
    done
    error=0
    read line
    ulimit -m ${m} -s ${m} -u 1 -t ${t} -n 5
    while [ "$line"x != ""x ]; do
        in="$line"
        read line
        while [ "$line"x != ""x ]; do
            in="$in"$'\n'"$line"
            read line
        done
        echo "$in" > ~/input.txt
        chroot ~/jail timeout ${t}s /main.out < ~/input.txt
        rc=$?
        if [ ${rc} -eq 124 -o ${rc} -eq 137 ]; then
            echo "Time Out"
            error=1
            break
        elif [ ${rc} -ne 0 ]; then
            echo "Runtime Error"
            error=1
            break
        fi
        echo ""
        read line
    done
    if [ ${error} -eq 0 ]; then
        echo "Done"
    fi
fi
