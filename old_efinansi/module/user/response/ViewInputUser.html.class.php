<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/user/business/AppUser.class.php';
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/user/business/AppGroup.class.php';
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/unitkerja/business/AppUnitkerja.class.php';
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/role/business/Role.class.php';

class ViewInputUser extends HtmlResponse{

   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot') .
         'module/user/template');
      $this->SetTemplateFile('input_user.html');
   }
   
   function ProcessRequest() {
      $idDec = Dispatcher::Instance()->Decrypt($_REQUEST['usr']);
      $userObj = new AppUser();
      $groupObj = new AppGroup();
            
      $groupName = $groupObj->GetDataGroup("", false);      
      $dataUser = $userObj->GetDataUserById($idDec);               

      Messenger::Instance()->SendToComponent('combobox', 'Combobox', 'view', 'html', 'group', 
         array('group',$groupName,$dataUser['0']['group_id'],'false',''), Messenger::CurrentRequest);
      
      $ObjRole = new Role();
      $dt_role = $ObjRole->GetComboRole();
      Messenger::Instance()->SendToComponent('combobox', 'Combobox', 'view', 'html', 'role', 
         array('role',$dt_role,$dataUser['0']['role_id'],'false',''), Messenger::CurrentRequest);
      
      $ObjSatuanKerja = new AppUnitkerja();
      $dt_unitker = $ObjSatuanKerja->GetComboUnitKerja();
      Messenger::Instance()->SendToComponent('combobox', 'Combobox', 'view', 'html', 'unit_kerja', 
         array('unit_kerja',$dt_unitker,$dataUser['0']['unit_kerja_id'],'false',''), Messenger::CurrentRequest);
    
      $return['dataUser'] = $dataUser;      
      $return['groupName'] = $groupName;
      if (isset($_GET['err']) ) {
         $return['error'] = Dispatcher::Instance()->Decrypt($_GET['err']);
      }
      return $return;
   }

   function ParseTemplate($data = NULL) {
      if (isset ($data['error'])) {
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', 'Data '. $data['error'] .' tidak boleh kosong.');
      }
      $dataUser = $data['dataUser'];
      
      $status='checked="checked"';
      $nstatus='';
      
      if (isset($dataUser['0']['is_active']) && $dataUser['0']['is_active']!='Yes') {
         $status='';
         $nstatus='checked="checked"';
      }   
      if ($_REQUEST['usr']=='') {
         $this->mrTemplate->SetAttribute('view_password', 'visibility', 'visible');
         $url="addUser";
         $tambah="Tambah";
      } else {
         $url="updateUser";
         $tambah="Ubah";  
      }
      $this->mrTemplate->AddVar('content', 'JUDUL', $tambah);
      $this->mrTemplate->AddVar('content', 'USERNAME', $dataUser[0]['user_name']);
      $this->mrTemplate->AddVar('content', 'USR', $_REQUEST['usr']);
      $this->mrTemplate->AddVar('content', 'CARI', $_GET['cari']);
      $this->mrTemplate->AddVar('content', 'REALNAME', $dataUser[0]['real_name']);
      $this->mrTemplate->AddVar('content', 'DESKRIPSI', $dataUser[0]['description']);
      $this->mrTemplate->AddVar('content', 'USER_ID', $dataUser[0]['user_id']);
      
      
      $this->mrTemplate->AddVar('content', 'STATUS', $status);
      $this->mrTemplate->AddVar('content', 'NSTATUS', $nstatus);
      $this->mrTemplate->AddVar('content', 'URL_ACTION', Dispatcher::Instance()->GetUrl('user', $url, 'do', 'html') );
      $this->mrTemplate->AddVar('content', 'URL_VIEW', Dispatcher::Instance()->GetUrl('user', 'user', 'view', 'html') );
      $groupName = $data['groupName'];    
   }
}
?>
