<?php	
//require_once('config.php');

$myServer = "TRA\SQL2017";
$myUser = "portalman";
$myPass = 'portalman';
$myDB = "TRANEW";

$params = array();
$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );

$connectionInfo = array("UID" => $myUser, "PWD" => $myPass, "Database"=> $myDB, "ReturnDatesAsStrings" => true,"CharacterSet" => "UTF-8");
//$db = mssql_connect($myServer, $myUser, $myPass) or die("Couldn't connect to SQL Server on $myServer");
$db = sqlsrv_connect( $myServer, $connectionInfo);
if ($db)
{
	//echo "Database Connection successful";
} else
{
	echo "Database Connection Failed";
	// echo "Parameters are $myUser $myPass $myDB";
}

?>