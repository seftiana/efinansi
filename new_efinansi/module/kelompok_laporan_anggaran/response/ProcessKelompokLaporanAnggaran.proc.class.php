<?php

/**
 *
 * class ProcessKelompokLaporanAnggaran
 * @package kelompok_laporan_anggaran
 * @subpackage business
 * @analyst nanang ruswianto <nanang@gamatechno.com>
 * @author noor hadi <noor.hadi@gamatechno.com>
 * @since 6 september 2012
 * @copyright 2012 Gamatechno Indonesia
 * 
 */
 
 
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
'module/kelompok_laporan_anggaran/business/KelompokLaporanAnggaran.class.php';

class ProcessKelompokLaporanAnggaran
{
	protected $mPost;
	protected $mKLA;
   
	protected $mPageView;
	protected $mPageInput;
   
	//css hanya dipake di view
	protected $mCssDone = "notebox-done";
	protected $mCssFail = "notebox-warning";

	protected $mReturn;
	protected $mDecId;
	protected $mEncId;

	public function __construct() 
	{
		$this->mKLA      = new KelompokLaporanAnggaran();
		$this->mPost     = $_POST->AsArray();
		$this->mDecId    = Dispatcher::Instance()->Decrypt($_REQUEST['dataId']);
		$this->mEncId    = Dispatcher::Instance()->Encrypt($this->mDecId);
		$this->mPageView = Dispatcher::Instance()->GetUrl(
														'kelompok_laporan_anggaran', 
														'KelompokLaporanAnggaran', 
														'view', 
														'html'
														);
														
        $this->mPageInput = Dispatcher::Instance()->GetUrl(
														'kelompok_laporan_anggaran', 
														'inputKelompokLaporanAnggaran', 
														'view', 
														'html'
														);      													
	}

	public function Check() 
	{
		if (isset($this->mPost['btnsimpan'])) {
			if((trim($this->mPost['klp_lap']) == "") || trim($this->mPost['no_urutan']) == "" ){
				return "empty";
			}
          
			if(!ctype_digit($this->mPost['no_urutan'])){
				return "is_number";
			}
			return true;
		}
      return false;
	}
   
	public function Add() 
	{
		$cek = $this->Check();
		if($cek === true) {
			$addKlpLap = $this->mKLA->DoAdd(
											$this->mPost['klp_lap'], 
											empty($this->mPost['bentuk_transaksi']) ? $this->mPost['jns_lap']:$this->mPost['bentuk_transaksi'],
											$this->mPost['is_tambah'], 
											$this->mPost['no_urutan'],
											$this->mPost['pagu_bas_mak']
											);
			if ($addKlpLap === true) {
				Messenger::Instance()->Send(
							'kelompok_laporan_anggaran', 
							'KelompokLaporanAnggaran', 
							'view', 
							'html', 
							array(
								$this->mPost,
								'Penambahan data Berhasil Dilakukan', 
								$this->mCssDone
								),
							Messenger::NextRequest
							);
			} else {
				Messenger::Instance()->Send(
							'kelompok_laporan_anggaran', 
							'KelompokLaporanAnggaran', 
							'view', 
							'html', 
							array(
								$this->mPost,
								'Gagal Menambah Data', 
								$this->mCssFail
							),
							Messenger::NextRequest
							);
			}
		} elseif($cek == "empty") {
				Messenger::Instance()->Send(
							'kelompok_laporan_anggaran', 
							'inputKelompokLaporanAnggaran', 
							'view', 
							'html', 
							array(
									$this->mPost,
									'Lengkapi Isian Data'
							),
							Messenger::NextRequest
							);
			return $this->mPageInput;
		} elseif($cek == "is_number") {
				Messenger::Instance()->Send(
							'kelompok_laporan_anggaran', 
							'inputKelompokLaporanAnggaran', 
							'view', 
							'html', 
							array(
									$this->mPost,
									'No Urutan harus angka'
							),
							Messenger::NextRequest
							);
							
			return $this->mPageInput;
      }
      
		return $this->mPageView;
	}

	public function Update() 
	{
		$cek = $this->Check();
		if($cek === true) {
			$updateKlpLap = $this->mKLA->DoUpdate(
												$this->mPost['klp_lap'], 
												empty($this->mPost['bentuk_transaksi']) ? $this->mPost['jns_lap']:$this->mPost['bentuk_transaksi'],
												$this->mPost['is_tambah'], 
												$this->mPost['no_urutan'], 
												$this->mDecId,
												$this->mPost['pagu_bas_mak']
												);
			if ($updateKlpLap === true) {
				Messenger::Instance()->Send(
												'kelompok_laporan_anggaran', 
												'KelompokLaporanAnggaran', 
												'view', 
												'html', 
												array(
														$this->mPost,
														'Perubahan Data Berhasil Dilakukan', 
														$this->mCssDone
													),
												Messenger::NextRequest
												);
			} else {
				Messenger::Instance()->Send(
												'kelompok_laporan_anggaran', 
												'KelompokLaporanAnggaran', 
												'view', 
												'html', 
												array(
														$this->mPost,
														'Perubahan Data Gagal Dilakukan', 
														$this->mCssFail
													),
												Messenger::NextRequest
												);
			}
		} elseif($cek == "empty") {
				Messenger::Instance()->Send(
												'kelompok_laporan_anggaran', 
												'inputKelompokLaporanAnggaran', 
												'view', 
												'html', 
												array(	
														$this->mPost,
														'Lengkapi Isian Data'
														),
												Messenger::NextRequest
												);

         return $this->mPageInput . "&dataId=" . $this->mEncId;
		} elseif($cek == "is_number") {
				Messenger::Instance()->Send(
												'kelompok_laporan_anggaran', 
												'inputKelompokLaporanAnggaran', 
												'view', 
												'html', 
												array(
														$this->mPost,
														'No Urutan harus angka'
														),
												Messenger::NextRequest
												);
				return $this->mPageInput . "&dataId=" . $this->mEncId;
		}
      
		return $this->mPageView;
	}

	public function Delete() 
	{
		$arrId = $this->mPost['idDelete'];
		$delete = $this->mKLA->DoDelete($arrId);
		if($delete === true) {
				Messenger::Instance()->Send(
											'kelompok_laporan_anggaran', 
											'KelompokLaporanAnggaran', 
											'view', 
											'html', 
											array(
													$this->mPost,
													'Penghapusan Data Berhasil Dilakukan', 
													$this->mCssDone
													),
											Messenger::NextRequest
											);
		} else {
			//jika masuk disini, berarti PASTI ada salah satu atau lebih data yang gagal dihapus
			for($i=0;$i<sizeof($arrId);$i++) {
				$deleteData = false;
				$deleteData = $this->mKLA->DoDelete($arrId[$i]);
				if($deleteData === true){ 
					$sukses += 1;
				}else {
					$gagal += 1;
					$sebab = $this->mKLA->GetError();
				}
			}
			
			Messenger::Instance()->Send(
										'kelompok_laporan_anggaran', 
										'KelompokLaporanAnggaran', 
										'view', 
										'html',
										array(
												$this->mPost, 
												$gagal . ' Data Tidak Dapat Dihapus<br />' . $sebab, 
												$this->mCssFail
											),
										Messenger::NextRequest
										);
		}
		
		return $this->mPageView;
   }
}
