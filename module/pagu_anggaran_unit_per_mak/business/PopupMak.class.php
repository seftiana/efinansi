<?php

class Mak extends Database {

   protected $mSqlFile= 'module/pagu_anggaran_unit_per_mak/business/popupmak.sql.php';

   function __construct($connectionNumber=0) {
      parent::__construct($connectionNumber);
   }

   function GetDataMak ($offset, $limit, $nama, $bas = null, $ex = array()) {
      $result = $this->Open($this->mSqlQueries['get_data_mak'], array(
         implode("','", $ex),
         (int)($ex == '' OR $ex == null),
         '%'.$nama.'%',
         '%'.$nama.'%',
         (int)($nama == '' OR $nama == null),
         '%'.$bas.'%',
         '%'.$bas.'%',
         (int)($bas == '' OR $bas == null),
         $offset,
         $limit
      ));
      return $result;
   }

   function GetCountDataMak () {

      $result = $this->Open($this->mSqlQueries['get_count_data_mak'], array());
      if (!$result) {
         return 0;
      } else {
         return $result[0]['count'];
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
