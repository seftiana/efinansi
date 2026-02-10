<?php
class DestroySession extends HtmlResponse {

   function TemplateModule() {
   }

   function ProcessRequest() {
      $module = GTFWConfiguration::GetValue( 'application', 'default_module');
      $submodule = GTFWConfiguration::GetValue( 'application', 'default_submodule');
      $action = GTFWConfiguration::GetValue( 'application', 'default_action');
      $type = GTFWConfiguration::GetValue( 'application', 'default_type');
      $this->RedirectTo($this->mrDispatcher->GetUrl($module, $submodule, $action, $type));
      return NULL;
   }

   function ParseTemplate($data = NULL) {
   }
}
?>