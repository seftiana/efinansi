<?php

require_once GTFWConfiguration::GetValue('application','docroot').
'module/laporan_pembayaran_mahasiswa/business/LaporanPembayaranMahasiswa.class.php';

class ViewLaporanPembayaranMahasiswa extends HtmlResponse
{
   function TemplateModule(){
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
      'module/laporan_pembayaran_mahasiswa/template/');
      $this->SetTemplateFile('view_laporan_pembayaran_mahasiswa.html');
   }

   function ProcessRequest(){
      $get_date      = getdate();
      $curr_day      = (int)$get_date['mday'];
      $curr_mon      = (int)$get_date['mon'];
      $curr_year     = (int)$get_date['year'];
      $mObj          = new LaporanPembayaranMahasiswa();
      $tahun_awal    = $curr_year-($curr_year-2015);
      $tahun_akhir   = $curr_year;
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
         $request_data['start_date']   = date('Y-m-d', mktime(0,0,0, $curr_mon, $curr_day, $curr_year));
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
      $data_list           = $mObj->getDataLaporan($offset, $limit, $request_data);
      $total_data			= $mObj->Count($request_data);
      $total_nominal       = $mObj->getDataLaporanSum($request_data);

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

      $return     = compact('query_string', 'request_data', 'data_list', 'start', 'total_nominal');
      return $return;
   }

   function ParseTemplate($data = null){
      extract($data);
      $urlSearch     = Dispatcher::Instance()->GetUrl(
         'laporan_pembayaran_mahasiswa',
         'LaporanPembayaranMahasiswa',
         'view',
         'html'
      );

      $url_export    = Dispatcher::Instance()->GetUrl(
         'laporan_pembayaran_mahasiswa',
         'LaporanPembayaranMahasiswa',
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
		 $total_real_bayar = 0;
         foreach ($data_list as $list) {

            $list['nomor']    		= $start;
            $list['nominal']   		= number_format($list['nominal'], 0, ',','.');
            $list['potongan']   	= number_format($list['potongan'], 0, ',','.');
            $list['deposit']   		= number_format($list['deposit'], 0, ',','.');
            $list['asli_bayar']   	= number_format($list['real_bayar'], 0, ',','.');
			$total_real_bayar 		+= $list['real_bayar'];
			
            $start++;

            $this->mrTemplate->AddVars('data_list', $list);
            $this->mrTemplate->parseTemplate('data_list', 'a');
         }

		 $total_per_page = number_format($total_real_bayar, 0, ',', '.');
		 $total_all_page = number_format($total_nominal, 0, ',', '.');
		 $this->mrTemplate->AddVar('content', 'TOTAL_REAL_BAYAR_PER_PAGE', $total_per_page);
		 $this->mrTemplate->AddVar('content', 'TOTAL_REAL_BAYAR_ALL', $total_all_page);
		 
		 

      }
   }
}
?>