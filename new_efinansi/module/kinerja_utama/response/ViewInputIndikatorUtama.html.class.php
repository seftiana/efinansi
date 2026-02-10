<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
'module/kinerja_utama/business/KinerjaUtama.class.php';

class ViewInputIndikatorUtama extends HtmlResponse {

	function TemplateModule() {
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot') . 
		'module/kinerja_utama/template');
		$this->SetTemplateFile('input_indikator_utama.html');
	}
	
	function ProcessRequest() {
		$idDec = Dispatcher::Instance()->Decrypt($_REQUEST['dataId']);
		$Obj = new KinerjaUtama();
		$msg = Messenger::Instance()->Receive(__FILE__);
		$return['Pesan'] = $msg[0][1];
		$return['Data'] = $msg[0][0];

		$data = $Obj->GetKinerjaUtamaById($idDec);

		$return['decDataId'] = $idDec;
		$return['dataK'] = $data;
		return $return;
	}

	function ParseTemplate($data = NULL) {
		if ($data['Pesan']) {
			$this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
			$this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $data['Pesan']);
		}
		$dataK = $data['dataK'];
		
		if ($_REQUEST['dataId']=='') {
			$tambah="Tambah";
		} else {
			$tambah="Ubah";
		}
		
		$this->mrTemplate->AddVar('content', 'JUDUL', $tambah);
		$this->mrTemplate->AddVar('content', 'NAMA', 
		empty($dataK[0]['nama'])?$data['Data']['nama']:$dataK[0]['nama']);
      	$this->mrTemplate->AddVar('content', 'KODE', 
	  	empty($dataK[0]['kode'])?$data['Data']['kode']:$dataK[0]['kode']);
	  	$this->mrTemplate->AddVar('content', 'PROGRAM', 
	  	empty($dataK[0]['kode_program'])?$data['Data']['kode_program']:$dataK[0]['kode_program']);
		$this->mrTemplate->AddVar('content', 'PROGRAM_ID', 
	  	empty($dataK[0]['id_program'])?$data['Data']['id_program']:$dataK[0]['id_program']);
		
		
		$this->mrTemplate->AddVar('content', 'URL_ACTION', 
		Dispatcher::Instance()->GetUrl('kinerja_utama', 'inputIndikatorUtama', 'do', 'html') . 
		"&dataId=" . Dispatcher::Instance()->Encrypt($data['decDataId']));
      
		$this->mrTemplate->AddVar('content', 'ID', 
		Dispatcher::Instance()->Decrypt($_GET['dataId']));
		
		$url_popup	= Dispatcher::Instance()->GetUrl('kinerja_utama','PopupProgram',
					  'view','html');
		$this->mrTemplate->AddVar('content','URL_POPUP',$url_popup);
		//$this->mrTemplate->AddVar('content', 'PAGE', Dispatcher::Instance()->Decrypt($_GET['page']));
	}
}
?>
