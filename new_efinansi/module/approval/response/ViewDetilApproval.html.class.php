<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/approval/business/AppDetilApproval.class.php';
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/user_unit_kerja/business/UserUnitKerja.class.php';

class ViewDetilApproval extends HtmlResponse {

   var $Pesan;
   var $decDataId;
   var $encDataId;

   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').'module/approval/template');
      $this->SetTemplateFile('view_detil_approval.html');
   }

   function TemplateBase() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
      $this->SetTemplateFile('document-common-popup.html');
      $this->SetTemplateFile('layout-common-popup.html');
   }

   function ProcessRequest() {
      $messenger  = Messenger::Instance()->Receive(__FILE__);
      $message    = $messengerData = $style = NULL;
      $mObj       = new AppDetilApproval();
      $dataId     = Dispatcher::Instance()->Decrypt($mObj->_GET['dataId']);
      $arrStatus  = array(0 => array(
         'id' => 'Ya',
         'name' => 'Disetujui'
      ), array(
         'id' => 'Belum',
         'name' => 'Belum'
      ), array(
         'id' => 'Tidak',
         'name' => 'Tidak'
      ));

      $requestData   = array();
      $queryRequest  = $mObj->_getQueryString();
      $dataKegiatan  = $mObj->getDataKegiatan($dataId);
      $dataList      = $mObj->getDataKegiatanDetail($dataId);
      $total_data    = $mObj->Count();
      $status        = array();
      if($messenger){
         $messengerData    = $messenger[0][0];
         $message          = $messenger[0][1];
         $style            = $messenger[0][2];

         if($messengerData['data'] AND !empty($messengerData['data'])){
            foreach ($messengerData['data'] as $data) {
               $requestData[$data['id']]  = $data;
               $status[]   = $data['status'];
            }
         }
      }

      $return['data_kegiatan']      = $dataKegiatan;
      $return['data_list']          = $dataList;
      $return['status_approval']    = $arrStatus;
      $return['query_string']       = $queryRequest;
      $return['message']            = $message;
      $return['style']              = $style;
      $return['request_data']       = $requestData;
      return $return;
   }

   function ParseTemplate($data = NULL) {
      $queryString      = $data['query_string'];
      $queryReturn      = preg_replace('/(dataId=[\d+])/', '', $queryString);
      $queryReturn      = preg_replace('/(search=[\d+])/', '', $queryString);
      $queryReturn      = preg_replace('/^[\&]+/', '', $queryReturn);
      $queryReturn      = preg_replace('/\&[\&]+/', '&', $queryReturn);
      $dataKegiatan     = $data['data_kegiatan'];
      $dataList         = $data['data_list'];
      $arrStatus        = $data['status_approval'];
      $message          = $data['message'];
      $style            = $data['style'];
      $requestData      = $data['request_data'];
      $kegiatanStatus   = array();
      $urlAction        = Dispatcher::Instance()->GetUrl(
         'approval',
         'updateDetilApproval',
         'do',
         'json'
      ) . '&' . $queryString;

      $urlHome          = Dispatcher::Instance()->GetUrl(
         'approval',
         'approval',
         'view',
         'html'
      ).'&search=1&'.$queryReturn;

      $this->mrTemplate->AddVars('data_kegiatan', $dataKegiatan);
      $this->mrTemplate->AddVar('content', 'URL_ACTION', $urlAction);
      $this->mrTemplate->AddVar('content', 'URL_RETURN', $urlHome);
      $this->mrTemplate->SetAttribute('btn_action', 'visibility', 'visible');
      if(empty($dataList)){
         $this->mrTemplate->AddVar('data_list', 'DATA_EMPTY', 'YES');
      }else{
         $this->mrTemplate->AddVar('data_list', 'DATA_EMPTY', 'NO');
         foreach ($dataList as $list) {
            $kegiatanStatus[]   = strtoupper($list['status']);
            $idx        = $list['id'];
            if($requestData[$idx] AND !empty($requestData[$idx])){
               $list['status']                  = $requestData[$idx]['status'];
               $list['satuan_approve']          = $requestData[$idx]['satuan_approve'];
               $list['satuan_nominal_approve']  = $requestData[$idx]['nominal_satuan_approve'];
               $list['nominal_total_approve']   = $requestData[$idx]['nominal_approve'];
            }
            foreach ($arrStatus as $key => $status) {
               if(strtolower($status['id']) == strtolower($list['status'])){
                  $arrStatus[$key]['selected']     = 'selected';
               }else{
                  $arrStatus[$key]['selected']     = null;
               }
            }

            switch (strtoupper($list['status'])) {
               case 'YA':
                  $list['class_row']            = 'success';
                  $list['readonly']             = 'readonly';
                  $list['display_approve']      = '';
                  $list['display_unapprove']    = 'display : none;';
                  $list['status']               = 'Sudah Disetujui';
                  break;
               case 'BELUM':
                  $list['class_row']            = 'onprocess';
                  $list['readonly']             = '';
                  $list['display_approve']      = 'display : none;';
                  $list['display_unapprove']    = '';
                  $list['status']               = '';
                  break;
               case 'TIDAK':
                  $list['class_row']            = 'fail';
                  $list['readonly']             = '';
                  $list['display_approve']      = '';
                  $list['display_unapprove']    = 'display : none;';
                  $list['status']               = 'Tidak Disetujui';
                  break;
               default:
                  $list['class_row']            = '';
                  $list['readonly']             = '';
                  $list['display_approve']      = '';
                  $list['display_unapprove']    = '';
                  $list['status']               = '';
                  break;
            }
            $this->mrTemplate->clearTemplate('list_status');
            $this->mrTemplate->addRows('list_status', $arrStatus);
            $this->mrTemplate->AddVars('items', $list);
            $this->mrTemplate->parseTemplate('items', 'a');
         }
      }

      if($message){
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $message);
         $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $style);
      }

      $stat    = array_unique($kegiatanStatus);
      if(!empty($status) && count($stat) == 1 AND $stat[0] == 'YA'){
         $this->mrTemplate->SetAttribute('btn_action', 'visibility', 'hidden');
      }
   }
}
?>
