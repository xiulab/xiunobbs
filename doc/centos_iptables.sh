#!/bin/bash

iptables -F
iptables -X
iptables -t nat -F
iptables -t nat -X

# 开放 sshd
iptables -A INPUT -p tcp --dport 22 -j ACCEPT
iptables -A OUTPUT -p tcp --sport 22 -j ACCEPT
iptables -A OUTPUT -p tcp --dport 22 -j ACCEPT

# DROP 掉所有的包
iptables -P INPUT DROP
iptables -P OUTPUT DROP
iptables -P FORWARD DROP

# 本机放行
iptables -t filter -I INPUT 1 -i lo -j ACCEPT
iptables -t filter -I OUTPUT 1 -o lo -j ACCEPT

# DNS
iptables -A INPUT -p udp --sport 53 -j ACCEPT
iptables -A OUTPUT -p udp --dport 53 -j ACCEPT
iptables -A INPUT -p udp --dport 53 -j ACCEPT
iptables -A OUTPUT -p udp --sport 53 -j ACCEPT

# NFS
iptables -A INPUT -p tcp --dport=111 -j ACCEPT
iptables -A OUTPUT -p tcp --sport=111 -j ACCEPT
iptables -A INPUT -p tcp --dport=2049 -j ACCEPT
iptables -A OUTPUT -p tcp --sport=2049 -j ACCEPT

# SAMBA
iptables -A INPUT -p tcp --dport=139 -j ACCEPT
iptables -A OUTPUT -p tcp --sport=139 -j ACCEPT
iptables -A INPUT -p tcp --dport=445 -j ACCEPT
iptables -A OUTPUT -p tcp --sport=445 -j ACCEPT
iptables -A INPUT -p udp --dport=137 -j ACCEPT
iptables -A OUTPUT -p udp --sport=137 -j ACCEPT
iptables -A INPUT -p udp --dport=138 -j ACCEPT
iptables -A OUTPUT -p udp --sport=138 -j ACCEPT

# 21, yum
iptables -A INPUT -p tcp --dport 21 -j ACCEPT
iptables -A OUTPUT -p tcp --sport 21 -j ACCEPT
iptables -A OUTPUT -p tcp --dport 21 -j ACCEPT


# 80
iptables -A INPUT -p tcp --dport=80 -j ACCEPT
iptables -A OUTPUT -p tcp --sport=80 -j ACCEPT
iptables -A OUTPUT -p tcp --dport=80 -j ACCEPT

# 3306，只允许 192.168.1.2 连接
iptables -A INPUT -p tcp --dport=3306 -s 192.168.0.1/3 -j ACCEPT
iptables -A INPUT -p udp --dport=3306 -s 192.168.0.1/3 -j ACCEPT
iptables -A OUTPUT -p tcp --sport=3306 -j ACCEPT
iptables -A OUTPUT -p tcp --dport=3306 -j ACCEPT
iptables -A OUTPUT -p udp --sport=3306 -j ACCEPT

#allow old connection, deny new connection
iptables -A INPUT -m state --state RELATED,ESTABLISHED -j ACCEPT
iptables -A INPUT -m state --state NEW,INVALID -j DROP


#rsync
iptables -A INPUT -p tcp -s 114.113.229.234 --sport 1024:65535 --dport 873 -j ACCEPT
iptables -A INPUT -p tcp -s 192.168.0.1/3 --sport 1024:65535 --dport 873 -j ACCEPT
iptables -A OUTPUT -p tcp -d 114.113.229.234 --dport 1024:65535 --sport 873 -j ACCEPT
iptables -A OUTPUT -p tcp -d 192.168.0.1/3 --dport 1024:65535 --sport 873 -j ACCEPT

#3690 svn
iptables -A INPUT -p tcp --dport=3690 -j ACCEPT
iptables -A OUTPUT -p tcp --sport=3690 -j ACCEPT

# DHCP
iptables -A INPUT -p udp --sport 67 --dport 68 -j ACCEPT

# ICMP
iptables -A INPUT -p icmp -j ACCEPT
iptables -A OUTPUT -p icmp -j ACCEPT

/etc/init.d/iptables save
chkconfig iptables --level 2345 on

service iptables start