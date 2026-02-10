<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/coa/response/Coa.proc.class.php';

class DoInputCoa extends HtmlResponse {

   function TemplateModule() {
   }
   
   function ProcessRequest() {
      
      $objCoa = new ProsessCoa();
      //set post
      $objCoa->SetPost($_POST);
      
      $urlRedirect = $objCoa->InputCoa();     
            
      $this->RedirectTo($urlRedirect) ;      
      
      return NULL;
    }

   function ParseTemplate($data = NULL) {
      
   }
}
?>
