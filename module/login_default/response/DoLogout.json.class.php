<?php
class DoLogout extends JsonResponse {

   function TemplateModule() {
   }

   function ProcessRequest() {
      Security::Instance()->Logout(TRUE);
      //$this->RedirectTo($this->mrDispatcher->GetUrl('login_default', 'session', 'destroy', 'html'));
         $module = GTFWConfiguration::GetValue( 'application', 'default_module');
         $submodule = GTFWConfiguration::GetValue( 'application', 'default_submodule');
         $action = GTFWConfiguration::GetValue( 'application', 'default_action');
         $type = GTFWConfiguration::GetValue( 'application', 'default_type');
         $urlRedirect = Dispatcher::Instance()->GetUrl($module, $submodule, $action, $type);
         
         return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("body-application","'.$urlRedirect.'&ascomponent=1")');
   }

   function ParseTemplate($data = NULL) {
   }
}
?>
