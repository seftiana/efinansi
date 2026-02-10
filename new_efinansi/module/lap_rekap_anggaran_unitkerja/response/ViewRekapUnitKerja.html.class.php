<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/lap_rekap_anggaran_unitkerja/business/RekapUnitKerja.class.php';
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/user_unit_kerja/business/UserUnitKerja.class.php';

class ViewRekapUnitKerja extends HtmlResponse
{
   function TemplateModule(){
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
         'module/lap_rekap_anggaran_unitkerja/template');
      $this->SetTemplateFile('view_rekap_unitkerja.html');
   }

   function ProcessRequest(){
      $userid           = Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
      $mUnitObj         = new UserUnitKerja();
      $dataUnit         = $mUnitObj->GetUnitKerjaRefUser($userid);
      $mObj             = new RekapUnitKerja();
      $arrPeriodeTahun  = $mObj->GetPeriodeTahun();
      $periodeTahun     = $mObj->GetPeriodeTahun(array(
         'active' => true
      ));
      $arrJenisKegiatan = $mObj->GetComboJenisKegiatan();
      $requestData      = array();

      if(isset($mObj->_POST['btnTampilkan'])){
         $requestData['ta_id']            = $mObj->_POST['tahun_anggaran'];
         $requestData['jenis_kegiatan']   = $mObj->_POST['jenis_kegiatan'];
         $requestData['unit_id']          = trim($mObj->_POST['unit_id']);
         $requestData['unit_nama']        = trim($mObj->_POST['unit_nama']);
         $requestData['program_id']       = $mObj->_POST['program_id'];
         $requestData['program_nama']     = trim($mObj->_POST['program_nama']);
      }elseif(isset($mObj->_GET['search'])){
         $requestData['ta_id']            = Dispatcher::Instance()->Decrypt($mObj->_GET['ta_id']);
         $requestData['jenis_kegiatan']   = Dispatcher::Instance()->Decrypt($mObj->_GET['jenis_kegiatan']);
         $requestData['unit_id']          = Dispatcher::Instance()->Decrypt($mObj->_GET['unit_id']);
         $requestData['unit_nama']        = Dispatcher::Instance()->Decrypt($mObj->_GET['unit_nama']);
         $requestData['program_id']       = Dispatcher::Instance()->Decrypt($mObj->_GET['program_id']);
         $requestData['program_nama']     = Dispatcher::Instance()->Decrypt($mObj->_GET['program_nama']);
      }else{
         $requestData['ta_id']            = $periodeTahun[0]['id'];
         $requestData['unit_id']          = $dataUnit['id'];
         $requestData['unit_nama']        = $dataUnit['nama'];
         $requestData['jenis_kegiatan']   = '';
         $requestData['program_id']       = '';
         $requestData['program_nama']     = '';
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
            $query[$key]   = Dispatcher::Instance()->Encrypt($value);
         }
         $queryString      = urldecode(http_build_query($query));
      }

      $offset        = 0;
      $limit         = 20;
      $page          = 0;
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

      # Combobox
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
            'style="width: 135px;" id="cmb_tahun_anggaran"'
         ),
         Messenger::CurrentRequest
      );

      # Combobox
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
            'id="cmb_jenis_kegiatan" style="width: 235px;"'
         ),
         Messenger::CurrentRequest
      );

      $return['data_unit']       = $dataUnit;
      $return['request_data']    = $requestData;
      $return['query_string']    = $queryString;
      $return['data_list']       = $mObj->ChangeKeyName($dataList);
      $return['start']           = $offset+1;
      $return['data_resume']     = $mObj->ChangeKeyName($dataResume);
      return $return;
   }

   function ParseTemplate($data = NULL){
      $requestData         = $data['request_data'];
      $dataUnit            = $data['data_unit'];
      $dataList            = $data['data_list'];
      $start               = $data['start'];
      $dataResume          = $data['data_resume'];
      $queryString         = $data['query_string'];
      $urlSearch           = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         Dispatcher::Instance()->mSubModule,
         Dispatcher::Instance()->mAction,
         Dispatcher::Instance()->mType
      );
      $urlPopupUnit        = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         'PopupUnitKerja',
         'view',
         'html'
      );
      $urlPopupProgram     = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         'PopupProgram',
         'view',
         'html'
      );
      $urlCetak            = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         'cetakUnitKerja',
         'view',
         'html'
      ).'&'.$queryString;
      $urlExcel            = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         'ExcelUnitKerja',
         'view',
         'xlsx'
      ).'&'.$queryString;

      $this->mrTemplate->AddVar('content', 'URL_SEARCH', $urlSearch);
      $this->mrTemplate->AddVar('content', 'URL_CETAK', $urlCetak);
      $this->mrTemplate->AddVar('content', 'URL_EXCEL', $urlExcel);
      $this->mrTemplate->AddVars('content', $requestData);
      $this->mrTemplate->AddVar('data_unit', 'STATUS', strtoupper($dataUnit['status']));
      $this->mrTemplate->AddVar('data_unit', 'UNIT_ID', $requestData['unit_id']);
      $this->mrTemplate->AddVar('data_unit', 'UNIT_NAMA', $requestData['unit_nama']);
      $this->mrTemplate->AddVar('data_unit', 'URL_POPUP_UNIT', $urlPopupUnit);
      $this->mrTemplate->AddVar('content', 'URL_POPUP_PROGRAM', $urlPopupProgram);

      if(empty($dataList)){
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
         $this->mrTemplate->AddVar('data_resume', 'DATA_EMPTY', 'YES');
      }else{
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');
         $this->mrTemplate->AddVar('data_resume', 'DATA_EMPTY', 'NO');
         // inisialisasi data
         $unit          = '';
         $program       = '';
         $kegiatan      = '';
         $subkegiatan   = '';
         $index         = 0;
         $dataGrid      = array();
         $nomor         = 0;
         $dataRekap     = array();
         for ($i=0; $i < count($dataList);) {
            if((int)$unit === (int)$dataList[$i]['unit_id'] && (int)$program === (int)$dataList[$i]['program_id'] && (int)$kegiatan === (int)$dataList[$i]['kegiatan_id']){
               $programKodeSistem         = $unit.'.'.$program;
               $kegiatanKodeSistem        = $unit.'.'.$program.'.'.$kegiatan;
               $dataRekap[$programKodeSistem]['nominal_setuju']   += $dataList[$i]['nominal_setuju'];
               $dataRekap[$kegiatanKodeSistem]['nominal_setuju']  += $dataList[$i]['nominal_setuju'];
               $dataGrid[$index]['id']    = $dataList[$i]['sub_kegiatan_id'];
               $dataGrid[$index]['kode']  = $dataList[$i]['sub_kegiatan_kode'];
               $dataGrid[$index]['nama']  = $dataList[$i]['sub_kegiatan_nama'];
               $dataGrid[$index]['jenis'] = 'sub_kegiatan';
               $dataGrid[$index]['class_name']     = ($nomor % 2 <> 0) ? 'table-common-even' : '';
               $dataGrid[$index]['row_style']      = '';
               $dataGrid[$index]['nominal_setuju'] = $dataList[$i]['nominal_setuju'];
               $i++;
               $nomor++;
            }elseif((int)$unit === (int)$dataList[$i]['unit_id'] && (int)$program === (int)$dataList[$i]['program_id'] && (int)$kegiatan !== (int)$dataList[$i]['kegiatan_id']){
               unset($nomor);
               $nomor                     = 1;
               $kegiatan                  = (int)$dataList[$i]['kegiatan_id'];
               $kodeSistem                = $unit.'.'.$program.'.'.$kegiatan;
               $dataRekap[$kodeSistem]['nominal_setuju']    = 0;
               $dataGrid[$index]['id']    = $dataList[$i]['kegiatan_id'];
               $dataGrid[$index]['kode']  = $dataList[$i]['kegiatan_kode'];
               $dataGrid[$index]['nama']  = $dataList[$i]['kegiatan_nama'];
               $dataGrid[$index]['kode_sistem'] = $kodeSistem;
               $dataGrid[$index]['unit_nama']   = '';
               $dataGrid[$index]['class_name']  = 'table-common-even2';
               $dataGrid[$index]['row_style']   = '';
               $dataGrid[$index]['jenis'] = 'kegiatan';
            }elseif((int)$unit === (int)$dataList[$i]['unit_id'] && (int)$program !== (int)$dataList[$i]['program_id']){
               $program       = (int)$dataList[$i]['program_id'];
               // continue($index);
            }else{
               $unit          = (int)$dataList[$i]['unit_id'];
               $kodeSistem    = $unit.'.'.(int)$dataList[$i]['program_id'];
               $dataRekap[$kodeSistem]['nominal_setuju']    = 0;
               $dataGrid[$index]['id']    = $dataList[$i]['program_id'];
               $dataGrid[$index]['kode']  = $dataList[$i]['program_kode'];
               $dataGrid[$index]['nama']  = $dataList[$i]['program_nama'];
               $dataGrid[$index]['unit_nama']   = $dataList[$i]['unit_nama'];
               $dataGrid[$index]['unit_id']     = $dataList[$i]['unit_id'];
               $dataGrid[$index]['unit_kode']   = $dataList[$i]['unit_kode'];
               $dataGrid[$index]['kode_sistem'] = $kodeSistem;
               $dataGrid[$index]['class_name']  = 'table-common-even1';
               $dataGrid[$index]['row_style']   = 'font-weight: bold;';
               $dataGrid[$index]['jenis']       = 'program';
            }
            $index++;
         }

         foreach ($dataGrid as $list) {
            switch (strtoupper($list['jenis'])) {
               case 'PROGRAM':
                  $list['nominal_setuju']    = number_format($dataRekap[$list['kode_sistem']]['nominal_setuju'], 0, ',', '.');
                  break;
               case 'KEGIATAN':
                  $list['nominal_setuju']    = number_format($dataRekap[$list['kode_sistem']]['nominal_setuju'], 0, ',', '.');
                  break;
               case 'SUB_KEGIATAN':
                  $list['nominal_setuju']    = number_format($list['nominal_setuju'], 0, ',','.');
                  break;
               default:
                  $list['nominal_setuju']    = number_format($list['nominal_setuju'], 0, ',','.');
                  break;
            }
            $this->mrTemplate->AddVars('data_list', $list);
            $this->mrTemplate->parseTemplate('data_list', 'a');
         }

         // DATA RESUME
         foreach ($dataResume as $resume) {
            $resume['nominal_setuju']     = number_format($resume['nominal_setuju'], 0, ',','.');
            $this->mrTemplate->AddVars('resume_item', $resume);
            $this->mrTemplate->parseTemplate('resume_item', 'a');
         }
      }
   }
}
?>