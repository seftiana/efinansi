<?php
class ViewAnchor extends HtmlResponse {

   function TemplateModule() {
      $this->SetTemplateBasedir( GTFWConfiguration::GetValue('application', 'docroot') .
         'module/login_default/template');
      $this->SetTemplateFile('view_anchor.html');
   }

   function ProcessRequest() {
      //var_dump($_SESSION['logged_in']);
      //die('ViewAnchor');
      if ($_SESSION['logged_in']) {
         return array('Logout', Dispatcher::Instance()->GetUrl('login_default', 'logout', 'do', 'html'),
            'image/header-upload.png');
      } else {
         return array('Login', Dispatcher::Instance()->GetUrl('login_default', 'login', 'view', 'html'),
            'image/header-download.png');
      }
   }

   function ParseTemplate($data = NULL) {
      $this->mrTemplate->addVar('content', 'ACTION', $data[0]);
      $this->mrTemplate->addVar('content', 'HREF', $data[1]);
      $this->mrTemplate->addVar('content', 'IMAGE', $data[2]);
   }
}
?>