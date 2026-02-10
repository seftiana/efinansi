<?php
/**
* ================= doc ====================
* FILENAME     : MataAnggaran.class.php
* @package     : MataAnggaran
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-03-27
* @Modified    : 2015-03-27
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/

class MataAnggaran extends Database
{
   # internal variables
   protected $mSqlFile;
   public $_POST;
   public $_GET;
   public $method;
   # Constructor
   function __construct ($connectionNumber = 0)
   {
      $this->mSqlFile   = 'module/finansi_mata_anggaran/business/mata_anggaran.sql.php';
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
         '%'.$param['bas_kode'].'%',
         '%'.$param['bas_kode'].'%',
         '%'.$param['kode'].'%',
         '%'.$param['nama'].'%',
         $offset,
         $limit
      ));

      return self::ChangeKeyName($return);
   }

   public function getDataDetail($id)
   {
      $return     = $this->Open($this->mSqlQueries['get_data_detail'], array(
         $id
      ));

      return self::ChangeKeyName($return[0]);
   }

   public function getBasType()
   {
      $return     = $this->Open($this->mSqlQueries['get_bas_tipe'], array());

      return $return;
   }

   public function doCheckMataAnggaran($basId, $makKode, $id = NULL)
   {
      $return     = $this->Open($this->mSqlQueries['do_check_mata_anggaran'], array(
         $basId,
         $id,
         (int)($id === NULL OR $id == ''),
         $makKode
      ));

      if($return){
         if($return[0]['count'] <> 0){
            return false;
         }else{
            return true;
         }
      }else{
         return true;
      }
   }

   public function doSaveData($param = array())
   {
      $result     = true;
      $this->StartTrans();
      if(!is_array($param)){
         $result  &= false;
      }

      $result     &= $this->Execute($this->mSqlQueries['do_insert_mata_anggaran'], array(
         $param['kode'],
         $param['bas_id'],
         $param['nilai_default'],
         $param['status'],
         $param['nama']
      ));

      $makId      = $this->LastInsertId();
      $result     &= $this->Execute($this->mSqlQueries['do_delete_pagu_bas_tipe'], array($makId));
      $result     &= $this->Execute($this->mSqlQueries['do_delete_coa_mata_anggaran'], array($makId));
      if($param['tipe'] != ''){
         $result     &= $this->Execute($this->mSqlQueries['do_insert_mata_anggaran_tipe'], array(
            $param['tipe'],
            $makId
         ));
      }

      if($param['coa_id'] != ''){
         $result     &= $this->Execute($this->mSqlQueries['do_insert_mata_anggaran_coa'], array(
            $param['coa_id'],
            $makId
         ));
      }

      return $this->EndTrans($result);
   }

   public function doUpdateData($param = array())
   {
      $result     = true;
      $this->StartTrans();
      if(!is_array($param)){
         $result  &= false;
      }

      $result     &= $this->Execute($this->mSqlQueries['do_update_mata_anggaran'], array(
         $param['kode'],
         $param['bas_id'],
         $param['nilai_default'],
         $param['status'],
         $param['nama'],
         $param['id']
      ));

      $makId      = $param['id'];
      $result     &= $this->Execute($this->mSqlQueries['do_delete_pagu_bas_tipe'], array($makId));
      $result     &= $this->Execute($this->mSqlQueries['do_delete_coa_mata_anggaran'], array($makId));
      if($param['tipe'] != ''){
         $result     &= $this->Execute($this->mSqlQueries['do_insert_mata_anggaran_tipe'], array(
            $param['tipe'],
            $makId
         ));
      }

      if($param['coa_id'] != ''){
         $result     &= $this->Execute($this->mSqlQueries['do_insert_mata_anggaran_coa'], array(
            $param['coa_id'],
            $makId
         ));
      }

      return $this->EndTrans($result);
   }

   public function doDeleteData($id)
   {
      $result     = true;
      $this->StartTrans();
      if(!$id){
         $result  &= false;
      }
      $result     &= $this->Execute($this->mSqlQueries['do_delete_pagu_bas_tipe'], array($id));
      $result     &= $this->Execute($this->mSqlQueries['do_delete_coa_mata_anggaran'], array($id));
      $result     &= $this->Execute($this->mSqlQueries['do_delete_mata_anggaran'], array($id));

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