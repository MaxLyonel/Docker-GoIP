#!/usr/bin/env bash
export PATH=$PATH:/bin:/sbin:/usr/bin:/usr/sbin:/usr/local/bin:/usr/local/sbin:~/bin

# Check if user is root
if [ $(id -u) != "0" ]; then
    echo "Error: You must be root to run this script, please use root to install lnmp"
    exit 1
fi

cur_dir=$(pwd)
Stack=$1
if [ "${Stack}" = "" ]; then
    Stack="lamp"
else
    Stack=$1
fi

LNMP_Ver='1.8'
. lnmp.conf
. include/main.sh
. include/init.sh
. include/mysql.sh
. include/mariadb.sh
. include/php.sh
. include/nginx.sh
. include/apache.sh
. include/end.sh
. include/only.sh
. include/multiplephp.sh	

Get_Dist_Name

if [ "${DISTRO}" = "unknow" ]; then
    Echo_Red "Unable to get Linux distribution name, or do NOT support the current distribution."
    exit 1
fi

if [[ "${Stack}" = "lnmp" || "${Stack}" = "lnmpa" || "${Stack}" = "lamp" ]]; then
    if [ -f /bin/lnmp ]; then
        Echo_Red "You have installed LAMP!"
        echo -e "If you want to reinstall LAMP, please BACKUP your data.\nand run uninstall script: ./uninstall.sh before you install."
        exit 1
    fi
fi

Check_LNMPConf

clear
echo "+------------------------------------------------------------------------+"
echo "|          A tool to auto-compile & install LAMP on Linux                |"
echo "+------------------------------------------------------------------------+"

Init_Install()
{
    Press_Install
    Print_APP_Ver
    Get_Dist_Version
    Print_Sys_Info
    Check_Hosts
    Check_CMPT
    if [ "${CheckMirror}" != "n" ]; then
        Check_Mirror
    fi
    Check_yum_apt
    Add_Swap
#    Set_Timezone
    if [ "$PM" = "yum" ]; then
        CentOS_InstallNTP
        CentOS_RemoveAMP
        CentOS_Dependent
    elif [ "$PM" = "apt" ]; then
        Deb_InstallNTP
        Xen_Hwcap_Setting
        Deb_RemoveAMP
        Deb_Dependent
    fi
    Disable_Selinux
    Check_Download
    Install_Libiconv
    Install_Libmcrypt
    Install_Mhash
    Install_Mcrypt
    Install_Freetype
    Install_Pcre
    Install_Icu4c
    if [ "${SelectMalloc}" = "2" ]; then
        Install_Jemalloc
    elif [ "${SelectMalloc}" = "3" ]; then
        Install_TCMalloc
    fi
    if [ "$PM" = "yum" ]; then
        CentOS_Lib_Opt
    elif [ "$PM" = "apt" ]; then
        Deb_Lib_Opt
    fi
    if [ "${DBSelect}" = "1" ]; then
        Install_MySQL_51
    elif [ "${DBSelect}" = "2" ]; then
        Install_MySQL_55
    elif [ "${DBSelect}" = "3" ]; then
        Install_MySQL_56
    elif [ "${DBSelect}" = "4" ]; then
        Install_MySQL_57
    elif [ "${DBSelect}" = "5" ]; then
        Install_MySQL_80
    elif [ "${DBSelect}" = "6" ]; then
        Install_MariaDB_5
    elif [ "${DBSelect}" = "7" ]; then
        Install_MariaDB_101
    elif [ "${DBSelect}" = "8" ]; then
        Install_MariaDB_102
    elif [ "${DBSelect}" = "9" ]; then
        Install_MariaDB_103
    elif [ "${DBSelect}" = "10" ]; then
        Install_MariaDB_104
    fi
    TempMycnf_Clean
    Clean_DB_Src_Dir
    Check_PHP_Option
}

Install_PHP()
{
    if [ "${PHPSelect}" = "1" ]; then
        Install_PHP_52
    elif [ "${PHPSelect}" = "2" ]; then
        Install_PHP_53
    elif [ "${PHPSelect}" = "3" ]; then
        Install_PHP_54
    elif [ "${PHPSelect}" = "4" ]; then
        Install_PHP_55
    elif [ "${PHPSelect}" = "5" ]; then
        Install_PHP_56
    elif [ "${PHPSelect}" = "6" ]; then
        Install_PHP_7
    elif [ "${PHPSelect}" = "7" ]; then
        Install_PHP_71
    elif [ "${PHPSelect}" = "8" ]; then
        Install_PHP_72
    elif [ "${PHPSelect}" = "9" ]; then
        Install_PHP_73
    elif [ "${PHPSelect}" = "10" ]; then
        Install_PHP_74
    fi
    Clean_PHP_Src_Dir
}

LNMP_Stack()
{
    Init_Install
    Install_PHP
    LNMP_PHP_Opt
    Install_Nginx
    Creat_PHP_Tools
    Add_Iptables_Rules
    Add_LNMP_Startup
    Check_LNMP_Install
}

LNMPA_Stack()
{
    Apache_Selection
    Init_Install
    if [ "${ApacheSelect}" = "1" ]; then
        Install_Apache_22
    else
        Install_Apache_24
    fi
    Install_PHP
    Install_Nginx
    Creat_PHP_Tools
    Add_Iptables_Rules
    Add_LNMPA_Startup
    Check_LNMPA_Install
}

LAMP_Stack()
{
    Apache_Selection #The Selection was forced to choose Apache 2.4.46	
    Warning_Before_Install
    Init_Install
    if [ "${ApacheSelect}" = "1" ]; then
        Install_Apache_22
    else
        Install_Apache_24
    fi
    Install_PHP
    Creat_PHP_Tools
    #Add_Iptables_Ruoles

    # Makesure that the mysql.service works
    if test -n "`ls -l /etc/systemd/system/mysql.service | grep '/dev/null'`"; then
	rm /etc/systemd/system/mysql.service -f
	\cp ${cur_dir}/init.d/mysql.service /etc/systemd/system/mysql.service
    fi

    Add_LAMP_Startup
    Check_LAMP_Install
}

case "${Stack}" in
    lnmp)
        Dispaly_Selection
        LNMP_Stack 2>&1 | tee /root/lnmp-install.log
        ;;
    lnmpa)
        Dispaly_Selection
        LNMPA_Stack 2>&1 | tee /root/lnmp-install.log
        ;;
    lamp)
		#The Selection was forced to choose MySQL 5.5.62 and PHP 5.6.40
		#Default password is "dbltek.com" for user root of MySQL
        Dispaly_Selection  
        LAMP_Stack 2>&1 | tee /root/lamp-install.log
        ;;
    nginx)
        Install_Only_Nginx 2>&1 | tee /root/nginx-install.log
        ;;
    db)
        Install_Only_Database
        ;;
    mphp)
        Install_Multiplephp
        ;;
    *)
        Echo_Red "Usage: $0 {lnmp|lnmpa|lamp}"
        Echo_Red "Usage: $0 {nginx|db|mphp}"
        ;;
esac

exit
