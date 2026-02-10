<?php

/**
 * berdasarkan dari modul engine transaksi
 * @package transaksi_spj_pertransaksi
 * @since 27 Januari 2012
 * @access public
 * @copyright 2012 Gamatechno
 */
 
class AppTransaksiDua extends Database
{

   protected $mSqlFile = 'module/history_transaksi_spj_pertransaksi/business/apptransaksidua.sql.php';
   protected $mLastTransId;
   
   function __construct($connectionNumber = 0)
   {
      parent::__construct($connectionNumber);
      //$this->SetDebugOn();
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
 
   
   /**
    * 
   public function TransaksiJurnal($data, $id_transaksi, $user_id, $keterangan)
   {
     $this->StartTrans();
	  $ok = $this->Execute($this->mSqlQueries['do_add_pembukuan_referensi'], 
	  													array(
														  		$id_transaksi,
														  		$user_id,
												  				$keterangan
												  		     ));
		
	  //$sql[] = sprintf($this->mSqlQueries['do_add_pembukuan_referensi'], $id_transaksi,
	  //                                                                   $user_id,
	  //                                                                   $keterangan);	
	  	  
	  //$pembukuan_id=$this->Insert_ID();
	  
	  $pembukuan_id = $this->LastInsertId();
	  if($ok) {
	     if (!empty($data['kredit'])){
  
	     $ok = $this->Execute($this->mSqlQueries['do_add_pembukuan_detail'], array (
		 									$pembukuan_id,
		                             		 $data['kredit']['id'],
		     						         $data['kredit']['nilai'],
									         '',//$val['keterangan'],
									         'K' 
			                         ));
        
        //$sql[]=sprintf($this->mSqlQueries['do_add_pembukuan_detail'],
		// 									$pembukuan_id,
		//                             		 $data['kredit']['id'],
		//     						         $data['kredit']['nilai'],
		//							         '',//$val['keterangan'],
		//							         'K' 
		//	                         );         

		 }
     }
	  
	  if($ok){	     		 
		 if(!empty($data['debet']['tambah'])) {
		 	for($i = 0; $i < count($data['debet']['tambah']['id']); $i++){
		    			   
			   $ok = $this->Execute($this->mSqlQueries['do_add_pembukuan_detail'], array(
			            				   $pembukuan_id,
                     	                   $data['debet']['tambah']['id'][$i],
                     	                   $data['debet']['tambah']['nilai'][$i],
										   '',//$val['keterangan'],
										   'D'
			                              ));
            
             //$sql[]=sprintf($this->mSqlQueries['do_add_pembukuan_detail'],
		 	//								 $pembukuan_id,
		     //                        		 $data['debet']['tambah']['id'][$i],
             //        	                   	 $data['debet']['tambah']['nilai'][$i],
			//						         '',//$val['keterangan'],
			//						         'D' 
			 //                        );
            
			}
		 } 
	  } 

	  $this->EndTrans($ok);	
	  //$ok = $sql;
	  //$ok = $data['debet']['tambah'];
	  return $ok;
   }
   *
   *
   */
   
    
   /**
    * fungsi DoAddTransaki
    * proses penyimpanan data modul transaksi spj_pertransaksi
    * pencatatan ke tabel transaksi
    * pencatatan ke tabel finansi_transaki_ref_transaksi untuk mencatat referensi transaksi
    * pencatatan ke jurnal ( tabel pembukuan referensi dan tabel pembukuan detail )
    */
   public function DoAddTransaksi($arrData)
   {
   		/**
   		 * begin transaction query
   		 */
   	   	$this->StartTrans();
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
         														$arrData['transIsJurnal']
															));
      	$this->mLastTransId = $this->LastInsertId();
   		/**
   		 * catat ref transaksi
   		 */
   		if($result){
   			$result = $this->Execute($this->mSqlQueries['do_add_ref_transaksi'], 
			   												array(
															   		$this->mLastTransId,
															   		$arrData['refTransaksiId']
															   	));
   		}
   		
