<?php

/**
 *  EXALOT digital language for all agents
 *
 *  api_10_in_e.php includes functions all about manifesting entities
 * 
 *  @see <http://exalot.com>
 *  
 *  @author  Ernesto Sun <contact@ernesto-sun.com>
 *  @version 20150112-eto
 *  @since 20150112-eto
 * 
 *  @copyright (C) 2014-2015 Ing. Ernst Johann Peterec <http://ernesto-sun.com>
 *  @license AGPL <http://www.gnu.org/licenses/agpl.txt>
 *
 *  EXALOT is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  EXALOT is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with EXALOT. If not, see <http://www.gnu.org/licenses/agpl.txt>.
 *
 */



/**
 *
*/


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
id_in_mod,   
n_u_mod,
is_limited,
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
v_cl_year,
is_plural_fo,
c_min_fo,
c_max_fo)VALUES";



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
e,
l,
cl_cr,
n_u_cr,
cl_mod,
modification,
id_in_mod,
n_u_mod)VALUES";




//----------------------------------------
function insert_row($n,$id_in)
{
   $row = &get_row_e_by_n($n);
   
    if(isset($row['r_p']))
    {
        // a p is best to be inserted before, because used in the next statement
        insert_row($row['r_p']['n'],$id_in);
    }
   
   
   $row['id']=insert("{$GLOBALS['temp']['sql_base_e']}(
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
  '{$GLOBALS['n-u']}',
  NOW(),
  'create',
  NOW(),
  {$id_in},
  '{$GLOBALS['n-u']}',
  '{$row['is_limited']}', 
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
  {$row['v_cl_year']},
  {$row['is_plural_fo']},
  {$row['c_min_fo']},
  {$row['c_max_fo']})");
  
 
    if($row['c_sub']>0&&$row['l']!='alias')
    {
        $sql_sub=$GLOBALS['temp']['sql_base_sub'];
        $dummy=''; 

        $subl='';
        if(!isset($row['subl']))
        {
            $subl="''";
        }
        
        for($j=1;$j<=$row['c_sub'];$j++)
        { 
	  $sql_sub.="{$dummy}(
	  '{$row['n']}',
	  {$j},
	  '{$row['sub'][$j]['n']}',
	  1,
	  {$row['sub'][$j]['c_min']},
	  {$row['sub'][$j]['c_max']},
	  {$row['sub'][$j]['is_optional']},
	  {$row['sub'][$j]['is_plural']},
	  {$row['sub'][$j]['is_inf']},
	  '{$row['sub'][$j]['e']}',
	  '{$row['sub'][$j]['l']}',
	  NOW(),
	  '{$GLOBALS['n-u']}',
	  NOW(),
	  'create',
	  {$id_in},
	  '{$GLOBALS['n-u']}')";        

          $dummy=',';
          
          if(!empty($subl))$subl.=",'{$row['sub'][$j]['n']}'";
        }    
        
        db_exec($sql_sub);
            
        if(!empty($subl))
        {
            $row['subl']=$subl;
        }
        
        if(!$row['is_usage_alias'])
        {
            db_exec("INSERT INTO {$GLOBALS['pre']}subl(n,n_def,c_sub,tx) VALUES(
          '{$row['n']}',
          '{$row['n_def']}',
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
          db_exec("INSERT INTO {$GLOBALS['pre']}sup(n,c_root,c_leaf,n_sup) VALUES(
          '{$row['n']}',
          {$k},
          {$c_leaf},
          '{$row['sup'][$k]}')");
        }
    }  
    else msg('error-internal','sup was not created for row to insert');
        
    return $row['id'];
  
}



