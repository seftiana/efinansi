<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/lap_monitoring_anggaran/business/AppLapMonitoringAnggaran.class.php';
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/user_unit_kerja/business/UserUnitKerja.class.php';
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/tanggal/business/Tanggal.class.php';

class ViewLapMonitoringAnggaran extends HtmlResponse
{
   function TemplateModule()
   {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot') .
         'module/lap_monitoring_anggaran/template');
      $this->SetTemplateFile('view_lap_monitoring_anggaran.html');
   }

   function ProcessRequest()
   {
      $userId        = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
      $mObj          = new AppLapMonitoringAnggaran();
      $mUnitObj      = new UserUnitKerja();
      $mTglObj       = new Tanggal();
      $arrUnitKerja  = $mUnitObj->GetUnitKerjaRefUser($userId);
      $arrPeriodeTahun  = $mObj->GetPeriodeTahun();
      $periodeTahun     = $mObj->GetPeriodeTahun(array(
         'active' => true
      ));
      $arrJenisKegiatan = $mObj->GetComboJenisKegiatan();
      $arrBulan         = $mTglObj->GetArrayMonth();
      $bulanKeys        = array_keys($arrBulan);
      $bulanValues      = array_values($arrBulan);
      for ($i = 0; $i < count($bulanKeys); $i++) {
         $arrMonth[$i]['id'] = $bulanKeys[$i];
         $arrMonth[$i]['name'] = $bulanValues[$i];
      }
      $requestData      = array();
      $queryString      = '';
      if(isset($mObj->_POST['btncari'])){
         $requestData['ta_id']            = $mObj->_POST['tahun_anggaran'];
         $requestData['program_id']       = $mObj->_POST['program'];
         $requestData['program_nama']     = $mObj->_POST['program_label'];
         $requestData['unit_id']          = $mObj->_POST['unitkerja'];
         $requestData['unit_nama']        = $mObj->_POST['unitkerja_label'];
         $requestData['jenis_kegiatan']   = $mObj->_POST['jenis_kegiatan'];
         $requestData['bulan']            = $mObj->_POST['bulan'];
      }elseif(isset($mObj->_GET['search'])){
         $requestData['ta_id']            = Dispatcher::Instance()->Decrypt($mObj->_GET['ta_id']);
         $requestData['program_id']       = Dispatcher::Instance()->Decrypt($mObj->_GET['program_id']);
         $requestData['program_nama']     = Dispatcher::Instance()->Decrypt($mObj->_GET['program_nama']);
         $requestData['unit_id']          = Dispatcher::Instance()->Decrypt($mObj->_GET['unit_id']);
         $requestData['unit_nama']        = Dispatcher::Instance()->Decrypt($mObj->_GET['unit_nama']);
         $requestData['jenis_kegiatan']   = Dispatcher::Instance()->Decrypt($mObj->_GET['jenis_kegiatan']);
         $requestData['bulan']            = Dispatcher::Instance()->Decrypt($mObj->_GET['bulan']);
      }else{
         $requestData['ta_id']            = $periodeTahun[0]['id'];
         $requestData['program_id']       = '';
         $requestData['program_nama']     = '';
         $requestData['unit_id']          = $arrUnitKerja['id'];
         $requestData['unit_nama']        = $arrUnitKerja['nama'];
         $requestData['jenis_kegiatan']   = '';
         $requestData['bulan']            = '';
      }

      foreach ($arrPeriodeTahun as $ta) {
         if((int)$ta['id'] === (int)$requestData['ta_id']){
            $requestData['ta_nama']    = $ta['name'];
         }
      }

      if(method_exists(Dispatcher::Instance(), 'getQueryString')){
         $queryString      = Dispatcher::Instance()->getQueryString($requestData);
      }else{
         $query            = array();
         foreach ($requestData as $key => $value) {
            $query[$key]   = Dispatcher::Instance()->Encrypt($value);
         }
         $queryString      = urldecode(http_build_query($query));
      }

      $offset         = 0;
      $limit          = 20;
      $page           = 0;
      if(isset($_GET['page'])){
         $page    = (string) $_GET['page']->StripHtmlTags()->SqlString()->Raw();
         $offset  = ($page - 1) * $limit;
      }
      #paging url
      $url    = Dispatcher::Instance()->GetUrl(
        Dispatcher::Instance()->mModule,
        Dispatcher::Instance()->mSubModule,
        Dispatcher::Instance()->mAction,
        Dispatcher::Instance()->mType
      ).'&search='.Dispatcher::Instance()->Encrypt(1).'&'.$queryString;

      $destination_id   = "subcontent-element";
      $dataList         = $mObj->GetData($offset, $limit, (array)$requestData);
      $total_data       = $mObj->GetCountData();

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


      Messenger::Instance()->SendToComponent(
         'combobox',
         'Combobox',
         'view',
         'html',
         'tahun_anggaran',
         array(
            'tahun_anggaran',
            $arrPeriodeTahun,
            $requestData['ta_id'],
            false,
            'style="width:200px;" id="cmb_tahun_anggaran"'
         ), Messenger::CurrentRequest);

      Messenger::Instance()->SendToComponent(
         'combobox',
         'Combobox',
         'view',
         'html',
         'jenis_kegiatan',
         array(
            'jenis_kegiatan',
            $arrJenisKegiatan,
            $requestData['jenis_kegiatan'],
            true,
            ' style="width:200px;" id="cmb_jenis_kegiatan"'
         ), Messenger::CurrentRequest);

      Messenger::Instance()->SendToComponent(
         'combobox',
         'Combobox',
         'view',
         'html',
         'bulan',
         array(
            'bulan',
            $arrMonth,
            $requestData['bulan'],
            true,
            'id="cmb_month"'
         ), Messenger::CurrentRequest);

      $return['data_unit']       = $mObj->ChangeKeyName($arrUnitKerja);
      $return['request_data']    = $requestData;
      $return['query_string']    = $queryString;
      $return['data_list']       = $mObj->ChangeKeyName($dataList);
      $return['start']           = $offset+1;
      return $return;
   }

   function ParseTemplate($data = NULL)
   {
      $page          = 1;
      if(isset($_GET['page'])) {
         $page       = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
      }
      $dataUnit      = $data['data_unit'];
      $requestData   = $data['request_data'];
      $queryString   = $data['query_string'].'&page='.$page;
      $start         = $data['start'];
      $dataList      = $data['data_list'];
      $urlSearch     = Dispatcher::Instance()->GetUrl(
         'lap_monitoring_anggaran',
         'lapMonitoringAnggaran',
         'view',
         'html'
      );
      $urlPopupProgram     = Dispatcher::Instance()->GetUrl(
         'lap_monitoring_anggaran',
         'popupProgram',
         'view',
         'html'
      );
      $urlPopupUnit        = Dispatcher::Instance()->GetUrl(
         'lap_monitoring_anggaran',
         'popupUnitkerja',
         'view',
         'html'
      );
      $urlCetak            = Dispatcher::Instance()->GetUrl(
         'lap_monitoring_anggaran',
         'CetakLapMonitoringAnggaran',
         'view',
         'html'
      ).'&'.$queryString;
      $urlExportExcel      = Dispatcher::Instance()->GetUrl(
         'lap_monitoring_anggaran',
         'ExcelLapMonitoringAnggaran',
         'view',
         'xlsx'
      ).'&'.$queryString;

      $this->mrTemplate->AddVar('content', 'URL_SEARCH', $urlSearch);
      $this->mrTemplate->AddVars('content', $requestData);
      $this->mrTemplate->AddVar('content', 'URL_POPUP_PROGRAM', $urlPopupProgram);
      $this->mrTemplate->AddVar('unit_kerja', 'STATUS', strtoupper($dataUnit['status']));
      $this->mrTemplate->AddVar('unit_kerja', 'UNIT_ID', $requestData['unit_id']);
      $this->mrTemplate->AddVar('unit_kerja', 'UNIT_NAMA', $requestData['unit_nama']);
      $this->mrTemplate->AddVar('unit_kerja', 'URL_POPUP_UNIT', $urlPopupUnit);
      $this->mrTemplate->AddVar('content', 'URL_CETAK', $urlCetak);
      $this->mrTemplate->AddVar('content', 'URL_EXCEL', $urlExportExcel);

      if (empty($dataList)) {
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
      } else {
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');
         // inisialisasi data
         $program          = '';
         $kegiatan         = '';
         $dataGrid         = array();
         $dataMonitoring   = array();
         $index            = 0;

         for ($i=0; $i < count($dataList);) {
            if((int)$dataList[$i]['program_id'] === (int)$program && (int)$dataList[$i]['kegiatan_id'] === (int)$kegiatan){
               $programKodeSistem      = $program;
               $kegiatanKodeSistem     = $program.'.'.$kegiatan;

               // ========================= PROGRAM =========================== //

               $dataMonitoring[$programKodeSistem]['nominal_usulan']     += $dataList[$i]['nominal_usulan'];
               $dataMonitoring[$programKodeSistem]['nominal_setuju']     += $dataList[$i]['nominal_setuju'];
               $dataMonitoring[$programKodeSistem]['nominal_revisi']  += $dataList[$i]['nominal_revisi'];
               $dataMonitoring[$programKodeSistem]['nominal_setelah_revisi']  += $dataList[$i]['nominal_setelah_revisi'];

               if(isset($dataList[$i]['status_approve']) || strtoupper($dataList[$i]['status_approve'] = 'YA') || strtoupper($dataList[$i]['status_approve'] = 'TIDAK')){ 
                  $dataMonitoring[$programKodeSistem]['nominal_pencairan']  += $dataList[$i]['nominal_pencairan_belum'] + $dataList[$i]['nominal_pencairan_ya'] + $dataList[$i]['nominal_pencairan_tidak'];
               }else{ 
                  $dataMonitoring[$programKodeSistem]['nominal_pencairan']  += $dataList[$i]['nominal_pencairan_belum'] + $dataList[$i]['nominal_pencairan_ya'] + $dataList[$i]['nominal_pencairan_tidak'];
               }

               $dataMonitoring[$programKodeSistem]['nominal_realisasi']     += $dataList[$i]['nominal_realisasi'];

               if(isset($dataList[$i]['status_approve']) || strtoupper($dataList[$i]['status_approve'] = 'YA') || strtoupper($dataList[$i]['status_approve'] = 'TIDAK')){
                  $dataMonitoring[$programKodeSistem]['sisa_dana']          += $dataList[$i]['nominal_setelah_revisi'] - $dataList[$i]['nominal_pencairan_ya'] - $dataList[$i]['nominal_pencairan_belum'] - $dataList[$i]['nominal_pencairan_tidak'];
               }else{
                  $dataMonitoring[$programKodeSistem]['sisa_dana']          += 0;
               }

               // ========================= KEGIATAN =========================== //

               $dataMonitoring[$kegiatanKodeSistem]['nominal_usulan']     += $dataList[$i]['nominal_usulan'];
               $dataMonitoring[$kegiatanKodeSistem]['nominal_setuju']     += $dataList[$i]['nominal_setuju'];
               $dataMonitoring[$kegiatanKodeSistem]['nominal_revisi']  += $dataList[$i]['nominal_revisi'];
               $dataMonitoring[$kegiatanKodeSistem]['nominal_setelah_revisi']  += $dataList[$i]['nominal_setelah_revisi'];

               if(isset($dataList[$i]['status_approve']) || strtoupper($dataList[$i]['status_approve'] = 'YA') || strtoupper($dataList[$i]['status_approve'] = 'TIDAK')){
                  $dataMonitoring[$kegiatanKodeSistem]['nominal_pencairan']  += $dataList[$i]['nominal_pencairan_belum'] + $dataList[$i]['nominal_pencairan_ya'] + $dataList[$i]['nominal_pencairan_tidak'];
               }else{
                  $dataMonitoring[$kegiatanKodeSistem]['nominal_pencairan']  += $dataList[$i]['nominal_pencairan_belum'] + $dataList[$i]['nominal_pencairan_ya'] + $dataList[$i]['nominal_pencairan_tidak'];
               }

               $dataMonitoring[$kegiatanKodeSistem]['nominal_realisasi']     += $dataList[$i]['nominal_realisasi'];

               if(isset($dataList[$i]['status_approve']) || strtoupper($dataList[$i]['status_approve'] = 'YA') || strtoupper($dataList[$i]['status_approve'] = 'TIDAK')){
                  $dataMonitoring[$kegiatanKodeSistem]['sisa_dana']          += $dataList[$i]['nominal_setelah_revisi'] - $dataList[$i]['nominal_pencairan_ya'] - $dataList[$i]['nominal_pencairan_belum'] - $dataList[$i]['nominal_pencairan_tidak'];
               }else{
                  $dataMonitoring[$kegiatanKodeSistem]['sisa_dana']          += $dataList[$i]['nominal_setelah_revisi'] - $dataList[$i]['nominal_pencairan_ya'] - $dataList[$i]['nominal_pencairan_belum'] - $dataList[$i]['nominal_pencairan_tidak'];;
               }

               // ========================= DATA =========================== //

               $dataGrid[$index]['kode']        = $dataList[$i]['sub_kegiatan_kode'];
               $dataGrid[$index]['nama']        = $dataList[$i]['sub_kegiatan_nama'];
               $dataGrid[$index]['unit_kode']   = $dataList[$i]['unit_kode'];
               $dataGrid[$index]['unit_nama']   = $dataList[$i]['unit_nama'];
               $dataGrid[$index]['status']      = strtoupper($dataList[$i]['status']);
               $dataGrid[$index]['nominal_usulan']    = $dataList[$i]['nominal_usulan'];
               $dataGrid[$index]['nominal_setuju']    = $dataList[$i]['nominal_setuju'];
               $dataGrid[$index]['nominal_revisi']    = $dataList[$i]['nominal_revisi'];
               $dataGrid[$index]['nominal_setelah_revisi']    = $dataList[$i]['nominal_setelah_revisi'];

               if(isset($dataList[$i]['status_approve']) && strtoupper($dataList[$i]['status_approve'] = 'YA')){ // Belum Membuat FPA / Memiliki FPA yang BELUM dan SUDAH disetujui
                  $dataGrid[$index]['nominal_pencairan']  += $dataList[$i]['nominal_pencairan_belum'] + $dataList[$i]['nominal_pencairan_ya'];
               }elseif(isset($dataList[$i]['status_approve'])){ // BELUM Disetujui
                  $dataGrid[$index]['nominal_pencairan']  += $dataList[$i]['nominal_pencairan_belum'];
               }elseif(strtoupper($dataList[$i]['status_approve'] = 'YA')){ // SUDAH Disetujui
                  $dataGrid[$index]['nominal_pencairan']  += $dataList[$i]['nominal_pencairan_ya'];
               }else{ // TIDAK Disetujui
                  $dataGrid[$index]['nominal_pencairan']  += $dataList[$i]['nominal_pencairan_tidak'];
               }

               $dataGrid[$index]['nominal_realisasi'] = $dataList[$i]['nominal_realisasi'];

               if(isset($dataList[$i]['status_approve']) && strtoupper($dataList[$i]['status_approve'] = 'YA')){ // Belum Membuat FPA / Memiliki FPA yang BELUM dan SUDAH disetujui
                  $dataGrid[$index]['total_fpa']          += $dataList[$i]['nominal_pencairan_ya'] + $dataList[$i]['nominal_pencairan_belum']; 
                  $dataGrid[$index]['sisa_dana']          += $dataList[$i]['nominal_setelah_revisi'] - $dataGrid[$index]['total_fpa'];
               }elseif(isset($dataList[$i]['status_approve'])){ // BELUM Disetujui
                  $dataGrid[$index]['sisa_dana']          += $dataList[$i]['nominal_setelah_revisi'] - $dataList[$i]['nominal_pencairan_belum'];
               }elseif(strtoupper($dataList[$i]['status_approve'] = 'YA')){ // SUDAH Disetujui
                  $dataGrid[$index]['sisa_dana']          += $dataList[$i]['nominal_setelah_revisi'] - $dataList[$i]['nominal_pencairan_ya'];
               }else{ // TIDAK Disetujui
                  $dataGrid[$index]['sisa_dana']          += $dataList[$i]['nominal_setelah_revisi'] - $dataList[$i]['nominal_pencairan_tidak'];
               }

               $dataGrid[$index]['deskripsi']   = $dataList[$i]['deskripsi'];
               $dataGrid[$index]['type']        = 'sub_kegiatan';
               $dataGrid[$index]['class_name']  = ($start % 2 <> 0) ? 'table-common-even' : '';
               $dataGrid[$index]['row_style']   = '';
               $i++;
               $start++;
            }elseif ((int)$dataList[$i]['program_id'] === (int)$program && (int)$dataList[$i]['kegiatan_id'] !== (int)$kegiatan) {
               $kegiatan      = (int)$dataList[$i]['kegiatan_id'];
               $kodeSistem    = $program.'.'.$kegiatan;
               $dataMonitoring[$kodeSistem]['nominal_usulan']     = 0;
               $dataMonitoring[$kodeSistem]['nominal_setuju']     = 0;
               $dataMonitoring[$kodeSistem]['nominal_revisi']  = 0;
               $dataMonitoring[$kodeSistem]['nominal_setelah_revisi']  = 0;
               $dataMonitoring[$kodeSistem]['nominal_pencairan']  = 0;
               $dataMonitoring[$kodeSistem]['nominal_realisasi']  = 0;
               $dataMonitoring[$kodeSistem]['sisa_dana']          = 0;

               $dataGrid[$index]['kode']        = $dataList[$i]['kegiatan_kode'];
               $dataGrid[$index]['nama']        = $dataList[$i]['kegiatan_nama'];
               $dataGrid[$index]['kode_sistem'] = $kodeSistem;
               $dataGrid[$index]['type']        = 'kegiatan';
               $dataGrid[$index]['class_name']  = 'table-common-even2';
               $dataGrid[$index]['row_style']   = '';
            }else{
               $program       = (int)$dataList[$i]['program_id'];
               $kodeSistem    = $program;
               $dataMonitoring[$kodeSistem]['nominal_usulan']     = 0;
               $dataMonitoring[$kodeSistem]['nominal_setuju']     = 0;
               $dataMonitoring[$kodeSistem]['nominal_revisi']  = 0;
               $dataMonitoring[$kodeSistem]['nominal_setelah_revisi']  = 0;
               $dataMonitoring[$kodeSistem]['nominal_pencairan']  = 0;
               $dataMonitoring[$kodeSistem]['nominal_realisasi']  = 0;
               $dataMonitoring[$kodeSistem]['sisa_dana']          = 0;

               $dataGrid[$index]['kode']        = $dataList[$i]['program_kode'];
               $dataGrid[$index]['nama']        = $dataList[$i]['program_nama'];
               $dataGrid[$index]['kode_sistem'] = $kodeSistem;
               $dataGrid[$index]['type']        = 'program';
               $dataGrid[$index]['class_name']  = 'table-common-even1';
               $dataGrid[$index]['row_style']   = 'font-weight: bold;';
            }
            $index++;
         }


         foreach ($dataGrid as $list) {
            switch (strtoupper($list['type'])) {
               case 'PROGRAM':
                  $list['nominal_usulan']    = number_format($dataMonitoring[$list['kode_sistem']]['nominal_usulan'], 0, ',','.');
                  $list['nominal_setuju']    = number_format($dataMonitoring[$list['kode_sistem']]['nominal_setuju'], 0, ',','.');
                  $list['nominal_revisi'] = number_format($dataMonitoring[$list['kode_sistem']]['nominal_revisi'], 0, ',','.');
                  $list['nominal_setelah_revisi'] = number_format($dataMonitoring[$list['kode_sistem']]['nominal_setelah_revisi'], 0, ',','.');
                  $list['nominal_pencairan'] = number_format($dataMonitoring[$list['kode_sistem']]['nominal_pencairan'], 0, ',','.');
                  $list['nominal_realisasi'] = number_format($dataMonitoring[$list['kode_sistem']]['nominal_realisasi'], 0, ',','.');
                  $list['sisa_dana']         = number_format($dataMonitoring[$list['kode_sistem']]['sisa_dana'], 0, ',','.');
                  break;
               case 'KEGIATAN':
                  $list['nominal_usulan']    = number_format($dataMonitoring[$list['kode_sistem']]['nominal_usulan'], 0, ',','.');
                  $list['nominal_setuju']    = number_format($dataMonitoring[$list['kode_sistem']]['nominal_setuju'], 0, ',','.');
                  $list['nominal_revisi'] = number_format($dataMonitoring[$list['kode_sistem']]['nominal_revisi'], 0, ',','.');
                  $list['nominal_setelah_revisi'] = number_format($dataMonitoring[$list['kode_sistem']]['nominal_setelah_revisi'], 0, ',','.');
                  $list['nominal_pencairan'] = number_format($dataMonitoring[$list['kode_sistem']]['nominal_pencairan'], 0, ',','.');
                  $list['nominal_realisasi'] = number_format($dataMonitoring[$list['kode_sistem']]['nominal_realisasi'], 0, ',','.');
                  $list['sisa_dana']         = number_format($dataMonitoring[$list['kode_sistem']]['sisa_dana'], 0, ',','.');
                  break;
               default:
                  $list['nominal_usulan']    = number_format($list['nominal_usulan'], 0, ',','.');
                  $list['nominal_setuju']    = number_format($list['nominal_setuju'], 0, ',','.');
                  $list['nominal_revisi'] = number_format($list['nominal_revisi'], 0, ',','.');
                  $list['nominal_setelah_revisi'] = number_format($list['nominal_setelah_revisi'], 0, ',','.');
                  $list['nominal_pencairan'] = number_format($list['nominal_pencairan'], 0, ',','.');
                  $list['nominal_realisasi'] = number_format($list['nominal_realisasi'], 0, ',','.');
                  $list['sisa_dana']         = number_format($list['sisa_dana'], 0, ',','.');
                  break;
            }
            $this->mrTemplate->AddVars('data_list', $list);
            $this->mrTemplate->parseTemplate('data_list', 'a');
         }
      }
   }
}
?>