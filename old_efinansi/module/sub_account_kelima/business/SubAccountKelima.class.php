<?php

/**
 * Class SubAccountKelima
 * @package sub_account_kelima
 * @copyright 2011 Gamatechno
 */

 /**
  * Class SubAccountKelima
  * @todo Untuk menghandle manipulasi data dalam tabel finansi_keu_ref_subacc_5
  */
class SubAccountKelima extends Database
{
   protected $mSqlFile;
   public $_POST;
   public $_GET;
   public $method;

   public function __construct($connectionNumber=0)
   {
      $this->mSqlFile   = 'module/sub_account_kelima/business/sub_account_kelima.sql.php';
      $this->_POST      = is_object($_POST) ? $_POST->AsArray() : $_POST;
      $this->_GET       = is_object($_GET) ? $_GET->AsArray() : $_GET;
      $this->method     = strtolower($_SERVER['REQUEST_METHOD']);
      parent::__construct($connectionNumber);
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

   public function getData($offset, $limit, $kode = '')
   {
      $return     = $this->Open($this->mSqlQueries['get_data'], array(
         '%'.$kode.'%',
         '%'.$kode.'%',
         $offset,
         $limit
      ));

      return $return;
   }

   public function getDataDetail($id)
   {
      $return     = $this->Open($this->mSqlQueries['get_data_detail'], array(
         $id
      ));

      return $return[0];
   }

   public function doCheckUniqueData($kode, $id = NULL)
   {
      $return  = $this->Open($this->mSqlQueries['do_check_unique_data'], array(
         $id,
         (int)($id === NULL OR $id == ''),
         $kode
      ));

      if($return){
         if($return[0]['count'] <> 0){
            return false;
         }else{
            return true;
         }
      }else{
         // ada yang salah dengan execute query
         return false;
      }
   }

   public function doCheckDefaultSubAccount()
   {
      $return     = $this->Open($this->mSqlQueries['check_subaccount_default'], array());

      if($return){
         if($return[0]['count'] <> 0){
            $return['result']    = true;
            $return['patern']    = $return[0]['patern'];
         }else{
            $return['result']    = false;
            $return['patern']    = $return[0]['patern'];
         }
      }else{
         $return['result']    = false;
         $return['patern']    = '*****';
      }

      return $return;
   }

   /**
    * function DoAdd
    * Untuk menangani proses simpan data
    * @param $subAccKode string Kode Sub Account
    * @param $subAccNama string Nama Sub Account
    * @access public
    */
   public function DoAdd($subAccKode,$subAccNama)
   {
      return $this->Execute($this->mSqlQueries['do_add_sub_account_kelima'], array(
         $subAccKode,
         $subAccNama
      ));
   }

   /**
    * function DoUpdate
    * Untuk menangani proses udpate data
    * @param $subAccKode string Kode Sub Account
    * @param $subAccNama string Nama Sub Account
    * @param $id string Kode Sub Account yang akan di edit
    * @access public
    */
   public function DoUpdate($subAccKode,$subAccNama,$id)
   {
      return $this->Execute($this->mSqlQueries['do_update_sub_account_kelima'],
         array($subAccKode,$subAccNama,$id));
   }

   /**
    * function DoDelete
    * Untuk menangani proses hapus data
    * @param $subAccKode string Kode Sub Account
    * @access public
    */
   public function DoDelete($subAccKode)
   {
      return $this->Execute($this->mSqlQueries['do_delete_sub_account_kelima'], array($subAccKode));
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
}
?>