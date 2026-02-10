<?php

require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/daftar_pinjaman/business/AppPinjaman.class.php';

class ViewInputPinjaman extends HtmlResponse{
	function TemplateModule() {
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/daftar_pinjaman/template');
		$this->SetTemplateFile('input_pinjaman.html');
	}

   function ProcessRequest() {
		$propObj = new AppPinjaman();
		
		$decId = Dispatcher::Instance()->Decrypt($_REQUEST['dataId']);
		$dataPinjaman = $propObj->GetDataPinjamanById($decId);
		$msg = Messenger::Instance()->Receive(__FILE__);
      
		$return['pesan']=$msg[0][1];
		$return['data']=$msg[0][0];
		$return['decDataId'] = $decId;
		$return['dataPinjaman'] = $dataPinjaman;
		return $return;
	}

   function ParseTemplate($data = NULL) {
		$dataPinjaman=$data['dataPinjaman'];
     //print_r($dataPinjaman);
		$this->mrTemplate->AddVar('status_koneksi', 'BERHASIL', 'YES');
		
		if ($data['pesan']) {
			$this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
			$this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $data['pesan']);
		}else{
      			$this->mrTemplate->SetAttribute('warning_box', 'visibility', 'hidden');
		}

		if ($_REQUEST['dataId']=='') {
			$url="addPinjaman";
			$tambah="Tambah";
		} else {
			$url="updatePinjaman";
			$tambah="Ubah";
		}

		$this->mrTemplate->AddVar('status_koneksi', 'JUDUL', $tambah);
		$this->mrTemplate->AddVar('status_koneksi', 'PINJAMAN_KODE', empty($dataPinjaman[0]['pinjaman_kode'])?$data['data']['pinjaman_kode']:$dataPinjaman[0]['pinjaman_kode']);
		$this->mrTemplate->AddVar('status_koneksi', 'PINJAMAN_NAMA', empty($dataPinjaman[0]['pinjaman_nama'])?$data['data']['pinjaman_nama']:$dataPinjaman[0]['pinjaman_nama']);
		$this->mrTemplate->AddVar('status_koneksi', 'PINJAMAN_JUMLAH', empty($dataPinjaman[0]['pinjaman_jumlah'])?$data['data']['pinjaman_jumlah']:$dataPinjaman[0]['pinjaman_jumlah']);
		$this->mrTemplate->AddVar('status_koneksi', 'PINJAMAN_ANGSURAN', empty($dataPinjaman[0]['pinjaman_angsuran'])?$data['data']['pinjaman_angsuran']:$dataPinjaman[0]['pinjaman_angsuran']);
		
		$this->mrTemplate->AddVar('status_koneksi', 'URL_ACTION', Dispatcher::Instance()->GetUrl('daftar_pinjaman', $url, 'do','html') . "&dataId=" . Dispatcher::Instance()->Encrypt($data['decDataId']));
		$this->mrTemplate->AddVar('status_koneksi', 'DATAID', Dispatcher::Instance()->Decrypt($_GET['dataId']));
		$this->mrTemplate->AddVar('status_koneksi', 'PAGE', Dispatcher::Instance()->Decrypt($_GET['page']));

	}
}
?>
