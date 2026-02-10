<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/user/business/AppGroup.class.php';

class ViewGroup extends HtmlResponse {

   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue( 'application', 'docroot').
         'module/user/template');
      $this->SetTemplateFile('view_group.html');
   }
   
   function ProcessRequest() {
      $groupObj = new AppGroup();
      $dataGroup = $groupObj->GetDataGroup('', true);
	   $return['dataGroup'] = $dataGroup;
      if (isset ($_GET['err'])) {
         $err = explode('|',Dispatcher::Instance()->Decrypt($_GET['err']));
         $return['actionResult']['action'] = $err[0];
         $return['actionResult']['err'] = $err[1];
      }
      return $return;
   }

   function ParseTemplate($data = NULL) {
      $this->mrTemplate->AddVar('content', 'URL_SEARCH', Dispatcher::Instance()->GetUrl('user', 'group', 'view', 'html') );
      $this->mrTemplate->AddVar('content', 'GROUP_URL_ADD', Dispatcher::Instance()->GetUrl('user', 'inputGroup', 'view', 'html') );
      
      if (isset($data['actionResult'])){
         if ($data['actionResult']['err'] == "") {
            $class = 'notebox-done';
            if($data['actionResult']['action'] == 'add') 
               $isiPesan = 'Penambahan data grup berhasil dilakukan.';
            else if($data['actionResult']['action'] == 'delete') 
               $isiPesan = 'Penghapusan data grup berhasil dilakukan.';
            else 
               $isiPesan = 'Pengubahan data grup berhasil dilakukan.';
         } else {
            $class = 'notebox-warning';
            if($data['actionResult']['action'] == 'add') 
               $isiPesan = 'Penambahan data grup tidak berhasil.';
            else if($data['actionResult']['action'] == 'delete') 
               $isiPesan = 'Penghapusan data grup tidak berhasil.';
            else 
               $isiPesan = 'Pengubahan data grup tidak berhasil.';
         }
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $isiPesan);
         $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $class);
      }
      
      if (empty($data['dataGroup'])) {
         $this->mrTemplate->AddVar('data_group', 'GROUP_EMPTY', 'YES');
      } else {
         $this->mrTemplate->AddVar('data_group', 'GROUP_EMPTY', 'NO');
         $dataGroup = $data['dataGroup'];
         
         $len = sizeof($dataGroup);
         $menuName='';
         $idGroup='';
         $no=0;
         for ($i=0; $i<$len; $i++) {
            if($idGroup!=$dataGroup[$i]['group_id']){
               $no++;
               $menuBaru[$no]['no']=$no;
               $menuBaru[$no]['group_id']=$dataGroup[$i]['group_id'];
               $menuBaru[$no]['group_name']=$dataGroup[$i]['group_name'];
               $idGroup=$dataGroup[$i]['group_id'];
               $menuName="";
            }
            if($dataGroup[$i]['menu_name']!=$menuName){
               $menuBaru[$no]['hak_akses'] .='<strong>'.$dataGroup[$i]['menu_name'].'</strong><br>'.'&nbsp;&nbsp;'.$dataGroup[$i]['sub_menu'].'<br>';
               $menuName=$dataGroup[$i]['menu_name'];
            }else $menuBaru[$no]['hak_akses'].='&nbsp;&nbsp;'.$dataGroup[$i]['sub_menu'].'<br>';
         }
         
         $no=1;
         for($i=1;$i<count($menuBaru)+1;$i++){            
            $menuBaru[$i]['number'] = $no;
            if ($no % 2 != 0) {
               $dataGroup[$i]['class_name'] = 'table-common-even';
            } else {
               $dataGroup[$i]['class_name'] = '';
            }
            $no++;
            $idEnc = Dispatcher::Instance()->Encrypt($menuBaru[$i]['group_id']);
            $menuBaru[$i]['url_edit'] = Dispatcher::Instance()->GetUrl('user', 'inputGroup', 'view', 'html') . '&grp=' . $idEnc;

            $idEnc = Dispatcher::Instance()->Encrypt($menuBaru[$i]['group_id']);
                        
            $urlAccept = 'user|deleteGroup|do|html-cari-'.$cari;
            $urlReturn = 'user|group|view|html-cari-'.$cari;
            $label = 'Group';
            $dataName = $menuBaru[$i]['group_name'];
            $menuBaru[$i]['url_delete'] = Dispatcher::Instance()->GetUrl('confirm', 'confirmDelete', 'do', 'html').'&urlDelete='. $urlAccept.'&urlReturn='.$urlReturn.'&id='.$idEnc.'&label='.$label.'&dataName='.$dataName;

                  //$dataGroup[$i]['url_delete'] = Dispatcher::Instance()->GetUrl('user', 'deleteGroup', 'do', 'html') . '&grp=' . $idEnc;
                  $this->mrTemplate->AddVars('data_group_item', $menuBaru[$i], 'GROUP_');
                  $this->mrTemplate->parseTemplate('data_group_item', 'a');
         }
            /*$menuId = explode('|', $dataGroup[$i]['menu_id']);                    
            $menuName = explode('|', $dataGroup[$i]['menu_name']);
            $parentMenu = explode('|', $dataGroup[$i]['parent_menu']);
            $mlen=sizeof($menuId);
            $s=0;
            for ($m=0;$m<$mlen;$m++) {    
               if ($parentMenu[$m]==0) {
               $menuBaru[$s]='<b>'.$menuName[$m].'</b><br>';
               for ($mm=0;$mm<$mlen;$mm++) {
                  if ($menuId[$m]==$parentMenu[$mm]) {
                     $menuBaru[$s]=$menuBaru[$s].'&nbsp;&nbsp;'.$menuName[$mm].'<br>';
                  }
               }
               $s++;
               }
            }
            $dataGroup[$i]['hak_akses']='';
            
            for ($k=0;$k<$s;$k++) {
               $dataGroup[$i]['hak_akses']=$dataGroup[$i]['hak_akses'].$menuBaru[$k];
            }
            
            
               
            
         }*/
      }
   }
}
?>
