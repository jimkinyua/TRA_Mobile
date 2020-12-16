<?php
require 'DB_PARAMS/connect.php';
require_once('county_details.php');
require_once('GlobalFunctions.php');

if (!isset($_SESSION))
{
	session_start();
}
function lastId($queryID) 
{
	sqlsrv_next_result($queryID);
	sqlsrv_fetch($queryID);
	return sqlsrv_get_field($queryID, 0);
}

function getrights($db,$UserID,$PageID)
{	
    
 	/* $view=0;
	$Edit=0;
	$Add==0;	
	$Delete=0;	 */ 
	
	$view=1;
	$Edit=1;
	$Add==1;	
	$Delete=1;	

	$sql=" select [View],[Edit],[Add],[Delete] from vwuserroles where AgentID=$UserID and PageID=$PageID";
	
    // if($PageID==25){
        // echo $UserID;
        // exit;
    // }
     
    
	$params = array();
	$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
	
	$result = sqlsrv_query($db,$sql,$params,$options);
	$myrow = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC);
	$row_count=sqlsrv_num_rows($result);
	
	if ($row_count == 0)
	{
	   $myrow['View']=0;
	   $myrow['Edit']=0;
	   $myrow['Add']=0;
	   $myrow['Delete']=0;  
	}
	
	return  $myrow;
}

function checkSession($db,$UserID)
{
 
    $DbSessionID=0;
    $sql="Select SessionID from SessionMgr where UserID=$UserID and Active=1";
    $result=sqlsrv_query($db,$sql);
    //echo $sql;
    while($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC))
    {        
        $DbSessionID=$row['SessionID'];
    }    

    if ($_SESSION['ID']!=$DbSessionID){
        $_SESSION['Expired']=1;
    }else{
        $_SESSION['Expired']=0;
    }
 
}

function generatePassword()
{
	$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%&*_";
	$s_chars=str_shuffle($chars);
	while (substr($s_chars,0,1)==='&')
	{
		$s_chars=str_shuffle($chars);
	}
	$password = substr( str_shuffle( $chars ), 0, 8 );
	return $password;
}

function randomNumber()
{
	$chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";	
	$password = substr( str_shuffle( $chars ), 0, 10 );
	return $password;
}

function redirect($RequestArray, $msg, $Destination)
{
	$replystr = '';
	foreach ($RequestArray AS $key => $value)
	{
		$replystr .= "&$key=$value";
	}
	$replystr = "msg=$msg".$replystr;
	Header ("Location: $Destination"."?".$replystr);	
}


/**
 * English Number Converter - Collection of PHP functions to convert a number
 *                            into English text.
 *
 * This exact code is licensed under CC-Wiki on Stackoverflow.
 * http://creativecommons.org/licenses/by-sa/3.0/
 *
 * @link     http://stackoverflow.com/q/277569/367456
 * @question Is there an easy way to convert a number to a word in PHP?
 *
 * This file incorporates work covered by the following copyright and
 * permission notice:
 *
 *   Copyright 2007-2008 Brenton Fletcher. http://bloople.net/num2text
 *   You can use this freely and modify it however you want.
 */

function convertNumber($number)
{
    list($integer, $fraction) = explode(".", (string) $number);

    $output = "";

    if ($integer{0} == "-")
    {
        $output = "negative ";
        $integer    = ltrim($integer, "-");
    }
    else if ($integer{0} == "+")
    {
        $output = "positive ";
        $integer    = ltrim($integer, "+");
    }

    if ($integer{0} == "0")
    {
        $output .= "zero";
    }
    else
    {
        $integer = str_pad($integer, 36, "0", STR_PAD_LEFT);
        $group   = rtrim(chunk_split($integer, 3, " "), " ");
        $groups  = explode(" ", $group);

        $groups2 = array();
        foreach ($groups as $g)
        {
            $groups2[] = convertThreeDigit($g{0}, $g{1}, $g{2});
        }

        for ($z = 0; $z < count($groups2); $z++)
        {
            if ($groups2[$z] != "")
            {
                $output .= $groups2[$z] . convertGroup(11 - $z) . (
                        $z < 11
                        && !array_search('', array_slice($groups2, $z + 1, -1))
                        && $groups2[11] != ''
                        && $groups[11]{0} == '0'
                            ? " and "
                            : ", "
                    );
            }
        }

        $output = rtrim($output, ", ");
    }

    if ($fraction > 0)
    {
        $output .= " point";
        for ($i = 0; $i < strlen($fraction); $i++)
        {
            $output .= " " . convertDigit($fraction{$i});
        }
    }

    return $output;
}

function convertGroup($index)
{
    switch ($index)
    {
        case 11:
            return " decillion";
        case 10:
            return " nonillion";
        case 9:
            return " octillion";
        case 8:
            return " septillion";
        case 7:
            return " sextillion";
        case 6:
            return " quintrillion";
        case 5:
            return " quadrillion";
        case 4:
            return " trillion";
        case 3:
            return " billion";
        case 2:
            return " million";
        case 1:
            return " Thousand";
        case 0:
            return "";
    }
}

