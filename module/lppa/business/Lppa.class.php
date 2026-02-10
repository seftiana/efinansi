<?php

/**
* ================= doc ====================
* FILENAME     : Lppa.class.php
* @package     : Lppa
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-03-17
* @Modified    : 2015-03-17
* @Analysts    : Dyah fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/

require_once GTFWConfiguration::GetValue('application','docroot') .
'module/user_unit_kerja/business/UserUnitKerja.class.php';


require_once GTFWConfiguration::GetValue('application', 'docroot') .
        'module/generate_number/business/GenerateNumber.class.php';
        
class Lppa extends Database
{
   # internal variables
   private $mNumber;
   protected $mSqlFile;
   protected $mUserId = null;
   public $_POST;
   public $_GET;
   public $method;
   
   # Constructor
   public function __construct ($connectionNumber = 0)
   {
      $this->mSqlFile   = 'module/lppa/business/lppa.sql.php';
      $this->_POST      = is_object($_POST) ? $_POST->AsArray() : $_POST;
      $this->_GET       = is_object($_GET) ? $_GET->AsArray() : $_GET;
      $this->method     = strtolower($_SERVER['REQUEST_METHOD']);
      parent::__construct($connectionNumber);
      $this->mNumber = new GenerateNumber($connectionNumber);
   }

   

   protected function GetAutoGenerateNomor($tanggal) {
        $result = $this->mNumber->getNomorLppa($tanggal);
        return $result;
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

    public function GetLppaById($id)
    {
        //$this->SetDebugOn();
        $result = $this->Open($this->mSqlQueries['get_lppa_by_id'], array($id));
        return $result[0];
    }

    public function GetCountListLppa()
    {
        $result = $this->Open($this->mSqlQueries['get_count_list_lppa'], array());
        return $result[0]['total'];
    }
    
    public function GetListLppa($offset, $limit, $data)
    {
        $result = $this->Open($this->mSqlQueries['get_list_lppa'], array(
            $data['ta_id'],
            $data['unit_id'],
            $data['unit_id'],
            $data['unit_id'],
            $offset,
            $limit
         ));
        return $result;
    }
    
    public function GetLaporanLppa($idLppa)
    {
        $result = $this->Open($this->mSqlQueries['get_laporan_lppa'], array($idLppa));
        return $result;
    }

    public function GetMaxId() {
        $return = $this->Open($this->mSqlQueries['get_max_id'], array());

        if ($return) {
            return $return[0];
        } else {
            return null;
        }
    }
    
    public function DoUpdateFile($fileName, $dataId) {
        
        $result = $this->Execute($this->mSqlQueries['do_update_file'], array(
            $fileName,
            $dataId
        ));

        return $result;
    }

    public function AddLppa($data = array())
    {
        $this->StartTrans();
        //$this->SetDebugOn();
        $nomorLppa = $this->GetAutoGenerateNomor(date('Y-m-d', strtotime($data['tanggal'])));

        $result  = $this->Execute($this->mSqlQueries['add_lppa'], array(
              $nomorLppa,
              $data['tahun_anggaran_id'],
              $data['realisasi_id'],
              $data['unit_kerja_id'],
              $data['uraian'],
              $data['penanggung_jawab'],
              $data['mengetahui']
        ));
        $id = $this->LastInsertId();
        if(!empty($data['KOMP']) && $result == TRUE){
            foreach($data['KOMP'] as $key => $v){
                $result  = $this->Execute($this->mSqlQueries['add_lppa_detail'], array(
                     $id,
                     $v['pdet_id'],
                     $v['nominal_lppa'],
                     $v['komponen_deskripsi']
                
                ));
            }
        }
       
        
        $this->EndTrans($result);
        return $result;
    }

   public function UpdateLppa($data = array())
   {
       $this->StartTrans();
       //$this->SetDebugOn();
        // $nomorPengajuan = $param['nomorPengajuan'];
        $result  = $this->Execute($this->mSqlQueries['update_lppa'], array(
              $data['tahun_anggaran_id'],
              $data['realisasi_id'],
              $data['unit_kerja_id'],
              $data['uraian'],
              $data['penanggung_jawab'],
              $data['mengetahui'],
              $data['lppa_id']
        ));

        $getTotalLppaDetail = $this->Open($this->mSqlQueries['get_total_lppa_detail'], array($data['lppa_id']));
        if($getTotalLppaDetail[0]['total'] > 0 ){
            if($result == TRUE){
                 $result  = $this->Execute($this->mSqlQueries['delete_lppa_detail'], array($data['lppa_id']));
            }
        }        
        if(!empty($data['KOMP']) && $result == TRUE){            
            foreach($data['KOMP'] as $key => $v){
                $result  = $this->Execute($this->mSqlQueries['add_lppa_detail'], array(
                     $data['lppa_id'],
                     $v['pdet_id'],
                     $v['nominal_lppa'],
                     $v['komponen_deskripsi']
                
                ));
            }
        }
       
               
        $this->EndTrans($result);
        return $result;
   }

   public function DeleteLppa($id)
   {
       $this->StartTrans();
       //$this->SetDebugOn();
        $result  = $this->Execute($this->mSqlQueries['delete_lppa'], array($id));
        $this->EndTrans($result);
        return $result;
   }
   //get combo tahun anggaran
   public function GetComboTahunAnggaran() 
   {
      $result = $this->Open($this->mSqlQueries['get_combo_tahun_anggaran'], array());
      return $result;
   }
   
   public function GetTahunAnggaranAktif() 
   {
      $result = $this->Open($this->mSqlQueries['get_tahun_anggaran_aktif'], array());
      return $result[0];
   }   
      

   public function getDateRange()
   {
      //$this->SetDebugOn();
      $result     = array();
      $data       = $this->Open($this->mSqlQueries['get_range_tanggal'], array());
      $getdate    = getdate();
      $currMon    = (int)$getdate['mon'];
      $currYear   = (int)$getdate['year'];
      
      if(!empty($data)){
         $result['start_date']    = date('Y-m-d', strtotime($data[0]['tanggal_awal']));
         $result['end_date']      = date('Y-m-t', strtotime($data[0]['tanggal_akhir']));
         $result['min_year']      = date('Y', strtotime($data[0]['tanggal_awal']));
         $result['max_year']      = date('Y', strtotime($data[0]['tanggal_akhir']));
      }else{
         $result['start_date']       = date('Y-m-d', mktime(0,0,0, $currMon, 1, $currYear));
         $result['end_date']         = date('Y-m-t', mktime(0,0,0, $currMon, 1, $currYear));
         $result['min_year']      = $currYear;
         $result['max_year']      = $currYear;
      }

      return $result;
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


}
?>