<?php
/**
 *
 * @class PopupSekenarioJurnal
 * @package history_transaksi_keuangan_sp2d
 * @description untuk menjalankan query daftar transaksi keuagan SP2D
 * @analyst dyah fajar <dyah@gamatechno.com>
 * @author noor hadi <noor.hadi@gamatechno.com>
 * @since Januari 2014
 * @copyright 2014 Gamatechno Indonedia
 * @modified Eko Susilo <eko.susilo@gamatechno.com> 2014-10-20 10:24:16
 */

class PopupSekenarioJurnal extends Database
{
   protected $mSqlFile;
   public $_POST;
   public $_GET;

   public function __construct($connectionNumber=0){
      $this->mSqlFile   = 'module/history_transaksi_keuangan_sp2d/business/popup_sekenario_jurnal.sql.php';
      $this->_POST      = is_object($_POST) ? $_POST->AsArray() : $_POST;
      $this->_GET       = is_object($_GET) ? $_GET->AsArray() : $_GET;
      parent::__construct($connectionNumber);
   }

   public function GetDataSkenario($offset, $limit, $param = array())
   {
      $return     = $this->Open($this->mSqlQueries['get_data_skenario'], array(
         '%'.$param['nama'].'%',
         $offset,
         $limit
      ));

      return $return;
   }

   public function GetCountSkenario($param = array())
   {
      $return     = $this->Open($this->mSqlQueries['get_count_skenario'], array(
         '%'.$param['nama'].'%'
      ));

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