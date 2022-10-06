<?php
//echo "1";
require_once('../api_global.php');

$returnMsg=array();
if($data->taskID){
	$ids=explode('.', $data->taskID);
	$task_id=$ids[0];
	$num=$ids[1];
	$where=" where messageid='$task_id'";
	if($num) $where.=" and telnum='$num'";
	$query=$db->query("select * from sends left join goip on goip.id=goipid $where order by sends.id");
	//echo "select * from sends left join goip on goip.id=goipid $where order by sends.id";
	//echo "select * from sends $where order by id";
	while($row=$db->fetch_array($query)){
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
		if($row['received']==1) $e['receipt']=1;
		array_push($returnMsg, $e);
	}
}

//echo $postData;
echo json_encode($returnMsg);
//echo $data;
?>
