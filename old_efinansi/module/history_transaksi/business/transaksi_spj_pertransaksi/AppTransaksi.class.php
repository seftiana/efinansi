<?php

class AppTransaksi extends Database {

	protected $mSqlFile= 'module/transaksi_spj/business/apptransaksi.sql.php';
	
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
		$result = $this->Open($this->mSqlQueries['get_combo_tipe_transaksi'], array($_SESSION['username']));
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
      if(empty($result))
         $result = $this->Open($this->mSqlQueries['get_transaksi_mak_untuk_pencairan'], array($transId));
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
      $query = sprintf($this->mSqlQueries['do_add_transaksi'], $arrData['transTtId'], 
            $arrData['transTransjenId'], 
            $arrData['transUnitkerjaId'], 
            $arrData['transReferensi'], 
            $arrData['transUserId'], 
            $arrData['transTanggal'], 
            $arrData['transDueDate'], 
            $arrData['transCatatan'], 
            $arrData['transNilai'], 
            $arrData['transPenanggungJawabNama'], 
            $arrData['transIsJurnal']);
      //echo $query;
      $insertId = $this->LastInsertId();
      $this->DoAddLog("Tambah Transaksi", $query);
      return $insertId;
   }
   
   function DoAddTransaksiDetilSPJ ($transId, $mak)
   {
      list($kegdetId, $pengrealId) = explode("|", $mak);
      $result = $this->Execute($this->mSqlQueries['do_add_transaksi_detil_spj'], array($transId, $kegdetId));
      
      return $result;
   }

   function DoAddTransaksiDetilPengembalianAnggaran ($transId, $mak)
   {
      list($kegdetId, $pengrealId) = explode("|", $mak);
      $result = $this->Execute($this->mSqlQueries['do_add_transaksi_detil_pengembalian_anggaran'], array($transId, $kegdetId, $pengrealId));
      
      return $result;
   }
   
   function DoAddTransaksiDetilAnggaran($transId, $mak) {
      $arrMak = explode("|", $mak); #print_r($arrMak); exit;
      $kegdetId = $arrMak[0];
      $pengrealId = $arrMak[1];
      if(!empty($arrMak[1]))
         $result = $this->Execute($this->mSqlQueries['do_add_transaksi_detil_anggaran'],             array($transId, $kegdetId, $pengrealId));
      else
         $result = $this->Execute($this->mSqlQueries['do_add_transaksi_detil_anggaran_penerimaan'], array($transId, $mak));
      /*
      echo sprintf($this->mSqlQueries['do_add_transaksi_detil_anggaran'], 
            $transId, $kegdetId, $pengrealId);*/
      return $result;
   }
   
   //tambahan untuk insert transaksi_detail pencaian
   function DoAddTransaksiDetilPencairan($transId, $mak) {
      $arrMak = explode("|", $mak); #print_r($arrMak); exit;
      $kegdetId = $arrMak[0];
      $pengrealId = $arrMak[1];
      $result = $this->Execute($this->mSqlQueries['do_add_transaksi_detil_pencairan'],             array($transId, $kegdetId, $pengrealId));
      /*
      echo sprintf($this->mSqlQueries['do_add_transaksi_detil_anggaran'], 
            $transId, $kegdetId, $pengrealId);*/
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
		$result = $this->Execute($this->mSqlQueries['do_add_pembukuan'], array($userId, $transId));
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
   
   function DoAddPembukuanDetilKodeJurnal ($idPembukuan, $COA)
   {
      foreach ($COA as $idCOA=>$value)
      {
         if ($value['nominal'] <= 0) continue;
         $arg = array($idPembukuan, $idCOA, $value['nominal'], ($value['typeRekening'] == 'debet') ? 'D' : 'K');
         $result = $this->Execute($this->mSqlQueries['do_add_pembukuan_detil_jurnal_kode'], $arg);
         if (!$result) break;
      }
      return $result;
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
      $query = sprintf($this->mSqlQueries['do_update_transaksi'], 
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
            $arrData['transId']);
      //echo $query;
      $this->DoAddLog("Update Transaksi", $query);
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
//MULAI CETAK DATA
//FORM CETAK
   function GetDataFormCetak($dataId) {
		$result = $this->Open($this->mSqlQueries['get_data_form_cetak'], array($dataId));
      //echo sprintf($this->mSqlQueries['get_transaksi_mak'], $transId);
		return $result[0];
   }

   function GetComboTahunAnggaran() {
		$result = $this->Open($this->mSqlQueries['get_combo_tahun_anggaran'], array());
		return $result;
   }

   function GetComboTahunAnggaranAktif() {
		$result = $this->Open($this->mSqlQueries['get_combo_tahun_anggaran_aktif'], array());
		return $result[0];
   }
   //LOGGER LOGGER LOGGER

   function DoAddLog($keterangan, $query) {
      $userId = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
      $ip = $_SERVER['REMOTE_ADDR'];
		$result = $this->Execute($this->mSqlQueries['do_add_log'], array($userId, $ip, $keterangan));
      $this->DoAddLogDetil($this->LastInsertId(), $query);
      return $result;
   }

   function DoAddLogDetil($id, $query) {
      $result = $this->Execute($this->mSqlQueries['do_add_log_detil'], array($id, addslashes($query)));
      return $result;
   }
   
   #tambahan untuk update status transaksi pencairan anggaran (pengajuan_realisaisi)
   function DoUpdateStatusTransaksiDiPengajuanRealisasi($peng_real_id) {
      $result = $this->Execute(
         $this->mSqlQueries['update_status_transaksi_di_pengajuan_realisasi'], 
            array($peng_real_id)
         );
      return $result;
   }
   
   #tambahan untuk cetak bukti transaksi
   function GetJabatanNama($key) {
      $result = $this->Open($this->mSqlQueries['get_jabatan'], array('%'.$key.'%'));
      return $result;
   }
   
   function GetJabatan($jab) {
      $result = $this->Open($this->mSqlQueries['get_nama_pejabat'], array($jab));
      return $result[0]['nama'];
   }
   
   #tambahan untuk otomasi count bukti transaksi
   function CountBuktiTrans($tipe_trans, $bulan, $unit_kerja) {
      switch($tipe_trans){
      	case "1":
      	case "5":
            $kode = "BKM";
            break;
      	case "2":
      	case "4":
            $kode = "BKK";
            break;
			case "3":
            $kode = "BM";
            break;
         default:
            $kode = "SPJ";
            break;
		}
      
      $result = $this->Open($this->mSqlQueries['count_bukti'], array("$kode%", $bulan, $unit_kerja));
      if (empty($result)) return array('count_trans'=>1);
      for ($i = count($result) - 1; $i >= 0; $i--) $tmp[] = $result[$i]['transReferensi'];
      natsort($tmp); end($tmp);
      return array('count_trans'=>preg_replace('/^[a-z]+(\d+)[^\d].*$/i', '\1', current($tmp)) + 1);
   }
}
?>
