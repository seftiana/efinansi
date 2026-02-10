<?php
/**
* ================= doc ====================
* FILENAME     : ViewDetailLaporanPosisiKeuangan.html.class.php
* @package     : ViewDetailLaporanPosisiKeuangan
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-02-26
* @Modified    : 2015-02-26
* @Analysts    : Dyah Fajar
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/lap_posisi_keuangan_sementara/business/LaporanPosisiKeuangan.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'main/function/number_format.class.php';

class ViewDetailLaporanPosisiKeuangan extends HtmlResponse
{
   function TemplateModule(){
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
      'module/lap_posisi_keuangan_sementara/template/');
      $this->SetTemplateFile('view_detail_laporan_posisi_keuangan.html');
   }

   function TemplateBase() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
      $this->SetTemplateFile('document-common-popup.html');
      $this->SetTemplateFile('layout-common-popup.html');
   }

   function ProcessRequest(){
      $mObj          = new LaporanPosisiKeuangan();
      $requestData   = array();
      $requestData['start_date']    = date('Y-m-d', strtotime(Dispatcher::Instance()->Decrypt($mObj->_GET['start_date'])));
      $requestData['end_date']      = date('Y-m-d', strtotime(Dispatcher::Instance()->Decrypt($mObj->_GET['end_date'])));
      $requestData['id']            = Dispatcher::Instance()->Decrypt($mObj->_GET['dataId']);

      $dataDetail    = $mObj->getDataDetail($requestData);

      $return['data_detail']     = $dataDetail;
      return $return;
   }

   function ParseTemplate($data = null){
      $dataList      = $data['data_detail']; 
      if(empty($dataList)){
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
      }else{
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');
         $saldoAwal     = 0;
         $totalD =0;
         $totalK =0;
         $totalDK = 0;
         $jumlahDK = 0;
         $no = 1;
         foreach ($dataList as $list) {            
            $list['number'] = $no; 
            switch ($list['coa_kelompok_id']) {
               case '2': /*pasiva*/
               case '3':/*modal*/
               case '4':/*pendapatan*/
                   $saldo = $list['nominal_kredit'] - $list['nominal_debet'];
                   break;
               case '1': /*aktiva*/
               case '5': /*beban*/
               default :
                   $saldo = $list['nominal_debet'] - $list['nominal_kredit'];
                   break;
           }
            $jumlahDK = $saldo;
            $totalDK += $jumlahDK;
            // $list['debet']    = NumberFormat::Accounting($list['nominal_debet'], 2);
            // $list['kredit']   = NumberFormat::Accounting($list['nominal_kredit'], 2);  
            $list['debet']    = $list['nominal_debet'];
            $list['kredit']   = $list['nominal_kredit']; 
            $list['jumlah_dk'] = NumberFormat::Accounting($jumlahDK,2);
            $this->mrTemplate->AddVars('data_list', $list);
            $this->mrTemplate->parseTemplate('data_list', 'a');
            $saldoAwal  += $saldo;
            $no++;
         }
          $this->mrTemplate->AddVar('data_grid', 'JUMLAH_TOTAL', NumberFormat::Accounting($totalDK,2));
      }
   }
}
?>