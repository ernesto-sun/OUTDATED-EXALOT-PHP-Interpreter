<?php

if(!$is_api_call)die("X");

$ok_in_id_list=dbs::value("COUNT(n_g)
FROM {$GLOBALS['pre']}g 
WHERE n_g IN (SELECT n_g FROM {$GLOBALS['pre']}con_g WHERE id_con={$con} 
AND id_list_is_valid=1
AND '{$GLOBALS['context']['n-u']},' IN (id_list)");

if($ok_in_id_list>0)
{
    //ok
      $okCon=1;
}
else
{
  // this is a slower process to 
  $g_data=dbs::value("SELECT CONCAT(' OR (',g_tx.sql_include,' AND NOT (',g_tx.sql_exclude,')) ' AS q,
    g.n_g AS n_g,
    g.id_list_cl_lastcalc AS lastcalc,
    g.id_list_i_dirty AS i_dirty
    FROM {$GLOBALS['pre']}g AS g 
    INNER JOIN {$GLOBALS['pre']}g_tx AS g_tx ON (g.n_g=g_tx.n_g) 
    WHERE n_g IN (SELECT n_g FROM {$GLOBALS['pre']}con_g WHERE id_con={$con} 
    AND id_list_is_valid=0");

  foreach $g_data as $g_row
  {
    if(dbs::value("SELECT 1 FROM {$GLOBALS['pre']}u AS u 
    WHERE u.n_u='{$GLOBALS['context']['n-u']}' AND (1=0 {$g_row['q']})"))
    {
      $okCon=1;
      // ok
      break;
    }
  }
  
  if(!$okCon)
  {
    msg("error-unauthorized","invalid login","user is not allwed in no group at uu-conversation");
  }

  dbs::insert("INSERT INTO {$GLOBALS['pre']}uu_u(
  id_con,
  n_u,
  is_accepted,
  cl_join,
  cl_st_first,
  cl_st_last,
  cl_leave
  is_present) VALUES (
  {$con},
  '{$GLOBALS['context']['n-u']}',
  1,
  NOW(),
  NOW(),
  NOW(),
  NOW(),
  1)");
  
  
  // TODO: recalculate id_list and dirty-setting

?>