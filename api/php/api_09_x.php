<?php
if(!$is_api_call)die('X');


// ----------------------------------------------------------------		        
function set_row_noo(&$row)
{
  $row['v_cl']=$GLOBALS['conf']['cl-min'];
  $row['v_cl_year']=$GLOBALS['conf']['cl-year-min'];

  $row['n']='noo';
  $row['n_def']='b';

  $row['c_str']=0;
  
  $row['v_str']='';
  $row['v_int']=0;
  $row['v_float']=0;

  $row['is_literal']=1;

  $row['p']='e';  
  $row['e']='e';	    
  $row['r']='e';
  $row['l']='usage';
}		        
	
	
// ----------------------------------------------------------------
function set_row_literal(&$row,$n_def,$v)
{
  $n='';

  // overwrite old cl in case it was set before
  $row['v_cl']=$GLOBALS['conf']['cl-min'];
  $row['v_cl_year']=$GLOBALS['conf']['cl-year-min'];
  $row['c_str']=0;
  
  switch($n_def)
  {
    case 's':
      $row['c_str']=strlen($v);
      if($row['c_str']>255)
      {
	$row['v_str']=substr($v,0,255);
	if($row['v_str'][254]=='\\')$row['v_str'][254]=' '; // if we are 'lucky' we have an escape-string at the end, would be bad on the SQL with ' afterwards
	$row['tx']=$v;
      }
      else
      {
	$row['v_str']=$v;
      }
      // $n='';  is unset for string (will be later)
      $row['v_int']=0;
      $row['v_float']=0;
      
    break;
    case 'cl':
      //expecting array
      if(!is_array($v))
      {
	msg('error-internal','EXA-parsing failed: set_row_literal for cl needs an array as input');
      }
      $row['v_cl']=$v['cl'];
      $row['v_cl_year']=$v['year'];
      $row['v_int']=$v['ms'];
      $row['v_str']=$v['str'];
      $row['v_float']=0;
      
      $n=str_replace(array(' ',':'),'-',$row['v_str']);
    
    break;
    case 'int':

      $row['v_int']=(int)$v;
      $row['v_str']=$n;
      $row['v_float']=0;
 
      $n=''.$v;
    break;
    case 'float':

      $row['v_float']=$v;
      $row['v_int']=(int)$v;
      $row['v_str']=''.$v;
      $n=strtolower(str_replace('.','-',''.$v));

    break;
    default:
      msg('error-internal','EXA-parsing failed: set_row_literal called with invalid n_def');

  }

    
  $row['n_def']=$n_def;  
  $row['n']="{$n_def}--{$n}";  
  $row['is_literal']=1;
  $row['p']='e';  
  $row['e']=$n_def;	    
  $row['r']='e';
  $row['l']='usage';
}



//-----------------------------
function inc_lot_num($n)
{
  dbs::exec("UPDATE exa_e
  SET lot_num_max=(@lmx:=lot_num_max+1)
  WHERE n='b' AND is_now=1;");
  return dbs::value('SELECT @lmx;');
}



//------------------------------------------------------
function set_row_from_db(&$row,$row_db)
{
  $row['n']=$row_db['n'];
  $row['n_def']=$row_db['n_def'];

  if(isset($row_db['id']))
  {
    $row['id']=$row_db['id'];
    $row['n_def_non_alias']=$row_db['n_def_non_alias'];
    $row['p']=$row_db['p'];
    $row['e']=$row_db['e'];
    $row['r']=$row_db['r'];
    $row['l']=$row_db['l'];
      
    $row['c_min']=$row_db['c_min'];
    $row['c_max']=$row_db['c_max'];
    $row['is_optional']=$row_db['is_optional'];
    $row['is_plural']=$row_db['is_plural'];
    $row['is_inf']=$row_db['is_inf'];
    $row['c_sub']=$row_db['c_sub'];
    $row['n_sub1']=$row_db['n_sub1'];
    $row['n_sub2']=$row_db['n_sub2'];
    $row['n_sub3']=$row_db['n_sub3'];
    $row['c_str']=$row_db['c_str'];
    $row['v_int']=$row_db['v_int'];
    $row['v_float']=$row_db['v_float'];
    $row['v_str']=$row_db['v_str'];
    $row['v_cl']=$row_db['v_cl'];
    $row['v_cl_year']=$row_db['v_cl_year'];

    if(isset($row_db['is_now']))
    {
      // those values are not set if row is not from db but resolve-cache
      $row['is_now']=$row_db['is_now'];
      $row['is_magic']=$row_db['is_magic'];
      $row['is_public']=$row_db['is_public'];
      $row['n_u_cr']=$row_db['n_u_cr'];
      $row['cl_cr']=$row_db['cl_cr'];
      $row['modification']=$row_db['modification'];
      $row['cl_mod']=$row_db['cl_mod'];
      $row['id_x_mod']=$row_db['id_x_mod'];
      $row['n_u_mod']=$row_db['n_u_mod'];
      $row['privacy_see']=$row_db['privacy_see'];
      $row['privacy_mod']=$row_db['privacy_mod'];
      $row['privacy_del']=$row_db['privacy_del'];
      $row['privacy_lot_see']=$row_db['privacy_lot_see'];
      $row['privacy_lot_mod']=$row_db['privacy_lot_mod'];
      $row['privacy_lot_del']=$row_db['privacy_lot_del'];
    }
  }
}


// -----------------------------------------------------------
// DETAILED-LEVEL Resolving of X starts 
// -----------------------------------------------------------

$c_op=count($GLOBALS['op']);

for($j=0;$j<$c_op;$j++)
{
  $row=&$GLOBALS['op'][$j];
  include './php/api_09_x_op.php';
}



?>