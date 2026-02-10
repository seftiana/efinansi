<?php
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'module/realisasi_pencairan_2/business/RealisasiPencairan.class.php';

class ViewComboProgram extends HtmlResponse {
   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue( 'application', 'docroot') .
         'module/realisasi_pencairan_2/template');
      $this->SetTemplateFile('combo_program.html');
   }

   function ProcessRequest() {
		$obj = new RealisasiPencairan();
		print_r($_GET);
		$this->ta_id = (string)$_GET['sendValue'];
		$result = $obj->GetComboProgramTa($this->ta_id);
		//print_r($this->ta_id);
		return $result;
	}

	function ParseTemplate($data = NULL) {
	if($this->ta_id=="all"){
		$status="PILIH";
	}	else	{
		$status="SEMUA";
	}

	$this->mrTemplate->AddVar('list_program', 'STATUS', $status);
	//print_r($data);
	if (empty($data)) {
		$this->mrTemplate->addVar('list_program','IS_EMPTY','YES');
	}	else	{
		$this->mrTemplate->addVar('list_program','IS_EMPTY','NO');

			foreach($data as $key => $value) {
				$this->mrTemplate->addVar('item_program', "COMBO_LABEL", $value['name']);
				$this->mrTemplate->addVar('item_program', "COMBO_VALUE", $value['id']);
				$this->mrTemplate->addVar('item_program', "COMBO_CHANGE", $_GET['change']);
				$this->mrTemplate->parseTemplate('item_program', 'a');
			}
      }
   }
}
?>