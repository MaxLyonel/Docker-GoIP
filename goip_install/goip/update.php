<?php
	define("OK", true);
	require_once("global.php");
	/* 1.02 */
	@$db->updatequery("ALTER TABLE `message` ADD `stoptime` INT UNSIGNED NULL DEFAULT '0'");
	@$db->updatequery("ALTER TABLE `message` ADD `tel` VARCHAR( 30 ) NULL ");
	@$db->updatequery("ALTER TABLE `message` CHANGE  `tel`  `tel` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL");
	@$db->updatequery("ALTER TABLE `message` ADD `prov` VARCHAR( 30 ) NULL ");
	@$db->updatequery("ALTER TABLE `message` ADD `uid` VARCHAR( 30 ) NULL ");
	@$db->updatequery("ALTER TABLE `message` ADD `msgid` INT UNSIGNED NULL DEFAULT '0'");
	/* 1.03 */
	@$db->updatequery("ALTER TABLE `receiver` ADD `name_l` VARCHAR( 30 ) NULL ");
	@$db->updatequery("ALTER TABLE `receiver` ADD `ename_f` VARCHAR( 30 ) NULL ");
	@$db->updatequery("ALTER TABLE `receiver` ADD `ename_l` VARCHAR( 30 ) NULL ");
	@$db->updatequery("ALTER TABLE `receiver` ADD `gender` varchar(1) NOT NULL, ");
	@$db->updatequery("ALTER TABLE `receiver` ADD `hometel` varchar(20) NOT NULL, ");
	@$db->updatequery("ALTER TABLE `receiver` ADD `officetel` varchar(20) NOT NULL, ");
	@$db->updatequery("ALTER TABLE `receiver` ADD `dead` int(1) NOT NULL, ");
	@$db->updatequery("ALTER TABLE `receiver` ADD `reject` int(1) NOT NULL, ");
	/* 1.04 */
	@$db->updatequery("CREATE TABLE IF NOT EXISTS `receive` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `srcnum` varchar(30) NOT NULL default '',
  `provid` int(10) unsigned NOT NULL default '0',
  `msg` text NOT NULL,
  `time` datetime NOT NULL default '0000-00-00 00:00:00',
  `goipid` int(11) NOT NULL default '0',
  `goipname` varchar(30) NOT NULL default '',
  `srcid` int(11) NOT NULL default '0',
  `srcname` varchar(30) NOT NULL default '',
  `srclevel` int(1) NOT NULL default '0',
  `status` int(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");
	@$db->updatequery("CREATE TABLE IF NOT EXISTS `record` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `goipid` int(10) unsigned NOT NULL default '0',
  `dir` int(1) NOT NULL default '0',
  `num` varchar(64) NOT NULL default '',
  `time` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  KEY `goipid` (`goipid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");

	/*1.05 201008*/
	@$db->updatequery("ALTER TABLE `goip` ADD `num` VARCHAR( 30 ) NULL ");

	/*1.06 201010*/
	@$db->updatequery("CREATE TABLE IF NOT EXISTS `USSD` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `TERMID` varchar(64) NOT NULL default '',
  `USSD_MSG` varchar(255) NOT NULL default '',
  `USSD_RETURN` varchar(255) NOT NULL default '',
  `ERROR_MSG` varchar(64) NOT NULL default '',
  `INSERTTIME` timestamp NOT NULL default '0000-00-00 00:00:00' on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");


	/*1.09.3 */
	@$db->updatequery("ALTER TABLE `record` ADD `expiry` int(11) default '-1'");

	/*1.10*/
	@$db->updatequery("ALTER TABLE `message` ADD `goipid` int(11) default '0' AFTER `prov`");

	/*1.10.5*/
	@$db->updatequery("ALTER TABLE `goip` ADD `signal` int(11) default NULL");
	@$db->updatequery("ALTER TABLE `goip` ADD `gsm_status` VARCHAR(30) not NULL");
	@$db->updatequery("ALTER TABLE `goip` ADD `voip_status` VARCHAR(30) not NULL");

	/*1.11.1*/
	@$db->updatequery("ALTER TABLE `sends` ADD `error_no` int(11) default NULL");

	/*1.12 201206 amy*/
	@$db->updatequery("CREATE TABLE IF NOT EXISTS `auto` (
  `auto_reply` tinyint(1) default '0',
  `reply_num_except` text NOT NULL,
  `reply_msg` text NOT NULL,
  `auto_send` tinyint(1) default '0',
  `auto_send_num` varchar(64) NOT NULL default '',
  `auto_send_msg` text NOT NULL,
  `auto_send_timeout` int(11) NOT NULL default '0',
  `all_send_num` varchar(64) NOT NULL default '',
  `all_send_msg` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
	$rs=@$db->fetch_array(@$db->updatequery("select auto_reply from auto"));
	if(!$rs[0])
	{
	@$db->updatequery("INSERT INTO `auto` (`auto_reply`, `reply_num_except`, `reply_msg`, `auto_send`, `auto_send_num`, `auto_send_msg`, `auto_send_timeout`, `all_send_num`, `all_send_msg`) VALUES(0, '', '', 0, '', '', 15, '', '');");
}
	/*1.12.1 */
	@$db->updatequery("ALTER TABLE `receiver` ADD `upload_time` VARCHAR(20) not NULL");

	/*1.12.1*/
	@$db->updatequery("ALTER TABLE `goip` ADD `voip_state` VARCHAR(30) not NULL");

	/*1.12.4*/
	@$db->updatequery("ALTER TABLE `sends` ADD `msg` text NOT NULL");


	/*1.13 201207*/
	@$db->updatequery("CREATE TABLE IF NOT EXISTS `auto_ussd` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(32) character set utf8 NOT NULL,
  `type` int(11) NOT NULL,
  `auto_sms_num` varchar(32) NOT NULL,
  `auto_sms_msg` varchar(320) character set utf8 NOT NULL,
  `auto_ussd` varchar(160) character set utf8 NOT NULL,
  `prov_id` int(11) NOT NULL default '0',
  `goip_id` int(11) NOT NULL default '0',
  `crontime` int(11) NOT NULL,
  `bal_sms_r` varchar(160) character set utf8 NOT NULL,
  `bal_ussd_r` varchar(160) character set utf8 NOT NULL,
  `bal_limit` int(11) NOT NULL,
  `recharge_ussd` varchar(160) character set utf8 NOT NULL,
  `last_time` timestamp NOT NULL default '0000-00-00 00:00:00',
  `next_time` int(10) unsigned NOT NULL default '0',
  `send_sms` varchar(32) NOT NULL,
  `send_email` varchar(32) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `prov_id` (`prov_id`,`goip_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;");

	@$db->updatequery("CREATE TABLE IF NOT EXISTS `recharge_card` (
  `id` int(11) NOT NULL auto_increment,
  `card` varchar(64) NOT NULL,
  `prov_id` int(11) NOT NULL,
  `used` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `prov_id` (`prov_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;");

	@$db->updatequery("ALTER TABLE `USSD` ADD `type` int(11) NOT NULL default '0'");
	@$db->updatequery("ALTER TABLE `USSD` ADD `card` varchar(64) NOT NULL");
	@$db->updatequery("ALTER TABLE `USSD` ADD `recharge_ok` tinyint(1) NOT NULL");
	@$db->updatequery("ALTER TABLE `goip` ADD `bal` int(11) NOT NULL");

	/* v1.13.1 */
	@$db->updatequery("ALTER TABLE  `user` CHANGE  `msg1`  `msg1` VARCHAR( 320 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL");
	@$db->updatequery("ALTER TABLE  `user` CHANGE  `msg2`  `msg2` VARCHAR( 320 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL");
	@$db->updatequery("ALTER TABLE  `user` CHANGE  `msg3`  `msg3` VARCHAR( 320 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL");
	@$db->updatequery("ALTER TABLE  `user` CHANGE  `msg4`  `msg4` VARCHAR( 320 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL");
	@$db->updatequery("ALTER TABLE  `user` CHANGE  `msg5`  `msg5` VARCHAR( 320 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL");
	@$db->updatequery("ALTER TABLE  `user` CHANGE  `msg6`  `msg6` VARCHAR( 320 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL");
	@$db->updatequery("ALTER TABLE  `user` CHANGE  `msg7`  `msg7` VARCHAR( 320 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL");
	@$db->updatequery("ALTER TABLE  `user` CHANGE  `msg8`  `msg8` VARCHAR( 320 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL");
	@$db->updatequery("ALTER TABLE  `user` CHANGE  `msg9`  `msg9` VARCHAR( 320 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL");
	@$db->updatequery("ALTER TABLE  `user` CHANGE  `msg10`  `msg10` VARCHAR( 320 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL");
	/* v1.13.2*/
	@$db->updatequery("ALTER TABLE auto_ussd add `recharge_type` int(1) NOT NULL default '0'");
	@$db->updatequery("ALTER TABLE auto_ussd add `recharge_ussd1` varchar(160) NOT NULL");
	@$db->updatequery("ALTER TABLE auto_ussd add `recharge_ussd1_goip` int(11) NOT NULL");

	/* v1.14 */

	$rs=@$db->fetch_array(@$db->updatequery("select version from system"));
	if(!$rs || $rs[0] < 114){ //below version 1.14 must add version
		@$db->updatequery("ALTER TABLE  `message` ADD INDEX (  `crontime` )");
		@$db->updatequery("ALTER TABLE  `message` ADD INDEX (  `type` )");
		@$db->updatequery("ALTER TABLE  `message` ADD INDEX (  `over` )");
		@$db->updatequery("ALTER TABLE  `message` ADD INDEX (  `prov` )");
		@$db->updatequery("ALTER TABLE  `message` ADD INDEX (  `time` )");
		@$db->updatequery("ALTER TABLE  `sends` ADD INDEX (  `over` )");
		@$db->updatequery("ALTER TABLE  `sends` ADD INDEX (  `messageid` )");
		@$db->updatequery("ALTER TABLE  `sends` ADD INDEX (  `goipid` )");
		@$db->updatequery("ALTER TABLE  `sends` ADD INDEX (  `recvid` )");

		@$db->updatequery("ALTER TABLE `system` ADD `version` int(11) NOT NULL default '0'");
		@$db->updatequery("update `system` set `version`='114'");
		@$db->updatequery("update auto_ussd set crontime=crontime*60"); //crontime set from hour to minute
	}
	@$db->updatequery("ALTER TABLE goip add `CELLINFO` varchar(160) NOT NULL");
	@$db->updatequery("ALTER TABLE goip add `CGATT` varchar(32) NOT NULL");
	@$db->updatequery("ALTER TABLE goip add `BCCH` varchar(160) NOT NULL");
	@$db->updatequery("ALTER TABLE record add `HANGUP_CAUSE` varchar(32) NOT NULL");
	
	/* 1212*/
	@$db->updatequery("ALTER TABLE auto_ussd add `recharge_ok_r` varchar(64) NOT NULL");
	@$db->updatequery("ALTER TABLE goip add `bal_time` datetime default '0000-00-00 00:00:00'");
	@$db->updatequery("ALTER TABLE goip add `keepalive_time` datetime default '0000-00-00 00:00:00'");
	@$db->updatequery("ALTER TABLE auto_ussd CHANGE  `send_sms` `send_sms` varchar(128) NOT NULL");
	@$db->updatequery("ALTER TABLE recharge_card add `use_time` datetime default '0000-00-00 00:00:00'");
	@$db->updatequery("ALTER TABLE recharge_card add `goipid` int(11) default '0'");
	//@$db->updatequery("ALTER TABLE recharge_card ADD INDEX (  `goipid` )");


	/*121224 1.14.2*/
	@$db->updatequery("ALTER TABLE goip add `gsm_login_time` datetime default '0000-00-00 00:00:00'");
	
	@$db->updatequery("ALTER TABLE goip add `gsm_login_time_t` int(11) NOT NULL default '0'");
	@$db->updatequery("ALTER TABLE goip add `keepalive_time_t` int(11) NOT NULL default '0'");
	@$db->updatequery("ALTER TABLE goip add `remain_time` int(11) NOT NULL default '-1'");

	@$db->updatequery("ALTER TABLE `system` ADD `smtp_user` varchar(64) NOT NULL");	
	@$db->updatequery("ALTER TABLE `system` ADD `smtp_pass` varchar(64) NOT NULL");
	@$db->updatequery("ALTER TABLE `system` ADD `smtp_mail` varchar(64) NOT NULL");
	@$db->updatequery("ALTER TABLE `system` ADD `smtp_server` varchar(64) NOT NULL");
	@$db->updatequery("ALTER TABLE `system` ADD `smtp_port` int(11) NOT NULL default '25'");
	
	@$db->updatequery("ALTER TABLE `system` ADD `email_report_gsm_logout_enable` tinyint(1) NOT NULL default '0'");
	@$db->updatequery("ALTER TABLE `system` ADD `email_report_gsm_logout_time_limit` int(11) NOT NULL default '5'");
	@$db->updatequery("ALTER TABLE `system` ADD `email_report_reg_logout_enable` tinyint(1) NOT NULL default '0'");
	@$db->updatequery("ALTER TABLE `system` ADD `email_report_reg_logout_time_limit` int(11) NOT NULL default '5'");
	@$db->updatequery("ALTER TABLE `system` ADD `email_report_remain_timeout_enable` tinyint(1) NOT NULL default '0'");
	@$db->updatequery("ALTER TABLE `system` ADD `report_mail` varchar(64) NOT NULL");
	
	
	/*1.15*/
	if(!$rs || $rs[0] < 115){ 
		@$db->updatequery("ALTER TABLE  `message` ADD INDEX (  `stoptime` )");
		@$db->updatequery("update `system` set `version`='115'");
	}
	@$db->updatequery("ALTER TABLE `system` ADD `send_page_jump_enable` tinyint(1) NOT NULL default '0'");
	@$db->updatequery("ALTER TABLE `system` ADD `endless_send_enable` tinyint(1) NOT NULL default '0'");
	@$db->updatequery("ALTER TABLE `system` ADD `re_ask_timer` int(11) NOT NULL default '3'");

	@$db->updatequery("ALTER TABLE `goip` ADD `imei` varchar(15)");
	@$db->updatequery("ALTER TABLE `goip` ADD `imsi` varchar(32)");
	@$db->updatequery("ALTER TABLE `goip` ADD `iccid` varchar(32)");  

	/*1.15.1*/
	@$db->updatequery("CREATE TABLE IF NOT EXISTS `imei_db` (
  `id` int(11) NOT NULL auto_increment,
  `imei` varchar(15) NOT NULL,
  `goipid` int(11) NOT NULL,
  `goipname` varchar(64) NOT NULL,
  `used` int(1) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `used` (`used`),
  KEY `goipid` (`goipid`),
  KEY `imei` (`imei`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;");

	/*1.16*/
	@$db->updatequery("ALTER TABLE auto_ussd add `recharge_ok_r2` varchar(64) character set utf8 NOT NULL");
	@$db->updatequery("ALTER TABLE auto_ussd add `bal_ussd_zero_match_char` varchar(160) character set utf8 NOT NULL");
	@$db->updatequery("ALTER TABLE auto_ussd add `bal_sms_zero_match_char` varchar(160) character set utf8 NOT NULL");
	@$db->updatequery("ALTER TABLE auto_ussd add `disable_if_low_bal` tinyint(1) NOT NULL");
	@$db->updatequery("ALTER TABLE  `goip` ADD  `last_call_record_id` INT NOT NULL");
	if(!$rs || $rs[0] < 116){
		@$db->updatequery("update `system` set `version`='116'");
		@$db->updatequery("ALTER TABLE  `goip` CHANGE  `bal`  `bal` INT( 11 ) NULL DEFAULT  '-1'");
		@$db->updatequery("update goip set bal='-1' where bal='0'");
		@$db->updatequery("ALTER TABLE  `goip` ADD INDEX (  `password` )");
		@$db->updatequery("ALTER TABLE  `record` ADD INDEX (  `expiry` )");
	}
	/*1.17*/
	@$db->updatequery("ALTER TABLE  `goip` CHANGE  `bal`  `bal` FLOAT( 11 ) NULL DEFAULT  '-1'");

	if(!$rs || $rs[0] < 117){
		@$db->updatequery("update `system` set `version`='117'");
		@$db->updatequery("ALTER TABLE  `sends` ADD  `received` tinyint(1) NOT NULL");
		@$db->updatequery("ALTER TABLE  `sends` ADD  `sms_no` int(11) NOT NULL default '-1'");
		@$db->updatequery("ALTER TABLE  `sends` ADD INDEX (  `sms_no` )");
		@$db->updatequery("ALTER TABLE  `sends` ADD INDEX (  `received` )");
	}


	/*1.17.1*/
	@$db->updatequery("ALTER TABLE `sends` add `total` int(11) NOT NULL default '0'");
	
	@$db->updatequery("ALTER TABLE  `message` add `total` int(11) NOT NULL default '0'");
	@$db->updatequery("ALTER TABLE  `goip` add `remain_count` int(11) NOT NULL default '-1'");
	@$db->updatequery("ALTER TABLE  `goip` add `remain_count_d` int(11) NOT NULL default '-1'");
	@$db->updatequery("ALTER TABLE  `goip` add `count_limit` int(11) NOT NULL default '-1'");
	@$db->updatequery("ALTER TABLE  `goip` add `count_limit_d` int(11) NOT NULL default '-1'");
	@$db->updatequery("ALTER TABLE  `goip` add `total` int(11) NOT NULL default '0'");

	@$db->updatequery("ALTER TABLE  `system` add `total` int(11) NOT NULL default '0'");
	@$db->updatequery("ALTER TABLE  `system` add `this_day` int(11) NOT NULL default '0'");

	/*1.18*/

	@$db->updatequery("ALTER TABLE `auto_ussd` ADD `group_id` int(11) NOT NULL default '0'");
	if(!$rs || $rs[0] < 118){
		@$db->updatequery("update `system` set `version`='118'");
		@$db->updatequery("ALTER TABLE  `system` ADD  `email_forward_sms_enable` tinyint(1) NOT NULL default 0");
		@$db->updatequery("ALTER TABLE  `goip` ADD  `group_id` int(11) NOT NULL default '0'");
		@$db->updatequery("ALTER TABLE  `goip` ADD INDEX (  `group_id` )");
		@$db->updatequery("ALTER TABLE  `auto_ussd` ADD INDEX (  `group_id` )");
		@$db->updatequery("CREATE TABLE IF NOT EXISTS `goip_group` (
  `id` int(11) NOT NULL auto_increment,
  `group_name` varchar(32) character set utf8 NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`group_name`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;");
	}

	@$db->updatequery("ALTER TABLE `goip` ADD `report_mail` varchar(64) NOT NULL");
	@$db->updatequery("ALTER TABLE `goip` ADD `fwd_mail_enable` tinyint(1) NOT NULL default '0'");
	@$db->updatequery("ALTER TABLE `auto_ussd` ADD `auto_disconnect_after_bal` tinyint(1) NOT NULL default '0'");
	@$db->updatequery("ALTER TABLE  `system` ADD `email_remain_count_enable` tinyint(1) NOT NULL default '0'");
	@$db->updatequery("ALTER TABLE  `system` ADD `email_remain_count_d_enable` tinyint(1) NOT NULL default '0'");


/*1.18.2*/
	@$db->updatequery("ALTER TABLE `auto_ussd` ADD `disable_callout_when_bal` tinyint(1) NOT NULL default '0'");
	@$db->updatequery("ALTER TABLE `auto_ussd` ADD `ussd2` varchar(160) NOT NULL");
	@$db->updatequery("ALTER TABLE `auto_ussd` ADD `ussd2_ok_match` varchar(160) NOT NULL");
	@$db->updatequery("ALTER TABLE `auto_ussd` ADD `ussd22` varchar(160) NOT NULL");
	@$db->updatequery("ALTER TABLE `auto_ussd` ADD `ussd22_ok_match` varchar(160) NOT NULL");
	@$db->updatequery("ALTER TABLE `auto_ussd` ADD `send_mail2` char(32) NOT NULL");
	@$db->updatequery("ALTER TABLE `auto_ussd` ADD `disable_if_ussd2_undone` tinyint(1) NOT NULL default '0'");
	@$db->updatequery("ALTER TABLE `auto_ussd` ADD `recharge_limit` int(11) NOT NULL default '0'");
	@$db->updatequery("ALTER TABLE `auto_ussd` ADD `send_sms2` varchar(32) NOT NULL");

/*1.18.3*/
	@$db->updatequery("ALTER TABLE `auto_ussd` ADD `recharge_sms_num` varchar(32) character set utf8 NOT NULL");
	@$db->updatequery("ALTER TABLE `auto_ussd` ADD `recharge_sms_msg` varchar(160) character set utf8 NOT NULL");
	@$db->updatequery("ALTER TABLE `auto_ussd` ADD `recharge_sms_ok_num` varchar(32) character set utf8 NOT NULL");
	@$db->updatequery("ALTER TABLE `auto_ussd` ADD `auto_ussd_step2` varchar(160) character set utf8 NOT NULL");
	@$db->updatequery("ALTER TABLE `auto_ussd` ADD `auto_ussd_step2_start_r` varchar(160) character set utf8 NOT NULL");
	
/*1.18.4*/
	@$db->updatequery("ALTER TABLE `message` ADD `card_id` int(11) NOT NULL");
	@$db->updatequery("ALTER TABLE `message` ADD `card` varchar(64) NOT NULL");

	@$db->updatequery("ALTER TABLE `goip` ADD `report_http` varchar(64) NOT NULL");
	@$db->updatequery("ALTER TABLE `goip` ADD `fwd_http_enable` BOOL NOT NULL DEFAULT '0'");

	@$db->updatequery("ALTER TABLE `prov` ADD `recharge_ok_r` varchar(64) NOT NULL");

/*1.19.1*/
	@$db->updatequery("ALTER TABLE `goip` ADD `carrier` varchar(32) NOT NULL");

/*1.19.2*/
	@$db->updatequery("ALTER TABLE `prov` ADD `auto_num_ussd` varchar(20) NOT NULL");
	@$db->updatequery("ALTER TABLE `prov` ADD `num_prefix` varchar(20) NOT NULL");
	@$db->updatequery("ALTER TABLE `prov` ADD `num_postfix` varchar(20) NOT NULL");
/*1.19.4*/
	@$db->updatequery("ALTER TABLE `auto_ussd` ADD `sms_report_goip` int(11) NOT NULL");
	@$db->updatequery("ALTER TABLE `auto_ussd` ADD `bal_delay` int(11) NOT NULL default '0'");
	@$db->updatequery("ALTER TABLE  `system` ADD `session_time` int(11) NOT NULL default '24'");
	

/*1.20.1*/
	@$db->updatequery("ALTER TABLE `auto_ussd` ADD `re_step2_enable` tinyint(1) NOT NULL");
	@$db->updatequery("ALTER TABLE `auto_ussd` ADD `re_step2_cmd` varchar(64) NOT NULL");
	@$db->updatequery("ALTER TABLE `auto_ussd` ADD `re_step2_ok_r` varchar(64) character set utf8 NOT NULL");

/*1.20.2*/
	@$db->updatequery("ALTER TABLE `auto_ussd` ADD `auto_reset_remain_enable` tinyint(1) NOT NULL");

	@$db->updatequery("ALTER TABLE `system` ADD `disable_status` tinyint(1) NOT NULL default '0'");

/*1.21.5 add set cell*/
	
/*1.22 
add 3-4 step USSD auto balance
add chaeck receive USSD balance
fixed bug of delete goip group
*/
	
	@$db->updatequery("ALTER TABLE  `auto_ussd` ADD  `auto_ussd_step3` VARCHAR( 160 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
ADD  `auto_ussd_step3_start_r` VARCHAR( 160 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
ADD  `auto_ussd_step4` VARCHAR( 160 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
ADD  `auto_ussd_step4_start_r` VARCHAR( 160 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ;");

	@$db->updatequery("ALTER TABLE  `receive` ADD  `smscnum` VARCHAR( 32 ) NOT NULL ;");

/*1.22.1 add doapi*/

/*1.22.1 changed bal_limit to float*/
	@$db->updatequery("ALTER TABLE  `auto_ussd` CHANGE  `bal_limit`  `bal_limit` FLOAT NOT NULL");

/*1.23 add fixed time and remain recharge*/
	@$db->updatequery("ALTER TABLE  `auto_ussd` ADD `recharge_con_type` int(11) NOT NULL default '0',
ADD  `fixed_time` varchar(10) NOT NULL,
ADD  `remain_limit` int(11) NOT NULL default '0',
ADD  `remain_set` int(11) NOT NULL default '0',
ADD  `fixed_next_time` int(10) unsigned NOT NULL;");
        if(!$rs || $rs[0] < 119){
                @$db->updatequery("update `system` set `version`='119'");
                @$db->updatequery("ALTER TABLE  `auto_ussd` ADD INDEX (  `sms_report_goip` )");
                @$db->updatequery("ALTER TABLE  `auto_ussd` ADD INDEX (  `recharge_con_type` )");
	}

	@$db->updatequery("ALTER TABLE `message` ADD `resend` int(11) NOT NULL default '0'");

/*1.24.1 fixed bug of provider with ' */

	@$db->updatequery("ALTER TABLE `goip` ADD `auto_num_c` int(1) NOT NULL default '0'");

/*1.24.3 fixed bug of mysql when shceduler SMS*/
/*1.24.3 fixed bug of call expiry*/
/*1.24.4 fixed bug of imsi when init and doapi when value=0*/
/*1.24.4 fixed bug of receive SMS to http */
/*1.25 add senttime in receive SMS */
	@$db->updatequery("ALTER TABLE `receive` ADD `senttime` DATETIME NOT NULL");

/*1.25 fixed bug of cron SMS and &$ in resend.php */
/*1.25-t1 modify ussd timeout from 10 to 20s */
/*1.25-t2 modify ussd timeout from 20 to 30s */
/*1.25-t3 modify ussd timeout from 30 to 60s */
/*1.26 change null to LOGOUT*/
/*1.27-t1 fixed bug of EXPIRY*/
/*1.27-t2 fixed bug of cron sms*/
/*1.27 add json api*/
	@$db->updatequery("ALTER TABLE `sends` ADD `sending_line` int(11) NOT NULL default '0'");
	@$db->updatequery("ALTER TABLE `system` ADD `json_send_url` varchar(128) NOT NULL");
	@$db->updatequery("ALTER TABLE `system` ADD `json_recv_url` varchar(128) NOT NULL");

/*1.28 fixed bug of upload */
/*1.28.2 add DIAL api */
/*1.28.3 add CCFC api */
/*1.28.4 add netselect api */
/*1.28.6 add num in json api */
/*1.29 fixed bug of reset time after recharge */
/*1.29.1 fixed bug of reset time after SMS recharge */
/*1.29.2 fixed bug of set_exp_time after SMS recharge */
/*1.29.3 fixed bug of reset time after set_exp_time when SMS recharge */
/*1.30.1 ???????????????????????????????????????????????????????????? */
echo "update done! <a href=''  target=_top>Return</a>";
?>
