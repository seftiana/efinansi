<?php

class AppTransaksi extends Database {

	protected $mSqlFile= 'module/transaksi_kode_jurnal_pengeluaran/business/appcetaktransaksi.sql.php';
	
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
		return $sql_parsed;
	}

   function CekTransaksi($kkb) {
		$result = $this->Open($this->mSqlQueries['cek_transaksi'], array($kkb));
      //print_r($result);
      if($result[0]['total'] > 0) return false;
      else return true;
   }

   function GetComboJenisTransaksi() {
		$result = $this->Open($this->mSqlQueries['get_combo_jenis_transaksi'], array());
		return $result;
   }

   function GetComboTipeTransaksi() {
		$result = $this->Open($this->mSqlQueries['get_combo_tipe_transaksi'], array());
		return $result;
   }

   function GetTransaksiById($id) {
		$result = $this->Open($this->mSqlQueries['get_transaksi_by_id'], array($id));
		return $result[0];
   }

	function GetTransaksiFile($transId) {
		$result = $this->Open($this->mSqlQueries['get_transaksi_file'], array($transId));
		return $result;
	}

	function GetTransaksiInvoice($transId) {
		$result = $this->Open($this->mSqlQueries['get_transaksi_invoice'], array($transId));
		return $result;
	}

   function GetTransaksiMAK($transId) {
		$result = $this->Open($this->mSqlQueries['get_transaksi_mak'], array($transId));
      //echo sprintf($this->mSqlQueries['get_transaksi_mak'], $transId);
		return $result[0];
   }

   function DoAddTransaksi($arrData) {
		$result = $this->Execute($this->mSqlQueries['do_add_transaksi'], 
         array(
            $arrData['transTtId'], 
            $arrData['transTransjenId'], 
            $arrData['transUnitkerjaId'], 
            $arrData['transReferensi'], 
            $arrData['transUserId'], 
            $arrData['transTanggal'], 
            $arrData['transDueDate'], 
            $arrData['transCatatan'], 
            $arrData['transNilai'], 
            $arrData['transPenanggungJawabNama'], 
            $arrData['transIsJurnal'])
         );
      //echo sprintf($this->mSqlQueries['do_add_transaksi'], $arrData['transReferensi'], $arrData['transUserId'], $arrData['transTanggal'], $arrData['transDueDate'], $arrData['transCatatan'], $arrData['transNilai'], $arrData['transPenanggungJawabNama'], $arrData['transIsJurnal']);
      return $this->LastInsertId();
   }

   function DoAddTransaksiDetilAnggaran($transId, $mak) {
      $arrMak = explode("|", $mak);
      $kegdetId = $arrMak[0];
      $pengrealId = $arrMak[1];
		$result = $this->Execute(
         $this->mSqlQueries['do_add_transaksi_detil_anggaran'], 
            array($transId, $kegdetId, $pengrealId)
         );
      return $result;
   }

   function DoAddTransaksiFile($transId, $arrNama, $path) {
      $arrInsert = array();
      for($i=0;$i<sizeof($arrNama);$i++) {
         $arrInsert[]= "('".$transId."', '".$arrNama[$i]."', '".$path."')";
      }
      $strInsert = implode(", ", $arrInsert);
      $sql = stripslashes($this->GetQueryKeren($this->mSqlQueries['do_add_transaksi_file'], array($strInsert)));
      //echo $sql;
		$result = $this->Execute($sql, array());
      return $result;
   }

   function DoAddTransaksiInvoice($transId, $arrInvoice) {
      for($i=0;$i<sizeof($arrInvoice);$i++) {
         $arrInsert[]= "('".$transId."', '".$arrInvoice[$i]."')";
      }
      $strInsert = implode(", ", $arrInsert);
      $sql = stripslashes($this->GetQueryKeren($this->mSqlQueries['do_add_transaksi_invoice'], array($strInsert)));
      //echo $sql;
		$result = $this->Execute($sql, array());
      return $result;
   }

   function DoAddPembukuan($transId, $userId) {
		$result = $this->Execute($this->mSqlQueries['do_add_pembukuan'], array($transId, $userId));
      //echo sprintf($this->mSqlQueries['do_add_pembukuan'], $transId, $userId);
      return $this->LastInsertId();
   }

   function DoAddPembukuanDetil($idPembukuan, $nilai, $arrSkenarioId=array()) {
      $strSkenarioId = implode("', '", $arrSkenarioId);
      $sql_debet = stripslashes($this->GetQueryKeren($this->mSqlQueries['do_add_pembukuan_detil_debet'], array($idPembukuan, $nilai, $strSkenarioId)));

      $sql_kredit = stripslashes($this->GetQueryKeren($this->mSqlQueries['do_add_pembukuan_detil_kredit'], array($idPembukuan, $nilai, $strSkenarioId)));
      //echo $sql_debet;
      //echo $sql_kredit;
		$result_debet = $this->Execute($sql_debet, array());
		$result_kredit = $this->Execute($sql_kredit, array());
      return $result_debet;
   }
