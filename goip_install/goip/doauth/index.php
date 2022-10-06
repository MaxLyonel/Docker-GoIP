<?php
define("OK", true);
require_once('../inc/conn.inc.php');
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

function gen_token()
{
	$v = 1;
	$key = mt_rand();
	$hash = hash_hmac("sha1", $v . mt_rand() . time(), $key, true);
	$token = str_replace('=', '', strtr(base64_encode($hash), '+/', '-_'));
	return $token;
}

$postData = file_get_contents('php://input');
$data = json_decode($postData);
$ip=get_real_ip();
if($data->username && $data->password) {
	$password=$data->auth->password;
	$username=$data->auth->username;
        $password=md5($password);
        $query=$db->query("SELECT id,permissions FROM user WHERE username='$username' and password='$password' ");
	$rs=$db->fetch_array($query);
	if($rs[0]){
		$userid=$rs[0];
		$token=gen_token();
		$db->query("insert into token set ip='$ip', token='$token', user_id='$userid', insert_time=now()");
		$db->query("delete from token where datediff( now( ) , insert_time ) >10");
		echo '{"token":"'.$token.'"}';
		exit;
		//echo $data->auth->username;
	}else {
		header("HTTP/1.1 403");
	}
}else {
	header("HTTP/1.1 403");
}
?>
