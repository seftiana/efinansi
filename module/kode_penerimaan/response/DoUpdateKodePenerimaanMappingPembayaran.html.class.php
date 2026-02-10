<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/kode_penerimaan/response/ProcessKodePenerimaanMappingPembayaran.proc.class.php';

class DoUpdateKodePenerimaanMappingPembayaran extends HtmlResponse {

   function TemplateModule() {
   }
   
   function ProcessRequest() {

      $Obj = new ProcessKodePenerimaanMappingPembayaran();
      
      $urlRedirect = $Obj->Update();     
            
      $this->RedirectTo($urlRedirect) ;      
      
      return NULL;
   }

   function ParseTemplate($data = NULL) {
   }
}
?>