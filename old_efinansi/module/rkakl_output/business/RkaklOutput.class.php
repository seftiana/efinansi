<?php
class RkaklOutput extends Database {

   protected $mSqlFile= 'module/rkakl_output/business/rkakloutput.sql.php';
   
   function __construct($connectionNumber=0) {
      parent::__construct($connectionNumber);       
   }
   
   function GetCountRkaklOutput($kode='', $nama='', $kegiatan = ''){
      $count = $this->Open(
         $this->mSqlQueries['get_count_rkakl_output'], 
         array( 
            '%'.$kode.'%', 
            '%'.$nama.'%', 
            '%'.$kegiatan.'%', 
            '%'.$kegiatan.'%', 
            (int)($kegiatan == '' OR $kegiatan == null)
         )
      );
      return $count['0']['count'];
   }
      
   function GetRkaklOutput($kode = '', $nama = '', $kegiatan = '', $start, $limit) {          
      return $this->Open(
         $this->mSqlQueries['get_rkakl_output'], 
         array(
            '%'.$kode.'%', 
            '%'.$nama.'%', 
            '%'.$kegiatan.'%', 
            '%'.$kegiatan.'%', 
            (int)($kegiatan == '' OR $kegiatan == null), 
            $start, 
            $limit
         )
      );
   }
   
   function GetRkaklOutputById($id) {          
      return $this->Open($this->mSqlQueries['get_rkakl_output_by_id'], array($id));
   }
   
   function AddRkaklOutput($kode,$nama,$userId,$kegiatan){
      $result     = $this->Execute(
         $this->mSqlQueries['insert_rkakl_output'], 
         array(
            $kode,
            $nama,
            $userId,
            $kegiatan
         )
      );
      if($result){
         return true;
      }else{
         return $this->GetLastError();
     }
   }
   
   function UpdateRkaklOutput($kode, $nama, $userId, $id, $kegiatan){
      return $this->Execute($this->mSqlQueries['update_rkakl_output'], 
     array($kode,$nama,$userId,$kegiatan,$id));
   }
   
   function DeleteRkaklOutputById($id){
      return $this->Execute($this->mSqlQueries['delete_rkakl_output'], array($id));
   }
   
   function DeleteRkaklOutputByArrayId($arrId) {
      $arrId = implode("', '", $arrId);
      $result=$this->Execute($this->mSqlQueries['delete_rkakl_output_array'], array($arrId));
      //echo $this->getLastError(); exit;
      return $result;
   }
   
   #fungsi untuk popup kegiatan
}
?>
