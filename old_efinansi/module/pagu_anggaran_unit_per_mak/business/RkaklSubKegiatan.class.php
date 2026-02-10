<?php
class RkaklSubKegiatan extends Database {

   protected $mSqlFile= 'module/pagu_anggaran_unit_per_mak/business/rkaklSubKegiatan.sql.php';

   function __construct($connectionNumber=0) {
      parent::__construct($connectionNumber);
   }

   public function GetCount(){
      $result = $this->Open($this->mSqlQueries['get_search_count'], array());
      return $result['0']['total'];
   }

   function GetCountRkaklSubKegiatan($kode='', $nama=''){
      if($nama != '' AND $kode != ''):
      	$and	= " OR ";
      elseif($nama != '' AND $kode == ''):
      	$and	= " AND ";
      elseif($nama == '' AND $kode != ''):
      	$and	= " AND ";
      elseif($nama == '' AND $kode == ''):
      	$and	= " AND ";
      else:
      	$and	= " AND ";
      endif;
      $sql		= sprintf($this->mSqlQueries['get_count_rkakl_sub_kegiatan'],
	  				'%s',$and,'%s');
	  $count	= $this->Open($sql, array('%'.$kode.'%','%'.$nama.'%'));
      
      return $count['0']['count'];
   }

   function GetRkaklSubKegiatan($kode='', $nama='', $start, $limit) {
   	 
      $sql		= sprintf($this->mSqlQueries['get_rkakl_sub_kegiatan'],
	  						'%s','OR','%s','%s','%s');
	  $result	= $this->Open($sql, array('%'.$kode.'%','%'.$nama.'%',$start,$limit));
	  
      return $result;
   }

   function GetRkaklSubKegiatanById($id) {
      return $this->Open($this->mSqlQueries['get_rkakl_sub_kegiatan_by_id'], array($id));
   }

   function AddRkaklSubKegiatan($kode,$nama){
      return $this->Execute($this->mSqlQueries['insert_rkakl_sub_kegiatan'], array($kode,$nama));
   }

   function UpdateRkaklSubKegiatan($kode, $nama, $id){
      return $this->Execute($this->mSqlQueries['update_rkakl_sub_kegiatan'], array($kode,$nama,$id));
   }

   function DeleteRkaklSubKegiatanById($id){
      return $this->Execute($this->mSqlQueries['delete_rkakl_sub_kegiatan'], array($id));
   }

	function DeleteRkaklSubKegiatanByArrayId($arrId) {
		$arrId = implode("', '", $arrId);
		$result=$this->Execute($this->mSqlQueries['delete_rkakl_sub_kegiatan_array'], array($arrId));
		//echo $this->getLastError(); exit;
		return $result;
	}
}
?>
