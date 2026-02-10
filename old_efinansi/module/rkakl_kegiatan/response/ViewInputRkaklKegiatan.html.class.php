<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
'module/rkakl_kegiatan/business/RkaklKegiatan.class.php';

class ViewInputRkaklKegiatan extends HtmlResponse {

	function TemplateModule() {
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot') . 'module/rkakl_kegiatan/template');
		$this->SetTemplateFile('input_rkakl_kegiatan.html');
	}
	
	function ProcessRequest() {
		$idDec = Dispatcher::Instance()->Decrypt($_REQUEST['dataId']);
		$Obj = new RkaklKegiatan();
		
		$msg = Messenger::Instance()->Receive(__FILE__);
		$arrProgram		= $Obj->GetProgram();
		
		#combo box program
		#Messenger::Instance()->SendToComponent('combobox', 'Combobox', 'view', 'html', 
		#		'data[kegiatan][program]',array('data[kegiatan][program]', 
		#		$arrProgram, $return['kegiatan']['program'], '', ' style="width:400px;" '), 
		#		Messenger::CurrentRequest);
		
		$return['Pesan'] 	= $msg[0][1];
		$return['Data'] 	= $msg[0][0];

		$data = $Obj->GetRkaklKegiatanById($idDec);

		$return['decDataId'] 	= $idDec;
		$return['dataK'] 		= $data;
		$return['program']		= $arrProgram;
		
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
		
		$this->mrTemplate->AddVar('content', 'URL_ACTION', 
		Dispatcher::Instance()->GetUrl('rkakl_kegiatan', 'inputRkaklKegiatan', 'do', 'html') . 
		"&dataId=" . 
		Dispatcher::Instance()->Encrypt($data['decDataId']));
      
		$this->mrTemplate->AddVar('content', 'ID', 
		Dispatcher::Instance()->Decrypt($_GET['dataId']));
		//$this->mrTemplate->AddVar('content', 'PAGE', Dispatcher::Instance()->Decrypt($_GET['page']));
		#combo box
		$program	= $data['program'];
		$this->mrTemplate->addVar("combobox", "COMBO_NAME", "combo_program");
		
		for($i=0;$i<sizeof($program);$i++):
			$this->mrTemplate->AddVar('combolist', 'COMBO_VALUE', $program[$i]['id_program']);
			$this->mrTemplate->AddVar('combolist', 'COMBO', $program[$i]['program_name']);
		
			$this->mrTemplate->parseTemplate('combolist',"a");
		endfor;
	}
}
?>
