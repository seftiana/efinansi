<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/pph_ref_gpp/response/PphGpp.proc.class.php';

class DoInputPphGpp extends HtmlResponse {

   function TemplateModule() {
   }
   
   function ProcessRequest() {
      
      $objPphGpp = new ProsessPphGpp();
      //set post
      $objPphGpp->SetPost($_POST);
      
      $urlRedirect = $objPphGpp->InputPphGpp();     
            
      $this->RedirectTo($urlRedirect) ;      
      
      return NULL;
    }

   function ParseTemplate($data = NULL) {
      
   }
}
?>
