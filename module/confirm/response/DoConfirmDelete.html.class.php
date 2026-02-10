<?php
class DoConfirmDelete extends HtmlResponse {
   function TemplateModule(){
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue( 'application', 'docroot') .
         'module/confirm/template');
      $this->SetTemplateFile('confirm_delete.html');
   }
   function ProcessRequest() {
      $method        = $_SERVER['REQUEST_METHOD'];
      $_POST         = is_object($_POST) ? $_POST->AsArray() : $_POST;
      $_GET          = is_object($_GET) ? $_GET->AsArray() : $_GET;
      $return[0]['emptydata']    = 'YES';
      $return[0]['multiple']     = "NO";
      $return[0]['urlDelete']    = null;
      $return[0]['label']        = null;
      $return[0]['urlReturn']    = null;
      $return[0]['message']      = null;

      if(strtolower($method) === 'post'){
         if(isset($_POST['id'])){
            $idDelete   = $_POST['id'];
            // $idDelete   = array_unique($idDelete);

            if(!empty($idDelete)){
               $return[0]['emptydata']    = 'NO';
               $return[0]['multiple']     = 'YES';
               for($i=0;$i<sizeof($idDelete);$i++) {
                  $return[$i]['id'] = $idDelete[$i];
                  $return[$i]['dataname'] = $_POST['name'][$idDelete[$i]];
                  if(isset($_POST['is_parent']) && $_POST['is_parent'][$idDelete[$i]]){
                     $return[0]['message'] = "<br />" . $msg[0][3];
                  }
               }
            }
         }
         $msg                       = Messenger::Instance()->Receive(__FILE__);
         $return[0]['label']        = $msg[0][0];
         $return[0]['urlDelete']    = $msg[0][1];
         $return[0]['urlReturn']    = $msg[0][2];

         if(isset($_POST['control_action'])){
            $return[0]['urlDelete'] = $_POST['control_action'];
            $return[0]['label']     = $_POST['control_label'];
            $return[0]['urlReturn'] = $_POST['control_return'];
         }
      }
      if(strtolower($method) === 'get'){
         if(isset($_GET['urlDelete'])){
            $deleteUrl              = Dispatcher::Instance()->Decrypt((string)$_GET['urlDelete']);
            $urlDel                 = explode('-',$deleteUrl);
            $newUrl                 = explode('|',$urlDel['0']);
            $par                    = explode('|',$urlDel['1']);
            $val                    = explode('|',$urlDel['2']);
            for($i=0;$i<count($par);$i++){
               $str .= '&'.$par[$i].'='.Dispatcher::Instance()->Encrypt($val[$i]);
            }
            $return[0]['urlDelete'] = Dispatcher::Instance()->GetUrl(
               $newUrl['0'],
               $newUrl['1'],
               $newUrl['2'],
               $newUrl['3']
            ).$str;
         }
         if(isset($_GET['urlReturn'])){
            $returnUrl  = Dispatcher::Instance()->Decrypt((string)$_GET['urlReturn']);
            $urlRet     = explode('-',$returnUrl);
            $newUrl     = explode('|',$urlRet['0']);
            $par        = explode('|',$urlRet['1']);
            $val        = explode('|',$urlRet['2']);
            $str        = '';
            for($i=0;$i<count($par);$i++) {
               $str     .= '&'.$par[$i].'='.Dispatcher::Instance()->Encrypt($val[$i]);
            }
            $return[0]['urlReturn'] = Dispatcher::Instance()->GetUrl($newUrl['0'],$newUrl['1'],$newUrl['2'],$newUrl['3']).$str;
         }

         $return[0]['label']     = isset($_GET['label']) ? Dispatcher::Instance()->Decrypt((string)$_GET['label']) : '';

         if(isset($_GET['id']) AND trim($_GET['id']) != ''){
            $return[0]['emptydata'] = 'NO';
            $return[0]['id']        = Dispatcher::Instance()->Decrypt((string)$_GET['id']);
            $return[0]['dataname']  = Dispatcher::Instance()->Decrypt($_GET['dataName']);
            $return[0]['message']   = Dispatcher::Instance()->Decrypt($_GET['message']);

            $return[0]['multiple']  = "NO";
         }
      }

      return $return;
    }

   function ParseTemplate($data = NULL) {
      $this->mrTemplate->AddVar('content', 'LABEL', $data[0]['label']);
      $this->mrTemplate->AddVar('emptydata', 'MESSAGE', $data[0]['message']);
      $this->mrTemplate->AddVar('emptydata', 'LABEL', $data[0]['label']);
      $this->mrTemplate->AddVar('emptydata', 'FORM_ACTION_URL', $data[0]['urlDelete']);
      $this->mrTemplate->AddVar('emptydata', 'URL_KEMBALI', $data[0]['urlReturn']);
      $this->mrTemplate->setAttribute('actions', 'visibility', 'hidden');

      if($data[0]['emptydata'] == "NO") {
         $this->mrTemplate->AddVar('emptydata', 'IS_EMPTY_DATA', 'NO');
         $this->mrTemplate->setAttribute('actions', 'visibility', 'show');
         if($data[0]['multiple'] == "YES") {
            $this->mrTemplate->AddVar('multiple_delete', 'IS_MULTIPLE_DELETE', 'YES');
            for($i=0;$i<sizeof($data);$i++) {
               $this->mrTemplate->AddVars('multiple_delete_item', $data[$i], 'MULTI_');
               $this->mrTemplate->parseTemplate('multiple_delete_item', 'a');
            }
         } else {
            $this->mrTemplate->AddVar('multiple_delete', 'IS_MULTIPLE_DELETE', 'NO');
            $this->mrTemplate->AddVar('multiple_delete', "ID", $data[0]['id']);
            $this->mrTemplate->AddVar('multiple_delete', 'DATANAME', $data[0]['dataname']);
         }
      } else {
         $this->mrTemplate->AddVar('emptydata', 'IS_EMPTY_DATA', 'YES');
      }
   }
}
?>