//-----------------------------
function rename_entity($n_before,$n,$id_in)
{
    // TODO: Attention!! Works only for usages, no changes in n_def or sup needed 
    insert("INSERT INTO {$GLOBALS['pre']}rename(n_old,n_new,is_now,cl_in,id_in_create) VALUES ('{$n_before}','{$n}',1,NOW(),'{$id_in}')");

    db_exec("UPDATE {$GLOBALS['pre']}e SET n='{$n}' WHERE n='{$n_before}' ");
    
    db_exec("UPDATE {$GLOBALS['pre']}e SET n_sub1='{$n}' WHERE n_sub1='{$n_before}' ");
    db_exec("UPDATE {$GLOBALS['pre']}e SET n_sub2='{$n}' WHERE n_sub2='{$n_before}' ");
    db_exec("UPDATE {$GLOBALS['pre']}e SET n_sub3='{$n}' WHERE n_sub3='{$n_before}' ");
    
    db_exec("UPDATE {$GLOBALS['pre']}tx SET n='{$n}' WHERE n='{$n_before}' ");

    db_exec("UPDATE {$GLOBALS['pre']}sub SET n='{$n}' WHERE n='{$n_before}' ");
    db_exec("UPDATE {$GLOBALS['pre']}sub SET n_sup='{$n}' WHERE n_sup='{$n_before}' ");
    
    db_exec("UPDATE {$GLOBALS['pre']}subl SET n='{$n}' WHERE n='{$n_before}' ");
    db_exec("UPDATE {$GLOBALS['pre']}subl SET n_def='{$n}' WHERE n_def='{$n_before}' ");
 
    db_exec("UPDATE {$GLOBALS['pre']}subl SET tx=REPLACE(tx,'\\'{$n_before}\\'','\\'{$n}\\'') WHERE tx LIKE '%\\'{$n_before}\\'%' ");
    
    db_exec("UPDATE {$GLOBALS['pre']}sup SET n='{$n}' WHERE n='{$n_before}' ");
    db_exec("UPDATE {$GLOBALS['pre']}sup SET n_sup='{$n}' WHERE n_sup='{$n_before}' ");

    db_exec("UPDATE {$GLOBALS['pre']}u_e SET n_e='{$n}' WHERE n_e='{$n_before}' ");

    db_exec("UPDATE {$GLOBALS['pre']}in SET n_e='{$n}' WHERE n_e='{$n_before}' ");
}


