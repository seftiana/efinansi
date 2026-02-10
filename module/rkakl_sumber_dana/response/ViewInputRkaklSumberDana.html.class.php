<?php

/**
 * ViewInputRkaklSumberDana.html.class.php
 * @copyright 2011 gamatechno
 */
 
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
'module/rkakl_sumber_dana/business/RkaklSumberDana.class.php';

/**
 * Class ViewInputRkaklSumberDana
 * untuk menangani tampilan input data
 */
class ViewInputRkaklSumberDana extends HtmlResponse {

	public function TemplateModule() 
	{
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot') . 
			'module/rkakl_sumber_dana/template');
		$this->SetTemplateFile('input_rkakl_sumber_dana.html');
	}
	
	public function ProcessRequest() 
	{
		$idDec = Dispatcher::Instance()->Decrypt($_REQUEST['dataId']);
		$Obj = new RkaklSumberDana();
		$msg = Messenger::Instance()->Receive(__FILE__);
		$return['Pesan'] = $msg[0][1];
		$return['Data'] = $msg[0][0];

		$data = $Obj->GetRkaklSumberDanaById($idDec);

		$return['decDataId'] = $idDec;
		$return['dataK'] = $data;
		return $return;
	}

	public function ParseTemplate($data = NULL) 
	{
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
		
		$this->mrTemplate->AddVar('content', 'ID', 
		Dispatcher::Instance()->Decrypt($_GET['dataId']));
		$this->mrTemplate->AddVar('content', 'JUDUL', $tambah);
		$this->mrTemplate->AddVar('content', 'SUMBER_DANA_NAMA',
			 empty($dataK[0]['sumber_dana_nama']) ? 
			 	$data['Data']['sumber_dana_nama' ]: 
			 	$dataK[0]['sumber_dana_nama']);
      	
		$this->mrTemplate->AddVar('content', 'URL_ACTION', 
			Dispatcher::Instance()->GetUrl('rkakl_sumber_dana', 
			'inputRkaklSumberDana', 'do', 'html') . 
			"&dataId=" . Dispatcher::Instance()->Encrypt($data['decDataId']));
		if($dataK[0]['is_aktif']=='T'){
			$this->mrTemplate->AddVar('content', 'AKTIF_TIDAK', 'checked="checked"');
		} else {
			$this->mrTemplate->AddVar('content', 'AKTIF_YA', 'checked="checked"');
		}
      
		//$this->mrTemplate->AddVar('content', 'PAGE', Dispatcher::Instance()->Decrypt($_GET['page']));
	}
}