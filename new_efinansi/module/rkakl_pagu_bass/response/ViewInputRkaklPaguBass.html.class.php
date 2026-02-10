<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
'module/rkakl_pagu_bass/business/RkaklPaguBass.class.php';

class ViewInputRkaklPaguBass extends HtmlResponse {

    function TemplateModule() {
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot') . 
		'module/rkakl_pagu_bass/template');
		$this->SetTemplateFile('input_rkakl_pagu_bass.html');
	}
	
	function ProcessRequest() {
		$idDec              = Dispatcher::Instance()->Decrypt($_REQUEST['dataId']);
		$Obj                = new RkaklPaguBass();
		$msg                = Messenger::Instance()->Receive(__FILE__);
		$return['Pesan']    = $msg[0][1];
		$return['Data']     = $msg[0][0];
        $return['Css']      = $msg[0][2];
        
		$data               = $Obj->GetRkaklPaguBassById($idDec);

		$return['decDataId']    = $idDec;
		$return['dataK']        = $data;
		return $return;
	}

	function ParseTemplate($data = NULL) {
		if ($data['Pesan']) {
			$this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
			$this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $data['Pesan']);
			$this->mrTemplate->AddVar('warning_box', 'CSS', $data['Css']);
		}
		$dataK = $data['dataK'];
		
		if ($_REQUEST['dataId']=='') {
			$tambah = "Tambah";
		} else {
			$tambah = "Ubah";
		}
		if($dataK[0]['status_aktif'] =='N' || $data['Data']['status_aktif'] == 'N'){
			$this->mrTemplate->AddVar('content', 'AKTIF_TIDAK','checked="checked"');
		} else {
			$this->mrTemplate->AddVar('content', 'AKTIF_YA','checked="checked"');
		}
		
		if($dataK[0]['nilai_default'] =='D' || $data['Data']['nilai_default'] == 'D'){
			$this->mrTemplate->AddVar('content', 'DEBET_SELECT','checked="checked"');
		} else {
			$this->mrTemplate->AddVar('content', 'KREDIT_SELECT','checked="checked"');
		}
		
		$keterangan = empty($dataK[0]['keterangan'])?$data['Data']['keterangan']:$dataK[0]['keterangan'];
		$kode       = empty($dataK[0]['kode'])?$data['Data']['kode']:$dataK[0]['kode'];
		$id         = empty($dataK[0]['id'])?$data['Data']['id']:$dataK[0]['id'];
		$url_act    = Dispatcher::Instance()->GetUrl('rkakl_pagu_bass', 'inputRkaklPaguBass', 'do', 'html') . 
		              "&dataId=" . Dispatcher::Instance()->Encrypt($data['decDataId']);
		              
		$this->mrTemplate->AddVar('content', 'JUDUL', $tambah);
		$this->mrTemplate->AddVar('content', 'KETERANGAN', $keterangan);
        $this->mrTemplate->AddVar('content', 'KODE', $kode);
        $this->mrTemplate->AddVar('content', 'ID', $id);
		
		$this->mrTemplate->AddVar('content', 'URL_ACTION', $url_act);
      
		$this->mrTemplate->AddVar('content', 'ID', Dispatcher::Instance()->Decrypt($_GET['dataId']));
		//$this->mrTemplate->AddVar('content', 'PAGE', Dispatcher::Instance()->Decrypt($_GET['page']));
	}
}
?>
