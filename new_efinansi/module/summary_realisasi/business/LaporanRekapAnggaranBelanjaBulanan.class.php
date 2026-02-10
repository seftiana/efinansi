<?php
/**
* ================= doc ====================
* FILENAME     : LaporanRekapAnggaranBelanjaBulanan.class.php
* @package     : LaporanRekapAnggaranBelanjaBulanan
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-04-29
* @Modified    : 2015-04-29
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/user_unit_kerja/business/UserUnitKerja.class.php';

class LaporanRekapAnggaranBelanjaBulanan extends Database
{
   # internal variables
   protected $mSqlFile;
   protected $mUserId = NULL;
   public $_POST;
   public $_GET;
   public $indonesianMonth    = array(
      0 => array(
         'id' => 1,
         'name' => 'Januari'
      ), array(
         'id' => 2,
         'name' => 'Februari'
      ), array(
         'id' => 3,
         'name' => 'Maret'
      ), array(
         'id' => 4,
         'name' => 'April'
      ), array(
         'id' => 5,
         'name' => 'Mei'
      ), array(
         'id' => 6,
         'name' => 'Juni'
      ), array(
         'id' => 7,
         'name' => 'Juli'
      ), array(
         'id' => 8,
         'name' => 'Agustus'
      ), array(
         'id' => 9,
         'name' => 'September'
      ), array(
         'id' => 10,
         'name' => 'Oktober'
      ), array(
         'id' => 11,
         'name' => 'November'
      ), array(
         'id' => 12,
         'name' => 'Desember'
      )
   );
   
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
      $this->mSqlFile   = 'module/summary_realisasi/business/laporan_rekap_anggaran_belanja_bulanan.sql.php';
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

   public function getHeaderBulan($tahunAnggaranId)
   {
        $dateRange        = $this->getDateRange($tahunAnggaranId);
        
        $bulan = array();
        
        $startDate = date('Y-m-d', strtotime($dateRange['start_date']));
        $endDate  = date('Y-m-d', strtotime($dateRange['end_date']));
        
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

   
   public function getDateRange($tahunAnggaranId)
   {
      $result     = array();
      $data       = $this->Open($this->mSqlQueries['get_range_tanggal'], array(
          $tahunAnggaranId
      ));
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
      $default       = array(
         'active' => false,
         'open' => false
      );
      $options       = array_merge($default, (array)$param);
      $return        = $this->Open($this->mSqlQueries['get_periode_tahun'], array(
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
   
   public function getNominalDetailBelanjaBulanan($param = array())
   {
         if(empty($param['status_approval']) || ($param['status_approval'] == 'all')) {
            $flag = 1;
         } else {
            $flag = 0;
         }
         //$this->SetDebugOn();
         $data = array();
         // $return     = $this->Open($this->mSqlQueries['get_nominal_belanja_per_bulan'], array(
         $return     = $this->Open($this->mSqlQueries['get_nominal_belanja_per_bulan_x'], array(
                                $param['status_approval'] ,$flag,
                                $param['ta_id'],
                                $param['unit_id'],
                                $param['unit_id'],
                                $param['unit_id'],
                                $param['program_id'],
                                (int)($param['program_id'] == '' OR $param['program_id'] === NULL) 
        ));
        if(!empty($return)){
            foreach($return as $value) { 
                $data['total'][$value['bulan']] += $value['nominalSetelahRevisi'];
                $data['jml_total'] += $value['nominalSetelahRevisi'];
            }   
        }

        return $data;
   }
   
   public function getDataAnggaranBelanjaBulanan($offset, $limit, $param = array())
   {
       //$this->SetDebugOn();
       if(empty($param['status_approval']) || ($param['status_approval'] == 'all')) {
           $flag = 1;
       } else {
           $flag = 0;
       }
       
      $return     = $this->Open($this->mSqlQueries['get_data_anggaran_belanja_bulanan'], array(
         $param['ta_id'],
         $param['program_id'],
         (int)($param['program_id'] == '' OR $param['program_id'] === NULL),
         $param['unit_id'],
         $param['unit_id'],
         $param['unit_id'],
         $param['status_approval'],$flag,
         $offset,
         $limit
      ));

      return self::ChangeKeyName($return);
   }

   
   
   public function getNominaRealisasiBulanan($param = array())
   {
         if(empty($param['status_approval']) || ($param['status_approval'] == 'all')) {
            $flag = 1;
         } else {
            $flag = 0;
         } 
         $data = array(); 
         $return     = $this->Open($this->mSqlQueries['get_data_realisasi'], array(
                                $param['status_approval'] ,$flag,
                                $param['ta_id'] ,
                                $param['ta_id'] ,
                                $param['unit_id'],
                                $param['unit_id'],
                                $param['unit_id'],
                                $param['program_id'],
                                (int)($param['program_id'] == '' OR $param['program_id'] === NULL),
                                // $param['status_approval'],$flag
        )); 
        if(!empty($return)){
            foreach($return as $value) {  
                $data['total'][$value['bulan']] += $value['nominalRealisasi']; 
                $data['jml_total'] += $value['nominalRealisasi']; 
            }   
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