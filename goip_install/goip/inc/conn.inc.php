<?php
set_time_limit(0);
error_reporting(E_ALL & ~E_NOTICE);
ini_set("memory_limit", "500M");
/*
	$dbhost='localhost';	//database server
	$dbuser='goip';		//database username
	$dbpw='goip';		//database password
	$dbname='goip';		//database name 哈
//*/
//ob_start();


include_once 'config.inc.php';
include_once("version.php");
include_once 'forbId.php';

//var_dump(headers_list());


//var_dump(headers_list());
/*
$dbhost1=$dbhost;
$dbuser1=$dbuser;
$dbpw1=$dbpw;
$dbname1=$dbname;
*/
function myaddslashes($var)
{
	if(!get_magic_quotes_gpc())
		return addslashes($var);
	else
		return $var;
}

class DB {
	function DB(){
		global $dbhost,$dbuser,$dbpw,$dbname, $conn;

		$conn=mysqli_connect($dbhost,$dbuser,$dbpw) or die("Could not connect");
		//@mysqli_connect($db_host,$db_user,$db_psw) or die(‘数据库连接失败.‘.mysqli_error());
		mysqli_select_db($conn, $dbname);
		mysqli_query($conn, "SET NAMES 'utf8'");		
		mysqli_query($conn, "set sql_mode='ANSI'");
	}
	function query($sql) {
		global $conn;
		$result=mysqli_query($conn,$sql) or die("Bad query: ($sql)");
		//$result=mysqli_query($conn,$sql) or die("Bad query: ".mysqli_error($conn)."($sql)");
		return $result;
	}
	function updatequery($sql) {
		global $conn;

                $result=mysqli_query($conn,$sql);
                return $result;
        }

	function fetch_array($query) {
		global $conn;
		return mysqli_fetch_array($query, MYSQLI_BOTH);
	}
	
	function fetch_assoc($query) {
		global $conn;
		return mysqli_fetch_assoc($query);
	}
	
	function num_rows($query) {
		global $conn;
		return mysqli_num_rows($query);
	}
	function real_escape_string($item){
		global $conn;
		return mysqli_real_escape_string($conn,$item);
	}
}

$db=new DB;

?>
