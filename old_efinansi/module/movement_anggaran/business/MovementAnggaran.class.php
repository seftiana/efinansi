<?php
/*
   @ClassName : MovementAnggaran
   @Copyright : PT Gamatechno Indonesia
   @Analyzed By : Dyan Galih <galih@gamatechno.com>
   @Author By : Dyan Galih <galih@gamatechno.com>
   @Version : 0.1
   @StartDate : 2011-01-01
   @LastUpdate : 2011-01-01
   @Description : Movement Anggaran
*/

class MovementAnggaran extends Database
{
   protected $mSqlFile;
   protected $mUserId ;

   function __construct ($connectionNumber=0)
   {
      $this->mSqlFile = 'module/movement_anggaran/business/movement_anggaran.sql.php';
      parent::__construct($connectionNumber);
      
      $this->mUserId = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
   }

   public function GetComboBas(){
      $result = $this->Open($this->mSqlQueries['get_combo_bas'], array());
      return $result;
   }
   
    public function UpdateKomponenAnggaran($nilai,$id){
        $result = $this->Execute($this->mSqlQueries['update_komponen_anggaran'], array($nilai,$id));
        if($result)
        {
            return $result;
        }
        else
        {
            return $this->getLastError();
        }
        
    }
    
    public function UpdateKomponenAnggaranAsal($nilai,$id)
    {
        $result = $this->Execute($this->mSqlQueries['update_komponen_anggaran_asal'], array($nilai,$id));
        
        if($result)
        {
            return $result;
        }
        else
        {
            return $this->getLastError();
        }
    }
    
    public function InsertIntoHistoryMovement($params = array())
    {
        //$this->SetDebugOn();
        $result        = true;
        $userId    = $this->mUserId;
        $this->StartTrans();
        if(!is_array($params)){
             $result &= false;
        }
        
        
        $result = $this->Execute($this->mSqlQueries['insert_into_history_movement'] , 
                  array(
                    $params['tahun_periode_id'],
                    $params['unitKerjaId'],
                    $params['kegrefId'],
                    $params['unitKerjaIdTujuan'],
                    $params['kegrefIdTujuan'],
                    $params['nominal_movement'] ,
                    $userId
                  ));
        
        $mvID  = $this->LastInsertId();
        //untuk detail belanja asal
         if(!empty($params['KOMP'])){
             foreach ($params['KOMP'] as $komponen) {
                  $result &= $this->Execute($this->mSqlQueries['insert_into_apbnp_detail'], array(
                  $mvID, 
                  $komponen['rp_id'],
                  $komponen['nominal_hid'],
                  $komponen['nominal'],
                  'asal',
                  $userId
                ));
             }
         }
        
        //untuk detail belanja tujuan
        if(!empty($params['KOMPTUJUAN'])){
             foreach ($params['KOMPTUJUAN'] as $komponen) {
                  $result &= $this->Execute($this->mSqlQueries['insert_into_apbnp_detail'], array(
                  $mvID, 
                  $komponen['rp_id'],
                  $komponen['nominal_hid'],
                  $komponen['nominal'],
                  'tujuan',
                  $userId
                ));
             }
         }
       return $this->EndTrans($result);
    }
    
    function GetLastInsertIdApbnp()
    {
        $result = $this->Open(
            $this->mSqlQueries['get_last_insert_id_apbnp'], 
            array()
        );
        
        if($result){
            return $result[0]['last_id'];
        }else{
            return $this->getLastError();
        }
    }
    
    public function DeleteApbnp($id)
    {
        $result = $this->Execute($this->mSqlQueries['delete_apbnp'], array($id));
        
        if($result){
            return $result;
        }else{
            echo $this->getLastError();
        }
    }
    
    public function InsertIntoApbnpDetail($idApbnpMaster,$komponenId,$nilaiAwal,$nilai,$status,$userId)
    {
        $result = $this->Execute($this->mSqlQueries['insert_into_apbnp_detail'], array(
            $idApbnpMaster, 
            $komponenId,
            $nilaiAwal,
            $nilai,
            $status,
            $userId
        ));
        
        if($result){
            return $result;
        }else{
            return $this->getLastError();
        }
    }
    
    public function GetTahunAnggaranAktif()
    {
        $result     = $this->Open($this->mSqlQueries['get_tahun_anggaran_aktif'], array());
        
        return $result[0];
    }
}
?>
