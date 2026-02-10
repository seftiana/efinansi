<?php
/**
* ================= doc ====================
* FILENAME     : ViewEditMataAnggaran.html.class.php
* @package     : ViewEditMataAnggaran
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-03-29
* @Modified    : 2015-03-29
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/finansi_mata_anggaran/business/MataAnggaran.class.php';

class ViewEditMataAnggaran extends HtmlResponse
{
   function TemplateModule(){
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
      'module/finansi_mata_anggaran/template/');
      $this->SetTemplateFile('view_edit_mata_anggaran.html');
   }

   function ProcessRequest(){
      $messenger     = Messenger::Instance()->Receive(__FILE__);
      $mObj          = new MataAnggaran();
      $querystring   = $mObj->_getQueryString();
      $basType       = $mObj->getBasType();
      $dataId        = Dispatcher::Instance()->Decrypt($mObj->_GET['data_id']);
      $requestData   = array();
      $message       = $style = $messengerData = NULL;
      $dataDetail    = $mObj->getDataDetail($dataId);
      $requestData['debet_checked']    = 'checked';
      $requestData['kredit_checked']   = '';
      $requestData['status_checked']   = 'checked';

      if(!empty($dataDetail)){
         $requestData['id']         = $dataDetail['id'];
         $requestData['coa_id']     = $dataDetail['akun_id'];
         $requestData['coa_kode']   = $dataDetail['akun_kode'];
         $requestData['coa_nama']   = $dataDetail['akun_nama'];
         $requestData['bas_id']     = $dataDetail['bas_id'];
         $requestData['bas']        = $dataDetail['bas_nama'];
         $requestData['kode']       = $dataDetail['kode'];
         $requestData['nama']       = $dataDetail['nama'];
         $requestData['status']     = $dataDetail['status'];
         switch (strtoupper($dataDetail['status'])) {
            case 'T':
               $requestData['status_checked']   = '';
               break;
            case 'Y':
               $requestData['status_checked']   = 'checked';
               break;
            default:
               $requestData['status_checked']   = '';
               break;
         }
         $requestData['bas_tipe']   = $dataDetail['tipe_id'];
         if($dataDetail['nilai_default']){
            switch (strtoupper($dataDetail['nilai_default'])) {
               case 'D':
                  $requestData['debet_checked']    = 'checked';
                  $requestData['kredit_checked']   = '';
                  break;
               case 'K':
                  $requestData['debet_checked']    = '';
                  $requestData['kredit_checked']   = 'checked';
                  break;
               default:
                  $requestData['debet_checked']    = 'checked';
                  $requestData['kredit_checked']   = '';
                  break;
            }
         }
      }

      if($messenger){
         $messengerData    = $messenger[0][0];
         $message          = $messenger[0][1];
         $style            = $messenger[0][2];

         $requestData['id']         = $messengerData['data_id'];
         $requestData['coa_id']     = $messengerData['coa_id'];
         $requestData['coa_kode']   = $messengerData['coa_kode'];
         $requestData['coa_nama']   = $messengerData['coa_nama'];
         $requestData['bas_id']     = $messengerData['bas_id'];
         $requestData['bas']        = $messengerData['bas'];
         $requestData['kode']       = $messengerData['kode'];
         $requestData['nama']       = $messengerData['nama'];
         $requestData['status']     = $messengerData['status'];
         switch (strtoupper($messengerData['status'])) {
            case 'T':
               $requestData['status_checked']   = '';
               break;
            case 'Y':
               $requestData['status_checked']   = 'checked';
               break;
            default:
               $requestData['status_checked']   = '';
               break;
         }
         $requestData['bas_tipe']   = $messengerData['bas_tipe'];
         if($messengerData['nilai_default']){
            switch (strtoupper($messengerData['nilai_default'])) {
               case 'D':
                  $requestData['debet_checked']    = 'checked';
                  $requestData['kredit_checked']   = '';
                  break;
               case 'K':
                  $requestData['debet_checked']    = '';
                  $requestData['kredit_checked']   = 'checked';
                  break;
               default:
                  $requestData['debet_checked']    = 'checked';
                  $requestData['kredit_checked']   = '';
                  break;
            }
         }
      }

      # Combobox
      Messenger::Instance()->SendToComponent(
         'combobox',
         'Combobox',
         'view',
         'html',
         'tipe',
         array(
            'bas_tipe',
            $basType,
            $requestData['bas_tipe'],
            'false',
            'id="bas_tipe"'
         ),
         Messenger::CurrentRequest
      );

      $return['query_string']    = $querystring;
      $return['request_data']    = $requestData;
      $return['message']         = $message;
      $return['style']           = $style;
      return $return;
   }

   function ParseTemplate($data = null){
      $message       = $data['message'];
      $style         = $data['style'];
      $requestData   = $data['request_data'];
      $queryString   = $data['query_string'];
      $queryReturn   = preg_replace('/(data_id=[\d]+)/', '', $queryString);
      $queryReturn   = preg_replace('/(search=[\d]+)/', '', $queryReturn);
      $queryReturn   = preg_replace('/\&[\&]/', '&', $queryReturn);
      $queryReturn   = '&search=1&'.$queryReturn;

      $urlReturn     = Dispatcher::Instance()->GetUrl(
         'finansi_mata_anggaran',
         'MataAnggaran',
         'view',
         'html'
      ) . $queryReturn;
      $urlAction     = Dispatcher::Instance()->GetUrl(
         'finansi_mata_anggaran',
         'UpdateMataAnggaran',
         'do',
         'json'
      ).'&'.$queryString;

      $urlPopupBas   = Dispatcher::Instance()->GetUrl(
         'finansi_mata_anggaran',
         'PopupBas',
         'view',
         'html'
      );

      $urlPopupCoa   = Dispatcher::Instance()->GetUrl(
         'finansi_mata_anggaran',
         'PopupCoa',
         'view',
         'html'
      );

      $this->mrTemplate->AddVar('content', 'URL_ACTION', $urlAction);
      $this->mrTemplate->AddVar('content', 'URL_RETURN', $urlReturn);
      $this->mrTemplate->AddVar('content', 'URL_POPUP_BAS', $urlPopupBas);
      $this->mrTemplate->AddVar('content', 'URL_POPUP_COA', $urlPopupCoa);
      $this->mrTemplate->AddVars('content', $requestData);

      if($message){
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $message);
         $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $style);
      }
   }
}
?>