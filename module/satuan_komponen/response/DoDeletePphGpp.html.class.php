<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/pph_ref_gpp/response/PphGpp.proc.class.php';

class DoDeletePphGpp extends HtmlResponse {

   function TemplateModule() {
   }
   
   function ProcessRequest() {
      $objPphGpp = new ProsessPphGpp();
      //set post
      $objPphGpp->SetPost($_POST);      
      $urlRedirect = $objPphGpp->DeletePphGpp();
      $this->RedirectTo($urlRedirect) ;     
      return NULL;      
   }

   function ParseTemplate($data = NULL) {
      
   }
}
?>
