<?php

class KodeJurnal extends Database {

	protected $mSqlFile= 'module/history_transaksi_realisasi_kode_jurnal/business/kode_jurnal.sql.php';
	
	function __construct($connectionNumber=0) {
		parent::__construct($connectionNumber);
		//$this->mrDbEngine->debug = 1;
	}
		
  function GetQueryKeren($sql,$params) {
		foreach ($params as $k => $v) {
			if (is_array($v)) {
				$params[$k] = '~~' . join("~~,~~", $v) . '~~';
				$params[$k] = str_replace('~~', '\'', addslashes($params[$k]));
			} else {
				$params[$k] = addslashes($params[$k]);
			}
		}
		$param_serialized = '~~' . join("~~,~~", $params) . '~~';
		$param_serialized = str_replace('~~', '\'', addslashes($param_serialized));
		eval('$sql_parsed = sprintf("' . $sql . '", ' . $param_serialized . ');');
		//echo $sql_parsed;
		return $sql_parsed;
	}

	function GetData($offset, $limit, $nama='') {
		$sql = $this->GetQueryKeren($this->mSqlQueries['get_data'], array('%'.$nama.'%', '%'.$nama.'%', $offset, $limit));
		//echo "<pre>" . stripslashes($sql) . "</pre>";
		return $this->Open(stripslashes($sql), array());
	}

	function GetCountData($nama) {
		$sql = $this->GetQueryKeren($this->mSqlQueries['get_count_data'], array('%'.$nama.'%', '%'.$nama.'%'));
      $result = $this->Open(stripslashes($sql), array());
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
   
   function getDetailKodeJurnal ($kode_jurnal_from_function_get_data)
   {
      foreach ($kode_jurnal_from_function_get_data as $value)
         $return[$value['id']] = $this->Open($this->mSqlQueries['get_detail_kode_jurnal'], array($value['id']));
      return $return;
   }

//===DO==
	
	function DoAddData($kode, $nama, $detilkode) {
		$bkkAdded = $this->Execute($this->mSqlQueries['do_add_data'], array($kode, $nama));
		$result = $this->LastInsertId();
		if ($result != "") {
			$jurkodeid = $result;
			$result = true;
			if (is_array($detilkode)) {
				for ($i=0; $i<count($detilkode['tambah']); $i++) {
					$coaid = $detilkode['tambah'][$i]['id'];
					$isdebet = $detilkode['tambah'][$i]['isdebet'];
					$result = $this->Execute($this->mSqlQueries['do_add_detil_jurnal'], array($jurkodeid, $coaid, $isdebet));
				}
			}
		}
		return $result;
	}
	
	function DoUpdateData($kode, $nama, $id, $detilkode) {
		$result = $this->Execute($this->mSqlQueries['do_update_data'], array($kode, $nama, $id));
	  //$debug = sprintf($this->mSqlQueries['do_update_gaji'], $gajiKode, $gajiNama, $tipeunit, $satker, $gajiId);
	  //echo $debug;
	  
		if ($result) {
			if (is_array($detilkode)) {
				$jurkodeid = $id;
				for ($i=0; $i<count($detilkode['tambah']); $i++) {
					$coaid = $detilkode['tambah'][$i]['id'];
					$isdebet = $detilkode['tambah'][$i]['isdebet'];
					$result = $this->Execute($this->mSqlQueries['do_add_detil_jurnal'], array($jurkodeid, $coaid, $isdebet));
				}
			}
		}
		return $result;
	}
	
	function DoDeleteData($id) {
		$result=$this->Execute($this->mSqlQueries['do_delete_data'], array($id));
		return $result;
	}

	function DoDeleteDataByArrayId($arrId) {
		$id = implode("', '", $arrId);
		$result=$this->Execute($this->mSqlQueries['do_delete_data_by_array_id'], array($id));
		return $result;
	}
   
}

?>