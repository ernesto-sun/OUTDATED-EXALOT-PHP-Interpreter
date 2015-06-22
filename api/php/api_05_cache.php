<?php
if(!$is_api_call)die('X');

//-----------------------------------------------------------
// CACHING starts
//-----------------------------------------------------------

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

?>