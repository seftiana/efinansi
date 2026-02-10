<?php

class DetilCoaJenisBiaya extends Database {

	protected $mSqlFile= 'module/finansi_coa_jenis_biaya/business/detil_coa_jenis_biaya.sql.php';
	
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

	function GetData($offset, $limit, $jurnal_id=0) {
		$sql = $this->GetQueryKeren($this->mSqlQueries['get_data'], array($jurnal_id, $offset, $limit));
		//echo "<pre>" . stripslashes($sql) . "</pre>";
		return $this->Open(stripslashes($sql), array());
	}
	
	function GetDataAll($jurnal_id=0) {
		$sql = $this->GetQueryKeren($this->mSqlQueries['get_data_all'], array($jurnal_id));
		//echo "<pre>" . stripslashes($sql) . "</pre>";
		return $this->Open(stripslashes($sql), array());
	}

	function GetCountData($jurnal_id) {
		$sql = $this->GetQueryKeren($this->mSqlQueries['get_count_data'], array($jurnal_id));
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
   
   function GetJurkodeById($id) {
		$result = $this->Open($this->mSqlQueries['get_jurkode_by_id'], array($id));
		return $result;
	}

//===DO==
	
	function DoAddData($kode, $nama) {
		$result = $this->Execute($this->mSqlQueries['do_add_data'], array($kode, $nama));
		return $result;
	}
	
	function DoUpdateData($kode, $nama, $id) {
		$result = $this->Execute($this->mSqlQueries['do_update_data'], array($kode, $nama, $id));
	  //$debug = sprintf($this->mSqlQueries['do_update_gaji'], $gajiKode, $gajiNama, $tipeunit, $satker, $gajiId);
	  //echo $debug;
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
