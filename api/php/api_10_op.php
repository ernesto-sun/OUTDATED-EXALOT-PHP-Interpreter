<?php
if(!$is_api_call)die('X');




$GLOBALS['temp']['sql_base_e']="INSERT INTO {$GLOBALS['pre']}e(
n,
n_def,
n_def_non_alias,
p,
e,
r,
l,
is_now,
is_magic,
is_public,
c_min,
c_max,
is_optional,
is_plural,
is_inf,
n_u_cr,
cl_cr,
modification,
cl_mod,
id_x_mod,   
n_u_mod,
privacy_see, 
privacy_mod, 
privacy_del, 
privacy_lot_see, 
privacy_lot_mod, 
privacy_lot_del,
c_sub,
n_sub1,
n_sub2,
n_sub3,
c_str,
v_int,
v_float,
v_str,
v_cl,
v_cl_year)VALUES";



$GLOBALS['temp']['sql_base_x']="INSERT INTO {$GLOBALS['pre']}x(
n_u,
n_u_client,
id_ses,
id_con,
id_st,
i,
n,
n_def,
n_def_non_alias,
id_x,
i_x,
x,
y,
depth,
path)VALUES";




$GLOBALS['temp']['sql_base_sub']="INSERT INTO {$GLOBALS['pre']}sub(
n_sup,
i,
n,
is_now,
c_min,
c_max,
is_optional,
is_plural,
is_inf,
cl_cr,
n_u_cr,
cl_mod,
modification,
id_x_mod,
n_u_mod)VALUES";


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

for($i=0;$i<$c_op;$i++)
{
  switch($GLOBALS['op'][$i]['y'])
  {
    case 'alias':
    case 'def_e':
    case 'def-f':
    case 'def-e-usage':
      // here row and sub are prepared 
  
  
    break;
  
    case 'e':
      echo "\n\r<br/>OP: match e: {$row['n']}<br/>/n/r";
      // pretty all matching work was done in x_exp already
      // here know find the usage and prepare for manifestation
      // 
      // we know already:
      //
      // * the definition exists and privacy setting allows to see/use
      // * the number of sub-entities matches the definition
      // * the type of all sub-entities match the definitions
      // * all sub-entities are matched or calculated already (or variable)
      // * this is no system-entity like limited or privacy, that is done in exp and op-sys
      // * this is no literal or n
      // * variables are in the var-array and context is available as literals/n 
      //
      // missing:
      //
      // * see if this usage was defined already
      // * if not existing, define a new usage-name  
      // * condition-check
      // * performing the not and notnot
      // * saving in var
      
    break;
    case 'f':
      echo "\n\r<br/>OP: calc f: {$row['n']}<br/>/n/r";
      // we know already:
      //
      // * the definition exists and privacy setting allows to see/use
      // * the function has correct input-types
      // * In case of a plural function the inputs are wrapped with p
      // * The output has the correct type (if not top-function)
      // * all inputs are calculated and pointing either to e or op
      // * variables are in the var-array and context is available as literals/n 
      //  
      // missing:
      //
      // * see if this f-usage was defined already
      // * if not existing, define a new f-usage
      // * see if the f is magic (include magic in case)
      // * find the right op for the input
      // * see if the op is magic (include magic in case)
      // * see if for the op an sql-command is stored (v_str)
      // * if sql-command is stored, make replacement: strtr($str,array) 
      // * else, construct SQL (if possible) and store (v_str)
      // Note: the replacement-value can be a literal, n,  a non-literal, or a SQL-fragment
      // * if sql is not possible execute php-function (before execute all sub-SQL waiting in case)
      // * if sql was stored or constructed DO NOT EXECUTE SQL but give it as op-result 
      // Note: SQL-commands can only have a max-size of 255 chars
      // * condition-check
      // * performing the not and notnot
      // * saving in var
      
    break;
    case 'p':
      echo "\n\r<br/>OP: match p: {$row['n']}<br/>/n/r";
    break;
  }
}



// go trough x and manifest