//-----------------------------
function manifest_e_usage(&$in,$id_in)
{
    //this comes from literal, e_usage, def_e_usage, path-resolution, context
    
    if(isset($in['r_def']))
    {
        if(isset($in['r']))
        {
           if($in['r']['id']>0)
           {
               // the entity existed already and was loaded by semantic (e_name, context)
               $in['id_e']=$in['r']['id'];
               $in['n_e']=$in['r']['n'];
               return;
           }
           
            //here the entity is def_e_usage, it was matched already by semantic 
           if(isset($in['id_e']))
           {
               // definition over old usage, equals a rename               
               $in['id_e']=rename_entity($in['n_e_before'], $in['v'],$id_in);
               $in['n_e']=$in['v'];
               return;
           }
           else
           {
               // new definition
               $in['id_e']=insert_row($in['r']['n'],$id_in);
               $in['n_e']=$in['r']['n'];
               return; 
           }
        }
        else
        {
            // Here the entity is e_usage, it matches the definition, but no row is created yet. By now all sub-id's must be given. If not, it must be a manually created entity.
            $subl="''";
            for($j=1;$j<=$in['c_sub'];$j++)
            {
                if(!isset($in['sub'][$j]['n_e']))
                {
                    manifest_e_usage($in['sub'][$j],$id_in);
                }
                $subl.=",'{$in['sub'][$j]['n_e']}'";
            }
            
            list($id_e,$n_e)=find_idn_by_subl($in['r_def']['n_def_non_alias'],$subl,$in['c_sub']);

            if($id_e>0)
            {
               // found it
               $in['id_e']=$id_e;
               $in['n_e']=$n_e;
               return; 
            }
            
            //this is a new usage, create the row and write it

            if($in['r_def']['is_limited'])
            {
              if($in['r_def']['n_u_cr']!=$GLOBALS['n-u'])
              {
                msg('error-semantic','EXA-resolving failed: new entity of \''.$in['r_def']['n'].'\' is limited by the creator user.',$GLOBALS['in'][$i]);
              }
            }
            
            
            $in['r']=$GLOBALS['row-0'];
            
            $in['r']['subl']=$subl;
            
            $in['r']['n']=$in['v'];
            $in['r']['n_def']=$in['r_def']['n'];

            $in['r']['p']=$in['r_def']['p'];
            $in['r']['e']=$in['r_def']['e'];
            $in['r']['r']=$in['r_def']['r'];
            $in['r']['l']='usage';

            $in['r']['n_def_non_alias']=$in['r_def']['n_def_non_alias'];
            
	    $in['r']['c_sub']=$in['c_sub'];

            for($j=1;$j<=$in['c_sub'];$j++)
            {
                $n=$in['sub'][$j]['n_e'];
                if($j<4)
                {
                    if($j==1)$in['r']['n_sub1']=$n;
                    elseif($j==2)$in['r']['n_sub2']=$n;
                    elseif($j==3)$in['r']['n_sub3']=$n;
                }
                
                if(!isset($in['sub'][$j]['r']))
                {
                    $in['sub'][$j]['r']=&get_row_e_by_n($n);
                }
                
                $in['r']['sub'][]=array('n_sup'=>$r['n'],
                    'i'=>$j,
                    'n'=>$n,
                    'c_min'=>$in['sub'][$j]['r']['c_min'],
                    'c_max'=>$in['sub'][$j]['r']['c_max'],
                    'is_optional'=>$in['sub'][$j]['r']['is_optional'],
                    'is_plural'=>$in['sub'][$j]['r']['is_plural'],
                    'is_inf'=>$in['sub'][$j]['r']['is_inf'],
                    'e'=>$in['sub'][$j]['r']['e'],
                    'l'=>$in['sub'][$j]['r']['l']);
            }
            
            if(!isset($in['r_def']['sup']))
            {
              $in['r_def']['sup']=idvlist("SELECT c_root,n_sup FROM {$GLOBALS['pre']}sup
              WHERE n='{$in['r_def']['n']}' 
              ORDER BY c_root",'c_root','n_sup');
            }
            $r['sup']=$in['r_def']['sup'];
            $r['sup'][]=$in['r_def']['n'];
            
            $GLOBALS['data-new'][$in['r']['n']]=&$in['r'];
            
            $in['id_e']=insert_row($in['r'],$id_in);
            $in['n_e']=$in['r']['n'];
        }
    }
    else
    {
        switch($in['y'])
        {
            case 'b': //---------------------------------------------------------
                $in['n_e']='b';
                $in['id_e']=$GLOBALS['id-e-b']; 
            break;  
            case 'x':
                $in['n_e']='x';
                $in['id_e']=$GLOBALS['id-e-x']; 
            break;  
            case 'noo':
                $in['n_e']='noo';
                $in['id_e']=$GLOBALS['id-e-noo']; 
            break;  
            case 'cl':
              if(!isset($in['id_e'])||$in['id_e']<1)
              {
                 if(!isset($in['n_e']))$in['n_e']=$in['v']['n'];   
                 $in['id_e']=ensure_literal_cl($in['n_e'],$in['v']['year'],$in['v']['cl'],$in['v']['ms'],$id_in);
              }
            break;
            case 's':
              if(!isset($in['id_e'])||$in['id_e']<1)
              {
                list($in['id_e'],$in['n_e'])=ensure_literal_s($in['v'],$in['s'],$in['c_str'],$id_in);
              }
            break;
            case 'int':
              if(!isset($in['id_e'])||$in['id_e']<1)
              {
                 if(!isset($in['n_e']))$in['n_e']='int-'.$in['v'];   
                 $in['id_e']=ensure_literal_int($in['n_e'],$in['v'],$id_in);
              }
            break;
            case 'float':
              if(!isset($in['id_e'])||$in['id_e']<1)
              {
                 if(!isset($in['n_e']))$in['n_e']='float-'.strtolower(str_replace('.','-',$in['v']));   
                 $in['id_e']=ensure_literal_float($in['n_e'],$in['v'],$id_in);
              }
             break;
            default:
              msg("error-internal","manifest_e_usage got a invalid case");  
            break;
        }
    }
}


//-----------------------------
function inc_lot_num($n)
{
  db_exec("UPDATE exa_e
  SET lot_num_max=(@lmx:=lot_num_max+1)
  WHERE n='{$n}' AND is_now=1;");
  return value('SELECT @lmx;');
}


//----------------------------------------
function ensure_literal_s($v,$s,$c_str,$id_in)
{
    $row_s=array();
    if($c_str<256)
    {
        $row_s=singlerow("SELECT id,n FROM {$GLOBALS['pre']}e 
        WHERE is_now=1 
        AND n_def='s' 
        AND c_str={$c_str}  
        AND v_str=\"{$s}\" ");
    }
    else
    {
        $row_s=singlerow("SELECT id_e AS id,n FROM {$GLOBALS['pre']}tx 
        WHERE is_now=1 
        AND c_str={$c_str}  
        AND tx=\"{$s}\" ");
    }

    if(count($row_s))
    {
      return array($row_s['id'],$row_s['n']);
    }

    $n='s-'.inc_lot_num('s');

    $id=insert("{$GLOBALS['temp']['sql_base_e']}(
'{$n}',
's',
's',
'e',
's',
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
{$id_in},
'{$GLOBALS['conf']['n-u-exalot']}',
0,
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
{$c_str},
0,
'0.0',
\"{$v}\",
'',
'',
0,
0,
0)");	  

    if($row['c_str']>255)
    {
      db_exec("INSERT INTO {$GLOBALS['pre']}tx
(id_e,n,is_now,tx,c_str)VALUES(
{$id},
'{$n}',
1,
\"{$s}\",
{$c_str})");
    }

    return array($id,$n);
}



//-----------------------------
function ensure_literal_cl($n,$year,$cl,$ms,$id_in)
{
    $id=value("SELECT id FROM {$GLOBALS['pre']}e 
     WHERE is_now=1 
     AND n_def='cl' 
     AND v_int={$ms} 
     AND v_cl_year={$year} 
     AND v_cl=\"{$cl}\" ");   
    
    if($id)
    {
      return $id;
    }

    $id=insert("{$GLOBALS['temp']['sql_base_e']}(
'{$n}',
'cl',
'cl',
'e',
'cl',
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
{$id_in},
'{$GLOBALS['conf']['n-u-exalot']}',
0,
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
0,
{$ms},
'0.0',
'',
'{$cl}',
'{$year}',
0,
0,
0)");	  
  
    return $id;
}


//-----------------------------
function ensure_literal_int($n_e,$v,$id_in)
{
    $id=value("SELECT id FROM {$GLOBALS['pre']}e 
     WHERE is_now=1 
     AND n_def='int' 
     AND v_int={$v}");   
    
    if($id)
    {
      return $id;
    }

    $id=insert("{$GLOBALS['temp']['sql_base_e']}(
'{$n}',
'int',
'int',
'e',
'int',
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
{$id_in},
'{$GLOBALS['conf']['n-u-exalot']}',
0,
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
0,
{$v},
'{$v}',
'',
'',
'',
0,
0,
0)");	  
  
    return $id;
    
}



//-----------------------------
function ensure_literal_float($n_e,$v,$id_in)
{
    $id=value("SELECT id FROM {$GLOBALS['pre']}e 
     WHERE is_now=1 
     AND n_def='int' 
     AND v_float='{$v}'");   
    
    if($id)
    {
      return $id;
    }

    $v_int=(int)$v;
    
    $id=insert("{$GLOBALS['temp']['sql_base_e']}(
'{$n}',
'int',
'int',
'e',
'int',
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
{$id_in},
'{$GLOBALS['conf']['n-u-exalot']}',
0,
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
0,
{$v_int},
'{$v}',
'',
'',
'',
0,
0,
0)");	  
  
    return $id;
    
}

