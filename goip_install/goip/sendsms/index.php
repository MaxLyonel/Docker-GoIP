<?php

require_once('../api_global.php');
require_once('../global.php');
function do_cron($db, $port)
{
	if(!$port) $port=44444;
	$flag=1;
	if(empty($rs[0])){
		$flag=0;
		/* 此是最新計劃， 喚醒服務進程*/
		if (($socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP)) <= 0) {
			//echo "socket_create() failed: reason: " . socket_strerror($socket) . "\n";
			//exit;
		}
		if (socket_sendto($socket,"CRON2", 5, 0, "127.0.0.1", $port)===false){
			//echo ("sendto error:". socket_strerror($socket));
		}
		for($i=0;$i<3;$i++){
			$read=array($socket);
			$err=socket_select($read, $write, $except, 5);
			if($err>0){
				if(($n=@socket_recvfrom($socket,$buf,1024,0,$ip,$port))==false){
					//echo("recvform error".socket_strerror($ret)."<br>");
					continue;
				}
				else{
					if($buf=="OK"){
						$flag=1;
						break;
					}
				}
			}
		}//for
	}//最新計劃
}
$prov_name=$data->provider;
$goip_line=$data->goip_line;
$number=$data->number;
$content=$data->content;
$crontime=time()-60;
$total=get_count_from_sms($content);

if(!$content || !$number) {
	echo '{"result":"REJECT","reason":"content or number error"}';
	exit;
}
/*
if(!$prov_name && !$goip_line) {
	echo '{"result":"REJECT","reason":"none_line"}';
	exit;
}
*/
if($goip_line){
	$query=$db->query("select id from goip where name='$goip_line' and gsm_status='LOGIN'");
	$rs=$db->fetch_array($query);
	if(!$rs[0]) {
		echo '{"result":"REJECT","reason":"none_line"}';
		exit;
	}
	$goip_id=$rs[0];
}
else if($prov_name){
	$query=$db->query("select * from prov where prov='$prov_name'");
	$rs=$db->fetch_array($query);
	if(!$rs[0]) {
		echo '{"result":"REJECT","reason":"none_provider"}';
		exit;
	}
	$prov_id=$rs[0];
	$query=$db->query("select id from goip where provider='$prov_id' and gsm_status='LOGIN'");
	$rs=$db->fetch_array($query);
	if(!$rs[0]) {
		echo '{"result":"REJECT","reason":"none_line"}';
		exit;
	}
}
else {
	$query=$db->query("select * from goip where gsm_status='LOGIN' limit 1");
	$rs=$db->fetch_array($query);
	if(!$rs[0]) {
		echo '{"result":"REJECT","reason":"none_line"}';
		exit;
	}
}



//$db->query("INSERT INTO message (type,msg,userid,crontime,tel,prov,goipid,total) VALUES (4, '$content',$userid, $crontime,'$number','$prov_id','$goip_id','$total')");
$db->query("INSERT INTO message (type,msg,userid,tel,prov,goipid,total) VALUES (100, '$content','$userid','$number','$prov_id','$goip_id','$total')");

//echo ("INSERT INTO message (type,msg,userid,crontime,tel,prov,goipid,total) VALUES (4, '$conent',$userid, now(),'$number','$prov_id','$goip_id','$total')");
$sendsiddb=$db->fetch_array($db->query("SELECT LAST_INSERT_ID()"));
$mobiles=explode(",",$number);
$mobiles=array_unique($mobiles);
	$i=0;
	$sqlv="insert into sends (messageid,userid,telnum,provider,total) values";
	foreach($mobiles as $tel_num){
		$sql.="($sendsiddb[0],'$userid','".$tel_num."','$prov_id','$total'),";
			$i++;
			if($i%2000==0){
				$sql[strlen($sql)-1]="";
				$db->query($sqlv.$sql);
				$sql="";
			}
	}
	if($sql){
		$sql[strlen($sql)-1]="";
		$db->query($sqlv.$sql);
	}


do_cron($db, $goipcronport);
echo '{"result":"ACCEPT","taskID":"'.$sendsiddb[0].'"}';
?>
