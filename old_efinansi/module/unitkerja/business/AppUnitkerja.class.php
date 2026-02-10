<?php

class AppUnitkerja extends Database {

	protected $mSqlFile= 'module/unitkerja/business/appunitkerja.sql.php';

	function __construct($connectionNumber=0) {
		parent::__construct($connectionNumber);
		$this->idUser = Security::Instance()->mAuthentication->getcurrentuser()->GetUserId();
		//$this->setDebugOn();
	}

   function GetDataExcel ($unitkerja='', $kode='', $tipeunit='') {
		if($tipeunit != "")
			$str_tipeunit = " AND unitkerjaTipeUnitId = " . $tipeunit;
		else
			$str_tipeunit = "";

		$sql = $this->GetQueryKeren($this->mSqlQueries['get_data_excel'], array('%'.$kode.'%', '%'.$kode.'%', '%'.$unitkerja.'%', '%'.$unitkerja.'%', $str_tipeunit));
		//echo "<pre>" . $sql . "</pre>";
		return $this->Open($sql, array());
	}

	function GetUnitKerja($offset, $limit,$kode,$nama,$type){
		$strplus ='';
		if($type!='')
			$strplus = " AND unitkerjaTipeunitId = '%s'";

		$newSql = sprintf($this->mSqlQueries['get_unit_kerja'],'%s','%s',$strplus,'%s','%s',$strplus,'%s','%s');
		//echo $newSql;
		if($type!='')
			$ret = $this->Open($newSql,array('%'.$kode.'%','%'.$nama.'%',$type,'%'.$kode.'%','%'.$nama.'%',$type,$offset, $limit));
		else
			$ret = $this->Open($newSql,array('%'.$kode.'%','%'.$nama.'%','%'.$kode.'%','%'.$nama.'%',$offset, $limit));
      //print_r($ret);
      return $ret;
	}
	//old function

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

	function GetDataUnitkerja ($offset, $limit, $unitkerja='', $kode='', $tipeunit='',$parentId=0) {
		if($tipeunit != "")
			$str_tipeunit = " AND unitkerjaTipeUnitId = " . $tipeunit;
		else
			$str_tipeunit = "";

		$sql = $this->GetQueryKeren(
					$this->mSqlQueries['get_data_unitkerja'],
					array(

							'%'.$kode.'%',
							'%'.$kode.'%',
							'%'.$unitkerja.'%',
							'%'.$unitkerja.'%',
							$str_tipeunit,
							$offset,
							$limit));
		//echo "<pre>" . $sql . "</pre>";
		return $this->Open($sql, array());
	}

