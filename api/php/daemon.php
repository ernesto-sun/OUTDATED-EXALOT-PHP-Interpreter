<?php
if(!$is_api_call)die("X");

function get_prefix_without_me()
{
	global $conn;
	$table = $conn->query('SHOW TABLES LIKE \'%_me\'')->fetch_array(MYSQLI_NUM)[0];
	$prefix= $conn->query("SELECT prefix FROM $table LIMIT 1")->fetch_array(MYSQLI_NUM)[0];
	
	if($prefix."_me"!=$table)die("db-error: the table _me is not well-prefixed, the prefix-setting is invalid or a false table ending with _me exists.");
	return $prefix;
}

function daemon_create_me()
{
	global $conn;
	$prefix=get_prefix_without_me();
		
	$me_cc=dbs::value("SELECT count(*) FROM {$prefix}_me");

	if($me_cc!=1)die("db-error: the table _me has not exactly one entry.");	
	$me=dbs::singlerow("SELECT NOW() AS timestamp,_me.* FROM {$prefix}_me AS _me ORDER BY id LIMIT 1");

	$sql="SELECT _me_var.var as id,_me_val.v as v  
FROM {$prefix}_me_var AS _me_var
LEFT JOIN {$prefix}_me_val AS _me_val ON (_me_val.id_me_var=_me_var.id)
WHERE _me_val.id_u=0;";
	dbs::idv_add($me,$sql);

	$me["cwd"]=getcwd_clean();
	$me["uri"]=getBaseURI();	
	$me["system"]=$_SERVER['SERVER_SOFTWARE'];	
	$me["basedir"]=getBaseDirOnly();	
	$me["document_root"]=$_SERVER['DOCUMENT_ROOT'];	
	$me["api"]=$_SERVER['SCRIPT_NAME'];	
	$me["domain"]=$_SERVER['HTTP_HOST'];	

	$file=$me["cwd"]."/php/_auto/me.php";
	$ok=file_put_contents($file,"<?php \r\n\$_ME=".var_export($me,1).";\r\n?>");
	if($ok)echo "PHP-File $file was written successfully!";
	else echo "ERROR: PHP-File $file could not be written!";
}

function daemon_create_lang()
{
	global $conn;
	$prefix=get_prefix_without_me();
	$lang=array();
	$sql="SELECT lang.code as id,lang.id_e_lang as v FROM {$prefix}lang AS lang ORDER BY cc_e DESC";
	dbs::idv_add($lang,$sql);
	
	$file=getcwd_clean()."php/_auto/lang.php";
	$ok=file_put_contents($file,"<?php \r\n\$_LANG=".var_export($lang,1).";\r\n?>");
	if($ok)echo "PHP-File $file was written sucsacessfully!";
	else echo "ERROR: PHP-File $file could not be written!";
}







?>