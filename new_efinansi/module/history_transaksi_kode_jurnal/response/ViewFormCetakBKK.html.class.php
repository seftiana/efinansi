<?php

require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
				'module/history_transaksi_kode_jurnal/business/AppTransaksi.class.php';


class ViewFormCetakBKK extends HtmlResponse {
	var $Data;
	var $Pesan;
	
	function TemplateModule() {
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot') . 
		'module/history_transaksi_kode_jurnal/template');
		$this->SetTemplateFile('view_form_cetak_bkk.html');
	}
	
	function ProcessRequest() {
		$idDec = Dispatcher::Instance()->Decrypt($_REQUEST['dataId']);
		$Obj = new AppTransaksi();
		$msg = Messenger::Instance()->Receive(__FILE__);
		$this->Pesan = $msg[0][1];
		$this->Data = $msg[0][0];
		/*
		$arr_pejabat_rektorat = $Obj->GetJabatanNama('rektor');
		Messenger::Instance()->SendToComponent('combobox', 'Combobox', 'view', 'html', 'pejabat_rektorat', array('pejabat_rektorat', $arr_pejabat_rektorat, '', '-', ' id="pejabat_rektorat" style="width:200px;" '), Messenger::CurrentRequest);
      
		$arr_pejabat_bendahara = $Obj->GetJabatanNama('bendahara');
		Messenger::Instance()->SendToComponent('combobox', 'Combobox', 'view', 'html', 'pejabat_bendahara', array('pejabat_bendahara', $arr_pejabat_bendahara, '', '-', ' id="pejabat_bendahara" style="width:200px;" '), Messenger::CurrentRequest);
		*/
		$arr_pejabat_biro_keuangan = $Obj->GetPerjabatRef();
		/**combo box untuk pejabat */
		Messenger::Instance()->SendToComponent(
												'combobox', 
												'Combobox', 
												'view', 
												'html',
												'pejabat_diketahui',
												array(
													'pejabat_diketahui', 
													$arr_pejabat_biro_keuangan, 
													'', 
													'false', 
													' id="pejabat_diketahui" style="width:200px;" '), 
												Messenger::CurrentRequest);
												
		Messenger::Instance()->SendToComponent(
												'combobox', 
												'Combobox', 
												'view', 
												'html',
												'pejabat_dikeluarkan',
												array(
													'pejabat_dikeluarkan',
													$arr_pejabat_biro_keuangan, 
													'', 
													'false', 
													' id="pejabat_dikeluarkan" style="width:200px;" '), 
												Messenger::CurrentRequest);			
																					
		Messenger::Instance()->SendToComponent(
												'combobox', 
												'Combobox', 
												'view', 
												'html',
												'pejabat_diterima',
												array(
													'pejabat_diterima',
													$arr_pejabat_biro_keuangan, 
													'', 
													'false', 
													' id="pejabat_diterima" style="width:200px;" '), 
												Messenger::CurrentRequest);												
		
		
		/** end combo box untuk pejabat */		
		$dataForm = $Obj->GetDataFormCetak($idDec);
		$return['mak'] = $mak;
		$return['decDataId'] = $idDec;
		$return['dataForm'] = $dataForm;
	
		return $return;
	}

	function ParseTemplate($data = NULL) {
		
		if ($this->Pesan) {
			$this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
			$this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->Pesan);
		}
		$this->mrTemplate->AddVar('content', 'URL_ACTION', Dispatcher::Instance()->GetUrl('history_transaksi_kode_jurnal', 'CetakBuktiTransaksi', 'view', 'html') . "&dataId=" . Dispatcher::Instance()->Encrypt($data['decDataId']) . '&tipe=bkk');
		$this->mrTemplate->AddVar('content', 'URL_CETAK', Dispatcher::Instance()->GetUrl('history_transaksi_kode_jurnal', 'CetakBuktiTransaksi', 'view', 'html') . "&dataId=" . Dispatcher::Instance()->Encrypt($data['decDataId']) . '&tipe=bkk');
		

		$this->mrTemplate->AddVar('content', 'URL_BATAL', Dispatcher::Instance()->GetUrl('history_transaksi_kode_jurnal', 'HTKodeJurnal', 'view', 'html'));

		$dataForm = $data['dataForm'];
		//$this->mrTemplate->AddVar('content', 'TAHUN_ANGGARAN', $dataForm['tahun_anggaran']);
		$this->mrTemplate->AddVar('content', 'NOMOR_BUKTI', $dataForm['nomor_bukti']);
		#$this->mrTemplate->AddVar('content', 'JUDUL', 'KUITANSI/BUKTI PEMBAYARAN');
		
		$this->mrTemplate->AddVar('content', 'TERIMA_DARI', 'Pengguna Anggaran'. GTFWConfiguration::GetValue('organization', 'company_name'));
		
		$this->mrTemplate->AddVar('content', 'NILAI', $dataForm['nilai']);
		$this->mrTemplate->AddVar('content', 'NILAI_LABEL', number_format($dataForm['nilai'], 2, '.', ','));
		$this->mrTemplate->AddVar('content', 'NILAI_TERBILANG', $this->terbilang($dataForm['nilai']));
		$this->mrTemplate->AddVar('content', 'UNTUK_PEMBAYARAN', $dataForm['untuk_pembayaran']);
		$this->mrTemplate->AddVar('content', 'PEMBUAT_KWITANSI', $dataForm['pembuat_kwitansi']);
		$this->mrTemplate->AddVar('content', 'NAMA_PEMBUAT_KWITANSI', $dataForm['nama_pembuat_kwitansi']);
		$this->mrTemplate->AddVar('content', 'TGL_PEMBAYARAN', $dataForm['tgl_pembayaran']);
		$this->mrTemplate->AddVar('content', 'PEJABAT_PEMBANTU_REKTOR', $dataForm['pejabat_pembantu_rektor']);
		$this->mrTemplate->AddVar('content', 'PEJABAT_BENDAHARA', $dataForm['pejabat_bendahara']);

		//$this->mrTemplate->AddVar('content', 'DATAID', Dispatcher::Instance()->Decrypt($_GET['dataId']));
	}
	function kekata($x) {
		$x = abs($x);
		$angka = array("", "satu", "dua", "tiga", "empat", "lima",
		"enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas");
		$temp = "";
		if ($x <12) {
			$temp = " ". $angka[$x];
		} else if ($x <20) {
			$temp = $this->kekata($x - 10). " belas";
		} else if ($x <100) {
			$temp = $this->kekata($x/10)." puluh". $this->kekata($x % 10);
		} else if ($x <200) {
			$temp = " seratus" . $this->kekata($x - 100);
		} else if ($x <1000) {
			$temp = $this->kekata($x/100) . " ratus" . $this->kekata($x % 100);
		} else if ($x <2000) {
			$temp = " seribu" . $this->kekata($x - 1000);
		} else if ($x <1000000) {
			$temp = $this->kekata($x/1000) . " ribu" . $this->kekata($x % 1000);
		} else if ($x <1000000000) {
			$temp = $this->kekata($x/1000000) . " juta" . $this->kekata($x % 1000000);
		} else if ($x <1000000000000) {
			$temp = $this->kekata($x/1000000000) . " milyar" . $this->kekata(fmod($x,1000000000));
		} else if ($x <1000000000000000) {
			$temp = $this->kekata($x/1000000000000) . " trilyun" . $this->kekata(fmod($x,1000000000000));
		}
		return $temp;
   }
   
	function terbilang($x, $style=4) {
		if($x<0) {
			$hasil = "minus ". trim($this->kekata($x));
		} else {
			$hasil = trim($this->kekata($x));
		}
		switch ($style) {
			case 1:
				$hasil = strtoupper($hasil);
				break;
			case 2:
				$hasil = strtolower($hasil);
				break;
			case 3:
				$hasil = ucwords($hasil);
				break;
			default:
				$hasil = ucfirst($hasil);
				break;
		}
		return $hasil.' Rupiah' ;
	}
}
?>