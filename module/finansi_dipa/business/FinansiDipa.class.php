<?php
/**
* ================= doc ====================
* FILENAME     : FinansiDipa.class.php
* @package     : FinansiDipa
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2014-12-07
* @Modified    : 2014-12-07
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/

class FinansiDipa extends Database
{
   # internal variables
   protected $mSqlFile;
   public $_POST;
   public $_GET;
   # Constructor
   function __construct ($connectionNumber = 0)
   {
      $this->_POST      = is_object($_POST) ? $_POST->AsArray() : $_POST;
      $this->_GET       = is_object($_GET) ? $_GET->AsArray() : $_GET;
      $this->mSqlFile   = 'module/finansi_dipa/business/finansi_dipa.sql.php';
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

   public function GetData($offset, $limit, $param = array())
   {
      $return     = $this->Open($this->mSqlQueries['get_data'], array(
         '%'.$param['kode'].'%',
         $offset,
         $limit
      ));

      return $return;
   }

   public function CheckDuplicate($kode = '', $id = null)
   {
      $return     = $this->Open($this->mSqlQueries['check_duplicate'], array(
         $id,
         (int)($id === NULL OR $id == ''),
         trim($kode),
         (int)($kode == '')
      ));

      if($return){
         if($return[0]['count'] <> 0){
            return true;
         }else{
            return false;
         }
      }else{
         return true;
      }
   }

   public function DoSaveDipa($param = array())
   {
      $result     = true;
      $this->StartTrans();
      $userId     = Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();

      if(strtoupper($param['status']) == 'Y'){
         $result  &= $this->Execute($this->mSqlQueries['reset_status'], array());
      }
      $result     &= $this->Execute($this->mSqlQueries['do_save_dipa'], array(
         $param['kode'],
         date('Y-m-d', strtotime($param['tanggal'])),
         $param['nominal'],
         $param['status'],
         $userId
      ));

      return $this->EndTrans($result);
   }

   public function DoDeleteDipa($id)
   {
      $result     = true;
      $this->StartTrans();
      $checkStatus   = $this->Open($this->mSqlQueries['check_dipa_aktif'], array(
         $id
      ));

      if($checkStatus !== NULL and count($checkStatus) <> 0){
         $result  &= false;
      }
      $result     &= $this->Execute($this->mSqlQueries['do_delete_dipa'], array($id));

      return $this->EndTrans($result);
   }

   public function GetDataDetail($id)
   {
      $return     = $this->Open($this->mSqlQueries['get_data_detail'], array(
         $id
      ));

      if($return){
         return $return[0];
      }else{
         return null;
      }
   }

   public function DoUpdateDipa($param = array())
   {
      $result     = true;
      $this->StartTrans();
      $userId     = Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
      $result     &= $this->Execute($this->mSqlQueries['do_update_dipa'], array(
         $param['kode'],
         date('Y-m-d', strtotime($param['tanggal'])),
         $param['nominal'],
         $userId,
         $param['id']
      ));

      return $this->EndTrans($result);
   }

   public function DoSetAktifDipa($id)
   {
      $result     = true;
      $this->StartTrans();
      $result     &= $this->Execute($this->mSqlQueries['reset_status'], array());
      $result     &= $this->Execute($this->mSqlQueries['set_aktif_dipa'], array($id));

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
}
?>