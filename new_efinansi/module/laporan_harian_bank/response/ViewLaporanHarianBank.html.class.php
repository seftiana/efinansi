<?php
/**
* ================= doc ====================
* FILENAME     : ViewLaporanHarianBank.html.class.php
* @package     : ViewLaporanHarianBank
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-05-26
* @Modified    : 2015-05-26
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/laporan_harian_bank/business/LaporanHarianBank.class.php';

class ViewLaporanHarianBank extends HtmlResponse
{
   function TemplateModule(){
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
      'module/laporan_harian_bank/template/');
      $this->SetTemplateFile('view_laporan_harian_bank.html');
   }

   function ProcessRequest(){
      $get_date      = getdate();
      $curr_mon      = (int)$get_date['mon'];
      $curr_day      = (int)$get_date['mday'];
      $curr_year     = (int)$get_date['year'];
      $mObj          = new LaporanHarianBank();
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
         $request_data['nama_bank']    = $mObj->_POST['nama_bank'];
         $request_data['nomor_bukti']  = $mObj->_POST['nomor_bukti'];
      }elseif(isset($mObj->_GET['search'])){
         $request_data['start_date']   = date('Y-m-d', strtotime(Dispatcher::Instance()->Decrypt($mObj->_GET['start_date'])));
         $request_data['end_date']     = date('Y-m-d', strtotime(Dispatcher::Instance()->Decrypt($mObj->_GET['end_date'])));
         $request_data['nama_bank']    = Dispatcher::Instance()->Decrypt($mObj->_GET['nama_bank']);
         $request_data['nomor_bukti']  = Dispatcher::Instance()->Decrypt($mObj->_GET['nomor_bukti']);
      }else{
         $request_data['start_date']   = date('Y-m-d', mktime(0,0,0, $curr_mon, $curr_day-7, $curr_year));
         $request_data['end_date']     = date('Y-m-d', mktime(0,0,0, $curr_mon, $curr_day, $curr_year));
         $request_data['nama_bank']    = '';
         $request_data['nomor_bukti']  = '';
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
      $data_list           = $mObj->getData($offset, $limit, $request_data);
      $total_data          = $mObj->Count();

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

      $return     = compact('query_string', 'request_data', 'data_list');
      return $return;
   }

   function ParseTemplate($data = null){
      extract($data);
      $urlSearch     = Dispatcher::Instance()->GetUrl(
         'laporan_harian_bank',
         'LaporanHarianBank',
         'view',
         'html'
      );

      $url_export    = Dispatcher::Instance()->GetUrl(
         'laporan_harian_bank',
         'LaporanHarianBank',
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
         $mutasi_debet     = 0;
         $mutasi_kredit    = 0;
         $saldo_akhir      = 0;
         foreach ($data_list as $list) {
            $mutasi_debet+=$list['nominal_debet'];
            $mutasi_kredit+=$list['nominal_kredit'];
            $saldo_akhir+=($list['nominal_debet']-$list['nominal_kredit']);
            if($tanggal == $list['tanggal']){
               $list['tanggal']  = '';
            }else{
               $tanggal       = $list['tanggal'];
            }

            if($list['sppu_id'] == ''){
               $list['uraian']      = $list['uraian_penerimaan'] == '' ? $list['uraian_pengeluaran'] : $list['uraian_penerimaan'];
            }else{
               $list['uraian']      = $list['uraian_fpa'];
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

            // cek status jurnal
            if($list['jurnal_penerimaan'] != '' 
               && $list['sppu_id'] == '' 
               && $list['bank_tipe'] == 'penerimaan'){

               $this->mrTemplate->AddVar('status_jurnal', 'STATUS', strtoupper($list['jurnal_penerimaan']));

            }elseif($list['jurnal_pengeluaran'] != ''
               && $list['sppu_bp'] == 'Y' 
               && $list['sppu_cr'] == 'T'){

               $this->mrTemplate->AddVar('status_jurnal', 'STATUS', strtoupper($list['jurnal_pengeluaran']));

            }elseif($list['jurnal_pengeluaran'] != '' 
               && $list['sppu_id'] == '' 
               && $list['sppu_bp'] == '' 
               && $list['sppu_cr'] == ''){

               $this->mrTemplate->AddVar('status_jurnal', 'STATUS', strtoupper($list['jurnal_pengeluaran']));

            }elseif($list['jurnal_penerimaan'] == '' 
               && $list['jurnal_pengeluaran'] == '' 
               && $list['sppu_bp'] == 'Y'
               && $list['sppu_cr'] == 'Y'){

               $this->mrTemplate->AddVar('status_jurnal', 'STATUS', 'CR');

            }else{
               $this->mrTemplate->AddVar('status_jurnal', 'STATUS', 'T');
            }
            // end - cek status jurnal

            $this->mrTemplate->AddVars('data_list', $list);
            $this->mrTemplate->parseTemplate('data_list', 'a');
         }

         if($mutasi_debet < 0){
            $mutasi_debet  = '('.number_format(abs($mutasi_debet), 2, ',','.').')';
         }else{
            $mutasi_debet  = number_format($mutasi_debet, 2, ',','.');
         }

         if($mutasi_kredit < 0){
            $mutasi_kredit    = '('.number_format(abs($mutasi_kredit), 2, ',','.').')';
         }else{
            $mutasi_kredit    = number_format($mutasi_kredit, 2, ',','.');
         }

         if($saldo_akhir < 0){
            $saldo_akhir      = '('.number_format(abs($saldo_akhir), 2, ',','.').')';
         }else{
            $saldo_akhir      = number_format($saldo_akhir, 2, ',','.');
         }

         $this->mrTemplate->AddVar('data_grid', 'MUTASI_DEBET', $mutasi_debet);
         $this->mrTemplate->AddVar('data_grid', 'MUTASI_KREDIT', $mutasi_kredit);
         $this->mrTemplate->AddVar('data_grid', 'SALDO_AKHIR', $saldo_akhir);
      }
   }
}
?>