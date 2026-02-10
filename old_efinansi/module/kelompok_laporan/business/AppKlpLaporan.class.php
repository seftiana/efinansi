<?php

class AppKelpLaporan extends Database {

	protected $mSqlFile= 'module/kelompok_laporan/business/appklplaporan.sql.php';

	function __construct($connectionNumber=0) {
		parent::__construct($connectionNumber);
		//$this->mrDbEngine->debug = 1;
        //$this->SetDebugOn();
	}

   public function GetKelompokInfo($id){
#      $this->SetDebugOn();
      $result = $this->Open($this->mSqlQueries['get_kelompok_info'], array($id));
      return $result[0];
   }

   function GetError() {
		$errno = mysql_errno();
		if($errno == "1451") {
			$return = "Terdapat data lain yang menggunakan data ini.";
		}
		return $return;
   }

   function GetJenisLaporan(){
   	return $this->Open($this->mSqlQueries['get_jenis_laporan'],array());
	}

	function GetBentukTransaksi($id){
   	return $this->Open($this->mSqlQueries['get_bentuk_transaksi'],array($id));
	}


	function GetData($offset, $limit, $nama='',$jns_lap = '') {
	   if($jns_lap != '' && $jns_lap != 'all'){
	       $sql_jns_lap = " AND jenis_laporan_id = '".$jns_lap."' ";
	   } else {
	       $sql_jns_lap ="";
	   }
        $query = sprintf($this->mSqlQueries['get_data'], '%'.$nama.'%',$sql_jns_lap, $offset, $limit);
		$result = $this->Open($query, array());
		return $result;
	}

	function GetCountData($nama,$jns_lap='') {
 	   if($jns_lap != '' && $jns_lap != 'all'){
	       $sql_jns_lap = " AND jenis_laporan_id = '".$jns_lap."' ";
	   } else {
	       $sql_jns_lap ="";
	   }
	    $query = sprintf($this->mSqlQueries['get_count'], '%'.$nama.'%',$sql_jns_lap);
		$result = $this->Open($query, array());
		if (!$result) {
			return 0;
		} else {
			return $result[0]['total'];
		}
	}

	function GetDataById($id) {
		$result = $this->Open($this->mSqlQueries['get_data_by_id'], array($id));
		return $result;
	}

	function GetDataByArrayId($arrId) {
		$id_klp_lap = implode("', '", $arrId);
		$result = $this->Open($this->mSqlQueries['get_data_by_array_id'], array($id_klp_lap));
		return $result;
	}

//===DO==

	function DoAddData($nama, $is_tambah, $jns_lap,$no_urutan) {
		$result = $this->Execute($this->mSqlQueries['do_add'], array($nama,$is_tambah, $jns_lap,$no_urutan));
		return $result;
	}

	function DoUpdateData($nama, $is_tambah, $jns_lap,$no_urutan, $id) {
		$result = $this->Execute($this->mSqlQueries['do_update'], array($nama, $is_tambah, $jns_lap,$no_urutan, $id));
		return $result;
	}

	function DoDeleteDataById($id) {
		$result=$this->Execute($this->mSqlQueries['do_delete_by_id'], array($id));
		return $result;
	}

	function DoDeleteDataByArrayId($arrId) {
		$id_klp_lap = implode("', '", $arrId);
		$result=$this->Execute($this->mSqlQueries['do_delete_by_array_id'], array($id_klp_lap));
		return $result;
	}

   //detil
   function GetCountDetilKlpLaporan($id, $key) {
      $result = $this->Open($this->mSqlQueries['get_count_detil_klp_laporan'], array($id, '%'.$key.'%'));
      if (!$result) {
         return 0;
      } else {
         return $result[0]['total'];
      }
   }

   function GetDataDetilKlpLaporan($id, $key, $offset, $limit) {
      $result = $this->Open($this->mSqlQueries['get_data_detil_klp_laporan'], array($id, '%'.$key.'%', $offset, $limit));
      return $result;
   }

   // do add detil coa kel lap
   function DoAddDetilData($id_kel_lap, $id_coa, $coa_type) {
      $result = $this->Execute($this->mSqlQueries['do_add_detil_coa_kel_lap'], array($id_kel_lap, $id_coa, $coa_type));
      return $result;
   }

   function DoDeleteDetilDataById($id) {
      $result=$this->Execute($this->mSqlQueries['do_delete_detil_by_id'], array($id));
      return $result;
   }

   function DoDeleteDetilDataByArrayId($arrId) {
      $id_coa_klp_lap = implode("', '", $arrId);
      $result=$this->Execute($this->mSqlQueries['do_delete_detil_by_array_id'], array($id_coa_klp_lap));
      return $result;
   }
   
   /**
    * untuk mendapatkan no urut
    * @since 11 April 2012
    */
    function GenerateNoUrutan($jns_lap)
    {
        $result = $this->Open($this->mSqlQueries['generate_no_urutan'], array($jns_lap,$jns_lap));
        return $result[0]['no_urutan'];
    }
    
}