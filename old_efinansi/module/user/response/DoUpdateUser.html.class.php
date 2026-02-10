<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/user/business/AppUser.class.php';
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/user_unit_kerja/business/UserUnitKerja.class.php';

class DoUpdateUser extends HtmlResponse {

   function TemplateModule() {
   }
   
   function ProcessRequest() {
      $idDec = Dispatcher::Instance()->Encrypt($_REQUEST['usr']);
      $userObj = new AppUser();
      $ObjUserUnitKerja =  new UserUnitKerja();
      $additionalUrl = "";

      if (isset($_POST['btnsimpan'])) {


         if($_POST['username']==''){
            $additionalUrl = "add|emptyUser";
            return Dispatcher::Instance()->GetUrl('user', 'user', 'view', 'html') . 
                  '&err=' . Dispatcher::Instance()->Encrypt($additionalUrl);
            exit;
         }

         if ($_POST['username']!="") {
            $ObjUserUnitKerja->StartTrans();
            $update = $userObj->DoUpdateUser($_POST['username'], $_POST['realname'],
                $_POST['status'], $_POST['group'], $_POST['deskripsi'], $idDec);
            $rsUUK = $ObjUserUnitKerja->UpdateUserUnitKerjaByUserId(array($_POST['unit_kerja'],$_POST['role'],$_POST['user_id']));
            $ObjUserUnitKerja->EndTrans($rsUUK);
            if ($rsUUK === true) {
               $additionalUrl = "update|";
            } else {
               $additionalUrl = "update|fail";
            }
            $this->RedirectTo(Dispatcher::Instance()->GetUrl('user', 'user', 'view', 'html') . 
               '&err=' . Dispatcher::Instance()->Encrypt($additionalUrl));
         } else {
            $this->RedirectTo(Dispatcher::Instance()->GetUrl('user', 'inputUser', 'view', 'html') . 
               '&usr=' . $_REQUEST['usr'] . '&err=' . Dispatcher::Instance()->Encrypt('Nama Pengguna'));
         }
      } else {
         $this->RedirectTo(Dispatcher::Instance()->GetUrl('user', 'user', 'view', 'html').'&cari='.$_POST['cari']) ;
      }
      return NULL;
   }

   function ParseTemplate($data = NULL) {
   }
}
?>
