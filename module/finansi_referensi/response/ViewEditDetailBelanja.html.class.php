<?php
/**
* ================= doc ====================
* FILENAME     : ViewEditDetailBelanja.html.class.php
* @package     : ViewEditDetailBelanja
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2014-01-08
* @Modified    : 2014-01-08
* @Analysts    : Dyah Fajar
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/'.Dispatcher::Instance()->mModule.'/business/FinansiReferensi.class.php';

class ViewEditDetailBelanja extends HtmlResponse
{
   function TemplateModule(){
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
      'module/'.Dispatcher::Instance()->mModule.'/template/');
      $this->SetTemplateFile('view_edit_detail_belanja.html');
   }

   function ProcessRequest(){
      $requestData            = array();
      $dataUnitKerja          = array();
      $index                  = 0;
      $messenger              = Messenger::Instance()->Receive(__FILE__);
      $mObj                   = new FinansiReferensi();
      $queryString            = $mObj->__getQueryString();
      $komponenId             = Dispatcher::Instance()->Decrypt($mObj->_GET['komponen_id']);
      $dataId                 = Dispatcher::Instance()->Decrypt($mObj->_GET['data_id']);
      $dataDetail             = $mObj->ChangeKeyName($mObj->GetDetailBelanjaDetail($komponenId, $dataId));
      $dataKomponenUnit       = $mObj->ChangeKeyName($mObj->GetDataKomponenUnit($komponenId, $dataId));

      $requestData['komponen_id']         = $komponenId;
      $requestData['id']                  = $dataDetail['id'];
      $requestData['detail_belanja_id']   = $dataDetail['id'];
      $requestData['detail_belanja']      = $dataDetail['nama'];
      $requestData['nominal']             = $dataDetail['nominal'];

      if(!empty($dataKomponenUnit)){
         foreach ($dataKomponenUnit as $unitKomponen) {
            $dataUnitKerja[$index]['id']     = $unitKomponen['unit_id'];
            $dataUnitKerja[$index]['kode']   = $unitKomponen['unit_kode'];
            $dataUnitKerja[$index]['nama']   = $unitKomponen['unit_nama'];
            $index++;
         }
      }

      if($messenger){
         $messengerData       = $messenger[0][0];
         $messengerMsg        = $messenger[0][1];
         $messengerStyle      = $messenger[0][2];

         $requestData['id']                  = $messengerData['id'];
         $requestData['detail_belanja_id']   = $messengerData['detail_belanja_id'];
         $requestData['detail_belanja']      = $messengerData['detail_belanja'];
         $requestData['nominal']             = $messengerData['nominal'];
         if(!empty($messengerData['unit'])){
            $index      = 0;
            foreach ($messengerData['unit'] as $unit) {
               $dataUnitKerja[$index]  = $unit;
               $index++;
            }
         }

      }

      $return['query_string']       = $queryString;
      $return['komponen_id']        = $komponenId;
      $return['request_data']       = $requestData;
      $return['message']            = $messengerMsg;
      $return['style']              = $messengerStyle;
      $return['unit_kerja']['data'] = json_encode($dataUnitKerja);
      return $return;
   }

   function ParseTemplate($data = null){
      $queryString      = $data['query_string'];
      $unit             = $data['unit'];
      $komponenId       = $data['komponen_id'];
      $requestData      = $data['request_data'];
      $message          = $data['message'];
      $style            = $data['style'];
      $dataUnit         = $data['unit_kerja'];

      $urlReturn        = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         'ManajemenDetailBelanja',
         'view',
         'html'
      ).'&'.$queryString;

      $urlPopupDetailBelanja  = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         'PopupDetailBelanja',
         'view',
         'html'
      ).'&komponen_id='.$komponenId;

      $urlAction        = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         'UpdateDetailBelanja',
         'do',
         'json'
      ).'&'.$queryString;

      $urlUnitKerja     = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         'GetUnitKerja',
         'do',
         'json'
      );

      $this->mrTemplate->AddVar('content', 'URL_RETURN', $urlReturn);
      $this->mrTemplate->AddVar('content', 'URL_ACTION', $urlAction);
      $this->mrTemplate->AddVars('content', $requestData);
      $this->mrTemplate->AddVar('content', 'POPUP_DETAIL_BELANJA', $urlPopupDetailBelanja);
      $this->mrTemplate->AddVar('content', 'URL_GET_UNIT_KERJA', $urlUnitKerja);
      $this->mrTemplate->AddVars('content', $dataUnit, 'UNIT_');

      if($message){
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $message);
         $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $style);
      }
   }
}
?>