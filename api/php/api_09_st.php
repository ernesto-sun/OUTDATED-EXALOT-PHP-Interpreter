<?php

/**
 *  EXALOT digital language for all agents
 *
 *  api_09_st.php feeds the table that holds an entry for each given statement.
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


if($x_len>255)
{
  $v_255=substr($x,0,255);
  if($v_255[254]=='\\')
  {
    // 'bad luck' after shortening an espcped string to have \ at the end, would escape the following '
    $v_255[254]='.';
  }
}
else
{
  $v_255=$x;
}

$is_cache=($is_cache?'1':'0'); // ensure boolean as string
 
$GLOBALS['id-st']=insert("INSERT INTO {$GLOBALS['pre']}st(
id_con,
id_ses,
n_u,
cl_in,
cl_ms_in,
st,
n_d,
v_in_255,
v_length,
accept,
accept_charset,
accept_cl,
n_lang1,
n_lang2,
n_lang3,
etag_in,
cache_sec_in,
i_in,
c_in,
is_cache) VALUES(
{$GLOBALS['id-con']},
{$GLOBALS['id-ses']},
'{$GLOBALS['n-u']}',
NOW(),
MICROSECOND(SYSDATE(6)),
'{$GLOBALS['st']['method']}',
'',
'{$v_255}',
{$x_len},
'{$GLOBALS['st']['accept']}',
'{$GLOBALS['st']['charset']}',
'{$GLOBALS['st']['accept_cl']}',
'{$GLOBALS['st']['n-lang1']}',
'{$GLOBALS['st']['n-lang2']}',
'{$GLOBALS['st']['n-lang3']}',
'',
{$cache_sec_in},
{$GLOBALS['st']['i']},
{$GLOBALS['st']['c']},
{$is_cache})"); 


