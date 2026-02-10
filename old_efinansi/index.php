<?php
error_reporting(E_ALL & ~E_NOTICE);
error_reporting(0);
$gtfw_base_dir = @file_get_contents('config/gtfw_base_dir.def');
// does anyone know the regex for these two string replacements, so it can be executed once?
$gtfw_base_dir = str_replace('\\', '/', trim($gtfw_base_dir));
$gtfw_base_dir = preg_replace('/[\/]+$/', '', $gtfw_base_dir);

if (file_exists($gtfw_base_dir)) {
   define('GTFW_BASE_DIR', $gtfw_base_dir . '/');
   define('GTFW_APP_DIR', str_replace('\\', '/', dirname(__FILE__)) . '/');
   $gtfw_init_dir = GTFW_BASE_DIR . 'main/init/' . basename(__FILE__, '.php') . '/*.php';

   foreach (glob($gtfw_init_dir) as $value) {
      require_once $value;
   }
} else {
   echo 'Fatal: Cannot find GTFW base!';
}
?>
