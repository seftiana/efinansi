<?php
// header("location: http://localhost/192.168.0.21/portal/");
// exit();
##add ccp

// echo "<script type=\"text/javascript\">
            // window.onload = function() { jam(); }
           
            // function jam() {
                // var e = document.getElementById('jam'),
                // d = new Date(), h, m, s;
                // h = d.getHours();
                // m = set(d.getMinutes());
                // s = set(d.getSeconds());
           
                // e.innerHTML = h +':'+ m +':'+ s;
           
                // setTimeout('jam()', 1000);
            // }
           
            // function set(e) {
                // e = e < 10 ? '0'+ e : e;
                // return e;
            // }
        // </script>";
// date_default_timezone_set("Asia/jakarta");
// echo "<center><h2>Maaf sedang dalam perbaikan - Silahkan kembali lagi nanti</h2><br> Perbanas Institute<br><br>". date('d-m-Y')."<br><span id='jam'></span><br>Untuk informasi lebih lanjut silahkan menghubungi bagian keuangan.<br>Terima kasih.</center>"; die;

##end

error_reporting(E_ALL & ~E_NOTICE);
//error_reporting(0);
#ini_set('display_errors',1);
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
