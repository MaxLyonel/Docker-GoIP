<?php
//echo "1";
require_once('../api_global.php');

$returnMsg=array();
$query=$db->query("select * from goip order by name");
while($row=$db->fetch_array($query)){
	$e['goip_line']=$row['name'];
	$e['online']=$row['alive'];
	$e['reg']=$row['gsm_status'];
	$e['num']=$row['num'];
	$e['remain_sms']=$row['remain_count'];
	$e['day_remain_sms']=$row['remain_count_d'];
	array_push($returnMsg, $e);
}

//echo $postData;
echo json_encode($returnMsg);
//echo $data;
?>
