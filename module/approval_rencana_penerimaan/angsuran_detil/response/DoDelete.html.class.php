<?php
/*
	@ClassName : DoDeleteFile
	@Copyright : PT Gamatechno Indonesia
	@Analyzed By : Nanang Ruswianto <nanang@gamatechno.com>
	@Author By : Dyan Galih <galih@gamatechno.com>
	@Version : 0.1
	@StartDate : 2010-01-07
	@LastUpdate : 2010-01-07
	@Description : 
*/

require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/rencana_pengeluaran/response/ProcessRencanaPengeluaran.proc.class.php';

class DoDeleteFile extends HtmlResponse {

	function TemplateModule() {
		
	}
	
	function ProcessRequest() {
		$objPrp = new ProcessRencanaPengeluaran();
		$urlRedirect = $objPrp->DeleteFile();
		$this->RedirectTo($urlRedirect);
		return NULL;
	}

	function ParseTemplate($data = NULL) {
	}
}
?>
