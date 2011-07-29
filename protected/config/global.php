<?php
function d($src) {
    echo '<pre>';
    var_dump($src);
    echo '</pre><br/>';
}

function ds($src) {
    ob_start();
    var_dump($src);
    $dump = ob_get_contents();
    ob_end_clean();
    return $dump;
}

function bu($url=null) 
{
    static $baseUrl;
    if ($baseUrl===null)
        $baseUrl=getApplicationUrl();
    return $url===null ? $baseUrl : $baseUrl.'/'.ltrim($url,'/');
}

function getApplicationUrl() 
{
  return (preg_match('/^HTTPS/i', $_SERVER['SERVER_PROTOCOL']) ? 'https' : 'http')
    . '://'
    . $_SERVER['HTTP_HOST']
    ;
}

function h($text)
{
    return htmlspecialchars($text,ENT_QUOTES,Yii::app()->charset);
}

function randomStr($setLength=NULL, $setKind=NULL){ 
  $rs  = '';
  if(is_null($setLength)) {
    $setLength = 8; 
  } elseif(is_numeric($setKind)) {
    $setLength = $setKind; 
  } 

  $letter = array( 
    'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 
    'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', 
    'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 
    'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 
    '0', '1', '2', '3', '4', '5', '6', '7', '8', '9',
  ); 

  // 文字範囲 
  switch($setKind){ 
    case 'alpha': 
      $st = 0; 
      $fn = 51; 
      break; 

    case 'num': 
      $st = 51; 
      $fn = 61; 
      break; 

    default: 
      $st = 0; 
      $fn = 61; 
      break; 
  } 

  //ランダムな文字列生成 
  for( $n=0; $n<$setLength; $n++ ){ 
    $rs .= $letter[ mt_rand( $st, $fn ) ]; 
  } 
  return $rs; 
} 