function convertThreeDigit($digit1, $digit2, $digit3)
{
    $buffer = "";

    if ($digit1 == "0" && $digit2 == "0" && $digit3 == "0")
    {
        return "";
    }

    if ($digit1 != "0")
    {
        $buffer .= convertDigit($digit1) . " hundred";
        if ($digit2 != "0" || $digit3 != "0")
        {
            $buffer .= " and ";
        }
    }

    if ($digit2 != "0")
    {
        $buffer .= convertTwoDigit($digit2, $digit3);
    }
    else if ($digit3 != "0")
    {
        $buffer .= convertDigit($digit3);
    }

    return $buffer;
}

function convertTwoDigit($digit1, $digit2)
{
    if ($digit2 == "0")
    {
        switch ($digit1)
        {
            case "1":
                return "Ten";
            case "2":
                return "Twenty";
            case "3":
                return "Thirty";
            case "4":
                return "Forty";
            case "5":
                return "Fifty";
            case "6":
                return "Sixty";
            case "7":
                return "Seventy";
            case "8":
                return "Eighty";
            case "9":
                return "Ninety";
        }
    } else if ($digit1 == "1")
    {
        switch ($digit2)
        {
            case "1":
                return "Eleven";
            case "2":
                return "Twelve";
            case "3":
                return "Thirteen";
            case "4":
                return "Fourteen";
            case "5":
                return "Fifteen";
            case "6":
                return "Sixteen";
            case "7":
                return "Seventeen";
            case "8":
                return "Eighteen";
            case "9":
                return "Nineteen";
        }
    } else
    {
        $temp = convertDigit($digit2);
        switch ($digit1)
        {
            case "2":
                return "Twenty-$temp";
            case "3":
                return "Thirty-$temp";
            case "4":
                return "Forty-$temp";
            case "5":
                return "Fifty-$temp";
            case "6":
                return "Sixty-$temp";
            case "7":
                return "Seventy-$temp";
            case "8":
                return "Eighty-$temp";
            case "9":
                return "Ninety-$temp";
        }
    }
}

function convertDigit($digit)
{
    switch ($digit)
    {
        case "0":
            return "zero";
        case "1":
            return "One";
        case "2":
            return "Two";
        case "3":
            return "Three";
        case "4":
            return "Four";
        case "5":
            return "Five";
        case "6":
            return "Six";
        case "7":
            return "Seven";
        case "8":
            return "Eight";
        case "9":
            return "Nine";
    }
}

function DisplayErrors()
{
     $errors = sqlsrv_errors(SQLSRV_ERR_ERRORS);
    //  echo '<pre>';
    //  print_r($errors);
    //  exit;

     foreach( $errors as $error )
     {
          echo "Error: ".$error['message']."\n";
     }
}

function encrypt_url($string) {
  $key = "MAL_979805"; //key to encrypt and decrypts.
  $result = '';
  $test = "";
   for($i=0; $i<strlen($string); $i++) {
     $char = substr($string, $i, 1);
     $keychar = substr($key, ($i % strlen($key))-1, 1);
     $char = chr(ord($char)+ord($keychar));

     $test[$char]= ord($char)+ord($keychar);
     $result.=$char;
   }

   return urlencode(base64_encode($result));
}

function decrypt_url($string) {
    $key = "MAL_979805"; //key to encrypt and decrypts.
    $result = '';
    $string = base64_decode(urldecode($string));
   for($i=0; $i<strlen($string); $i++) {
     $char = substr($string, $i, 1);
     $keychar = substr($key, ($i % strlen($key))-1, 1);
     $char = chr(ord($char)-ord($keychar));
     $result.=$char;
   }
   //echo $string;
   return $result;
}

function sentence_case($str) {
   $cap = true;
   $ret='';
   for($x = 0; $x < strlen($str); $x++){
       $letter = substr($str, $x, 1);
       if($letter == "." || $letter == "!" || $letter == "?"){
           $cap = true;
       }elseif($letter != " " && $cap == true){
           $letter = strtoupper($letter);
           $cap = false;
       } 
       $ret .= $letter;
   }
   return $ret;
}
function DateDiff($date1, $date2) 
{
	$date1=date_create($date1);
	$date1=date_format('Y-m-d',$date1);
	$date = DateTime::createFromFormat("Y-m-d", $date1);
	echo $date->format("Y");
}
function MonthName($month)
{
    switch ($month)
    {
        case 12:
            return "December";
		case 11:
            return "November";
        case 10:
            return "October";
        case 9:
            return "September";
        case 8:
            return "August";
        case 7:
            return "July";
        case 6:
            return "June";
        case 5:
            return "May";
        case 4:
            return "April";
        case 3:
            return "March";
        case 2:
            return "February";
        case 1:
            return "January";
		
    }
}
function roundUpToAny($n,$x=5) {
    return (ceil($n)%$x === 0) ? ceil($n) : round(($n+$x/2)/$x)*$x;
}
?>
