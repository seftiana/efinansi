<?php
#doc
# package:     ViewDaftarTransaksi
# scope:       PUBLIC
# @created     ucil.619
# @Author      Eko Susilo
# @Created     2012-09-04
# @Modified    2012-09-04
# @Analysts    Nanang Ruswianto
# @copyright   Copyright (c) 2012 Gamatechno
#/doc
require_once GTFWConfiguration::GetValue('application','docroot').
'module/transaksi_sp2d/business/Sppd.class.php';

class ViewDaftarTransaksi extends HtmlResponse
{
   function TemplateModule(){
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
      'module/transaksi_sp2d/template/');
      $this->SetTemplateFile('view_daftar_transaksi.html');
   }
   
   function ProcessRequest(){
      $objSppd    = new Sppd();
      
      $_POST      = $_POST->AsArray();
      if(isset($_POST['btnSearch'])){
         $periode_awal_day    = $_POST['periode_awal_day'];
         $periode_awal_mon    = $_POST['periode_awal_mon'];
         $periode_awal_year   = $_POST['periode_awal_year'];
         $periode_akhir_day   = $_POST['periode_akhir_day'];
         $periode_akhir_mon   = $_POST['periode_akhir_mon'];
         $periode_akhir_year  = $_POST['periode_akhir_year'];
         $periode_awal        = date(
            'Y-m-d', 
            strtotime($periode_awal_year.'-'.$periode_awal_mon.'-'.$periode_awal_day)
         );
         $periode_akhir       = date(
            'Y-m-d', 
            strtotime($periode_akhir_year.'-'.$periode_akhir_mon.'-'.$periode_akhir_day)
         );
         $kode_referensi      = trim($_POST['no_referensi']);
      }elseif(isset($_GET['search'])){
         $periode_awal     = date('Y-m-d', strtotime($_GET['p_start']));
         $periode_akhir    = date('Y-m-d', strtotime($_GET['p_end']));
         $kode_referensi   = trim($_GET['k']);
      }else{
         $periode_awal     = date('Y-m-d', mktime(0,0,0, 1, 1, date('Y', time())));
         $periode_akhir    = date('Y-m-d', time());
         $kode_referensi   = '';
      }
      
      $offset        = 0;
      $limit         = 20;
      $page          = 0;
      
      if(isset($_GET['page'])){
         $page       = (string) $_GET['page']->StripHtmlTags()->SqlString()->Raw();
         $offset     = ($page - 1) * $limit;
      }
      
      $dataSppd      = $objSppd->GetDataTransaksi(
         $kode_referensi, 
         $periode_awal, 
         $periode_akhir, 
         $offset, 
         $limit
      );
      $total_data    = $objSppd->CountData();
      #paging url
      $url    = Dispatcher::Instance()->GetUrl(
                Dispatcher::Instance()->mModule,Dispatcher::Instance()->mSubModule,
                Dispatcher::Instance()->mAction,
                Dispatcher::Instance()->mType).
                '&p_start='.$periode_awal.
                '&p_end='.$periode_akhir.
                '&k='.$kode_referensi.
                '&search='.Dispatcher::Instance()->Encrypt(1);
      
      $destination_id = "subcontent-element";
      
      #send data to pagging component
      Messenger::Instance()->SendToComponent(
         'paging', 
         'Paging', 
         'view', 
         'html', 
         'paging_top', 
         array(
            $limit,
            $total_data, 
            $url, 
            $page, 
            $destination_id
         ),
         Messenger::CurrentRequest
      );
      
      
      # GTFW Tanggal
      $tahun_awal       = date('Y',time())-5;
      $tahun_akhir      = date('Y', time())+5;
      Messenger::Instance()->SendToComponent(
         'tanggal', 
         'Tanggal', 
         'view', 
         'html', 
         'periode_awal', 
         array(
            $periode_awal, 
            $tahun_awal, 
            $tahun_akhir, 
            '', 
            '', 
            ''
         ), 
         Messenger::CurrentRequest
      );
      
      # GTFW Tanggal
      Messenger::Instance()->SendToComponent(
         'tanggal', 
         'Tanggal', 
         'view', 
         'html', 
         'periode_akhir', 
         array(
            $periode_akhir, 
            $tahun_awal, 
            $tahun_akhir, 
            '', 
            '', 
            ''
         ), 
         Messenger::CurrentRequest
      );
      
      $return['data_list']    = $dataSppd;
      $return['no_ref']       = $kode_referensi;
      $return['start']        = $offset+1;
      return $return;
   }
   
   function ParseTemplate($data = null){
      $url_search    = Dispatcher::Instance()->GetUrl(
         'transaksi_sp2d',
         'DaftarTransaksi',
         'view',
         'html'
      );
      $url_add       = Dispatcher::Instance()->GetUrl(
         'transaksi_sp2d',
         'AddSppd',
         'view',
         'html'
      );
      
      $dataList      = $data['data_list'];
      
      $this->mrTemplate->AddVar('content', 'URL_SEARCH', $url_search);
      $this->mrTemplate->AddVar('content', 'SEARCH_NO_REFERENSI', $data['no_ref']);
      
      if (empty($dataList))
      {
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
      }
      else
      {
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');
         for ($i = 0; $i < count($dataList); $i++)
         {
            $dataList[$i]['nomor']        = $data['start']+$i;
            if($i % 2 == 0):
               $dataList[$i]['class_name']      = 'table-common-even';
            else:
               $dataList[$i]['class_name']      = '';
            endif;
            $transNilai[$i]                     = $dataList[$i]['transNilai'];
            $dataList[$i]['transNilai']         = number_format($dataList[$i]['transNilai'], 0, ',','.');
            $dataList[$i]['url_cetak']          = $url_add.'&trans_id='.$dataList[$i]['transId'].'&real='.$dataList[$i]['pengrealId'];
            $this->mrTemplate->AddVars('data_list', $dataList[$i], 'DATA_');
            $this->mrTemplate->parseTemplate('data_list', 'a');
         }
         $totalNilai          = number_format(array_sum($transNilai), 0, ',','.');
         $this->mrTemplate->AddVar('data_grid', 'TOTAL_NILAI', $totalNilai);
         
      }
      
   }
}
?>
