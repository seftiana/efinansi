<?php

class AppDetilUsulanKegiatan extends Database {

	protected $mSqlFile= 'module/usulan_kegiatan/business/appdetilusulankegiatan.sql.php';

	function __construct($connectionNumber=0) {
		parent::__construct($connectionNumber);
		//$this->mrDbEngine->debug = 1;
		//$this->SetDebugOn();	
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
		return $sql_parsed;
	}


	function GetDataDetilUsulanKegiatan($offset, $limit, $kegiatanId='', $subprogram='', $jenis='') {

		if($subprogram != "") {
			$str_subprogram = " AND kegrefSubProgId=$subprogram ";
		} else {
			$str_subprogram = "";
		}
		if($jenis != "" && $jenis!="all") {
			$str_jenis= " AND jeniskegId=$jenis ";
		} else {
			$str_jenis= "";
		}
		$sql = $this->GetQueryKeren($this->mSqlQueries['get_data_detil_usulan_kegiatan'], array($kegiatanId, $str_subprogram, $str_jenis, $offset, $limit));
		//echo $sql;
		return $this->Open($sql, array());
	}
/*
	function GetCountDataDetilUsulanKegiatan($kegiatanId='', $subprogram='', $jenis='') {
		if($subprogram != "") {
			$str_subprogram = " AND kegrefSubProgId=$subprogram ";
		} else {
			$str_subprogram = "";
		}
		if($jenis != "" && $jenis!="all") {
			$str_jenis= " AND jeniskegId=$jenis ";
		} else {
			$str_jenis= "";
		}
		$sql = $this->GetQueryKeren($this->mSqlQueries['get_count_data_detil_usulan_kegiatan'], array($kegiatanId, $str_subprogram, $str_jenis));
		//echo $sql;
		$result = $this->Open($sql, array());
		if (!$result) {
			return 0;
		} else {
			return $result[0]['total'];
		}
	}
*/

	function GetCountDataDetilUsulanKegiatan() {
		$result = $this->Open($this->mSqlQueries['get_count_data_detil_usulan_kegiatan'], array());
		if (!$result) {
			return 0;
		} else {
			return $result[0]['total'];
		}
	}
	function GetDataUsulanKegiatanById($id) {

      $ret = $this->Open($this->mSqlQueries['get_data_usulan_kegiatan_by_id'], array($id));

      //echo sprintf($this->mSqlQueries['get_data_usulan_kegiatan_by_id'], $id);
	  return $ret[0];
	}

	function GetDataUsulanKegiatanByProgramId($id) {

      $ret = $this->Open($this->mSqlQueries['get_data_usulan_kegiatan_by_program_id'], array($id));
	  return $ret[0];
	}

   function GetDataDetilUsulanKegiatanById($id) {
      $ret = $this->Open($this->mSqlQueries['get_data_detil_usulan_kegiatan_by_id'], array($id));
      //echo sprintf($this->mSqlQueries['get_data_detil_usulan_kegiatan_by_id'], $id);
	  return $ret[0];
   }

	function DoAddDetilUsulanKegiatan($kegiatanId, $kegiatanref, $deskripsi, $catatan, $output='', $mulai='', $selesai='',$prioritas,$mastuk, $mastk, $keltuk, $keltk, $ikk, $iku,$output_rkakl='',$tupoksi_id='') {
		$id_user		= Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
		$result = $this->Execute($this->mSqlQueries['do_add_detil_usulan_kegiatan'], array($kegiatanId, $kegiatanref, $deskripsi, $catatan, $output, $mulai, $selesai,$prioritas, $mastuk, $mastk, $keltuk, $keltk, empty($ikk)?NULL:$ikk, empty($iku)?NULL:$iku, empty($output_rkakl)? NULL : $output_rkakl, 
		empty($tupoksi_id) ? NULL :$tupoksi_id,	$id_user));
		
      //echo sprintf($this->mSqlQueries['do_add_detil_usulan_kegiatan'], $kegiatanId, $kegiatanref, $deskripsi, $catatan, $output, $mulai, $selesai);
      /**
      $result = sprintf($this->mSqlQueries['do_add_detil_usulan_kegiatan'],$kegiatanId, $kegiatanref, $deskripsi, $catatan, $output, $mulai, $selesai,$prioritas, $mastuk, $mastk, $keltuk, $keltk, empty($ikk)?NULL:$ikk, empty($iku)?NULL:$iku, empty($output_rkakl)? NULL : $output_rkakl, 
		empty($tupoksi_id) ? NULL :$tupoksi_id,	$id_user);
		*/
		return $result;
	}

	function DoUpdateDetilUsulanKegiatan($kegiatanId, $kegiatanref, $deskripsi, $catatan, $output='', $mulai='', $selesai='',$prioritas, $mastuk, $mastk, $keltuk, $keltk, $ikk, $iku,$output_rkakl='',$tupoksi_id, $id) {
		$id_user		= Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
		$result = $this->Execute($this->mSqlQueries['do_update_detil_usulan_kegiatan'], array($kegiatanId, $kegiatanref, $deskripsi, $catatan, $output, $mulai, $selesai,$prioritas, $mastuk, $mastk, $keltuk, $keltk, empty($ikk)?NULL:$ikk, empty($iku)?NULL:$iku, empty($output_rkakl)?NULL:$output_rkakl,
		empty($tupoksi_id) ? NULL :$tupoksi_id,
		$id_user, $id));
      //echo sprintf($this->mSqlQueries['do_update_detil_usulan_kegiatan'], $kegiatanId, $kegiatanref, $deskripsi, $catatan, $output, $mulai, $selesai, $id);
		return $result;
	}

	function DoDeleteDetilUsulanKegiatanById($kegiatan_detil_id) {
		$result=$this->Execute($this->mSqlQueries['do_delete_detil_usulan_kegiatan_by_id'], array($kegiatan_detil_id));
		return $result;
	}

	function DoDeleteDetilUsulanKegiatanByArrayId($arrKegiatanDetilId) {
		$strKegiatanDetilId = @implode("', '", $arrKegiatanDetilId);
		$result=$this->Execute($this->mSqlQueries['do_delete_detil_usulan_kegiatan_by_array_id'], array($strKegiatanDetilId));
		return $result;
	}

	//get combo jenis kegiatan
	function GetComboJenisKegiatan() {
		$result = $this->Open($this->mSqlQueries['get_combo_jenis_kegiatan'], array());
		return $result;
	}

	function GetComboPrioritas() {
		$result = $this->Open($this->mSqlQueries['get_combo_prioritas'], array());
		return $result;
	}
/*
   function GetMinMaxWaktuPelaksanaan() {
		$result = $this->Open($this->mSqlQueries['get_min_max_waktu_pelaksanaan'], array());
		return $result[0];
   }
   */
}
?>
