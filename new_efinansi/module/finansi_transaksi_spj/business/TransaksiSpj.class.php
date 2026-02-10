<?php
/**
* ================= doc ====================
* FILENAME     : TransaksiSpj.class.php
* @package     : TransaksiSpj
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-03-17
* @Modified    : 2015-03-17
* @Analysts    : Dyah fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/generate_number/business/GenerateNumber.class.php';
require_once GTFWConfiguration::GetValue('application','docroot').
'module/user_unit_kerja/business/UserUnitKerja.class.php';

class TransaksiSpj extends Database
{
   # internal variables
   private $mNumber;
   protected $mSqlFile;
   protected $mUserId = null;
   public $_POST;
   public $_GET;
   public $method;
   # Constructor
   function __construct ($connectionNumber = 0)
   {
      $this->mSqlFile   = 'module/finansi_transaksi_spj/business/transaksi_spj.sql.php';
      $this->_POST      = is_object($_POST) ? $_POST->AsArray() : $_POST;
      $this->_GET       = is_object($_GET) ? $_GET->AsArray() : $_GET;
      $this->method     = strtolower($_SERVER['REQUEST_METHOD']);
      parent::__construct($connectionNumber);
      $this->mNumber    = new GenerateNumber($connectionNumber);
   }

   private function setUserId()
   {
      if(class_exists('Security')){
         if(method_exists(Security::Instance(), 'GetUserId')){
            $this->mUserId    = Security::Instance()->GetUserId();
         }else{
            $this->mUserId    = Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
         }
      }
   }

   public function getUserId()
   {
      $this->setUserId();
      return (int)$this->mUserId;
   }

   public function setDate()
   {
      $return     = $this->Open($this->mSqlQueries['get_date_range'], array());

      return self::ChangeKeyName($return[0]);
   }

   public function getTipeTransaksi()
   {
      $return     = $this->Open($this->mSqlQueries['get_tipe_transaksi'], array());

      return $return;
   }

   public function getJenisTransaksi()
   {
      $return     = $this->Open($this->mSqlQueries['get_jenis_transaksi'], array());

      return $return;
   }

   public function getUnitInfo($id)
   {
      $return     = $this->Open($this->mSqlQueries['get_unit_info'], array($id));

      return self::ChangeKeyName($return[0]);
   }

   public function getFilesTransaksi($id)
   {
      if (!defined('DS')) {
         define('DS', DIRECTORY_SEPARATOR);
      }
      $appDir     = GTFW_APP_DIR;
      $index      = 0;
      $files      = array();
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

      $return     = $this->Open($this->mSqlQueries['get_files_transaksi'], array(
         $id
      ));

      if(!empty($return)){
         $index   = 0;
         foreach ($return as $rt) {
            $fileName            = realpath($appDir . DS . $rt['path'] . DS . $rt['fileName']);
            $location            = $base . '/' . $rt['path'] . '/' . $rt['fileName'];
            $files[$index]['location']    = NULL;
            $files[$index]['path']        = $rt['fileName'];
            $files[$index]['name']        = preg_replace('/^SPJ(\d+)_+/', '', $rt['fileName']);
            $files[$index]['size']        = 0;
            $files[$index]['download']    = false;
            if(file_exists($fileName)){
               $files[$index]['size']     = self::_format_bytes(filesize($fileName));
               $files[$index]['download'] = true;
               $files[$index]['location'] = preg_replace('/\/[\/]/', '/', $location);
            }

            $index++;
         }
      }

      return $files;
   }

   public function doSaveTransaksi($data = array())
   {
      if (!defined('DS')) {
         define('DS', DIRECTORY_SEPARATOR);
      }
      $dataDir       = GTFW_APP_DIR . DS . 'file';
      $tmpDirUpload  = realpath($dataDir) . DS . 'tmp';
      $uploadDir     = realpath($dataDir) . DS . 'spj';
      $filePath      = str_replace(realpath(GTFW_APP_DIR), '', realpath($uploadDir));
      $result        = true;
      $this->StartTrans();
      if(!is_array($data)){
         $result  &= false;
      }

      // inisialisasi data untuk generate number
      $userId           = $this->getUserId();
      $unitInfo         = $this->getUnitInfo($data['unit_id']);
      $unitParent       = (empty($unitInfo)) ? NULL : (int)$unitInfo['parent_id'];
      $result           &= $this->Execute($this->mSqlQueries['set_tahun_pembukuan'], array());
      $result           &= $this->Execute($this->mSqlQueries['set_tahun_anggaran'], array());
      $result           &= $this->Execute($this->mSqlQueries['do_set_tahun_pembukuan'], array());
      $result           &= $this->Execute($this->mSqlQueries['do_set_tahun_anggaran'], array());

      $referensi        = $this->mNumber->getTransReference(
         $data['tipe_transaksi'],
         $data['unit_id'],
         $data['tanggal']
      );

      // insert transaksi
      $result           &= $this->Execute($this->mSqlQueries['do_insert_transaksi'], array(
         $data['tipe_transaksi'],
         $data['jenis_transaksi'],
         $data['unit_id'],
         $userId,
         date('Y-m-d', strtotime($data['tanggal'])),
         date('Y-m-d', strtotime($data['tanggal'])),
         date('Y-m-d', strtotime($data['due_date'])),
         $data['uraian'],
         $data['nominal'],
         $data['penanggung_jawab']
      ));
      $transaksiId      = $this->LastInsertId();

      if((int)$data['tipe_transaksi'] === 6){
         $result     &= $this->Execute($this->mSqlQueries['do_insert_transaksi_spj'], array(
            $transaksiId,
            $data['kegiatan_id']
         ));
      }elseif ((int)$data['tipe_transaksi'] == 5 AND $unitParent === NULL) {
         $result     &= $this->Execute($this->mSqlQueries['do_add_transaksi_detil_pengembalian_anggaran'], array(
            $transaksiId,
            $data['kegiatan_id'],
            $data['realisasi_id']
         ));
      }elseif ((int)$data['tipe_transaksi'] === 4 AND $unitParent !== NULL) {
         $result     &= $this->Execute($this->mSqlQueries['do_add_transaksi_detil_pencairan'], array(
            $transaksiId,
            $data['kegiatan_id'],
            $data['realisasi_id']
         ));
      }else{
         $result     &= $this->Execute($this->mSqlQueries['do_add_transaksi_detil_anggaran'], array(
            $transaksiId,
            $data['kegiatan_id'],
            $data['realisasi_id']
         ));
      }

      // insert file attachment
      if($data['attachment'] && !empty($data['attachment'])){
         foreach ($data['attachment'] as $attachment) {
            $srcFile    = $tmpDirUpload . DS . $attachment['path'];
            $destFile   = $uploadDir . DS . $attachment['path'];
            if(file_exists(realpath($srcFile))){
               if(rename($srcFile, $destFile)){
                  $result  &= true;
               }else{
                  $result  &= false;
               }
            }
            $result  &= $this->Execute($this->mSqlQueries['do_save_attachment_transaksi'], array(
               $transaksiId,
               $attachment['path'],
               $filePath
            ));
         }
      }

      if($data['invoices'] && !empty($data['invoices'])){
         foreach ($data['invoices'] as $inv) {
            $result  &= $this->Execute($this->mSqlQueries['do_save_invoice_transaksi'], array(
               $inv['nomor'],
               $transaksiId
            ));
         }
      }

      // $result           &= false;
      $return['result']    = $this->EndTrans($result);
      $return['trans_id']  = $transaksiId;
      return $return;
   }

   public function getTransaksiDetail($id)
   {
      $return     = $this->Open($this->mSqlQueries['get_data_detail'], array(
         $id
      ));

      return self::ChangeKeyName($return[0]);
   }

   public function getInvoiceTransaksi($id)
   {
      $return     = $this->Open($this->mSqlQueries['get_invoice_transaksi'], array(
         $id
      ));

      return self::ChangeKeyName($return);
   }

   /*
    * @param string $camelCasedWord Camel-cased word to be "underscorized"
    * @param string $case case type, uppercase, lowercase
    * @return string Underscore-syntaxed version of the $camelCasedWord
    */
   public static function humanize($camelCasedWord, $case = 'upper')
   {
      switch ($case) {
         case 'upper':
            $return     = strtoupper(preg_replace('/(?<=\w)([A-Z])/', '_\1', $camelCasedWord));
            break;
         case 'lower':
            $return     = strtolower(preg_replace('/(?<=\w)([A-Z])/', '_\1', $camelCasedWord));
            break;
         case 'title':
            $return     = ucwords(preg_replace('/(?<=\w)([A-Z])/', '_\1', $camelCasedWord));
            break;
         case 'sentences':
            $return     = ucfirst(preg_replace('/(?<=\w)([A-Z])/', '_\1', $camelCasedWord));
            break;
         default:
            $return     = strtoupper(preg_replace('/(?<=\w)([A-Z])/', '_\1', $camelCasedWord));
            break;
      }
      return $return;
   }

   /*
    * @desc change key name from input data
    * @param array $input
    * @param string $case based on humanize method
    * @return array
    */
   public function ChangeKeyName($input = array(), $case = 'lower')
   {
      if(!is_array($input)){
         return $input;
      }

      foreach ($input as $key => $value) {
         if(is_array($value)){
            foreach ($value as $k => $v) {
               $array[$key][self::humanize($k, $case)] = $v;
            }
         }
         else{
            $array[self::humanize($key, $case)]  = $value;
         }
      }

      return (array)$array;
   }

   /**
    * @param string  path_info url to be parsed, default null
    * @return string parsed_url based on Dispatcher::Instance()->getQueryString();
    */
   public function _getQueryString($pathInfo = null)
   {
      $parseUrl            = is_null($pathInfo) ? parse_url($_SERVER['QUERY_STRING']) : parse_url($pathInfo);
      $explodedUrl         = explode('&', $parseUrl['path']);
      $requestData         = '';
      foreach ($explodedUrl as $path) {
         if(preg_match('/^mod=[a-zA-Z0-9_-]+/', $path)){
            continue;
         }
         if(preg_match('/^sub=[a-zA-Z0-9_-]+/', $path)){
            continue;
         }
         if(preg_match('/^act=[a-zA-Z0-9_-]+/', $path)){
            continue;
         }
         if(preg_match('/^typ=[a-zA-Z0-9_-]+/', $path)){
            continue;
         }
         if(preg_match('/^ascomponent=[a-zA-Z0-9_-]+/', $path)){
            continue;
         }
         if(preg_match('/uniqid=[a-zA-Z0-9_-]+/', $path)){
            continue;
         }

         list($key, $value)   = explode('=', $path);
         $requestData[$key]   = Dispatcher::Instance()->Decrypt($value);
      }
      if(method_exists(Dispatcher::Instance(), 'getQueryString') === true){
         $queryString         = Dispatcher::Instance()->getQueryString($requestData);
      }else{
         foreach ($requestData as $key => $value) {
            $query[$key]      = Dispatcher::Instance()->Encrypt($value);
         }
         $queryString         = urldecode(http_build_query($query));
      }
      return $queryString;
   }

   public function getModule($pathInfo = null)
   {
      $module              = NULL;
      if(is_null($pathInfo)){
         $parseUrl         = parse_url($_SERVER['QUERY_STRING']);
         $explodedUrl      = explode('&', $parseUrl['path']);
      }else{
         $parseUrl         = parse_url($pathInfo);
         $explodedUrl      = explode('&', $parseUrl['query']);
      }

      foreach ($explodedUrl as $path) {
         if(preg_match('/^mod=[a-zA-Z0-9_-]+/', $path, $matches)){
            list($key, $value)   = explode('=', $matches[0]);
            $module        = $value;
         }
      }

      return $module;
   }

   public function getSubModule($pathInfo = null)
   {
      $subModule           = NULL;
      if(is_null($pathInfo)){
         $parseUrl         = parse_url($_SERVER['QUERY_STRING']);
         $explodedUrl      = explode('&', $parseUrl['path']);
      }else{
         $parseUrl         = parse_url($pathInfo);
         $explodedUrl      = explode('&', $parseUrl['query']);
      }

      foreach ($explodedUrl as $path) {
         if(preg_match('/^sub=[a-zA-Z0-9_-]+/', $path, $matches)){
            list($key, $value)   = explode('=', $matches[0]);
            $subModule     = $value;
         }
      }

      return $subModule;
   }

   public function getAction($pathInfo = null)
   {
      $action           = NULL;
      if(is_null($pathInfo)){
         $parseUrl         = parse_url($_SERVER['QUERY_STRING']);
         $explodedUrl      = explode('&', $parseUrl['path']);
      }else{
         $parseUrl         = parse_url($pathInfo);
         $explodedUrl      = explode('&', $parseUrl['query']);
      }

      foreach ($explodedUrl as $path) {
         if(preg_match('/^act=[a-zA-Z0-9_-]+/', $path, $matches)){
            list($key, $value)   = explode('=', $matches[0]);
            $action        = $value;
         }
      }

      return $action;
   }

   public function getType($pathInfo = null)
   {
      $type                = NULL;
      if(is_null($pathInfo)){
         $parseUrl         = parse_url($_SERVER['QUERY_STRING']);
         $explodedUrl      = explode('&', $parseUrl['path']);
      }else{
         $parseUrl         = parse_url($pathInfo);
         $explodedUrl      = explode('&', $parseUrl['query']);
      }

      foreach ($explodedUrl as $path) {
         if(preg_match('/^typ=[a-zA-Z0-9_-]+/', $path, $matches)){
            list($key, $value)   = explode('=', $matches[0]);
            $type          = $value;
         }
      }

      return $type;
   }

   public function returnBytes($val) {
      $val = trim($val);
      $last = strtolower($val[strlen($val)-1]);
      switch($last) {
         // The 'G' modifier is available since PHP 5.1.0
         case 'g':
            $val *= 1024;
         case 'm':
            $val *= 1024;
         case 'k':
            $val *= 1024;
      }

      return $val;
   }

   public function _format_bytes($a_bytes) {
      if ($a_bytes < 1024) {
         return $a_bytes .' B';
      } elseif ($a_bytes < 1048576) {
         return round($a_bytes / 1024, 2) .' KiB';
      } elseif ($a_bytes < 1073741824) {
         return round($a_bytes / 1048576, 2) . ' MiB';
      } elseif ($a_bytes < 1099511627776) {
         return round($a_bytes / 1073741824, 2) . ' GiB';
      } elseif ($a_bytes < 1125899906842624) {
         return round($a_bytes / 1099511627776, 2) .' TiB';
      } elseif ($a_bytes < 1152921504606846976) {
         return round($a_bytes / 1125899906842624, 2) .' PiB';
      } elseif ($a_bytes < 1180591620717411303424) {
         return round($a_bytes / 1152921504606846976, 2) .' EiB';
      } elseif ($a_bytes < 1208925819614629174706176) {
         return round($a_bytes / 1180591620717411303424, 2) .' ZiB';
      } else {
         return round($a_bytes / 1208925819614629174706176, 2) .' YiB';
      }
   }
}
?>