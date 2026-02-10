<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
'module/angsuran_detil/response/ProcessRencanaPengeluaran.proc.class.php';

class DoAddRencanaPengeluaran extends HtmlResponse {

   function TemplateModule() {
	   
   }
   
   function ProcessRequest() {

      $Obj = new ProcessRencanaPengeluaran();   
      
      $urlRedirect = $Obj->Add();     
            
      $this->RedirectTo($urlRedirect) ;      
      
      return NULL;
    }

   function ParseTemplate($data = NULL) {
      
   }
}
?>
