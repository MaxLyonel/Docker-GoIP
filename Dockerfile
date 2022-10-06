FROM ubuntu:20.04

WORKDIR /root

COPY . .

RUN apt update -y && apt install -y net-tools && apt install -y systemctl && apt install -y iptables

WORKDIR /root/lamp_install

RUN printf "%s\n" yes yes yes yes yes | ./install.sh

WORKDIR /root/goip_install

RUN ./goip_install.sh

EXPOSE 44444/udp

CMD ["systemctl", "start", "httpd"]


