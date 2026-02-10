<?php
#doc
#    classname:    ViewPopupSubKegiatan
#    scope:        PUBLIC
#
#/doc
require_once GTFWConfiguration::GetValue('application','docroot').
'module/rencana_kinerja_tahunan_kegiatan/business/AppPopupSubkegiatan.class.php';
require_once GTFWConfiguration::GetValue('application','docroot').
'module/user_unit_kerja/business/UserUnitKerja.class.php';

class ViewPopupSubKegiatan extends HtmlResponse
{
   function TemplateModule()
   {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
      'module/rencana_kinerja_tahunan_kegiatan/template/');
      $this->SetTemplateFile('view_popup_subkegiatan.html');
   }

   function TemplateBase() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') .
      'main/template/');
      $this->SetTemplateFile('document-common-popup.html');
      $this->SetTemplateFile('layout-common-popup.html');
   }

   function ProcessRequest()
   {
      $mObj          = new AppPopupSubkegiatan();
      $mUnitObj      = new UserUnitKerja();
      $requestData   = array();
      $requestQuery  = $mObj->_getQueryString();

      if(isset($mObj->_POST['btncari'])){
         $requestData['unit_id'] = $mObj->_POST['unit_id'];
         $requestData['ta_id']   = $mObj->_POST['ta_id'];
         $requestData['kode']    = $mObj->_POST['kode'];
         $requestData['nama']    = $mObj->_POST['nama'];
         $requestData['program_id']       = $mObj->_POST['program'];
         $requestData['jenis_kegiatan']   = $mObj->_POST['jenis_kegiatan'];
      }elseif (isset($mObj->_GET['search'])) {
         $requestData['unit_id'] = Dispatcher::Instance()->Decrypt($mObj->_GET['unit_id']);
         $requestData['ta_id']   = Dispatcher::Instance()->Decrypt($mObj->_GET['ta_id']);
         $requestData['kode']    = Dispatcher::Instance()->Decrypt($mObj->_GET['kode']);
         $requestData['nama']    = Dispatcher::Instance()->Decrypt($mObj->_GET['nama']);
         $requestData['program_id']       = Dispatcher::Instance()->Decrypt($mObj->_GET['program_id']);
         $requestData['jenis_kegiatan']   = Dispatcher::Instance()->Decrypt($mObj->_GET['jenis_kegiatan']);
      }else{
         $requestData['unit_id'] = Dispatcher::Instance()->Decrypt($mObj->_GET['unit_id']);
         $requestData['ta_id']   = Dispatcher::Instance()->Decrypt($mObj->_GET['ta_id']);
         $requestData['kode']    = '';
         $requestData['nama']    = '';
         $requestData['program_id']       = '';
         $requestData['jenis_kegiatan']   = '';
      }

      // load default data
      $unitKerja        = $mUnitObj->GetUnitKerja($requestData['unit_id']);
      $periodeTahun     = $mObj->ChangeKeyName($mObj->getPeriodeTahun($requestData['ta_id']));
      $arrDataProgram   = $mObj->GetDataProgram($requestData['ta_id']);
      $arrJenisKegiatan = $mObj->GetDataJenisKegiatan();
      $requestData['unit_nama']  = $unitKerja['unit_kerja_nama'];
      $requestData['ta_nama']    = $periodeTahun['name'];
      $requestData['unitkerja']  = $requestData['unit_id'];
      if(method_exists(Dispatcher::Instance(), 'getQueryString')){
         # @param array
         $queryString      = Dispatcher::instance()->getQueryString($requestData);
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

      $destination_id   = "popup-subcontent";
      // bypass pengecekan unit kerja
      $requestData['unit_id'] = NULL;
      $dataList         = $mObj->getData($offset, $limit, (array)$requestData);
      $total_data       = $mObj->Count();

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
         'program',
         array(
            'program',
            $arrDataProgram,
            $requestData['program_id'],
            true,
            'id="cmb_kegiatan" style="width: 215px;"'
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
            'id="cmb_jenis_kegiatan"'
         ),
         Messenger::CurrentRequest
      );

      $return['request_query']      = $requestQuery;
      $return['request_data']       = $requestData;
      $return['query_string']       = $queryString;
      $return['data_list']          = $mObj->ChangeKeyName($dataList);
      $return['start']              = $offset+1;
      return $return;
   }

   function ParseTemplate($data = null)
   {
      $queryString      = $data['query_string'];
      $requestQuery     = $data['request_query'];
      $requestData      = $data['request_data'];
      $start            = $data['start'];
      $dataList         = $data['data_list'];
      $dataKomponen     = array();
      $urlSearch        = Dispatcher::Instance()->GetUrl(
         'rencana_kinerja_tahunan_kegiatan',
         'PopupSubKegiatan',
         'view',
         'html'
      ).'&'.$requestQuery;

      $this->mrTemplate->AddVar('content', 'URL_SEARCH', $urlSearch);
      $this->mrTemplate->AddVars('content', $requestData);

      if(empty($dataList)){
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
      }else{
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');
         $dataGrid      = array();
         $index         = 0;
         $program       = '';
         $kegiatan      = '';

         for ($i=0; $i < count($dataList);) {
            if((int)$program === (int)$dataList[$i]['program_id'] &&
               (int)$kegiatan === (int)$dataList[$i]['kegiatan_id']){
               $kodeSistem       = $program.'.'.$kegiatan.'.'.$dataList[$i]['komponen_id'];
               $dataKomponen[$kodeSistem] = $dataList[$i];

               $dataGrid[$index]['id']    = $dataList[$i]['komponen_id'];
               $dataGrid[$index]['kode']  = $dataList[$i]['komponen_kode'];
               $dataGrid[$index]['nama']  = $dataList[$i]['komponen_nama'];
               $dataGrid[$index]['no']    = $start;
               $dataGrid[$index]['type']  = 'DATA';
               $dataGrid[$index]['row_style']      = '';
               $dataGrid[$index]['style']          = ($i % 2 <> 0) ? 'table-common-even' : '';
               $dataGrid[$index]['detail_belanja'] = $dataList[$i]['detail'];
               $dataGrid[$index]['kode_sistem']    = $kodeSistem;
               $i++;
               $start++;
            }elseif ((int)$program === (int)$dataList[$i]['program_id'] &&
               (int)$kegiatan !== (int)$dataList[$i]['kegiatan_id']) {
               $kegiatan      = (int)$dataList[$i]['kegiatan_id'];
               $dataGrid[$index]['id']    = $dataList[$i]['kegiatan_id'];
               $dataGrid[$index]['kode']  = $dataList[$i]['kegiatan_kode'];
               $dataGrid[$index]['nama']  = $dataList[$i]['kegiatan_nama'];
               $dataGrid[$index]['type']  = 'KEGIATAN';
               $dataGrid[$index]['class_name']  = 'table-common-even2';
               $dataGrid[$index]['row_style']   = 'font-weight: bold;';
            }else{
               $program       = (int)$dataList[$i]['program_id'];
               $dataGrid[$index]['id']    = $dataList[$i]['program_id'];
               $dataGrid[$index]['kode']  = $dataList[$i]['program_kode'];
               $dataGrid[$index]['nama']  = $dataList[$i]['program_nama'];
               $dataGrid[$index]['type']  = 'PROGRAM';
               $dataGrid[$index]['class_name']  = 'table-common-even1';
               $dataGrid[$index]['row_style']   = 'font-weight: bold;';
            }
            $index++;
         }

         foreach ($dataGrid as $grid) {
            switch (strtoupper($grid['type'])) {
               case 'PROGRAM':
                  $this->mrTemplate->AddVar('content_links', 'LEVEL', strtoupper($grid['type']));
                  break;
               case 'KEGIATAN':
                  $this->mrTemplate->AddVar('content_links', 'LEVEL', strtoupper($grid['type']));
                  break;
               case 'DATA':
                  $this->mrTemplate->AddVar('content_links', 'LEVEL', strtoupper($grid['type']));
                  $this->mrTemplate->AddVars('content_links', $grid);
                  break;
               default:
                  $this->mrTemplate->AddVar('content_links', 'LEVEL', 'DEFAULT');
                  # code...
                  break;
            }

            $this->mrTemplate->AddVars('data_list', $grid);
            $this->mrTemplate->parseTemplate('data_list', 'a');
         }

      }

      $dataJson['data']   = json_encode($dataKomponen);
      $this->mrTemplate->AddVars('content', $dataJson, 'KOMPONEN_');
   }
}
?>