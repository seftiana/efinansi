<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/uraian_belanja/business/UraianBelanja.class.php';

class DoDeleteUraianBelanja extends JsonResponse {

   function TemplateModule() {
   }
   
   function ProcessRequest() {
      
      $idDec = Dispatcher::Instance()->Decrypt($_REQUEST['idDelete']);
      $idCari = Dispatcher::Instance()->Decrypt($_REQUEST['cari']);
      
      $Obj = new UraianBelanja();
      $deleteData = $Obj->DeleteUraianBelanja($idDec);
       if ($deleteData == true) {
         $additionalUrl = "delete|";
      } else {
         $additionalUrl = "delete|fail";
      }
      return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element",
               " '.Dispatcher::Instance()->GetUrl('uraian_belanja', 'uraianBelanja', 'view', 'html') . 
               '&err=' . Dispatcher::Instance()->Encrypt($additionalUrl).'&ascomponent=1")') ;
   }

   function ParseTemplate($data = NULL) {
      
   }
}
?>
