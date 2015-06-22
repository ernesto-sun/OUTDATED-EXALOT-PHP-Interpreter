<?php
if(!$is_api_call)die("X");


		        
// ----------------------------------------------------------------
function resolve_name_and_exp($n)
{
  $res=array('n'=>$n,
	      'exp_min'=>1,
	      'exp_max'=>1);

  $pos_exp=strpos($n,'^');
  if($pos_exp)
  {
    $res['n']=substr($n,0,$pos_exp);
    if(empty($res['n'])) return 0;
    
    $exp=substr($n,$pos_exp+1);
    if(empty($exp)) return 0;
  
    $exp=explode('-',$exp);
    $c_exp=count($exp);
    if($c_exp<1)return 0;
    
    if($c_exp<2)
    {
      $lastPos=strlen($exp[0])-1;
      if($exp[0][$lastPos]=='+')
      {
	$res['exp_max']=0;
	$exp[0]=substr($exp[0],0,$lastPos);
	$res['exp_min']=(int)$exp[0];
      }
      else
      {
	$res['exp_min']=$res['exp_max']=(int)$exp[0];
      }
    }
    elseif($c_exp<3)
    {
      $res['exp_min']=(int)$exp[0];
      $res['exp_max']=(int)$exp[1];
      
      if($res['exp_min']>$res['exp_max'])return 0;
    }
    else
    {
      return 0;
    }
    if($res['exp_min']<0) return 0;
    if($res['exp_max']<0) return 0;
  }
  return $res;
}


?>