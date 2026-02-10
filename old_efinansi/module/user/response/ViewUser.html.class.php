<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/user/business/AppUser.class.php';

class ViewUser extends HtmlResponse {

   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').'module/user/template');
      $this->SetTemplateFile('view_user.html');
   }

   function ProcessRequest() {

      $userObj = new AppUser();

      if (isset($_POST['username'])) $userName = $_POST['username'];
         elseif (isset($_GET['uname'])) $userName = Dispatcher::Instance()->Decrypt($_GET['uname']);

      if(isset($_POST['check'])){
         $user_check = $_POST['check']['user'];
         $pUserName = $userName;
      }elseif(isset($_GET['user_check'])){
         $user_check = Dispatcher::Instance()->Decrypt($_GET['user_check']);
         $pUserName = $userName;
      }else{
         $pUserName = '';
      }

      if (isset($_POST['username'])) $realName = $_POST['realname'];
         else if (isset($_GET['rname'])) $realName = Dispatcher::Instance()->Decrypt($_GET['rname']);

      if(isset($_POST['check'])){
          $real_check = $_POST['check']['real'];
          $pRealName = $realName ;
      }elseif(isset($_GET['real_check'])){
          $real_check = Dispatcher::Instance()->Decrypt($_GET['real_check']);
          $pRealName = $realName ;
      }else{
         $pRealName = '';
      }

      if (isset($_GET['cari'])) {
         $carii=explode("|", $_GET['cari']);
         $userName=$carii[0];
         $realName=$carii[1];
         $pUserName=$carii[0];
         $pRealName=$carii[1];
      }

      $totalData = $userObj->GetCountDataUser($pUserName, $pRealName);
      $itemViewed = 10;
      $currPage = 1;
      $startRec = 0 ;
      if(isset($_GET['page'])) {
         $currPage = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
         $startRec =($currPage-1) * $itemViewed;
      }

		$dataUser = $userObj->GetDataUser($startRec,$itemViewed, $pUserName, $pRealName);


      if(!empty($dataUser)){
         $url = Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule,
                  Dispatcher::Instance()->mSubModule,
                  Dispatcher::Instance()->mAction,
                  Dispatcher::Instance()->mType.
                  '&uname=' . Dispatcher::Instance()->Encrypt($userName).
                  '&rname=' . Dispatcher::Instance()->Encrypt($realName).
                  '&user_check='. Dispatcher::Instance()->Encrypt($user_check).
                  '&real_check='. Dispatcher::Instance()->Encrypt($real_check)
                  );
         Messenger::Instance()->SendToComponent('paging', 'Paging', 'view', 'html', 'paging_top',
            array($itemViewed,$totalData, $url, $currPage),
            Messenger::CurrentRequest);
      }
      if (isset ($_GET['err'])) {
         $err = explode('|',Dispatcher::Instance()->Decrypt($_GET['err']));
         $return['actionResult']['action'] = $err[0];
         $return['actionResult']['err'] = $err[1];
      }

      $return['dataUser'] = $dataUser;
      $return['start'] = $startRec+1;
      $return['search']['userName'] = $userName;
      $return['search']['realName'] = $realName;
      $return['check']['user'] = $user_check;
      $return['check']['real'] = $real_check;
      return $return;
   }

   function ParseTemplate($data = NULL) {

      if(($data['check']['real']!=''))
         $this->mrTemplate->AddVar('content', 'REAL_CHECKED', 'CHECKED');

      if(($data['check']['user']!=''))
      $this->mrTemplate->AddVar('content', 'USER_CHECKED', 'CHECKED');

      $cari=$data['search']['userName'].'|'.$data['search']['realName'];
      $this->mrTemplate->AddVar('content', 'USERNAME', $data['search']['userName']);
      $this->mrTemplate->AddVar('content', 'REALNAME', $data['search']['realName']);
      $this->mrTemplate->AddVar('content', 'URL_SEARCH', Dispatcher::Instance()->GetUrl('user', 'user', 'view', 'html') );
      $this->mrTemplate->AddVar('content', 'USER_URL_ADD', Dispatcher::Instance()->GetUrl('user', 'inputUser', 'view', 'html') );
      if (isset($data['actionResult'])){
         if ($data['actionResult']['err'] == "") {
            $class = 'notebox-done';
            if($data['actionResult']['action'] == 'add')
               $isiPesan = 'Penambahan data pengguna berhasil dilakukan.';
            else if($data['actionResult']['action'] == 'delete')
               $isiPesan = 'Penghapusan data pengguna berhasil dilakukan.';
            else if($data['actionResult']['action'] == 'upass_berhasil')
               $isiPesan = 'Perubahan password berhasil.';
            else
               $isiPesan = 'Pengubahan data pengguna berhasil dilakukan.';
         } else {
            $class = 'notebox-warning';
            #print_r($data['actionResult']['err']);exit;
            if ($data['actionResult']['err'] == "emptyUser") {
               $isiPesan = 'User Name Tidak Boleh Kosong.';
            }elseif ($data['actionResult']['err'] == "emptypassword") {
               $isiPesan = 'Password Tidak Boleh Kosong.';
            }else{
               if($data['actionResult']['action'] == 'add')
                  $isiPesan = 'Penambahan data pengguna tidak berhasil.';
               else if($data['actionResult']['action'] == 'delete')
                  $isiPesan = 'Penghapusan data pengguna tidak berhasil.';
               else
                  $isiPesan = 'Pengubahan data pengguna tidak berhasil.';
            }
         }
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $isiPesan);
         $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $class);
      }
      if (empty($data['dataUser'])) {
         $this->mrTemplate->AddVar('data_user', 'USER_EMPTY', 'YES');
      } else {
         $this->mrTemplate->AddVar('data_user', 'USER_EMPTY', 'NO');
         $dataUser = $data['dataUser'];
         $len = sizeof($dataUser);

         for ($i=0; $i<$len; $i++) {
               $no = $i+$data['start'];
               $dataUser[$i]['number'] = $no;
               if ($no % 2 != 0) {
                  $dataUser[$i]['class_name'] = 'table-common-even';
               } else {
                  $dataUser[$i]['class_name'] = '';
               }
               if ($dataUser[$i]['is_active'] == 'Yes') {
                  $dataUser[$i]['status'] = 'aktif';
               } else {
                  $dataUser[$i]['status'] = 'tidak aktif';
               }
               $idEnc = Dispatcher::Instance()->Encrypt($dataUser[$i]['user_id']);
               $dataUser[$i]['url_edit'] = Dispatcher::Instance()->GetUrl('user', 'inputUser', 'view', 'html') .
                  '&usr=' . $dataUser[$i]['user_id'].'&cari='.$cari;

               $urlAccept = 'user|deleteUser|do|html-cari-'.$cari;
               $urlReturn = 'user|user|view|html-cari-'.$cari;
               $label = 'User';
               $dataName = $dataUser[$i]['user_name'];
               $dataUser[$i]['url_delete'] = Dispatcher::Instance()->GetUrl('confirm', 'confirmDelete', 'do', 'html').'&urlDelete='. $urlAccept.'&urlReturn='.$urlReturn.'&id='.$idEnc.'&label='.$label.'&dataName='.$dataName;
               /*$dataUser[$i]['url_delete'] = Dispatcher::Instance()->GetUrl('user', 'deleteUser', 'do', 'html') .
                  '&usr=' . $idEnc.'&cari='.$cari;*/
               $dataUser[$i]['url_updatepassword'] = Dispatcher::Instance()->GetUrl('user', 'changePassword', 'view', 'html') .
                  '&usr=' . $idEnc.'&cari='.$cari;
               if($_SESSION['username']==$dataUser[$i]['user_name']){
                  $dataUser[$i]['display_status'] = 'none';
               }else{
                  $dataUser[$i]['display_status'] = '';
               }
               $this->mrTemplate->AddVars('data_user_item', $dataUser[$i], 'USER_');
               $this->mrTemplate->parseTemplate('data_user_item', 'a');
         }
      }
   }
}
?>