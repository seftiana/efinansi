<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/user/business/AppUser.class.php';
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/user_unit_kerja/business/UserUnitKerja.class.php';

class ProsessAddUser {
      
   function AddUser() {

      $userObj = new AppUser();
      $ObjUserUnitKerja =  new UserUnitKerja();
      
      $additionalUrl = "";
      


      if (isset($_POST['btnsimpan'])) {
         //empty check

         if($_POST['username']==''){
            $additionalUrl = "add|emptyUser";
            return Dispatcher::Instance()->GetUrl('user', 'user', 'view', 'html') . 
                  '&err=' . Dispatcher::Instance()->Encrypt($additionalUrl);
            exit;
         }

         if($_POST['password']==''){
            $additionalUrl = "add|emptypassword";
            return Dispatcher::Instance()->GetUrl('user', 'user', 'view', 'html') . 
                  '&err=' . Dispatcher::Instance()->Encrypt($additionalUrl);
            exit;
         }

         if ($_POST['username']!="" and $_POST['password']!="") {
            $ObjUserUnitKerja->StartTrans();
            $addUser = $userObj->DoAddUser($_POST['username'], $_POST['password'], $_POST['realname'], 
               $_POST['deskripsi'], $_POST['status'], $_POST['group']);
            //get max id user
            $max_id = $userObj->GetMaxId();
            $addUUK = $ObjUserUnitKerja->InsertUserUnitKerja(array($max_id,$_POST['unit_kerja'],$_POST['role']));
            $ObjUserUnitKerja->EndTrans($addUUK);

            if ($addUUK === true) $additionalUrl = "add|";
            else $additionalUrl = "add|fail";
            $urlRedirect = Dispatcher::Instance()->GetUrl('user', 'user', 'view', 'html') . 
               '&err=' . Dispatcher::Instance()->Encrypt($additionalUrl);
         } else {
            if ($_POST['username'] == "") $additionalUrl = "Nama Pengguna";
            else $additionalUrl = "Password";
            $urlRedirect = Dispatcher::Instance()->GetUrl('user', 'inputUser', 'view', 'html') . 
               '&err=' . Dispatcher::Instance()->Encrypt($additionalUrl);
         }
      } else {
         $urlRedirect = Dispatcher::Instance()->GetUrl('user', 'user', 'view', 'html').'&cari='.$_POST['cari'] ;
      }
      return $urlRedirect;
    }   
}
?>
