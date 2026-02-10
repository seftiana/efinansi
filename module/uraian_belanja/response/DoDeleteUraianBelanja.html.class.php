<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/uraian_belanja/business/UraianBelanja.class.php';

class DoDeleteUraianBelanja extends HtmlResponse {

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
      $this->RedirectTo(Dispatcher::Instance()->GetUrl('uraian_belanja', 'uraianBelanja', 'view', 'html') . 
         '&err=' . Dispatcher::Instance()->Encrypt($additionalUrl));
   }

   function ParseTemplate($data = NULL) {
      
   }
}
?>
