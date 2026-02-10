<?php
#doc
# package:     AppReferensi
# scope:       PUBLIC
# @created     ucil.619
# @Author      Eko Susilo
# @Created     2013-09-06
# @Modified    2013-09-06
# @Analysts    Nanang Ruswianto
# @copyright   Copyright (c) 2012 Gamatechno
#/doc

class AppReferensi extends Database
{
   #   internal variables
   protected $mSqlFile;
   public $_POST;
   public $_GET;
   protected $userId;
   #   Constructor
   function __construct ($connectionNumber = 0)
   {
      $this->_POST         = is_object($_POST) ? $_POST->AsArray() : $_POST;
      $this->_GET          = is_object($_GET) ? $_GET->AsArray() : $_GET;
      $this->userId        = Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
      $this->mSqlFile      = 'module/'.Dispatcher::Instance()->mModule.'/business/app_referensi.sql.php';
      parent::__construct($connectionNumber);
   }

   public function GetDataOutput($offset, $limit, $param = array())
   {
      $return     = $this->Open($this->mSqlQueries['get_data_output'], array(
         $param['taId'],
         (int)($param['taId'] == '' OR $param['taId'] === NULL),
         '%'.$param['kode_kegiatan'].'%',
         '%'.$param['kode_kegiatan'].'%',
         '%'.$param['kode'].'%',
         '%'.$param['kode'].'%',
         (int)$offset,
         (int)$limit
      ));

      return $return;
   }

   public function GetCountOutput($param = array())
   {
      $return     = $this->Open($this->mSqlQueries['get_count_output'], array(
         $param['taId'],
         (int)($param['taId'] == '' OR $param['taId'] === NULL),
         '%'.$param['kode_kegiatan'].'%',
         '%'.$param['kode_kegiatan'].'%',
         '%'.$param['kode'].'%',
         '%'.$param['kode'].'%'
      ));

      if($return){
         return $return[0]['count'];
      }else{
         return 0;
      }
   }

   public function GetDataProgramRef($offset, $limit, $param = array())
   {
      $opt           = array(
         'ta_id' => NULL,
         'kode' => NULL
      );
      $options       = array_merge($opt, (array)$param);
      $return        = $this->Open($this->mSqlQueries['get_data_program_ref'], array(
         $options['ta_id'],
         (int)($options['ta_id'] === NULL OR $options['ta_id'] == ''),
         '%'.$options['kode'].'%',
         '%'.$options['kode'].'%',
         $offset,
         $limit
      ));

      return $return;
   }

   public function GetCountProgramRef($param = array())
   {
      $opt           = array(
         'ta_id' => NULL,
         'kode' => NULL
      );
      $options       = array_merge($opt, (array)$param);
      $return        = $this->Open($this->mSqlQueries['get_count_program_ref'], array(
         $options['ta_id'],
         (int)($options['ta_id'] === NULL OR $options['ta_id'] == ''),
         '%'.$options['kode'].'%',
         '%'.$options['kode'].'%'
      ));

      if($return){
         return $return[0]['count'];
      }else{
         return 0;
      }
   }

   public function GetDetailBelanja($offset, $limit, $param = array())
   {
      $return     = $this->Open($this->mSqlQueries['get_detail_belanja'], array(
         $param['ref_id'],
         '%'.$param['kode'].'%',
         '%'.$param['nama'].'%',
         $offset,
         $limit
      ));

      return $return;
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
      if(!is_array($input))
      {
         return $input;
      }

      foreach ($input as $key => $value) {
         if(is_array($value))
         {
            foreach ($value as $k => $v) {
               $array[$key][self::humanize($k, $case)] = $v;
            }
         }
         else
         {
            $array[self::humanize($key, $case)]  = $value;
         }
      }

      return (array)$array;
   }

   /**
    * @param string  path_info url to be parsed, default null
    * @return string parsed_url based on Dispatcher::Instance()->getQueryString();
    */
   public function __getQueryString($pathInfo = null)
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