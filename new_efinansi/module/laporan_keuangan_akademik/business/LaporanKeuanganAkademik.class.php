<?php

/**
* ================= doc ====================
* FILENAME     : LaporanKeuanganAkademik.class.php
* @package     : LaporanKeuanganAkademik
* scope        : PUBLIC
* @Author      : noor hadi
* @Created     : 2015-04-29
* @Modified    : 2015-04-29
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2015 Gamatechno
* ================= doc ====================
*/


require_once GTFWConfiguration::GetValue('application','docroot').
'module/user_unit_kerja/business/UserUnitKerja.class.php';

class LaporanKeuanganAkademik extends Database
{
   # internal variables
   protected $mSqlFile;
   protected $mUserId = NULL;
   
   protected $mTPenerimaanPerKelompokUnit = array();
   protected $mTPenerimaanPerIdentitasUnit = array();
   protected $mTPenerimaanPerKelompok = array();
   protected $mTPenerimaanPerIdentitas = array();
   protected $mTPenerimaanPerKelompokR = array();
   protected $mTPenerimaanPerIdentitasR = array();   
      
   protected $mTPengeluaranPerKelompokUnit = array();
   protected $mTPengeluaranPerIdentitasUnit = array();   
   protected $mTPengeluaranPerKelompok = array();
   protected $mTPengeluaranPerIdentitas = array();  
   protected $mTPengeluaranPerKelompokR = array();
   protected $mTPengeluaranPerIdentitasR = array();
   
   protected $mSemesterGenap = array();
   protected $mSemesterGasal = array();

   public $_POST;
   public $_GET;
   public $indonesianMonth    = array(
      1=>  'Januari',
      2 => 'Februari',
      3 => 'Maret',
      4 => 'April',
      5 => 'Mei',
      6 => 'Juni',
      7 => 'Juli',
      8 => 'Agustus',
      9 => 'September',
      10 => 'Oktober',
      11 => 'November',
      12 => 'Desember',
      );
   # Constructor
   function __construct ($connectionNumber = 0)
   {
      $this->mSqlFile   = 'module/laporan_keuangan_akademik/business/laporan_keuangan_akademik.sql.php';
      $this->_POST      = is_object($_POST) ? $_POST->AsArray() : $_POST;
      $this->_GET       = is_object($_GET) ? $_GET->AsArray() : $_GET;
      parent::__construct($connectionNumber);
      $this->mSemesterGasal =  array(9,10,11,12,1,2) ;
      $this->mSemesterGenap =  array(3,4,5,6,7,8);
      //$this->SetDebugOn();
   }

