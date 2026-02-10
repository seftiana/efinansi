<?php
class KinerjaKegiatan extends Database {

   protected $mSqlFile= 'module/kinerja_kegiatan/business/kinerjakegiatan.sql.php';
   
   function __construct($connectionNumber=0) {
      parent::__construct($connectionNumber);       
   }
   
   function GetCountKinerjaKegiatan($kode='', $nama=''){
      $count = $this->Open(
         $this->mSqlQueries['get_count_kinerja_kegiatan'], 
         array()
      );
      return $count['0']['count'];
   }
      
   function GetKinerjaKegiatan($kode='', $nama='', $start, $limit) {           
      return $this->Open($this->mSqlQueries['get_kinerja_kegiatan'], 
     array('%'.$kode.'%', '%'.$nama.'%', $start, $limit));
   }
   
   function GetKinerjaKegiatanById($id) {           
      return $this->Open($this->mSqlQueries['get_kinerja_kegiatan_by_id'], array($id));
   }
   
   function AddKinerjaKegiatan($kode,$nama,$kegiatan){
      return $this->Execute($this->mSqlQueries['insert_kinerja_kegiatan'], 
     array($kode,$nama,$kegiatan));
   }
   
   function UpdateKinerjaKegiatan($kode,$nama,$kegiatan,$id){
      return $this->Execute($this->mSqlQueries['update_kinerja_kegiatan'], 
     array($kode,$nama,$kegiatan,$id));
   }
   
   function DeleteKinerjaKegiatanById($id){
      return $this->Execute($this->mSqlQueries['delete_kinerja_kegiatan'], array($id));
   }
   
   function DeleteKinerjaKegiatanByArrayId($arrId) {
      $arrId = implode("', '", $arrId);
      $result=$this->Execute($this->mSqlQueries['delete_kinerja_kegiatan_array'], array($arrId));
      //echo $this->getLastError(); exit;
      return $result;
   }
}
?>
