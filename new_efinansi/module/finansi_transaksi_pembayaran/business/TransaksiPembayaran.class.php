<?php
/**
* ================= doc ====================
* FILENAME     : TransaksiPembayaran.class.php
* @package     : TransaksiPembayaran
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-04-22
* @Modified    : 2015-04-22
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/rest/business/RestDb.class.php';

class TransaksiPembayaran extends Database
{
   # internal variables
   protected $mUserId = NULL;
   private $appPembayaran  = 520;
   protected $mSqlFile;
   private $mRest;
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
      $this->_POST      = is_object($_POST) ? $_POST->AsArray() : $_POST;
      $this->_GET       = is_object($_GET) ? $_GET->AsArray() : $_GET;
      $this->mSqlFile   = 'module/finansi_transaksi_pembayaran/business/transaksi_pembayaran.sql.php';
      $this->mRest      = new RestDb();
      $this->method     = strtolower($_SERVER['REQUEST_METHOD']);
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

   public function getRangeYear()
   {
      $return     = $this->Open($this->mSqlQueries['get_range_year'], array());
      $getdate    = getdate();
      $currYear   = (int)$getdate['year'];
      $currMon    = (int)$getdate['mon'];
      $currDay    = (int)$getdate['mday'];

      return self::ChangeKeyName($return[0]);
   }

   public function Count()
   {
      $return     = $this->Open($this->mSqlQueries['count'], array());
      if($return){
         return (int)$return[0]['count'];
      }else{
         return 0;
      }
   }

   public function getDataTransaksi($offset, $limit, $param = array())
   {
      $return     = $this->Open($this->mSqlQueries['get_data_transaksi'], array(
         '%'.$param['referensi'].'%',
         $param['tanggal_awal'],
         $param['tanggal_akhir'],
         $offset,
         $limit
      ));

      return self::ChangeKeyName($return);
   }

   public function getDataTransaksiDetail($id)
   {
      $return     = $this->Open($this->mSqlQueries['get_transaksi_detail'], array(
         $id
      ));

      return self::ChangeKeyName($return);
   }

 
   /**
    * @package GetDataPeriodePembayaran
    */
   public function getDataPeriodePembayaran()
   {
      $module     = 'services';
      $subModule  = 'ReferensiPeriode';
      $action     = $type = 'rest';
      // RestDb::Instance()->setDebugOn();
      RestDb::Instance()->setApplication($this->appPembayaran);
      RestDb::Instance()->setModule($module);
      RestDb::Instance()->setSubModule($subModule);
      RestDb::Instance()->setAction($action);
      RestDb::Instance()->setType($type);
      $return     = RestDb::Instance()->SendNull('post');
      $result['status']    = $return['status'];
      $result['data_list'] = self::ChangeKeyName($return['data']);
      return $result; 
   }  
   
   /**
    * @package GetDatapiutang pembayaran dari applikasi pembayaran
    * @param  array  $param [tanggal_awal, tanggal_akhir]
    * @return Array  Data Pembayaran
    */
   public function getDataPiutangPembayaran($param = array(), $method = 'post')
   {
      $module     = 'services';
      switch (strtolower($param['type'])) {
         case 'piutang':
            $subModule  = 'TransaksiPiutang';
            break;
         case 'pengakuan':
            $subModule  = 'TransaksiPengakuan';
            break;
         default:
            $subModule  = 'TransaksiPiutang';
            break;
      }
      $action     = $type = 'rest';
      // RestDb::Instance()->setDebugOn();
      RestDb::Instance()->setApplication($this->appPembayaran);
      RestDb::Instance()->setModule($module);
      RestDb::Instance()->setSubModule($subModule);
      RestDb::Instance()->setAction($action);
      RestDb::Instance()->setType($type);
    
      $return     = RestDb::Instance()->Send((array)$param, $method);
      $result['status']    = $return['status'];
      $result['data_list'] = self::ChangeKeyName($return['data']);
      return $result;
   }

   public function doUpdateStatusPembayaran($param = array(), $status = 0)
   {
      $module     = 'services';
      switch (strtolower($param['type'])) {
         case 'piutang':
            $subModule  = 'TransaksiPiutang';
            break;
         case 'pengakuan':
            $subModule  = 'TransaksiPengakuan';
            break;
         default:
            $subModule  = 'TransaksiPiutang';
            break;
      }
      $action     = $type = 'rest';
      $param['isTransaksi']   = $status;
      // RestDb::Instance()->setDebugOn();
      RestDb::Instance()->setApplication($this->appPembayaran);
      RestDb::Instance()->setModule($module);
      RestDb::Instance()->setSubModule($subModule);
      RestDb::Instance()->setAction($action);
      RestDb::Instance()->setType($type);

      $return     = RestDb::Instance()->Send((array)$param, 'post');
      return $return;
   }

   public function doInsertTransaksiPembayaran($param = array())
   {
      $result     = true;
      $userId     = $this->getUserId();
      $this->StartTrans();
      if(!is_array($param)){
         $result  &= false;
      }
      $result  &= $this->Execute($this->mSqlQueries['set_tahun_pembukuan'], array());
      $result  &= $this->Execute($this->mSqlQueries['set_jenis_transaksi'], array());
      $result  &= $this->Execute($this->mSqlQueries['set_tipe_transaksi'], array());
      $result  &= $this->Execute($this->mSqlQueries['set_unit_kerja'], array());
      $result  &= $this->Execute($this->mSqlQueries['set_tahun_anggaran'], array());
      $result  &= $this->Execute($this->mSqlQueries['do_set_tahun_pembukuan'], array());
      $result  &= $this->Execute($this->mSqlQueries['do_set_jenis_transaksi'], array());
      $result  &= $this->Execute($this->mSqlQueries['do_set_unit_kerja'], array());
      $result  &= $this->Execute($this->mSqlQueries['do_set_tahun_anggaran'], array());
      $result  &= $this->Execute($this->mSqlQueries['do_set_tipe_transaksi'], array());

      foreach ($param as $pr) {
         $result  &= $this->Execute($this->mSqlQueries['do_set_referensi'], array());
         $result  &= $this->Execute($this->mSqlQueries['do_set_referensi'], array(
            $pr['type']
         ));
         $result  &= $this->Execute($this->mSqlQueries['do_insert_transaksi'], array(
            $userId,
            $pr['jenis_biaya'],
            $pr['nominal'],
            $pr['nama']
         ));
      }

      return $this->EndTrans($result);
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