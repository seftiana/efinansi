<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/'.Dispatcher::Instance()->mModule.'/business/AppLapRekapProgram.class.php';
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/user_unit_kerja/business/UserUnitKerja.class.php';
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/tanggal/business/Tanggal.class.php';

class ViewLapRekapProgram extends HtmlResponse {

   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
         'module/'.Dispatcher::Instance()->mModule.'/template');
      $this->SetTemplateFile('view_lap_rekap_program.html');
   }

   function ProcessRequest() {
      $userId           = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
      $mObj             = new AppLapRekapProgram();
      $mUnitObj         = new UserUnitKerja();
      $mDate            = new Tanggal();
      $dataUnit         = $mUnitObj->GetUnitKerjaRefUser($userId);
      $arrPeriodeTahun  = $mObj->GetPeriodeTahun();
      $periodeTahun     = $mObj->GetPeriodeTahun(array(
         'active' => true
      ));
      $arrJenisKegiatan = $mObj->GetComboJenisKegiatan();
      $arrBulan         = $mDate->GetArrayMonth();
      $bulanKeys        = array_keys($arrBulan);
      $bulanValues      = array_values($arrBulan);
      for ($i = 0; $i < count($bulanKeys); $i++) {
         $arrMonth[$i]['id']     = $bulanKeys[$i];
         $arrMonth[$i]['name']   = $bulanValues[$i];
      }
      $requestData      = array();

      if(isset($mObj->_POST['btncari'])){
         $requestData['ta_id']            = $mObj->_POST['tahun_anggaran'];
         $requestData['unit_id']          = $mObj->_POST['unitkerja'];
         $requestData['unit_nama']        = $mObj->_POST['unitkerja_label'];
         $requestData['program_id']       = $mObj->_POST['program'];
         $requestData['program_nama']     = $mObj->_POST['program_label'];
         $requestData['jenis_kegiatan']   = $mObj->_POST['jenis_kegiatan'];
         $requestData['bulan']            = $mObj->_POST['bulan'];
      }elseif(isset($mObj->_GET['search'])){
         $requestData['ta_id']            = Dispatcher::Instance()->Decrypt($mObj->_GET['ta_id']);
         $requestData['unit_id']          = Dispatcher::Instance()->Decrypt($mObj->_GET['unit_id']);
         $requestData['unit_nama']        = Dispatcher::Instance()->Decrypt($mObj->_GET['unit_nama']);
         $requestData['program_id']       = Dispatcher::Instance()->Decrypt($mObj->_GET['program_id']);
         $requestData['program_nama']     = Dispatcher::Instance()->Decrypt($mObj->_GET['program_nama']);
         $requestData['jenis_kegiatan']   = Dispatcher::Instance()->Decrypt($mObj->_GET['jenis_kegiatan']);
         $requestData['bulan']            = Dispatcher::Instance()->Decrypt($mObj->_GET['bulan']);
      }else{
         $requestData['ta_id']            = $periodeTahun[0]['id'];
         $requestData['unit_id']          = $dataUnit['id'];
         $requestData['unit_nama']        = $dataUnit['nama'];
         $requestData['program_id']       = '';
         $requestData['program_nama']     = '';
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
      ).'&search='.Dispatcher::Instance()->Encrypt(1).'&'.$queryString;

      $destination_id   = "subcontent-element";
      $dataList         = $mObj->GetData($offset, $limit, (array)$requestData);
      $total_data       = $mObj->GetCountData();
      $dataResume       = $mObj->GetDataResume((array)$requestData);
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
            'style="width:200px;" id="cmb_jenis_kegiatan"'),
         Messenger::CurrentRequest);

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
            true
         ), Messenger::CurrentRequest);

      $return['request_data']    = $requestData;
      $return['query_string']    = $queryString;
      $return['data_unit']       = $mObj->ChangeKeyName($dataUnit);
      $return['data_list']       = $mObj->ChangeKeyName($dataList);
      $return['start']           = $offset+1;
      $return['data_resume']     = $mObj->ChangeKeyName($dataResume);
      return $return;
   }

   function ParseTemplate($data = NULL) {
      $page          = 1;
      if(isset($_GET['page'])) {
         $page       = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
      }
      $dataUnit      = $data['data_unit'];
      $requestData   = $data['request_data'];
      $queryString   = $data['query_string'].'&page='.$page;
      $dataList      = $data['data_list'];
      $start         = $data['start'];
      $dataResume    = $data['data_resume'];
      $urlSearch     = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         Dispatcher::Instance()->mSubModule,
         'view',
         'html'
      );
      $urlCetak      = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         'CetakLapRekapProgram',
         'view',
         'html'
      ).'&'.$queryString;
      $urlExcel      =  Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         'ExcelLapRekapProgram',
         'view',
         'xlsx'
      ).'&'.$queryString;
      $urlPopupProgram  = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         'popupProgram',
         'view',
         'html'
      );
      $urlPopupUnit     = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         'popupUnitkerja',
         'view',
         'html'
      );

      $this->mrTemplate->AddVar('content', 'URL_SEARCH', $urlSearch);
      $this->mrTemplate->AddVars('content', $requestData);
      $this->mrTemplate->AddVar('data_unit', 'STATUS', strtoupper($dataUnit['status']));
      $this->mrTemplate->AddVar('data_unit', 'UNIT_ID', $requestData['unit_id']);
      $this->mrTemplate->AddVar('data_unit', 'UNIT_NAMA', $requestData['unit_nama']);
      $this->mrTemplate->AddVar('data_unit', 'URL_POPUP_UNITKERJA', $urlPopupUnit);
      $this->mrTemplate->AddVar('content', 'URL_CETAK', $urlCetak);
      $this->mrTemplate->AddVar('content', 'URL_EXCEL', $urlExcel);
      $this->mrTemplate->AddVar('content', 'URL_POPUP_PROGRAM', $urlPopupProgram);

      if(empty($dataList)){
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
         $this->mrTemplate->AddVar('data_resume', 'DATA_EMPTY', 'YES');
      }else{
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');
         $this->mrTemplate->AddVar('data_resume', 'DATA_EMPTY', 'NO');

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

               $dataMonitoring[$programKodeSistem]['nominal_usulan']     += $dataList[$i]['nominal_usulan'];
               $dataMonitoring[$programKodeSistem]['nominal_setuju']     += $dataList[$i]['nominal_setuju'];
               $dataMonitoring[$programKodeSistem]['nominal_pencairan']  += $dataList[$i]['nominal_pencairan'];
               $dataMonitoring[$programKodeSistem]['nominal_realisasi']  += $dataList[$i]['nominal_realisasi'];
               $dataMonitoring[$programKodeSistem]['sisa_dana']          += $dataList[$i]['sisa_dana'];

               $dataMonitoring[$kegiatanKodeSistem]['nominal_usulan']     += $dataList[$i]['nominal_usulan'];
               $dataMonitoring[$kegiatanKodeSistem]['nominal_setuju']     += $dataList[$i]['nominal_setuju'];
               $dataMonitoring[$kegiatanKodeSistem]['nominal_pencairan']  += $dataList[$i]['nominal_pencairan'];
               $dataMonitoring[$kegiatanKodeSistem]['nominal_realisasi']  += $dataList[$i]['nominal_realisasi'];
               $dataMonitoring[$kegiatanKodeSistem]['sisa_dana']          += $dataList[$i]['sisa_dana'];

               $dataGrid[$index]['kode']        = $dataList[$i]['sub_kegiatan_kode'];
               $dataGrid[$index]['nama']        = $dataList[$i]['sub_kegiatan_nama'];
               $dataGrid[$index]['unit_kode']   = $dataList[$i]['unit_kode'];
               $dataGrid[$index]['unit_nama']   = $dataList[$i]['unit_nama'];
               $dataGrid[$index]['status']      = strtoupper($dataList[$i]['status']);
               $dataGrid[$index]['nominal_usulan']    = $dataList[$i]['nominal_usulan'];
               $dataGrid[$index]['nominal_setuju']    = $dataList[$i]['nominal_setuju'];
               $dataGrid[$index]['nominal_pencairan'] = $dataList[$i]['nominal_pencairan'];
               $dataGrid[$index]['nominal_realisasi'] = $dataList[$i]['nominal_realisasi'];
               $dataGrid[$index]['sisa_dana']         = $dataList[$i]['sisa_dana'];
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
                  $list['nominal_pencairan'] = number_format($dataMonitoring[$list['kode_sistem']]['nominal_pencairan'], 0, ',','.');
                  $list['nominal_realisasi'] = number_format($dataMonitoring[$list['kode_sistem']]['nominal_realisasi'], 0, ',','.');
                  $list['sisa_dana']         = number_format($dataMonitoring[$list['kode_sistem']]['sisa_dana'], 0, ',','.');
                  break;
               case 'KEGIATAN':
                  $list['nominal_usulan']    = number_format($dataMonitoring[$list['kode_sistem']]['nominal_usulan'], 0, ',','.');
                  $list['nominal_setuju']    = number_format($dataMonitoring[$list['kode_sistem']]['nominal_setuju'], 0, ',','.');
                  $list['nominal_pencairan'] = number_format($dataMonitoring[$list['kode_sistem']]['nominal_pencairan'], 0, ',','.');
                  $list['nominal_realisasi'] = number_format($dataMonitoring[$list['kode_sistem']]['nominal_realisasi'], 0, ',','.');
                  $list['sisa_dana']         = number_format($dataMonitoring[$list['kode_sistem']]['sisa_dana'], 0, ',','.');
                  break;
               default:
                  $list['nominal_usulan']    = number_format($list['nominal_usulan'], 0, ',','.');
                  $list['nominal_setuju']    = number_format($list['nominal_setuju'], 0, ',','.');
                  $list['nominal_pencairan'] = number_format($list['nominal_pencairan'], 0, ',','.');
                  $list['nominal_realisasi'] = number_format($list['nominal_realisasi'], 0, ',','.');
                  $list['sisa_dana']         = number_format($list['sisa_dana'], 0, ',','.');
                  break;
            }
            $this->mrTemplate->AddVars('data_list', $list);
            $this->mrTemplate->parseTemplate('data_list', 'a');
         }

         foreach ($dataResume as $resume) {
            $resume['nominal_usulan']     = number_format($resume['nominal_usulan'], 0, ',','.');
            $resume['nominal_setuju']     = number_format($resume['nominal_setuju'], 0, ',','.');
            $resume['nominal_pencairan']  = number_format($resume['nominal_pencairan'], 0, ',','.');
            $resume['nominal_realisasi']  = number_format($resume['nominal_realisasi'], 0, ',','.');
            $resume['sisa_dana']          = number_format($resume['sisa_dana'], 0, ',','.');
            $this->mrTemplate->AddVars('resume_item', $resume);
            $this->mrTemplate->parseTemplate('resume_item', 'a');
         }
      }
   }
}
?>