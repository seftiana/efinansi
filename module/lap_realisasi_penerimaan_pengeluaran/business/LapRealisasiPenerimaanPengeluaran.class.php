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

class LapRealisasiPenerimaanPengeluaran extends Database
{
   # internal variables
   protected $mSqlFile;
   protected $mUserId = NULL;
   public $_POST;
   public $_GET;

   private $_namaBulan= array(
        '1' => 'Januari',
        '2' => 'Februari',
        '3' => 'Maret',
        '4' => 'April',
        '5' => 'Mei',
        '6' => 'Juni',
        '7' => 'Juli',
        '8' => 'Agustus',
        '9' => 'September',
        '10' => 'Oktober',
        '11' => 'November',
        '12' => 'Desember'
   );
   # Constructor
   function __construct ($connectionNumber = 0)
   {
      $this->mSqlFile   = 'module/lap_realisasi_penerimaan_pengeluaran/business/lap_realisasi_penerimaan_pengeluaran.sql.php';
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

   public function getHeaderBulan($startDate,$endDate)
   {
        $bulan = array();
        $begin = new DateTime($startDate);
        $end   = new DateTime($endDate);
        
        $idx = 0;
        for($i = $begin; $begin <= $end; $i->modify('+1 month')){
            $bulan[$idx]['nama_bulan'] = $this->_namaBulan[$i->format("n")].' '.$i->format("Y");
            $bulan[$idx]['kode_bulan'] = $i->format("Y").'-'.$i->format("n");
            $idx++;
        }
        
        return $bulan;
   }
   
   public function getDateRange()
   {
      $result     = array();
      $data       = $this->Open($this->mSqlQueries['get_range_tanggal'], array());
      $getdate    = getdate();
      $currMon    = (int)$getdate['mon'];
      $currYear   = (int)$getdate['year'];
      
      if(!empty($data)){
         $result['startDate']    = date('Y-m-d', strtotime($data[0]['tanggalAwal']));
         $result['endDate']      = date('Y-m-t', strtotime($data[0]['tanggalAkhir']));
         $result['minYear']      = date('Y', strtotime($data[0]['tanggalAwal']));
         $result['maxYear']      = date('Y', strtotime($data[0]['tanggalAkhir']));
      }else{
         $result['startDate']       = date('Y-m-d', mktime(0,0,0, $currMon, 1, $currYear));
         $result['endDate']         = date('Y-m-t', mktime(0,0,0, $currMon, 1, $currYear));
         $result['minYear']      = $currYear;
         $result['maxYear']      = $currYear;
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

   public function getDataPenerimaanPengeluaran($offset, $limit, $param = array())
   {
      $return  = $this->Open($this->mSqlQueries['get_data_penerimaan_pengeluaran'],array(
         $param['ta_id'],
         $param['unit_id'],
         $param['unit_id'],
         $param['unit_id'],
         date('Y-m-d', strtotime($param['start_date'])),
         date('Y-m-d', strtotime($param['end_date'])),
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
   
   
   public function getTotalPenerimaan($offset, $limit, $param = array())
   {
      $return  = $this->Open($this->mSqlQueries['get_total_penerimaan'],array(
         $param['ta_id'],
         $param['unit_id'],
         $param['unit_id'],
         $param['unit_id'],
         date('Y-m-d', strtotime($param['start_date'])),
         date('Y-m-d', strtotime($param['end_date']))
      ));
     return self::ChangeKeyName($return[0]);
   }
   
   public function getTotalPengeluaran($offset, $limit, $param = array())
   {
      $return     = $this->Open($this->mSqlQueries['get_total_pengeluaran'], array(
         $param['ta_id'],
         $param['unit_id'],
         $param['unit_id'],
         $param['unit_id'],
         date('Y-m-d', strtotime($param['start_date'])),
         date('Y-m-d', strtotime($param['end_date']))
      ));

      return self::ChangeKeyName($return[0]);
   }
   
   public function getPengeluaranPerBulan($offset, $limit, $param = array())
   {
      $data = array();
      $return = $this->Open($this->mSqlQueries['get_penerimaan_pengeluaran_per_bulan'], array(
         $param['ta_id'],
         $param['unit_id'],
         $param['unit_id'],
         $param['unit_id'],
         date('Y-m-d', strtotime($param['start_date'])),
         date('Y-m-d', strtotime($param['end_date'])),
         $param['ta_id'],
         $param['unit_id'],
         $param['unit_id'],
         $param['unit_id'],
         date('Y-m-d', strtotime($param['start_date'])),
         date('Y-m-d', strtotime($param['end_date']))
      ));
      
      foreach ($return as $key => $value) {
          //$data[$value['sectionId']][$value['bulan']][$value['idKode']]['nominal_total_usulan'] = $value['nominalTotalUsulan'];
          $data[$value['sectionId']][$value['bulan']][$value['idKode']]['nominal_usulan'] = $value['nominalUsulan'];
          $data[$value['sectionId']][$value['bulan']][$value['idKode']]['nominal_realisasi'] = $value['nominalRealisasi'];
      }
      
      return $data;
   }   

   public function getTotalPengeluaranPerBulan($param = array())
   {
      $data = array();
      $return = $this->Open($this->mSqlQueries['get_total_pengeluaran_per_bulan'], array(
         $param['ta_id'],
         $param['unit_id'],
         $param['unit_id'],
         $param['unit_id'],
         date('Y-m-d', strtotime($param['start_date'])),
         date('Y-m-d', strtotime($param['end_date'])),
         $param['ta_id'],
         $param['unit_id'],
         $param['unit_id'],
         $param['unit_id'],
         date('Y-m-d', strtotime($param['start_date'])),
         date('Y-m-d', strtotime($param['end_date']))
      ));
      
      foreach ($return as $key => $value) {
          $data[$value['sectionId']]['nominal_total_usulan'] += $value['nominalTotalUsulan'];
          $data[$value['sectionId']][$value['bulan']]['nominal_usulan'] = $value['nominalUsulan'];
          $data[$value['sectionId']][$value['bulan']]['nominal_realisasi'] = $value['nominalRealisasi'];
      }
      
      return $data;
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