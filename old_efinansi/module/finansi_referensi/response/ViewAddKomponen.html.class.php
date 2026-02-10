<?php
#doc
# package:     ViewAddKomponen
# scope:       PUBLIC
# @created     ucil.619
# @Author      Eko Susilo
# @Created     2013-09-20
# @Modified    2014-11-11
# @Analysts    system-analysts
# @copyright   Copyright (c) 2012 Gamatechno
#/doc
require_once GTFWConfiguration::GetValue('application','docroot').
'module/'.Dispatcher::Instance()->mModule.'/business/FinansiReferensi.class.php';

class ViewAddKomponen extends HtmlResponse
{
   function TemplateModule(){
     $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
     'module/'.Dispatcher::Instance()->mModule.'/template/');
     $this->SetTemplateFile('view_add_komponen.html');
   }

   function ProcessRequest(){
      $mObj                         = new FinansiReferensi();
      $messenger                    = Messenger::Instance()->Receive(__FILE__);
      $tahunAnggaran                = $mObj->GetTahunAnggaran(array('active' => true));
      $dataString                   = array();
      $queryString                  = $mObj->__getQueryString();
      $dataString['ta_id']          = $tahunAnggaran[0]['id'];
      $dataString['tahun_anggaran'] = $tahunAnggaran[0]['name'];
      $dataUnitKerja                = array();
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

         if(!empty($msgData['unit'])){
            $index      = 0;
            foreach ($msgData['unit'] as $unit) {
               $dataUnitKerja[$index]  = $unit;
               $index++;
            }
         }
      }

      $return['message']            = $msgMessage;
      $return['style']              = $msgStyle;
      $return['data_string']        = $dataString;
      $return['query_string']       = $queryString;
      $return['unit_kerja']['data'] = json_encode($dataUnitKerja);
      return $return;
   }

   function ParseTemplate($data = null){
      $dataUnit         = $data['unit_kerja'];
      $dataString       = $data['data_string'];
      $message          = $data['message'];
      $style            = $data['style'];
      $queryString      = $data['query_string'];
      $urLReturn        = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         'ProgramKegiatan',
         'view',
         'html'
      ).'&search='.Dispatcher::Instance()->Encrypt(1).'&'.$queryString;
      $urlAction        = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         'AddKomponen',
         'do',
         'json'
      ).'&'.$queryString;
      $urlPopupOutput   = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         'PopupOutputKegiatan',
         'view',
         'html'
      );
      $urlUnitKerja     = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         'GetUnitKerja',
         'do',
         'json'
      );

      $this->mrTemplate->AddVar('content', 'URL_RETURN', $urLReturn);
      $this->mrTemplate->AddVar('content', 'URL_ACTION', $urlAction);
      $this->mrTemplate->AddVar('content', 'URL_POPUP_OUTPUT', $urlPopupOutput);
      $this->mrTemplate->AddVar('content', 'URL_GET_UNIT_KERJA', $urlUnitKerja);
      $this->mrTemplate->AddVars('content', $dataString, '');
      $this->mrTemplate->AddVars('content', $dataUnit, 'UNIT_');

      if($message){
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $message);
         $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $style);
      }
   }
}
?>