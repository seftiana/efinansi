<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/tahun_pembukuan/response/ProcessTahunPembukuan.proc.class.php';

class DoTutupBuku extends JsonResponse {

   function TemplateModule() {
   }
   
   function ProcessRequest() {

      $objProsessTahunPembukuan = new ProsessTahunPembukuan();
      //set post
      $objProsessTahunPembukuan->SetPost($_POST);
      
      $urlRedirect = $objProsessTahunPembukuan->InputTahunPembukuan(); 
      
      return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlRedirect.'&ascomponent=1")');       
    }

   function ParseTemplate($data = NULL) {
   }
}
?>
