<?php if ( ! defined('GTFW_BASE_DIR')) exit('No direct script access allowed');

/**
 * 
 * @package penerima_alokasi_unit
 * @subpackage response
 * @description untuk menangani proses pendistribusian alokasi unit
 * @analyst nanang ruswianto <nanang@gamatechno.com>
 * @author noor hadi <noor.hadi@gamatechno.com>
 * @since 21 November 2012
 * 
 * @copyright 2012 Gamatechno Indonesia
 * 
 */
  
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
						'module/penerima_alokasi_unit/business/PenerimaAlokasiUnit.class.php';
						
						
class ProcessPenerimaAlokasiUnit
{

	protected $mPOST;
	protected $mObj;
	protected $mPageView;
	protected $mPageInput;
	//css hanya dipake di view
	protected $mCssDone = "notebox-done";
	protected $mCssFail = "notebox-warning";

	protected $mDecId;
	protected $mEncId;
	protected $mModuleName ='penerima_alokasi_unit';
	protected $mSubModuleViewName='PenerimaAlokasiUnit';
	protected $mSubModuleInputName='InputPenerimaAlokasiUnit';
	protected $mSubModuleInputSumberName='InputSumberAlokasiUnit';

	public function __construct() 
	{
		$this->mObj = new PenerimaAlokasiUnit();
		$this->mPOST = $_POST->AsArray();
		$this->mDecId = Dispatcher::Instance()->Decrypt($_REQUEST['data_id']);
		$this->mEncId = Dispatcher::Instance()->Encrypt($this->mDecId);
		$this->mPageView = Dispatcher::Instance()->GetUrl(
												$this->mModuleName, 
												$this->mSubModuleViewName, 
												'view', 
												'html');
												
		$this->mPageInput = Dispatcher::Instance()->GetUrl(
												$this->mModuleName, 
												$this->mSubModuleInputName, 
												'view', 
												'html');
		
	}

	public function Check($tipe='add') 
	{
		$errorCode = true;
		
		if (isset($_POST['btnsimpan'])) {
			if(trim($this->mPOST['kode_penerimaan_nama'])== '') {
					$errorCode = "kodeEmpty";
			}
			if(!isset($this->mPOST['p_unit']) && ($this->mPOST['alokasi_pusat_nilai'] > 0)){
			    $errorCode = "pUnitEmpty";
			}
			if(isset($this->mPOST['p_unit']) && ($this->mPOST['alokasi_pusat_nilai'] > 0)){
				$pStatus = 1;
				$pTotalAlokasiUnit = 0;
				foreach($this->mPOST['p_unit'] as $pKey => $pValue){
							if(($pValue['nilai'] ==0 )|| (trim($pValue['nilai']) =='')){
									$pStatus *= 0;
							} else {
									$pStatus *= 1;
									$pTotalAlokasiUnit += $pValue['nilai'];
							}
				}
				if($pStatus == 0){
					$errorCode = "pNilaiAlokasiEmpty";
				}elseif($pTotalAlokasiUnit > $this->mPOST['alokasi_pusat_nilai'] ){// 100){
					$errorCode = "pNilaiAlokasiOver";
				}elseif($pTotalAlokasiUnit < $this->mPOST['alokasi_pusat_nilai'] ){// 100){
					$errorCode = "pNilaiAlokasiKecil";
				}
			}
			if(!isset($this->mPOST['unit']) && ($this->mPOST['alokasi_unit_nilai'] > 0)){	
			    $errorCode = "unitEmpty";
			}
			if(isset($this->mPOST['unit']) && ($this->mPOST['alokasi_unit_nilai'] > 0)){
				$status = 1;
				$totalAlokasiUnit = 0;
				foreach($this->mPOST['unit'] as $key => $value){
							if(($value['nilai'] ==0 )|| (trim($value['nilai']) =='')){
									$status *= 0;
							} else {
									$status *= 1;
									$totalAlokasiUnit += $value['nilai'];
							}
				}
				if($status == 0){
					$errorCode = "nilaiAlokasiEmpty";
				}elseif($totalAlokasiUnit > $this->mPOST['alokasi_unit_nilai']){//100){
					$errorCode = "nilaiAlokasiOver";
				}elseif($totalAlokasiUnit < $this->mPOST['alokasi_unit_nilai']){//100){
					$errorCode = "nilaiAlokasiKecil";
				}
			}
			if(($this->mPOST['alokasi_id'] != '') 
				&& ($this->mObj->GetCountKodePenerimaanAlokasi($this->mPOST['alokasi_id']) > 0)
				&& ($tipe =='add')
				) {
					$errorCode = "kodePeneriaanExist";
			}									
		} else{
			return false;
		}
		
		return $errorCode;
		
	}

