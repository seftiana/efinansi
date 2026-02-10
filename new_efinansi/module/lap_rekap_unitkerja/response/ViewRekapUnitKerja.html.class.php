<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/lap_rekap_unitkerja/business/RekapUnitKerja.class.php';
require_once GTFWConfiguration::GetValue('application','docroot').
'module/user_unit_kerja/business/UserUnitKerja.class.php';

class ViewRekapUnitKerja extends HtmlResponse
{
   protected $RekapUnitKerja;
   protected $data;
   function ViewRekapUnitKerja()
   {
     $this->RekapUnitKerja = new RekapUnitKerja();
   }


   function TemplateModule()
   {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
         'module/lap_rekap_unitkerja/template');
      $this->SetTemplateFile('view_rekap_unitkerja.html');
   }

   function ProcessRequest()
   {
      $mObj             = new RekapUnitKerja();
      $mUnitObj         = new UserUnitKerja;
      $userid           = Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
      $arrUnitKerja     = $mObj->ChangeKeyName($mUnitObj->GetUnitKerjaRefUser($userid));
      $arrPeriodeTahun  = $mObj->GetPeriodeTahun();
      $periodeTahun     = $mObj->GetPeriodeTahun(array('active' => true));
      $arrJenisKegiatan = $mObj->GetComboJenisKegiatan();
      $requestData      = array();

      if(isset($mObj->_POST['btnTampilkan'])){
         $requestData['ta_id']            = $mObj->_POST['data']['ta_id'];
         $requestData['unit_nama']        = trim($mObj->_POST['data']['unit_nama']);
         $requestData['unit_id']          = $mObj->_POST['data']['unit_id'];
         $requestData['program_id']       = $mObj->_POST['data']['program_id'];
         $requestData['program_nama']     = trim($mObj->_POST['data']['program_nama']);
         $requestData['jenis_kegiatan']   = $mObj->_POST['data']['jenis_kegiatan'];
      }elseif(isset($mObj->_GET['search'])){
         $requestData['ta_id']            = Dispatcher::Instance()->Decrypt($mObj->_GET['ta_id']);
         $requestData['unit_nama']        = trim(Dispatcher::Instance()->Decrypt($mObj->_GET['unit_nama']));
         $requestData['unit_id']          = Dispatcher::Instance()->Decrypt($mObj->_GET['unit_id']);
         $requestData['program_id']       = Dispatcher::Instance()->Decrypt($mObj->_GET['program_id']);
         $requestData['program_nama']     = trim(Dispatcher::Instance()->Decrypt($mObj->_GET['program_nama']));
         $requestData['jenis_kegiatan']   = Dispatcher::Instance()->Decrypt($mObj->_GET['jenis_kegiatan']);
      }else{
         $requestData['ta_id']            = $periodeTahun[0]['id'];
         $requestData['unit_nama']        = $arrUnitKerja['nama'];
         $requestData['unit_id']          = $arrUnitKerja['id'];
         $requestData['program_id']       = '';
         $requestData['program_nama']     = '';
         $requestData['jenis_kegiatan']   = '';
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
            $query[$key]   = trim(Dispatcher::Instance()->Encrypt($value));
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

      $destination_id = "subcontent-element";
      $dataList         = $mObj->GetData($offset, $limit, (array)$requestData);
      $total_data       = $mObj->GetCount();
      $dataResume       = $mObj->GetResumeUnitKerja((array)$requestData);

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
            'data[ta_id]',
            $arrPeriodeTahun,
            $requestData['ta_id'],
            false,
            'id="cmb_tahun_anggaran" style="width: 135px;"'
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
            'data[jenis_kegiatan]',
            $arrJenisKegiatan,
            $requestData['jenis_kegiatan'],
            true,
            'id="cmb_jenis_kegiatan" style="width: 215px;"'
         ),
         Messenger::CurrentRequest
      );

      /*$unitkerja        = $mUnitObj->GetUnitKerjaUser($userid);


      if(isset($_POST['btnTampilkan'])) { //pasti dari form pencarian :p
        if(is_object($_POST['data']))
          $this->data = $_POST['data']->AsArray();
       else
          $this->data = $_POST['data'];

         $ta_id_selected = $this->data['ta_id'];
       $jenis_kegiatan = $this->data['jenis_kegiatan'];

       if(!isset($this->data['unit_id'])) {
          $this->data['unit_id']=$unitkerja['unit_kerja_id'];
           $this->data['unit_nama']=$unitkerja['unit_kerja_nama'];
       }

       $search_nav =  '&data[ta_id]='.$this->data['ta_id'].
                        '&data[jenis_kegiatan]='.$this->data['jenis_kegiatan'].
                        '&data[unit_id]='.$this->data['unit_id'].
                        '&data[unit_nama]='.$this->data['unit_nama'];

      } elseif(isset($_GET['data'])) {
         if(is_object($_GET['data']))
          $this->data = $_GET['data']->AsArray();
       else
          $this->data = $_GET['data'];

         $ta_id_selected = $this->data['ta_id'];
       $jenis_kegiatan = $this->data['jenis_kegiatan'];

       if(!isset($this->data['unit_id'])) {
          $this->data['unit_id']=$unitkerja['unit_kerja_id'];
           $this->data['unit_nama']=$unitkerja['unit_kerja_nama'];
       }

       $search_nav = '&data[ta_id]='.$this->data['ta_id'].
                       '&data[jenis_kegiatan]='.$this->data['jenis_kegiatan'].
                       '&data[unit_id]='.$this->data['unit_id'].
                       '&data[unit_nama]='.$this->data['unit_nama'];

     } else {
       $this->data['unit_id']= $unitkerja['unit_kerja_id'];
       $this->data['unit_nama']= $unitkerja['unit_kerja_nama'];
       $search_nav='';
     }

     if(!isset($this->data['ta_id'])) {
       $this->data['ta_id']= $ta_id_selected;
      $this->data['ta_nama']= $ta_nama_selected;
    }

     $itemViewed = 20;
      $currPage = 1;
      $startRec = 0 ;

      if(isset($_GET['page'])) {
         $currPage = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
         $startRec =($currPage-1) * $itemViewed;
      }

    $totalData =  $mObj->GetCount($requestData) ;
    $return['data'] = $mObj->GetData($startRec,$itemViewed,$requestData) ;

    $return['resume_unit_kerja'] = $mObj->GetResumeUnitKerja($this->data);
    $return['resume_program'] = $mObj->GetResumeProgram($this->data);
    $return['resume_kegiatan'] = $mObj->GetResumeKegiatan($this->data);


    $url = Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule,
               Dispatcher::Instance()->mSubModule,
               Dispatcher::Instance()->mAction,
               Dispatcher::Instance()->mType
               ).$search_nav;

      Messenger::Instance()->SendToComponent('paging', 'Paging', 'view', 'html', 'paging_top',
         array($itemViewed,$totalData, $url, $currPage),
         Messenger::CurrentRequest);

   $return['unitkerja'] = $unitkerja;
    $return['total_sub_unit_kerja'] = $mUnitObj->GetTotalSubUnitKerja($unitkerja['unit_kerja_id']);
    $return['startRec'] =$startRec;
   $return['itemViewed'] = $itemViewed;*/
   //debug($return['resume']);

      $return['data_unit']       = $arrUnitKerja;
      $return['request_data']    = $requestData;
      $return['data_list']       = $mObj->ChangeKeyName($dataList);
      $return['start']           = $offset+1;
      $return['data_resume']     = $mObj->ChangeKeyName($dataResume);
      $return['query_string']    = $queryString;
      return $return;
   }

   function ParseTemplate($data = NULL) {
      $dataUnit            = $data['data_unit'];
      $requestData         = $data['request_data'];
      $dataList            = $data['data_list'];
      $start               = $data['start'];
      $dataResume          = $data['data_resume'];
      $queryString         = $data['query_string'].'&page='.$start;
      $urlSearch           = Dispatcher::Instance()->GetUrl(
         'lap_rekap_unitkerja',
         'rekapUnitKerja',
         'view',
         'html'
      );
      $urlPopupUnit        = Dispatcher::Instance()->GetUrl(
         'lap_rekap_unitkerja',
         'unitKerja',
         'popup',
         'html'
      );
      $urlPopupProgram     = Dispatcher::Instance()->GetUrl(
         'lap_rekap_unitkerja',
         'program',
         'popup',
         'html'
      );

      $urlCetak         = Dispatcher::Instance()->GetUrl(
         'lap_rekap_unitkerja',
         'cetakUnitKerja',
         'view',
         'html'
      ).'&'.$queryString;

      $urlExcel         = Dispatcher::Instance()->GetUrl(
         'lap_rekap_unitkerja',
         'ExcelUnitKerja',
         'view',
         'xlsx'
      ).'&'.$queryString;

      $this->mrTemplate->AddVar('content', 'URL_SEARCH',  $urlSearch);
      $this->mrTemplate->AddVars('content', $requestData);
      $this->mrTemplate->AddVar('data_unit', 'LEVEL', strtoupper($dataUnit['status']));
      $this->mrTemplate->AddVar('data_unit', 'UNIT_ID', $dataUnit['id']);
      $this->mrTemplate->AddVar('data_unit', 'UNIT_NAMA', $dataUnit['nama']);
      $this->mrTemplate->AddVar('data_unit', 'POPUP_UNIT', $urlPopupUnit);
      $this->mrTemplate->AddVar('content', 'URL_POPUP_PROGRAM', $urlPopupProgram);
      $this->mrTemplate->AddVar('content', 'URL_CETAK', $urlCetak);
      $this->mrTemplate->AddVar('content', 'URL_EXCEL', $urlExcel);

      if(empty($dataList)){
         $this->mrTemplate->AddVar('data_grid', 'IS_DATA_EMPTY', 'YES');
         $this->mrTemplate->AddVar('resume', 'RESUME_EMPTY', 'YES');
      }else{
         $this->mrTemplate->AddVar('resume', 'RESUME_EMPTY', 'NO');
         $this->mrTemplate->AddVar('data_grid', 'IS_DATA_EMPTY', 'NO');
         $index      = 0;
         $dataGrid   = array();
         $program    = '';
         $kegiatan   = '';
         $unit       = '';
         $dataRekap  = array();

         for ($i=0; $i < count($dataList);) {
            if((int)$dataList[$i]['unit_id'] === (int)$unit && (int)$dataList[$i]['program_id'] === (int)$program && (int)$dataList[$i]['kegiatan_id'] === (int)$kegiatan){

               $programKodeSistem         = $unit.'.'.$program;
               $kegiatanKodeSistem        = $unit.'.'.$program.'.'.$kegiatan;

               $dataRekap[$programKodeSistem]['nominal_usulan']   += $dataList[$i]['nominal_usulan'];
               $dataRekap[$programKodeSistem]['nominal_setuju']   += $dataList[$i]['nominal_setuju'];
               $dataRekap[$kegiatanKodeSistem]['nominal_usulan']  += $dataList[$i]['nominal_usulan'];
               $dataRekap[$kegiatanKodeSistem]['nominal_setuju']  += $dataList[$i]['nominal_setuju'];

               $dataGrid[$index]['id']    = $dataList[$i]['sub_kegiatan_id'];
               $dataGrid[$index]['kode']  = $dataList[$i]['sub_kegiatan_kode'];
               $dataGrid[$index]['nama']  = $dataList[$i]['sub_kegiatan_nama'];
               $dataGrid[$index]['nominal_usulan']    = $dataList[$i]['nominal_usulan'];
               $dataGrid[$index]['nominal_setuju']    = $dataList[$i]['nominal_setuju'];
               $dataGrid[$index]['level']             = 'sub_kegiatan';
               $i++;
            }elseif((int)$dataList[$i]['unit_id'] === (int)$unit && (int)$dataList[$i]['program_id'] === (int)$program && (int)$dataList[$i]['kegiatan_id'] !== (int)$kegiatan){
               $kegiatan                  = (int)$dataList[$i]['kegiatan_id'];
               $kodeSistem                = $unit.'.'.$program.'.'.$dataList[$i]['kegiatan_id'];
               // unset nominal
               $dataRekap[$kodeSistem]['nominal_usulan']    = 0;
               $dataRekap[$kodeSistem]['nominal_setuju']    = 0;

               $dataGrid[$index]['id']    = $dataList[$i]['kegiatan_id'];
               $dataGrid[$index]['kode']  = $dataList[$i]['kegiatan_kode'];
               $dataGrid[$index]['nama']  = $dataList[$i]['kegiatan_nama'];
               $dataGrid[$index]['kode_sistem']    = $kodeSistem;
               $dataGrid[$index]['level']          = 'kegiatan';
               $dataGrid[$index]['class_name']     = 'table-common-even2';
            }elseif((int)$dataList[$i]['unit_id'] === (int)$unit && (int)$dataList[$i]['program_id'] !== (int)$program){
               $program    = (int)$dataList[$i]['program_id'];
               $index--;
            }else{
               $unit                      = (int)$dataList[$i]['unit_id'];
               $kodeSistem                = $unit.'.'.$dataList[$i]['program_id'];
               // unset nominal
               $dataRekap[$kodeSistem]['nominal_usulan']    = 0;
               $dataRekap[$kodeSistem]['nominal_setuju']    = 0;

               $dataGrid[$index]['id']    = $dataList[$i]['program_id'];
               $dataGrid[$index]['kode']  = $dataList[$i]['program_kode'];
               $dataGrid[$index]['nama']  = $dataList[$i]['program_nama'];
               $dataGrid[$index]['unit_nama']      = $dataList[$i]['unit_nama'];
               $dataGrid[$index]['kode_sistem']    = $kodeSistem;
               $dataGrid[$index]['level']          = 'program';
               $dataGrid[$index]['class_name']     = 'table-common-even1';
               $dataGrid[$index]['row_style']      = 'font-weight: bold;';
            }
            $index++;
         }

         foreach ($dataGrid as $list) {
            switch (strtoupper($list['level'])) {
               case 'PROGRAM':
                  $list['nominal_usulan']       = number_format($dataRekap[$list['kode_sistem']]['nominal_usulan'], 0, ',','.');
                  $list['nominal_setuju']       = number_format($dataRekap[$list['kode_sistem']]['nominal_setuju'], 0, ',','.');
                  break;
               case 'KEGIATAN':
                  $list['nominal_usulan']       = number_format($dataRekap[$list['kode_sistem']]['nominal_usulan'], 0, ',','.');
                  $list['nominal_setuju']       = number_format($dataRekap[$list['kode_sistem']]['nominal_setuju'], 0, ',','.');
                  break;
               default:
                  $list['nominal_usulan']       = number_format($list['nominal_usulan'], 0, ',','.');
                  $list['nominal_setuju']       = number_format($list['nominal_setuju'], 0, ',','.');
                  break;
            }
            $this->mrTemplate->AddVars('data_item', $list);
            $this->mrTemplate->parseTemplate('data_item', 'a');
         }

         $number     = 0;
         foreach ($dataResume as $resume) {
            $resume['class_name']         = ($number % 2 == 0) ? 'table-common-even' : '';
            $resume['nominal_usulan']     = number_format($resume['nominal_usulan'], 0, ',','.');
            $resume['nominal_setuju']     = number_format($resume['nominal_setuju'], 0, ',','.');
            $this->mrTemplate->AddVars('resume_item', $resume);
            $this->mrTemplate->parseTemplate('resume_item', 'a');
            $number++;
         }
      }
   }

}
?>