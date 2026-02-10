<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/rkakl_sub_kegiatan/business/RkaklSubKegiatan.class.php';

class ViewInputRkaklSubKegiatan extends HtmlResponse {

	function TemplateModule() {
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot') . 'module/rkakl_sub_kegiatan/template');
		$this->SetTemplateFile('input_rkakl_sub_kegiatan.html');
	}
	
	function ProcessRequest() {
		$Obj = new RkaklSubKegiatan();
		if(isset($_GET['dataId']) && $_GET['dataId'] != '') {
			$idDec = Dispatcher::Instance()->Decrypt($_GET['dataId']);
			$aksi = 'edit';
		} else {
			$aksi = 'tambah';
		}
		$msg = Messenger::Instance()->Receive(__FILE__);
		$return['Pesan'] = $msg[0][1];
		$return['Data'] = $msg[0][0];

		$data = $Obj->GetRkaklSubKegiatanById($idDec);

		$return['decDataId'] = $idDec;
		$return['aksi'] = $aksi;
		$return['dataK'] = $data;
		return $return;
	}

	function ParseTemplate($data = NULL) {
		if ($data['Pesan']) {
			$this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
			$this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $data['Pesan']);
		}
		$dataK = $data['dataK'];
		
		if ($data['aksi'] == 'tambah') {
			$tambah="Tambah";
			$this->mrTemplate->AddVar('content', 'URL_ACTION', Dispatcher::Instance()->GetUrl('rkakl_sub_kegiatan', 'inputRkaklSubKegiatan', 'do', 'html'));
		} elseif ($data['aksi'] == 'edit') {
			$tambah="Ubah";
			$this->mrTemplate->AddVar('content', 'URL_ACTION', Dispatcher::Instance()->GetUrl('rkakl_sub_kegiatan', 'inputRkaklSubKegiatan', 'do', 'html') . "&dataId=" . Dispatcher::Instance()->Encrypt($data['decDataId']));
		}
		
		$this->mrTemplate->AddVar('content', 'JUDUL', $tambah);
		$this->mrTemplate->AddVar('content', 'ID', $data['decDataId']->mrVariable);
		$this->mrTemplate->AddVar('content', 'NAMA', empty($dataK[0]['nama'])?$data['Data']['nama']:$dataK[0]['nama']);
		$this->mrTemplate->AddVar('content', 'KODE', empty($dataK[0]['kode'])?$data['Data']['kode']:$dataK[0]['kode']);
		
      
		//$this->mrTemplate->AddVar('content', 'PAGE', Dispatcher::Instance()->Decrypt($_GET['page']));
	}
}
?>
