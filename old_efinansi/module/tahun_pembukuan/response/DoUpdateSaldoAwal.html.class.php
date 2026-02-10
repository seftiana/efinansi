<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/tahun_pembukuan/response/ProcessSaldo.proc.class.php';

class DoUpdateSaldoAwal extends HtmlResponse {

   function TemplateModule() {
   }
   
   function ProcessRequest() {
      
      $objProsessSaldo = new ProcessSaldo();
      //set post
      $objProsessSaldo->SetPost($_POST);
      
      $urlRedirect = $objProsessSaldo->UpdateProses();     
            
      $this->RedirectTo($urlRedirect) ;      
      
      return NULL;
    }

   function ParseTemplate($data = NULL) {
      
   }
}
?>