//MULAI EDIT DATA


   function CekTransaksiUpdate($kkb, $transId) {
		$result = $this->Open($this->mSqlQueries['cek_transaksi_update'], array($kkb, $transId));
      //echo sprintf($this->mSqlQueries['cek_transaksi_update'], $kkb, $transId);
      //print_r($result);
      if($result[0]['total'] > 0) return true;
      else return false;
   }

   function DoUpdateTransaksi($arrData) {
      //echo sprintf($this->mSqlQueries['do_update_transaksi'], $arrData['transTtId'],  $arrData['transTransjenId'],  $arrData['transUnitkerjaId'],  $arrData['transReferensi'],  $arrData['transUserId'], $arrData['transTanggal'], $arrData['transDueDate'], $arrData['transCatatan'], $arrData['transNilai'], $arrData['transPenanggungJawabNama'], $arrData['transIsJurnal'],$arrData['transId']);
		$result = $this->Execute($this->mSqlQueries['do_update_transaksi'], 
         array(
            $arrData['transTtId'], 
            $arrData['transTransjenId'], 
            $arrData['transUnitkerjaId'], 
            $arrData['transReferensi'], 
            $arrData['transUserId'], 
            $arrData['transTanggal'], 
            $arrData['transDueDate'], 
            $arrData['transCatatan'], 
            $arrData['transNilai'], 
            $arrData['transPenanggungJawabNama'], 
            $arrData['transIsJurnal'],
            $arrData['transId'])
         );
      return $result;
   }

   function DoUpdateTransaksiDetilAnggaran($transId, $mak) {
      $arrMak = explode("|", $mak);
      $kegdetId = $arrMak[0];
      $pengrealId = $arrMak[1];
		$result = $this->Execute(
         $this->mSqlQueries['do_update_transaksi_detil_anggaran'], 
            array($transId, $kegdetId, $pengrealId)
         );
      //echo sprintf($this->mSqlQueries['do_update_transaksi_detil_anggaran'], $transId, $kegdetId, $pengrealId);
      return $result;
   }

   function DoDeleteTransaksiDetilAnggaran($makId) {
		$result = $this->Execute(
         $this->mSqlQueries['do_delete_transaksi_detil_anggaran'], 
            array($makId)
         );
      //echo sprintf($this->mSqlQueries['do_delete_transaksi_detil_anggaran'], $makId);
      return $result;
   }

   function DoDeleteTransaksiInvoice($arrDataId) {
		$dataId = implode("', '", $arrDataId);
		$result = $this->Execute($this->mSqlQueries['do_delete_transaksi_invoice'], array($dataId));
      //echo sprintf($this->mSqlQueries['do_delete_transaksi_invoice'], $dataId);
      return $result;
   }

   function DoDeleteTransaksiFile($arrDataId) {
		$dataId = implode("', '", $arrDataId);
		$result = $this->Execute($this->mSqlQueries['do_delete_transaksi_file'], array($dataId));
      //echo sprintf($this->mSqlQueries['do_delete_transaksi_file'], $dataId);
      return $result;
   }
//SELESAI EDIT DATA


	function DoDeleteDataByArrayId($arrDataId) {
		$dataId = implode("', '", $arrDataId);
		$result = $this->Execute($this->mSqlQueries['do_delete_data_by_array_id'], array($dataId));
		return $result;
	}
	
	function DoDeleteDataById($dataId) {
		$result = $this->Execute($this->mSqlQueries['do_delete_data_by_id'], array($dataId));
		return $result;
	}


}
?>
