<?php

require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/user/business/AppGroup.class.php';
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/user/business/GroupPrivilege.class.php';

class ViewInputGroup extends HtmlResponse {

   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue( 'application', 'docroot').'module/user/template');
      $this->SetTemplateFile('input_group.html');
   }
   
   function ProcessRequest() {
      $privObj = new GroupPrivilege();

      if (isset ($_REQUEST['grp'])){
         $idDec = Dispatcher::Instance()->Decrypt($_REQUEST['grp']);
         $groupObj = new AppGroup();
         $return['dataGroup'] = $groupObj->GetDataGroupById($idDec);
      } else $return['dataGroup'] = null;
      
      $return['menuGroup'] = $privObj->GetAllPrivilege($idDec);
      $return['menuGroupReport'] = $privObj->GetAllPrivilegeReport($idDec);
      
      if (isset($_GET['err'])) $return['err'] =  Dispatcher::Instance()->Decrypt($_GET['err']);
      return $return;  
   }

   function ParseTemplate($data = NULL) {
      if (isset($data['err'])) {
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', 'Nama Group tidak boleh kosong');
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
      }
        
      if ($_REQUEST['grp']=='') {
         $url="addGroup";
         $tambah="Tambah";
      } else {
         $url="updateGroup";
         $tambah="Ubah";
      }
      $idGroup = Dispatcher::Instance()->Decrypt($_GET['grp'])->Raw();
      $dataGroup = $data['dataGroup'];
      $this->mrTemplate->AddVar('content', 'GROUPNAME', $dataGroup[0]['group_name']);
      $this->mrTemplate->AddVar('content', 'DESKRIPSI', $dataGroup[0]['group_description']);
      $this->mrTemplate->AddVar('content', 'GRP', $idGroup);
      
      $this->mrTemplate->AddVar('content', 'JUDUL', $tambah);
      $this->mrTemplate->AddVar('content', 'URL_ACTION', Dispatcher::Instance()->GetUrl('user', $url, 'do', 'html'));
      $menu = explode('|',$dataGroup[0]['menu_name']);
      $menuGroup = $data['menuGroup'];
      
      $len = sizeOf($menuGroup);
      $mlen = sizeOf($menu);
      for ($i=0;$i<$len;$i++) {  
         if ($menuGroup[$i]['menu_parent_id']==0) {
            $parent=$menuGroup[$i]['menu_name'];
            $this->mrTemplate->addVar('menu', 'MENU_PARENT', 'YES');
            $this->mrTemplate->addVar('menu', 'PARENT_MENU', $parent);
            $this->mrTemplate->parseTemplate('menu', 'a'); 
            for ($j=$i;$j<$len;$j++) { 
               if ($menuGroup[$j]['menu_parent_id']==$menuGroup[$i]['menu_id']) {
                  $idmenu=$menuGroup[$j]['menu_id'];
                  $menu_name=$menuGroup[$j]['menu_name'];             
                  for ($k=0;$k<$mlen;$k++) { 
                     if (!empty($menuGroup[$j]['MenuName'])) {
                         $this->mrTemplate->addVar('menu', 'CHECK', 'checked'); 
                         break;
                     } else $this->mrTemplate->addVar('menu', 'CHECK', '');
                  } 
                  $this->mrTemplate->addVar('menu', 'MENU_PARENT', 'NO');
                  $this->mrTemplate->addVar('menu', 'IDMENU', $idmenu);  
                  $this->mrTemplate->addVar('menu', 'MENU', $menu_name);       
                  $this->mrTemplate->parseTemplate('menu', 'a');
               } 
            } 
        }
      }
      
      foreach ($data['menuGroupReport'] as $value)
      {
         $value['menu_parent'] = ($value['dummy_parent_menu_id'] == 0) ? 'YES' : 'NO';
         $value['checked'] = ($value['menu_group_id'] == 0) ? '' : 'checked';
         
         $this->mrTemplate->addVars('menu_report', $value); 
         $this->mrTemplate->parseTemplate('menu_report', 'a');
      }
   }
}
?>
