<?php
/**
* @package ViewModuleInformation
* @copyright Copyright (c) PT Gamatechno Indonesia
* @Analyzed By Dyan Galih <galih@gamatechno.com>
* @author Dyan Galih <galih@gamatechno.com>
* @version 0.1
* @startDate 2011-01-01
* @lastUpdate 2011-01-01
* @description View Module Information
*/

require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/module_information/business/ModuleInformation.class.php';

class ViewModuleInformation extends HtmlResponse {

   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue( 'application', 'docroot').'module/module_information/template');
      $this->SetTemplateFile('view_module_information.html');
   }

   function ProcessRequest() {
      $objModuleInformation = new ModuleInformation();

      $return = $objModuleInformation->GetModuleInformation($_GET['module'],$_GET['submodule'],$_GET['action'],$_GET['type']);

      return $return;
   }

   function ParseTemplate($data = NULL) {
      if($data=="")
         $data="<br /> No Information";
      $this->mrTemplate->AddVars('content', $data);
   }
}
?>