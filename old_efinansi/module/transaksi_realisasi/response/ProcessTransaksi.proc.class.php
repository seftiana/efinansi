<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
	'module/transaksi_realisasi/business/AppTransaksi.class.php';
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
	'module/generate_number/business/GenerateNumber.class.php';

class ProcessTransaksi {

	var $_POST;
	var $Obj;
	var $pageView;
	var $pageDetil;
	var $pageInputDetil;
	var $arrData;
	//css hanya dipake di view
	var $cssDone = "notebox-done";
	var $cssFail = "notebox-warning";
   var $cssAlert = "notebox-alert";

	var $return;
	var $decId;
	var $encId;
	var $userId;
	var $generateNumber;

	function __construct() {
		$this->Obj = new AppTransaksi();
		$this->_POST = $_POST->AsArray();
		$this->decId = Dispatcher::Instance()->Decrypt($_REQUEST['dataId']);
		$this->encId = Dispatcher::Instance()->Encrypt($this->decId);
		$this->userId =trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
		$this->pageView = Dispatcher::Instance()->GetUrl(
								'transaksi_realisasi', 
								'Transaksi', 
								'view', 
								'html');
		$this->pageInputDetil = Dispatcher::Instance()->GetUrl(
								'transaksi_realisasi', 
								'transaksiDetil', 
								'view', 
								'html');								
		$this->pageDetil = Dispatcher::Instance()->GetUrl(
								'history_transaksi', 
								'HTRealisasiPencairan', 
								'view', 
								'html');
		
		/**
		 * untuk proses generate number bukti transaksi
		 */
	 	
		$this->generateNumber = new GenerateNumber();
      	
      	/**
      	 * end
      	 */
	}

	function setData($status='add') {
		$this->arrData['transUnitkerjaId'] = $this->_POST['unitkerja'];
		$this->arrData['transTransjenId'] = $this->_POST['jenis_transaksi'];
		$this->arrData['transTtId'] = $this->_POST['tipe_transaksi'];
		
		if($status == 'add'){
			$noBuktiTransaksi = $this->generateNumber->GetNoBuktiTransaksi(
														$this->_POST['tipe_transaksi'],
														$this->_POST['unitkerja']);
		} elseif($status == 'update') {
			/**
             * cek apakah unitkerja id berubah
             * jika berubah naka generate no bukti transaksi lagi sesui dengan
             * unitkerja id  yang baru
             */
            if($this->_POST['unitkerja'] == $this->_POST['unitkerja_lama']){
            	$noBuktiTransaksi = $this->_POST['no_kkb'];
            } else {
            	$noBuktiTransaksi = $this->generateNumber->GetNoBuktiTransaksi(
														$this->_POST['tipe_transaksi'],
														$this->_POST['unitkerja']);
            	
            }
            /**
             * end
             */
   		}
		$this->arrData['transReferensi'] =$noBuktiTransaksi;
		$this->arrData['transUserId'] = $this->userId;
		$this->arrData['transDueDate'] = $this->_POST['due_date_year'] . "-" . 
										 $this->_POST['due_date_mon'] . "-" . 
										 $this->_POST['due_date_day'];
										 
		$this->arrData['transTanggal'] = $this->_POST['tanggal_transaksi_year'] . "-" . 
		                                 $this->_POST['tanggal_transaksi_mon'] . "-" . 
										 $this->_POST['tanggal_transaksi_day'];
										 
		$this->arrData['transCatatan'] = $this->_POST['catatan_transaksi'];
		$this->arrData['transPenanggungJawabNama'] = $this->_POST['penanggung_jawab'];
		$this->arrData['transNilai'] = $this->_POST['nominal'];
		if($this->_POST['skenario'] == "auto") {
		   $this->arrData['transIsJurnal'] = "Y";
		} else {
		   $this->arrData['transIsJurnal'] = "T";
		}
		if($this->decId != "") $this->arrData['transId'] = $this->decId;
		return $this->arrData;
	}
	
	function Check() {
		if (isset($_POST['btnsimpan'])) {
			/**
         if($this->decId != '') {
            $cek = $this->Obj->CekTransaksiUpdate($this->_POST['no_kkb'], $this->decId);
         } else {
            $cek = $this->Obj->CekTransaksi($this->_POST['no_kkb']);
         }
         */
         if($cek === false) {
            return "exists";
         }
         return true;
		}
		return false;
	}

