<?php

class AppInputAdjustmentPengeluaran extends Database {

	protected $mSqlFile= 'module/adjustment_pengeluaran/business/appinputadjustmentpengeluaran.sql.php';
	
	function __construct($connectionNumber=0) {
		parent::__construct($connectionNumber);
		//$this->mrDbEngine->debug = 1;
		
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
		return $sql_parsed;
	}
*/
	function GetInformasi($kegiatan_detil_id) {
		$result = $this->Open($this->mSqlQueries['get_informasi'], array($kegiatan_detil_id));
		  if($result[0]['is_unit_kerja'] == '0')
			 $result[0]['is_unit_kerja'] = true;
		  else
			 $result[0]['is_unit_kerja'] = false;
		//echo "<pre style='font-size:12px;'>";
		//print_r($result);
		//echo "</pre>";
		
		return $result[0];
	}
		
	function GetData($kegiatan_detil_id) {
		$result = $this->Open($this->mSqlQueries['get_data'], array($kegiatan_detil_id));
      //echo $this->GetLastError();exit;
		
		return $result;
	}

	function GetCountData($kegiatan_detil_id) {
		$result = $this->Open($this->mSqlQueries['get_count_data'], array($kegiatan_detil_id));
		if (!$result) {
			return 0;
		} else {
			return $result[0]['total'];
		}
	}

   function GetHistoryIsExist($id) {
    $id = @implode("', '", $id);
		$result = $this->Open($this->mSqlQueries['get_history_is_exist'], array($id));
		return $result[0]['jml'];
   }

	function DoUpdateDetilApproval($formula, $nominal, $satuan, $id) {
		for($i=0;$i<sizeof($id);$i++) {
			$nominal[$id[$i]] = $nominal[$id[$i]] / $formula[$id[$i]];
			$result = $this->Execute($this->mSqlQueries['do_update_detil_approval'], array($nominal[$id[$i]], $satuan[$id[$i]], $id[$i]));
         if (!$result) break;
		}
		return $result;
	}
 
	function DoInputHistoryPengeluaran($id) {
    $id = @implode("', '", $id);
		$result = $this->Execute($this->mSqlQueries['do_add_history'], array($id));
		//$debug = sprintf($this->mSqlQueries['do_add_history'], $status, $id);
	    //echo $debug;
		return $result;
	}

}
?>
