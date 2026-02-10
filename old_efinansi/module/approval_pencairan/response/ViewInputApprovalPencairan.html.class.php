<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/approval_pencairan/business/AppApprovalPencairan.class.php';

class ViewInputApprovalPencairan extends HtmlResponse
{
   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot') . 'module/approval_pencairan/template');
      $this->SetTemplateFile('input_approval_pencairan.html');
   }

   function ProcessRequest() {
      $messenger        = Messenger::Instance()->Receive(__FILE__);
      $mObj             = new AppApprovalPencairan();
      $dataId           = Dispatcher::Instance()->Decrypt($mObj->_GET['data_id']);
      $queryString      = $mObj->_getQueryString();
      $queryRequest     = preg_replace('/(data_id=[\d]+)/', '', $queryString);
      $queryRequest     = preg_replace('/(search=[\d]+)/', '', $queryString);
      $queryRequest     = preg_replace('/\&[\&]+/', '&', $queryString);
      $dataApproval     = $mObj->getDataDetail($dataId);
      $dataKomponen     = $mObj->getKomponenPencairan($dataId);
      $message          = $style = $messengerData = NULL;
      $requestData      = array();
      $arrStatus        = array(0 => array(
         'id' => 'Ya',
         'name' => 'Setuju'
      ), array(
         'id' => 'Tidak',
         'name' => 'Tidak Setuju'
      ));

      if($messenger){
         $messengerData    = $messenger[0][0];
         $message          = $messenger[0][1];
         $style            = $messenger[0][2];
         $requestData['status']  = $messengerData['status'];
         if($messengerData['KOMP'] && !empty($messengerData['KOMP'])){
            foreach ($dataKomponen as $key => $value) {
               $dataKomponen[$key]['nominal_approve'] = $messengerData['KOMP'][$value['realisasi_det_id']]['nominal_approve'];
            }
         }
      }

      # Combobox
      Messenger::Instance()->SendToComponent(
         'combobox',
         'Combobox',
         'view',
         'html',
         'status',
         array(
            'status',
            $arrStatus,
            $requestData['status'],
            'false',
            'id="cmb_status"'
         ),
         Messenger::CurrentRequest
      );

      $return['query_string']    = $queryString;
      $return['query_request']   = $queryRequest;
      $return['data_realisasi']  = $dataApproval;
      $return['komponen']['data']   = json_encode((array)$dataKomponen);
      $return['message']         = $message;
      $return['style']           = $style;
      return $return;
   }

   function ParseTemplate($data = NULL) {
       print_r($return['komponen']['data']);
      $message             = $data['message'];
      $style               = $data['style'];
      $queryString         = $data['query_string'];
      $queryRequest        = $data['query_request'];
      $dataRealisasi       = $data['data_realisasi'];
      $dataKomponen        = $data['komponen'];
      $urlAction           = Dispatcher::Instance()->GetUrl(
         'approval_pencairan',
         'updateApprovalPencairan',
         'do',
         'json'
      ).'&'.$queryString;

      $urlReturn           = Dispatcher::Instance()->GetUrl(
         'approval_pencairan',
         'ApprovalPencairan',
         'view',
         'html'
      ).'&search=1&'.$queryRequest;

      if($dataRealisasi['nominal'] < 0){
         $dataRealisasi['nominal_label']  = number_format(abs($dataRealisasi['nominal']), 2, ',','.');
      }else{
         $dataRealisasi['nominal_label']  = number_format($dataRealisasi['nominal'], 2, ',', '.');
      }
      $this->mrTemplate->AddVar('content', 'URL_ACTION', $urlAction);
      $this->mrTemplate->AddVar('content', 'URL_RETURN', $urlReturn);
      $this->mrTemplate->AddVars('content', $dataRealisasi);
      $this->mrTemplate->AddVars('content', $dataKomponen, 'KOMPONEN_');

      if($message){
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $message);
         $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $style);
      }
   }
}
?>