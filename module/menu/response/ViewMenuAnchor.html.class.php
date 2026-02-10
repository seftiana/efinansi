<?php
/*
Ini menu yang bsia ditaruh di left side utk template gtfw yg aseli
html-nya pake ul, li

menu yang muncul tergatnung dr setting security. Jika security enabled maka hanya menu yang bersesuaian saja yg bisa muncul
*/
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/menu/business/Menu.class.php';

class ViewMenuAnchor extends HtmlResponse {
   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue( 'application', 'docroot') .
         'module/menu/template');
      $this->SetTemplateFile('view_menu.html');
   }

   function ProcessRequest() {
      $demo = new Menu();
      $demo->LoadSql('module/menu/business/menu.sql.php');
      return $demo->ListAvailableMenu();
   }

   function ParseTemplate($data = NULL) {
      $cur_mod = $dispt->mModule;
      $cur_sub_mod = $dispt->mSubmodule;

      if (empty($data)) {
         $this->mrTemplate->addVar('menulist', 'MENU_DESC', 'No demo is available!');
         $this->mrTemplate->addVar('menulist', 'MENU_URL', '#');
      } else {
         foreach ($data as $key => $value) {
            if (Security::Instance()->mSecurityEnabled)
               $allow = Security::Instance()->AllowedToAccess($value['Module'], $value['SubModule'], 'view', $value['Type']);
            else
               $allow = true;



            if (($value['Module']==$cur_mod) && ($value['SubModule']==$cur_sub_mod)) {
               $this->mrTemplate->addVar('menulist', 'STRONG_OPEN', '<strong>');
               $this->mrTemplate->addVar('menulist', 'STRONG_CLOSE', '</strong>');
            } else {
               $this->mrTemplate->addVar('menulist', 'STRONG_OPEN', '');
               $this->mrTemplate->addVar('menulist', 'STRONG_CLOSE', '');
            }

            if ($allow) {
               $this->mrTemplate->addVar('menulist', 'MENU_NAME', $value['MenuName']);
               $this->mrTemplate->addVar('menulist', 'MENU_DESC', $value['Description']);
               $this->mrTemplate->addVar('menulist', 'MENU_URL', GTFWConfiguration::GetValue( 'application', 'baseaddress') .
               Dispatcher::Instance()->GetUrl($value['Module'], $value['SubModule'], /*$value['Action']*/ 'view', $value['Type']));
               $this->mrTemplate->parseTemplate('menulist', 'a');
            }
         }
      }
   }
}
?>