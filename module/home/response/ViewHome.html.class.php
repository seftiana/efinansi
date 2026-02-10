<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/menu/business/Menu.class.php';

class ViewHome extends HtmlResponse {
   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') .
         'module/home/template');
      $this->SetTemplateFile('view_home.html');
   }

   function ProcessRequest() {
      $menuObj       = new Menu();
      $menuObj->LoadSql('module/menu/business/menu.sql.php');
      $menu          = $menuObj->ListAllAvailableSubMenuForGroup(
         $_SESSION['username'],
         $_GET['dmmid']
      );
      return $menu;
   }

   function GetMenuKey ($name, $arrMenu, $len) {
      for ($i=0; $i<$len; $i++) {
         if ($name == $arrMenu[$i]) {
            return  $i;
         }
      }
      return -1;
   }

   function ParseTemplate($data = NULL) {
      $urlEarlyWarning     = Dispatcher::Instance()->GetUrl(
         'early_warning',
         'EarlyWarning',
         'view',
         'html'
      ).'&ascomponent=1';
      $this->mrTemplate->AddVar('content', 'URL_EARLY_WARNING', $urlEarlyWarning);

      if (!empty($data)) {
         for($i=0;$i<sizeof($data);$i++){
            $url='';
            if($data[$i]['ParentMenuId']=='0'){
               $url  = '&dmmid='.$data[$i]['DmMenuId'].'&mid='.$data[$i]['MenuId'];
               $this->mrTemplate->addVar('icon_menu', 'MOUSE_UP', 'onMouseUp="ShowMenu('.$data[$i]['DmMenuId'].')"');
            }else if (strpos($data[$i]['MenuId'],'report') === 0){
               $url  .= '&lay_id='.substr($data[$i]['LayId'],6);
            }

            $this->mrTemplate->addVar('icon_menu', 'LINK_URL', Dispatcher::Instance()->GetUrl(
               $data[$i]['Module'],
               $data[$i]['SubModule'],
               $data[$i]['Action'],
               $data[$i]['Type']
            ).$url);
            $this->mrTemplate->addVar('icon_menu', 'ICON_NAME', $data[$i]['DmIconPath']);
            $this->mrTemplate->addVar('icon_menu', 'LINK_NAME', $data[$i]['MenuName']);

            $this->mrTemplate->parseTemplate('icon_menu', 'a');
         }
      }
   }
}
?>