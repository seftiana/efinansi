<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/uraian_belanja/response/UraianBelanja.proc.class.php';

class DoInputUraianBelanja extends HtmlResponse {

   function TemplateModule() {
   }
   
   function ProcessRequest() {

      $obj = new ProsessUraianBelanja();
      
      $urlRedirect = $obj->InputUraianBelanja();     
            
      $this->RedirectTo($urlRedirect) ;      
      
      return NULL;
    }

   function ParseTemplate($data = NULL) {
      
   }
}
?>
