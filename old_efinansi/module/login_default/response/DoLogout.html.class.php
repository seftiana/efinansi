<?php
class DoLogout extends HtmlResponse {

   function TemplateModule() {
   }

   function ProcessRequest() {
      Security::Instance()->Logout(TRUE);
      	Log::Instance()->SendLog('Proses Logout Sukses');
      //$this->RedirectTo($this->mrDispatcher->GetUrl('login_default', 'session', 'destroy', 'html'));
         $module = GTFWConfiguration::GetValue( 'application', 'default_module');
         $submodule = GTFWConfiguration::GetValue( 'application', 'default_submodule');
         $action = GTFWConfiguration::GetValue( 'application', 'default_action');
         $type = GTFWConfiguration::GetValue( 'application', 'default_type');
         $this->RedirectTo(Dispatcher::Instance()->GetUrl($module, $submodule, $action, $type));
   }

   function ParseTemplate($data = NULL) {
   }
}
?>
