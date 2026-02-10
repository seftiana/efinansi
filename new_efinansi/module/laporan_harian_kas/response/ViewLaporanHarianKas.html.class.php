<?php
/**
* ================= doc ====================
* FILENAME     : ViewLaporanHarianKas.html.class.php
* @package     : ViewLaporanHarianKas
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-05-26
* @Modified    : 2015-05-26
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/laporan_harian_kas/business/LaporanHarianKas.class.php';

class ViewLaporanHarianKas extends HtmlResponse
{
   function TemplateModule(){
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
      'module/laporan_harian_kas/template/');
      $this->SetTemplateFile('view_laporan_harian_kas.html');
   }

   function ProcessRequest(){
      $get_date      = getdate();
      $curr_mon      = (int)$get_date['mon'];
      $curr_day      = (int)$get_date['mday'];
      $curr_year     = (int)$get_date['year'];
      $mObj          = new LaporanHarianKas();
      $tahun_awal    = $curr_year-5;
      $tahun_akhir   = $curr_year+5;
      $query_string  = '';

      if(isset($mObj->_POST['btnSearch'])){
         $startDate_day       = (int)$mObj->_POST['start_date_day'];
         $startDate_mon       = (int)$mObj->_POST['start_date_mon'];
         $startDate_year      = (int)$mObj->_POST['start_date_year'];
         $endDate_day         = (int)$mObj->_POST['end_date_day'];
         $endDate_mon         = (int)$mObj->_POST['end_date_mon'];
         $endDate_year        = (int)$mObj->_POST['end_date_year'];
         $request_data['start_date']   = date('Y-m-d', mktime(0,0,0, $startDate_mon, $startDate_day, $startDate_year));
         $request_data['end_date']     = date('Y-m-d', mktime(0,0,0, $endDate_mon, $endDate_day, $endDate_year));
      }elseif(isset($mObj->_GET['search'])){
         $request_data['start_date']   = date('Y-m-d', strtotime(Dispatcher::Instance()->Decrypt($mObj->_GET['start_date'])));
         $request_data['end_date']     = date('Y-m-d', strtotime(Dispatcher::Instance()->Decrypt($mObj->_GET['end_date'])));
      }else{
         $request_data['start_date']   = date('Y-m-d', mktime(0,0,0, $curr_mon, $curr_day-7, $curr_year));
         $request_data['end_date']     = date('Y-m-d', mktime(0,0,0, $curr_mon, $curr_day, $curr_year));
      }

      if(method_exists(Dispatcher::Instance(), 'getQueryString')){
         # @param array
         $query_string  = Dispatcher::instance()->getQueryString($request_data);
      }else{
         $query         = array();
         foreach ($request_data as $key => $value) {
            $query[$key]   = Dispatcher::Instance()->Encrypt($value);
         }
         $query_string     = urldecode(http_build_query($query));
      }

      $offset     = 0;
      $limit      = 20;
      $page       = 0;
      if(isset($_GET['page'])){
         $page    = (string) $_GET['page']->StripHtmlTags()->SqlString()->Raw();
         $offset  = ($page - 1) * $limit;
      }

      #paging url
      $url        = Dispatcher::Instance()->GetUrl(
        Dispatcher::Instance()->mModule,
        Dispatcher::Instance()->mSubModule,
        Dispatcher::Instance()->mAction,
        Dispatcher::Instance()->mType
      ).'&search='.Dispatcher::Instance()->Encrypt(1).'&'.$query_string;

      $destination_id      = "subcontent-element";
      $data_list           = $mObj->getDataLaporanKas($offset, $limit, $request_data);
      $total_data          = $mObj->Count();
      $saldoAwal           = $mObj->getSaldoAwal($request_data['start_date']);
      $totalDK             = $mObj->getTotalDebetKredit($request_data);
      
      $start      = $offset+1;

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
      Messenger::Instance()->SendToComponent(
         'tanggal',
         'Tanggal',
         'view',
         'html',
         'start_date',
         array(
            $request_data['start_date'],
            $tahun_awal,
            $tahun_akhir,
            false,
            false,
            false
         ),
         Messenger::CurrentRequest
      );

      Messenger::Instance()->SendToComponent(
         'tanggal',
         'Tanggal',
         'view',
         'html',
         'end_date',
         array(
            $request_data['end_date'],
            $tahun_awal,
            $tahun_akhir,
            false,
            false,
            false
         ),
         Messenger::CurrentRequest
      );

      $return     = compact('query_string', 'request_data', 'data_list', 'start', 'saldoAwal','totalDK');
      return $return;
   }

   function ParseTemplate($data = null){
      extract($data);
      $urlSearch     = Dispatcher::Instance()->GetUrl(
         'laporan_harian_kas',
         'LaporanHarianKas',
         'view',
         'html'
      );

      $url_export    = Dispatcher::Instance()->GetUrl(
         'laporan_harian_kas',
         'LaporanHarianKas',
         'view',
         'xlsx'
      ).'&'.$query_string;

      $this->mrTemplate->AddVar('content', 'URL_SEARCH', $urlSearch);
      $this->mrTemplate->AddVar('content', 'URL_EXPORT', $url_export);
      $this->mrTemplate->AddVars('content', $request_data);

      if(empty($data_list)){
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
      }else{
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');
         $saldo_awal       = 0;
         $mutasi_debet     = 0;
         $mutasi_kredit    = 0;
         $mutasi_jumlah    = 0;
         $saldo_akhir      = 0;
         foreach ($data_list as $list) {
            #$mutasi_debet+=$list['nominal_debet'];
            #$mutasi_kredit+=$list['nominal_kredit'];
            #$mutasi_jumlah+=$list['nominal_debet']-$list['nominal_kredit'];
            #$saldo_akhir+=($list['nominal_debet']-$list['nominal_kredit']);

            $list['nomor']    = $start;
            $list['tanggal']  = $list['trans_tanggal'];

            if($tanggal == $list['tanggal']){
               $list['tanggal']  = '';
            }else{
               $tanggal       = $list['tanggal'];
            }

            if($list['tt_id'] == '1' && $list['trans_jenis'] == '9'){ // Penerimaan Kas
               $list['trans_catatan']  = $list['uraian_fpa'];
            }else{ // Pengeluaran Kas
               $list['trans_catatan']  = $list['trans_catatan'];
            }

            if($list['nominal_debet'] < 0){
               $list['debet']    = '('.number_format(abs($list['nominal_debet']), 2, ',','.').')';
            }else{
               $list['debet']    = number_format($list['nominal_debet'], 2, ',','.');
            }

            if($list['nominal_kredit'] < 0){
               $list['kredit']   = '('.number_format(abs($list['nominal_kredit']), 2, ',','.').')';
            }else{
               $list['kredit']   = number_format($list['nominal_kredit'], 2, ',','.');
            }

            $start++;

            $this->mrTemplate->AddVar('status_jurnal', 'STATUS', strtoupper($list['is_jurnal']));
            $this->mrTemplate->AddVars('data_list', $list);
            $this->mrTemplate->parseTemplate('data_list', 'a');
         }

        // foreach($saldoAwal as $saldo){
       // print_r($saldoAwal);
            if($saldoAwal < 0){
               $saldo_awal  = '('.number_format(abs($saldoAwal), 2, ',','.').')';
               $saldo_total  = '('.number_format(abs( $saldoAwal +$saldo_akhir), 2, ',','.').')';
            }else{
               $saldo_awal  = number_format($saldoAwal, 2, ',','.');
               $saldo_total = number_format( $saldoAwal+$saldo_akhir, 2, ',','.');
            }
         //}

         if($totalDK['debet'] < 0){
            $mutasi_debet  = '('.number_format(abs($totalDK['debet']), 2, ',','.').')';
         }else{
            $mutasi_debet  = number_format($totalDK['debet'], 2, ',','.');
         }

         if($totalDK['kredit'] < 0){
            $mutasi_kredit    = '('.number_format(abs($totalDK['kredit']), 2, ',','.').')';
         }else{
            $mutasi_kredit    = number_format($totalDK['kredit'], 2, ',','.');
         }

         $mutasiJumlah = ($totalDK['debet'] -  $totalDK['kredit']);
         if($mutasiJumlah < 0){
            $mutasi_jumlah    = '('.number_format(abs($mutasiJumlah), 2, ',','.').')';
         }else{
            $mutasi_jumlah    = number_format($mutasiJumlah, 2, ',','.');
         }

         $saldo_akhir = $saldoAwal + $mutasiJumlah;
         if($saldo_akhir < 0){
            $saldo_akhir      = '('.number_format(abs($saldo_akhir), 2, ',','.').')';
         }else{
            $saldo_akhir      = number_format($saldo_akhir, 2, ',','.');
         }

         $this->mrTemplate->AddVar('data_grid', 'SALDO_AWAL', $saldo_awal);
         $this->mrTemplate->AddVar('data_grid', 'MUTASI_DEBET', $mutasi_debet);
         $this->mrTemplate->AddVar('data_grid', 'MUTASI_KREDIT', $mutasi_kredit);
         $this->mrTemplate->AddVar('data_grid', 'MUTASI_JUMLAH', $mutasi_jumlah);
         $this->mrTemplate->AddVar('data_grid', 'SALDO_AKHIR', $saldo_akhir);
      }
   }
}
?>