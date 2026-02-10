<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/tahun_pembukuan/response/ProcessSaldo.proc.class.php';

class DoAddSaldoAwal extends JsonResponse {

   function TemplateModule() {
   }
   
   function ProcessRequest() {
      
      $objProsessSaldo = new ProcessSaldo();
      $objProsessSaldo->SetPost($_POST);
      $urlRedirect = $objProsessSaldo->AddProses();     
      
      if(is_array($urlRedirect) && $urlRedirect['error'] === true)
      	return array('exec' => '$("#warning_box").html("'.$urlRedirect['msg'].'");$("#warning_box").show();refreshNilai();$("#btnbatal").attr("disabled",false)');
      else
      	return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlRedirect.'&ascomponent=1")');            
    }

   function ParseTemplate($data = NULL) {
   }
}
?>
