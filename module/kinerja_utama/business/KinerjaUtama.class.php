<?php
class KinerjaUtama extends Database {

   protected $mSqlFile= 'module/kinerja_utama/business/kinerjautama.sql.php';
   
   function __construct($connectionNumber=0) {
      parent::__construct($connectionNumber);       
   }
   
   function GetCountKinerjaUtama($kode='', $nama=''){
      $count = $this->Open($this->mSqlQueries['get_count_kinerja_utama'], array());
      return $count['0']['total'];
   }
      
   function GetKinerjaUtama($kode='', $nama='', $start, $limit) {		     
      return $this->Open($this->mSqlQueries['get_kinerja_utama'], 
	  array('%'.$kode.'%', '%'.$nama.'%', $start, $limit));
   }
   
   function GetKinerjaUtamaById($id) {		     
      return $this->Open($this->mSqlQueries['get_kinerja_utama_by_id'], array($id));
   }
   
   function AddKinerjaUtama($kode,$nama,$program){
      return $this->Execute($this->mSqlQueries['insert_kinerja_utama'], 
	  array($kode,$nama,$program));
   }
   
   function UpdateKinerjaUtama($kode,$nama,$program,$id){
      return $this->Execute($this->mSqlQueries['update_kinerja_utama'], 
	  array($kode,$nama,$program,$id));
   }
   
   function DeleteKinerjaUtamaById($id){
      return $this->Execute($this->mSqlQueries['delete_kinerja_utama'], array($id));
   }
	
	function DeleteKinerjaUtamaByArrayId($arrId) {
		$arrId = implode("', '", $arrId);
		$result=$this->Execute($this->mSqlQueries['delete_kinerja_utama_array'], array($arrId));
		//echo $this->getLastError(); exit;
		return $result;
	}
}
?>
