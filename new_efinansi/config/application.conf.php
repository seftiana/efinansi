<?php
#ini_set('display_errors', 1);
#ini_set('display_startup_errors', 1);
#error_reporting(E_ALL);

$isSecure      = false;
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
   $isSecure   = true;
}elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on') {
   $isSecure   = true;
}
$REQUEST_PROTOCOL             = $isSecure ? 'https' : 'http';

if (!defined('DS')) {
   define('DS', DIRECTORY_SEPARATOR);
}
$dir        = $base = null;
$base       = dirname($_SERVER['PHP_SELF']);
$webroot    = basename($base);
$indexPos   = strpos($base, '/index.php');
if ($indexPos !== false) {
   $base    = substr($base, 0, $indexPos);
}

if ($base === DS || $base === '.') {
   $base    = '';
}
$base       = implode('/', array_map('rawurlencode', explode('/', $base)));
//=============ApplicationId========================
$application['application_id'] = 510; //GT Finansi Anggaran
//=============directory============================

// do not edit this config
$application['gtfw_base']     = GTFW_BASE_DIR;
$application['docroot']       = GTFW_APP_DIR;
$application['basedir']       = $base . '/'; // with trailling slash
$application['baseaddress']   = $REQUEST_PROTOCOL .'://'.$_SERVER['HTTP_HOST']; // without trailling slash
$application['domain']        = null; // name of domain


$application['db_conn'][0]['db_driv']     = 'adodb';
$application['db_conn'][0]['db_type']     = 'mysqlt';
$application['db_conn'][0]['db_host']     = 'db1.perbanas.id';
$application['db_conn'][0]['db_user']     = 'efinansi';
$application['db_conn'][0]['db_pass']     = 'myp455_efinansi';
$application['db_conn'][0]['db_name']     = 'efinansi';
$application['db_conn'][0]['db_port']     = 3306;
$application['db_conn'][0]['db_result_cache_lifetime']   = '';
$application['db_conn'][0]['db_result_cache_path']       = '';
$application['db_conn'][0]['db_debug_enabled']           = true;

/**
 * untuk koneksi ke gtpembayaran
 */
$application['db_conn'][1]['db_driv']     = 'adodb';
$application['db_conn'][1]['db_type']     = 'mysqlt';
$application['db_conn'][1]['db_host']     = '139.255.43.229';
$application['db_conn'][1]['db_user']     = 'gt';
$application['db_conn'][1]['db_pass']     = 'gtPASS';
$application['db_conn'][1]['db_name']     = 'gtpembayaran_dev';
$application['db_conn'][1]['db_port']     = 3306;
$application['db_conn'][1]['db_result_cache_lifetime']   = '';
$application['db_conn'][1]['db_result_cache_path']       = '';
$application['db_conn'][1]['db_debug_enabled']           = true;

/**
 * untuk koneksi ke gtpembayaran pasca
 */
$application['db_conn'][2]['db_driv']     = 'adodb';
$application['db_conn'][2]['db_type']     = 'mysqlt';
$application['db_conn'][2]['db_host']     = '127.0.0.1';
$application['db_conn'][2]['db_user']     = 'gt';
$application['db_conn'][2]['db_pass']     = 'gtpass';
$application['db_conn'][2]['db_name']     = 'perbanas_pasca_client_gtpembayaran_dev';
$application['db_conn'][2]['db_port']     = 3307;
$application['db_conn'][2]['db_result_cache_lifetime']   = '';
$application['db_conn'][2]['db_result_cache_path']       = '';
$application['db_conn'][2]['db_debug_enabled']           = true;



//$application['url_source']     =  "http://103.206.245.198/gtakademik/services/rest/index.php";
$application['url_source']     =  "http://akademik.perbanas.id/services/rest/index.php";

//============session============================
$application['use_session']         = TRUE;
$application['session_name']        = 'GTFWSessID';
// TODO: should not be here!!!, and pelase, support NULL value to fallback to PHP INI's session save path
$application['session_save_path']   = NULL;
$application['session_expire']      = 180; // in minutes
$application['session_cookie_params']['lifetime']  = 60 * $application['session_expire']; // in seconds
$application['session_cookie_params']['path']      = $application['basedir'];
$application['session_cookie_params']['domain']    = $application['domain'];
$application['session_cookie_params']['secure']    = FALSE; // needs secure connection?

//============default page============================
$application['default_module']      = 'login_default';
$application['default_submodule']   = 'login';
$application['default_action']      = 'view';
$application['default_type']        = 'html';
// =========== Default Login Page ====================
$application['login_page']['mod']   = 'login_default';
$application['login_page']['sub']   = 'login';
$application['login_page']['act']   = 'view';
$application['login_page']['typ']   = 'html';
//============security===========================
$application['enable_security']  = true;
$application['default_user']     = 'nobody';
$application['enable_url_obfuscator']     = FALSE;
$application['url_obfuscator_exception']  = array('soap'); // list of exeption request/response type
$application['url_type']         = 'Long'; // type: Long or Short
$application['login_method']     = 'TRUE';

//============development============================
$application['debug_mode']    = FALSE;

//=========== Single Sign On ========================
$application['system_id']     = 'com.gamatechno.gtfw';
$application['sso_group']     = 'com_gamatechno_academica'; //FIXME: what if this system is associated with more than one sso group

//=========== Single Sign On Server ========================
$application['sso_ldap_connection']    = 3; // connection number available for ldap access, see db_conn above

//============== syslog =============================
$application['syslog_category']  = array(); // what category permitted to be printed out, array() equals all category
$application['syslog_enabled']   = false;
$application['syslog_io_engine'] = 'std'; //tcp, file, std
$application['syslog_log_path']  = '/tmp/';
$application['syslog_tcp_host']  = 'localhost';
$application['syslog_tcp_port']  = 9777;

//================ soapgateway ========================
$application['wsdl_use_cache']      = false; // use cached wsdl if available
$application['wsdl_cache_path']     = '/tmp/'; // use cached wsdl if available
$application['wsdl_cache_lifetime'] = 60 * 60 * 24 /* one day!*/; // invalidate wsdl cache every x seconds

//================ additional config =====================
$application['company_name']     = "Perbanas Institute";
$application['application_name'] = "gtFinansi";
$application['company_address']  = "none";
$application['menu_version']     = "2";
$application['city']             = "Jakarta";
$application['language']         = "indonesia";

//================== koneksi ke db lain ===========
$application['aset']       = false;
$application['sia']        = false;
$application['admisi']     = true;
$application['sdm']        = false;
$application['um']         = true;
//================config tahun pembukuan=========================
$application['tahun_pembukuan']     = "yearly"; #monthly, yearly

//===================== config auto verivikasi =====================
$application['auto_verifikation']   = false; #true /

//===================== config auto approve jurnal dan auto posting =====================
$application['auto_approve']     = false;#true or false
$application['auto_posting']     = false;#true or false
$application['subAccJml']        = 7;
$application['subAccFormat']     = '99-99-99-99-99-99-99';

//==================== config untuk laporan aktivitas  ===================================
$application['hide_zero_value'] = false;#true or false (true = sembunyikan nominal 0 )

?>
