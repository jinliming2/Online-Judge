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

FROM ubuntu
MAINTAINER Liming Jin
RUN apt-get update -y
RUN apt-get install -y g++
COPY run.sh  ~/run.sh
RUN mkdir    ~/jail
RUN mkdir -p ~/jail/usr/bin
RUN mkdir -p ~/jail/usr/lib/x86_64-linux-gnu
RUN mkdir -p ~/jail/lib/x86_64-linux-gnu
RUN mkdir -p ~/jail/lib64
RUN cp /lib64/ld-linux-x86-64.so.2              ~/jail/lib64/
# Easy Sandbox
COPY EasySandbox.so                             /jail.so
RUN cp /jail.so                                 ~/jail/jail.so
# Preload Dynamic Library
RUN cp /lib/x86_64-linux-gnu/libdl.so.2         ~/jail/lib/x86_64-linux-gnu/
# C Languang
RUN cp /lib/x86_64-linux-gnu/libc.so.6          ~/jail/lib/x86_64-linux-gnu/
# C++ Language
RUN cp /usr/lib/x86_64-linux-gnu/libstdc++.so.6 ~/jail/usr/lib/x86_64-linux-gnu
RUN cp /lib/x86_64-linux-gnu/libm.so.6          ~/jail/lib/x86_64-linux-gnu/
RUN cp /lib/x86_64-linux-gnu/libgcc_s.so.1      ~/jail/lib/x86_64-linux-gnu/
# timeout
RUN cp /usr/bin/timeout                         ~/jail/usr/bin/
RUN cp /lib/x86_64-linux-gnu/librt.so.1         ~/jail/lib/x86_64-linux-gnu/
RUN cp /lib/x86_64-linux-gnu/libpthread.so.0    ~/jail/lib/x86_64-linux-gnu/


ENTRYPOINT ["~/run.sh"]
