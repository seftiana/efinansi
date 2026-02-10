<?php

require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
		'module/history_transaksi_penerimaan/business/AppTransaksi.class.php';
		
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
	'module/generate_number/business/GenerateNumber.class.php';
	
class ProcessTransaksi {

	var $_POST;
	var $Obj;
	var $pageView;
	var $pageInput;
	var $pageInputDetil;
	
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
		$this->pageView = Dispatcher::Instance()->GetUrl(
										'history_transaksi_penerimaan', 
										'HTFormPenerimaan', 
										'view', 
										'html');
										
		$this->pageDetil = Dispatcher::Instance()->GetUrl(
									'history_transaksi_penerimaan', 
									'HTPenerimaan', 
									'view', 
									'html');
									
		$this->pageInputDetil = Dispatcher::Instance()->GetUrl(
									 'history_transaksi_penerimaan',
									 'HTFormPenerimaanDetil', 
									 'view', 
									 'html');
		
		$this->userId =trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
		/**
		 * untuk proses generate number bukti transaksi
		 */
		$this->generateNumber = new GenerateNumber();
      	/**
      	 * end
      	 */
      	
	}

	function Check() {
		if (isset($_POST['btnsimpan'])) { 
         if($this->decId != '') {
            $cek = $this->Obj->CekTransaksiUpdate($this->_POST['no_kkb'], $this->decId);
         } else {
            $cek = $this->Obj->CekTransaksi($this->_POST['no_kkb']);
         }
         if($cek === false) {
            return "exists";
         }
         return true;
		}
		return false;
	}

	function Update() {
		$cek = $this->Check();
      $cek = true;
		if($cek === true) {
		
         //print_r($this->_POST); //exit;  
         //if(empty($this->_POST['no_invoice_list']) AND empty($this->_POST['no_invoice_list_edit'])) {
           // Messenger::Instance()->Send('transaksi', 'Transaksi', 'view', 'html', array($this->_POST,'Nomor Ivoice harap diisi', $this->cssAlert),Messenger::NextRequest);
            //return $this->pageView . '&dataId=' . $this->encId . '&ascomponent=1';
            #$tes = $this->pageView . '&dataId=' . $this->encId;
            #echo 'sini '.$tes; exit;
         //}else{
            $arrData['transUnitkerjaId'] = $this->_POST['unitkerja'];
            $arrData['transTransjenId'] = $this->_POST['jenis_transaksi'];
            $arrData['transTtId'] = $this->_POST['tipe_transaksi'];
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
            
            $arrData['transReferensi'] = $noBuktiTransaksi;// $this->_POST['no_kkb'];
            $arrData['transUserId'] = $this->userId;
            $arrData['transDueDate'] = $this->_POST['due_date_year'] . "-" . 
										$this->_POST['due_date_mon'] . "-" . 
										$this->_POST['due_date_day'];
            $arrData['transTanggal'] = $this->_POST['tanggal_transaksi_year'] . "-" . 
										$this->_POST['tanggal_transaksi_mon'] . "-" . 
										$this->_POST['tanggal_transaksi_day'];
            $arrData['transCatatan'] = $this->_POST['catatan_transaksi'];
            $arrData['transPenanggungJawabNama'] = $this->_POST['penanggung_jawab'];
            $arrData['transNilai'] = $this->_POST['nominal'];
            if($this->_POST['skenario'] == "auto") {
               $arrData['transIsJurnal'] = "Y";
            } else {
               $arrData['transIsJurnal'] = "T";
            }
            $arrData['transId'] = $this->decId;
            //echo "<pre>";
            //print_r($this->_POST);
            //print_r($_FILES);
            //echo "</pre>";
            //exit();
            //echo "<pre>";
            $upd_transaksi = $this->Obj->DoUpdateTransaksi($arrData);
            //echo $arrData['transId'];
            if($upd_transaksi == true) {
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
                  $this->Obj->DoAddTransaksiDetilAnggaran($arrData['transId'], $this->_POST['mak']);
               } elseif($this->_POST['mak_lama'] && !$this->_POST['mak']) {
                  //kasus 3, hapus
                  $this->Obj->DoDeleteTransaksiDetilAnggaran($this->_POST['mak_lama']);
               } elseif($this->_POST['mak_lama'] && $this->_POST['mak'] && $this->_POST['mak_lama'] != $this->_POST['mak']) {
                  //kasus 4, update
                  $this->Obj->DoUpdateTransaksiDetilAnggaran($this->_POST['mak_lama_id'], $this->_POST['mak']);
               }
   
   
               //add table transaksi_invoice, tidak wajib
               $arrInvoice = $this->_POST['no_invoice_list'];
               if(!empty($arrInvoice)) {
                  $transaksi_invoice = $this->Obj->DoAddTransaksiInvoice($arrData['transId'], $arrInvoice);
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
                     $transaksi_detil_gaji = $this->Obj->DoAddTransaksiFile($arrData['transId'], $namafile, "file/transaksi/");
                     for($i=0;$i<sizeof($namafile);$i++) {
                        if(!$files['name'][$i]) continue;
                        @move_uploaded_file($files['tmp_name'][$i], "file/transaksi/" . $namafile[$i]);
                     }
                  }
               }
   
               //skenario
               if($this->_POST['skenario'] == "auto") {
                  //KETERANGAN DIISI KOSONG
                  $id_pembukuan = $this->Obj->DoAddPembukuan($arrData['transId'], $arrData['transUserId']);
                  $detil_pembukuan = $this->Obj->DoAddPembukuanDetil($id_pembukuan, $this->_POST['nominal'], $this->_POST['skenario_list']);
               }
   
               Messenger::Instance()->Send('history_transaksi_penerimaan', 'HTPenerimaanDetil', 'view', 'html', array($this->_POST, 'Perubahan Data Transaksi Berhasil Dilakukan', $this->cssDone),Messenger::NextRequest);
               
            }
            return $this->pageInputDetil.'&dataId=' . $this->decId;
        // }
      } elseif($cek == "exists") {
         //echo "666";
         //echo "exist";
         Messenger::Instance()->Send('history_transaksi_penerimaan', 'HTFormPenerimaan', 'view', 'html', array($this->_POST,'Transaksi Dengan Nomor <b>'.($this->_POST['no_kkb']).'</b> Sudah Dibuat', $this->cssFail),Messenger::NextRequest);
		   return $this->pageView . '&dataId=' . $this->encId;
      } else {
         //gagal masukin data
         //echo "gagal";
         Messenger::Instance()->Send('history_transaksi_penerimaan', 'HTFormPenerimaan', 'view', 'html', array($this->_POST,'Gagal Merubah Data Transaksi', $this->cssFail),Messenger::NextRequest);
		   return $this->pageView . '&dataId=' . $this->encId;
      }
      //echo "adflj";
		return $this->pageView;
	}

	function Delete() {
		$arrId = $this->_POST['idDelete'];
		#print_r($this->_POST); exit;
		#$deleteArrData = $this->Obj->DoDeleteDataByArrayId($arrId);
      $deleteArrData = $this->Obj->DoDeleteDataById($arrId);  
		if($deleteArrData === true) {
			Messenger::Instance()->Send('history_transaksi_penerimaan', 'HTPenerimaan', 'view', 'html', array($this->_POST,'Penghapusan Data Berhasil Dilakukan', $this->cssDone),Messenger::NextRequest);
		} else {
			//jika masuk disini, berarti PASTI ada salah satu atau lebih data yang gagal dihapus
			/*for($i=0;$i<sizeof($arrId);$i++) {
				$deleteData = false;
				$deleteData = $this->Obj->DoDeleteDataById($arrId[$i]);
				if($deleteData === true) $sukses += 1;
				else $gagal += 1;
			}*/
			Messenger::Instance()->Send('history_transaksi_penerimaan', 'HTPenerimaan', 'view', 'html', array($this->_POST, $gagal . ' Data Tidak Dapat Dihapus.', $this->cssFail),Messenger::NextRequest);
		}
		return $this->pageDetil;
	}
}