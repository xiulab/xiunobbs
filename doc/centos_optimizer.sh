#!/bin/bash
SERVICES="abrt-ccpp abrt-oops abrtd acpid atd auditd avahi-daemon autofs avahi-daemon bluetooth certmonger cups cpuspeed dnsmasq firstboot hidd ip6tables kudzu kdump lvm2-monitor matahari-broker matahari-host matahari-network matahari-service matahari-sysconfig mdmonitor mcstrans mdmonitor microcode_ctl  NetworkManager netconsole netfs nfslock oddjobd pcscd portmap portreserve postfix psacct rpcgssd quota_nld rdisc restorecond rpcidmapd xfs sendmail saslauthd smartd sssd wdaemon wpa_supplicant ypbind  yum-updatesd"
for service in $SERVICES
do
        chkconfig $service off
        service $service stop
done

platform=`uname -i`  
if [ $platform != "x86_64" ];then  
        echo "this script is only for 64bit Operating System !" 
        exit 1  
fi

echo "the platform is ok" 
version=`lsb_release -r |awk '{print substr($2,1,1)}'`  
if [ $version != 6 ];then  
echo "this script is only for CentOS 6 !" 
exit 1  
fi  
cat << EOF  
+---------------------------------------+  
|   your system is CentOS 6 x86_64      |  
|      start optimizing.......          |  
+---------------------------------------  
EOF

#make the 163.com as the default yum repo  
mv /etc/yum.repos.d/CentOS-Base.repo /etc/yum.repos.d/CentOS-Base.repo.backup  
wget http://mirrors.163.com/.help/CentOS6-Base-163.repo -O /etc/yum.repos.d/CentOS-Base.repo  

#add the third-party repo  
#add the epel  
rpm -Uvh http://download.Fedora.RedHat.com/pub/epel/6/x86_64/epel-release-6-5.noarch.rpm  
rpm --import /etc/pki/rpm-gpg/RPM-GPG-KEY-EPEL-6  

#add the rpmforge  
rpm -Uvh http://packages.sw.be/rpmforge-release/rpmforge-release-0.5.2-2.el6.rf.x86_64.rpm  
rpm --import /etc/pki/rpm-gpg/RPM-GPG-KEY-rpmforge-dag  

#update the system and set the ntp  
yum clean all  
yum -y update glibc\*  
yum -y update yum\* rpm\* python\*  
yum -y update  
yum -y install ntp  
echo "* 4 * * * /usr/sbin/ntpdate 210.72.145.44 > /dev/null 2>&1" >> /var/spool/cron/root  
service crond restart  

#set the file limit  
echo "ulimit -SHn 102400" >> /etc/rc.local  
cat >> /etc/security/limits.conf << EOF  
*           soft   nofile       65535  
*           hard   nofile       65535  
EOF  

#set the control-alt-delete to guard against the miSUSE  
sed -i 's#exec /sbin/shutdown -r now#\#exec /sbin/shutdown -r now#' /etc/init/control-alt-delete.conf  

#disable selinux  
sed -i 's/SELINUX=enforcing/SELINUX=disabled/' /etc/selinux/config  

#set ssh  
sed -i 's/^GSSAPIAuthentication yes$/GSSAPIAuthentication no/' /etc/ssh/sshd_config  
sed -i 's/#UseDNS yes/UseDNS no/' /etc/ssh/sshd_config  
service sshd restart  

#tune kernel parametres  
cat >> /etc/sysctl.conf << EOF  
net.ipv4.tcp_fin_timeout = 1  
net.ipv4.tcp_keepalive_time = 1200  
net.ipv4.tcp_mem = 94500000 915000000 927000000  
net.ipv4.tcp_tw_reuse = 1  
net.ipv4.tcp_timestamps = 0  
net.ipv4.tcp_synack_retries = 1  
net.ipv4.tcp_syn_retries = 1  
net.ipv4.tcp_tw_recycle = 1  
net.core.rmem_max = 16777216  
net.core.wmem_max = 16777216  
net.core.netdev_max_backlog = 262144  
net.core.somaxconn = 262144  
net.ipv4.tcp_max_orphans = 3276800  
net.ipv4.tcp_max_syn_backlog = 262144  
net.core.wmem_default = 8388608  
net.core.rmem_default = 8388608  
EOF  
/sbin/sysctl -p  

#define the backspace button can erase the last character typed  
echo 'stty erase ^H' >> /etc/profile  
echo "syntax on" >> /root/.vimrc  


#disable the ipv6  
cat > /etc/modprobe.d/ipv6.conf << EOFI  
alias net-pf-10 off  
options ipv6 disable=1  
EOFI
echo "NETWORKING_IPV6=off" >> /etc/sysconfig/network  
cat << EOF  
+-------------------------------------------------+  
|               optimizer is done                 |  
+-------------------------------------------------+  
EOF

# ÉèÖÃ runlevel
sed -i "s/id:5/id:3/g" /etc/inittab
init 3