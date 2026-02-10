<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
			'module/menu/business/Menu.class.php';

class ViewMenu extends HtmlResponse {
   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue( 'application', 'docroot') .
         'module/menu/template');
      if(GTFWConfiguration::GetValue( 'application', 'menu_version')=='2')
	      $this->SetTemplateFile('view_menu_2.html');
	   else
		  $this->SetTemplateFile('view_menu_1.html');
   }

  
   function ProcessRequest() {

      $menuObj = new Menu();
      $menuObj->LoadSql('module/menu/business/menu.sql.php');
      
      $menu = $menuObj->ListAvailableMenu($_SESSION['username'], 'Yes');
         
      return $menu;
   }

   function ParseTemplate($data = NULL) {
	   $menuName = null;
   	#print_r($data);
		if (!empty($data)) {
         $len = sizeof($data);
         if ($len>0){
				$number = 0;
            for ($i=0; $i<$len; $i++) {
					if($menuName != $data[$i]['MenuName']){
						$this->mrTemplate->addVar('nav_left', 'UL_ID', $data[$i]['DmMenuId']);
						$idMenu[] = $data[$i]['DmMenuId'];
						$this->mrTemplate->addVar('nav_left', 'LEFT_NAV_VALUE', 
						$data[$i]['MenuName']);
						$url = Dispatcher::Instance()->GetUrl($data[$i]['Module'],
						$data[$i]['SubModule'],$data[$i]['Action'],$data[$i]['Type']).
						$data[$i]['url'];
						$this->mrTemplate->addVar('nav_left', 'LEFT_NAV', $url);
						$menuName = $data[$i]['MenuName'];
						
						$this->mrTemplate->clearTemplate('sub_nav');
						$this->mrTemplate->clearTemplate('sub_nav_item');
						
						for ($j=0; $j<$len; $j++) {
							
							if($menuName == $data[$j]['MenuName']){
								
									$this->mrTemplate->setAttribute('sub_nav', 
									'visibility', 'visible');
									
									$this->mrTemplate->addVar('sub_nav', 'UL_ID', 
									$data[$j]['DmMenuId']);

									$this->mrTemplate->addVar('sub_nav_item', 
									'LEFT_SUB_NAV_VALUE', $data[$j]['subMenu']);
									$url = Dispatcher::Instance()->GetUrl($data[$j]
									['subMenuModule'],$data[$j]['subMenuSubModule'],
									$data[$j]['subMenuAction'],$data[$j]['subMenuType']);
                           if (strpos($data[$j]['DmMenuId'],'report') === 0)
                              $url .= '&lay_id='.$data[$j]['layout_id'];
									$this->mrTemplate->addVar('sub_nav_item', 
									'LEFT_SUB_NAV', $url);
									$this->mrTemplate->parseTemplate('sub_nav_item', 'a');
								
							}
						}
						$this->mrTemplate->parseTemplate('nav_left', 'a');
						
					}
            }
				$idMenu = implode('|',$idMenu);
				$this->mrTemplate->addVar('content', 'ID_MENU', $idMenu);
				
         }
      } else $this->mrTemplate->addVar('menu_atas', 'ATAS_MAINNAME', 
	  							$_SESSION['active_user_group_id']);
   }
}
?>
