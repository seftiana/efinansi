<?php

require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/additional_lib/ImgHelper.class.php';
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/report/business/Report.class.php';

class ViewReportGraphic extends ImgResponse {

   function ProcessRequest() {
      $rep = new Report();
		
		$data = $rep->GetTableById($_GET['tab_id']);
		//print_r($_GET['tipe']);print_r($data);exit;
		eval($data['TABLE_PHP_CODE']);
   }
   
}
?>
