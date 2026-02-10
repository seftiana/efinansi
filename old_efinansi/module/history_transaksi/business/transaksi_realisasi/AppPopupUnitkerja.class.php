<?php

class AppPopupUnitkerja extends Database {

   protected $mSqlFile= 'module/transaksi_realisasi/business/apppopupunitkerja.sql.php';
   
   function __construct($connectionNumber=0) {
      parent::__construct($connectionNumber);       
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
		
	function GetDataUnitkerja ($offset, $limit, $kode='', $unitkerja='', $tipeunit='', $role=array(), $unitkerjaUser=array()) {
		if($tipeunit != "") 
			$str_tipeunit = " AND unitkerjaTipeUnitId = " . $tipeunit;
		else 
			$str_tipeunit = "";

		if($role['role_name'] == "OperatorUnit") 
			$str_unit = " AND (unitkerjaParentId=" . $unitkerjaUser['unit_kerja_id'] . " OR unitkerjaId=" . $unitkerjaUser['unit_kerja_id'] . ")";
		else 
			$str_unit="";
		$sql = $this->GetQueryKeren($this->mSqlQueries['get_data_unitkerja'], array('%'.$kode.'%', '%'.$kode.'%', '%'.$unitkerja.'%', '%'.$unitkerja.'%', $str_tipeunit, $str_unit, $offset, $limit));
		//echo "<pre>" . $sql . "</pre>";
		return $this->Open($sql, array());
	}

	function GetCountDataUnitkerja ($kode='', $unitkerja='', $tipeunit='', $role=array(), $unitkerjaUser=array()) {
		if($tipeunit != "") 
			$str_tipeunit = " AND unitkerjaTipeUnitId = " . $tipeunit;
		else 
			$str_tipeunit = "";

		if($role['role_name'] == "OperatorUnit") 
			$str_unit = " AND (unitkerjaParentId=" . $unitkerjaUser['unit_kerja_id'] . " OR unitkerjaId=" . $unitkerjaUser['unit_kerja_id'] . ")";
		else 
			$str_unit="";
		$sql = $this->GetQueryKeren($this->mSqlQueries['get_count_data_unitkerja'], array('%'.$kode.'%', '%'.$kode.'%', '%'.$unitkerja.'%', '%'.$unitkerja.'%', $str_tipeunit, $str_unit));
		//echo $sql;
		$result = $this->Open($sql, array());

		if (!$result) {
			return 0;
		} else {
			return $result[0]['total'];
		}
	}
	function GetDataTipeUnit($unitkerjaId = NULL) {
		$result = $this->Open($this->mSqlQueries['get_data_tipe_unit'], array());
		return $result;
	}
}
?>
