<?php
/**
* ================= doc ====================
* FILENAME     : PeriodeTahun.class.php
* @package     : PeriodeTahun
* scope        : PUBLIC
* @Modified By : Eko Susilo
* @Modified    : 2014-05-30
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/rest/business/RestDb.class.php';

class PeriodeTahun extends Database
{
   protected $mSqlFile;
   public $_POST;
   public $_GET;
   public $method;

   public function __construct($connectionNumber=0)
   {
      $this->mSqlFile   = 'module/periode_tahun/business/periode_tahun.sql.php';
      $this->_POST      = is_object($_POST) ? $_POST->AsArray() : $_POST;
      $this->_GET       = is_object($_GET) ? $_GET->AsArray() : $_GET;
      $this->method     = strtolower($_SERVER['REQUEST_METHOD']);
      parent::__construct($connectionNumber);
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

   public function getData($offset, $limit, $param = array())
   {
      $return     = $this->Open($this->mSqlQueries['get_data'], array(
         $param['renstra_id'],
         (int)(($param['renstra_id'] === NULL or $param['renstra_id'] == '') OR strtolower($param['renstra_id']) == 'all'),
         '%'.$param['nama'].'%',
         $offset,
         $limit
      ));

      return $return;
   }

   public function getRenstra($active = false)
   {
      $return     = $this->Open($this->mSqlQueries['get_renstra'], array(
         (int)($active === false)
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

   public function doCheckData($renstraId, $nama, $id = NULL)
   {
      $return     = $this->Open($this->mSqlQueries['check_periode_tahun'], array(
         $renstraId,
         $nama,
         $id,
         (int)($id === NULL or $id == '')
      ));

      if($return){
         if($return[0]['count'] <> 0){
            return false;
         }else{
            return true;
         }
      }else{
         return false;
      }
   }

   public function doSaveData($param = array())
   {
      $result     = true;
      $this->StartTrans();
      if(!is_array($param)){
         $result  &= false;
      }

      // check data status aktif
      // jika status aktif di pilih unset smua status
      if(strtoupper($param['status_aktif']) == 'Y'){
         $result  &= $this->Execute($this->mSqlQueries['do_set_deaktif'], array());
      }

      // do insert data periode tahun
      $result     &= $this->Execute($this->mSqlQueries['do_insert_periode_tahun'], array(
         $param['nama'],
         $param['status_aktif'],
         $param['status_open'],
         date('Y-m-d', strtotime($param['start_date'])),
         date('Y-m-d', strtotime($param['end_date'])),
         $param['renstra_id']
      ));
      return $this->EndTrans($result);
   }

   public function doUpdateData($param = array())
   {
      $result     = true;
      $this->StartTrans();
      if(!is_array($param)){
         $result  &= false;
      }

      // do insert data periode tahun
      $result     &= $this->Execute($this->mSqlQueries['do_update_periode_tahun'], array(
         $param['nama'],
         $param['status_aktif'],
         $param['status_open'],
         date('Y-m-d', strtotime($param['start_date'])),
         date('Y-m-d', strtotime($param['end_date'])),
         $param['renstra_id'],
         $param['id']
      ));
      return $this->EndTrans($result);
   }

   public function doDeleteData($id)
   {
      $result     = true;
      $this->StartTrans();
      if(!$id){
         $result  &= false;
      }

      $result     &= $this->Execute($this->mSqlQueries['do_delete'], array(
         $id
      ));

      return $this->EndTrans($result);
   }

   public function doSetActive($id)
   {
       $result     = true;
      $this->StartTrans();
      if(!$id){
         $result  &= false;
      }
      $result     &= $this->Execute($this->mSqlQueries['do_set_deaktif'], array());
      $result     &= $this->Execute($this->mSqlQueries['do_set_aktif'], array(
         $id
      ));

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
}
?>