<?php
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
  //if($return_code !=200 && $return_code!=302) 
	echo "HTTP return $return_code\n";
  // return array($return_code, $return_content); 
  return $return_content;
 }

$url = "http://192.168.2.1/goip/querylines/";
/*
//$data = json_encode(array('a'=>"weqweqwe", 'b'=>2));
//"auth":{"username":"goip","password":"goip"},
//"token":"token",
//"token":"-sei_TkSJv8h_Aqid0EOIcgVV2s",
"goip_line":"G101",
"provider":"移动",
*/
$data = '{
"auth":{"username":"root","password":"root"},
"taskID":"5792",
"number":"10086,1008616",  
"content":"test2"
}';

//list($return_code, $return_content) = http_post_data($url, $data); 
$aaa = http_post_data($url, $data);
$ccc=json_decode($aaa); 
//print_r($ccc); 
echo $aaa;
?>