	public function Add() 
	{
		$cek = $this->Check();
		if($cek === true) {
			
			$add = $this->mObj->DoAdd(	
										$this->mPOST['alokasi_id'], 
										$this->mPOST['alokasi_unit_id'], 
										$this->mPOST['alokasi_pusat_id'], 
										$this->mPOST['unit'],
										$this->mPOST['p_unit']
										);
			if ($add === true) {
				Messenger::Instance()->Send(
											$this->mModuleName, 
											$this->mSubModuleViewName, 
											'view', 
											'html', 
											array(
												$this->mPOST,
												'Penambahan data Berhasil Dilakukan', 
												$this->mCssDone),
											Messenger::NextRequest);
			} else {
				Messenger::Instance()->Send(
											$this->mModuleName, 
											$this->mSubModuleViewName, 
											'view', 
											'html', 
											array(
												$this->mPOST,
												'Gagal Menambah Data', 
												$this->mCssFail),
											Messenger::NextRequest);
			}
		} elseif($cek == "kodeEmpty") {
			Messenger::Instance()->Send(
										$this->mModuleName, 
										$this->mSubModuleInputName, 
										'view', 
										'html', 
										array(
												$this->mPOST,
												'Alokasi Penerimaan Masih Kosong'),
										Messenger::NextRequest);
			return $this->mPageInput;
		
		} elseif($cek == "pUnitEmpty") {
			Messenger::Instance()->Send(
										$this->mModuleName, 
										$this->mSubModuleInputName, 
										'view', 
										'html', 
										array(
												$this->mPOST,
												'Unit Kerja Penerima Alokasi Untuk Pusat Belum Dipilih'),
										Messenger::NextRequest);
			return $this->mPageInput;
		} elseif($cek == "pNilaiAlokasiEmpty") {
			Messenger::Instance()->Send(
										$this->mModuleName, 
										$this->mSubModuleInputName, 
										'view', 
										'html', 
										array(
												$this->mPOST,
												'Nilai alokasi Unit Kerja  Untuk Pusat Masih Ada yang kosong'),
										Messenger::NextRequest);
			return $this->mPageInput;
		} elseif($cek == "pNilaiAlokasiOver") {
			Messenger::Instance()->Send(
										$this->mModuleName, 
										$this->mSubModuleInputName, 
										'view', 
										'html', 
										array(
												$this->mPOST,
												'Total nilai penerima Pusat melebihi alokasi untuk pusat. Silahkan Cek kembali nilai alokasi'),
										Messenger::NextRequest);
			return $this->mPageInput;

		} elseif($cek == "pNilaiAlokasiKecil") {
			Messenger::Instance()->Send(
										$this->mModuleName, 
										$this->mSubModuleInputName, 
										'view', 
										'html', 
										array(
												$this->mPOST,
												'Total nilai penerima Pusat lebih kacil dari alokasi untuk pusat. Silahkan Cek kembali nilai alokasi'),
										Messenger::NextRequest);
			return $this->mPageInput;
		                  
		} elseif($cek == "unitEmpty") {
			Messenger::Instance()->Send(
										$this->mModuleName, 
										$this->mSubModuleInputName, 
										'view', 
										'html', 
										array(
												$this->mPOST,
												'Unit Kerja Penerima Alokasi Untuk Unit Belum Dipilih'),
										Messenger::NextRequest);
			return $this->mPageInput;
		} elseif($cek == "nilaiAlokasiEmpty") {
			Messenger::Instance()->Send(
										$this->mModuleName, 
										$this->mSubModuleInputName, 
										'view', 
										'html', 
										array(
												$this->mPOST,
												'Nilai alokasi Unit Kerja Untuk Unit Masih Ada yang kosong'),
										Messenger::NextRequest);
			return $this->mPageInput;
		} elseif($cek == "nilaiAlokasiOver") {
			Messenger::Instance()->Send(
										$this->mModuleName, 
										$this->mSubModuleInputName, 
										'view', 
										'html', 
										array(
												$this->mPOST,
												'Total nilai penerima Unit melebihi alokasi untuk unit. Silahkan Cek kembali nilai alokasi'),
										Messenger::NextRequest);
			return $this->mPageInput;
		} elseif($cek == "nilaiAlokasiKecil") {
			Messenger::Instance()->Send(
										$this->mModuleName, 
										$this->mSubModuleInputName, 
										'view', 
										'html', 
										array(
												$this->mPOST,
												'Total nilai penerima Unit lebih kecil dari alokasi untuk unit. Silahkan Cek kembali nilai alokasi'),
										Messenger::NextRequest);
			return $this->mPageInput;
		} elseif($cek == "kodePeneriaanExist") {
			Messenger::Instance()->Send(
										$this->mModuleName, 
										$this->mSubModuleInputName, 
										'view', 
										'html', 
										array(
												$this->mPOST,
												'Alokasi Kode Penerimaan Sudah Ada'),
										Messenger::NextRequest);
			return $this->mPageInput;
		} 
		return $this->mPageView;
	}
	
