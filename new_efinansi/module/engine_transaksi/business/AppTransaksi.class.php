<?php
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'module/generate_number/business/GenerateNumber.class.php';

class AppTransaksi extends Database
{

   protected $mSqlFile = 'module/engine_transaksi/business/apptransaksi.sql.php';
   function __construct($connectionNumber = 0)
   {
      parent::__construct($connectionNumber);
      //$this->SetDebugOn();
   }

   function GetComboJenisTransaksi() {
		$result = $this->Open($this->mSqlQueries['get_combo_jenis_transaksi'], array());
		return $result;
   }

   public function TransaksiJurnal($data, $id_transaksi, $user_id, $keterangan)
   {
     $this->StartTrans();
	  $ok = $this->Execute($this->mSqlQueries['do_add_pembukuan_referensi'], array($id_transaksi,
	                                                                               $user_id,
																				                     date('Y-m-d'),
	                                                                               $keterangan																				   
	                                                                              ));
	  $sql[] = sprintf($this->mSqlQueries['do_add_pembukuan_referensi'], $id_transaksi,
	                                                                     $user_id,
																		                  date('Y-m-d'),
	                                                                     $keterangan);	
	  	  
	  $pembukuan_id=$this->Insert_ID();
	  
	  
	  if($ok) {
	     if (!empty($data['kredit'])){
	     foreach ($data['kredit'] as $val){
	     $ok = $this->Execute($this->mSqlQueries['do_add_pembukuan_detail'], array ($pembukuan_id,
		                             $val['id'],
		     						         $val['nilai'],
									         $val['keterangan'],
									         'K' 
			                         ));
	     }}
     }
	  
	  if($ok){	     		 
		 if(!empty($data['debet'])) {		    
		    foreach($data['debet'] as $val){			   
			   $ok = $this->Execute($this->mSqlQueries['do_add_pembukuan_detail'], array($pembukuan_id,
			                               $val['id'],
										   $val['nilai'],
										   $val['keterangan'],
										   'D'
			                              ));
			}
		 } 
	  } 

	  $this->EndTrans($ok);	  
	  
	  return $ok;
   }

   function GetComboTipeTransaksi() {
		$result = $this->Open($this->mSqlQueries['get_combo_tipe_transaksi'], array($_SESSION['username']));
		return $result;
   }

   function CekTransaksi($no)
   {
      $result = $this->Open($this->mSqlQueries['cek_transaksi'], array(
         $no
      ));

      if ($result[0]['total'] > 0)
      return false;
      else
      return true;
   }

   function GetTransaksiById($id)
   {
      $result = $this->Open($this->mSqlQueries['get_transaksi_by_id'], array(
         $id
      ));

      return $result[0];
   }

   function DoAddTransaksi($arrData)
   {
      $result = $this->Execute($this->mSqlQueries['do_add_transaksi'], array(
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
         $arrData['transIsJurnal']
      ));
      $query = sprintf($this->mSqlQueries['do_add_transaksi'], $arrData['transTtId'], $arrData['transTransjenId'], $arrData['transUnitkerjaId'], $arrData['transReferensi'], $arrData['transUserId'], $arrData['transTanggal'], $arrData['transDueDate'], $arrData['transCatatan'], $arrData['transNilai'], $arrData['transPenanggungJawabNama'], $arrData['transIsJurnal']);

      //echo $query;
      $insertId = $this->LastInsertId();
      $this->DoAddLog("Tambah Transaksi", $query);

      return $insertId;
   }

   function DoAddTransaksiFile($transId, $arrNama, $path)
   {
      $arrInsert = array();

      for ($i = 0;$i < sizeof($arrNama);$i++)
      {
         $arrInsert[] = "('" . $transId . "', '" . $arrNama[$i] . "', '" . $path . "')";
      }
      $strInsert = implode(", ", $arrInsert);
      $sql = stripslashes($this->GetQueryKeren($this->mSqlQueries['do_add_transaksi_file'], array(
         $strInsert
      )));

      //echo $sql;
      $result = $this->Execute($sql, array());

      return $result;
   }
   function DoAddTransaksiInvoice($transId, $arrInvoice)
   {

      for ($i = 0;$i < sizeof($arrInvoice);$i++)
      {
         $arrInsert[] = "('" . $transId . "', '" . $arrInvoice[$i] . "')";
      }
      $strInsert = implode(", ", $arrInsert);
      $sql = stripslashes($this->GetQueryKeren($this->mSqlQueries['do_add_transaksi_invoice'], array(
         $strInsert
      )));

      //echo $sql;
      $result = $this->Execute($sql, array());

      return $result;
   }
   function DoAddPembukuan($transId, $userId)
   {
      $result = $this->Execute($this->mSqlQueries['do_add_pembukuan'], array(
         $transId,
         $userId
      ));

      return $this->LastInsertId();
   }
   function DoAddPembukuanDetil($idPembukuan, $nilai, $arrSkenarioId = array())
   {
      $strSkenarioId = implode("', '", $arrSkenarioId);
      $result_debet = $this->Execute($this->mSqlQueries['do_add_pembukuan_detil_debet'], array(
         $idPembukuan,
         $nilai,
         $strSkenarioId
      ));
      $result_kredit = $this->Execute($this->mSqlQueries['do_add_pembukuan_detil_kredit'], array(
         $idPembukuan,
         $nilai,
         $strSkenarioId
      ));

      return $result_debet;
   }

   function DoUpdateTransaksi($arrData)
   {
      $result = $this->Execute($this->mSqlQueries['do_update_transaksi'], array(
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
         $arrData['transId']
      ));
      $query = sprintf($this->mSqlQueries['do_update_transaksi'], $arrData['transTtId'], $arrData['transTransjenId'], $arrData['transUnitkerjaId'], $arrData['transReferensi'], $arrData['transUserId'], $arrData['transTanggal'], $arrData['transDueDate'], $arrData['transCatatan'], $arrData['transNilai'], $arrData['transPenanggungJawabNama'], $arrData['transIsJurnal'], $arrData['transId']);

      //echo $query;
      $this->DoAddLog("Update Transaksi", $query);

      return $result;
   }
   function DoDeleteDataById($arrDataId)
   {
      $dataId = implode("', '", $arrDataId);
      $result = $this->Execute($this->mSqlQueries['do_delete_data_by_array_id'], array(
         $dataId
      ));

      return $result;
   }

   function DoAddLog($keterangan, $query)
   {
      $userId = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
      $ip = $_SERVER['REMOTE_ADDR'];
      $result = $this->Execute($this->mSqlQueries['do_add_log'], array(
         $userId,
         $ip,
         $keterangan
      ));
      $this->DoAddLogDetil($this->LastInsertId() , $query);

      return $result;
   }
   function DoAddLogDetil($id, $query)
   {
      $result = $this->Execute($this->mSqlQueries['do_add_log_detil'], array(
         $id,
         addslashes($query)
      ));

      return $result;
   }

   //untuk autogenerate yg baru--
   function AutoGenerate($code)
   {
      $generateNumber = new GenerateNumber();

      return $generateNumber->GetGenerateNumber($code);
   }
}
?>