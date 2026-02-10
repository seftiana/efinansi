<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/user/business/AppUser.class.php';

class ViewChangePassword extends HtmlResponse {

   function TemplateModule() {
      $this->SetTemplateBasedir( GTFWConfiguration::GetValue( 'application', 'docroot') .
         'module/user/template');
      $this->SetTemplateFile('change_password.html');
   }
   
   function ProcessRequest() {

      if (isset ($_REQUEST['err'])) {
         $data['err'] = Dispatcher::Instance()->Encrypt($_REQUEST['err']);         
      }
      $data['usr'] = $_GET['usr'];
      return $data;
   }

   function ParseTemplate($data = NULL) {
       
      $this->mrTemplate->AddVar('content', 'USER_ID', $data['usr']);
      if (isset ($data['err'])) {
         if ($data['err']=='kosong') {
            $pesan='Anda belum memasukkan password';
         } else if ($data['err']=='tidak sama') {
            $pesan='Password baru yang anda masukkan tidak sama';
         } 
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $pesan);
      }
      
      $this->mrTemplate->AddVar('content', 'RETURN_PAGE', $_GET['returnPage']);
      $this->mrTemplate->AddVar('content', 'URL_EDIT', Dispatcher::Instance()->GetUrl('user', 'updatePassword', 'do', 'html'));
   }
}
?>
