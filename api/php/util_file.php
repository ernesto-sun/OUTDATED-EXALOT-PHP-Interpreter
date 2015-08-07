<?php
if (!isset($GLOBALS["indexOk"]) || !$GLOBALS["indexOk"]) die();

//---------------------------------
function getImgNameMini($imgName)
{
  $tmp = pathinfo($imgName);
  return $tmp['filename'].".mini.".$tmp['extension'];
}

//---------------------------------
function getImgNameMedi($imgName)
{
  $tmp = pathinfo($imgName);
  return $tmp['filename'].".medi.".$tmp['extension'];
}

//---------------------------------------------
function showSpecialMedia($idd, $wq)
{
  $fid_img = value("select id from field where id_question=".$wq['id']." and name like 'g-txt-media'");
  $fid_desc = value("select id from field where id_question=".$wq['id']." and name like 'g-txt-media-desc'");
  if (!$fid_img || !$fid_desc) return;

  $img = "";
  $desc = "";
  $imgLink = "";
  $id_value_desc = 0;

  if ($idd['id_valuearea'] > 1000)
  { 
    $img = value("SELECT value FROM value WHERE id_field=".$fid_img." AND id_valuearea=".$idd['id_valuearea']." limit 1");
    $id_value_desc = value("SELECT id FROM value WHERE id_field=".$fid_desc." AND id_valuearea = ".$idd['id_valuearea']." limit 1");

    if ($id_value_desc > 0)
    {
      $desc = value("SELECT value from value where id=".$id_value_desc);
    }
  }
  
  $_SESSION['tmp_media_id_valuearea'] = $idd['id_valuearea'];
  $_SESSION['tmp_media_id_valuesection']= $idd['id_valuesection'];
  $_SESSION['tmp_media_id_valuequestion'] = $idd['id_valuequestion'];
  
  echo "<div class='div-media'>";
  echo "<h2 class='h2-section h2-media'>Project-Image</h2>";
  echo "<label class='label-mini'>Image (.png, .jpg, .gif) <img class='img-inline img-info img-info-file' src='img/info.png' alt='info'/></label>";
  echo "<input type='hidden' name='MAX_FILE_SIZE' value='10000000'>";
  echo "<input size='24' name='g-txt-media' type='file' />"; 
  echo "<label class='label-mini label-mini-img-desc' >Image-Description</label>";


  $inpname="x-".$idd['id_area']."-".
            $idd['id_valuearea']."-".
            $idd['id_section']."-".
            $idd['id_valuesection']."-".
            $idd['id_question']."-".
            $idd['id_valuequestion']."-".
            $fid_desc."-".
            $id_value_desc;


  echo "<input class='inp-text' name='".$inpname."' type='text' value='",$desc,"'/>"; 

  if (strlen($img) > 0)
  {
    $imgLinkFull = getLinkPathUserImg().$img;
    $imgLink = getLinkPathUserImg().getImgNameMedi($img);

    $imgAlt = "";
    if (strlen($desc) > 0) $imgAlt = substr($desc, 0, 100);
    else $imgAlt = getImgNamePure($img);
    
    echo "<input class='inp-checkbox inp-checkbox-img-delete' type='checkbox' value='true' name='g-txt-media-delete'/>remove image"; 
    
    echo "<div class='div-img-project-edit'><a href='",$imgLinkFull,"' target='_blank'><img alt='",$imgAlt,"' src='",$imgLink,"'/></a></div>";
  }
  else
  {
  }

  echo "</div>";
}



//---------------------------------
function getLinkPathUserImg()
{
 require_once("php/util.php");
 return getBaseURI()."i/";
}

//---------------------------------
function getPathUserImg()
{
 require_once("php/util.php");
 return getBaseDir()."i/";
}

//---------------------------------
function getImgNamePure($imgName)
{
 $tmp = basename($imgName);
 $posSep = strpos($tmp, "__");
 if ($posSep === false) return $tmp;
 return substr($tmp, $posSep + 2);
}


//----------------------------------
function getImgObject($imgName)
{
  $tmp = pathinfo($imgName);
  $img = 0;
  switch (strtolower($tmp['extension']))
  {
    case "jpg":
    case "jpeg":
      $img = imagecreatefromjpeg(getPathUserImg().$tmp['basename']);
    break;
    case "png":
      $img = imagecreatefrompng(getPathUserImg().$tmp['basename']);
    break;
    case "gif":
      $img = imagecreatefromgif(getPathUserImg().$tmp['basename']);
    break;
  }
  return $img;
}


