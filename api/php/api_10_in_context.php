<?php


/**
 *  EXALOT digital language for all agents
 *
 *  api_10_context.php holds functions for realizing context-entities
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

//----------------------------------------------------
function ensure_context(&$in,$id_in)
{
  $id_e=0;
  $n_e ='';
    
  switch($in['v'])
  {
    case 'in':
      // only in op/lot/sub   	READ-ONLY  (@out not supported yet)

      if(isset($in['in_lot']))
      {
	$n_e=$in['in_lot']['sub'][1]['n_e'];
	$id_e=$in['in_lot']['sub'][1]['id_e'];
      }
      elseif(isset($in['in_sub']))
      {
	$n_e=$in['in_sub']['sub'][1]['n_e'];
	$id_e=$in['in_sub']['sub'][1]['id_e'];
     }
      else
      {
	// op can be retrieved, because is root-element
	$in_op=&$GLOBALS['in-top'][$in['x_i']];
	$n_e=$in_op['sub'][1]['n_e'];
	$id_e=$in_op['sub'][1]['id_e'];
      }
    break;
    case 'e':
        // here is not the execution, only the integration, so do nothing
    break;
    case 'r':
        // here is not the execution, only the integration, so do nothing
    break;
    case 'i':
        // here is not the execution, only the integration, so do nothing
    break;
    case 'c':
        // here is not the execution, only the integration, so do nothing
    break;
    case 'u':
      // protected user-view	READ-ONLY 
        
      // ensure context-u in database, for current user
      $n_e='context-u-'.$GLOBALS['n-u'];
      
      $id_e=value("SELECT id FROM {$GLOBALS['pre']}e 
     WHERE is_now=1 
     AND n_def='context-u' 
     AND n='{$n_e}'");
     
     if($id_e<1)
     {
         // TODO Create context-u
         
        // [['e','context-u'],
        //   'name-nick^0-1',
        //   'name-full^0-1',
        //   'trust^0-1',
        //   'feeling^0+',
        //   'region^0+',
        //   'relation^0+',
        //   'location^0-1',
        //   'cl-birth^0-1',
        //   'cl-register^0-1']
     }
               

	
    
    break;
    case 'agent':
      // protected agent-view	READ-ONLY
      $n_e='context-agent-'.$GLOBALS['n-u-agent'];

      $id_e=value("SELECT id FROM {$GLOBALS['pre']}e 
     WHERE is_now=1 
     AND n_def='context-agent' 
     AND n='{$n_e}'");
     
     if($id_e<1)
     {
         // TODO Create context-agent
        
     }
    break;
    case 'con':
      // protected con-view		READ-ONLY
      $n_e='context-con-'.$GLOBALS['n-u'].'-'.$GLOBALS['id-con'];

      $id_e=value("SELECT id FROM {$GLOBALS['pre']}e 
     WHERE is_now=1 
     AND n_def='context-con' 
     AND n='{$n_e}'");
     
     if($id_e<1)
     {
         // TODO Create context-con
        
     }
    break;
    case 'st':
      // protected statement-view	READ-ONLY
      $n_e='context-statement-'.$GLOBALS['n-u'].'-'.$GLOBALS['id-con'].'-'.$GLOBALS['id-st'];

      $id_e=value("SELECT id FROM {$GLOBALS['pre']}e 
     WHERE is_now=1 
     AND n_def='context-statement' 
     AND n='{$n_e}'");
     
     if($id_e<1)
     {
         // TODO Create context-statement
        
     }
    break;
    case 'g':
      // protected group-view	READ-WRITE (depending on user-permissions)
      //$in['y']='e_usage';
        
      $n_e='context-group-'.$GLOBALS['id-con'];

      $id_e=value("SELECT id FROM {$GLOBALS['pre']}e 
     WHERE is_now=1 
     AND n_def='context-group' 
     AND n='{$n_e}'");
     
     if($id_e<1)
     {
         // TODO Create context-group
        
     }
        
    break;
    case 'service':
      // useful data about the current-exalot-service-execution	READ-ONLY

      $n_e='context-service-'.$GLOBALS['conf']['version'];

      $id_e=value("SELECT id FROM {$GLOBALS['pre']}e 
     WHERE is_now=1 
     AND n_def='context-service' 
     AND n='{$n_e}'");
     
     if($id_e<1)
     {
         // TODO Create context-service
              
        
//      $in['sub']=array(0=>0,
//		       1=>array('y'=>'s',
//				'is_literal'=>1,
//				'n_def'=>'name',
//				'c_str'=>strlen($GLOBALS['conf']['service']),
//				'v'=>$GLOBALS['conf']['service'],
//				'c_sub'=>0,
//				'sub'=>array(0=>0)),
//		       2=>array('y'=>'s',
//				'is_literal'=>1,
//				'n_def'=>'s-version',
//				'c_str'=>strlen($GLOBALS['conf']['version']),
//				'v'=>$GLOBALS['conf']['version'],
//				'c_sub'=>0,
//				'sub'=>array(0=>0)),
//		       3=>array('y'=>'s',
//				'is_literal'=>1,
//				'n_def'=>'s-version-alias',
//				'c_str'=>strlen($GLOBALS['conf']['version-alias']),
//				'v'=>$GLOBALS['conf']['version-alias'],
//				'c_sub'=>0,
//				'sub'=>array(0=>0)),
//		       4=>array('y'=>'s',
//				'is_literal'=>1,
//				'n_def'=>'s-os',
//				'c_str'=>strlen(PHP_OS),
//				'v'=>PHP_OS,
//				'c_sub'=>0,
//				'sub'=>array(0=>0)));
     }
    break;
    case 'now':	
	// timestamp of first now-usage	READ-ONLY
      
	if(!isset($GLOBALS['temp']['v-now']))
	{
	  $cl=strToClock(value($GLOBALS['conf']['mysql-before-5-6-4']?
				    'SELECT NOW()':
				    'SELECT sysdate(3)')); // to ensure being at db-time
	
	  $GLOBALS['temp']['v-now']=$cl;
	}

        $n_e=$GLOBALS['temp']['v-now']['n'];
        $id_e=ensure_literal_cl($n_e,$GLOBALS['temp']['v-now']['year'],$GLOBALS['temp']['v-now']['cl'],$GLOBALS['temp']['v-now']['ms'],$id_in);
        
    break;
    case 'random':
        // a random number in float between 0 and 100 (PHP-random)	READ-ONLY
        $n_e='float-'.strtolower(str_replace('.','-',$v));
        $v=100*mt_rand(1,mt_getrandmax()-1)/mt_getrandmax();
        $id_e=ensure_literal_float($n_e, $v, $id_in);  
    break;
    default:
      msg('error-internal','invalid case in a context',$in);
    break;
  } 
  
  return array($id_e,$n_e);
}

