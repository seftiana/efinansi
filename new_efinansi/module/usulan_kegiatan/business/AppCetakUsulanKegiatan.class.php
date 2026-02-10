<?php

class AppCetakUsulanKegiatan extends Database {

   protected $mSqlFile= 'module/usulan_kegiatan/business/appcetakusulankegiatan.sql.php';
   
   function __construct($connectionNumber=0) {
      parent::__construct($connectionNumber);       
   }
	function GetDataProgram($id) {
      $ret = $this->Open($this->mSqlQueries['get_data_program'], array($id));
	  //print_r($ret);
	  return $ret[0];
	}
	function GetDataKegiatan($id) {
      $ret = $this->Open($this->mSqlQueries['get_kegiatan'], array($id));
	  //$debug = sprintf($this->mSqlQueries['get_kegiatan'], $id);
	  //echo $debug;
	  return $ret;
	}
      
	/*	
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
	function GetDataUnitkerja ($offset, $limit, $satker='', $kode='', $unitkerja='', $tipeunit='') {
		if($satker != "") $str_satker = " AND ukr.unitkerjaParentId = $satker ";
		else $str_satker = "";
		if($tipeunit != "") $str_tipeunit = " AND tukr.tipeunitId = $tipeunit ";
		else $str_tipeunit = "";

		//$sql = sprintf($this->mSqlQueries['get_data_unitkerja'], '%s', '%s', '%s', '%s', '%d', '%d');
		$result = $this->GetQueryKeren($this->mSqlQueries['get_data_unitkerja'], array('%'.$kode.'%', '%'.$unitkerja.'%', $str_satker, $str_tipeunit, $offset, $limit));
		//echo $result;

	  
	 // $debug = sprintf($sql, '%'.$kode.'%', '%'.$unitkerja.'%', $str_satker, $str_tipeunit, $offset, $limit);
	  //echo $debug;
	  //print_r($result);

		return $this->Open($result, array());
	}

	function GetCountDataUnitkerja ($satker='', $kode='', $unitkerja='', $tipeunit='') {
		if($satker != "") $str_satker = " AND ukr.unitkerjaParentId = $satker ";
		else $str_satker = "";
		if($tipeunit != "") $str_tipeunit = " AND tukr.tipeunitId = $tipeunit ";
		else $str_tipeunit = "";
		$sql = $this->GetQueryKeren($this->mSqlQueries['get_count_data_unitkerja'], array('%'.$kode.'%', '%'.$unitkerja.'%', $str_satker, $str_tipeunit));
		$result = $this->Open($sql, array());
	 // $debug = sprintf($this->mSqlQueries['get_count_data_unitkerja'], '%'.$kode.'%', '%'.$unitkerja.'%', $str_satker, $str_tipeunit);
	 // echo $debug;
	  //print_r($result);

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
	*/
}
?>
