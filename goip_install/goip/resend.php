<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link href="../style.css" rel="stylesheet" type="text/css">
<title>Resending Messge</title>
</head>
<body>
<?php
session_start();
define("OK", true);
require_once("global.php");
if(!get_magic_quotes_gpc()){
        $_REQUEST[USERNAME]=addslashes($_REQUEST[USERNAME]);
        $_REQUEST[PASSWORD]=addslashes($_REQUEST[PASSWORD]);
}
//print_r($_GET);
//if($_REQUEST[Memo]) $_POST[Memo]=$_REQUEST[Memo];
if(!isset($_SESSION['goip_username'])){
	//echo "SELECT id FROM user WHERE username='".$_REQUEST[USERNAME]."' and password='".md5($_REQUEST[PASSWORD])."'";
	$rs=$db->fetch_array($db->query("SELECT id FROM user WHERE username='".$_REQUEST[USERNAME]."' and password='".md5($_REQUEST[PASSWORD])."'"));
	if(empty($rs[0])){
		//require_once ('login.php');
		exit;
	}
	$userid=$rs[0];
}
else $userid=$_SESSION[goip_userid];
/*
if(!isset($_SESSION['goip_username'])) {
	require_once ('login.php');
	exit;
}
*/
ignore_user_abort(true);
set_time_limit(0);
ob_end_flush();
ini_set("memory_limit", "1024M");
echo str_pad(" ", 256);
if($goipcronport)
	$port=$goipcronport;
else 
	$port=44444;
	
$query=$db->query("SELECT * from prov ");
while($row=$db->fetch_assoc($query)) {
	//echo $row[id]." ".$row[inter]."<br>";
	$row[interlen]=strlen($row[inter]);
	$row[locallen]=strlen($row[local]);
	$prov[$row[id]]=$row;
}
function restart(&$goiprow,$len,$msg)
{
        global $db;
        global $port;
	global $re_ask_timer;
        $query=$db->query("SELECT prov.*,goip.* FROM goip,prov where prov.id=goip.provider and alive=1 and gsm_status='LOGIN' and  goip.id=$goiprow[id]");
        $rs=$db->fetch_array($query);
        //echo "SELECT prov.*,goip.* FROM goip,prov where prov.id=goip.provider and alive=1 and gsm_status!='LOGOUT' and goip.id=$goiprow[id]". "restart: $rs[name] !!!<br>";
        if($rs[0]){
		if($rs['remain_count']==0 || $rs['remain_count_d']==0) {
			echo "GoIP Line($goiprow[name]) remain count is down<br>";
			return;
		}
                $buf="DONE $goiprow[messageid]\n";
                if (@socket_sendto($goiprow[sock],$buf, strlen($buf), 0, "127.0.0.1", $port)===false)
                        echo ("sendto error");

                $goiprow['timer']=3;
                $goiprow['send']="MSG";
                //echo "$sendid $goiprow[id] $goiprow[messageid] <br>";
                $goiprow['time']=time();//??????
                $goiprow[host]=$rs[host];
                $goiprow[port]=$rs[port];
                $buf="START ".$goiprow['messageid']." $goiprow[host] $goiprow[port]\n";
                //echo $buf."<br>"."<br>"."<br>"."<br>"."<br>"."<br>";
                if (@socket_sendto($goiprow[sock],$buf, strlen($buf), 0, "127.0.0.1", $port)===false)
                        echo ("sendto error");
                for($i=0;$i<3;$i++){
                        //echo "check:$i";
                        $read=array($goiprow[sock]);
                        $err=socket_select($read, $write = NULL, $except = NULL, 5);
                        if($err>0){
                                //echo "11213134";
                                if(($n=@socket_recvfrom($goiprow[sock],$buf,1024,0,$ip,$port1))==false){
                                        echo("recvform error".socket_strerror($ret)."<br>");
                                        continue;
                                }
                                else{
                                        //echo "111111111:$buf";
                                        if($buf=="OK"){
                                                $flag=1;
                                                break;
                                        }
                                }
                        }
                }//for
                if($i>=3){
                        echo ("Cannot get response from process named \"goipcron\". please check this process.sending stop!");
			exit;
                //$buf="MSG ".$goiprow['messageid']." $len $msg\n";
                //if (@socket_sendto($socket,$buf, strlen($buf), 0, "127.0.0.1", $port)===false)
                        //echo ("sendto error");
		}
        }
        else {
                echo "$goiprow[name] logout, after 100 seconds ask again.<br>";
                $goiprow[send]="RMSG";
                $goiprow[timer]=$re_ask_timer;
        }
}
	function dolastsend(&$goipsend,$len,$msg)
	{
		//print_r($goipsend);
		global $port;
		$sendid=$goipsend[messageid];
		if($goipsend[send]=="RMSG"){
			if($goipsend[timer] <=1 ){
                                restart($goipsend,$len,$msg);
                                //echo "$goipsend[send] $goipsend[timer]";
				//$goipsend[send]="MSG";
				//$goipsend[timer]=3;
			}
			else return;
		}
		if($goipsend[send]=="HELLO"){
			$buf="HELLO ".$sendid."\n";
						
			//$goipsend[timer]=0;
			
		}
		elseif($goipsend[send]=="PASSWORD"){
			$buf="PASSWORD $sendid $goipsend[password]\n";
				
		}
		elseif($goipsend[send]=="SEND"){
			if($goipsend[tel][msg]) 
				$buf="SMS $sendid $goipsend[telid] $goipsend[password] ".$goipsend[tel][telnum]." ".$goipsend[tel][msg];
			else
				$buf="SEND $sendid $goipsend[telid] ".$goipsend[tel][telnum]."\n";
			echo "$buf ($goipsend[name] $goipsend[prov]) <br>";
		}	
		elseif($goipsend[send]=="MSG"){
			$buf="MSG $sendid $len $msg\n";

		}	
		//echo "<br> buf:$buf <br>";
		if($buf)
			if (@socket_sendto($goipsend[sock],$buf, strlen($buf), 0, "127.0.0.1", $port)===false)
				echo ("sendto error");			
	}
        function checkover($goipdb)
        {
		global $endless_send;
		if(!$endless_send) return false;

                foreach($goipdb as $the0 => $goipsend){
                        if($goipsend[timer]>0){//??????
                                if($goipdb[$the0][send]!="RMSG"){  //????????????????????????????????????????????????GOIP
                                        return false;//?????????
                                }
                        }
                }
                return true;
        }


