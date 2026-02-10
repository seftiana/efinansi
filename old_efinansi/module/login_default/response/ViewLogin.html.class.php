<?php
class ViewLogin extends HtmlResponse {

   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') .
         'module/login_default/template');
      if (isset($_GET['fail'])) {
         $this->SetTemplateFile('view_login_fail.html');
      } else {
         $this->SetTemplateFile('view_login.html');
      }
   }

   function ProcessRequest() {

      if (Security::Instance()->IsLoggedIn()) {
         // redirect to proper place
         $module = 'home';
         $submodule = 'home';
         $action = 'view';
         $type = 'html';
         $this->RedirectTo(Dispatcher::Instance()->GetUrl($module, $submodule, $action, $type));
         return NULL;
      }

      return Security::Instance()->RequestSalt();
   }

   function TemplateBase() {

      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');

      $this->SetTemplateFile('document-common.html');

		$this->SetTemplateFile('layout-common-blank.html');
   }

   function ParseTemplate($data = NULL) {
      $this->mrTemplate->AddVar('content', 'BACK_TO', $_GET['back_to']);

      $this->mrTemplate->AddVar('document', 'LOADER_NAME_ADDITONAL', '-login');
      $_SESSION['login_salt'] = $data;
      if (!isset($_GET['fail'])) {
         $this->SetBodyAttribute('onload', '"document.form_login.username.focus();"');
      }
      $this->mrTemplate->AddVar('head_addition', 'APP_BASE_ADDRESS', GTFWConfiguration::GetValue('application', 'baseaddress') .
         GTFWConfiguration::GetValue('application', 'basedir'));
      $this->mrTemplate->AddVar('content', 'LOGIN_SALT', $data);
   }
}
?>