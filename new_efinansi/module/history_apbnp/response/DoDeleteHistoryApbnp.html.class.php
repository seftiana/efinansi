<?php

require_once GTFWConfiguration::GetValue('application','docroot').
	'module/history_apbnp/response/ProcessHistoryApbnp.proc.class.php';


class DoDeleteHistoryApbnp extends HtmlResponse
{
   public function TemplateModule() {}
   public function ProcessRequest()
   {   	
    	$objProcess = new ProcessHistoryApbnp;      
      	$urlRedirect = $objProcess->Delete();     
		$this->RedirectTo($urlRedirect) ; 
		return null; 
   }
   public function ParseTemplate($data = NULL) {}
   
}

?>