	public function Update() 
	{
		$cek = $this->Check('update');
		if($cek === true) {
		
			$update = $this->mObj->DoUpdate(
											$this->mPOST['alokasi_id'], 
											$this->mPOST['alokasi_unit_id'], 
											$this->mPOST['alokasi_pusat_id'], 
											$this->mPOST['unit'],
											$this->mPOST['p_unit'],
											$this->mPOST['data_id']
										);
		
			if ($update === true) {
				Messenger::Instance()->Send(
											$this->mModuleName, 
											$this->mSubModuleViewName, 
											'view', 
											'html', 
											array(
												$this->mPOST,
												'Perubahan Data Berhasil Dilakukan', 
												$this->mCssDone),
											Messenger::NextRequest);
			} else {
				Messenger::Instance()->Send(
											$this->mModuleName, 
											$this->mSubModuleInputName, 
											'view', 
											'html', 
											array(
												$this->mPOST,
												'Perubahan Data Gagal Dilakukan'.print_r($this->mPOST), 
												$this->mCssFail),
											Messenger::NextRequest);
				
					return $this->mPageInput . "&data_id=" . $this->mEncId;
			}
		} elseif($cek == "kodeEmpty") {
			Messenger::Instance()->Send(
										$this->mModuleName, 
										$this->mSubModuleInputName, 
										'view', 
										'html', 
										array(
												$this->mPOST,
												'Alokasi Penerimaan Masih Kosong'),
										Messenger::NextRequest);
			return $this->mPageInput . "&data_id=" . $this->mEncId;
		
		} elseif($cek == "pUnitEmpty") {
			Messenger::Instance()->Send(
										$this->mModuleName, 
										$this->mSubModuleInputName, 
										'view', 
										'html', 
										array(
												$this->mPOST,
												'Unit Kerja Penerima Alokasi Untuk Pusat Belum Dipilih'),
										Messenger::NextRequest);
			return $this->mPageInput . "&data_id=" . $this->mEncId;
		} elseif($cek == "pNilaiAlokasiEmpty") {
			Messenger::Instance()->Send(
										$this->mModuleName, 
										$this->mSubModuleInputName, 
										'view', 
										'html', 
										array(
												$this->mPOST,
												'Nilai alokasi Unit Kerja  Untuk Pusat Masih Ada yang kosong'),
										Messenger::NextRequest);
			return $this->mPageInput . "&data_id=" . $this->mEncId;
		} elseif($cek == "pNilaiAlokasiOver") {
			Messenger::Instance()->Send(
										$this->mModuleName, 
										$this->mSubModuleInputName, 
										'view', 
										'html', 
										array(
												$this->mPOST,
												'Total nilai penerima Pusat melebihi alokasi untuk pusat. Silahkan Cek kembali nilai alokasi'),
										Messenger::NextRequest);
			return $this->mPageInput . "&data_id=" . $this->mEncId;
		                  
		} elseif($cek == "pNilaiAlokasiKecil") {
			Messenger::Instance()->Send(
										$this->mModuleName, 
										$this->mSubModuleInputName, 
										'view', 
										'html', 
										array(
												$this->mPOST,
												'Total nilai penerima Pusat lebih kecil dari alokasi untuk pusat. Silahkan Cek kembali nilai alokasi'),
										Messenger::NextRequest);
			return $this->mPageInput . "&data_id=" . $this->mEncId;
		                  
		} elseif($cek == "unitEmpty") {
			Messenger::Instance()->Send(
										$this->mModuleName, 
										$this->mSubModuleInputName, 
										'view', 
										'html', 
										array(
												$this->mPOST,
												'Unit Kerja Penerima Alokasi Untuk Unit Belum Dipilih'),
										Messenger::NextRequest);
			return $this->mPageInput . "&data_id=" . $this->mEncId;
		} elseif($cek == "nilaiAlokasiEmpty") {
			Messenger::Instance()->Send(
										$this->mModuleName, 
										$this->mSubModuleInputName, 
										'view', 
										'html', 
										array(
												$this->mPOST,
												'Nilai alokasi Unit Kerja Untuk Unit Masih Ada yang kosong'),
										Messenger::NextRequest);
			return $this->mPageInput . "&data_id=" . $this->mEncId;
		} elseif($cek == "nilaiAlokasiOver") {
			Messenger::Instance()->Send(
										$this->mModuleName, 
										$this->mSubModuleInputName, 
										'view', 
										'html', 
										array(
												$this->mPOST,
												'Total nilai penerima Unit melebihi alokasi untuk unit. Silahkan Cek kembali nilai alokasi'),
										Messenger::NextRequest);
			return $this->mPageInput . "&data_id=" . $this->mEncId;
		
		} elseif($cek == "nilaiAlokasiKecil") {
			Messenger::Instance()->Send(
										$this->mModuleName, 
										$this->mSubModuleInputName, 
										'view', 
										'html', 
										array(
												$this->mPOST,
												'Total nilai penerima Unit lebih kecil dari alokasi untuk unit. Silahkan Cek kembali nilai alokasi'),
										Messenger::NextRequest);
			return $this->mPageInput . "&data_id=" . $this->mEncId;
		}
		return $this->mPageView;
	}

