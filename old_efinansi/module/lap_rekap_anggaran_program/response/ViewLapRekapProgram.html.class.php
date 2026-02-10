<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/lap_rekap_anggaran_program/business/AppLapRekapProgram.class.php';
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/user_unit_kerja/business/UserUnitKerja.class.php';
class ViewLapRekapProgram extends HtmlResponse
{
   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
         'module/lap_rekap_anggaran_program/template');
      $this->SetTemplateFile('view_lap_rekap_program.html');
   }

   function ProcessRequest() {
      $userId           = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
      $mObj             = new AppLapRekapProgram();
      $mUnitObj         = new UserUnitKerja();
      $unitkerjaRef     = $mUnitObj->GetUnitKerjaRefUser($userId);
      $arrPeriodeTahun  = $mObj->GetPeriodeTahun();
      $periodeTahun     = $mObj->GetPeriodeTahun(array('active' => true));
      $arrJenisKegiatan = $mObj->GetComboJenisKegiatan();
      $requestData      = array();
      $queryString      = '';

      if(isset($mObj->_POST['btncari'])){
         $requestData['ta_id']            = trim($mObj->_POST['tahun_anggaran']);
         $requestData['unit_id']          = trim($mObj->_POST['unitkerja']);
         $requestData['unit_nama']        = trim($mObj->_POST['unitkerja_label']);
         $requestData['program_id']       = trim($mObj->_POST['program']);
         $requestData['program_nama']     = trim($mObj->_POST['program_label']);
         $requestData['jenis_kegiatan']   = trim($mObj->_POST['jenis_kegiatan']);
      }elseif(isset($mObj->_GET['search'])){
         $requestData['ta_id']            = Dispatcher::Instance()->Decrypt($mObj->_GET['ta_id']);
         $requestData['unit_id']          = Dispatcher::Instance()->Decrypt($mObj->_GET['unit_id']);
         $requestData['unit_nama']        = Dispatcher::Instance()->Decrypt($mObj->_GET['unit_nama']);
         $requestData['program_id']       = Dispatcher::Instance()->Decrypt($mObj->_GET['program_id']);
         $requestData['program_nama']     = Dispatcher::Instance()->Decrypt($mObj->_GET['program_nama']);
         $requestData['jenis_kegiatan']   = Dispatcher::Instance()->Decrypt($mObj->_GET['jenis_kegiatan']);
      }else{
         $requestData['ta_id']            = $periodeTahun[0]['id'];
         $requestData['unit_id']          = $unitkerjaRef['id'];
         $requestData['unit_nama']        = $unitkerjaRef['nama'];
         $requestData['program_id']       = '';
         $requestData['program_nama']     = '';
         $requestData['jenis_kegiatan']   = '';
      }

      foreach ($arrPeriodeTahun as $ta) {
         if((int)$ta['id'] === (int)$requestData['ta_id']){
            $requestData['ta_nama']       = $ta['name'];
         }
      }

      if(method_exists(Dispatcher::Instance(), 'getQueryString')){
         $queryString      = Dispatcher::Instance()->getQueryString($requestData);
      }else{
         $query            = array();
         foreach ($requestData as $key => $value) {
            $query[$key]   = Dispatcher::Instance()->Encrypt($query);
         }
         $queryString      = urldecode(http_build_query($query));
      }

      $offset        = 0;
      $limit         = 20;
      $page          = 0;
      $total_data    = total_data;
      if(isset($_GET['page'])){
         $page       = (string) $_GET['page']->StripHtmlTags()->SqlString()->Raw();
         $offset     = ($page - 1) * $limit;
      }
      #paging url
      $url           = Dispatcher::Instance()->GetUrl(
        Dispatcher::Instance()->mModule,
        Dispatcher::Instance()->mSubModule,
        Dispatcher::Instance()->mAction,
        Dispatcher::Instance()->mType
      ).'&search='.Dispatcher::Instance()->Encrypt(1).'&'.$queryString;

      $destination_id      = "subcontent-element";
      $dataList            = $mObj->GetData($offset, $limit, (array)$requestData);
      $total_data          = $mObj->GetCountData();
      $dataResume          = $mObj->GetResume((array)$requestData);
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
            ' style="width:200px;" id="cmb_tahun_anggaran"'
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

      $return['data_unit']       = $unitkerjaRef;
      $return['request_data']    = $requestData;
      $return['query_string']    = $queryString;
      $return['data_list']       = $mObj->ChangeKeyName($dataList);
      $return['data_resume']     = $mObj->ChangeKeyName($dataResume);
      $return['start']           = $offset+1;
      return $return;
   }

   function ParseTemplate($data = NULL)
   {
      $requestData      = $data['request_data'];
      $dataUnit         = $data['data_unit'];
      $queryString      = $data['query_string'];
      $dataList         = $data['data_list'];
      $dataResume       = $data['data_resume'];
      $start            = $data['start'];
      $urlSearch        = Dispatcher::Instance()->GetUrl(
         'lap_rekap_anggaran_program',
         'lapRekapProgram',
         'view',
         'html'
      );
      $urlCetak         = Dispatcher::Instance()->GetUrl(
         'lap_rekap_anggaran_program',
         'CetakLapRekapProgram',
         'view',
         'html'
      ).'&'.$queryString.'&page='.$start;
      $urlExcel         = Dispatcher::Instance()->GetUrl(
         'lap_rekap_anggaran_program',
         'ExcelLapRekapProgram',
         'view',
         'xlsx'
      ).'&'.$queryString.'&page='.$start;
      $urlPopupProgram  = Dispatcher::Instance()->GetUrl(
         'lap_rekap_anggaran_program',
         'popupProgram',
         'view',
         'html'
      );
      $urlPopupUnit     = Dispatcher::Instance()->GetUrl(
         'lap_rekap_anggaran_program',
         'popupUnitkerja',
         'view',
         'html'
      );

      $this->mrTemplate->AddVar('content', 'URL_SEARCH', $urlSearch);
      $this->mrTemplate->AddVar('content', 'URL_CETAK', $urlCetak);
      $this->mrTemplate->AddVar('content', 'URL_EXCEL', $urlExcel);
      $this->mrTemplate->AddVar('unit_type', 'STATUS', strtoupper($dataUnit['status']));
      $this->mrTemplate->AddVar('unit_type', 'UNIT_ID', $requestData['unit_id']);
      $this->mrTemplate->AddVar('unit_type', 'UNIT_NAMA', $requestData['unit_nama']);
      $this->mrTemplate->AddVar('unit_type', 'URL_POPUP_UNITKERJA', $urlPopupUnit);
      $this->mrTemplate->AddVars('content', $requestData);
      $this->mrTemplate->AddVar('content', 'URL_POPUP_PROGRAM', $urlPopupProgram);

      if(empty($dataList)){
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
         $this->mrTemplate->AddVar('grid_resume', 'RESUME_EMPTY', 'YES');
      }else{
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');
         $this->mrTemplate->AddVar('grid_resume', 'RESUME_EMPTY', 'NO');

         # inisialisasi data
         $program       = '';
         $kegiatan      = '';
         $index         = 0;
         $dataGrid      = array();
         $dataRekap     = array();
         for ($i=0; $i < count($dataList);) {
            if((int)$dataList[$i]['program_id'] === (int)$program && (int)$dataList[$i]['kegiatan_id'] === (int)$kegiatan){
               $programKodeSistem         = $program;
               $kegiatanKodeSistem        = $program.'.'.$kegiatan;
               $dataRekap[$programKodeSistem]['nominal_setuju']      += $dataList[$i]['nominal_setuju'];
               $dataRekap[$kegiatanKodeSistem]['nominal_setuju']     += $dataList[$i]['nominal_setuju'];

               $dataGrid[$index]['id']    = $dataList[$i]['sub_kegiatan_id'];
               $dataGrid[$index]['kode']  = $dataList[$i]['sub_kegiatan_kode'];
               $dataGrid[$index]['nama']  = $dataList[$i]['sub_kegiatan_nama'];
               $dataGrid[$index]['unit_nama']      = $dataList[$i]['unit_nama'];
               $dataGrid[$index]['nominal_setuju'] = $dataList[$i]['nominal_setuju'];
               $dataGrid[$index]['type']           = 'sub_kegiatan';
               $dataGrid[$index]['class_name']     = ($start % 2 <> 0) ? 'table-common-even' : '';
               $i++;
               $start++;
            }elseif((int)$dataList[$i]['program_id'] === (int)$program && (int)$dataList[$i]['kegiatan_id'] !== (int)$kegiatan){
               $kegiatan         = (int)$dataList[$i]['kegiatan_id'];
               $kodeSistem       = $program.'.'.$kegiatan;
               $dataRekap[$kodeSistem]['nominal_setuju']    = 0;
               $dataGrid[$index]['id']    = $dataList[$i]['kegiatan_id'];
               $dataGrid[$index]['kode']  = $dataList[$i]['kegiatan_kode'];
               $dataGrid[$index]['nama']  = $dataList[$i]['kegiatan_nama'];
               $dataGrid[$index]['kode_sistem'] = $kodeSistem;
               $dataGrid[$index]['type']        = 'kegiatan';
               $dataGrid[$index]['class_name']  = 'table-common-even2';
            }else{
               $program          = (int)$dataList[$i]['program_id'];
               $kodeSistem       = $program;
               $dataRekap[$kodeSistem]['nominal_setuju']    = 0;
               $dataGrid[$index]['id']    = $dataList[$i]['program_id'];
               $dataGrid[$index]['kode']  = $dataList[$i]['program_kode'];
               $dataGrid[$index]['nama']  = $dataList[$i]['program_nama'];
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
                  $list['nominal_setuju']    = number_format($dataRekap[$list['kode_sistem']]['nominal_setuju'], 0, ',', '.');
                  break;
               case 'KEGIATAN':
                  $list['nominal_setuju']    = number_format($dataRekap[$list['kode_sistem']]['nominal_setuju'], 0, ',', '.');
                  break;
               default:
                  $list['nominal_setuju']    = number_format($list['nominal_setuju'], 0, ',','.');
                  break;
            }
            $this->mrTemplate->AddVars('data_list', $list);
            $this->mrTemplate->parseTemplate('data_list', 'a');
         }

         foreach ($dataResume as $res) {
            $res['nominal_usulan']     = number_format($res['nominal_usulan'], 0, ',','.');
            $res['nominal_setuju']     = number_format($res['nominal_setuju'], 0, ',','.');
            $this->mrTemplate->AddVars('resume_item', $res);
            $this->mrTemplate->parseTemplate('resume_item', 'a');
         }
      }
   }
}
?>
