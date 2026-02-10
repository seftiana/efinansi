<?php
/*
	@ClassName : Service
	@Copyright : PT Gamatechno Indonesia
	@Analyzed By : Dyan Galih
	@Author By : Didi Zuliansyah
	@Version : 01
	@StartDate : 2012-01-01
	@LastUpdate : 2012-01-01
	@Description : Class Service
*/

require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
	'module/generate_number/business/GenerateNumber.class.php';
	
class Service extends Database
{
	protected $mSqlFile;
	protected $error = 0;
	protected $pesanError;
	protected $generateNumber;

	function __construct ($connectionNumber=0)
	{
		$this->mSqlFile = 'module/'.Dispatcher::Instance()->mModule.'/business/service.sql.php';
		parent::__construct($connectionNumber);
		$this->generateNumber = new GenerateNumber();    
		//$this->setDebugOn();
	}
	
	/**
	 * GetAlokasiPenerimaan
	 * @access public
	 * @return array
	 */
	public function GetAlokasiPenerimaan(){
		$result = $this->Open(
							$this->mSqlQueries['get_alokasi_penerimaan'], 
							array(
							));
		return $result;
	}

	/**		
	 * sendTransaksi
	 * @desciption untuk membuat transaksi auto jurnal
	 * @return boolean
	 * @access public
	 * 
	 */
	public function sendTransaksi($arrData,$kodeTransaksi = 'POK') 
	{
	//	$this->setDebugOn();
		$msg = '';
		$noBuktiTransaksi ='';
		$unitId='';
		$tipeTransaksiKode = '';
		
		if(is_object($arrData)){$arrData->AsArray();}
			$this->CekEmptyField($arrData);
		if(!empty($arrData) && ($this->error == 0)){
		/**
		 * cek sekenario jurnal
		 */
		$getCountRefFormCoa = $this->Open($this->mSqlQueries['get_count_ref_form_coa'], array($kodeTransaksi));
		
		if($getCountRefFormCoa[0]['total'] > 0){
			$this->StartTrans();
			/**
			 * buat transaksi
			 */
			    $tipeTransaksiKode = ($kodeTransaksi == 'POK') ? '101' : '102';
			    
			    $unitId = $this->getUnitId(trim($arrData['kode_unit']));
					
			    $noBuktiTransaksi = $this->generateNumber->GetNoBuktiTransaksi(
																$tipeTransaksiKode,
																$unitId);
																
				$result = $this->Execute($this->mSqlQueries['insert_transaksi'],
												array(
														$kodeTransaksi,
														$unitId,
														$noBuktiTransaksi,
														$arrData['tanggal'],
														$arrData['tanggal'],
														'no.'.$arrData['nomor'].' : '.$arrData['uraian'],
														$arrData['total'],
														$arrData['penanggung_jawab']
													));
			 $transId = $this->LastInsertId();
		   /***
		    * get transaksi info
		    */	
		   if($result){
				$transInfo = $this->Open($this->mSqlQueries['get_transaksi_info'],
												array(
														$transId
													));
			
				/**
				 * catat jurnal
				 * 1. catat di pembukuan ref
				 * 2. catat di pembukuan detail
				 */
		 
				$result = $this->Execute($this->mSqlQueries['insert_pembukuan_ref'],
														array(
																$transId,
																$transInfo[0]['tanggal'] ,
																$transInfo[0]['referensi']
																));
				$prId = $this->LastInsertId();																
				if($result){	
					$result = $this->Execute($this->mSqlQueries['insert_pembukuan_detail'],
														array(
																$prId,
																$transInfo[0]['nominal'],
																$transInfo[0]['referensi'],
																$transInfo[0]['catatan'],
																$kodeTransaksi
																));
				}
			} 	
			/**
			* end jurnal
			*/  		
	 
			$this->EndTrans($result);
		} else {
			$result  =FALSE;
			$msg = ': Sekenario auto jurnal belum didefinisikan';
		}
		} else{
			$result = FALSE;
			$msg = ': Data masih kosong';
		}
		if($result == TRUE){
			$r['status'] = TRUE;
			$r['message'] = 'Proses Transaksi Sukses';
		} else{
			$r['status'] = FALSE;
			if(!empty($this->pesanError) && is_array($this->pesanError)){
				$msg = ': '.implode(',',$this->pesanError);			
			}
			$r['message'] = 'Proses Transaksi Gagal '. $msg;
		}
		return $r;
	}
	/**
	*
	* Method untuk mengambil coa from setting
	* @param $kodeTransaksi
	*
	*/	
	/**
	public function getCoaFromSetting($kodeTransaksi)
	{
	   return $this->open($this->mSqlQueries['auto_jurnal_coa'],array($kodeTransaksi));
	}
	
	public function autoJurnal($data)
	{
	   
	   $msg["status"] = "201";
	   
	   #check jika parameter kode_transaksi kosong
	   if(empty($data['kode_transaksi'])){
	      
	      #jika kosong maka akan langsung di return dan proses tidak dilanjutkan
	      $msg["status"] = "204";
	      $msg["message"] = "Kode Transaksi Tidak Boleh Kosong";	   
	   }else{
	      
	      #jika ada isinya
	      $resultCoa = $this->getCoaFromSetting($data['kode_transaksi']);
	      
	      $detilId = 0;
	      $detil=$data['detil'];
	      unset($data['detil']);
	      $totalDebit=0;
	      $totalKredit=0;
	      
	      
	      for ($i = 0; $i < count($detil); $i++)
	      {
            $pembDetil[$detilId]["coa_kode"] = (is_object($detil[$i]["coa_kode"]))?$detil[$i]["coa_kode"]->mrVariable:$detil[$i]["coa_kode"];
            $pembDetil[$detilId]["keterangan"] = (is_object($detil[$i]["keterangan"]))?$detil[$i]["keterangan"]->mrVariable:$detil[$i]["keterangan"];
            $pembDetil[$detilId]["status_dk"] = (is_object($detil[$i]["status_dk"]))?$detil[$i]["status_dk"]->mrVariable:$detil[$i]["status_dk"];
            $pembDetil[$detilId]["nilai"] = (is_object($detil[$i]["nilai"]))?$detil[$i]["nilai"]->mrVariable:$detil[$i]["nilai"];
            if($pembDetil[$detilId]["status_dk"]=="D"){
               $totalDebit+=(is_object($detil[$i]["nilai"]))?$detil[$i]["nilai"]->mrVariable:$detil[$i]["nilai"];
            }else{
               $totalKredit+=(is_object($detil[$i]["nilai"]))?$detil[$i]["nilai"]->mrVariable:$detil[$i]["nilai"];
            }
            $detilId++;
	      }
	      
	      for ($i = 0; $i < count($resultCoa); $i++)
	      {
            $pembDetil[$detilId]["coa_kode"] = $resultCoa[$i]["coa_kode"];
            $pembDetil[$detilId]["keterangan"] = $resultCoa[$i]["keterangan"];
            $pembDetil[$detilId]["status_dk"] = $resultCoa[$i]["status_dk"];
            $pembDetil[$detilId]["nilai"] = ($pembDetil[$detilId]["status_dk"]=="D")?$totalKredit:$totalDebit;
	      }
	      
	      $data['detil'] = $pembDetil;
	      $msg = $this->sendTransaksi($data);
	   }
	   
	   return $msg;
	}
	*/
	# =========== PROCESS ============= #
	function CekEmptyField($arrData){
		if((trim( $arrData['nomor'] ) !=='') &&  ( $this->getCountTransaksi(trim($arrData['nomor'])   ) > 0 )){
			$this->error += 1;
			$this->pesanError[] = "Nomor Transaksi Sudah Ada";
		} else if((trim( $arrData['kode_unit'] ) !=='') && 
					($this->getCountUnitId(trim($arrData['kode_unit'])) == 0)){
			$this->error += 1;
			$this->pesanError[] = "Kode Unit Tidak Ada";
		}else {
			/**
			if($arrData['kode_transaksi'] == ""){
				$this->error += 1;
				$this->pesanError[] = "Kode Transaksi Kosong";
			}
			**/
			
			if($arrData['kode_unit'] == ""){
				$this->error += 1;
				$this->pesanError[] = "Kode Unit Kosong";
			}
			if($arrData['tanggal'] == ""){
				$this->error += 1;
				$this->pesanError[] = "Tanggal Kosong";
			}
			if(trim($arrData['nomor']) == ""){
				$this->error += 1;
				$this->pesanError[] = "Nomor Kosong";
			}

			if($arrData['uraian'] == ""){
				$this->error += 1;
				$this->pesanError[] = "Uraian Kosong";
			}
			if($arrData['total'] == ""){
				$this->error += 1;
				$this->pesanError[] = "Total Kosong";
			}
			if($arrData['penanggung_jawab'] == ""){
				$this->error += 1;
				$this->pesanError[] = "Penanggung Jawab Kosong";
			}
			/**
			if(isset($arrData['detil']) && count($arrData['detil'])>0){
			
				foreach($arrData['detil'] as $key=> $value){
					if((trim($value['coa_kode']) != '') && ( $this->getCountCoa(trim($value['coa_kode']))==0 )){
						$this->error += 1;
						$this->pesanError[] = "Kode Coa -".$key." tidak cocok";
					}else{
						if(trim($value['coa_kode'])==""){
							$this->error += 1;
							$this->pesanError[] = "Kode Coa - ".$key;
						}
						if(trim($value['nilai'])==""){
							$this->error += 1;
							$this->pesanError[] = "Nilai - ".$key;
						}
						if(trim($value['keterangan'])==""){
							$this->error += 1;
							$this->pesanError[] = "Keterangan COA - ".$key;
						}
						if(trim($value['status_dk'])==""){
							$this->error += 1;
							$this->pesanError[] = "Status D/K - ".$key;
						}
					}
				}
			}	
			*/
		}
	}
	
