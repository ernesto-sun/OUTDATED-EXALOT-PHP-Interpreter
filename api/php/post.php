<?php

/*    POST Processing

Following options are available:
 
A definition is a specialized form of an expression. (Given an explizit name)

A statement can include several expressions by using ["p",...]

Each expression is handled seperately. For each in-expression an out-expression is generated (uc-con)

The first sub-entity of an expression must be known. (It decides about definition or expression)

The sub-entities are looked at from left to right. A sub-entity can not be a definition. 

If an expression is a definition the sub-entities must be n or p but no more complex expressions.


Example: ["add",["s","the number is: "],13.5]

The definition of add is: [["f","add"],"p-num","num"]
and also [["f","add"],"p-s","s"]

[["f","add"],"p","e"]

["add","p-s","s"]

This 



[["f","and"],"p-bit","bit",["foreach","1","@in",["eq","@e","0"],["break","0"],""]]

[["f","and"],"pair-bit","bit",["if",["eq","@in","pair-bit-ii"],"i","o"]]


Diskret definierte Funktionen?! Dafuer gibt es if

Entities have conditions.
Functions have algorithms.




*/


if(!$is_api_call)die("X");


function resolve_post($e,$firstlevel=1)
{
  global $_RES;

  $_RES[0]['']

  
  $error_malformed=0;

  $count_sub=count($e);
  $first=$e[0];
  $count_first=is_array($first)?count($first):1;
  if($count_first>2)
  {
    $error_malformed=1;
  }
  elseif($count_first==2)
  {
    // one definition is given
    $error_malformed=resolve_def($e);
  }
  elseif($count_first==1)
  {
    switch($first)
    {
      case "p":
      case "p-e":
	if($firstlevel)
	{
	  $error_malformed=1;
	}
	else
	{
	  foreach($e as $cc => $e_sub)
	  {
	    $error_malformed=resolve_post($e_sub,0);
	  }
	}
      break;
      default:
      {
	$error_malformed=resolve_exp($e);
      }
    }
  }
  else
  {
    $error_malformed=1;
  }
  return $error_malformed;  
}

require_once "php/resolve_def.php";
require_once "php/resolve_exp.php";

$error_malformed=resolve_post($e);

if($error_malformed)
{
  $result=array("Malformed");
  $result_state=402;
}
else
{
  $result=array("Ok");

}


?>
