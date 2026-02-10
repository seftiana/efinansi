<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/user/business/AppGroup.class.php';
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/user/business/GroupPrivilege.class.php';

class DoUpdateGroup extends JsonResponse {

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
      $arrMenu = array();

      $sidebar = Dispatcher::Instance()->GetUrl('menu', 'menu', 'view', 'html').'&ascomponent=1';

      if (isset($_POST['btnsimpan'])) {
         if ($_POST['groupname']!="") {
            $updateMenu = true;
            if (isset($_POST['menu'])) {
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
            $deleteMenu = $privObj->DoDeleteGroupMenu($idDec);
            $deleteModule = $privObj->DoDeleteGroupModule($idDec);
            $updateDelete = $updateGroup && $deleteMenu && $deleteModule;
            $addModule = $privObj->DoAddGroupModule($idDec,'5');
            if ($updateDelete) {
               if (!empty($arrMenu)) {
                  foreach($arrMenu as $key=>$value) {
                     //add ParentMenu
                     $addMenu = $privObj->DoAddGroupMenu($value['parent']['menu_name'], $idDec,
                        $value['parent']['default_module_id'], 0, $value['parent']['is_show']);
                     $parentId = $privObj->GetMaxMenuId();
                     if ($addMenu && $parentId) {
                        // add anak2nya
                        $clen = sizeof($value['child']) ;
                        for ($j=0; $j<$clen; $j++) {
                           $addMenu = $privObj->DoAddGroupMenu($value['child'][$j]['menu_name'], $idDec,
                              $value['child'][$j]['default_module_id'], $parentId, $value['child'][$j]['is_show']);
                           $addModule = $privObj->DoAddGroupModuleFromDummyMenu($value['child'][$j]['menu_id'], $idDec );
                           $updateMenu = $addMenu && $addModule;
                           if (!$updateMenu) {
                              break;
                           }
                        }
                     } else {
                        break;
                     }
                  }
               }
               //sini ganti

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
               $additionalUrl = "update|fail";
            }


            return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element",
               " '.Dispatcher::Instance()->GetUrl('user', 'group', 'view', 'html') .
               '&err=' . Dispatcher::Instance()->Encrypt($additionalUrl).'&ascomponent=1");GtfwAjax.replaceContentWithUrl("sidebarContents","'.$sidebar.'")');
         } else {
            return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element",
               " '.Dispatcher::Instance()->GetUrl('user', 'inputGroup', 'view', 'html') .
               '&grp=' . $_REQUEST['grp'] . '&err=' . Dispatcher::Instance()->Encrypt('Nama Group').
               '&ascomponent=1")');
         }
      } else {
         return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element",
               " '.Dispatcher::Instance()->GetUrl('user', 'group', 'view', 'html').
               '&ascomponent=1");GtfwAjax.replaceContentWithUrl("sidebarContents","'.$sidebar.'")');
      }
   }

   function ParseTemplate($data = NULL) {

   }
}
?>