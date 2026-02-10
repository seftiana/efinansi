<?php

/**
 * 
 * @package history_transaksi_keuangan_sp2d
 * @subpackage business
 * @class Jurnal
 * @author noor hadi <noor.hadi@gamatechno.com>
 * @analyst dyah fajar <dyah@gamatechno.com>
 * @copyright 2014 gamatechno indonesia
 */
 
class Jurnal extends Database
{
	protected $mSqlFile = 'module/history_transaksi_keuangan_sp2d/business/jurnal.sql.php';
	
	protected $mUserId ;
	
	public function __construct($connectionNumber = 0)
	{
		parent::__construct($connectionNumber);
		$this->mUserId = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
	}
	
	public function GetTransksiById($id)
	{
		$result = $this->open($this->mSqlQueries['get_transaksi_by_id'], array($id));		
		return $result[0];
	}
	
	public function GetTransksiJurnalById($id)
	{
		$result = $this->open($this->mSqlQueries['get_transaksi_jurnal_by_id'], array($id));		
		return $result;//[0];
	}
	
	public function Update($data)
	{
		//$this->SetDebugOn();
		$this->StartTrans();
		/** hapus data di pembukuan detail dan referensi **/
		$result = $this->DeleteJurnal($data['transaksi_id']);
		/** update transaksi **/		
		/** isi jurnal kembali **/
		if($result){
			$result = $this->SaveJurnal($data);
		}
		
		$this->EndTrans($result);
		
		return $result;
	}
	
	public function Delete($id)
	{
		//$this->SetDebugOn();
		$this->StartTrans();
		/** hapus data di pembukuan detail dan referensi **/
		$result = $this->DeleteJurnal($id);
		/** update transaksi **/
		/** set transaksi jurnal = T **/
		if($result) {
			$result = $this->Execute($this->mSqlQueries['set_status_jurnal_t'],array($id));
		}
		
		$this->EndTrans($result);
		return $result;
	}
	
	public function Save($data)
	{
		//$this->SetDebugOn();	
		/**
		 * simpan transaksi
		 */		 
		$this->StartTrans();
		$result = $this->SaveJurnal($data);
		//$result = false;
		$this->EndTrans($result);
		/**
		 * end proses jurnal
		 */
		return $result;		
	}
	
	protected function SaveJurnal($data)
	{
		 /**
		  * proses penjurnalan
		  */ 			 
			//$transInfo = $this->Open($this->mSqlQueries['get_transaksi_info'],array($data['transaksi_id']));
			
			/**
			 * catat jurnal
			 * 1. catat di pembukuan ref
			 * 2. catat di pembukuan detail
			 */
			
			/**
			 * simpan ke tabel pembukuan referensi
			 */
			    $keteranganTransaksi = 'no bukti transaksi : ' . 
										$data['transaksi_no_bukti'] . ' ( '.
										$data['transaksi_uraian'] . 
										' penanggung jawab : '.
										$data['transaksi_penanggung_jawab'] . ' ) ';
				$result = $this->Execute($this->mSqlQueries['insert_pembukuan_ref'],
														array(
																$data['transaksi_id'],
																$this->mUserId,
																$data['transaksi_tanggal'],
																$keteranganTransaksi
																));
				
				$prId = $this->LastInsertId();
			
			/**
			 * simpan ke tabel pembukuan detail
			 */	
				if($result){	
					$keteranganTambahan = 'Generate Auto Jurnal :' . $data['transaksi_uraian'];
					
					if(!empty($data['COA'])){
						$valueInserts = array();
						//echo sizeof($data['COA']);
						foreach($data['COA'] as $key => $value){
								$valueInserts[] = "('".$prId."','".$value['akun_id']."','".
												$value['akun_nominal']."','".$keteranganTambahan.
												"','".$keteranganTambahan."','".
												$value['akun_dk']."') ";
						}
						
						$valueInsert = implode(',',$valueInserts);
						$queryInsert  = $this->mSqlQueries['insert_pembukuan_detail_2'].' '.$valueInsert;
						$result = $this->Execute($queryInsert,array());
					} else {
						$result = false;
					}
					
					/*
					$result = $this->Execute($this->mSqlQueries['insert_pembukuan_detail'],
														array(
																$prId,
																$data['transaksi_nominal'],
																'',
																$keteranganTambahan,
																$data['transaksi_sekenario_jurnal_id']
																));
																*/ 
					if($result){
						$result = $this->Execute($this->mSqlQueries['set_status_jurnal_y'],array($data['transaksi_id']));
					}																
				}
		 /**
		  * end proses jurnal
		  */		
		 return $result;
	}
	
	protected function DeleteJurnal($id)
	{
		$result = $this->Execute($this->mSqlQueries['delete_pembukuan_detail_by_trans_id'],array($id));
		if($result) {
			$result = $this->Execute($this->mSqlQueries['delete_pembukuan_referensi_by_trans_id'],array($id));
		}
		
		return $result;
	}
	
}

?>