<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/jurnal_pengeluaran/response/ProcJurnalPengeluaran.proc.class.php';

class DoAddJurnalPengeluaran extends HtmlResponse {

   function TemplateModule() {
   }
   
   function ProcessRequest() {

      $Obj = new ProcJurnalPengeluaran();   
      
      $urlRedirect = $Obj->Add();     
            
      $this->RedirectTo($urlRedirect) ;      
      
      return NULL;
    }

   function ParseTemplate($data = NULL) {
      
   }
}
?>
