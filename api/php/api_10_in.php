<?php
if(!$is_api_call)die('X');



$GLOBALS['temp']['sql_base_in']="INSERT INTO {$GLOBALS['pre']}in(
y,
v,
s,
c_str,
c_min,
c_max,
c_sub,
path,
is_not,
is_notnot,
is_exp,
is_contextual,
i,
x_depth,
x_path,
i_top,
id_e,
n_e,
n_u,
n_u_agent,
id_ses,
id_con,
id_st,
id_in_sup)VALUES";

// -----------------------------------------------------------
// DETAILED-LEVEL Resolving of INPUT starts 
// -----------------------------------------------------------


include('php/api_10_in_e.php');

if($GLOBALS['temp']['i-context'])
{
  include('php/api_10_context.php');
}

//if($GLOBALS['temp']['i-path'])
//{
//  include('php/api_10_path.php');
//}
//
//if($GLOBALS['temp']['i-sys'])
//{
//  include('php/api_10_sys.php');
//}




for($i=1;$i<=$GLOBALS['in-i'];$i++)
{
 
 print_r($GLOBALS['in'][$i]);
    
    
  $id_e=0;  
  $n_e='';
  
  $v=$GLOBALS['in'][$i]['v'];
  $y=$GLOBALS['in'][$i]['y'];
  $s='';
  $c_str=0;
  
  if($y=='s')
  {
    $s=$GLOBALS['in'][$i]['s'];
    $c_str=$GLOBALS['in'][$i]['c_str'];
  }
  
  $c_min=isset($GLOBALS['in'][$i]['c_min'])?$GLOBALS['in'][$i]['c_min']:'0';
  $c_max=isset($GLOBALS['in'][$i]['c_max'])?$GLOBALS['in'][$i]['c_max']:'0';

  $is_not=isset($GLOBALS['in'][$i]['is_not'])?'1':'0';
  $is_notnot=isset($GLOBALS['in'][$i]['is_notnot'])?'1':'0';

  $is_contextual=isset($GLOBALS['in'][$i]['is_contextual'])?'1':'0';
  
  $is_exp=isset($GLOBALS['in'][$i]['is_exp'])?'1':'0';
  
  $is_path=isset($GLOBALS['in'][$i]['path']);
  $path=$is_path?$GLOBALS['in'][$i]['path']:'';
  
  $is_optional=isset($GLOBALS['in'][$i]['is_optional']);
  $is_plural=isset($GLOBALS['in'][$i]['is_plural']);
  $is_inf=isset($GLOBALS['in'][$i]['is_inf']);
  
  $c_sub=$GLOBALS['in'][$i]['c_sub'];
  
  
  $id_in=$GLOBALS['in'][$i]['id_in']=insert("{$GLOBALS['temp']['sql_base_in']}(
  '{$y}',
  '{$v}',
  '{$s}',
  '{$c_str}',
  '{$c_min}',
  '{$c_max}',
  '{$c_sub}',
  '{$path}',
  '{$is_not}',
  '{$is_notnot}',
  '{$is_exp}',
  '{$is_contextual}',
  '{$GLOBALS['in'][$i]['i']}',
  '{$GLOBALS['in'][$i]['depth']}',
  '{$GLOBALS['in'][$i]['x_path']}',
  '{$GLOBALS['in'][$i]['x_i']}',
  '{$id_e}',
  '{$n_e}',
  '{$GLOBALS['n-u']}',
  '{$GLOBALS['n-u-agent']}',
  '{$GLOBALS['id-ses']}',
  '{$GLOBALS['id-con']}',
  '{$GLOBALS['id-st']}',
   0)");
   
  $depth_sub=$GLOBALS['in'][$i]['depth']+1;
  db_exec("UPDATE {$GLOBALS['pre']}in SET id_in_sup={$id_in} WHERE id_st={$GLOBALS['id-st']} AND i_top={$GLOBALS['in'][$i]['x_i']} AND x_depth={$depth_sub}");
  
  
  if($c_sub==0)
  {
    // --------------------------------------- ---------------------------------
    // --------------------------------------- SINGLE BEGIN --------------------
    // --------------------------------------- ---------------------------------
    // can be: def_sub_e, def_sub_p, context, var_get, b, x, noo, s, cl, int, float, e-name, 
    // only in op: p, f-name 
    
    switch($y)
    {
      case 'alias': //---------------------------------------------------------
        // row is prepared to be inserted

          $n_e=$v;  
          $id_e=insert_row($n_e,$id_in);  
          
      break;
      case 'p': //---------------------------------------------------------
        // this only happens in op, second parameter (by syntax-check)
        // do nothing
        // also n_def is not set here
      break;
      case 'f-name': //---------------------------------------------------------
        // this only happens in op, first parameter (by syntax-check)
        // function must be a new-defined one in this statement
	// no setting of n_def needed, only used in op
      break;
      case 'b': //---------------------------------------------------------
      case 'x':
      case 'noo':
      case 's': //---------------------------------------------------------
      case 'cl':
      case 'int':
      case 'float':
           manifest_e_usage($GLOBALS['in'][$i],$id_in);
           $id_e=$GLOBALS['in'][$i]['id_e'];
           $n_e=$GLOBALS['in'][$i]['n_e'];
          
      break;
      case 'def_sub_p': //---------------------------------------------------------
       // Note: check for sub-entities is done inside def_e_usage
       // Nothing else to do here
      break;
      case 'context': //--------------------------------------------------------- 
        // include context-resolution
          list($id_e,$n_e)=ensure_context($GLOBALS['in'][$i],$id_in);
      break; 
      case 'var_get': //---------------------------------------------------------
        // read variable

          $in_val=&$GLOBALS['in'][$i]['var_top']['var'][$v]['in'];
          $id_e=$in_val['id_e'];
          $n_e=$in_val['n_e'];
      break; 
      case 'e_name':
      case 'def_sub_e': //---------------------------------------------------------
         $n_e=$v; 
         $id_e=$GLOBALS['in'][$i]['r']['id'];  // was read by semantic already, id must be set already for new entities even
      break; 
      default:
	msg('error-internal','invalid case in single entity',$GLOBALS['in'][$i]);
      break;
    }
    
    if($is_path)
    {
        if(isset($GLOBALS['in'][$i]['in_path']['id_e']))
        {
            // entity was read by semantic-context already
            $id_e=$GLOBALS['in'][$i]['in_path']['id_e'];
            $n_e=$GLOBALS['in'][$i]['in_path']['n_e'];
        }
        else
        {
           // can only be e_usage or literal
           manifest_e_usage($GLOBALS['in'][$i]['in_path'],$id_in); 
           $id_e=$GLOBALS['in'][$i]['in_path']['id_e'];
           $n_e=$GLOBALS['in'][$i]['in_path']['n_e'];
        }
    }
  }    
  else
  {
    // -------------------------------------------------------------------------
    // --------------------------------------- PLURAL BEGIN --------------------
    // --------------------------------------------------------------------------
    // can be: var_set, p, if, each, lot, sub, f_usage, e_usage,
    // only in each: break

    switch($y)
    {
      case 'def_e_usage':     
      case 'e_usage': //---------------------------------------------------------

           manifest_e_usage($GLOBALS['in'][$i],$id_in);
           $id_e=$GLOBALS['in'][$i]['id_e'];
           $n_e=$GLOBALS['in'][$i]['n_e'];
          
      break;
      case 'def_e': //---------------------------------------------------------
          $n_e=$v;  
          $id_e=insert_row($n_e,$id_in);  
      break;
      case 'privacy': //---------------------------------------------------------
          // TODO: all kind of privacy-settings
      break;
      case 'co': //---------------------------------------------------------
        $n_e = $GLOBALS['in'][$i]['sub'][1]['n_e'];
        $id_in_exp = $GLOBALS['in'][$i]['sub'][2]['id_in'];
        db_exec("UPDATE {$GLOBALS['pre']}e SET id_in_co='{$id_in_exp}' WHERE n='{$n_e}' AND is_now=1");
      break;
      case 'default': //---------------------------------------------------------
        $n_e = $GLOBALS['in'][$i]['sub'][1]['n_e'];
        $n_e_default = $GLOBALS['in'][$i]['sub'][2]['n_e'];
        db_exec("UPDATE {$GLOBALS['pre']}e SET n_default='{$n_e_default}' WHERE n='{$n_e}' AND is_now=1");
        break;
      case 'limited': //---------------------------------------------------------
          // OK, Nothing to do any more because the new limited entity was inserted with is_limited=1
      break;
      case 'def_f': //---------------------------------------------------------
          $n_e=$v;  
          $id_e=insert_row($n_e,$id_in);  
      break;
      case 'op': //---------------------------------------------------------
          // define the operation for a new f,
          $n_e=$GLOBALS['in'][$i]['r']['n'];  
          $id_e=insert_row($n_e,$id_in);
          
          // and set the expression to $id_in_exp (conditions are not allowed on op anyway)
          $id_in_exp = $GLOBALS['in'][$i]['sub'][3]['id_in'];
          db_exec("UPDATE {$GLOBALS['pre']}e SET id_in_co='{$id_in_exp}' WHERE n='{$n_e}' AND is_now=1");
          
      break;
      case 'var_set': //---------------------------------------------------------

      break;
      case 'p': //---------------------------------------------------------
          // p are checked within the context they are allowed: def_e, f-usage and e_usage, form there n_def must be set

      break;	
      case 'pile': //---------------------------------------------------------

      break;
      case 'if': //---------------------------------------------------------
        // TODO also support CASE
        // check both sides individually (true/false)
        // perform the if
        // Note: DARK CODE can happen inside a if in the case thats never reached, by 'if false'
        // Note: if is not allowed in lot/sub, because they are filters anyway. (Indirect if can happen)


      break;
      case 'break': //---------------------------------------------------------
        // break the each-loop,
        // to (help) avoid broken p-blocks a break can only happen 'in if inside each'
        // Note: DARK CODE can happen inside a block after a if-true-break;



      break;
      case 'each': //---------------------------------------------------------
        // perform the each-loop,
        // the resulting type is given by init-parameter [2]
        // the exp must fit the resulting type, but can be more specific
        // the exp [3] was checked but not executed so far.
        // Note: each is not allowed in lot/sub, to keep them flat filters. (Indirect each can happen)
        // Note: lot/sub are not allowed in each, because they are ment to be input for an each (indirectly it can happen)

      break;
      case 'lot': //---------------------------------------------------------
        // perform the lot-call,
        // for e-def/e-alias mainly
        // can not happen on p and e-usage
        // can also happen on f (allows filtering both in and out)
      break;
      case 'sub': //---------------------------------------------------------
        // perform the sub-call,
        // for p mainly
        // can not happen on f
        // can also be used for e, but the result is nontrivial to type then
        // decission: better not allow sub for e (when having @1@2 path also) ?!
      break;

      case 'f_usage': //---------------------------------------------------------
        // match the function
        // EXECUTE IT

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
  
  
  
      default:
        msg('error-internal','invalid case in a plural entity',$GLOBALS['in'][$i]);
      break;
  
    }
  }
  
  
    db_exec("UPDATE {$GLOBALS['pre']}in SET id_e={$id_e},n_e='{$n_e}' WHERE id={$id_in}");
  
    db_exec("INSERT INTO {$GLOBALS['pre']}u_e(n_u,n_e,i_hit,i_dirty_allowed,cl_in_first,cl_in_last) VALUES 
   ('{$GLOBALS['n-u']}','{$n_e}',0,1,NOW(),NOW())
   ON DUPLICATE KEY UPDATE i_hit=i_hit+1,cl_in_last=NOW(),i_dirty_allowed=(CASE WHEN i_dirty_allowed>0 THEN i_dirty_allowed+1 ELSE 0 END)");

  
} // end for



