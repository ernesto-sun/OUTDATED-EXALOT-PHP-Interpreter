<?php
if(!$is_api_call)die('X');


// --------------------------------------------------
// STATEMENT Handling starts
// --------------------------------------------------


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
 
$GLOBALS['context']['id-st']=dbs::insert("INSERT INTO {$GLOBALS['pre']}st(
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
{$GLOBALS['context']['id-con']},
{$GLOBALS['context']['id-ses']},
'{$GLOBALS['context']['n-u']}',
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


?>