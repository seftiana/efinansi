<?php
class RkaklKegiatan extends Database {

   protected $mSqlFile= 'module/rkakl_kegiatan/business/rkaklKegiatan.sql.php';

   function __construct($connectionNumber=0) {
      parent::__construct($connectionNumber);
   }

   function GetCountRkaklKegiatan($kode='', $nama=''){
      $count   = $this->Open($this->mSqlQueries['get_count_rkakl_kegiatan'], array(
         '%'.$kode.'%', 
         '%'.$nama.'%'
      ));
      return $count['0']['count'];
   }

   public function GetCount(){
      $result = $this->Open($this->mSqlQueries['get_search_count'], array());
      return $result['0']['total'];
   }

   function GetRkaklKegiatan($kode='', $nama='', $start, $limit) {
      $result     = $this->Open($this->mSqlQueries['get_rkakl_kegiatan'], array(
         '%'.$kode.'%', 
         '%'.$nama.'%', 
         $start, 
         $limit
      ));

      return $result;

     //return $this->Open($this->mSqlQueries['get_rkakl_kegiatan'],
     //array('%'.$kode.'%', '%'.$nama.'%', $start, $limit));
   }

   function GetRkaklKegiatanById($id) {
      return $this->Open($this->mSqlQueries['get_rkakl_kegiatan_by_id'], array($id));
   }

   function AddRkaklKegiatan($kode,$nama,$program){
      return $this->Execute($this->mSqlQueries['insert_rkakl_kegiatan'], array($kode,$nama,$program));
   }

   function UpdateRkaklKegiatan($kode, $nama,$program, $id){
      return $this->Execute($this->mSqlQueries['update_rkakl_kegiatan'], array($kode,$nama,$program,$id));
   }

   function DeleteRkaklKegiatanById($id){
      return $this->Execute($this->mSqlQueries['delete_rkakl_kegiatan'], array($id));
   }

  function DeleteRkaklKegiatanByArrayId($arrId) {
     $arrId = implode("', '", $arrId);
     $result=$this->Execute($this->mSqlQueries['delete_rkakl_kegiatan_array'], array($arrId));
     //echo $this->getLastError(); exit;
     return $result;
  }

   #tambahan get program
  function GetProgram(){
      return $this->Open($this->mSqlQueries['get_data_program'],array());
  }
}
?>