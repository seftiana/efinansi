<?php
class RkaklKodePenerimaan extends Database {

   protected $mSqlFile= 'module/rkakl_kode_penerimaan/business/rkaklkodepenerimaan.sql.php';
   
   function __construct($connectionNumber=0) {
      parent::__construct($connectionNumber);       
   }
   
   function GetCountRkaklKodePenerimaan($kode='', $nama=''){
      $count = $this->Open($this->mSqlQueries['get_count_rkakl_kode_penerimaan'], array( '%'.$kode.'%', '%'.$nama.'%'));
      return $count['0']['count'];
   }
      
   function GetRkaklKodePenerimaan($kode='', $nama='', $start, $limit) {		     
      return $this->Open($this->mSqlQueries['get_rkakl_kode_penerimaan'], array('%'.$kode.'%', '%'.$nama.'%', $start, $limit));
   }
   
   function GetRkaklKodePenerimaanById($id) {		     
      return $this->Open($this->mSqlQueries['get_rkakl_kode_penerimaan_by_id'], array($id));
   }
   
   function AddRkaklKodePenerimaan($kode,$nama){
      return $this->Execute($this->mSqlQueries['insert_rkakl_kode_penerimaan'], array($kode,$nama));
   }
   
   function UpdateRkaklKodePenerimaan($kode, $nama, $id){
      return $this->Execute($this->mSqlQueries['update_rkakl_kode_penerimaan'], array($kode,$nama,$id));
   }
   
   function DeleteRkaklKodePenerimaanById($id){
      return $this->Execute($this->mSqlQueries['delete_rkakl_kode_penerimaan'], array($id));
   }
	
	function DeleteRkaklKodePenerimaanByArrayId($arrId) {
		$arrId = implode("', '", $arrId);
		$result=$this->Execute($this->mSqlQueries['delete_rkakl_kode_penerimaan_array'], array($arrId));
		//echo $this->getLastError(); exit;
		return $result;
	}
}
?>
