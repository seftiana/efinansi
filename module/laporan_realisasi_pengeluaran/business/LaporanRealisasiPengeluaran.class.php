<?php
/**
* ================= doc ====================
* FILENAME     : LaporanRealisasiPengeluaran.class.php
* @package     : LaporanRealisasiPengeluaran
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-03-13
* @Modified    : 2015-03-13
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/

class LaporanRealisasiPengeluaran extends Database
{
   # internal variables
   protected $mSqlFile;
   protected $mUserId = NULL;
   public $_POST;
   public $_GET;

   # Constructor
   function __construct ($connectionNumber = 0)
   {
      $this->mSqlFile   = 'module/laporan_realisasi_pengeluaran/business/laporan_realisasi_pengeluaran.sql.php';
      $this->_POST      = is_object($_POST) ? $_POST->AsArray() : $_POST;
      $this->_GET       = is_object($_GET) ? $_GET->AsArray() : $_GET;
      parent::__construct($connectionNumber);
   }

   private function setUserId()
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
      $this->setUserId();
      return (int)$this->mUserId;
   }

   public function getDateRange()
   {
      $result     = array();
      $data       = $this->Open($this->mSqlQueries['get_range_tanggal'], array());
      $getdate    = getdate();
      $currMon    = (int)$getdate['mon'];
      $currYear   = (int)$getdate['year'];
      $result['startDate']       = date('Y-m-d', mktime(0,0,0, $currMon, 1, $currYear));
      $result['endDate']         = date('Y-m-t', mktime(0,0,0, $currMon, 1, $currYear));
      if(!empty($return)){
         $result['minYear']      = date('Y', strtotime($data[0]['tanggalAwal']));
         $result['maxYear']      = date('Y', strtotime($data[0]['tanggalAkhir']));
      }else{
         $result['minYear']      = $currYear-5;
         $result['maxYear']      = $currYear+5;
      }

      return self::ChangeKeyName($result);
   }

   public function getPeriodeTahun($param = array())
   {
      $default    = array(
         'active' => false,
         'open' => false
      );
      $options    = array_merge($default, (array)$param);
      $return     = $this->Open($this->mSqlQueries['get_periode_tahun'], array(
         (int)($options['active'] === false),
         (int)($options['open'] === false)
      ));

      return $return;
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

   public function getData($offset, $limit, $param = array())
   {
      //$this->SetDebugOn();
      $return     = $this->Open($this->mSqlQueries['get_data'], array(
         $param['ta_id'],
         $param['unit_id'],
         $param['unit_id'],
         $param['unit_id'],
         date('Y-m-d', strtotime($param['start_date'])),
         date('Y-m-d', strtotime($param['end_date'])),
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