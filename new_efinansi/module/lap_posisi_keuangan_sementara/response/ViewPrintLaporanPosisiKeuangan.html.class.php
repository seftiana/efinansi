<?php
/**
* ================= doc ====================
* FILENAME     : ViewPrintLaporanPosisiKeuangan.html.class.php
* @package     : ViewPrintLaporanPosisiKeuangan
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-02-27
* @Modified    : 2015-02-27
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/lap_posisi_keuangan_sementara/business/LaporanPosisiKeuangan.class.php';

class ViewPrintLaporanPosisiKeuangan extends HtmlResponse
{
   function TemplateModule(){
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
      'module/lap_posisi_keuangan_sementara/template/');
      $this->SetTemplateFile('view_print_laporan_posisi_keuangan.html');
   }

   function TemplateBase() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
      $this->SetTemplateFile('document-print.html');
      $this->SetTemplateFile('layout-common-print.html');
   }

   function ProcessRequest(){
      $mObj       = new LaporanPosisiKeuangan();
      $requestData['start_date']    = date('Y-m-d', strtotime(Dispatcher::Instance()->Decrypt($mObj->_GET['start_date'])));
      $requestData['end_date']      = date('Y-m-d', strtotime(Dispatcher::Instance()->Decrypt($mObj->_GET['end_date'])));

      $dataList                  = $mObj->getDataLaporan($requestData);
      $return['data_list']       = $dataList;
      return $return;
   }

   function ParseTemplate($data = null){
      $gridList         = $data['data_list'];

      $bsId       = '';
      $dataList   = array();
      $index      = '';
      $index      = 0;
      $templates  = array(
         15 => 'aktiva_lancar',
         16 => 'aktiva_tidak_lancar',
         17 => 'kewajiban_jangka_pendek',
         18 => 'kewajiban_jangka_panjang',
         19 => 'aktiva_bersih',
         23 => 'aktiva_lain'
      );

      // untuk menyimpan nominal balance sheet
      $balance_sheet     = array();
      for ($i=0; $i < count($gridList);) {
         if($gridList[$i]['kellap_jns_id'] == $bsId){
            $balance_sheet[$bsId]['current']    += $gridList[$i]['nominal'];
            $balance_sheet[$bsId]['debet']      += $gridList[$i]['nominal_debet'];
            $balance_sheet[$bsId]['kredit']     += $gridList[$i]['nominal_kredit'];

            $dataList[$bsId]['data'][$index]['id']          = $gridList[$i]['kellap_id'];
            $dataList[$bsId]['data'][$index]['nama']        = $gridList[$i]['kellap_nama'];
            $dataList[$bsId]['data'][$index]['nominal']     = $gridList[$i]['nominal'];
            $dataList[$bsId]['data'][$index]['debet']       = $gridList[$i]['nominal_debet'];
            $dataList[$bsId]['data'][$index]['kredit']      = $gridList[$i]['nominal_kredit'];
            $i++;
            $index+=1;
         }else{
            unset($index);
            $index      = 0;
            $bsId       = $gridList[$i]['kellap_jns_id'];
            unset($balance_sheet[$bsId]['current']);
            unset($balance_sheet[$bsId]['debet']);
            unset($balance_sheet[$bsId]['kredit']);
            $balance_sheet[$bsId]['current']    = 0;
            $balance_sheet[$bsId]['debet']      = 0;
            $balance_sheet[$bsId]['kredit']     = 0;

            $dataList[$bsId]['template']     = $templates[$bsId];
            $dataList[$bsId]['label']        = $gridList[$i]['kellap_jns_nama'];
            $dataList[$bsId]['id']           = $bsId;
         }
      }

      foreach ($templates as $temp) {
         $this->mrTemplate->clearTemplate($temp, TRUE);
      }

      foreach ($dataList as $list) {
         if(!empty($list['data'])){
            $this->mrTemplate->AddVar($list['template'], 'DATA_EMPTY', 'NO');
            $this->mrTemplate->AddVar($list['template'], 'LABEL', $list['label']);
            if($balance_sheet[$list['id']]['current'] < 0){
               $currentBalance      = '('. number_format(abs($balance_sheet[$list['id']]['current']), 2, ',','.').')';
            }else{
               $currentBalance      = number_format($balance_sheet[$list['id']]['current'], 2, ',','.');
            }

            foreach ($list['data'] as $data) {
               if($list['template'] === NULL){
                  continue;
               }
               if($data['nominal'] < 0){
                  $data['nominal']     = '('. number_format(abs($data['nominal']), 2, ',','.') .')';
               }else{
                  $data['nominal']     = number_format($data['nominal'], 2, ',','.');
               }

               $data['url_detail_current']   = $urlDetail.'&dataId='.Dispatcher::Instance()->Encrypt($data['id']).'&show=current';
               $data['url_detail_prev']      = $urlDetail.'&dataId='.Dispatcher::Instance()->Encrypt($data['id']).'&show=previous';
               $this->mrTemplate->AddVars('data_item_'.$list['template'], $data);
               $this->mrTemplate->parseTemplate('data_item_'.$list['template'], 'a');
            }

            // penulisan total nominal dari setiap kelompok laporan
            $this->mrTemplate->AddVar($list['template'], 'SUM_CURRENT_BALANCE', $currentBalance);
         }else{
            $this->mrTemplate->AddVar($list['template'], 'DATA_EMPTY', 'YES');
         }
      }

      $currentAktifa       = $balance_sheet[15]['current']+$balance_sheet[16]['current']+$balance_sheet[23]['current'];
      $currentLiabilities  = $balance_sheet[17]['current']+$balance_sheet[18]['current']+$balance_sheet[19]['current'];

      if($currentAktifa < 0){
         $currentAktifaLabel  = '('.number_format(abs($currentAktifa), 2, ',','.').')';
      }else{
         $currentAktifaLabel  = number_format($currentAktifa, 2, ',','.');
      }

      if($currentLiabilities < 0){
         $currentLiabilitiesLabel   = '('.number_format(abs($currentLiabilities), 2, ',','.').')';
      }else{
         $currentLiabilitiesLabel   = number_format($currentLiabilities, 2, ',','.');
      }

      $this->mrTemplate->AddVar('content', 'TOTAL_CURRENT_AKTIVA', $currentAktifaLabel);

      $this->mrTemplate->AddVar('content', 'TOTAL_CURRENT_LIABILITIES', $currentLiabilitiesLabel);
   }
}
?>