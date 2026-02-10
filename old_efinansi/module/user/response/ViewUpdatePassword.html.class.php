<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/user/business/AppUser.class.php';

class ViewUpdatePassword extends HtmlResponse {

   function TemplateModule() {
      $this->SetTemplateBasedir( GTFWConfiguration::GetValue( 'application', 'docroot') .
         'module/user/template');
      $this->SetTemplateFile('ubah_password.html');
   }
   
   function ProcessRequest() {
      $data['iduser'] = $_SESSION['userid'];
      if (isset ($_REQUEST['err'])) {
         $data['err'] = Dispatcher::Instance()->Encrypt($_REQUEST['err']);
      }
      return $data;
   }

   function ParseTemplate($data = NULL) {
      if (isset ($data['err'])) {
         if ($data['err']=='kosong') {
            $pesan='Anda belum memasukkan password';
         } else if ($data['err']=='tidak sama') {
            $pesan='Password baru yang anda masukkan tidak sama';
         } else if ($data['err']=='password') {
            $pesan='Password yang anda masukkan tidak benar';
         } 
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $pesan);
      }
      $this->mrTemplate->AddVar('content', 'RETURN_PAGE', $_GET['returnPage']);
      $this->mrTemplate->AddVar('content', 'URL_EDIT', Dispatcher::Instance()->GetUrl('user', 'updateProfile', 'do', 'html'));
   }
}
?>