	public function Delete() 
	{
		   /**
	    if(is_array($this->mPOST['idDelete'])){
	       $arrId = $this->mPOST['idDelete'];   
	    } else {
	       $arrId[] = $this->mPOST['idDelete'];
	    }
	    **/
	    
	   	$penerimaId = 	$this->mPOST['idDelete'];   
		$delete = $this->mObj->DoDelete($penerimaId);
		if($delete === true) {
			Messenger::Instance()->Send(
									$this->mModuleName, 
									$this->mSubModuleViewName, 
									'view', 
									'html', 
									array(
											$this->mPOST,
											'Penghapusan Data Berhasil Dilakukan', 
											$this->mCssDone),
									Messenger::NextRequest);
		} else {/**
			//jika masuk disini, berarti PASTI ada salah satu atau lebih data yang gagal dihapus
			for($i=0;$i<sizeof($arrId);$i++) {
				$deleteData = false;
				$deleteData = $this->mObj->DoDelete($arrId[$i]);
				if($deleteData === true) $sukses += 1;
				else $gagal += 1;
			}*/
			Messenger::Instance()->Send(
										$this->mModuleName, 
										$this->mSubModuleViewName, 
										'view', 
										'html', 
										array(
												$this->mPOST, 
												' Data Tidak Dapat Dihapus.', 
												$this->mCssFail),
										Messenger::NextRequest);
		}
		return $this->mPageView;
	}
}
?>