	/**
	function sendTransaksi_old($arrData){

		if(!empty($arrData)){
			#cek error
			$this->CekEmptyField($arrData);
			
			 //proses simpan
			
			if($this->error > 0){
				$result['status'] = 'false';
				$result['message'] = 'Empty or Error Params : '.implode(', ',$this->pesanError);
				return $result;
			} else{
				if(count($arrData['detil'])>0){
					#cek total = debit+kredit & totalDebit = totalKredit
				
					$totalDebit=0; $totalKredit=0;
					//arrData= is_object($arrData) ? $arrData->AsArray() : $arrData;
				
					for($i=0;$i<count($arrData['detil']);$i++){
						if($arrData['detil'][$i]['status_dk']=='D') $totalDebit += (is_object($arrData['detil'][$i]['nilai']))?$arrData['detil'][$i]['nilai']->mrVariable:$arrData['detil'][$i]['nilai'];
						else $totalKredit += (is_object($arrData['detil'][$i]['nilai']))?$arrData['detil'][$i]['nilai']->mrVariable:$arrData['detil'][$i]['nilai'];
					}
					$total = (is_object($arrData['total']))?$arrData['total']->mrVariable:$arrData['total'];
				
					if(($total == $totalDebit)&&($totalDebit == $totalKredit)){
						$this->StartTrans();
						#insert transaksi
						$addTransaksi = $this->InsertTransaksi($arrData['kode_transaksi'],$arrData['kode_unit'],$arrData['nomor'],$arrData['tanggal'],$arrData['uraian'],$arrData['total'],$arrData['penanggung_jawab']);
						#insert pembukuan ref
						$transId = $this->getLastTransaksiId();
						$addPembRef = $this->InsertPembukuanRef($transId,$arrData['tanggal']);
						#insert pembukuan detil
						$prId = $this->getPembukuanRefId($transId);
						$addPembDetil = $this->InsertPembukuanDetil($prId,$arrData['detil']);
					
						if($addTransaksi && $addPembRef && $addPembDetil){
							$this->EndTrans(true);
							$result['status'] = true;
							$result['message'] = 'Input Transaksi Berhasil';
						}else{
							$this->EndTrans(false);
							$result['status'] = false;
							$result['message'] = 'Input Transaksi Gagal';
						}
					}else{
						$result['status'] = false;
						$result['message'] = 'Input Transaksi Gagal - Nilai total tidak sama dengan total detil/total debit tidak sama dengan total kredit';
					}
				}else{
					$result['status'] = false;
					$result['message'] = 'Input Transaksi Gagal - Data Detil Kosong';
				}
			}
			
			 // end proses simpan
			
			 			
		}else{
			$result['status'] = false;
			$result['message'] = 'Input Transaksi Gagal - Data Kosong';
		}
		
		return $result;
	}
	*/
	# ============ DATABASE ============= #
	
