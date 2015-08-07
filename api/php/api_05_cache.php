<?php

/**
 *  EXALOT digital language for all agents
 *
 *  api_05_cache.php can find a cached result for a valid syntax and return it
 *  immediatelly in case of a GET-request 
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


$cache_sec_in=(int)$GLOBALS['conf']['default-cache-sec'];      
$is_cache=$GLOBALS['st']['is-public']&&$GLOBALS['st']['is-now'];


if($is_cache&&false) // TODO caching....
{

    // u-level default-max-sec-cache-age 

    // x-raw can be used for that
    // Header Cache-Control
    if (isset($header['Cache-Control']))
    {
	    $ok=preg_match_all('/\s*max\-age\s*\=\s*(\d*).*/i',$header['Cache-Control'],$match);
	    if($ok)
	    {
		    $cache_sec_in=(int)$match[1][0];
	    }
    }

    if (isset($header['Pragma']))
    {
      if(stripos($header['Pragma'],'no-cache')!==false)$cache_sec_in=0;
    }
}

