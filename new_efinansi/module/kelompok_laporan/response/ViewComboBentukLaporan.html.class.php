<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/kelompok_laporan/business/AppKlpLaporan.class.php';

class ViewComboBentukLaporan extends HtmlResponse {
	
	function TemplateModule() {
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot') . 'module/kelompok_laporan/template');
		$this->SetTemplateFile('combo_bentuk_transaksi.html');
	}
	
	function ProcessRequest() {
		$idDec = Dispatcher::Instance()->Decrypt($_REQUEST['dataId']);
		$Obj = new AppKelpLaporan();
		 
		 $bentukTransaksi = $Obj->GetBentukTransaksi($_REQUEST['dataId']);
		 if(empty($bentukTransaksi)){
		 	$disabledStatus="disabled";
		 	$bentukTransaksi['0']['name']="Tidak Ada Data";
		}
		Messenger::Instance()->SendToComponent('combobox', 'Combobox', 'view', 'html', 'bentuk_transaksi', 
	     array('bentuk_transaksi', $bentukTransaksi, $idBentukTransaksi, 'none', $disabledStatus), 
		 Messenger::CurrentRequest);

		return $return;
	}

	function ParseTemplate($data = NULL) {

	}
}
?>