	## GET
	function getCountTransaksi($nomor){
		//$this->setDebugOn();
		$result = $this->Open($this->mSqlQueries['get_count_transaksi'], 
							array('%'.$nomor.'%')
						);
		return $result[0]['total'];
	}
	
	function getCountUnitId($kodeUnit){
		//$this->setDebugOn();
		$result = $this->Open($this->mSqlQueries['get_count_unit_kerja_id'], array($kodeUnit));
		return $result[0]['total'];
	}
	function getUnitId($kodeUnit){
		//$this->setDebugOn();
		$result = $this->Open($this->mSqlQueries['get_unit_kerja_id'], array($kodeUnit));
		if(isset($result[0]['unit_id'])){
			$r = $result[0]['unit_id'];
		}else{
			$r = null;
		}
		return $r;
	}
	/**
	function getCountCoa($kodeCoa){
		//$this->setDebugOn();
		$result = $this->Open($this->mSqlQueries['get_count_coa'], array($kodeCoa));
		return $result[0]['total'];
	}

	
	function getLastTransaksiId(){
		$result = $this->Open($this->mSqlQueries['get_last_transaksi_id'], array());
		return $result[0]['id'];
	}
	
	function getPembukuanRefId($transId){
		$result = $this->Open($this->mSqlQueries['get_pembukuan_ref_id'], array($transId));
		return $result[0]['id'];
	}
	
	function getLastTransaksiRef(){
		$result = $this->Open($this->mSqlQueries['get_last_transaksi_ref'], array());
		return $result[0]['nomor'];
	}
	*/
	## DO
	/*
	function InsertTransaksi($kode_transaksi,$kode_unit,$nomor,$tanggal,$uraian,$total,$penanggung_jawab){
		//$this->setDebugOn();
		$query = str_replace('[nomor]', $nomor, $this->mSqlQueries['insert_transaksi']);
		$result = $this->Execute($query, array($kode_transaksi,$kode_unit,$tanggal,$tanggal,$tanggal,$uraian,$total,$penanggung_jawab));
		return $result;
	}
	
	function InsertPembukuanRef($transId,$tanggal){
		$query = str_replace('[transId]', $transId, $this->mSqlQueries['insert_pembukuan_ref']);
		$result = $this->Execute($query, array($tanggal));
		return $result;
	}
	
	function InsertPembukuanDetil($prId,$arrData){
	   
		#cek total Debit/Kredit
		$totalDebit=0;
		$totalKredit=0;
		for($i=0;$i<count($arrData);$i++){
			if($arrData[$i]['status_dk']=='D')
				(!empty($arrData[$i]['nilai']->mrVariable))?$totalDebit+=$arrData[$i]['nilai']->mrVariable:$totalDebit+=$arrData[$i]['nilai'];
			else
				(!empty($arrData[$i]['nilai']->mrVariable))?$totalKredit+=$arrData[$i]['nilai']->mrVariable:$totalKredit+=$arrData[$i]['nilai'];
		}
		
		if ($totalDebit == $totalKredit){
		   	for ($i = 0; $i < count($arrData); $i++)
		   	{
		   		if($arrData[$i]['kode_subacc'] == ''){
					$subAcc[$i] = array('00','00','00','00','00','00','00');
				}else{
					$subAcc[$i] = explode('-',$arrData[$i]['kode_subacc']); 
				}
				
				$pd["pd"]=$prId;
				$pd['coa_kode'] = (!is_object($arrData[$i]['coa_kode']))?$arrData[$i]['coa_kode']:$arrData[$i]["coa_kode"]->mrVariable;
	         	$pd["keterangan"] = (!is_object($arrData[$i]['keterangan']))?$arrData[$i]['keterangan']:$arrData[$i]["keterangan"]->mrVariable;
	         	$pd["status_dk"] = (!is_object($arrData[$i]['status_dk']))?$arrData[$i]['status_dk']:$arrData[$i]["status_dk"]->mrVariable;
	         	$pd["nilai"] = (!is_object($arrData[$i]['nilai']))?$arrData[$i]['nilai']:$arrData[$i]["nilai"]->mrVariable;
				$pd = array_merge($pd,$subAcc[$i]);
				
				#sebelum insert ke pembukuan detil, ada baiknya check total debit dan kredit sudah saama atau belum
				#tambahkan trap disini fix-me
	
				$result = $this->Execute($this->mSqlQueries['insert_pembukuan_detil'], $pd);
				unset($pd);
		   }
		}else{
			return false;
		}

		return $result;
	}
	*/
	
}
?>