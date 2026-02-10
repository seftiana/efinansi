<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
'module/kinerja_kegiatan/business/KinerjaKegiatan.class.php';

class ViewInputIndikatorKegiatan extends HtmlResponse {

function TemplateModule() {
	$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot') . 
	'module/kinerja_kegiatan/template');
	$this->SetTemplateFile('input_indikator_kegiatan.html');
}
	
function ProcessRequest() {
	$idDec 				= Dispatcher::Instance()->Decrypt($_REQUEST['dataId']);
	$Obj 				= new KinerjaKegiatan();
	$msg 				= Messenger::Instance()->Receive(__FILE__);
	$return['Pesan'] 	= $msg[0][1];
	$return['Data'] 	= $msg[0][0];

	$data 				= $Obj->GetKinerjaKegiatanById($idDec);

	$return['decDataId'] 	= $idDec;
	$return['dataK'] 		= $data;
	
	return $return;
}

function ParseTemplate($data = NULL) {
	if($data['Pesan']){
		$this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
		$this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $data['Pesan']);
	}
	$dataK 	= $data['dataK'];
		
	if($_REQUEST['dataId']==''){
		$tambah	= "Tambah";
	}else{
		$tambah	= "Ubah";
	}
	
	$url_popup_kegiatan	= Dispatcher::Instance()->
						  GetUrl('kinerja_kegiatan','PopupKegiatan','view','html');
	$this->mrTemplate->AddVar('content','URL_POPUP_KEGIATAN',$url_popup_kegiatan);
	$this->mrTemplate->AddVar('content', 'JUDUL', $tambah);
	$this->mrTemplate->AddVar('content', 'NAMA', 
		   empty($dataK[0]['nama'])?$data['Data']['nama']:$dataK[0]['nama']);
    $this->mrTemplate->AddVar('content', 'KODE', 
		   empty($dataK[0]['kode'])?$data['Data']['kode']:$dataK[0]['kode']);
		
	$this->mrTemplate->AddVar('content', 'URL_ACTION', 
	Dispatcher::Instance()->
	GetUrl('kinerja_kegiatan', 'inputIndikatorKegiatan', 'do', 'html') . 
	"&dataId=" . Dispatcher::Instance()->Encrypt($data['decDataId']));
      
	$this->mrTemplate->AddVar('content', 'ID', Dispatcher::Instance()->Decrypt($_GET['dataId']));
	
	$this->mrTemplate->AddVar('content','KODE_KEGIATAN',
		   empty($dataK[0]['kode_kegiatan'])
		   ?$data['Data']['kode_kegiatan']:$dataK[0]['kode_kegiatan']);
	$this->mrTemplate->AddVar('content','ID_KEGIATAN',
		   empty($dataK[0]['id_kegiatan'])?$data['Data']['id_kegiatan']:$dataK[0]['id_kegiatan']);
	//$this->mrTemplate->AddVar('content', 'PAGE', Dispatcher::Instance()->
	//Decrypt($_GET['page']));
}
}
?>