   private function _GetTanggalAkhirBulan($tanggal)
   {
       $bulan = date('m', strtotime($tanggal));
       $tahun = date('Y', strtotime($tanggal));
       switch($bulan) {
         case 1 :  $tanggal = 31; break;
         case 2 :  $tanggal = (($tahun % 4 ==0) ? 29 : 28 ); break;
         case 3 :  $tanggal = 31; break;
         case 4 :  $tanggal = 30; break;
         case 5 :  $tanggal = 31; break;
         case 6 :  $tanggal = 30; break;
         case 7 :  $tanggal = 31; break;
         case 8 :  $tanggal = 31; break;
         case 9 :  $tanggal = 30; break;
         case 10 : $tanggal = 31; break;
         case 11 : $tanggal = 30; break;
         case 12 : $tanggal = 31; break;
         default:
             $tanggal = 30;
             break;
       }
       return date('Y-m-d', mktime(0,0,0, $bulan, $tanggal,$tahun));
       
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
   
   private function _GetPeriodeTahunAnggaran($periodeTanggal)
   {                                                  
        $return     = $this->Open($this->mSqlQueries['get_periode_tahun_anggaran'],
            array(
                $periodeTanggal,
                $periodeTanggal,
            ));        
           
        return $return[0]['id'];         
   }
   
   public function GetTahunAnggaranDetailById($taId)
   {
        $data = array();
        $return = $this->Open($this->mSqlQueries['get_tahun_anggaran_detail_by_id'],array($taId));        
        if(!empty($return)){
            $data['tanggal_awal'] = $return[0]['tanggal_awal'];
            $data['nama_bulan_awal'] = $this->indonesianMonth[$return[0]['bulan_awal']];
            $data['tahun_awal'] = $return[0]['tahun_awal'];
            
            $data['tanggal_akhir'] = $return[0]['tanggal_akhir'];
            $data['nama_bulan_akhir'] = $this->indonesianMonth[$return[0]['bulan_akhir']];
            $data['tahun_akhir'] = $return[0]['tahun_akhir'];
        }
        return $data;
   }
   
   public function GetCoaAlokasiAkademik()
   {
        $data =  array();
        $return     = $this->Open($this->mSqlQueries['get_coa_alokasi_akademik'],array());           
        foreach($return as $key =>$v) {
           $data[$v['coaId']] =  $v['coaId'];
        }        
        return $data;       
   }
   
   public function GetUnitKerja($tahunAnggaranId,$bulan=0)
   {//$this->SetDebugOn();
       //$tahunAnggaranId = $this->_GetPeriodeTahunAnggaran($periodeTanggal); 
       $gasal = $this->mSemesterGasal;
       $genap = $this->mSemesterGenap;
        $return     = $this->Open($this->mSqlQueries['get_unit_kerja'],
            array($tahunAnggaranId));        
         if(!empty($return)) {    
            foreach($return as $key =>$v) {
             if(in_array($bulan,$gasal)){
                    $return[$key]['unitKerjaKelas']=  $return[$key]['jumlahKelasGasal'];
                } elseif(in_array($bulan,$genap)) {
                    $return[$key]['unitKerjaKelas'] = $return[$key]['jumlahKelasGenap'];
                } else {
                   $return[$key]['unitKerjaKelas'] =  $return[$key]['unitKerjaKelas'];
                }
            }
         }
        return self::ChangeKeyName($return);
   }

   public function GetUnitKerjaId($tahunAnggaranId)
   {//$this->SetDebugOn();
       //$tahunAnggaranId = $this->_GetPeriodeTahunAnggaran($periodeTanggal); 
       $data =  array();
        $return     = $this->Open($this->mSqlQueries['get_unit_kerja'],array($tahunAnggaranId));        
        if(!empty($return)) {    
            foreach($return as $key =>$v) {
                $data[$v['unitKerjaId']]['unitKerjaId'] =  $v['unitKerjaId'];
            }   
        }     
        return $data;
   }
   public function GetJumlahKelasPerUnit($tahunAnggaranId,$bulan = 0)
   {
       $gasal = $this->mSemesterGasal;
       $genap = $this->mSemesterGenap;
       $data =  array();
      
        $return     = $this->Open($this->mSqlQueries['get_jumlah_kelas_per_unit'],
            array(
                $tahunAnggaranId
            ));        
        if(!empty($return)) {    
            foreach($return as $key =>$v) {
                if(in_array($bulan,$gasal)){
                    $data[$v['unitKerjaId']] =  $v['jumlahKelasGasal'];
                } elseif(in_array($bulan,$genap)) {
                    $data[$v['unitKerjaId']] =  $v['jumlahKelasGenap'];
                } else {
                     $data[$v['unitKerjaId']] =  $v['jumlahKelas'];
                }
                
            }   
        }     
        return $data;
   }

   public function GetNominalPerItemPenerimaan($periodeTanggalAwal,$periodeTanggalAkhir)
   {
      
       $data =  array();
       $return     = $this->Open($this->mSqlQueries['get_nominal_per_item_penerimaan'],
            array(
                $periodeTanggalAwal,
                $this->_GetTanggalAkhirBulan($periodeTanggalAkhir)
            )); 
       if(!empty($return)) {
            foreach($return as $key =>$v) {
               $data[$v['identitas']][$v['kelompokId']][$v['subKelompokId']] = $v['nominal'];
               $this->mTPenerimaanPerKelompok[$v['kelompokId']] += $v['nominal'];
               $this->mTPenerimaanPerIdentitas['nominal'] += $v['nominal'];
            }    
       }
       
       return $data;
   }
   
    public function GetNominalPerItemPenerimaanRange($periodeTanggalAwal,$periodeTanggalAkhir)
   {
      
       $data =  array();
       $return     = $this->Open($this->mSqlQueries['get_nominal_per_item_penerimaan'],
            array(
               $periodeTanggalAwal,
                $this->_GetTanggalAkhirBulan($periodeTanggalAkhir)
            )); 
       if(!empty($return)) {
            foreach($return as $key =>$v) {
               $data[$v['identitas']][$v['kelompokId']][$v['subKelompokId']] = $v['nominal'];
               $this->mTPenerimaanPerKelompokR[$v['kelompokId']] += $v['nominal'];
               $this->mTPenerimaanPerIdentitasR['nominal'] += $v['nominal'];
            }    
       }
       
       return $data;
   }
   
      
   public function GetNominalPerUnitPenerimaan($periodeTanggalAwal,$periodeTanggalAkhir,$taId,$bulan = 0)
   {
       
       $getCoaAlokasiAkademik = $this->getCoaAlokasiAkademik();
       $getJumlahKelasPerUnit = $this->GetJumlahKelasPerUnit($taId,$bulan);
       $getUnitKerjaId = $this->GetUnitKerjaId($taId);
       $totalKelas =  array_sum($getJumlahKelasPerUnit);        
       $data =  array();
       $return     = $this->Open($this->mSqlQueries['get_nominal_per_unit_penerimaan'],
            array(
                $periodeTanggalAwal,
                $this->_GetTanggalAkhirBulan($periodeTanggalAkhir)
            )); 
    
       foreach($return as $key =>$v) {
         
           if(array_key_exists($v['subKelompokId'], $getCoaAlokasiAkademik)){
               foreach($getUnitKerjaId as $key => $u){
                  $nominal = ($v['nominal'] * ($getJumlahKelasPerUnit[$u['unitKerjaId']] / $totalKelas));  
                  $data[$u['unitKerjaId']][$v['identitas']][$v['kelompokId']][$getCoaAlokasiAkademik[$v['subKelompokId']]] = $nominal;
                  $this->mTPenerimaanPerKelompokUnit[$u['unitKerjaId']][$v['identitas']][$v['kelompokId']] += $nominal;
                  $this->mTPenerimaanPerIdentitasUnit[$u['unitKerjaId']][$v['identitas']] += $nominal;
               }              
          
           } else {
               $data[$v['unitKerjaId']][$v['identitas']][$v['kelompokId']][$v['subKelompokId']]  = $v['nominal'];
               $this->mTPenerimaanPerKelompokUnit[$v['unitKerjaId']][$v['identitas']][$v['kelompokId']] += $v['nominal'];
               $this->mTPenerimaanPerIdentitasUnit[$v['unitKerjaId']][$v['identitas']] += $v['nominal'];
           }  
           
       }        
       
       return $data;
   }
   
   public function GetNominalPerKelompokUnitPenerimaan()
   {
        
        return $this->mTPenerimaanPerKelompokUnit;
   }

   public function GetTotalPerKelompokPenerimaan()
   {
        return $this->mTPenerimaanPerKelompok;
   }

   public function GetTotalPerKelompokPenerimaanRange()
   {
        return $this->mTPenerimaanPerKelompokR;
   }
      
   public function GetNominalPerPenerimaan()
   {
        return $this->mTPenerimaanPerIdentitasUnit;
   }

   public function GetTotalPerPenerimaan()
   {        
        return $this->mTPenerimaanPerIdentitas['nominal'];
   }

  public function GetTotalPerPenerimaanRange()
   {        
        return $this->mTPenerimaanPerIdentitasR['nominal'];
   }
   public function GetNominalPerItemPengeluaran($periodeTanggalAwal,$periodeTanggalAkhir,$taId,$bulan = 0)
   {
       
        $getCoaAlokasiAkademik = $this->getCoaAlokasiAkademik();
        $getJumlahKelasPerUnit = $this->GetJumlahKelasPerUnit($taId,$bulan);
        $getUnitKerjaId = $this->GetUnitKerjaId($taId);
        $totalKelas =  array_sum($getJumlahKelasPerUnit);    
               
        $data =  array();
        $return     = $this->Open($this->mSqlQueries['get_nominal_per_item_pengeluaran'],
            array(
                $periodeTanggalAwal,
                $this->_GetTanggalAkhirBulan($periodeTanggalAkhir)
            ));  
        if(!empty($return)){             
            foreach($return as $key =>$v) {
                $data[$v['identitas']][$v['kelompokId']][$v['subKelompokId']] =  $v['nominal'];
                $this->mTPengeluaranPerKelompok[$v['kelompokId']] +=  $v['nominal'];
                $this->mTPengeluaranPerIdentitas['nominal'] +=  $v['nominal'];
            }
        }
        return $data;
   }

   public function GetNominalPerItemPengeluaranRange($periodeTanggalAwal,$periodeTanggalAkhir)
   {
        $data =  array();
        $return     = $this->Open($this->mSqlQueries['get_nominal_per_item_pengeluaran'],
            array(
                $periodeTanggalAwal,
                $this->_GetTanggalAkhirBulan($periodeTanggalAkhir)
            ));  
        if(!empty($return)){             
            foreach($return as $key =>$v) {
                $data[$v['identitas']][$v['kelompokId']][$v['subKelompokId']] =  $v['nominal'];
                $this->mTPengeluaranPerKelompokR[$v['kelompokId']] +=  $v['nominal'];
                $this->mTPengeluaranPerIdentitasR['nominal'] +=  $v['nominal'];
            }
        }
        return $data;
   }      
   public function GetNominalPerUnitPengeluaran($periodeTanggalAwal,$periodeTanggalAkhir,$taId,$bulan = 0)
   {
       $getCoaAlokasiAkademik = $this->getCoaAlokasiAkademik();
       $getJumlahKelasPerUnit = $this->GetJumlahKelasPerUnit($taId,$bulan);
       $getUnitKerjaId = $this->GetUnitKerjaId($taId);
       $totalKelas =  array_sum($getJumlahKelasPerUnit);       
       
        $data =  array();
        $return     = $this->Open($this->mSqlQueries['get_nominal_per_unit_pengeluaran'],
            array(
                $periodeTanggalAwal,
                $this->_GetTanggalAkhirBulan($periodeTanggalAkhir)
            ));           
        foreach($return as $key =>$v) {
          
           if(array_key_exists($v['kelompokId'], $getCoaAlokasiAkademik)){
               foreach($getUnitKerjaId as $key => $u){
                  $nominal = ($v['nominal'] * ($getJumlahKelasPerUnit[$u['unitKerjaId']] / $totalKelas));  
                  $data[$u['unitKerjaId']][$v['identitas']][$getCoaAlokasiAkademik[$v['kelompokId']]][$v['subKelompokId']] = $nominal;
                 
                  $this->mTPengeluaranPerKelompokUnit[$u['unitKerjaId']][$v['identitas']][$v['kelompokId']] += $nominal;
                  $this->mTPengeluaranPerIdentitasUnit[$u['unitKerjaId']][$v['identitas']] += $nominal;                 
               }              
          
           } else {
               $data[$v['unitKerjaId']][$v['identitas']][$v['kelompokId']][$v['subKelompokId']]  = $v['nominal'];
               $this->mTPengeluaranPerKelompokUnit[$v['unitKerjaId']][$v['identitas']][$v['kelompokId']]  +=  $v['nominal'];
               $this->mTPengeluaranPerIdentitasUnit[$v['unitKerjaId']][$v['identitas']]  +=  $v['nominal'];               
           }  
        }        
        return $data;
   }

   public function GetNominalPerKelompokUnitPengeluaran()
   {
       return $this->mTPengeluaranPerKelompokUnit;
   }

   public function GetTotalPerKelompokPengeluaran()
   {
        return $this->mTPengeluaranPerKelompok;
   }

   public function GetTotalPerKelompokPengeluaranRange()
   {
        return $this->mTPengeluaranPerKelompokR;
   }   

   public function GetNominalPerPengeluaran()
   {
 
        return $this->mTPengeluaranPerIdentitasUnit;
   }      

   public function GetTotalPerPengeluaran()
   {
        return $this->mTPengeluaranPerIdentitas['nominal'];
   }

   public function GetTotalPerPengeluaranRange()
   {
        return $this->mTPengeluaranPerIdentitasR['nominal'];
   }
      
   public function GetDataLaporanKeuanganAkademik($periodeTanggal)
   {//$this->SetDebugOn();
        $return     = $this->Open($this->mSqlQueries['get_data_laporan_keuangan_akademik'],
            array(
            ));   
        return self::ChangeKeyName($return);       
   }
   public function GetDataLaporanKeuanganAkademikLimit($periodeTanggal,$offset,$maxRow)
   {
        $return     = $this->Open($this->mSqlQueries['get_data_laporan_keuangan_akademik'].' '.
                                  $this->mSqlQueries['get_limit']  ,
            array(
                $offset,
                $maxRow
            ));   
        return self::ChangeKeyName($return);       
   }   
   public function getUserId()
   {
      $this->setUserId();
      return (int)$this->mUserId;
   }

   public function getPeriodeTahun($param = array())
   {//$this->SetDebugOn();
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