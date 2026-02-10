<?php
/**
* ================= doc ====================
* FILENAME     : HistoryTransaksiSpj.class.php
* @package     : HistoryTransaksiSpj
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-04-24
* @Modified    : 2015-04-24
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/generate_number/business/GenerateNumber.class.php';
require_once GTFWConfiguration::GetValue('application','docroot').
'module/user_unit_kerja/business/UserUnitKerja.class.php';

class HistoryTransaksiSpj extends Database
{
   # internal variables
   private $mNumber;
   protected $mSqlFile;
   protected $mUserId = null;
   public $_POST;
   public $_GET;
   public $method;
   public $indonesianMonth    = array(
      0 => array(
         'id' => 0,
         'name' => 'N/A'
      ), array(
         'id' => 1,
         'name' => 'Januari'
      ), array(
         'id' => 2,
         'name' => 'Februari'
      ), array(
         'id' => 3,
         'name' => 'Maret'
      ), array(
         'id' => 4,
         'name' => 'April'
      ), array(
         'id' => 5,
         'name' => 'Mei'
      ), array(
         'id' => 6,
         'name' => 'Juni'
      ), array(
         'id' => 7,
         'name' => 'Juli'
      ), array(
         'id' => 8,
         'name' => 'Agustus'
      ), array(
         'id' => 9,
         'name' => 'September'
      ), array(
         'id' => 10,
         'name' => 'Oktober'
      ), array(
         'id' => 11,
         'name' => 'November'
      ), array(
         'id' => 12,
         'name' => 'Desember'
      )
   );
   # Constructor
   function __construct ($connectionNumber = 0)
   {
      $this->mSqlFile   = 'module/finansi_transaksi_spj_history/business/history_transaksi_spj.sql.php';
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

   public function Count()
   {
      $return     = $this->Open($this->mSqlQueries['count'], array());
      if($return){
         return $return[0]['count'];
      }else{
         return 0;
      }
   }

   public function getTahunPeriode($param = array())
   {
      $default    = array(
         'active' => false,
         'open' => false
      );
      $options    = array_merge($default, (array)$param);
      $return     = $this->Open($this->mSqlQueries['get_periode_tahun'], array(
         (int)($options['active'] === false),
         (int)($options['open'] === false)
      ));

      return $return;
   }

   public function getTahunPembukuan($active = false)
   {
      $return     = self::ChangeKeyName($this->Open($this->mSqlQueries['get_tahun_pembukuan'], array(
         (int)($active === false OR $active === NULL)
      )));
      $result     = array();
      if(!empty($return)){
         $index   = 0;
         foreach ($return as $rt) {
            $tanggal_awal     = self::indonesianDate(
               date('Y-m-d', strtotime($rt['tanggal_awal'])),
               'long'
            );
            $tanggal_akhir    = self::indonesianDate(
               date('Y-m-d', strtotime($rt['tanggal_akhir'])),
               'long'
            );
            $result[$index]['id']   = $rt['id'];
            $result[$index]['name'] = $tanggal_awal.' - '.$tanggal_akhir;
            $index+=1;
         }
      }

      return $result;
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

   public function getDataTransaksi($offset, $limit, $param = array())
   {
      $return     = $this->Open($this->mSqlQueries['get_data_transaksi'], array(
         $param['ta_id'],
         $param['mak_id'],
         (int)($param['mak_id'] === NULL OR $param['mak_id'] == ''),
         '%'.$param['kode'],
         $param['unit_id'],
         $param['unit_id'],
         $param['unit_id'],
         $param['tanggal_awal'],
         $param['tanggal_akhir'],
         $offset,
         $limit
      ));

      return self::ChangeKeyName($return);
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

   public function doDeleteFile($name)
   {
      $result     = $this->Execute($this->mSqlQueries['do_delete_file'], array(
         $name
      ));

      return $result;
   }

   public function doUpdateTransaksi($data = array())
   {
      if (!defined('DS')) {
         define('DS', DIRECTORY_SEPARATOR);
      }
      $transaksiId   = $data['id'];
      $dataFiles     = $this->getFilesTransaksi($transaksiId);
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
      $result           &= $this->Execute($this->mSqlQueries['do_delete_trans_spj_detail'], array(
         $transaksiId
      ));
      $result           &= $this->Execute($this->mSqlQueries['do_delete_invoice_transaksi'], array(
         $transaksiId
      ));
      $result           &= $this->Execute($this->mSqlQueries['do_delete_file_transaksi'], array(
         $transaksiId
      ));

      // insert transaksi
      $result           &= $this->Execute($this->mSqlQueries['do_update_transaksi'], array(
         $data['tipe_transaksi'],
         $data['jenis_transaksi'],
         $data['unit_id'],
         $userId,
         date('Y-m-d', strtotime($data['tanggal'])),
         date('Y-m-d', strtotime($data['tanggal'])),
         date('Y-m-d', strtotime($data['due_date'])),
         $data['uraian'],
         $data['nominal'],
         $data['penanggung_jawab'],
         $transaksiId
      ));

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

   public function doDeleteTransaksi($id)
   {
      if (!defined('DS')) {
         define('DS', DIRECTORY_SEPARATOR);
      }
      $dataFiles     = $this->getFilesTransaksi($id);
      $dataDir       = GTFW_APP_DIR . DS . 'file';
      $tmpDirUpload  = realpath($dataDir) . DS . 'tmp';
      $uploadDir     = realpath($dataDir) . DS . 'spj';
      $filePath      = str_replace(realpath(GTFW_APP_DIR), '', realpath($uploadDir));
      $result        = true;
      $this->StartTrans();
      if(!$id){
         $result     &= false;
      }
      $result           &= $this->Execute($this->mSqlQueries['do_delete_trans_spj_detail'], array(
         $id
      ));
      $result           &= $this->Execute($this->mSqlQueries['do_delete_invoice_transaksi'], array(
         $id
      ));
      $result           &= $this->Execute($this->mSqlQueries['do_delete_file_transaksi'], array(
         $id
      ));
      $result           &= $this->Execute($this->mSqlQueries['do_delete_transaksi'], array(
         $id
      ));
      if($result === true){
         if(!empty($dataFiles)){
         foreach ($dataFiles as $file) {
            // jika file tidak ada abaikan
            if($file['location'] === NULL){
               continue;
            }
            $file    = realpath($filePath . DS . $file['path']);
            unlink($file);
         }
      }
      }
      return $this->EndTrans($result);
   }

   #tambahan untuk cetak bukti transaksi
   function GetJabatanNama($key) {
      $result = $this->Open($this->mSqlQueries['get_jabatan'], array('%'.$key.'%'));
      return $result;
   }

   function GetJabatan($jab) {
      $result = $this->Open($this->mSqlQueries['get_nama_pejabat'], array($jab));
      return $result[0]['nama'];
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

   /**
    * @required indonesianMonths
    * @param String $date date format YYYY-mm-dd H:i:s, YYYY-mm-dd
    * @param String $format long, short
    * @return String  Indonesian Date
    */
   public function indonesianDate($date, $format = 'long')
   {
      $timeFormat          = '%02d:%02d:%02d';
      $patern              = '/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})/';
      $patern1             = '/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})/';
      switch ($format) {
         case 'long':
            $dateFormat    = '%02d %s %04d';
            break;
         case 'short':
            $dateFormat    = '%02d-%s-%04d';
            break;
         default:
            $dateFormat    = '%02d %s %04d';
            break;
      }

      if(preg_match($patern, $date, $matches)){
         $year    = (int)$matches[1];
         $month   = (int)$matches[2];
         $day     = (int)$matches[3];
         $hour    = (int)$matches[4];
         $minute  = (int)$matches[5];
         $second  = (int)$matches[6];
         $mon     = $this->indonesianMonth[$month];

         $date    = sprintf($dateFormat, $day, $mon, $year);
         $time    = sprintf($timeFormat, $hour, $minute, $second);
         $result  = $date.' '.$time;

      }elseif(preg_match($patern1, $date, $matches)){
         $year    = (int)$matches[1];
         $month   = (int)$matches[2];
         $day     = (int)$matches[3];
         $mon     = $this->indonesianMonth[$month]['name'];

         $date    = sprintf($dateFormat, $day, $mon, $year);

         $result  = $date;
      }else{
         $date    = getdate();
         $year    = (int)$date['year'];
         $month   = (int)$date['mon'];
         $day     = (int)$date['mday'];
         $hour    = (int)$date['hours'];
         $minute  = (int)$date['minutes'];
         $second  = (int)$date['seconds'];
         $mon     = $this->indonesianMonth[$month]['name'];

         $date    = sprintf($dateFormat, $day, $mon, $year);
         $time    = sprintf($timeFormat, $hour, $minute, $second);
         $result  = $date.' '.$time;
      }

      return $result;
   }
}
?>