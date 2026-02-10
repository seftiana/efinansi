<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/user/business/AppGroup.class.php';
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/user/business/GroupPrivilege.class.php';

class DoUpdateGroup extends HtmlResponse {

   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot') .
         'module/user/template');
      $this->SetTemplateFile('view_user.html');
   }
   
   function ProcessRequest() {
      $idDec = Dispatcher::Instance()->Decrypt($_POST['grp']);
      $groupObj = new AppGroup();
      $privObj = new GroupPrivilege();
      $additionalUrl = "";
		
      if (isset($_POST['btnsimpan'])) {
         if ($_POST['groupname']!="") {
            $updateMenu = true;
            if (isset($_POST['menu'] )) {
               $updateMenu = false;
               foreach($_POST['menu'] as $value) {
                  $menu[] =  $value ;
               }
					
               $dataMenu = $privObj->GetPrivilegeByArrayId($menu);
					
               $len = sizeof($dataMenu);
               
               for ($i=0; $i<$len; $i++) {
                  if (!isset($arrMenu[$dataMenu[$i]['menu_parent_id']]['parent'])) {
                     $tmp = $privObj->GetPrivilegeById($dataMenu[$i]['menu_parent_id']);
                     $arrMenu[$dataMenu[$i]['menu_parent_id']]['parent'] = $tmp[0];
                  }
                  $arrMenu[$dataMenu[$i]['menu_parent_id']]['child'][] = $dataMenu[$i];
               } 
            }
			
				
            $groupObj->StartTrans(); 
            $updateGroup = $groupObj->DoUpdateGroup($_POST['groupname'], $_POST['deskripsi'], $idDec);
            $updateMenu = false;
            $deleteMenu = $privObj->DoDeleteGroupMenu($idDec);
            $deleteModule = $privObj->DoDeleteGroupModule($idDec);      
            $updateDelete = $updateGroup && $deleteMenu && $deleteModule;
            $addModule = $privObj->DoAddGroupModule($idDec,'5');
            $addModulePesan = $privObj->DoAddGroupModuleByModuleName('collaboration', $idDec);
            if ($updateDelete) {
               if (!empty($arrMenu)) {
                  foreach($arrMenu as $key=>$value) {
                     //add ParentMenu
                     //print_r($value);
                     $addMenu = $privObj->DoAddGroupMenu($value['parent']['menu_name'], $idDec, 
                        $value['parent']['default_module_id'], 0, $value['parent']['is_show']);
                     $parentId = $privObj->GetMaxMenuId();
                     if ($addMenu && $parentId) {
                        // add anak2nya
                        $len = sizeof($value['child']) ;
								
                        for ($i=0; $i<$len; $i++) {
                           $addMenu = $privObj->DoAddGroupMenu($value['child'][$i]['menu_name'], $idDec,
                              $value['child'][$i]['default_module_id'], $parentId, $value['child'][$i]['is_show']);
                           $addModule = $privObj->DoAddGroupModuleFromDummyMenu($value['child'][$i]['menu_id'], $idDec);
                           $updateMenu = $addMenu && $addModule;
                           if (!$updateMenu) {
                              break;
                              break;
                           }
                        }              
                     } else {
                        break;
                     } 
                  }
               }
               //sini ganti
               //exit;
//               echo "updateMenu=" .$updateMenu;
               
               // privileges query builder
               $menu_report = (isset($_POST['menu_report'])) ? $_POST['menu_report']->AsArray() : array();
               if ($updateMenu) $updateMenu = $privObj->DeletePrivilegesReport($idDec);
               if ($updateMenu) $updateMenu = $privObj->AddPrivilegesReport($idDec, $menu_report);
               // ---------
               
               $groupObj->EndTrans($updateMenu); 
            } else {
               $groupObj->EndTrans(false); 
            }
            
            if ($updateMenu === true) {
               $additionalUrl = "update|"; 
            } else {
               $additionalUrl = "update|fail".$_POST['groupname']. $_POST['deskripsi']. $idDec;
            }
            
            $this->RedirectTo(Dispatcher::Instance()->GetUrl('user', 'group', 'view', 'html') . 
               '&err=' . Dispatcher::Instance()->Encrypt($additionalUrl));
         } else {
            $this->RedirectTo(Dispatcher::Instance()->GetUrl('user', 'inputGroup', 'view', 'html') . 
               '&grp=' . $_REQUEST['grp'] . '&err=' . Dispatcher::Instance()->Encrypt('Nama Group'));
         }
      }else {
         $this->RedirectTo(Dispatcher::Instance()->GetUrl('user', 'group', 'view', 'html')) ;
      }

      return NULL;
   }

   function ParseTemplate($data = NULL) {
    
   }
}
?>
