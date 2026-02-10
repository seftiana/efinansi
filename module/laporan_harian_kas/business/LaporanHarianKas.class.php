<?php
/**
* ================= doc ====================
* FILENAME     : LaporanHarianKas.class.php
* @package     : LaporanHarianKas
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-05-26
* @Modified    : 2015-05-26
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/

class LaporanHarianKas extends Database
{
   # internal variables
   protected $mSqlFile;
   public $_POST;
   public $_GET;
   # Constructor
   function __construct ($connectionNumber = 0)
   {
      $this->mSqlFile   = 'module/laporan_harian_kas/business/laporan_harian_kas.sql.php';
      $this->_GET       = is_object($_GET) ? $_GET->AsArray() : $_GET;
      $this->_POST      = is_object($_POST) ? $_POST->AsArray() : $_POST;
      parent::__construct($connectionNumber);
   }

   private function _getTanggalPembukuanAktif(){   
        $return     = $this->Open($this->mSqlQueries['get_tanggal_periode_pembukuan_aktif'], array());
        if(!empty($return)) {
            $tanggal = $return[0]['tanggal_awal'];
        } else {
            $tanggal = date('Y-m-d');
        }
        
        return $tanggal;
   }
   
   public function getSaldoAwal($tanggalAwalBulan)
   {
      $tanggalAwalTahun = $this->_getTanggalPembukuanAktif();
      $saldoAwalTahun     = $this->Open($this->mSqlQueries['get_saldo_awal'], array(
          $tanggalAwalBulan,$tanggalAwalBulan
      ));
      $saldoAwalBulanBerjalan = $this->Open($this->mSqlQueries['get_saldo_awal_berjalan'], array(
          $tanggalAwalTahun,$tanggalAwalBulan,
          $tanggalAwalTahun,$tanggalAwalBulan
      ));
      
      $return  = ($saldoAwalTahun[0]['saldo_awal'] + $saldoAwalBulanBerjalan[0]['saldo_awal']);      
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

   public function getDataLaporanKas($offset, $limit, $param = array())
   {
      $return     = $this->Open($this->mSqlQueries['get_data_laporan_kas'], array(
         date('Y-m-d', strtotime($param['start_date'])),
         date('Y-m-d', strtotime($param['end_date'])),
         date('Y-m-d', strtotime($param['start_date'])),
         date('Y-m-d', strtotime($param['end_date'])),
         $offset,
         $limit
      ));

      return self::ChangeKeyName($return);
   }

   public function getDataLaporanKasExport($param = array())
   {
      $return     = $this->Open($this->mSqlQueries['get_data_laporan_kas_export'], array(
         date('Y-m-d', strtotime($param['start_date'])),
         date('Y-m-d', strtotime($param['end_date'])),
         date('Y-m-d', strtotime($param['start_date'])),
         date('Y-m-d', strtotime($param['end_date']))
      ));

      return self::ChangeKeyName($return);
   }
   
  public function getTotalDebetKredit($param = array())
   {
      $return     = $this->Open($this->mSqlQueries['get_data_laporan_kas_export'], array(
         date('Y-m-d', strtotime($param['start_date'])),
         date('Y-m-d', strtotime($param['end_date'])),
         date('Y-m-d', strtotime($param['start_date'])),
         date('Y-m-d', strtotime($param['end_date']))
      ));
      
      $mutasiDebet = 0;
      $mutasiKredit = 0;
      if(!empty($return)){
          foreach($return as $itemDK){
              $mutasiDebet += $itemDK['nominalDebet'];
              $mutasiKredit += $itemDK['nominalKredit'];
          }
      }
      return array('debet' => $mutasiDebet, 'kredit' => $mutasiKredit);
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

   function _dateToIndo($date) {
        $indonesian_months = array(
            'N/A',
            'Januari',
            'Februari',
            'Maret',
            'April',
            'Mei',
            'Juni',
            'Juli',
            'Agustus',
            'September',
            'Oktober',
            'Nopember',
            'Desember'
        );

        if (preg_match("/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})/", $date, $patch)) {
            $year = (int) $patch[1];
            $month = (int) $patch[2];
            $month = $indonesian_months[(int) $patch[2]];
            $day = (int) $patch[3];
            $hour = (int) $patch[4];
            $min = (int) $patch[5];
            $sec = (int) $patch[6];

            $return = $day . ' ' . $month . ' ' . $year;
        } elseif (preg_match("/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})/", $date, $patch)) {
            $year = (int) $patch[1];
            $month = (int) $patch[2];
            $month = $indonesian_months[$month];
            $day = (int) $patch[3];

            $return = $day . ' ' . $month . ' ' . $year;
        } else {
            $return = (int) $date;
        }
        return $return;
    }
}
?>