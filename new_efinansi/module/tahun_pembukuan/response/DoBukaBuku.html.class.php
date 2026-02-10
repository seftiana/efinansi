<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/tahun_pembukuan/response/ProcessTahunPembukuan.proc.class.php';

class DoBukaBuku extends HtmlResponse {

   function TemplateModule() {
   }
   
   function ProcessRequest() {
      
      $objProsessTahunPembukuan = new ProsessTahunPembukuan();
      //set post
      $objProsessTahunPembukuan->SetPost($_POST);
      
      $urlRedirect = $objProsessTahunPembukuan->InputTahunPembukuan();      
            
      $this->RedirectTo($urlRedirect) ;      
      
      return NULL;
    }

   function ParseTemplate($data = NULL) {
      
   }
}
?>
