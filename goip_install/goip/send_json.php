<?php
require_once('inc/conn.inc.php');
function http_post_data($url, $data_string) {
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
  curl_setopt($ch, CURLOPT_HTTPHEADER, array(
   'Content-Type: application/json; charset=utf-8',
   'Content-Length: ' . strlen($data_string))
  );
  ob_start();
  curl_exec($ch);
  $return_content = ob_get_contents();
  //echo $return_content."<br>"; 
  ob_end_clean();

  $return_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  // return array($return_code, $return_content); 
  return $return_content;
 }
$type=$argv[1];
//echo $type;
$id=$argv[2];
echo "send json start in php\n";

if(!$id || ($type!="send" && $type!="recv")) {
	echo "no ID or on Type exit php\n";
	exit;
}
if($type=="send"){
	$query=$db->query("select json_send_url from system");
	$rs=$db->fetch_array($query);
	if(!$rs[0]) {
		echo "no URL exit php\n";
		exit;
	}
	
	$url=$rs[0];
	$query=$db->query("select * from sends left join goip on goip.id=goipid where sends.id='$id' order by sends.id");
	if($row=$db->fetch_array($query)){
		$e=null;
		$e['taskID']=$row['messageid'].".".$row['telnum'];
		$e["goip_line"]=$row['name'];
		$e["sendTime"]=$row['time'];
		if($row['over']==1){
			$e['send']="succeeded";
		}else if($row['error_no']!=0){
			$e['send']="failed";
			$e['err_code']=$row['error_no'];
		}else if($row['sending_line']!=0){
			$e['send']="sending";
		}else $e['send']="unsend"; 
/*
		if($row['over']==0 && $row['error_no']!=0){
			$e['send']="failed";
			$e['err_code']=$row['error_no'];
		}else if($row['over']==0) $e['send']="unsend"; 
		else $e['send']="succeeded";
*/
		if($row['received']==1) $e['receipt']=1;
		//array_push($returnMsg, $e);
		$data=json_encode($e);
		echo "send json to $url ";
		echo "\nrecv:".http_post_data($url, $data);
		echo "\n";
	}
}else if($type=="recv"){
	$query=$db->query("select json_recv_url from system");
	$rs=$db->fetch_array($query);
	if(!$rs[0]) {
		echo "no ID or on Type exit php\n";
		exit;
	}
	$url=$rs[0];
	$query=$db->query("select * from receive left join goip on goip.id=goipid where receive.id='$id'");
	if($row=$db->fetch_array($query)){
		$e["goip_line"]=$row['name'];
		$e["from_number"]=$row['srcnum'];
		$e["content"]=$row['msg'];
		$e["recv_time"]=$row['time'];
		$data=json_encode($e);
		echo "send json to $url ";
		echo "\nrecv:".http_post_data($url, $data);
		echo "\n";
	}
}
echo "send json end in php\n";

?>
