<?php

/**
 *  EXALOT digital language for all agents
 *
 *  config.php contains basic server-settings for admins
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

$GLOBALS['conf']=array(

/* --------------- SERVICE BEGIN ------------- */

'service'=>'EXALOT',
'n-u-exalot'=>'u-exalot',
'version'=>1,
'version-alias'=>'baby',

/* --------------- SERVICE END ------------- */

/* --------------- DATABASE- BEGIN ------------- */

'mysql-host'=>'localhost',
'mysql-user'=>'exa_user',
'mysql-db'=>'exa_db',
'mysql-port'=>'3306',
'mysql-prefix'=>'exa_',

/* --------------- DATABASE- END ------------- */


/* --------------- CHACHING- BEGIN ------------- */

'nocach'=>0,
'force-cach'=>0,

/* --------------- CHACHING- END ------------- */


/* --------------- STATEMENT- BEGIN ------------- */

'lang-code-n'=>'en',
'lang-code-fallback'=>'en',

'default-theme'=>array('html'=>'html-mini',
		       'json'=>'json'),

/* --------------- STATEMENT- END ------------- */

/* --------------- TECHNICAL DETAILS BEGIN ------------- */

'cl-min'=>'1000-01-01 00:00',
'cl-max'=>'9999-12-31 00:00',
'cl-year-min'=>-16000000000,
'cl-year-max'=>16000000000,
'cl-year-min-db'=>1000,
'cl-year-max-db'=>9999,


'default-cache-sec'=>86400,


'max-ct-id-u-list'=>1000, // COMMENT 'the maximum number of users in a group to use the id-list-cache, see ct-id-u',


'mysql-before-5-6-4'=>0  // COMMENT that enables using NOW() instead of sysdate(3)  (no milliseconds)

/* --------------- TECHNICAL DETAILS END ------------- */


);

include '../../config_sec.php';



