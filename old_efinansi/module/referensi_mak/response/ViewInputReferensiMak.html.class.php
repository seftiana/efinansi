<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
'module/referensi_mak/business/ReferensiMak.class.php';

class ViewInputReferensiMak extends HtmlResponse {

	function TemplateModule() {
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot') . 
		'module/referensi_mak/template');
		$this->SetTemplateFile('input_referensi_mak.html');
	}
	
	function ProcessRequest() {
		$idDec  = Dispatcher::Instance()->Decrypt($_REQUEST['dataId']);
		$Obj    = new ReferensiMak();
		$msg    = Messenger::Instance()->Receive(__FILE__);
		$return['Pesan']    = $msg[0][1];
		$return['Data']     = $msg[0][0];
		$return['css']      = $msg[0][2];
		$data               = $Obj->GetRefMakById($idDec);
		$typeBasArr         = $Obj->GetComboTypeBas();
		$typeBasSelected    = empty($data[0]['bas_tipe_id']) ?$return['Data']['bas_tipe']:$data[0]['bas_tipe_id'];
		
		Messenger::Instance()->SendToComponent('combobox', 'Combobox', 'view', 'html', 'bas_tipe', 
	    array('bas_tipe', $typeBasArr,  $typeBasSelected, '-', ' style="width:200px;" id="bas_tipe"'), 
	    Messenger::CurrentRequest);
        
		$return['decDataId']    = $idDec;
		$return['dataK']        = $data;
		return $return;
	}

	function ParseTemplate($data = NULL) {
		if ($data['Pesan']) {
			$this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
			$this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $data['Pesan']);
			$this->mrTemplate->AddVar('warning_box', 'CSS', $data['css']);
		}
		$dataK = $data['dataK'];
		
		if ($_REQUEST['dataId']=='') {
			$tambah = "Tambah";
		} else {
			$tambah = "Ubah";
		}
		if($dataK[0]['status_aktif']=='T' || $data['Data']['status_aktif'] == 'T'){
			$this->mrTemplate->AddVar('content', 'AKTIF_TIDAK','checked="checked"');
		} else {
			$this->mrTemplate->AddVar('content', 'AKTIF_YA','checked="checked"');
		}
		
		if($dataK[0]['nilai_default']=='D' || $data['Data']['nilai_default'] == 'D'){
			$this->mrTemplate->AddVar('content', 'DEBET_SELECT','checked="checked"');
		} else {
			$this->mrTemplate->AddVar('content', 'KREDIT_SELECT','checked="checked"');
		}
		
		$url_popup_bas  = Dispatcher::Instance()->GetUrl('referensi_mak', 'popupPaguBas', 'view', 'html');
		$url_popup_coa  = Dispatcher::Instance()->GetUrl('referensi_mak', 'coa', 'popup', 'html');
		$mak_nama       = empty($dataK[0]['nama'])?$data['Data']['nama']:$dataK[0]['nama'];
		$mak_kode       = empty($dataK[0]['kode'])?$data['Data']['kode']:$dataK[0]['kode'];
		$id_pagu_bas    = empty($dataK[0]['kode'])?$data['Data']['id_pagubas']:$dataK[0]['id_pagubas'];
		$kode_pagu_bas  = empty($dataK[0]['kode'])?$data['Data']['kode_pagubas']:$dataK[0]['kode_pagubas'];
		$url_action     = Dispatcher::Instance()->GetUrl(
		                                                'referensi_mak', 
		                                                'inputReferensiMak', 
		                                                'do', 
		                                                'html') . 
		                                                "&dataId=".Dispatcher::Instance()->Encrypt($data['decDataId']);
		$id_enc         = Dispatcher::Instance()->Decrypt($_GET['dataId']);
		$coa_id         = empty($dataK[0]['coa_id'])? $data['Data']['id_coa']:$dataK[0]['coa_id'];
		$coa_kode       = empty($dataK[0]['coa_kode_akun'])? $data['Data']['kode_coa']:$dataK[0]['coa_kode_akun'];
		$coa_nama       = empty($dataK[0]['coa_nama_akun'])? $data['Data']['nama_coa']:$dataK[0]['coa_nama_akun'];
		
	    $this->mrTemplate->AddVar("content", "URL_POPUP_COA", $url_popup_coa);
		$this->mrTemplate->AddVar('content', 'URL_POPUP_PAGUBAS', $url_popup_bas);
		$this->mrTemplate->AddVar('content', 'JUDUL', $tambah);
		$this->mrTemplate->AddVar('content', 'NAMA', $mak_nama);
        $this->mrTemplate->AddVar('content', 'KODE', $mak_kode);
		$this->mrTemplate->AddVar('content', 'ID_PAGUBAS', $id_pagu_bas);
		$this->mrTemplate->AddVar('content', 'KODE_PAGUBAS', $kode_pagu_bas);
		$this->mrTemplate->AddVar('content', 'URL_ACTION', $url_action);
      
		$this->mrTemplate->AddVar('content', 'ID', $id_enc);
		
		$this->mrTemplate->AddVar('content', 'ID_COA', $coa_id);
		$this->mrTemplate->AddVar('content', 'KODE_COA', $coa_kode);
		$this->mrTemplate->AddVar('content', 'NAMA_COA', $coa_nama);
	}
}
?>