	function Add() {
		$cek = $this->Check();
		if($cek === true) {
			$this->setData();
            $id_transaksi = $this->Obj->DoAddTransaksi($this->arrData);
			if($id_transaksi === false) {
				Messenger::Instance()->Send(
											'transaksi_realisasi', 
											'detilTransaksi', 
											'view', 
											'html', 
											array(
													$this->_POST,'Gagal Menambah Data', 
													$this->cssFail),
											Messenger::NextRequest);
			} else {
				if($id_transaksi != "") {
				//jika jenis transaksi == Anggaran, insert MAK ke table transaksi_detail_anggaran
					if($this->_POST['mak'] != "") {
						 $pencairan = $this->Obj->DoAddTransaksiDetilPencairan(
						 									 $id_transaksi, 
															 $this->_POST['mak']);
															 
						 $satu = $this->Obj->DoAddTransaksiDetilAnggaran(
						 									 $id_transaksi, 
															 $this->_POST['mak']);
					}
				}
				//insert ke table file, tidak wajib
                $files = $_FILES['file_attach'];
                if(!empty($files)) {
					for($i=0;$i<sizeof($files['name']);$i++) {
						if(!$files['name'][$i]) continue;
						$ext = end(explode(".", $files['name'][$i]));
						$namafile[] = date("Y-m-d_H-i-s_") . substr(md5(microtime()), 0, 3) . "." . $ext;
					}
					if(!empty($namafile)) {
						$transaksi_detil_gaji = $this->Obj->DoAddTransaksiFile(
																$id_transaksi, 
																$namafile, 
																"file/transaksi/");
						for($i=0;$i<sizeof($namafile);$i++) {
							if(!$files['name'][$i]) continue;
							@move_uploaded_file($files['tmp_name'][$i], "file/transaksi/" . $namafile[$i]);
						}
						if($transaksi_detil_gaji === false) {
							Messenger::Instance()->Send(
														'transaksi_realisasi', 
														'detilTransaksi', 
														'view', 
														'html',
														 array(
														 		$this->_POST,
														        'Gagal Menambah Data Transaksi Detil Gaji',
															    $this->cssFail),
																Messenger::NextRequest);
						}
					}
                }
			    //table transaksi_invoice, tidak wajib
                $arrInvoice = $this->_POST['no_invoice_list'];
                if(!empty($arrInvoice)) {
					$transaksi_invoice = $this->Obj->DoAddTransaksiInvoice($id_transaksi, $arrInvoice);
                }

                //skenario
				if($this->_POST['skenario'] == "auto") {
                  //KETERANGAN DIISI KOSONG
					$id_pembukuan = $this->Obj->DoAddPembukuan($id_transaksi, $this->arrData['transUserId']);
					$detil_pembukuan = $this->Obj->DoAddPembukuanDetil(
															$id_pembukuan, 
															$this->_POST['nominal'], 
															$this->_POST['skenario_list']
															);
				}

				#update status istransaksi untuk pencairan realisasi
				if($this->_POST['peng_real_id'] != '')
					$update_status_transaksi = $this->Obj->DoUpdateStatusTransaksiDiPengajuanRealisasi(
																$this->_POST['peng_real_id']);
				//if($transaksi_detil_gaji === true) {
				Messenger::Instance()->Send(
											'transaksi_realisasi', 
											'transaksiDetil', 
											'view', 
											'html', 
											array(
													$this->_POST, 
													'Penambahan Data Transaksi Berhasil Dilakukan', 
													$this->cssDone),
											Messenger::NextRequest);	
				return $this->pageInputDetil. '&dataId=' .$this->Obj->GetLastInsertTransId();
			}

		} elseif($cek === "exists") {
			Messenger::Instance()->Send(
										'transaksi_realisasi', 
										'Transaksi', 
										'view', 
										'html', 
										array(
												$this->_POST,
												'Transaksi Dengan Nomor <b>'.
												($this->_POST['no_kkb']).'</b> Sudah Dibuat', 
												$this->cssFail),
										Messenger::NextRequest);
			return $this->pageView;
		} else {
			 //gagal masukin data
			Messenger::Instance()->Send(
										'transaksi_realisasi', 
										'detilTransaksi', 
										'view', 
										'html', 
										array(
												$this->_POST,
												'Gagal Menambah Data', 
												$this->cssFail),
										Messenger::NextRequest);
		}

		return $this->pageDetil;
	}

