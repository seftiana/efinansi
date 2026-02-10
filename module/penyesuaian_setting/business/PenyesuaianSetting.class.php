<?php
/*
   @ClassName : PenyesuaianSetting
   @Copyright : PT Gamatechno Indonesia
   @Analyzed By : Dyan Galih <galih@gamatechno.com>
   @Author By : Dyan Galih <galih@gamatechno.com>
   @Version : 0.1
   @StartDate : 2011-09-09
   @LastUpdate : 2011-09-09
   @Description : Setting Penyesuaian
*/

class PenyesuaianSetting extends Database
{
   protected $mSqlFile;

   function __construct ($connectionNumber=0)
   {
      $this->mSqlFile = 'module/penyesuaian_setting/business/penyesuaian_setting.sql.php';
      parent::__construct($connectionNumber);
#      $this->SetDebugOn();
   }

   public function GetSettingPenyesuaian(){
      $result = $this->Open($this->mSqlQueries['get_setting_penyesuaian'], array());
      return $result;
   }

   public function InputSettingPenyesuaian($data){
      $this->Execute($this->mSqlQueries['input_penyesuaian'], $data);
      return $this->AffectedRows();
   }

   public function UpdateSettingPenyesuaian($data){
      $this->Execute($this->mSqlQueries['update_penyesuaian'], $data);
      return $this->AffectedRows();
   }

   public function InputSettingPenyesuaianDetil($detil, $mstId){

      $this->Execute($this->mSqlQueries['delete_penyesuaian_detil'], array($mstId));
      foreach ($detil as $key => $value)
      {
         $data['mstId'] = $mstId;
         $data['coa'] = $key;
         $data['nominal'] = $value['nominal'];
         $data['type'] = $value['typeRekening']=='debet'?'D':'K';
         $data['user'] = Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
         $this->Execute($this->mSqlQueries['add_detil_penyesuaian'], $data);

         if($this->AffectedRows()==false)
            return $this->AffectedRows();
      }

      return true;
   }

   public function GetMaxMstId(){
      $result = $this->Open($this->mSqlQueries['get_last_mst_id'], array());
      return $result['0']['setPenyesuaianId'];
   }

   public function GetListPenyesuaian($offset, $limit, $kode){
      $result = $this->Open($this->mSqlQueries['get_list_penyesuaian'], array($kode,'%'.$kode.'%', $offset, $limit));
      return $result;
   }

   public function GetCount(){
      $result = $this->Open($this->mSqlQueries['get_search_count'], array());
      return $result[0]['total'];
   }

   public function GetDataById($id){
      $result = $this->Open($this->mSqlQueries['get_penyesuaian_by_id'], array($id));
      return $result[0];
   }

   public function GetDataDetilByMstId($id){
      $result = $this->Open($this->mSqlQueries['get_penyesuaian_detil_by_mst_id'], array($id));
      return $result;
   }

   public function DeleteSettingJurnalPenyesuaian($id){
      $this->StartTrans();
      $this->Execute($this->mSqlQueries['delete_penyesuaian'], array($id));
      $this->Execute($this->mSqlQueries['delete_penyesuaian_detil'], array($id));
      $result = $this->EndTrans(true);
      return $result;
   }

}
?>