	function GetCountDataUnitkerja ($unitkerja='', $kode='', $tipeunit='',$parentId=0) {
		if($tipeunit != "")
			$str_tipeunit = " AND unitkerjaTipeUnitId = " . $tipeunit;
		else
			$str_tipeunit = "";
		/*
      if($satker != "") $str_satker = " AND ukr.unitkerjaParentId = $satker ";
		else $str_satker = "";
		if($tipeunit != "") $str_tipeunit = " AND tukr.tipeunitId = $tipeunit ";
		else $str_tipeunit = "";
      */
		$sql = $this->GetQueryKeren(
						$this->mSqlQueries['get_count_data_unitkerja'],
						array(

								'%'.$kode.'%',
								'%'.$kode.'%',
								'%'.$unitkerja.'%',
								'%'.$unitkerja.'%',
								 $str_tipeunit));
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

	function GetDataUnitkerjaById($unitkerjaId) {
		$result = $this->Open($this->mSqlQueries['get_data_unitkerja_by_id'], array($unitkerjaId));
	  //$debug = sprintf($this->mSqlQueries['get_data_unitkerja_by_id'], $unitkerjaId);
	  //echo $debug;
		return $result;
	}

	function GetDataUnitkerjaByArrayId($arrUnitkerjaId) {
		$unitkerjaId = implode("', '", $arrUnitkerjaId);
		$result = $this->Open($this->mSqlQueries['get_data_unitkerja_by_array_id'], array($unitkerjaId));
	  //$debug = sprintf($this->mSqlQueries['get_data_unitkerja_by_id'], $unitkerjaId);
	  //echo $debug;
		return $result;
	}

	//untuk combo box
	function GetDataSatker($unitkerjaId = NULL) {
		$sql_params = (empty($unitkerjaId) ? "" : " WHERE unitkerjaId='".$unitkerjaId."'");
		$sql = sprintf($this->mSqlQueries['get_data_satker'],$sql_params);
		//$result = $this->Open($this->mSqlQueries['get_data_satker'], array());
		$result = $this->Open($sql, array());
		return $result;
	}

	function GetDataTipeUnit($unitkerjaId = NULL) {
		$result = $this->Open($this->mSqlQueries['get_data_tipe_unit'], array());
		return $result;
	}

	function GetStatusUnitKerja(){
		return $this->Open($this->mSqlQueries['get_status_unit_kerja'],array());
	}

//===DO==
	function GetGenerateKodeSistem($unitKerjaParent=0)
	{
		$result = $this->Open($this->mSqlQueries['generate_kode_sistem'],
							array(
							$unitKerjaParent,$unitKerjaParent,$unitKerjaParent,
							$unitKerjaParent,$unitKerjaParent,$unitKerjaParent,
							$unitKerjaParent
							));
		return $result[0]['kode'];
	}
	function DoAddUnitkerja($pimpinan, $unitkerjaKode, $unitkerjaNama, $tipeunit, $statusunit, $parentId=0) {

      if($parentId == '' or empty($parentId)) $parentId = '0';
      	$kodeSistem  = $this->GetGenerateKodeSistem($parentId);
		$result = $this->Execute($this->mSqlQueries['do_add_unitkerja'],
						array(
								$unitkerjaKode,
								$kodeSistem,
								$unitkerjaNama,
								$tipeunit,
								$statusunit,
								$parentId,
								$pimpinan));
 	//$debug = sprintf($this->mSqlQueries['do_add_unitkerja'],$unitkerjaKode, $satker, $unitkerjaNama, $tipeunit, $statusunit, $satker, $pimpinan);
	 // echo $debug;

	   //print_r($this->getLastError());
		return $result;
	}

	function DoUpdateUnitkerja($pimpinan, $unitkerjaKode, $unitkerjaNama, $tipeunit,
                $statusunit, $parentId=0, $unitkerjaId) {
      if($parentId == '' or empty($parentId)) $parentId = '0';

      $get_unit_kode_sistem = $this->GetKodeSistem($unitkerjaId);
      if($parentId != $get_unit_kode_sistem['parent_id']){
            $kodeSistem = $this->GetGenerateKodeSistem($parentId);
      } else {
            $kodeSistem = $get_unit_kode_sistem['kode_sistem'];
      }
		$result = $this->Execute($this->mSqlQueries['do_update_unitkerja'],
                                    array(
                                            $unitkerjaKode,
                                            $kodeSistem,
                                            $unitkerjaNama,
                                            $tipeunit,
                                            $statusunit,
                                             $parentId,
                                             $pimpinan,
                                             $unitkerjaId));

	  //$debug = sprintf($this->mSqlQueries['do_update_unitkerja'], $unitkerjaKode, $unitkerjaNama, $tipeunit, $statusunit, $satker, $pimpinan, $unitkerjaId);
	  //echo $debug;
		return $result;
	}

	function DoDeleteUnitkerjaById($unitkerjaId) {
		$result=$this->Execute($this->mSqlQueries['do_delete_unitkerja_by_id'], array($unitkerjaId,$unitkerjaId));

		return $result;
	}
	function DoDeleteUnitkerjaByArrayId($arrUnitkerjaId) {
		$unitkerjaId = implode("', '", $arrUnitkerjaId);
		$result=$this->Execute($this->mSqlQueries['do_delete_unitkerja_by_array_id'], array($unitkerjaId,$unitkerjaId));

		return $result;
	}

   function GetComboUnitKerja(){
      return $this->Open($this->mSqlQueries['get_combo_unit_kerja'],array());
   }

   function cekUnitParent($parentId){
      $total = $this->Open($this->mSqlQueries['cek_unit_parent'],array($parentId));
      return $total[0]['total'];
   }

   function GetKodeSistem($unitId)
   {
        $kode_sistem = $this->Open($this->mSqlQueries['get_kode_sistem'],array($unitId));
        return $kode_sistem[0];
   }
}
?>
