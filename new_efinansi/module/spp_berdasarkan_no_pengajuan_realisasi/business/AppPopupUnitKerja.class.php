<?php

class AppPopupUnitkerja extends Database {

   protected $mSqlFile= 'module/realisasi_pencairan_2/business/apppopupunitkerja.sql.php';
   
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
		
	function GetDataUnitkerja ($offset, $limit, $kode='', $unitkerja='', $tipeunit='',$parentId) 
	{
	/**
		if($tipeunit != "") 
			$str_tipeunit = " AND unitkerjaTipeUnitId = " . $tipeunit;
		else 
			$str_tipeunit = "";

		if($role['role_name'] == "OperatorUnit") 
			$str_unit = " AND (unitkerjaParentId=" . 
						$unitkerjaUser['unit_kerja_id'] . " OR unitkerjaId=" . 
						$unitkerjaUser['unit_kerja_id'] . ")";
		else 
			$str_unit="";
			
		$sql = $this->GetQueryKeren($this->mSqlQueries['get_data_unitkerja'], 
				array('%'.$kode.'%', '%'.$kode.'%', '%'.$unitkerja.'%',
				 '%'.$unitkerja.'%', $str_tipeunit, $str_unit, $offset, $limit));
		//echo "<pre>" . $sql . "</pre>";
		
		return $this->Open($sql, array());
		*/
		$result = $this->Open($this->mSqlQueries['get_list_unit_kerja'],
					array(
							$parentId,
							'%',
							'%'.$kode.'%',
							'%'.$unitkerja.'%',
							'%'.$tipeunit.'%',
							$offset, $limit
						 )
					);
	
		return $result;
	}

	function GetCountDataUnitkerja ($kode='', $unitkerja='', $tipeunit='', $parentId) 
	{
		/**
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
		
		*/
		$result = $this->Open($this->mSqlQueries['get_count_list_unit_kerja'],
					array(
							$parentId,
							'%',
							'%'.$kode.'%',
							'%'.$unitkerja.'%',
							'%'.$tipeunit.'%'
						 )
					);
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

	/**
	 * added fitur baru
	 * @since 30 Desember 2011
	 */
 	public function GetTotalSubUnitKerja($parentId)
	 {
	 	$result = $this->Open($this->mSqlQueries['get_total_sub_unit_kerja'], 
		 	array($parentId));
	 	return $result[0]['total'];
	 }
	
	/**
	 * fungsi GetDataUnitKerjaPimpinan
	 * added
	 * @since 9 Januarai 2012
	 * untuk mendapatkan daftar unit kerja beserta nama pimpinan
	 * @access public 
	 * @return array()
	 */	
	 public function GetDataUnitKerjaPimpinan ($offset, $limit, $unitkerja='',$pimpinan='',$parentId) 
	 {
	 	$result = $this->Open($this->mSqlQueries['get_data_list_unit_kerja_pimpinan'],
					array(
							$parentId,
							'%',
							'%'.$unitkerja.'%',
							'%'.$pimpinan.'%',
							$offset, $limit
						 )
					);
	
		return $result;
	 }
	 
	 /**
	 * fungsi GetCountDataUnitKerjaPimpinan
	 * added
	 * @since 9 Januarai 2012
	 * untuk mendapatkan total unit kerja dengan nama pimpinan
	 * @access public 
	 * @return number
	 */	
	 public function GetCountDataUnitKerjaPimpinan ($unitkerja='',$pimpinan='',$parentId) 
	 {
	 	$result = $this->Open($this->mSqlQueries['get_count_data_list_unit_kerja_pimpinan'],
					array(
							$parentId,
							'%',
							'%'.$unitkerja.'%',
							'%'.$pimpinan.'%'
						 )
					);
		if (!$result) {
			return 0;
		} else {
			return $result[0]['total'];
		}
	 }
	 
	/**
	 * end
	 */	
    function GetListDataUnitkerja ($offset, $limit, $kode='', $unitkerja='', $tipeunit='', 
							  $role=array(), $unitkerjaUser=array()) {
		if($tipeunit != "") 
			$str_tipeunit = " AND unitkerjaTipeUnitId = " . $tipeunit;
		else 
			$str_tipeunit = "";

		if($role['role_name'] == "OperatorUnit") 
			$str_unit = " AND (unitkerjaParentId=" . 
						$unitkerjaUser['unit_kerja_id'] . " OR unitkerjaId=" . 
						$unitkerjaUser['unit_kerja_id'] . ")";
		else 
			$str_unit="";
			
		$sql = $this->GetQueryKeren($this->mSqlQueries['get_data_unitkerja'], 
				array('%'.$kode.'%', '%'.$kode.'%', '%'.$unitkerja.'%',
				 '%'.$unitkerja.'%', $str_tipeunit, $str_unit, $offset, $limit));
		//echo "<pre>" . $sql . "</pre>";
		$return = $this->Open($sql, array());
		
		return $return;
		//print_r($return);
		//if($return){
		//    return $return;
		//}else{
		//    echo $this->getLastError();
		//}
		//return $this->Open($sql, array());
	}

	function GetCountListDataUnitkerja ($kode='', $unitkerja='', $tipeunit='', $role=array(), $unitkerjaUser=array()) {
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
	function GetListDataTipeUnit($unitkerjaId = NULL) {
		$result = $this->Open($this->mSqlQueries['get_data_tipe_unit'], array());
		return $result;
	}

}

