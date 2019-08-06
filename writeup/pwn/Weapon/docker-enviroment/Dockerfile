FROM ubuntu:16.04

RUN sed -i "s/http:\/\/archive.ubuntu.com/http:\/\/mirrors.tuna.tsinghua.edu.cn/g" /etc/apt/sources.list
RUN apt-get update && apt-get -y dist-upgrade
RUN apt-get update && apt-get install -y lib32z1 xinetd build-essential
RUN apt-get install xinetd

RUN useradd -m ctf
WORKDIR /home/ctf
RUN cp -R /lib* /home/ctf
RUN cp -R /usr/lib* /home/ctf

RUN mkdir /home/ctf/dev
RUN mknod /home/ctf/dev/null c 1 3
RUN mknod /home/ctf/dev/zero c 1 5
RUN mknod /home/ctf/dev/random c 1 8
RUN mknod /home/ctf/dev/urandom c 1 9
RUN chmod 666 /home/ctf/dev/*

#bin files
RUN mkdir /home/ctf/bin

RUN cp /bin/bash /home/ctf/bin
RUN cp /bin/sh /home/ctf/bin
RUN cp /usr/bin/timeout /home/ctf/bin
RUN cp /bin/ls /home/ctf/bin
RUN cp /bin/cat /home/ctf/bin

#remove not have
RUN rm -rf /home/ctf/lib/apt /home/ctf/lib/cpp /home/ctf/lib/gnupg /home/ctf/lib/init /home/ctf/lib/lsb /home/ctf/lib/os-release /home/ctf/lib/rsyslog /home/ctf/lib/tc /home/ctf/lib/udev /home/ctf/lib/binfmt.d /home/ctf/lib/dpkg /home/ctf/lib/gold-ld /home/ctf/lib/initramfs-tools /home/ctf/lib/ldscripts /home/ctf/lib/mime /home/ctf/lib/python2.7 /home/ctf/lib/systemd /home/ctf/lib/terminfo /home/ctf/lib/compat-ld /home/ctf/lib/gcc /home/ctf/lib/ifupdown /home/ctf/lib/insserv /home/ctf/lib/locale /home/ctf/lib/modules-load.d /home/ctf/lib/python3 /home/ctf/lib/tar /home/ctf/lib/tmpfiles.d


COPY ./ctf.xinetd /etc/xinetd.d/ctf
COPY ./run.sh /home/ctf
COPY ./pwn /home/ctf
COPY ./flag /home/ctf

RUN chmod +x /home/ctf/*
RUN chown -R root:ctf /home/ctf
RUN chmod -R 750 /home/ctf

RUN touch /home/ctf/*
RUN touch /home/ctf/*/*
RUN touch /home/ctf/*/*/*

RUN echo "Blocked by ctf_xinetd" > /etc/banner_fail
RUN echo 'ctf - nproc 1500' >>/etc/security/limits.conf

CMD exec /bin/bash -c "/etc/init.d/xinetd start; trap : TERM INT; sleep infinity & wait"

EXPOSE 8888 
