<?php

/**
 *  EXALOT digital language for all agents
 *
 *  util_error.php holds special error-handling
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


//----------------------------------------------------------------
function error_handler_2()
{
    $error = error_get_last();

    if($error && ($error['type'] & E_FATAL))
    {
        error_handler($error['type'], $error['message'], $error['file'], $error['line']);
    }    
}


//----------------------------------------------------------------
function error_handler( $errno, $errstr, $errfile, $errline ) 
{
    $typestr='unknown';
    $is_error=0;
    switch ($errno)
    {
        case E_ERROR: // 1 //
            $typestr = 'E_ERROR'; 
            $is_error=1;
            break;
        case E_WARNING: // 2 //
            $typestr = 'E_WARNING'; 
            break;
        case E_PARSE: // 4 //
            $typestr = 'E_PARSE'; 
            $is_error=1;
            break;
        case E_NOTICE: // 8 //
            $typestr = 'E_NOTICE'; 
            break;
        case E_CORE_ERROR: // 16 //
            $typestr = 'E_CORE_ERROR'; 
            $is_error=1;
            break;
        case E_CORE_WARNING: // 32 //
            $typestr = 'E_CORE_WARNING'; 
            break;
        case E_COMPILE_ERROR: // 64 //
            $typestr = 'E_COMPILE_ERROR'; 
            $is_error=1;
            break;
        case E_CORE_WARNING: // 128 //
            $typestr = 'E_COMPILE_WARNING'; 
            break;
        case E_USER_ERROR: // 256 //
            $typestr = 'E_USER_ERROR'; 
            $is_error=1;
            break;
        case E_USER_WARNING: // 512 //
            $typestr = 'E_USER_WARNING'; 
            break;
        case E_USER_NOTICE: // 1024 //
            $typestr = 'E_USER_NOTICE'; 
            break;
        case E_STRICT: // 2048 //
            $typestr = 'E_STRICT';
            break;
        case E_RECOVERABLE_ERROR: // 4096 //
            $typestr = 'E_RECOVERABLE_ERROR'; 
            $is_error=1;
            break;
        case E_DEPRECATED: // 8192 //
            $typestr = 'E_DEPRECATED';
            $is_error=1;
            break;
        case E_USER_DEPRECATED: // 16384 //
            $typestr = 'E_USER_DEPRECATED'; 
            $is_error=1;
            break;

    }

    $message = '\n\r<b>'.$typestr.': </b>'.$errstr.' in <b>'.$errfile.'</b> on line <b>'.$errline.'</b><br/>\n\r';
    msg(($is_error?'error-php':'warning-php'),$message);

}



//----------------------------------------------------------------
//----------------------------------------------------------------

if (version_compare(PHP_VERSION,'5.2.0')>=0) 
{
    // extended error-handling for newer PHP-Version
    define('E_FATAL',  E_ERROR | E_USER_ERROR | E_PARSE | E_CORE_ERROR | 
            E_COMPILE_ERROR | E_RECOVERABLE_ERROR);
    
    register_shutdown_function('error_handler_2');    
}    
    
//define('DISPLAY_ERRORS', TRUE);
//define('LOG_ERRORS', TRUE);
//define('ENV', 'dev');

error_reporting($GLOBALS['debug']?E_ALL|E_STRICT:0);


//----------------------------------------------------------------
//----------------------------------------------------------------
set_error_handler('error_handler');
//----------------------------------------------------------------
//----------------------------------------------------------------
