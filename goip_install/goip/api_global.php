<?php
define("OK", true);
require_once('inc/conn.inc.php');
function get_real_ip()
{
    $ip=FALSE;
    //客户端IP 或 NONE 
    if(!empty($_SERVER["HTTP_CLIENT_IP"])){
        $ip = $_SERVER["HTTP_CLIENT_IP"];
    }
    //多重代理服务器下的客户端真实IP地址（可能伪造）,如果没有使用代理，此字段为空
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ips = explode (", ", $_SERVER['HTTP_X_FORWARDED_FOR']);
        if ($ip) { array_unshift($ips, $ip); $ip = FALSE; }
        for ($i = 0; $i < count($ips); $i++) {
            if (!eregi ("^(10│172.16│192.168).", $ips[$i])) {
                $ip = $ips[$i];
                break;
            }
        }
    }
    //客户端IP 或 (最后一个)代理服务器 IP 
    return ($ip ? $ip : $_SERVER['REMOTE_ADDR']);
}
$postData = file_get_contents('php://input');
$data = json_decode($postData);
if($data->auth && $data->auth->username && $data->auth->password) {
	$password=$data->auth->password;
	$username=$data->auth->username;
        $password=md5($password);
        $query=$db->query("SELECT id,permissions FROM user WHERE username='$username' and password='$password' ");
	$rs=$db->fetch_array($query);
	if($rs[0]){
		$userid=$rs[0];
		//echo $data->auth->username;
header("content:application/json;chartset=uft-8");
		
	}else {
		header("HTTP/1.1 401 Unauthorized");
		exit();
	}
}else if($data->token){
	$token=$data->token;
	$ip = get_real_ip();
	//echo $ip;
	$query=$db->query("SELECT user_id FROM token WHERE ip='$ip' and token='$token' and insert_time > NOW()-INTERVAL 1 HOUR");
	//echo "SELECT user_id FROM token WHERE ip='$ip' and token='$token' and insert_time > NOW()-INTERVAL 1 HOUR";
	$rs=$db->fetch_array($query);
	if($rs[0]){
		$userid=$rs[0];
		//echo "token ok";
	}else {
		//echo "token error";
		header("HTTP/1.1 401 Unauthorized");
		exit();
	}
}else {
header("HTTP/1.1 401 Unauthorized");
exit;
//http_response_code(401);
//exit(json_encode(error([], '登录过期，请重新登录'), JSON_UNESCAPED_UNICODE));
//echo "Error no auth";
}
//echo $postData;
//$data = json_encode(array('a'=>" 234 ", 'b'=>2));
//echo $data;
?>
