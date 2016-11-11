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

#内核版本检查
version=$(uname -r)
vm=$(echo ${version} | cut -d \. -f 1)
vs=$(echo ${version} | cut -d \. -f 2)
if [ ${vm} -lt 3 ] || [ ${vm} -eq 3 -a ${vs} -lt 10 ]; then
    echo "ERROR: Linux kernel must be 3.10 at minimum!" >&2
    exit 1
fi

#Ubuntu版本
version=$(lsb_release -a)
version=$(echo "${version}" | while read line; do
    item=${line%	*}
    item=${item% *}
    if [ ${item}x = "Distributor"x ]; then
        item=${line##*	}
        if [ ${item}x != "Ubuntu"x ]; then
            echo "ERROR: This Script is for Ubuntu Only!"
            exit 200
        fi
    elif [ ${item}x = "Release:"x ]; then
        echo ${line##*	}
    fi
done)
if [ $? = 200 ]; then
    echo ${version} >&2
    exit 2
fi
if [ ${version}x != "12.04"x -a ${version}x != "14.04"x -a ${version}x != "16.04"x ]; then
    echo -e "Please Select Your System Version:"
    echo -e "\t\t1. Ubuntu 16.04 Xenial [LTS]"
    echo -e "\t\t2. Ubuntu 14.04 Trusty [LTS]"
    echo -e "\t\t3. Ubuntu 12.04 Precise [LTS]"
    echo -n "Your choice[1~4]: "
    read line
    case ${line} in
        1)
            version="16.04";;
        2)
            version="14.04";;
        3)
            version="12.04";;
        *)
            echo "ERROR: Wrong Answer!" >&2
            exit 3;;
    esac
fi

#安装Docker
ret=1
if [ ${version}x = "12.04"x ]; then
    dpkg -l linux-image-generic-lts-trusty
    ret=$?
fi
sudo apt-get update -y
sudo apt-get install -y apt-transport-https ca-certificates
sudo apt-key adv --keyserver hkp://p80.pool.sks-keyservers.net:80 --recv-keys 58118E89F3A912897C070ADBF76221572C52609D
case ${version} in
    16.04)
        echo "deb https://apt.dockerproject.org/repo ubuntu-xenial main" | sudo tee /etc/apt/sources.list.d/docker.list;;
    14.04)
        echo "deb https://apt.dockerproject.org/repo ubuntu-trusty main" | sudo tee /etc/apt/sources.list.d/docker.list;;
    12.04)
        echo "deb https://apt.dockerproject.org/repo ubuntu-precise main" | sudo tee /etc/apt/sources.list.d/docker.list;;
esac
sudo apt-get update -y
if [ ${version}x = "12.04"x ]; then
    sudo apt-get install -y linux-image-generic-lts-trusty
    if [ ${ret} -ne 0 ]; then
        echo "=========================================================="
        echo "============   Need reboot your system now!   ============"
        echo "=====   Please run this script again after reboot!   ====="
        echo "=========================================================="
        echo "Press Enter to continue..."
        read line
        sudo reboot
    fi
else
    sudo apt-get install -y linux-image-extra-$(uname -r) linux-image-extra-virtual
fi
sudo apt-get install -y docker-engine
sudo service docker start
sudo groupadd docker
sudo usermod -aG docker $USER

#安装PHP
sudo apt-get install -y php7.0-cli php-pear php7.0-dev libevent-dev

#安装MongoDB
sudo apt-key adv --keyserver hkp://keyserver.ubuntu.com:80 --recv EA312927
case ${version} in
    16.04)
        echo "deb http://repo.mongodb.org/apt/ubuntu xenial/mongodb-org/3.2 multiverse" | sudo tee /etc/apt/sources.list.d/mongodb-org-3.2.list;;
    14.04)
        echo "deb http://repo.mongodb.org/apt/ubuntu trusty/mongodb-org/3.2 multiverse" | sudo tee /etc/apt/sources.list.d/mongodb-org-3.2.list;;
    12.04)
        echo "deb http://repo.mongodb.org/apt/ubuntu precise/mongodb-org/3.2 multiverse" | sudo tee /etc/apt/sources.list.d/mongodb-org-3.2.list;;
esac
sudo apt-get update -y
sudo apt-get install -y mongodb-org
sudo service mongod start

#安装PHP MongoDB扩展
sudo apt-get install -y pkg-config
sudo pecl install mongodb
echo "extension=mongodb.so" | sudo tee /etc/php/7.0/mods-available/mongodb.ini
sudo ln -s /etc/php/7.0/mods-available/mongodb.ini /etc/php/7.0/cli/conf.d/mongodb.ini


#安装PHP Event扩展
echo "========================================================"
echo "============   WARNING: When ask you to:    ============"
echo "=====   Include libevent OpenSSL support [yes] :   ====="
echo "===============   Please input 'no'!!!   ==============="
echo "========================================================"
echo "Press Enter to continue..."
read line
sudo pecl install event
echo "extension=event.so" | sudo tee /etc/php/7.0/mods-available/event.ini
sudo ln -s /etc/php/7.0/mods-available/event.ini /etc/php/7.0/cli/conf.d/event.ini
