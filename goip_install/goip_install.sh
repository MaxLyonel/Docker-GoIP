#!/bin/sh
Get_Dist_Name()
{
    if grep -Eqi "CentOS" /etc/issue || grep -Eq "CentOS" /etc/*-release; then
        DISTRO='CentOS'
        PM='yum'
    elif grep -Eqi "Aliyun" /etc/issue || grep -Eq "Aliyun Linux" /etc/*-release; then
        DISTRO='Aliyun'
        PM='yum'
    elif grep -Eqi "Amazon Linux" /etc/issue || grep -Eq "Amazon Linux" /etc/*-release; then
        DISTRO='Amazon'
        PM='yum'
    elif grep -Eqi "Fedora" /etc/issue || grep -Eq "Fedora" /etc/*-release; then
        DISTRO='Fedora'
        PM='yum'
    elif grep -Eqi "Oracle Linux" /etc/issue || grep -Eq "Oracle Linux" /etc/*-release; then
        DISTRO='Oracle'
        PM='yum'
    elif grep -Eqi "Red Hat Enterprise Linux" /etc/issue || grep -Eq "Red Hat Enterprise Linux" /etc/*-release; then
        DISTRO='RHEL'
        PM='yum'
    elif grep -Eqi "Debian" /etc/issue || grep -Eq "Debian" /etc/*-release; then
        DISTRO='Debian'
        PM='apt'
    elif grep -Eqi "Ubuntu" /etc/issue || grep -Eq "Ubuntu" /etc/*-release; then
        DISTRO='Ubuntu'
        PM='apt'
    elif grep -Eqi "Raspbian" /etc/issue || grep -Eq "Raspbian" /etc/*-release; then
        DISTRO='Raspbian'
        PM='apt'
    elif grep -Eqi "Deepin" /etc/issue || grep -Eq "Deepin" /etc/*-release; then
        DISTRO='Deepin'
        PM='apt'
    elif grep -Eqi "Mint" /etc/issue || grep -Eq "Mint" /etc/*-release; then
        DISTRO='Mint'
        PM='apt'
    elif grep -Eqi "Kali" /etc/issue || grep -Eq "Kali" /etc/*-release; then
        DISTRO='Kali'
        PM='apt'
    else
        DISTRO='unknow'
    fi
}

Get_OS_Bit()
{
    if [ `getconf WORD_BIT` = '32' ] && [ `getconf LONG_BIT` = '64' ] ; then
        Is_64bit='y'
    else
        Is_64bit='n'
    fi
}

Install_32bit_Libs()
{
echo "This is a 64bit system. Install 32bit libs."
echo ""
if [ ${PM} = "yum" ]; then
	yum install -y libnsl.i686 libnsl.x86_64 libxcrypt.i686 libxcrypt.x86_64 glibc.i686 glibc.x86_64 zlib.i686 zlib.x86_64 krb5-libs.i686 krb5-libs.x86_64
elif [ ${PM} = "apt" ]; then
	dpkg --add-architecture i386 && apt-get update
	apt-get install -y lib32z1-dev libgssapi-krb5-2:i386
fi
[ $? -eq 0 ] || ( echo "ERROR: Install 32bit libs failed."; exit 1 )
echo ""
}

Check_LAMP()
{
if [ -s /bin/lnmp ]; then
	LAMP_installed_by="script"
else
	LAMP_installed_by="unknown"
fi
}

Detect_Database_Exist()
{
can_import_database="yes"
if ${MYSQL_PATH} -uroot $MY_PRA -e "show databases;" | grep goip > /dev/null
then
    echo -e "\033[31mthe SMS SERVER's DATABASE \"goip\" already exists.\033[0m"
    echo -e "\033[31mAre you sure to override the DATABASE? !!! All data of this DATABASE \"goip\" will be erasured if you do this !!!\033[0m"
    echo "Enter \"yes\" or \"no\":"
	local breakout="0"
	while [ ${breakout} = "0" ]
	do
    	read OVERRIDE
    	case "$OVERRIDE" in
			[Yy][Ee][Ss]) 
				breakout="1" 
				can_import_database="yes"
				;;
			[Nn][Oo])  
				breakout="1" 
				can_import_database="no"
				;;
		 	*)  
				breakout="0"
				echo "Please enter \"yes\" or \"no\"." 
				;;
    	esac
	done
fi
}

Install_When_LAMP_Installed_By_Script()
{
	MYSQL_PATH="/usr/bin/mysql"
	MY_PRA="-pdbltek.com"
	
	# Copy file to /usr/local/goip
	echo "Copying file to /usr/local/goip"
	if ps aux | grep "goipcron" | grep -v "grep" > /dev/null
	then
    	killall goipcron
	fi
	cp -r goip /usr/local/
	chmod -R 777 /usr/local/goip
	
	[ -L /home/www/goip ] || ln -s /usr/local/goip /home/www/goip
	echo "Alias /goip /home/www/goip" >> /usr/local/apache/conf/httpd.conf
	Detect_Database_Exist
	if [ ${can_import_database} = "yes" ]; then
		${MYSQL_PATH} -uroot ${MY_PRA} < goip/goipinit.sql
		if [ $? -eq 0 ]; then
			echo "Import database successfully."
		else
			echo "ERROR: Import database failed."
			exit 1
		fi
	else
		echo "Exit"
		exit 0
	fi
	[ -d /var/lib/mysql ] || mkdir -p /var/lib/mysql
	[ ! -e /var/lib/mysql/mysql.sock ] && ln -s /tmp/mysql.sock /var/lib/mysql/mysql.sock
#	/usr/local/apache/bin/apachectl restart
}


Install_When_LAMP_Installed_By_Normal()
{
#set default apache config path and Create symbolic link for mysql socket file if need
if [ ${DISTRO} = "Debian" ] || [ ${DISTRO} = "Ubuntu" ];then
	APACHE_PATH="/etc/apache2/sites-enabled"
	[ ! -L /var/lib/mysql/mysql.sock ] && ln -s /var/run/mysqld/mysqld.sock /var/lib/mysql/mysql.sock 
else
	APACHE_PATH="/etc/httpd/conf.d"
fi

# Read user input
echo "Configure httpd config:"
echo "Enter the httpd config file PATH: (default: $APACHE_PATH)"
echo "Defautl press Enter"

read HTTP_PATH
if [ "${HTTP_PATH}" = "" ] 
then
	HTTP_PATH=$APACHE_PATH
fi

if [ ! -d ${HTTP_PATH} ]
then
	echo "${HTTP_PATH} do not exist"
	exit 1
fi

echo ""
echo ""
echo "Import Goip Databases "
echo "Enter the Mysql root password if the password exist:"
read MYSQL_PW
if [ ${MYSQL_PW}1 = "1" ] 
then
	:
else
	 MY_PRA="-p${MYSQL_PW}"
fi

echo ""
echo ""
echo "Enter your Mysql PATH: (default: /usr/bin/mysql)"
echo "Defautl press Enter"
read MYSQL_PATH
if [ "${MYSQL_PATH}" = ""  ] 
then
	MYSQL_PATH="/usr/bin/mysql"
fi

# import Database
Detect_Database_Exist
if [ ${can_import_database} = "yes" ]; then
    ${MYSQL_PATH} -uroot ${MYSQL_PW} < goip/goipinit.sql
else
    exit
fi
[ $? = "0" ] || ( echo "ERROR: Import database failed."; exit 1 )


# Add "Alias" to apache config
echo '
Alias /goip "/var/www/goip"
<Directory "/var/www/goip">
    Options FollowSymLinks Indexes MultiViews
    AllowOverride None
    Order allow,deny
    Allow from all
</Directory>
' > $HTTP_PATH/goip.conf

# Copy file to /usr/local/goip
echo "Copying file to /usr/local/goip"
if ps aux | grep "goipcron" | grep -v "grep" > /dev/null
then
    killall goipcron
fi
cp -r goip /usr/local/
chmod -R 777 /usr/local/goip

# Create symbolic link for /usr/local/goip
[ ! -L "/var/www/goip" ] && ln -s /usr/local/goip /var/www/goip
}


Install_SMS_Server()
{
if [ ${LAMP_installed_by} = "script" ];then
    Install_When_LAMP_Installed_By_Script
else
    Install_When_LAMP_Installed_By_Normal
fi
}

Add_Run_On_Startup()
{
	echo ""
	echo "Set startup"
    #systemd -h &> /dev/null
    if command -v systemd >/dev/null 2>&1 && [ -f /etc/rc.local ];then
		chmod +x /etc/rc.local
		local="/etc/rc.local"
	elif command -v systemd >/dev/null 2>&1 && [ ! -f /etc/rc.local ];then
        touch /etc/rc.local && echo "#!/bin/sh" > /etc/rc.local && chmod +x /etc/rc.local && local="/etc/rc.local"
        rctmp="/tmp/rc.local.dbltek.service"
        echo "[Unit]" > $rctmp
		echo "Description=/etc/rc.local Compatibility" >> $rctmp
		echo "Documentation=man:systemd-rc-local-generator(8)" >> $rctmp
		echo "ConditionFileIsExecutable=/etc/rc.local" >> $rctmp
		echo "After=network.target" >> $rctmp
		echo "[Service]" >> $rctmp
		echo "Type=forking" >> $rctmp
		echo "ExecStart=/etc/rc.local start" >> $rctmp
		echo "TimeoutSec=0" >> $rctmp
		echo "RemainAfterExit=yes" >> $rctmp
		echo "GuessMainPID=no" >> $rctmp
        [ -d "/lib/systemd/system/" ] && cp $rctmp  /lib/systemd/system/
        [ -d "/etc/systemd/system/" ] && ln -s /lib/systemd/system/rc.local.dbltek.service /etc/systemd/system/rc.local.dbltek.service
    else
		[ -f "/etc/conf.d/local.start" ] && local="/etc/conf.d/local.start"
		[ -f "/etc/rc.d/rc.local" ] && local="/etc/rc.d/rc.local"
	fi
		# delete the line "/usr/local/goip/run_goipcron" if it exist		
		if grep -q "goipcron" $local; then
			sed -i '/\/usr\/local\/goip\/run_goipcron/d' $local
		fi
		# add a line "/usr/local/goip/run_goipcron"
		if grep -q "^exit 0$" $local; then
    		sed -i '/exit\ 0/i\/usr\/local\/goip\/run_goipcron' $local
		else
    		echo "/usr/local/goip/run_goipcron" >> $local
		fi			
}

Restart_Apache()
{
	echo ""
	echo "Restarting Apache/Httpd ..."
	if [ -s /etc/systemd/system/httpd.service ]; then
		systemctl restart httpd.service
		[ $? -eq 0 ] || restart_http_fail="Restart Apache/Httpd failed. you may need to check and restart it manually."
	elif [ -s /etc/init.d/httpd ]; then
		/etc/init.d/httpd restart
		[ $? -eq 0 ] || restart_http_fail="Restart Apache/Httpd failed. you may need to check and restart it manually."
	elif [ -s /etc/init.d/apache2 ]; then
		/etc/init.d/apache2 restart
		[ $? -eq 0 ] || restart_http_fail="Restart Apache/Httpd failed. you may need to check and restart it manually."
	else
		restart_http_fail="Do not know how to restart Apache/Httpd. you may need to check and restart it manually."
	fi
	
}

Add_firewall_rules()
{
	echo " "
	echo "++++++ Add firewall rules: +++++++"
	if command -v firewalld >/dev/null 2>&1; then
		echo "Detected that the system is using \"firewalld\". "
		if test -z "`systemctl | grep firewalld | grep running`"; then
			echo "\"firewalld\" is not running. Skip to add rules."
		else
			default_zone=`firewall-cmd --get-default-zone`
			echo "Execute the following commands:"
			echo "firewall-cmd  --zone=${default_zone} --add-port=80/tcp  --permanent"
			firewall-cmd  --zone=${default_zone} --add-port=80/tcp  --permanent
			echo "firewall-cmd  --zone=${default_zone} --add-port=44444/udp  --permanent"
			firewall-cmd  --zone=${default_zone} --add-port=44444/udp  --permanent
			echo "firewall-cmd --reload"
			firewall-cmd --reload
		fi
	elif command -v ufw >/dev/null 2>&1; then
		echo "Detected that the system is using \"ufw\". "
		echo "Execute the following commands:"
		echo "ufw allow 80/tcp" && ufw allow 80/tcp
		echo "ufw allow 44444/udp" && ufw allow 44444/udp
	elif command -v iptables >/dev/null 2>&1; then
	    echo "Detected that the system is using \"iptables\". "
		echo "Execute the following commands:"
		echo "iptables -I INPUT -p tcp --dport 80 -j ACCEPT" && iptables -I DOCKER-USER -p tcp --dport 80 -j ACCEPT
		echo "iptables -I INPUT -p udp --dport 44444 -j ACCEPT" && iptables -I DOCKER-USER -p udp --dport 44444 -j ACCEPT echo "service iptables save" && service iptables save
	else
		echo "No \"firewalld\" or \"ufw\" or \"iptables\" found, skip."
	fi
	echo ""
	echo "Some of Clould Providers have extra firewall for cloud server in the web control pannel."
	echo "You may need to add rules to open 80/tcp and 44444/udp in the web control pannel as well."
	echo "+++++++++++++++++++++++++++++++++++"
}

Check_killall()
{
	if ! command -v killall > /dev/null 2>&1; then
		echo "Command \"killall\" not found. Install now"
		[ "$PM" = "yum" ] && yum install -y psmisc
		[ "$PM" = "apt" ] && apt install -y psmisc
	fi
}


######################## Start to Run Installation #####################

export PATH=/usr/local/sbin:/usr/local/bin:/sbin:/bin:/usr/sbin:/usr/bin

echo ""
echo ""
echo "Starting GoIP SMS System install "
echo ""
echo ""
systemctl start httpd
/etc/init.d/mysql start

# Check if user is root
if [ $(id -u) != "0" ]; then
    echo "Error: You must be root to run this script."
    exit 1
fi

# Check if the current directory is goip_install
if [ ! -d goip ]
then
        echo "Please run this script in goip_install directory."
        exit 1
fi


Get_Dist_Name
Get_OS_Bit

# Install 32bit libs if it is not x86_64 
[ ${Is_64bit} = "y" ] && Install_32bit_Libs

Check_LAMP
Check_killall
Install_SMS_Server
Add_Run_On_Startup
Add_firewall_rules
Restart_Apache

echo ""
echo "Start SMS Server"
/usr/local/goip/run_goipcron

echo ""
echo "Install done."
echo "You can visit \"http://<IP_Addr>/goip\" now."
echo "Default username and password both are: root"
