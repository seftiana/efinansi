<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/user/business/AppGroup.class.php';
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/user/business/GroupPrivilege.class.php';

class ProcessAddGroup{
   
   function AddGroup() {      
      if (isset($_POST['btnsimpan'])) {
         $groupObj = new AppGroup();
         $privObj = new GroupPrivilege();
         $additionalUrl = "";
         $arrMenu = array();
         if ($_POST['groupname']!="") {
            $menu = array();
            $addMenu = true;
            if (isset($_POST['menu'] )) {
               $addMenu = false;
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
            $addGroup = $groupObj->DoAddGroup($_POST['groupname'], $_POST['deskripsi']);
            $addModule = $privObj->DoAddGroupModule('', 5, true);
            $addModulePesan = $privObj->DoAddGroupModuleByModuleName('collaboration');
            if ($addGroup && $addModule && $addModulePesan) {
               if (!empty($arrMenu)) {
                  foreach($arrMenu as $key=>$value) {
                     //add ParentMenu                    
                     $addMenu = $privObj->DoAddGroupMenuForNewGroup($value['parent']['menu_name'], 
                        $value['parent']['default_module_id'], 0, $value['parent']['is_show']);
                     $parentId = $privObj->GetMaxMenuId();
                     if ($addMenu && $parentId) {
                        // add anak2nya
                        $len= sizeof($value['child']) ;
                        for ($i=0; $i<$len; $i++) {
                           $addMenu = $privObj->DoAddGroupMenuForNewGroup($value['child'][$i]['menu_name'], 
                              $value['child'][$i]['default_module_id'], $parentId, $value['child'][$i]['is_show']);
                           $addModule = $privObj->DoAddGroupModuleFromDummyMenu($value['child'][$i]['menu_id']);
                           $addMenu = $addMenu && $addModule;
                           if (!$addMenu) {
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
               $groupObj->EndTrans($addMenu); 
            } else {
               $groupObj->EndTrans(false); 
            }
            
            if ($addMenu === true) {
               $additionalUrl = "add|"; 
            } else {
               $additionalUrl = "add|fail";
            }
            $urlRedirect = Dispatcher::Instance()->GetUrl('user', 'group', 'view', 'html') .
               '&err=' . Dispatcher::Instance()->Encrypt($additionalUrl); 
         } else {
            $additionalUrl = "namaGroup";
            $urlRedirect = Dispatcher::Instance()->GetUrl('user', 'inputGroup', 'view', 'html') .
               '&err=' . Dispatcher::Instance()->Encrypt($additionalUrl); 
         }
      } else {
         $urlRedirect = Dispatcher::Instance()->GetUrl('user', 'group', 'view', 'html');
      }
      return $urlRedirect;
   }
}
?>
