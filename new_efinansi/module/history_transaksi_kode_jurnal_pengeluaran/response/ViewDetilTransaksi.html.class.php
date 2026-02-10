<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
	'module/history_transaksi_kode_jurnal_pengeluaran/business/AppDetilTransaksi.class.php';

class ViewDetilTransaksi extends HtmlResponse {

	var $Pesan;

	function TemplateModule() {
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot') . 
		'module/history_transaksi_kode_jurnal_pengeluaran/template');
		$this->SetTemplateFile('view_detil_transaksi.html');
	}
	
	function ProcessRequest() {
		$Obj = new AppDetilTransaksi();
      //$decDataId = Dispatcher::Instance()->Decrypt($_GET['dataId']);
      if(isset($_POST['mulai_day'])) {
         $decMulaiTanggal = $_POST['mulai_day'];
         $decMulaiBulan = $_POST['mulai_mon'];
         $decMulaiTahun = $_POST['mulai_year'];

         $decSelesaiTanggal = $_POST['selesai_day'];
         $decSelesaiBulan = $_POST['selesai_mon'];
         $decSelesaiTahun = $_POST['selesai_year'];

      } elseif(isset($_GET['mulai_day'])) {
         $decMulaiTanggal = Dispatcher::Instance()->Decrypt($_GET['mulai_day']);
         $decMulaiBulan = Dispatcher::Instance()->Decrypt($_GET['mulai_mon']);
         $decMulaiTahun = Dispatcher::Instance()->Decrypt($_GET['mulai_year']);

         $decSelesaiTanggal = Dispatcher::Instance()->Decrypt($_GET['selesai_day']);
         $decSelesaiBulan = Dispatcher::Instance()->Decrypt($_GET['selesai_mon']);
         $decSelesaiTahun = Dispatcher::Instance()->Decrypt($_GET['selesai_year']);

      } else {
         $decMulaiTanggal = date("01");
         $decMulaiBulan = date("01");
         $decMulaiTahun = date("Y");

         $decSelesaiTanggal = date("d");
         $decSelesaiBulan = date("m");
         $decSelesaiTahun = date("Y");
      }
      $mulai_selected = $decMulaiTahun . "-" . $decMulaiBulan . "-" . $decMulaiTanggal;
      $selesai_selected = $decSelesaiTahun . "-" . $decSelesaiBulan . "-" . $decSelesaiTanggal;

	//view
		$itemViewed = 20;
		$currPage = 1;
		$startRec = 0 ;
		if(isset($_GET['page'])) {
			$currPage = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
			$startRec =($currPage-1) * $itemViewed;
		}
		$dataDetilTransaksi = $Obj->getData($startRec, $itemViewed, $mulai_selected, $selesai_selected);
		$totalData = $Obj->GetCountData();

		$url = Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule, Dispatcher::Instance()->mSubModule, Dispatcher::Instance()->mAction, Dispatcher::Instance()->mType . '&mulai_day=' . Dispatcher::Instance()->Encrypt($decMulaiTanggal) . '&mulai_mon=' . Dispatcher::Instance()->Encrypt($decMulaiBulan) . '&mulai_year=' . Dispatcher::Instance()->Encrypt($decMulaiTahun) . '&selesai_day=' . Dispatcher::Instance()->Encrypt($decSelesaiTanggal) . '&selesai_mon=' . Dispatcher::Instance()->Encrypt($decSelesaiBulan) . '&selesai_year=' . Dispatcher::Instance()->Encrypt($decSelesaiTahun) . '&cari=' . Dispatcher::Instance()->Encrypt(1));

		Messenger::Instance()->SendToComponent('paging', 'Paging', 'view', 'html', 'paging_top', array($itemViewed,$totalData, $url, $currPage), Messenger::CurrentRequest);

		$msg = Messenger::Instance()->Receive(__FILE__);
		$this->Pesan = $msg[0][1];
		$this->css = $msg[0][2];

      $tahun['start'] = date("Y")-5;
      $tahun['end'] = date("Y")+5;
      Messenger::Instance()->SendToComponent('tanggal', 'Tanggal', 'view', 'html', 'mulai', array($mulai_selected, $tahun['start'], $tahun['end'], '', '', 'mulai'), Messenger::CurrentRequest);
      Messenger::Instance()->SendToComponent('tanggal', 'Tanggal', 'view', 'html', 'selesai', array($selesai_selected, $tahun['start'], $tahun['end'], '', '', 'selesai'), Messenger::CurrentRequest);
		
		$return['dataDetilTransaksi'] = $dataDetilTransaksi;
		$return['start'] = $startRec+1;

      return $return;
	}

	function ParseTemplate($data = NULL) {
		$this->mrTemplate->AddVar('content', 'URL_SEARCH', Dispatcher::Instance()->GetUrl('history_transaksi_kode_jurnal_pengeluaran', 'detilTransaksi', 'view', 'html'));
		$this->mrTemplate->AddVar('content', 'URL_KEMBALI', Dispatcher::Instance()->GetUrl('history_transaksi_kode_jurnal_pengeluaran', 'Transaksi', 'view', 'html'));
		if($this->Pesan) {
			$this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
			$this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->Pesan);
			$this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $this->css);
		}

		if (empty($data['dataDetilTransaksi'])) {
			$this->mrTemplate->AddVar('data', 'DATA_EMPTY', 'YES');
		} else {
			//$decPage = Dispatcher::Instance()->Decrypt($_REQUEST['page']);
			//$encPage = Dispatcher::Instance()->Encrypt($decPage);
			$this->mrTemplate->AddVar('data', 'DATA_EMPTY', 'NO');
         
         //untuk confirm delete
         $label = 'Transaksi';
         $urlDelete = 'history_transaksi_kode_jurnal_pengeluaran|deleteTransaksi|do|html';
         $urlReturn = 'history_transaksi_kode_jurnal_pengeluaran|detilTransaksi|view|html';
         $URLDELETE = Dispatcher::Instance()->GetUrl('history_transaksi_kode_jurnal_pengeluaran', 'deleteTransaksi', 'do', 'html');

//mulai bikin tombol delete
			#$label = "Transaksi";
			#$urlDelete = Dispatcher::Instance()->GetUrl('transaksi_kode_jurnal', 'deleteTransaksi', 'do', 'html');
			#$urlReturn = Dispatcher::Instance()->GetUrl('transaksi_kode_jurnal', 'detilTransaksi', 'view', 'html');
			#Messenger::Instance()->Send('confirm', 'confirmDelete', 'do', 'html', array($label, $urlDelete, $urlReturn),Messenger::NextRequest);
			#$this->mrTemplate->AddVar('content', 'URL_DELETE', Dispatcher::Instance()->GetUrl('confirm', 'confirmDelete', 'do', 'html'));

			$dataDetilTransaksi = $data['dataDetilTransaksi'];
		   $arr_periode = array();
         $periode = "";
         $j=0; #print_r($dataDetilTransaksi);
			for ($i=0; $i<sizeof($dataDetilTransaksi); $i++) {
            $idEnc = Dispatcher::Instance()->Encrypt($dataDetilTransaksi[$i]['id']);  
            if($dataDetilTransaksi[$i]['tanggal'] != $periode) {
               $arr_periode[$j]['periode'] = $dataDetilTransaksi[$i]['tanggal'];
               $periode = $dataDetilTransaksi[$i]['tanggal'];
               $j++;
            }
            #untuk cetak bkk bkm bm
            #peneriman / bkm
            if($dataDetilTransaksi[$i]['tipe_id'] == 1) {
               $dataDetilTransaksi[$i]['url_cetak_bukti'] = Dispatcher::Instance()->GetUrl('history_transaksi_kode_jurnal_pengeluaran', 'FormCetakBKM', 'view', 'html') . '&dataId=' . $idEnc . '&tipe=bkm';
               $dataDetilTransaksi[$i]['label_cetak_bukti'] = 'Cetak BKM';
               #$url_cetak_bukti = Dispatcher::Instance()->GetUrl('transaksi_kode_jurnal', 'CetakBuktiTransaksi', 'view', 'html') . '&dataId=' . $idEnc . '&tipe=bkm';
               #$dataDetilTransaksi[$i]['url_cetak_bukti'] = '<a href="javascript:void(0)" onclick="bukaPopupCetak(\''.$url_cetak_bukti.'\')" title="Cetak BKM"><img src="images/button-print.gif" alt="Cetak BKM"/></a>';
            }elseif($dataDetilTransaksi[$i]['tipe_id'] == 2) { // bkk pengeluaran
               $dataDetilTransaksi[$i]['url_cetak_bukti'] = Dispatcher::Instance()->GetUrl('history_transaksi_kode_jurnal_pengeluaran', 'FormCetakBKK', 'view', 'html') . '&dataId=' . $idEnc . '&tipe=bkk';
               $dataDetilTransaksi[$i]['label_cetak_bukti'] = 'Cetak BKK';
               #$url_cetak_bukti = Dispatcher::Instance()->GetUrl('transaksi_kode_jurnal', 'CetakBuktiTransaksi', 'view', 'html') . '&dataId=' . $idEnc . '&tipe=bkk';
               #$dataDetilTransaksi[$i]['url_cetak_bukti'] = '<a href="javascript:void(0)" onclick="bukaPopupCetak(\''.$url_cetak_bukti.'\')" title="Cetak BKK"><img src="images/button-print.gif" alt="Cetak BKK"/></a>';
            }elseif($dataDetilTransaksi[$i]['tipe_id'] == 3) { // bm umum
               $dataDetilTransaksi[$i]['url_cetak_bukti'] = Dispatcher::Instance()->GetUrl('history_transaksi_kode_jurnal_pengeluaran', 'FormCetakBM', 'view', 'html') . '&dataId=' . $idEnc . '&tipe=bm';
               $dataDetilTransaksi[$i]['label_cetak_bukti'] = 'Cetak BM';
               #$url_cetak_bukti = Dispatcher::Instance()->GetUrl('transaksi_kode_jurnal', 'CetakBuktiTransaksi', 'view', 'html') . '&dataId=' . $idEnc . '&tipe=bkk';
               #$dataDetilTransaksi[$i]['url_cetak_bukti'] = '<a href="javascript:void(0)" onclick="bukaPopupCetak(\''.$url_cetak_bukti.'\')" title="Cetak BKK"><img src="images/button-print.gif" alt="Cetak BKK"/></a>';
            }
				$no = $i+$data['start'];
				$dataDetilTransaksi[$i]['number'] = $no;
				if($i == 0) $this->mrTemplate->AddVar('content', 'FIRST_NUMBER', $no);				
				if($i == sizeof($dataDetilTransaksi)-1) $this->mrTemplate->AddVar('content', 'LAST_NUMBER', $no);
				if ($no % 2 != 0) $dataDetilTransaksi[$i]['class_name'] = 'table-common-even';
				else $dataDetilTransaksi[$i]['class_name'] = '';

            $dataDetilTransaksi[$i]['tanggal'] = $this->date2string($dataDetilTransaksi[$i]['tanggal']);
            $dataDetilTransaksi[$i]['nominal_label'] = number_format($dataDetilTransaksi[$i]['nominal'], 2, ',', '.');

				$dataDetilTransaksi[$i]['url_cetak'] = Dispatcher::Instance()->GetUrl('history_transaksi_kode_jurnal_pengeluaran', 'FormCetakTransaksi', 'view', 'html') . '&dataId=' . $idEnc;
            if($dataDetilTransaksi[$i]['is_jurnal'] == "Y") {
               $dataDetilTransaksi[$i]['url_edit'] = '';
               $dataDetilTransaksi[$i]['url_delete'] = '';
               /*$dataDetilTransaksi[$i]['url_delete'] = Dispatcher::Instance()->GetUrl('confirm', 'confirmDelete', 'do', 'html') .
            '&urlDelete='. $urlDelete.'&urlReturn='.$urlReturn.
            '&id='.Dispatcher::Instance()->Encrypt($dataDetilTransaksi[$i]['id']).
            '&label='.$label.'&dataName='.$dataDetilTransaksi[$i]['kkb'];*/
            } else {
               $url_deletee = Dispatcher::Instance()->GetUrl('confirm', 'confirmDelete', 'do', 'html') .
            '&urlDelete='. $urlDelete.'&urlReturn='.$urlReturn.
            '&id='.Dispatcher::Instance()->Encrypt($dataDetilTransaksi[$i]['id']).
            '&label='.$label.'&dataName='.$dataDetilTransaksi[$i]['kkb']; 
               $url_edit = Dispatcher::Instance()->GetUrl('history_transaksi_kode_jurnal_pengeluaran', 'Transaksi', 'view', 'html') . '&dataId=' . $idEnc;
				   $dataDetilTransaksi[$i]['url_edit'] = '<a class="xhr dest_subcontent-element" href="'.$url_edit.'" title="Edit"><img src="images/button-edit.gif" alt="Edit"/></a>';
               
               $dataDetilTransaksi[$i]['url_delete'] = '<a class="xhr dest_subcontent-element" onClick="javascript: return showBoxConfirmDelete("'.$dataDetilTransaksi[$i]['id'].'", "'.$dataDetilTransaksi[$i]['kkb'].'", "'.$URLDELETE.'");" href="'.$url_deletee.'" title="Hapus"><img src="images/button-delete.gif" alt="Hapus"/></a>';
               
            #$this->mrTemplate->AddVar("data_item", "IDDELETE", $data['fasilitas'][$i]['fasilitas_id']);
            $dataDetilTransaksi[$i]['iddelete'] = $dataDetilTransaksi[$i]['id'];
            #$this->mrTemplate->AddVar("data_item", "URLDELETE", $URLDELETE);
            $dataDetilTransaksi[$i]['urldelete'] = $URLDELETE;
            /*$dataDetilTransaksi[$i]['url_delete'] = Dispatcher::Instance()->GetUrl('confirm', 'confirmDelete', 'do', 'html') .
            '&urlDelete='. $urlDelete.'&urlReturn='.$urlReturn.
            '&id='.Dispatcher::Instance()->Encrypt($dataDetilTransaksi[$i]['id']).
            '&label='.$label.'&dataName='.$dataDetilTransaksi[$i]['kkb'];*/
            /*$this->mrTemplate->AddVar("data_item", "URL_DELETE", Dispatcher::Instance()->GetUrl('confirm', 'confirmDelete', 'do', 'html') .
            '&urlDelete='. $urlDelete.'&urlReturn='.$urlReturn.
            '&id='.Dispatcher::Instance()->Encrypt($data['fasilitas'][$i]['fasilitas_id']).
            '&label='.$label.'&dataName='.$data['fasilitas'][$i]['fasilitas_nama']);*/
            }

				$this->mrTemplate->AddVars('data_item', $dataDetilTransaksi[$i], 'DATA_');
				$this->mrTemplate->parseTemplate('data_item', 'a');	 
			}
         //print_r($arr_periode);
         for($j=0;$j<sizeof($arr_periode);$j++) {
				$this->mrTemplate->AddVar('periode_item', 'LIST_PERIODE', $this->date2string($arr_periode[$j]['periode']));
				$this->mrTemplate->parseTemplate('periode_item', 'a');	          
         }
		}
	}
	
	function date2string($date) {
	   $bln = array(
	                1  => 'Januari',
					2  => 'Februari',
					3  => 'Maret',
					4  => 'April',
					5  => 'Mei',
					6  => 'Juni',
					7  => 'Juli',
					8  => 'Agustus',
					9  => 'September',
					10 => 'Oktober',
					11 => 'November',
					12 => 'Desember'					
	               );
	   $arrtgl = explode('-',$date);
	   return $arrtgl[2].'&nbsp;'.$bln[(int) $arrtgl[1]].'&nbsp;'.$arrtgl[0];
	   
	}
}
?>