//----------------------------------
function makeImgMiniAndMedi($imgName)
{
  $tmp = pathinfo($imgName);
  $img = false;
  $isPngOrGif = false;
  switch (strtolower($tmp['extension']))
  {
    case "jpg":
    case "jpeg":
      $img = imagecreatefromjpeg(getPathUserImg().$tmp['basename']);
    break;
    case "png":
      $img = imagecreatefrompng(getPathUserImg().$tmp['basename']);
      $isPngOrGif = true;
    break;
    case "gif":
      $img = imagecreatefromgif(getPathUserImg().$tmp['basename']);
      $isPngOrGif = true;
    break;
  }
  if (!$img) return;
  list($width_orig, $height_orig) = getimagesize(getPathUserImg().$tmp['basename']);

  $width = 120;
  $height = 90;

  $ratio_orig = $width_orig/$height_orig;

  if ($width / $height > $ratio_orig) $width = $height * $ratio_orig;
  else $height = $width / $ratio_orig;

  $imgMini = imagecreatetruecolor($width, $height);

  if($isPngOrGif)
  {
    imagecolortransparent($imgMini, imagecolorallocatealpha($imgMini, 0, 0, 0, 127));
    imagealphablending($imgMini, false);
    imagesavealpha($imgMini, true);
  }


  imagecopyresampled($imgMini, $img, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);

  switch (strtolower($tmp['extension']))
  {
    case "jpg":
    case "jpeg":
      imagejpeg($imgMini, getPathUserImg().getImgNameMini($tmp['basename']));
    break;
    case "png":
      if (!imageistruecolor($img)) imagetruecolortopalette($imgMini, true, 255); 
      imagepng($imgMini, getPathUserImg().getImgNameMini($tmp['basename']));
    break;
    case "gif":
      if (!imageistruecolor($img)) imagetruecolortopalette($imgMini, true, 255); 
      imagegif($imgMini, getPathUserImg().getImgNameMini($tmp['basename']));
    break;
  }
  imagedestroy($imgMini);

  $width = 300;
  $height = 225;

  if ($width / $height > $ratio_orig) $width = $height * $ratio_orig;
  else $height = $width / $ratio_orig;

  $imgMedi = imagecreatetruecolor($width, $height);

  if($isPngOrGif)
  {
    imagecolortransparent($imgMedi, imagecolorallocatealpha($imgMedi, 0, 0, 0, 127));
    imagealphablending($imgMedi, false);
    imagesavealpha($imgMedi, true);
  }

  imagecopyresampled($imgMedi, $img, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);

  switch (strtolower($tmp['extension']))
  {
    case "jpg":
    case "jpeg":
      imagejpeg($imgMedi, getPathUserImg().getImgNameMedi($tmp['basename']));
    break;
    case "png":
      if (!imageistruecolor($img)) imagetruecolortopalette($imgMedi, true, 255); 
      imagepng($imgMedi, getPathUserImg().getImgNameMedi($tmp['basename']));
    break;
    case "gif":
      if (!imageistruecolor($img)) imagetruecolortopalette($imgMedi, true, 255); 
      imagegif($imgMedi, getPathUserImg().getImgNameMedi($tmp['basename']));
    break;
  }
  imagedestroy($imgMedi);
  imagedestroy($img);  
}




//----------------------------------
function uploadImage($var,$id)
{
	$res=array();
	if (!isset($_FILES[$var]) || $_FILES[$var]['error'] || strlen($_FILES[$var]['name']) < 1)
	{
		$res["msg"]="File upload system error!";
		return $res;
  }
	
	$res["mime"]=$_FILES[$var]['type'];
	$ext="";
	switch($res["mime"])
	{
		case 'image/png':
			$ext="png";
			break;
    case 'image/jpeg':
			$ext="jpg";
			break;
    case 'image/gif':
			$ext="gif";
			break;
		default:
			$res["msg"]="File-Type is not valid. Please use JPEG, GIF, PNG.";
			return $res;
		break;
	}


  $fileName = "u".$id."_".preg_replace('/[^a-zA-Z0-9]/','_',getTimeStamp()."__".$_FILES[$var]['name']).".".$ext;

  $fileTarget = getPathUserImg().$fileName;

  if (move_uploaded_file($_FILES[$var]['tmp_name'], $fileTarget)) 
  {
    makeImgMiniAndMedi($fileName);
		$res["msg"]="ok";
		$res["filename"]=$fileName;

  } 
  else 
  {
		$res["msg"]="Moving uploaded file was not possible!";
  }
	return $res;
}


?>