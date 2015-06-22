<?php
if(!$is_api_call)die('X');


// Header Accept

$ok=0;
if (isset($header['Accept']))
{
	$arr_accept=explode(',',$header['Accept']);

	if(($cc_accept=count($arr_accept))>0)
	{
		for($cc=0;$cc<$cc_accept;$cc++)
		{
			$GLOBALS['st']['accept']=trim(strtolower($arr_accept[$cc]));
			if($pos=strrpos($GLOBALS['st']['accept'],'/'))$GLOBALS['st']['accept']=substr($GLOBALS['st']['accept'],$pos+1);
			if(strpos($GLOBALS['st']['accept'],'*')!==false){$GLOBALS['st']['accept']='json';$ok=1;break;}
			if(strpos($GLOBALS['st']['accept'],'json')!==false){$GLOBALS['st']['accept']='json';$ok=1;break;}
			if(strpos($GLOBALS['st']['accept'],'htm')!==false){$GLOBALS['st']['accept']='html';$ok=1;break;}
		}
	}
}
else
{
	$GLOBALS['st']['accept']='json';
	$ok=1;
}

if(!$ok)
{
        msg('error-http-contenttype','only supported: json (default) and html');
}

// Header 'Accept-Language'

$GLOBALS['st']['n-lang1']='';
$GLOBALS['st']['n-lang2']='';
$GLOBALS['st']['n-lang3']='';

if (isset($header['Accept-Language']))
{
	$arr_lang=explode(',',$header['Accept-Language']);
	
	if(($cc_lang=count($arr_lang))>0)
	{
		for($cc=0;$cc<$cc_lang;$cc++)
		{
			$v=strtolower(str_replace(array('-',';'),'',substr(trim($arr_lang[$cc]),0,3)));

			if(isset($GLOBALS['lang'][$v]))
			{
				if(strlen($GLOBALS['st']['n-lang1'])&&$GLOBALS['lang'][$v]!=$GLOBALS['st']['n-lang1'])
				{
					$GLOBALS['st']['n-lang2']=$GLOBALS['lang'][$v];
					break;
				}
				else $GLOBALS['st']['n-lang1']=$GLOBALS['lang'][$v];
			}
		}
	}
}

// The fallback-language is set in any case, at least as third language
$_n_lang_fb=$GLOBALS['lang'][$GLOBALS['conf']['lang-code-fallback']];
if(!strlen($GLOBALS['st']['n-lang1']))$GLOBALS['st']['n-lang1']=$_n_lang_fb;
elseif (!strlen($GLOBALS['st']['n-lang2']))
{
  if($GLOBALS['st']['n-lang1']!=$_n_lang_fb)$GLOBALS['st']['n-lang2']=$_n_lang_fb;  
}
else
{
  if($GLOBALS['st']['n-lang1']!=$_n_lang_fb&&$GLOBALS['st']['n-lang2']!=$_n_lang_fb)$GLOBALS['st']['n-lang3']=$_n_lang_fb;  
}

// Accept-Charset

$ok=0;
if (isset($header['Accept-Charset']))
{
	$arr_charset=explode(',',$header['Accept-Charset']);

	if(($cc_charset=count($arr_charset))>0)
	{
		for($cc=0;$cc<$cc_charset;$cc++)
		{
			$GLOBALS['st']['charset']=trim(strtolower($arr_charset[$cc]));
			if(strpos($GLOBALS['st']['charset'],'*')!==false){$GLOBALS['st']['charset']='utf-8';$ok=1;break;}
			if(strpos($GLOBALS['st']['charset'],'utf-8')!==false){$GLOBALS['st']['charset']='utf-8';$ok=1;break;}
			if(strpos($GLOBALS['st']['charset'],'iso-8859-1')!==false){$GLOBALS['st']['charset']='iso-8859-1';$ok=1;break;}
		}
	}
}
else
{
	$GLOBALS['st']['charset']='utf-8';
	$ok=1;
}


if(!$ok)
{
	msg('error-http-contenttype','supported charsets are only: UTF-8 (default), ISO-8859-1');
}

// Accept-Datetime
// Allowed formats
// *  Sun, 06 Nov 1994 08:49:37 GMT  ; RFC 822, updated by RFC 1123
// *  Sun Nov 6 08:49:37 1994        ; ANSI C's asctime() format
//
// All Header-DateTime is interpreted in GMT (web-definition)
//
//  export with gmdate('D, d M Y H:i:s', $timestamp).' GMT' in RFC 1123

$GLOBALS['st']['is-now']=1;
$ok=0;
if (isset($header['Accept-Datetime']))
{
	$time=strToClock($header['Accept-Datetime']);
	if($time['syntax_bad'])
	{
		msg('error-http-contenttype','Accept-Datetime not recognized as RFC 1123 or ANSI-C-time or EXA-clock');
	}
	$GLOBALS['st']['accept_cl']=$time['cl'];
	$GLOBALS['st']['accept_cl_year']=$time['year'];
	$GLOBALS['st']['is-now']=0;
}
else
{
	$GLOBALS['st']['accept_cl_year']=$GLOBALS['conf']['cl-year-min'];
	$GLOBALS['st']['accept_cl']=$GLOBALS['conf']['cl-min'];
	$GLOBALS['st']['is-now']=1;
}



?>