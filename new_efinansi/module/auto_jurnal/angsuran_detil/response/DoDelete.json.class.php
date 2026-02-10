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

class DoDeleteFile extends JsonResponse {

	function TemplateModule() {
		
	}
	
	function ProcessRequest() {
		$objPrp = new ProcessRencanaPengeluaran();
		$result = $objPrp->DeleteFile('json');
		return $return;
	}

	function ParseTemplate($data = NULL) {
	}
}
?>
