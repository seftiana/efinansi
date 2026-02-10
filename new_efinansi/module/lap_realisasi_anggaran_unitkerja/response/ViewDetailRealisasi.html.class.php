<?php
/**
* ================= doc ====================
* FILENAME     : ViewDetailRealisasi.html.class.php
* @package     : ViewDetailRealisasi
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2014-07-22
* @Modified    : 2014-07-22
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/'.Dispatcher::Instance()->mModule.'/business/RekapUnitKerja.class.php';

class ViewDetailRealisasi extends HtmlResponse
{
   function TemplateModule(){
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
      'module/'.Dispatcher::Instance()->mModule.'/template/');
      $this->SetTemplateFile('view_detail_realisasi.html');
   }

   function TemplateBase() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
      $this->SetTemplateFile('document-common-popup.html');
      $this->SetTemplateFile('layout-common-popup.html');
   }

   function ProcessRequest(){
      $mObj       = new RekapUnitKerja();
      $dataId     = $_GET['id']->Integer()->Raw();
      $dataIdP     = $_GET['idp']->Integer()->Raw();
      $dataDetail = $mObj->GetDataDetail($dataId);
      $dataList   = $mObj->GetDataPengajuanRealisasi($dataIdP);

      $return['data_detail']     = $mObj->ChangeKeyName($dataDetail);
      $return['data_list']       = $mObj->ChangeKeyName($dataList);
      return $return;
   }

   function ParseTemplate($data = null){
      $dataDetail       = $data['data_detail'];
      $dataList         = $data['data_list'];
      $mObj             = new RekapUnitKerja();
      $this->mrTemplate->SetAttribute('tipe_unit', 'visibility', 'hidden');
      $this->mrTemplate->SetAttribute('tipe_fakultas', 'visibility', 'hidden');
      $this->mrTemplate->SetAttribute('tipe_jurusan', 'visibility', 'hidden');
      $this->mrTemplate->SetAttribute('tipe_prodi', 'visibility', 'hidden');
      $this->mrTemplate->AddVars('content', $dataDetail);
      switch (strtoupper($dataDetail['unit_type'])) {
         case 'UNIT':
            $this->mrTemplate->SetAttribute('tipe_unit', 'visibility', 'visible');
            $this->mrTemplate->AddVar('tipe_unit', 'UNIT_NAMA', $dataDetail['unit_nama']);
            break;
         case 'FAKULTAS':
            $this->mrTemplate->SetAttribute('tipe_unit', 'visibility', 'visible');
            $this->mrTemplate->AddVar('tipe_unit', 'UNIT_NAMA', $dataDetail['unit_nama']);
            $this->mrTemplate->SetAttribute('tipe_fakultas', 'visibility', 'visible');
            $this->mrTemplate->AddVar('tipe_fakultas', 'FAKULTAS_NAMA', $dataDetail['fakultas_nama']);
            break;
         case 'JURUSAN':
            $this->mrTemplate->SetAttribute('tipe_unit', 'visibility', 'visible');
            $this->mrTemplate->AddVar('tipe_unit', 'UNIT_NAMA', $dataDetail['unit_nama']);
            $this->mrTemplate->SetAttribute('tipe_fakultas', 'visibility', 'visible');
            $this->mrTemplate->AddVar('tipe_fakultas', 'FAKULTAS_NAMA', $dataDetail['fakultas_nama']);
            $this->mrTemplate->SetAttribute('tipe_jurusan', 'visibility', 'visible');
            $this->mrTemplate->AddVar('tipe_jurusan', 'JURUSAN_NAMA', $dataDetail['jurusan_nama']);
            break;
         case 'PRODI':
            $this->mrTemplate->SetAttribute('tipe_unit', 'visibility', 'visible');
            $this->mrTemplate->AddVar('tipe_unit', 'UNIT_NAMA', $dataDetail['unit_nama']);
            $this->mrTemplate->SetAttribute('tipe_fakultas', 'visibility', 'visible');
            $this->mrTemplate->AddVar('tipe_fakultas', 'FAKULTAS_NAMA', $dataDetail['fakultas_nama']);
            $this->mrTemplate->SetAttribute('tipe_jurusan', 'visibility', 'visible');
            $this->mrTemplate->AddVar('tipe_jurusan', 'JURUSAN_NAMA', $dataDetail['jurusan_nama']);
            $this->mrTemplate->SetAttribute('tipe_prodi', 'visibility', 'visible');
            $this->mrTemplate->AddVar('tipe_prodi', 'PRODI_NAMA', $dataDetail['prodi_nama']);
            break;
         default:
            # code...
            break;
      }

      if(empty($dataList)){
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
      }else{
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');

         $pengrealId       = '';
         $dataGrid         = array();
         $dataRealisasi    = array();
         $index            = 0;
         $idx              = 0;
         for ($i=0; $i < count($dataList);) {
            if($pengrealId == $dataList[$i]['id']){
               $dataRealisasi[$pengrealId][$idx]['nominal']    = number_format($dataList[$i]['trans_nilai'], 0, ',','.');
               $dataRealisasi[$pengrealId][$idx]['nomor']      = $dataList[$i]['referensi'];
               if(empty($dataList[$i]['trans_tanggal'])) {
                     $dataRealisasi[$pengrealId][$idx]['tanggal']    = '-';
               } else {
                     $dataRealisasi[$pengrealId][$idx]['tanggal']    = $mObj->indonesianDate(date('Y-m-d', strtotime($dataList[$i]['trans_tanggal'])));
               }      
               $idx++;
               $i++;
            }else{
               $pengrealId       = $dataList[$i]['id'];
               $idx        = 0;
               $dataGrid[$index]['id']    = $dataList[$i]['id'];
               $dataGrid[$index]['nomor_pengajuan']   = $dataList[$i]['nomor_pengajuan'];
               if(empty($dataList[$i]['tanggal'])) {
                    $dataGrid[$index]['tanggal']           = '-';
               } else {
                    $dataGrid[$index]['tanggal']           = $mObj->indonesianDate($dataList[$i]['tanggal']);
               }
               $dataGrid[$index]['nominal']           = number_format($dataList[$i]['nominal_approve'], 0, ',','.');
               $dataGrid[$index]['nominal_lppa']      = number_format($dataList[$i]['nominal_lppa'], 0, ',','.');
               $dataGrid[$index]['keterangan']        = $dataList[$i]['keterangan'];
               $dataGrid[$index]['no_lppa']           = $dataList[$i]['no_lppa'];
               $index++;
            }
         }

         foreach ($dataGrid as $list) {
            $this->mrTemplate->clearTemplate('realisasi_item');
            $this->mrTemplate->AddRows('realisasi_item', $dataRealisasi[$list['id']]);
            $this->mrTemplate->AddVars('data_list', $list);
            $this->mrTemplate->parseTemplate('data_list', 'a');
         }
      }
   }
}
?>