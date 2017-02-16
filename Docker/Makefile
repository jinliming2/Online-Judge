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

EasySandbox.so: EasySandbox.o malloc.o
	gcc -shared EasySandbox.o malloc.o -o EasySandbox.so -ldl
	cp EasySandbox.so C/EasySandbox.so
	cp EasySandbox.so Cpp/EasySandbox.so

EasySandbox.o: EasySandbox/EasySandbox.c
	gcc -c -fPIC -std=c99 -g -Wall -D_BSD_SOURCE EasySandbox/EasySandbox.c -o EasySandbox.o

malloc.o: EasySandbox/malloc.c
	gcc -c -fPIC -std=c99 -g -Wall -D_BSD_SOURCE EasySandbox/malloc.c -o malloc.o

clean:
	rm EasySandbox.o malloc.o