//--------------------------------------
function manifest_x(&$row)
{
  $context=$GLOBALS['context'];
   
  $row['id_x']=dbs::insert("{$GLOBALS['temp']['sql_base_x']}(
  '{$context['n-u']}',
  '{$context['n-u-client']}',
  {$context['id-ses']},
  {$context['id-con']},
  {$context['id-st']},
  {$context['i-xin']},
  '{$row['n']}',
  '{$row['n_def']}',
  '{$row['n_def_non_alias']}',
  {$row['id_x_parent']},
  {$row['i_x']},
  'in',
  'new',
  {$row['depth']},
  '{$row['x_path']}')");
  
  
  
  switch($row['e'])
  {
    case 's':
      if($row['id']>0)
      {
	return; // the string is created already
      }

      $e='s';
      $row_s=array();
      if($row['c_str']<256)
      {
	$row_s=dbs::value("SELECT id,n FROM {$GLOBALS['pre']}e 
	WHERE is_now=1 
	AND n_def='s' 
	AND c_str={$row['c_str']}  
	AND v_str=\"{$row['v_str']}\" ");
      }
      else
      {
	$row_s=dbs::value("SELECT id_e AS id,n FROM {$GLOBALS['pre']}tx 
	WHERE is_now=1 
	AND c_str={$row['c_str']}  
	AND tx=\"{$row['v_str']}\" ");
	$e='tx';
      }
      
      if(count($row_s))
      {
	$row['sub'][$j]['id']=$row_s['id'];
	$row['sub'][$j]['n']=$row_s['n'];
	$row['sub'][$j]['n_def']='s';
      }
      else
      {
      
	//create new s
	$row['id']=dbs::insert("{$GLOBALS['temp']['sql_base_e']}(
'',
's',
's',
'e',
'{$e}',
'e',
'usage',
1,
1,
1,
1,
1,
0,
0,
0,
'{$GLOBALS['conf']['n-u-exalot']}',
NOW(),
'create',
NOW(),
{$row['id_x']},
'{$GLOBALS['conf']['n-u-exalot']}',
'public',
'private',
'private',
'private',
'private',
'private',
0,
'',
'',
'',
{$row['c_str']},
0,
'0.0',
\"{$row['v_str']}\",
'{$row['v_cl']}',
{$row['v_cl_year']})");	  

	  $row['n']="s--{$row['id']}";

	  dbs::exec("UPDATE {$GLOBALS['pre']}e SET n='{$row['n']}' WHERE id={$row['id']}");  
	  
	  if($row['c_str']>255)
	  {
	    dbs::exec("INSERT INTO {$GLOBALS['pre']}tx
	    (id_e,n,is_now,tx,c_str)VALUES(
{$row['id']},
'{$row['n']}',
1,
\"{$row['tx']}\",
{$row['c_str']})");
	  
	  }

	}
    break;
    default:
    
      if($row['p']=='p')
      {

	$n_single=$row['n_sub1'];
	if(empty($n_single))$n_single=$row['n_def'];
	$n_p='p-{$n_single}';
	      
	$exist_p=dbs::value("SELECT 1 
	FROM {$GLOBALS['pre']}e 
	WHERE is_now=1 AND n_def='p' AND n='{$n_p}'");    

	if(!$exist_p)
	{
	  dbs::exec("{$GLOBALS['temp']['sql_base_e']}(
  '{$n_p}',
  'p',
  'p',
  'p',
  'p',
  'p',
  'def',
  1,
  1,
  1,
  0,
  0,
  1,
  1,
  1,
  '{$GLOBALS['conf']['n-u-exalot']}',
  NOW(),
  'create',
  NOW(),
  {$row['id_x']},
  '{$GLOBALS['conf']['n-u-exalot']}',
  'public',
  'private',
  'private',
  'private',
  'private',
  'private',
  0,
  '{$n_single}',
  '',
  '',
  0,
  0,
  '0.0',
  0,
  '{$row['v_cl']}',
  {$row['v_cl_year']})");	 
	  
	}
      }

    $create=0;
    if($row['id']==0)
    {
      if(isset($GLOBALS['data-new-done'][$row['n']]))
      {
	$row['id']=$GLOBALS['data-new-done'][$row['n']]['id'];
      }
      else
      {
	$create=1;
      }
    }

    
    echo "<br/>Manifesting: ['{$row['n_def']}','{$row['n']}'] ",($create?'new':'')," <br/>\n\r";
    
    $create_sub=($create&&$row['c_sub']>0);
    
    if($create_sub)
    {
      $row['subl']="''"; //rebuild of subl since syntax  
      $sql_sub=$GLOBALS['temp']['sql_base_sub'];
    }

    if($row['l']=='usage'||$create)
    {
      // ignore sub if definition is known
	
      for($j=1;$j<=$row['c_sub'];$j++)
      { 
	$row['sub'][$j]['id_x_parent']=$row['id_x'];
	manifest_x($row['sub'][$j]);

	if($create_sub)
	{
	  $row_sub=$row['sub'][$j];
	  
	  $row['subl'].=",'{$row_sub['n']}'";
	  
	  if($j>1)$sql_sub.=',';
	  
	  $sql_sub.="(
	  '{$row['n']}',
	  {$j},
	  '{$row_sub['n']}',
	  1,
	  {$row_sub['c_min']},
	  {$row_sub['c_max']},
	  {$row_sub['is_optional']},
	  {$row_sub['is_plural']},
	  {$row_sub['is_inf']},
	  NOW(),
	  '{$context['n-u']}',
	  NOW(),
	  'create',
	  {$row_sub['id_x']},
	  '{$context['n-u']}')";
	}
      }
    }
    
    
    if($create)
    {
      // here all sub-entities must be created or asigend
      if($create_sub)dbs::exec($sql_sub);
      
      $row['id']=dbs::insert("{$GLOBALS['temp']['sql_base_e']}(
  '{$row['n']}',
  '{$row['n_def']}',
  '{$row['n_def_non_alias']}',
  '{$row['p']}',
  '{$row['e']}',
  '{$row['r']}',
  '{$row['l']}',
  1,
  0,
  1,
  {$row['c_min']},
  {$row['c_max']},
  {$row['is_optional']},
  {$row['is_plural']},
  {$row['is_inf']},
  '{$GLOBALS['context']['n-u']}',
  NOW(),
  'create',
  NOW(),
  {$row['id_x']},
  '{$GLOBALS['context']['n-u']}',
  'public',
  'private',
  'private',
  'private',
  'private',
  'private',
  '{$row['c_sub']}',
  '{$row['n_sub1']}',
  '{$row['n_sub2']}',
  '{$row['n_sub3']}',
  {$row['c_str']},
  {$row['v_int']},
  '{$row['v_float']}',
  \"{$row['v_str']}\",
  '{$row['v_cl']}',
  {$row['v_cl_year']})");

      if($row['c_str']>255)
      {
	dbs::exec("INSERT INTO {$GLOBALS['pre']}tx(id_e,n,is_now,tx,c_str) VALUES(
	{$row['id']},
	'{$row['n']}',
	1,
	'{$row['tx']}',
	'{$row['c_str']}')");
      }

      if($row['l']!='alias')
      {
	if(!$row['is_usage_alias'])
	{
	  dbs::exec("INSERT INTO {$GLOBALS['pre']}subl(n,n_def,n_sub1,n_sub2,n_sub3,c_sub,tx) VALUES(
    '{$row['n']}',
    '{$row['n_def']}',
    '{$row['n_sub1']}',
    '{$row['n_sub2']}',
    '{$row['n_sub3']}',
    '{$row['c_sub']}',
    \"{$row['subl']}\")");
	}
      }
      
    
    if(isset($row['sup']))
    {
 	$c_sup=count($row['sup']);
	for($k=1;$k<=$c_sup;$k++)
	{
	  $c_leaf=$c_sup-$k+1;
	  dbs::exec("INSERT INTO {$GLOBALS['pre']}sup(n,c_root,c_leaf,n_sup) VALUES(
	  '{$row['n']}',
	  {$k},
	  {$c_leaf},
	  '{$row['sup'][$k]}')");
	}
    }
    
    $GLOBALS['data-new-done'][$row['n']]=&$row;
    } // end if create

    break;
  } // end select on n
  
  dbs::exec("UPDATE {$GLOBALS['pre']}x SET 
y='ok', 
id_e={$row['id']} 
WHERE id={$row['id_x']}");  

}






?>