function startdo($db, $tels, $sendid, $msg, $len, $goipid=0){	
		global $port;
		global $prov;
		global $endless_send;
		global $re_ask_timer;
		$nowtime=date ("Y-m-d H:i:s");
		/*???????????????*/
		//$sendid=$sendsiddb[0];
		
		//$id=0;
		$db->query("update message set `over`=1 where id=$sendid");
		$query=$db->query("SELECT prov.*,goip.* FROM goip,prov where prov.id=goip.provider ORDER BY RAND() LIMIT 1000");
		$socks=array();
		$goipdb=array();
		while($goiprow=$db->fetch_array($query)) {
			$goipname[]=$goiprow[provider];


			/*??????????????????*/			
			if(($goipid && $goiprow[id]==$goipid) || (!$goipid && count($tels[$goiprow[provider]]))){ //?????????????????????????????????????????????
				//echo "sendid $sendid <br>";
				$errortels[$goiprow[provider]]=array();
				if (($socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP)) <= 0) {
					echo "socket_create() failed: reason: " . socket_strerror($socket) . "\n";
					exit;
				}
                                $goiprow['sock']=$socket;
                                $goiprow['time']=time();//??????
				if($goiprow['remain_count']==0 || $goiprow['remain_count_d']==0) {
					echo "GoIP Line($goiprow[name]) remain count is down<br>";
					continue;
				}
                                if($goiprow[alive] != 1 || $goiprow[gsm_status] != 'LOGIN'){
                                        $goiprow['timer']=$re_ask_timer;
                                        $goiprow['send']="RMSG";
                                        $goiprow['messageid']=$sendid+($goiprow[id] << 16)+$goiprow['time']%10000;
                                        $goipdb[]=$goiprow;
                                        $socks[]=$socket;
                                        echo "$goiprow[name] logout, after 100 seconds ask again.<br>";
                                        continue;
                                }
                                $goiprow['timer']=3;
                                $goiprow['send']="MSG";
                                $goiprow['messageid']=$sendid+($goiprow[id] << 16)+$goiprow['time']%10000;
                                $goipdb[]=$goiprow;
                                echo "$sendid $goiprow[id] $goiprow[messageid] <br>";
                                $buf="START ".$goiprow['messageid']." $goiprow[host] $goiprow[port]\n";

				if (@socket_sendto($socket,$buf, strlen($buf), 0, "127.0.0.1", $port)===false)
					echo ("sendto error");
				for($i=0;$i<3;$i++){
					$read=array($socket);
					$err=socket_select($read, $write = NULL, $except = NULL, 5);
					if($err>0){		
						if(($n=@socket_recvfrom($socket,$buf,1024,0,$ip,$port1))==false){
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
				if($i>=3)
					die("Cannot get response from process named \"goipcron\". please check this process.sending stop.");				
				$buf="MSG ".$goiprow['messageid']." $len $msg\n";
				if (@socket_sendto($socket,$buf, strlen($buf), 0, "127.0.0.1", $port)===false)
					echo ("sendto error");
				$socks[]=$socket;
			}
			//print_r($goiprow);
		}
		foreach($tels as $provtmp => $valuetmp){
			foreach($goipdb as $goiptmp){
				if($goiptmp['provider']==$provtmp)
					continue 2;
			}
			$n=count($valuetmp);
			echo "<font color='#FF0000'>Will send {$n} message(s) to receivers of ".$prov[$provtmp]['prov'].", but cannot find any logon GoIP of ".$prov[$provtmp]['prov']." </font><br>";
		}		
		//$read = array($socket);
		$timeout=5;
		for(;;){
			$read=$socks;
			flush();
			if(count($read)==0)
				break;
			$err=socket_select($read, $write = NULL, $except = NULL, $timeout);
			if($err===false)
				echo "select error!";
			elseif($err==0){ //????????????
				$i=0;
				$flag=1;
				$nowtime=time();
				//reset($goipdb);
				//while (list (, $goipsend) = each ($goipdb)) {
				foreach($goipdb as $the0 => $goipsend){
					//$goipsend=$goipdb[$the0];
					$goipdb[$the0]['time']=$nowtime;
					if($goipsend[timer]>0){//??????
						if($goipdb[$the0][send]!="RMSG")  //????????????????????????????????????????????????GOIP
							$flag=0;//?????????
						dolastsend($goipdb[$the0],$len,$msg);
						$goipdb[$the0]['timer']--;
						//echo("<br>$i timer:".$goipsend[timer]."<br>");
						$i++;
					}
					else{ //????????????
						if($goipsend[send]=="OK") //????????????
							continue;
						if($goipsend[send]=="SEND"){
							echo "<font color='#FF0000'>($goipnow[telid] $goipnow[tel] $goipnow[msg])faile</font><br>";
							foreach($goipdb as $the => $goiptmp){ 
								if($goiptmp[send]=="OK" && $goiptmp[provider]==$goipsend[provider]){
									if($goipsend[tel][error] && in_array($goiptmp[id],$goipsend[tel][error]))
										continue;//???????????????
									$goipdb[$the][send]="SEND";
									$goipdb[$the][tel]=$goipsend[tel];
									$goipdb[$the][telid]=$goipsend[telid];
									$goipsend[send]=="OK"; //??????
									dolastsend($goiptmp,$len,$msg);
									$goipdb[$the0][send]="RMSG";//?????????goip???100s?????????
									$goipdb[$the0][timer]=$re_ask_timer;
									$goipdb[$the0][tel]=0;
									$goipdb[$the0][telid]=0;
									break;
								}									
							}
						}
						else{
							/*100s ???????????????*/
							echo "<font color='#FF0000'>cannot get response from goip:  $goipsend[send] ($goipsend[name] $goipsend[prov])</font><br>";
							$goipdb[$the0][send]="RMSG";
							$goipdb[$the0][timer]=$re_ask_timer; 
						}
						if($goipsend[send]=="SEND"){//?????????????????????goip?????????????????????100s???????????????
							if($goipsend[tel][error])
								array_push($errortels[$goipsend[provider]], $goipsend[tel]);//??????????????????
							else 
								array_push($tels[$goipsend[provider]], $goipsend[tel]); //??????
							//array_push($tels[$goipsend[provider]], $goipsend[tel]); //??????
							/*???????????????*/
							$goipdb[$the0][send]="RMSG";
							$goipdb[$the0][timer]=$re_ask_timer;
							$goipdb[$the0][tel]=0;
							$goipdb[$the0][telid]=0;						
						}	
					}	
				}
				if($flag && !$endless_send)
					break; //????????????
			}//????????????
			else{ //??????
			
			  foreach($read as $socket){
				unset($buf);
				//$buf="";
				
				if(($n=@socket_recvfrom($socket,$buf,1024,0,$ip,$port1))==false){
					//echo("recvform error".socket_strerror($ret)."<br>");
					continue;
				}
				echo("len:$n $buf<br>");
				
				//$bufline=explode("\n",$buf);
				//foreach($bufline as $line){
					//$comm=explode(" ",$line);
				  $comm=explode(" ",$buf);
					foreach($goipdb as $the => $goipnow){
						//echo "$key => $val\n";
						if($goipnow[sock]==$socket){
							break;
						}	
					}
					
					if(empty($goipnow)){ //????????????????????????
						continue; 
					}
					if(strncmp($goipnow[messageid],$comm[1], strlen($goipnow[messageid])))//???????????????id
						continue;
					if($comm[0]=="OK"){
						//?????????????????????????????? 
						//echo "inser: ".$goipnow[tel][id]."  $goipnow[tel] ok<br>";
						if(is_numeric($comm[3])) 
							$db->query("update sends set `over`=1,sms_no=$comm[3],goipid=$goipnow[id],time=now() where id='".$goipnow[tel][id]."' and messageid=$sendid");	
						else 
							$db->query("update sends set `over`=1,goipid=$goipnow[id],time=now() where id='".$goipnow[tel][id]."' and messageid=$sendid");	
						/**/
						if($goipnow[send]!="SEND"){//??????????????????????????????
							echo "not send status <br>";
							continue;
						}	
						if($comm[2]==$goipnow[tel][id] ){ //?????????????????????,?????????????????????
							echo "<font color='#00FF00'>SEND: $goipnow[telid] ".$goipnow[tel][telnum]."  ".$goipnow[tel][msg]." ok($goipnow[name] $goipnow[prov])</font><br>";
							$goipdb[$the]['send']="OK";//?????????					
							$goipdb[$the][telid]=0;
							//$goipdb[$the][tel]=0;
							$goipdb[$the][timer]=0;
							if(!check_sms_remain_count($db,$goipnow[id],$goipnow[name],$goipdb[$the][tel][total])) continue;
							if(($goipdb[$the][tel]=array_pop($tels[$goipnow[provider]]))!==NULL){//??????	
								/*????????????????????????id, ??????*/
								$goipdb[$the][telid]=$goipdb[$the][tel][id];
								//$goipdb[$the][telid]=$testid++;
								$goipdb[$the]['send']="SEND";
								$goipdb[$the][timer]=3;
								dolastsend( $goipdb[$the],$len,$msg);
								//$goipdb[$the]['send']="SEND";
								
								//$buf="SEND ".$goipdb[$the][messageid]." ".$goipdb[$the][tel][id]." ".$goipdb[$the][tel][telnum]."\n";
								//echo "SEND $goipnow[name] ".$goipdb[$the][messageid]." ".$goipdb[$the][telid]." ".$goipdb[$the][tel]."\n<br>";
								//if (socket_sendto($socket,$buf, strlen($buf), 0, "127.0.0.1", $port)===false)
									//echo ("sendto error");
								$goipdb[$the][timer]=3;
							}
							elseif($errortels[$goipnow[provider]]){
								foreach($errortels[$goipnow[provider]] as $telthe => $nowrow){
									if(!in_array($goipnow[id],$nowrow['error'])){
										$goipdb[$the][tel]=$nowrow;
										unset($errortels[$goipnow[provider]][$telthe]);
										$goipdb[$the][tel]=$goipdb[$the][tel];						
			
										$goipdb[$the][telid]=$goipdb[$the][tel][id];
										//$goipdb[$the][telid]=$testid++;
										//echo "inser: ".$goipdb[$the][telid]." ".$goipdb[$the][tel]." start<br>";
										$goipdb[$the]['send']="SEND";
										$goipdb[$the][timer]=3;
										dolastsend( $goipdb[$the],$len,$msg);
										//$goipdb[$the]['send']="SEND";
										//$buf="SEND ".$goipdb[$the][messageid]." ".$goipdb[$the][tel][id]." ".$goipdb[$the][tel][telnum]."\n";
										//echo "SEND $goipnow[name]".$goipdb[$the][messageid]." ".$goipdb[$the][telid]." ".$goipdb[$the][tel]."\n<br>";
										//if (socket_sendto($socket,$buf, strlen($buf), 0, "127.0.0.1", $port)===false)
											//echo ("sendto error");
										$goipdb[$the][timer]=3;									
										break;
									}
								}							
							}
							if(checkover($goipdb)) break 2;
						}

					}
					elseif($comm[0]=="WAIT"){
							echo "WAIT $goipnow[send] $comm[2] $goipnow[telid] <br>";         
							if($goipnow[send]=="SEND" && $comm[2]==$goipnow[telid]){          
									$goipdb[$the][timer]=3;//????????????                         
							}
					} 
					elseif($comm[0]=="MSG"){ //???????????????
					/*
						if($goipnow[send]="SEND")
							array_push($tels[$goipnow[provider]], $goipnow[tel]); //??????
						if($goipnow[send]!="MSG"){
							$goipnow[timer]=3;
							$goipnow[send]="MSG";
						}
						//if($comm[0]!="MSG")
							//$goipnow[timer]=0;
					*/	
					}
					elseif($comm[0]=="SEND"){
						//$goipnow['ok']=1;
						//$goipnow[send]="SEND";	
						if($goipnow[send]=="SEND")//????????????????????????
							continue;		
						$goipdb[$the]['send']="OK";//?????????
						$goipdb[$the][telid]=0;
						$goipdb[$the][tel]=0;
						$goipdb[$the][timer]=0;	
						if(!check_sms_remain_count($db,$goipnow[id],$goipnow[name])) continue;
						if(($goipdb[$the][tel]=array_pop($tels[$goipnow[provider]]))!==NULL){//??????	
							/*????????????????????????id, ??????*/

							$goipdb[$the][telid]=$goipdb[$the][tel][id];
							//$goipdb[$the][telid]=$testid++;
							//echo "inser: ".$goipdb[$the][tel][id]." ".$goipdb[$the][tel][telnum]." start<br>";
							$goipdb[$the]['send']="SEND";
							$goipdb[$the][timer]=3;
							dolastsend( $goipdb[$the],$len,$msg);							
							//$goipdb[$the]['send']="SEND";
							//$buf="SEND ".$goipdb[$the][messageid]." ".$goipdb[$the][tel][id]." ".$goipdb[$the][tel][telnum]."\n";
							//echo $buf." ($goipnow[name] $goipnow[prov])<br>";
							$db->query("update sends set goipid=".$goipdb[$the][id].", error_no='' where id='".$goipdb[$the][telid]."'");
							//if (socket_sendto($socket,$buf, strlen($buf), 0, "127.0.0.1", $port)===false)
								//echo ("sendto error");
							$goipdb[$the][timer]=3;
						}
						elseif($errortels[$goipnow[provider]]){
							foreach($errortels[$goipnow[provider]] as $telthe => $nowrow){
								if(!in_array($goipnow[id],$nowrow[error])){
									$goipdb[$the][tel]=$nowrow;
									unset($errortels[$goipnow[provider]][$telthe]);
									//$goipdb[$the][tel]=$goipdb[$the][tel][tel];
									$goipdb[$the][telid]=$goipdb[$the][tel][id];
									//$goipdb[$the][telid]=$testid++;
									//echo "inser: ".$goipdb[$the][telid]." ".$goipdb[$the][tel]." start<br>";
									$goipdb[$the]['send']="SEND";
									$goipdb[$the][timer]=3;
									dolastsend( $goipdb[$the],$len,$msg);
									//$goipdb[$the]['send']="SEND";
									//$buf="SEND ".$goipdb[$the][messageid]." ".$goipdb[$the][tel][id]." ".$goipdb[$the][tel][telnum]."\n";
									//echo $buf." ($goipnow[name] $goipnow[prov])<br>";
									$db->query("update sends set goipid=".$goipdb[$the][id].", error_no='' where id='".$goipdb[$the][telid]."'");
									//if (socket_sendto($socket,$buf, strlen($buf), 0, "127.0.0.1", $port)===false)
										//echo ("sendto error");
									$goipdb[$the][timer]=3;									
									break;
								}
							}							
						}
						if(checkover($goipdb)) break 2;
					}
					elseif($comm[0]=="PASSWORD"){
						//$teli=substr($comm[1], -1);
						//echo ("PASSWORD s:$goipnow[send] c:$comm[1] p:$goipnow[password]\n");
						if($goipnow['send']!="PASSWORD" && $goipnow['send']!="MSG")//????????????????????????????????????
							continue;
						
						socket_sendto($socket,"PASSWORD $comm[1] $goipnow[password]\n", strlen("PASSWORD $comm[1] $goipnow[password]\n"), 0, "127.0.0.1", $port);
						$goipdb[$the][send]="PASSWORD";
						$goipdb[$the][timer]=3;					
					}
					elseif($comm[0]=="ERROR"){
						//sscanf($comm[3], "errorstatus:%d",$error_no);
						echo "<font color='#FF0000'>$buf ($goipnow[name] $goipnow[prov] ".$goipnow[tel][telnum].")</font><br>";
                                                if($goipdb[$the][send]=="PASSWORD" && ($comm[2]=="SENDID" || $comm[2]=="GSM_LOGOUT")){
                                                                $goipdb[$the]['timer']=$re_ask_timer;
                                                                $goipdb[$the]['send']="RMSG";
                                                }
                                                elseif($goipdb[$the][send]=="SEND" && ($comm[2]=="SENDID" || $comm[2]=="GSM_LOGOUT")){//sendid??????
                                                        $goipdb[$the]['telrow']['error'][]=$goipdb[$the]['id'];
                                                        $findokflag=0;
							
                                                        foreach($goipdb as $the1 => $goiptmp){
                                                                if($goiptmp[send]=="OK" && $goiptmp[provider]==$goipsend[provider] && !in_array($goiptmp[id],$goipdb[$the]['telrow']['error']) ){
                                                                        $goipdb[$the1][send]="SEND";
                                                                        $goipdb[$the1][tel]=$goipnow[tel];
                                                                        $goipdb[$the1][telid]=$goipnow[telid];
                                                                        $goipdb[$the1][telrow]=$goipdb[$the]['telrow'];
                                                                        //$goipdb[$the1][telrow]['error']=$goipnow[telrow]['error'];
                                                                        $goipdb[$the1][timer]=3;
                                                                        $findokflag=1;
                                                                        $db->query("update sends set goipid=$goiptmp[id] where id='".$goipnow[telid]."'");
                                                                        dolastsend( $goipdb[$the1],$len,$msg);
                                                                        break;
                                                                }
                                                        }
                                                        if(!$findokflag){
                                                                array_push($errortels[$goipsend[provider]], $goipdb[$the][telrow]);
								$db->query("update sends set error_no='$error_no',time=now() where id='".$goipdb[$the][telid]."'");
                                                                //array_push($tels[$goipsend[provider]], $goipsend[tel]); //??????
                                                        }

                                                        $goipdb[$the][telid]=0;
                                                        $goipdb[$the][tel]=0;
                                                        if($comm[2]=="SENDID"){
                                                                $goipdb[$the]['send']="MSG";//?????????
                                                                $goipdb[$the][timer]=3;
                                                                dolastsend($goipdb[$the],$len,$msg);
                                                        }
                                                        elseif($comm[2]=="GSM_LOGOUT"){
                                                                echo "GSM_LOGOUT!!!!ask after 100S<br>";
                                                                $goipdb[$the]['timer']=$re_ask_timer;
                                                                $goipdb[$the]['send']="RMSG";                                                                                                   }
                                                }
						elseif($goipdb[$the][send]=="SEND" && $comm[2]==$goipnow[telid]){//????????????
							sscanf($comm[3], "errorstatus:%d",$error_no);
							//echo "erroe_no: $error_no";
							$goipdb[$the]['tel']['error'][]=$goipdb[$the]['id'];
							//print_r($goipdb[$the]['tel']['error']);
							//echo "goipsend[tel][error]:".$goipsend[tel][error];
							//print_r($errortels);
							if(count($goipdb[$the]['tel']['error'])<=1){ 
							    foreach($goipdb as $the1 => $goiptmp){
								if(!check_sms_remain_count($db,$goiptmp[id],$goiptmp[name])) continue;
								if($goiptmp[send]=="OK" && $goiptmp[provider]==$goipnow[provider] && !in_array($goiptmp[id],$goipdb[$the]['tel']['error']) ){
									$goipdb[$the1][send]="SEND";
									$goipdb[$the1][tel]=$goipdb[$the][tel];
									$goipdb[$the1][telid]=$goipnow[telid];
									//$goipdb[$the1][tel]['error']=$goipnow[tel]['error'];
									$goipdb[$the1][timer]=3;
									$findokflag=1;
									//echo "update sends set goipid=$goiptmp[id] where id=$goipnow[telid]";
									$db->query("update sends set goipid=$goiptmp[id] where id='".$goipnow[telid]."'");
									dolastsend($goipdb[$the1],$len,$msg);
									break;
								}
							    }                                                      
							    if(!$findokflag){
								//echo "update sends set error_no='$error_no',time=now() where id=".$goipdb[$the][telid];
								$db->query("update sends set error_no='$error_no',time=now() where id='".$goipdb[$the][telid]."'");
								array_push($errortels[$goipnow[provider]], $goipdb[$the][tel]);
								//$db->query("delete from sends where id=$goipnow[telid]");
								//array_push($tels[$goipsend[provider]], $goipsend[tel]); //??????
							    }
							}
							$goipdb[$the]['send']="OK";//?????????
							$goipdb[$the][telid]=0;
							$goipdb[$the][tel]=0;
							$goipdb[$the][timer]=0;
							if(!check_sms_remain_count($db,$goipdb[$the][id],$goipdb[$the][name])) continue;
							if(($goipdb[$the][tel]=array_pop($tels[$goipnow[provider]]))!==NULL){//??????	
								$goipdb[$the][telid]=$goipdb[$the][tel][id];
								//$goipdb[$the][telid]=$testid++;
								//echo "insert: ".$goipdb[$the][tel][id]." ".$goipdb[$the][tel][telnum]." start<br>";
								$goipdb[$the]['send']="SEND";
								$goipdb[$the][timer]=3;
								dolastsend( $goipdb[$the],$len,$msg);
								//$buf="SEND ".$goipdb[$the][messageid]." ".$goipdb[$the][tel][id]." ".$goipdb[$the][tel][telnum]."\n";
								//echo $buf." ($goipnow[name] $goipnow[prov])<br>";
								//echo "update sends set goipid=".$goipdb[$the][id]." and error_no='' where id=".$goipdb[$the][telid];
								$db->query("update sends set goipid=".$goipdb[$the][id].", error_no='' where id='".$goipdb[$the][telid]."'");
								//if (socket_sendto($socket,$buf, strlen($buf), 0, "127.0.0.1", $port)===false)
									//echo ("sendto error");
								$goipdb[$the][timer]=3;
							}
							elseif($errortels[$goipnow[provider]]){
								foreach($errortels[$goipnow[provider]] as $telthe => $nowrow){

									if(!in_array($goipnow[id],$nowrow[error])){
										$goipdb[$the][tel]=$nowrow;
										unset($errortels[$goipnow[provider]][$telthe]);
										//$goipdb[$the][tel]=$goipdb[$the][tel][tel];
										$goipdb[$the][telid]=$goipdb[$the][tel][id];
										//$goipdb[$the][telid]=$testid++;
										//echo "inser: ".$goipdb[$the][telid]." ".$goipdb[$the][tel]." start<br>";
										$db->query("update sends set goipid=".$goipdb[$the][id].", error_no='' where id='".$goipdb[$the][telid]."'");
										
										$goipdb[$the]['send']="SEND";
										$goipdb[$the][timer]=3;
										dolastsend( $goipdb[$the],$len,$msg);
										//$buf="SEND ".$goipdb[$the][messageid]." ".$goipdb[$the][tel][id]." ".$goipdb[$the][tel][telnum]."\n";
										//echo $buf." ($goipnow[name] $goipnow[prov])<br>";
										//echo "update sends set goipid=".$goipdb[$the][id]." and error_no='' where id=".$goipdb[$the][telid];
										//if (socket_sendto($socket,$buf, strlen($buf), 0, "127.0.0.1", $port)===false)
										//	echo ("sendto error");
										$goipdb[$the][timer]=3;									
										break;
									}

								}							
							}
							if(checkover($goipdb)) break 2;
						}
						elseif($goipnow[timer]>0){
							dolastsend($goipdb[$the],$len,$msg);
							$goipdb[$the][timer]--;
						}
						else //??????????????????
							continue;
					}//elseif($comm[0]=="ERROR"){
					$goipdb[$the]['time']=time();
				//}//foreach($bufline as $line){
			  }//foreach($read
				$i=0;
				
				$nowtime=time();
				foreach($goipdb as $the0 => $goipsend){
					//$flag=0;
					if($goipsend['time'] <$nowtime-$timeout && $goipsend[send]!="OK"){//?????????
						$goipdb[$the0]['time']=$nowtime;
						if($goipsend[timer]>0){//??????
							//$flag=1;//?????????
							dolastsend($goipdb[$the0],$len,$msg);
							$goipdb[$the0][timer]--;
							//echo("<br>$i timer:".$goipsend[timer]."<br>");
							$i++;
						}
						else{ //????????????
						if($goipsend[send]=="SEND"){
							echo "<font color='#FF0000'>$goipnow[telid] $goipnow[tel] faile</font><br>";
							foreach($goipdb as $the => $goiptmp){ 
								if($goiptmp[send]=="OK" && $goiptmp[provider]==$goipsend[provider]){
									if($goipsend[tel][error] && in_array($goiptmp[id],$goipsend[tel][error]))
										continue;//???????????????
									$goipdb[$the][send]="SEND";
									$goipdb[$the][tel]=$goipsend[tel];
									$goipdb[$the][telid]=$goipsend[telid];
									$goipsend[send]=="OK"; //??????
									dolastsend($goiptmp,$len,$msg);
									$goipdb[$the0][send]="RMSG";//?????????goip???100s?????????
									$goipdb[$the0][timer]=$re_ask_timer;
									$goipdb[$the0][tel]=0;
									$goipdb[$the0][telid]=0;
									break;
								}									
							}
						}
						else{
							/*100s ???????????????*/
							echo "<font color='#FF0000'>cannot get response from goip:  $goipsend[send] ($goipsend[name] $goipsend[prov])</font><br>";
							$goipdb[$the0][send]="RMSG";
							$goipdb[$the0][timer]=$re_ask_timer; 
						}
						if($goipsend[send]=="SEND"){//?????????????????????goip?????????????????????100s???????????????
							if($goipsend[tel][error])
								array_push($errortels[$goipsend[provider]], $goipsend[tel]);//??????????????????
							else 
								array_push($tels[$goipsend[provider]], $goipsend[tel]); //??????
							//array_push($tels[$goipsend[provider]], $goipsend[tel]); //??????
							/*???????????????*/
							$goipdb[$the0][send]="RMSG";
							$goipdb[$the0][timer]=$re_ask_timer;
							$goipdb[$the0][tel]=0;
							$goipdb[$the0][telid]=0;						
						}	
								
						}	
					}
					//$goipdb[$the0]['time']=$nowtime;
					
					//else $flag++;//??????
				}
			}//else{ //??????
			/*????????????*/
		}//for(;;){
		foreach($socks as $socket){
			foreach($goipdb as $the => $goipnow){
				//echo "$key => $val\n";
				if($goipnow[sock]==$socket){
					break;
				}	
			}
			if($goipnow[sock]==$socket){
				$buf="DONE ".$goipdb[$the][messageid]."\n";
				socket_sendto($socket,$buf, strlen($buf), 0, "127.0.0.1", $port);
			}
		}
		//$i=0;
		//$i=count($tels);
		//$i+=count($errortels);
		$db->query("update message set `over`=2 where id=$sendid");
		echo "All sendings done!";
		echo "<br><br>";
		echo "<a href=sendinfo.php?id=$sendid target=main><font size=2'>Click me to check details.</font></a>";
}

	if(!empty($_GET[messageid])){
		$totalnum=0;
		if($_SESSION['goip_permissions'] <= 1)
			$merow=$db->fetch_array($db->query("SELECT id,msg,goipid FROM message where id=$_GET[messageid]"));
		else 
			$merow=$db->fetch_array($db->query("SELECT id,msg,goipid FROM message where id=$_GET[messageid] and userid=$userid"));	
		if(!$merow)
			die("Sending is non-existent or permission denied!");
		$sendid=$merow[id];
		$msg=$merow[msg];
		if(!$msg || !strlen($msg)) $msg='1';
		$goipid=$merow[goipid];
		$len=strlen($msg);
		$query=$db->query("SELECT * FROM sends  where messageid=$_GET[messageid] and `over`=0 ORDER BY id");
		while($row=$db->fetch_array($query)) {
			$tels[$row[provider]][]=$row; 
			$totalnum++;
		}
	}
			
	elseif(!empty($_GET["id"])){
		$Id=$_GET["id"];
		$query=$db->query("SELECT * FROM sends  where id in ($Id) ORDER BY id");
		$flag=1;
		while($row=$db->fetch_array($query)) {
			if($flag){
				if($_SESSION['goip_permissions'] <= 1)
					$merow=$db->fetch_array($db->query("SELECT id,msg,goipid FROM message where id=$row[messageid]"));
				else 
					$merow=$db->fetch_array($db->query("SELECT id,msg,goipid FROM message where id=$row[messageid] and userid=$userid"));
				if(!$merow)
					die("Sending is non-existent or permission denied!");	
				$sendid=$merow[id];
				$msg=$merow[msg];
				if(!$msg || !strlen($msg)) $msg='1';
				$goipid=$merow[goipid];
				$len=strlen($msg);
				echo "len:$len msg:$msg";
				$flag=0;
			}
			$tels[$row[provider]][]=$row; 
			$totalnum++;
		}
	}
	else 
		die("Sending is non-existent");
	//$Idb=$Ida;
	//$Idc=$Ida;
	echo "total: $totalnum <br>";
	//die;
	//print_r($tels);
	session_write_close();
	startdo($db, $tels, $sendid, $msg, $len, $goipid);

?>
</body>
</html>
