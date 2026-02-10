<?php
/**
* ================= doc ====================
* FILENAME     : AppReferensi.class.php
* @package     : AppReferensi
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-03-06
* @Modified    : 2015-03-06
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/user_unit_kerja/business/UserUnitKerja.class.php';

class AppReferensi extends Database
{
   # internal variables
   protected $mSqlFile;
   public $_POST;
   public $_GET;
   protected $mUserId = NULL;
   # Constructor
   function __construct ($connectionNumber = 0)
   {
      $this->mSqlFile   = 'module/laporan_rekap_pengeluaran_kas_bulanan/business/app_referensi.sql.php';
      $this->_POST      = is_object($_POST) ? $_POST->AsArray() : $_POST;
      $this->_GET       = is_object($_GET) ? $_GET->AsArray() : $_GET;
      parent::__construct($connectionNumber);
   }

   private function setUser()
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
      $this->setUser();
      return (int)$this->mUserId;
   }

   public function getTypeUnit()
   {
      $return     = $this->Open($this->mSqlQueries['get_tipe_unit'], array());

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

   public function getUnitKerja($offset, $limit, $param = array())
   {
      $mUnitObj      = new UserUnitKerja();
      $userId        = $this->getUserId();
      $unitKerja     = $mUnitObj->GetUnitKerjaRefUser($userId);
      $unitId        = $unitKerja['id'];
      $return        = $this->Open($this->mSqlQueries['get_unit_kerja'], array(
         '%'.$param['kode'].'%',
         '%'.$param['nama'].'%',
         $param['tipe'],
         (int)($param['tipe'] == '' OR $param['tipe'] == 'all'),
         $unitId,
         $unitId,
         $unitId,
         $offset,
         $limit
      ));

      return $return;
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
}
?>