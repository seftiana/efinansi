<?php
/**
* ================= doc ====================
* FILENAME     : popupFpa.class.php
* @package     : PopupFpa
* scope        : PUBLIC
* @Author      : noor hadi
* @Created     : 2016-03-07
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2016 Gamatechno
* ================= doc ====================
*/

require_once GTFWConfiguration::GetValue('application','docroot').
'module/user_unit_kerja/business/UserUnitKerja.class.php';

class PopupFpa extends Database
{
   # internal variables
   protected $mSqlFile;
   public $_POST;
   public $_GET;
   protected $mUserId = null;
   protected $mUnitKerja = null;
   # Constructor
   function __construct ($connectionNumber = 0)
   {
        $this->mSqlFile   = 'module/finansi_sppu/business/popup_fpa.sql.php';
        $this->_POST      = is_object($_POST) ? $_POST->AsArray() : $_POST;
        $this->_GET       = is_object($_GET) ? $_GET->AsArray() : $_GET;
        parent::__construct($connectionNumber);
        $mUnitObj   = new UserUnitKerja();
        $userId     =  trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
        $this->mUnitKerja  = $mUnitObj->GetUnitKerjaUser($userId);
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

   /**
    * [getDataUnit GetData unit kerja]
    * @param  Int $offset
    * @param  Int $limit
    * @param  array $param[tipe, kode, nama]
    * @return array $return
    */
   public function getData($offset, $limit, $param = array())
   {
        $this->SetDebugOn();
        $return  = $this->Open($this->mSqlQueries['get_data_realisasi'], array(
            $param['ta_id'],
            $this->mUnitKerja['unit_kerja_id'],
            $this->mUnitKerja['unit_kerja_id'],
            $this->mUnitKerja['unit_kerja_id'],
            '%'.trim($param['unit']).'%',
            '%'.trim($param['uraian']).'%',
            '%'.trim($param['uraian']).'%',
            '%'.trim($param['uraian']).'%',
            $param['bulan'],
            (int)(($param['bulan'] === NULL OR $param['bulan'] == '') OR strtolower($param['bulan']) == 'all'),
            $offset,
            $limit
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
}
?>