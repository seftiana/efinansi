<?php
#doc
# package:     ViewEditKomponen
# scope:       PUBLIC
# @created     ucil.619
# @Author      Eko Susilo
# @Created     2013-09-23
# @Modified    2014-11-11
# @Analysts
# @copyright   Copyright (c) 2012 Gamatechno
#/doc
require_once GTFWConfiguration::GetValue('application','docroot').
'module/'.Dispatcher::Instance()->mModule.'/business/FinansiReferensi.class.php';

class ViewEditKomponen extends HtmlResponse
{
   function TemplateModule(){
     $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
     'module/'.Dispatcher::Instance()->mModule.'/template/');
     $this->SetTemplateFile('view_edit_komponen.html');
   }

   function ProcessRequest(){
      $mObj          = new FinansiReferensi();
      $dataId        = Dispatcher::Instance()->Decrypt($mObj->_GET['data_id']);
      $unitKerjaRef  = $mObj->ChangeKeyName($mObj->GetUnitKerjaRef());
      $messenger     = Messenger::Instance()->Receive(__FILE__);
      $dataString    = array();
      $unitKerja     = array();
      $dataList      = $mObj->ChangeKeyName($mObj->GetKegiatanRefById($dataId));
      $queryString   = $mObj->__getQueryString();
      $dataUnitKerja = array();

      $dataString['id']                = $dataList['data']['id'];
      $dataString['ta_id']             = $dataList['data']['thanggar_id'];
      $dataString['tahun_anggaran']    = $dataList['data']['thanggar_nama'];
      $dataString['kode']              = $dataList['data']['kode'];
      $dataString['nama']              = $dataList['data']['nama'];
      $dataString['kegiatan_id']       = $dataList['data']['kegiatan_id'];
      $dataString['kegiatan_kode']     = $dataList['data']['kegiatan_kode'];
      $dataString['kegiatan']          = $dataList['data']['kegiatan_nama'];
      $dataString['output_id']         = $dataList['data']['output_id'];
      $dataString['output_kode']       = $dataList['data']['output_kode'];
      $dataString['output']            = $dataList['data']['output_nama'];
      $dataString['rkakl_subkegiatan_id']    = $dataList['data']['rkakl_sub_kegiatan_id'];
      $dataString['rkakl_subkegiatan_kode']  = $dataList['data']['rkakl_sub_kegiatan_kode'];
      $dataString['rkakl_subkegiatan_nama']  = $dataList['data']['rkakl_sub_kegiatan_nama'];

      if(!empty($dataList['unit'])){
         $i       = 0;
         foreach ($dataList['unit'] as $unit) {
            $dataUnitKerja[$i]['id']    = $unit['id'];
            $dataUnitKerja[$i]['kode']  = $unit['kode'];
            $dataUnitKerja[$i]['nama']  = $unit['nama'];
            $i++;
         }
      }

      if($messenger){
         $msgData       = $messenger[0][0];
         $msgMessage    = $messenger[0][1];
         $msgStyle      = $messenger[0][2];
         $dataString['id']             = $msgData['data_id'];
         $dataString['kegiatan_id']    = $msgData['kegiatan_id'];
         $dataString['kegiatan_kode']  = $msgData['kegiatan_kode'];
         $dataString['kegiatan']       = $msgData['kegiatan'];
         $dataString['output_id']      = $msgData['output_id'];
         $dataString['output_kode']    = $msgData['output_kode'];
         $dataString['output']         = $msgData['output'];
         $dataString['kode']           = $msgData['kode'];
         $dataString['nama']           = $msgData['nama'];
         $dataString['rkakl_subkegiatan_id']    = $msgData['rkakl_subkegiatan_id'];
         $dataString['rkakl_subkegiatan_kode']  = $msgData['rkakl_subkegiatan_kode'];
         $dataString['rkakl_subkegiatan_nama']  = $msgData['rkakl_subkegiatan_nama'];
         unset($dataUnitKerja);
         if(!empty($msgData['unit'])){
            $index      = 0;
            foreach ($msgData['unit'] as $unit) {
               $dataUnitKerja[$index]  = $unit;
               $index++;
            }
         }
      }

      $return['unit']               = $unitKerja;
      $return['data_string']        = $dataString;
      $return['message']            = $msgMessage;
      $return['style']              = $msgStyle;
      $return['query_string']       = $queryString;
      $return['unit_kerja']['data'] = json_encode($dataUnitKerja);
      return $return;
   }

   function ParseTemplate($data = null){
      $queryString      = $data['query_string'];
      $unit             = (array)$data['unit'];
      $dataUnit         = $data['unit_kerja'];
      $dataString       = $data['data_string'];
      $message          = $data['message'];
      $style            = $data['style'];
      $urlReturn        = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         'ProgramKegiatan',
         'view',
         'html'
      ).'&search='.Dispatcher::Instance()->Encrypt(1).'&'.$queryString;
      $urlAction        = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         'UpdateKomponen',
         'do',
         'json'
      ).'&'.$queryString;
      $urlUnitKerja     = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         'GetUnitKerja',
         'do',
         'json'
      );
      $urlPopupOutput   = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         'PopupOutputKegiatan',
         'view',
         'html'
      ).'&'.$queryString;

      $this->mrTemplate->AddVar('content', 'URL_ACTION', $urlAction);
      $this->mrTemplate->AddVar('content', 'URL_RETURN', $urlReturn);
      $this->mrTemplate->AddVar('content', 'URL_GET_UNIT_KERJA', $urlUnitKerja);
      $this->mrTemplate->AddVars('content', $dataString, '');
      $this->mrTemplate->AddVars('content', $dataUnit, 'UNIT_');
      $this->mrTemplate->AddVar('content', 'URL_POPUP_OUTPUT', $urlPopupOutput);

      if($message){
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $message);
         $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $style);
      }
   }
}
?>