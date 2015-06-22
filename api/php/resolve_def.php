<?php


/*

Basically we can distinguish between following definitions:

* Top-level entities e.g. [["e",".."],"..]
* Sub-level entities e.g. [["pla",".."],"..]
* Top-level functions e.g. [["f",".."],"..]   

Note: Sub-level functions are not supported. But function-names can be defined several times with
different input-parameters. 

Sub-level entities must match to their definition. 

Top-level entitites might include sub-entities with exponents. 
Sub-level-entities can also include exponents but those must be within the limits of their definition.


*/


if(!$is_api_call)die("X");

require_once("php/resolve_common.php");

// ----------------------------------------------------------------
function resolve_def($e)
{
  global $_ME;

  $d=$e[0][0];

  $row_d=dbs::singlerow("SELECT * FROM {$_ME['prefix']}e WHERE n='$d' AND is_now=1");
  if(!count($row_d))
  {
    // definition not found
    return 1;
  }

  switch ($d)
  {
    case "e":
      // top-level entity
      return resolve_def_e_top($e);
    break;
    case "f":
      // function
      return resolve_def_f($e);
    break;
    default:
      // sub-level-entity
      return resolve_def_e_sub($e,$row_d);
    break;
  }
}



// ----------------------------------------------------------------
function resolve_name_and_exp($n)
{
  $pos_exp=strpos($n,"^");
  $exp_min=1;
  $exp_max=1;
  if($pos_exp)
  {
    $n=substr($n,0,$pos_exp);
    $exp=substr($n,$pos_exp);
    if(strlen($exp))
    {
      $exp=explode("-",$exp);
      if(!is_int($exp[0]))
      {
	//invalid exponent
	return 1;
      }
      $exp_min=(int)$exp[0];
      if(count($exp)>2)
      {
	//malformed exponent
	return 1;
      }
      if(count($exp)==2)
      {
	if(!is_int($exp[1]))
	{
	  //invalid exponent
	  return 1;
	}
	$exp_max=(int)$exp[1];
	if($exp_max<$exp_min)
	{
	  //malformed exponent
	  return 1;
	}
      }
      else
      {
	$exp_max=$exp_min;
      }
    }
    if($exp_min<0)
    {
      //invalid exponent
      return 1;
    }
    if($exp_max<1)
    {
      //invalid exponent
      return 1;
    }
  }
  
  return array("n"=>$n,
	       "exp_min"=>$exp_min,
	       "exp_max"=>$exp_max);
}


// ----------------------------------------------------------------
function resolve_def_e_top($e)
{
  global $_ME;

  $n=$e[0][1];

  $row_e=dbs::singlerow("SELECT id FROM {$_ME['prefix']}e WHERE n='$n' AND is_now=1");
  if(count($row_e))
  {
    // name already used
    return 1;
  }
  
  $ct_sub=count($e)-1;
  if(!$ct_sub)
  {
    // no sub-entities defined
    return 1;
  }
  
  $row_sub_e=array();
  for ($cc=1;$cc<=$ct_sub;$cc++)
  {
    $sub_n=$e[$cc];
    $exp_res=resolve_name_and_exp($sub_n);
    if(!is_array($exp_res))
    {
      // malformed exponents
      return 1;
    }
    $sub_n=$exp_res["n"];
    $exp_min=$exp_res["exp_min"];
    $exp_max=$exp_res["exp_max"];
    
    $row_current=dbs::singlerow("SELECT * FROM {$_ME['prefix']}e WHERE n='{$sub_n}' AND is_now=1");    
    if(!count($row_current))
    {
      if($sub_n==$n)
      {
        //recursive definition
        
      }
      else
      {
	// sub-name not found
	return 1;
      }
    }  
    $row_sub_e[]=$row_current;
  }
  
  return 0;
}



?>