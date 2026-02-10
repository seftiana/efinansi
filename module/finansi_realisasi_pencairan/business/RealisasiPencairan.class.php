<?php
/**
* ================= doc ====================
* FILENAME     : RealisasiPencairan.class.php
* @package     : RealisasiPencairan
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-04-01
* @Modified    : 2015-04-01
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/user_unit_kerja/business/UserUnitKerja.class.php';
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/generate_number/business/GenerateNumber.class.php';

class RealisasiPencairan extends Database
{
   # internal variables
   protected $mSqlFile;
   private $mUnitObj;
   private $mNumber;
   public $_POST;
   public $_GET;
   public $method;
   
   private $mLimitKasKecil = 500000;
   protected $mUserId = NULL;
   # Constructor
   function __construct ($connectionNumber = 0)
   {
      $this->mSqlFile   = 'module/finansi_realisasi_pencairan/business/realisasi_pencairan.sql.php';
      $this->_POST      = is_object($_POST) ? $_POST->AsArray() : $_POST;
      $this->_GET       = is_object($_GET) ? $_GET->AsArray() : $_GET;
      $this->method     = strtolower($_SERVER['REQUEST_METHOD']);
      $this->mUnitObj   = new UserUnitKerja($connectionNumber);
      $this->mNumber    = new GenerateNumber($connectionNumber);
      parent::__construct($connectionNumber);
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

   public function getTypeTransaksi()
   {
      $return     = $this->Open($this->mSqlQueries['get_tipe_transaksi'], array());

      return $return;
   }

   public function getJenisTransaksi()
   {
      $return     = $this->Open($this->mSqlQueries['get_jenis_transaksi'], array());

      return $return;
   }

   public function getSubAccount()
   {
      $return     = $this->Open($this->mSqlQueries['get_sub_account_default'], array());
      $result['patern']    = $return[0]['patern'];
      $result['regex']     = $return[0]['regex'];
      $result['sub_akun']  = $return[0]['default'];

      return $result;
   }

   public function getRangeTanggalPembukuan(){
      $return = $this->Open($this->mSqlQueries['get_range_tanggal_pembukuan'],array());

      return $return[0];
   }
   
   public function SaveTransaksi($data = array())
   {//$this->SetDebugOn();
      if (!defined('DS')) {
         define('DS', DIRECTORY_SEPARATOR);
      }
      $dataDir       = GTFW_APP_DIR . DS . 'file';
      $tmpDirUpload  = realpath($dataDir) . DS . 'tmp';
      $uploadDir     = realpath($dataDir) . DS . 'realisasi_pencairan';
      $filePath      = str_replace(realpath(GTFW_APP_DIR), '', realpath($uploadDir));
      $result        = true;
      $userId        = $this->getUserId();
      $subAccount    = $this->getSubAccount();
      $subAkun       = $subAccount['sub_akun'];
      list($subacc_1,$subacc_2,$subacc_3,$subacc_4,$subacc_5,$subacc_6,$subacc_7) = explode('-', $subAkun);
      $transaksiId   = NULL;
      $pembukuanId   = NULL;
      $blnRealisasai = date('m', strtotime($data['tanggal']));
      $this->StartTrans();
      if(!is_array($data)){
         $result  &= false;
      }
      /*
      $nomorReferensi   = $this->mNumber->getTransReference(
         $data['tipe_transaksi_id'],
         $data['unit_id'],
         date('Y-m-d', strtotime($data['tanggal']))
      );
      */
      $nomorReferensiKK   = $this->mNumber->getTransReferenceCPKasKecil(
           date('Y-m-d', strtotime($data['tanggal']))
        );
      
      if(  $data['nominal'] > $this->mLimitKasKecil){
        //no ref kas besar
        $nomorReferensi   = $this->mNumber->getTransReferenceCP(
           date('Y-m-d', strtotime($data['tanggal']))
        );
      } else {
        //no ref kas kecil  
        $nomorReferensi   = $nomorReferensiKK;
      } 
      // set
      $result     &= $this->Execute($this->mSqlQueries['set_tahun_anggaran'], array());
      $result     &= $this->Execute($this->mSqlQueries['set_tahun_pembukuan'], array());
      $result     &= $this->Execute($this->mSqlQueries['do_set_tahun_anggaran'], array());
      $result     &= $this->Execute($this->mSqlQueries['do_set_tahun_pembukuan'], array());
      // insert into
      $result     &= $this->Execute($this->mSqlQueries['do_insert_transaksi'], array(
         $data['tipe_transaksi_id'],
         $data['jenis_transaksi_id'],
         $data['unit_id'],
         $nomorReferensi,
         $userId,
         date('Y-m-d', strtotime($data['tanggal'])),
         date('Y-m-d', strtotime($data['due_date'])),
         $data['keterangan'],
         $data['nominal'],
         $data['penanggung_jawab'],
         $data['penerima'],
         $data['auto_jurnal']
      ));

      $transaksiId   = $this->LastInsertId();
     
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

      // jika skenario jurnal = AUTO
      if(strtoupper($data['skenario']) == 'AUTO'){
         if($data['skenario_jurnal'] && !empty($data['skenario_jurnal'])){
            $result     &= $this->Execute($this->mSqlQueries['do_insert_pembukuan_referensi'], array(
               $transaksiId,
               $userId,
               date('Y-m-d', strtotime($data['tanggal'])),
               $data['keterangan']
            ));

            $pembukuanId   = $this->LastInsertId();
            $skenarioId    = array();
            foreach ($data['skenario_jurnal'] as $skenario) {
               $skenarioId[]  = $skenario['id'];
            }
            $result        &= $this->Execute($this->mSqlQueries['do_insert_pembukuan_detail'], array(
               $pembukuanId,
               $data['nominal'],
               $subacc_1,
               $subacc_2,
               $subacc_3,
               $subacc_4,
               $subacc_5,
               $subacc_6,
               $subacc_7,
               implode("','", $skenarioId)
            ));

         }

      }

      // insert transaksi detail anggaran      
      $result        &= $this->Execute($this->mSqlQueries['do_insert_transaksi_det_anggaran'], array(
         $transaksiId,
         $data['kegiatan_id'],
         $data['realisasi_id']
      ));
           
      // insert transaksi detail pencairan
      $result        &= $this->Execute($this->mSqlQueries['do_insert_transaksi_det_pencairan'], array(
         $transaksiId,
         $data['kegiatan_id'],
         $data['realisasi_id']
      ));

  // insert transaksi detail penc;airan komp belanja
        $transaksiDtPencairanId = $this->LastInsertId();
      if($data['komponen'] && !empty($data['komponen'])){
         foreach ($data['komponen'] as $komp) {
             $result &= $this->Execute($this->mSqlQueries['do_insert_transaksi_det_pencairan_komp_belanja'], array(
                    $transaksiDtPencairanId,
                    $komp['pd_id'],
                    $komp['nominal']
               ));
         }
      }     
      $return['result']       = $this->EndTrans($result);
      $return['trans_id']     = $transaksiId;
      $return['pembukuan_id'] = $pembukuanId;
      return $return;
   }

   public function getTransaksiDetail($id)
   {
      $return     = $this->Open($this->mSqlQueries['get_data_detail'], array(
         $id
      ));

      return self::ChangeKeyName($return[0]);
   }

 public function getKomponenAnggaranByTransId($id)
   {
      $return     = $this->Open($this->mSqlQueries['get_komponen_anggaran_by_trans_id'], array(
         $id
      ));

      return self::ChangeKeyName($return);
   }
   
   public function getInvoiceTransaksi($id)
   {
      $return     = $this->Open($this->mSqlQueries['get_invoice_transaksi'], array(
         $id
      ));

      return self::ChangeKeyName($return);
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
         if(!empty($requestData)) {
            foreach ($requestData as $key => $value) {
                $query[$key]      = Dispatcher::Instance()->Encrypt($value);
            }
            $queryString         = urldecode(http_build_query($query));
         }
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