<?php
#ini_set("display_errors","1");
#error_reporting(E_ALL);

//print_r($_GET);

if(isset($_GET['gtfwVar'])){
   $arrParam = explode('/',$_GET['gtfwVar']);

   $_GET['mod'] = $arrParam[0];
   $_GET['sub'] = $arrParam[1];
   $_GET['act'] = 'rest';
   $_GET['token'] = $arrParam[2];
   $_GET['typ'] = 'rest';

   for($i=3;$i<count($arrParam);$i++){
      $_GET[$arrParam[$i]]=isset($arrParam[$i+1])?$arrParam[$i+1]:"";
      $i++;
   }
   $_REQUEST = $_GET;
}

$gtfw_base_dir = @file_get_contents('config/gtfw_base_dir.def');
// does anyone know the regex for these two string replacements, so it can be executed once?
$gtfw_base_dir = str_replace('\\', '/', trim($gtfw_base_dir));
$gtfw_base_dir = preg_replace('/[\/]+$/', '', $gtfw_base_dir);
//var_dump($gtfw_base_dir);
if (file_exists($gtfw_base_dir)) {
   define('GTFW_BASE_DIR', $gtfw_base_dir . '/');
   define('GTFW_APP_DIR', str_replace('\\', '/', dirname(__FILE__)) . '/');
   require_once $gtfw_base_dir.'/rest.php';
} else {
   echo 'Fatal: Cannot find GTFW base!';
}

?>
