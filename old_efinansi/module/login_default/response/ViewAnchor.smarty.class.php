<?php
class ViewAnchor extends SmartyResponse {

   function TemplateModule() {
      $this->SetTemplateFile('view_anchor.tmpl');
   }
   
   function ProcessRequest() {
      if ($_SESSION['logged_in']) {
         return array('Logout', $this->mrDispatcher->GetUrl('login_default', 'logout', 'do', 'html'),
            'image/header-upload.png');
      } else {
         return array('Login', $this->mrDispatcher->GetUrl('login_default', 'login', 'view', 'html'),
            'image/header-download.png');
      }
   }

   function ParseTemplate($data = NULL) {
      $this->assign('ACTION', $data[0]);
      $this->assign('HREF', $data[1]);
      $this->assign('IMAGE', $data[2]);
   }
}
?>
