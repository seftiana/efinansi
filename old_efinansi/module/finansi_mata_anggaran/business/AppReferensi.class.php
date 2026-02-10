<?php
/**
* ================= doc ====================
* FILENAME     : AppReferensi.class.php
* @package     : AppReferensi
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-03-27
* @Modified    : 2015-03-27
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/

class AppReferensi extends Database
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
      $this->mSqlFile   = 'module/finansi_mata_anggaran/business/app_referensi.sql.php';
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

   public function getDataBas($offset, $limit, $param = array())
   {
      $return     = $this->Open($this->mSqlQueries['get_pagu_bas'], array(
         '%'.$param['kode'].'%',
         '%'.$param['nama'].'%',
         $offset,
         $limit
      ));

      return self::ChangeKeyName($return);
   }

   public function getDataCoa($offset, $limit, $param = array())
   {
      $return     = $this->Open($this->mSqlQueries['get_coa'], array(
         '%'.$param['kode'].'%',
         '%'.$param['nama'].'%',
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
}
?>