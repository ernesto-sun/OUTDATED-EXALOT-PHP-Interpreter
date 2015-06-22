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


$GLOBALS['data-new-done']=array();

for($i=1;$i<=$GLOBALS['row']['c_sub'];$i++)
{ 
  $GLOBALS['context']['i-xin']=$i;
  $GLOBALS['row']['sub'][$i]['id_x_parent']=0;
  manifest_x($GLOBALS['row']['sub'][$i]);
}

// ------------------------------------------- make system operations

foreach($GLOBALS['op-sys'] as $op_sys)
{ 
  switch($op_sys['op'])
  {
    case 'limited':
      dbs::exec("UPDATE {$GLOBALS['pre']}e SET 
      is_limited=1 
      WHERE n='{$op_sys['e']}' 
      AND is_now=1");  
    break;
  }
}





?>