   		/**
   		 * catat ke jurnal
   		 */
   		if($result){
	  	$result = $this->Execute($this->mSqlQueries['do_add_pembukuan_referensi'], 
	  													array(
														  		$this->mLastTransId,
														  		$arrData['transUserId'],
												  				$arrData['transReferensi']
												  		     ));
		/**
	  		$sql[] = sprintf($this->mSqlQueries['do_add_pembukuan_referensi'], $id_transaksi,
	                                                                     $user_id,
	                                                                     $keterangan);	
	  	*/
	  	$lastPembukuanId = $this->LastInsertId();
	  	}
	  	$data = $arrData['arrCoa'];
  		if($result) {
    		if (!empty($data['kredit'])){	
	     		$result = $this->Execute($this->mSqlQueries['do_add_pembukuan_detail'], 
														array (
		 														$lastPembukuanId,
		                             		 					$data['kredit']['id'],
		     						         					$data['kredit']['nilai'],
									         					'',//$val['keterangan'],
									         					'K' 
                												));
        	/**
        		$sql[]=sprintf($this->mSqlQueries['do_add_pembukuan_detail'],
		 									$pembukuan_id,
		                             		 $data['kredit']['id'],
		     						         $data['kredit']['nilai'],
									         '',//$val['keterangan'],
									         'K' 
			                         ); 
		 	*/         
		 	}
   		}
	  
	  if($result){	     		 
		 if(!empty($data['debet']['tambah'])) {
		 	for($i = 0; $i < count($data['debet']['tambah']['id']); $i++){
		    //foreach($data['debet']['tambah'] as $val){			   
			   $ok = $this->Execute($this->mSqlQueries['do_add_pembukuan_detail'], 
			   											array(
			            				   						$lastPembukuanId,
                     	                   						$data['debet']['tambah']['id'][$i],
	                   											$data['debet']['tambah']['nilai'][$i],
										   						'',//$val['keterangan'],
										   						'D'
             												));
             /**
             $sql[]=sprintf($this->mSqlQueries['do_add_pembukuan_detail'],
		 									 $pembukuan_id,
		                             		 $data['debet']['tambah']['id'][$i],
                     	                   	 $data['debet']['tambah']['nilai'][$i],
									         '',//$val['keterangan'],
									         'D' 
			                         );
             */
			}
		 } 
	  } 

	  /**
	   * end transaction query
	   */	
	  $this->EndTrans($result);	

      $this->DoAddLog("Tambah Transaksi", $query);

      return $result;
   }
	
   /**
    * fungsi GetLastTransId
	* untuk mendapatkan id transaksi yang terakhir dari tabel transaksi
	* di dapat dari proses DoAddTransaksi
    * @since 16 Februari 2012
    * @access public
    * @return number
	*/	
   public function GetLastTransId()
   {
   		return (isset($this->mLastTransId) ? $this->mLastTransId : ''); 
   }
   
   function DoAddTransaksiFile($transId, $arrNama, $path)
   {
      $arrInsert = array();

      for ($i = 0;$i < sizeof($arrNama);$i++)
      {
        	$arrInsert[] = "('" . $transId . "', '" . $arrNama[$i] . "', '" . $path . "')";
      }
      $strInsert = implode(", ", $arrInsert);
	   $sql = sprintf($this->mSqlQueries['do_add_transaksi_file'], $strInsert);
      //echo $sql;
      $result = $this->Execute($sql, array());
      return $result;
   }
   function DoAddTransaksiInvoice($transId, $arrInvoice)
   {
		$arrInsert = array();
      for ($i = 0;$i < sizeof($arrInvoice);$i++)
      {
         $arrInsert[] = "('" . $transId . "', '" . $arrInvoice[$i] . "')";
      }
      $strInsert = implode(", ", $arrInsert);
      $sql = sprintf($this->mSqlQueries['do_add_transaksi_invoice'],$strInsert);
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
      /**
      $query = sprintf( $this->mSqlQueries['do_update_transaksi'], 
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

      echo $query;
      */
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
   
   /**
    * fungsi GetLastInsertTransId
    * untuk mendapatkan trasaksi id yang baru saja di simpan
    * @since 16 Januari 2012
    * @access public
    * @return number
    */
   function GetLastInsertTransId()
   {
   		$result = $this->Open($this->mSqlQueries['get_last_insert_trans_id'],array());
   		return $result[0]['lastTransId'];
   }

   /**
    * fungsi GetDataJurnal
    * untuk mendapatkan data jurnal
    * @since 26 Januari 2012
    */
   function GetDataJurnal($transId)
   {
   		$result = $this->Open($this->mSqlQueries['data_jurnal'],array($transId));
   		return $result;
   }
}