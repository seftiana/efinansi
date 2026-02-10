<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/kelompok_laporan/business/AppKlpLaporan.class.php';

class ViewBentukTransaksi extends HtmlResponse {
	
	function TemplateModule() {
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot') . 'module/kelompok_laporan/template');
		$this->SetTemplateFile('combo_bentuk_transaksi.html');
	}
	
	function ProcessRequest() {
		$idDec = Dispatcher::Instance()->Decrypt($_GET['dataId']);
		$Obj = new AppKelpLaporan();
		 
		 $bentukTransaksi = $Obj->GetBentukTransaksi($idDec);
		Messenger::Instance()->SendToComponent('combobox', 'Combobox', 'view', 'html', 'bentuk_transaksi', 
	     array('bentuk_transaksi', $bentukTransaksi, $idBentukTransaksi, 'none', ''), 
		 Messenger::CurrentRequest);

		return $return;
	}

	function ParseTemplate($data = NULL) {
		
	}
}
?>
