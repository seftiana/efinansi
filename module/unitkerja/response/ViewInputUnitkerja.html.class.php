<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/unitkerja/business/AppUnitkerja.class.php';

class ViewInputUnitkerja extends HtmlResponse {

	function TemplateModule() {
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot') . 'module/unitkerja/template');
		$this->SetTemplateFile('input_unitkerja.html');
	}
	
	function ProcessRequest() {
		$idDec = Dispatcher::Instance()->Decrypt($_REQUEST['dataId']);
		$decJenis = Dispatcher::Instance()->Decrypt($_GET['jenis']);
		$parentUnitId = Dispatcher::Instance()->Decrypt($_GET['parentUnitId']);
		$unitkerjaObj = new AppUnitkerja();
		$msg = Messenger::Instance()->Receive(__FILE__);
		$this->Pesan = $msg[0][1];
		$data = $msg[0][0];
		
		if(!empty($data)) {
			$dataUnitkerja[0]['unitkerja_kode'] = $data['unitkerja_kode'];
			$dataUnitkerja[0]['unitkerja_nama'] = $data['unitkerja_nama'];
			$dataUnitkerja[0]['unitkerja_pimpinan'] = $data['unitkerja_pimpinan'];
			$dataUnitkerja[0]['satuankerja_id'] = $data['satker'];
			$dataUnitkerja[0]['tipeunit_id'] = $data['tipeunit'];
			$dataUnitkerja[0]['statusunit'] = $data['statusunit'];
		} else {
			$dataUnitkerja = $unitkerjaObj->GetDataUnitkerjaById($idDec);
		}

      if($decJenis == 'subunit') {
     	   $satuaKerjaId =($parentUnitId == '') ?  $dataUnitkerja[0]['satuankerja_id']  : $parentUnitId;
		   $satker = $unitkerjaObj->GetDataSatker();
		   Messenger::Instance()->SendToComponent('combobox', 'Combobox', 'view', 'html', 'satker', array('satker', $satker,  $satuaKerjaId, 'false', 'style="width:200px;"'), Messenger::CurrentRequest);
      }

		$tipeunit = $unitkerjaObj->GetDataTipeunit();
		Messenger::Instance()->SendToComponent('combobox', 'Combobox', 'view', 'html', 'tipeunit', array('tipeunit', $tipeunit, $dataUnitkerja[0]['tipeunit_id'], 'false', 'style="width:200px;"'), Messenger::CurrentRequest);

		$arrStatusUnit = $unitkerjaObj->GetStatusUnitKerja();
		Messenger::Instance()->SendToComponent('combobox', 'Combobox', 'view', 'html', 'statusunit', array('statusunit', $arrStatusUnit, $dataUnitkerja[0]['statusunit'], '-', 'style="width:200px;"'), Messenger::CurrentRequest);

		$return['decJenis'] = $decJenis;
		$return['decDataId'] = $idDec;
		$return['dataUnitkerja'] = $dataUnitkerja;
		$return['parentUnitId'] = $parentUnitId;
		$return['unitKerjaLabel']=$unitkerjaObj->GetDataSatker($parentUnitId);
		return $return;
	}

	function ParseTemplate($data = NULL) {
		if ($this->Pesan) {
			$this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
			$this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->Pesan);
		}
		$dataUnitkerja = $data['dataUnitkerja'];
		$unitKerjaLabel = $data['unitKerjaLabel'];
		
		if ($_REQUEST['dataId']=='') {
			$url="addUnitkerja";
			$tambah="Tambah";
		} else {
			$url="updateUnitkerja";
			$tambah="Ubah";
		}
      if($data['decJenis'] == 'subunit') {
			$this->mrTemplate->SetAttribute('combo_satker', 'visibility', 'visible');
			$this->mrTemplate->AddVar('content', 'UNIT_SUB_UNIT', 'Sub Unit');
      } else {
			$this->mrTemplate->AddVar('content', 'UNIT_SUB_UNIT', 'Unit');
      }
      
      	if($data['parentUnitId']!=''){            
      		$this->mrTemplate->AddVar('unitkerja_parent', 'UNITKERJA_IS_PARENT', 'YES');
      		$this->mrTemplate->AddVar('unitkerja_parent_label', 'UNITKERJA_PARENT_ID', 
			  	$data['parentUnitId']);
      		$this->mrTemplate->AddVar('unitkerja_parent_label', 'UNITKERJA_PARENT_LABEL', 
			     $unitKerjaLabel[0]['name']);
      	} else {
      		$this->mrTemplate->AddVar('unitkerja_parent', 'UNITKERJA_IS_PARENT', 'NO');
      	}
		$this->mrTemplate->AddVar('content', 'JUDUL', $tambah);
		$this->mrTemplate->AddVar('content', 'UNITKERJA_KODE', $dataUnitkerja[0]['unitkerja_kode']);
		$this->mrTemplate->AddVar('content', 'UNITKERJA_NAMA', $dataUnitkerja[0]['unitkerja_nama']);
		$this->mrTemplate->AddVar('content', 'UNITKERJA_PIMPINAN', $dataUnitkerja[0]['unitkerja_pimpinan']);
		$this->mrTemplate->AddVar('content', 'URL_ACTION', Dispatcher::Instance()->GetUrl('unitkerja', $url, 'do', 'html') . '&jenis=' . Dispatcher::Instance()->Encrypt($data['decJenis']) . "&dataId=" . Dispatcher::Instance()->Encrypt($data['decDataId']));
		$this->mrTemplate->AddVar('content', 'UKRID', Dispatcher::Instance()->Decrypt($_GET['dataId']));
		$this->mrTemplate->AddVar('content', 'PAGE', Dispatcher::Instance()->Decrypt($_GET['page']));
	}
}
?>