	function Update() {
		$cek = $this->Check();
        //$cek = true;
		if($cek === true) {
			$this->setData('update');
            $upd_transaksi = $this->Obj->DoUpdateTransaksi($this->arrData);
            if($upd_transaksi === true) {
               //jika jenis transaksi == Anggaran, insert MAK ke table transaksi_detail_anggaran
               //relasi dengan detil anggaran adalah 1-1,
               /*kasusnya :
                  1. bukan anggaran ~ bukan anggaran //tidak usah dibikin
                  2. bukan anggaran ~ anggaran
                  3. anggaran ~ bukan anggaran
                  4. anggaran ~ anggaran

               */
               if(!$this->_POST['mak_lama'] && $this->_POST['mak']) {
                  //kasus 2, insert
                  $this->Obj->DoAddTransaksiDetilAnggaran($this->arrData['transId'], $this->_POST['mak']);
               } elseif($this->_POST['mak_lama'] && !$this->_POST['mak']) {
                  //kasus 3, hapus
                  $this->Obj->DoDeleteTransaksiDetilAnggaran($this->_POST['mak_lama_id']);
               } elseif($this->_POST['mak_lama'] && 
			   			$this->_POST['mak'] && $this->_POST['mak_lama'] != $this->_POST['mak']) {
                  //kasus 4, update
                  $this->Obj->DoUpdateTransaksiDetilAnggaran($this->arrData['transId'], $this->_POST['mak']);
               }
               //add table transaksi_invoice, tidak wajib
               $arrInvoice = $this->_POST['no_invoice_list'];
               if(!empty($arrInvoice)) {
                  $transaksi_invoice = $this->Obj->DoAddTransaksiInvoice($this->arrData['transId'], $arrInvoice);
               }

               //delete table transaksi_invoice
               $arrInvoiceDelete = $this->_POST['no_invoice_list_delete'];
               if(!empty($arrInvoiceDelete)) {
                  $transaksi_invoice_delete = $this->Obj->DoDeleteTransaksiInvoice($arrInvoiceDelete);
               }

               /*
               kasusnya :
               1. gambar dihapus
               2. gambar ditambah
               */
               //kasus 1
               if(!empty($this->_POST['file_attach_delete'])) {
                  $this->Obj->DoDeleteTransaksiFile($this->_POST['file_attach_delete']);
                  for($i=0;$i<sizeof($this->_POST['file_attach_delete']);$i++) {
                     @unlink("file/transaksi/" . $this->_POST['file_attach_delete_nama'][$i]);
                  }
               }
               //kasus 2
               //insert ke table file, tidak wajib
               $files = $_FILES['file_attach'];
               if(!empty($files)) {
                  for($i=0;$i<sizeof($files['name']);$i++) {
                     if(!$files['name'][$i]) continue;
                     $ext = end(explode(".", $files['name'][$i]));
                     $namafile[] = date("Y-m-d_H-i-s_") . substr(md5(microtime()), 0, 3) . "." . $ext;
                  }
                  if(!empty($namafile)) {
                     $transaksi_detil_gaji = $this->Obj->DoAddTransaksiFile(
					 											 $this->arrData['transId'], 
																 $namafile, 
																 "file/transaksi/");
                     for($i=0;$i<sizeof($namafile);$i++) {
                        if(!$files['name'][$i]) continue;
                        @move_uploaded_file($files['tmp_name'][$i], "file/transaksi/" . $namafile[$i]);
                     }
                  }
               }

               //skenario
               if($this->_POST['skenario'] == "auto") {
                  //KETERANGAN DIISI KOSONG
                  $id_pembukuan = $this->Obj->DoAddPembukuan(
				  										  $this->arrData['transId'], 
														  $this->arrData['transUserId']);
                  $detil_pembukuan = $this->Obj->DoAddPembukuanDetil(
			  											  $id_pembukuan, 
													      $this->_POST['nominal'], 
														  $this->_POST['skenario_list']);
               }

            Messenger::Instance()->Send(
											'transaksi_realisasi', 
											'transaksiDetil', 
											'view', 
											'html', 
											array(
													$this->_POST, 
													'Perubahan Data Transaksi Berhasil Dilakukan', 
													$this->cssDone),
											Messenger::NextRequest);
				return $this->pageInputDetil. '&dataId=' .$this->decId;
		
            }
         //}
      } elseif($cek == "exists") {
         Messenger::Instance()->Send(
		 								'transaksi_realisasi', 
										 'Transaksi', 
										 'view', 
										 'html', 
										 array(
										 		$this->_POST,
												 'Transaksi Dengan Nomor <b>'.
												 ($this->_POST['no_kkb']).'</b> Sudah Dibuat', 
										 $this->cssFail),
								 		Messenger::NextRequest);
								 		
		 return $this->pageView . '&dataId=' . $this->encId;
      } else {
         //gagal masukin data
         Messenger::Instance()->Send(
		 							 'transaksi_realisasi', 
									 'detilTransaksi', 
									 'view', 
									 'html', 
									 array(
									 		$this->_POST,
											 'Gagal Merubah Data Transaksi', 
											 $this->cssFail),
									 Messenger::NextRequest);
      }
		return $this->pageDetil;
	}

	function Delete() {
		$arrId = $this->_POST['idDelete'];
      $deleteArrData = $this->Obj->DoDeleteDataById($arrId);
		if($deleteArrData === true) {
			Messenger::Instance()->Send(
										'history_transaksi', 
										'HTRealisasiPencairan',  
										'view', 
										'html', 
										array(
											$this->_POST,
											'Penghapusan Data Berhasil Dilakukan', 
											$this->cssDone),
										Messenger::NextRequest);
		} else {
			Messenger::Instance()->Send(
										'history_transaksi', 
										'HTRealisasiPencairan',  
										'view', 
										'html',
									    array(
												$this->_POST, 
												$gagal . ' Data Tidak Dapat Dihapus.', 
												$this->cssFail),
										Messenger::NextRequest);
		}
		return $this->pageDetil;
	}
}
