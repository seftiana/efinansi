<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/lap_rincian_pengeluaran_unit/business/AppLapRincianPengeluaran.class.php';
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/user_unit_kerja/business/UserUnitKerja.class.php';

class ViewLapRincianPengeluaran extends HtmlResponse
{
   function TemplateModule()
   {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
         'module/lap_rincian_pengeluaran_unit/template');
      $this->SetTemplateFile('view_lap_rincian_pengeluaran.html');
   }

   function ProcessRequest()
   {
      $mObj       = new AppLapRincianPengeluaran();
      $mUnitObj   = new UserUnitKerja();
      $userid     = Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
      $arrPeriodeTahun     = $mObj->GetPeriodeTahun();
      $periodeTahun        = $mObj->GetPeriodeTahun(array('active' => true));
      $dataUnit            = $mUnitObj->GetUnitKerjaRefUser($userid);
      $requestData         = array();

      if(isset($mObj->_POST['btncari'])){
         $requestData['ta_id']      = $mObj->_POST['tahun_anggaran'];
         $requestData['unit_id']    = $mObj->_POST['unit_id'];
         $requestData['unit_nama']  = trim($mObj->_POST['unit_nama']);
      }elseif(isset($mObj->_GET['search'])){
         $requestData['ta_id']      = Dispatcher::Instance()->Decrypt($mObj->_GET['ta_id']);
         $requestData['unit_id']    = Dispatcher::Instance()->Decrypt($mObj->_GET['unit_id']);
         $requestData['unit_nama']  = Dispatcher::Instance()->Decrypt($mObj->_GET['unit_nama']);
      }else{
         $requestData['ta_id']      = $periodeTahun[0]['id'];
         $requestData['unit_id']    = $dataUnit['id'];
         $requestData['unit_nama']  = $dataUnit['nama'];
      }

      foreach ($arrPeriodeTahun as $ta) {
         if((int)$ta['id'] === (int)$requestData['ta_id']){
            $requestData['ta_nama'] = $ta['name'];
         }
      }

      if(method_exists(Dispatcher::Instance(), 'getQueryString')){
         $queryString         = Dispatcher::Instance()->getQueryString($requestData);
      }else{
         $query               = array();
         foreach ($requestData as $key => $value) {
            $query[$key]      = Dispatcher::Instance()->Encrypt($value);
         }
         $queryString         = urldecode(http_build_query($query));
      }

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
            'id="cmb_tahun_anggaran" style="width: 215px;"'
         ),
         Messenger::CurrentRequest
      );

      $itemViewed    = 20;
      $currPage      = 1;
      $startRec      = 0 ;
      if(isset($_GET['page'])) {
         $currPage   = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
         $startRec   = ($currPage-1) * $itemViewed;
      }

      $dataList      = $mObj->GetData($startRec, $itemViewed, (array)$requestData);
      $totalData     = $mObj->Count();

      $url        = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         Dispatcher::Instance()->mSubModule,
         Dispatcher::Instance()->mAction,
         Dispatcher::Instance()->mType).'&search=1&'.$queryString;

      Messenger::Instance()->SendToComponent(
         'paging',
         'Paging',
         'view',
         'html',
         'paging_top',
         array(
            $itemViewed,
            $totalData,
            $url,
            $currPage
         ), Messenger::CurrentRequest);

      $return['data_unit']       = $dataUnit;
      $return['request_data']    = $requestData;
      $return['query_string']    = $queryString;
      $return['itemViewed']      = $itemViewed;
      $return['start']           = $startRec+1;
      $return['data_list']       = $mObj->ChangeKeyName($dataList);
      return $return;
   }

   function ParseTemplate($data = NULL)
   {
      $requestData   = $data['request_data'];
      $dataUnit      = $data['data_unit'];
      $queryString   = $data['query_string'];
      $dataList      = $data['data_list'];
      $start         = $data['start'];
      $urlSearch     = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         Dispatcher::Instance()->mSubModule,
         Dispatcher::Instance()->mAction,
         Dispatcher::Instance()->mType
      );
      $urlPopupUnit  = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         'PopupUnitKerja',
         'view',
         'html'
      );
      $urlCetak      = Dispatcher::Instance()->GetUrl(
         'lap_rincian_pengeluaran_unit',
         'cetakLapRincianPengeluaran',
         'view',
         'html'
      ).'&'.$queryString;
      $urlExcel      = Dispatcher::Instance()->GetUrl(
         'lap_rincian_pengeluaran_unit',
         'excelLapRincianPengeluaran',
         'view',
         'xlsx'
      ) .'&'.$queryString;

      $this->mrTemplate->AddVar('content', 'URL_SEARCH', $urlSearch);
      $this->mrTemplate->AddVar('data_unit', 'STATUS', strtoupper($dataUnit['status']));
      $this->mrTemplate->AddVar('data_unit', 'UNIT_ID', $requestData['unit_id']);
      $this->mrTemplate->AddVar('data_unit', 'UNIT_NAMA', $requestData['unit_nama']);
      $this->mrTemplate->AddVar('data_unit', 'URL_POPUP_UNIT', $urlPopupUnit);
      $this->mrTemplate->AddVar('content', 'URL_CETAK', $urlCetak);


      $this->mrTemplate->AddVar('content', 'URL_EXCEL', $urlExcel);

      if (empty($dataList)) {
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
      } else {
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');
         // inisialisasi data
         $program       = '';
         $kegiatan      = '';
         $subkegiatan   = '';
         $kegdet        = '';
         $makId         = '';
         $index         = 0;
         $dataGrid      = array();
         $dataRincian   = array();

         for ($i=0; $i < count($dataList);) {
            if((int)$dataList[$i]['program_id'] === (int)$program
               && (int)$dataList[$i]['kegiatan_id'] === (int)$kegiatan
               && (int)$subkegiatan === (int)$dataList[$i]['sub_kegiatan_id']
               && (int)$kegdet === (int)$dataList[$i]['kegdet_id']
               && (int)$makId === (int)$dataList[$i]['mak_id']){
               // kode sistem
               $kodeSistemProgram         = $program;
               $kodeSistemKegiatan        = $program.'.'.$kegiatan;
               $kodeSistemSubKegiatan     = $program.'.'.$kegiatan.'.'.$subkegiatan;
               $kodeSistemKegdet          = $program.'.'.$kegiatan.'.'.$subkegiatan.'.'.$kegdet;
               $kodeSistemMak             = $program.'.'.$kegiatan.'.'.$subkegiatan.'.'.$kegdet.'.'.$makId;

               $dataRincian[$kodeSistemProgram]['nominal_satuan']       += $dataList[$i]['setuju_nominal'];
               $dataRincian[$kodeSistemKegiatan]['nominal_satuan']      += $dataList[$i]['setuju_nominal'];
               $dataRincian[$kodeSistemSubKegiatan]['nominal_satuan']   += $dataList[$i]['setuju_nominal'];
               $dataRincian[$kodeSistemKegdet]['nominal_satuan']        += $dataList[$i]['setuju_nominal'];
               $dataRincian[$kodeSistemMak]['nominal_satuan']           += $dataList[$i]['setuju_nominal'];

               $dataRincian[$kodeSistemProgram]['nominal_total']       += $dataList[$i]['setuju_jumlah'];
               $dataRincian[$kodeSistemKegiatan]['nominal_total']      += $dataList[$i]['setuju_jumlah'];
               $dataRincian[$kodeSistemSubKegiatan]['nominal_total']   += $dataList[$i]['setuju_jumlah'];
               $dataRincian[$kodeSistemKegdet]['nominal_total']        += $dataList[$i]['setuju_jumlah'];
               $dataRincian[$kodeSistemMak]['nominal_total']           += $dataList[$i]['setuju_jumlah'];

               $dataGrid[$index]['kode']        = $dataList[$i]['komp_kode'];
               $dataGrid[$index]['nama']        = $dataList[$i]['komp_nama'];
               $dataGrid[$index]['unit_nama']   = $dataList[$i]['unit_nama'];
               $dataGrid[$index]['nomor']       = $start;
               $dataGrid[$index]['tipe']        = 'komponen';
               $dataGrid[$index]['volume']      = $dataList[$i]['volume'];
               $dataGrid[$index]['nominal_satuan']    = $dataList[$i]['setuju_nominal'];
               $dataGrid[$index]['nominal_total']     = $dataList[$i]['setuju_jumlah'];
               $i++;
               $start++;
            }elseif((int)$dataList[$i]['program_id'] === (int)$program
               && (int)$dataList[$i]['kegiatan_id'] === (int)$kegiatan
               && (int)$subkegiatan === (int)$dataList[$i]['sub_kegiatan_id']
               && (int)$kegdet === (int)$dataList[$i]['kegdet_id']
               && (int)$makId !== (int)$dataList[$i]['mak_id']){
               $makId            = (int)$dataList[$i]['mak_id'];
               $kodeSistem       = $program.'.'.$kegiatan.'.'.$subkegiatan.'.'.$kegdet.'.'.$makId;
               $dataRincian[$kodeSistem]['nominal_satuan']  = 0;
               $dataRincian[$kodeSistem]['nominal_total']   = 0;
               $dataGrid[$index]['kode_sistem'] = $kodeSistem;
               $dataGrid[$index]['nomor']       = '';
               $dataGrid[$index]['kode']        = $dataList[$i]['mak_kode'];
               $dataGrid[$index]['nama']        = $dataList[$i]['mak_nama'];
               $dataGrid[$index]['class_name']  = 'rkat';
               $dataGrid[$index]['row_style']   = 'font-weight: bold;';
               $dataGrid[$index]['tipe']        = 'rkat';
            }elseif((int)$dataList[$i]['program_id'] === (int)$program
               && (int)$dataList[$i]['kegiatan_id'] === (int)$kegiatan
               && (int)$subkegiatan === (int)$dataList[$i]['sub_kegiatan_id']
               && (int)$kegdet !== (int)$dataList[$i]['kegdet_id']){
               $kegdet           = (int)$dataList[$i]['kegdet_id'];
               $kodeSistem       = $program.'.'.$kegiatan.'.'.$subkegiatan.'.'.$kegdet;
               $dataRincian[$kodeSistem]['nominal_satuan']  = 0;
               $dataRincian[$kodeSistem]['nominal_total']   = 0;
               $dataGrid[$index]['kode_sistem'] = $kodeSistem;
               $dataGrid[$index]['nomor']       = '';
               $dataGrid[$index]['kode']        = $dataList[$i]['sub_kegiatan_kode'].'.'.$kegdet;
               $dataGrid[$index]['nama']        = $dataList[$i]['deskripsi'];
               $dataGrid[$index]['class_name']  = 'table-common-even2';
               $dataGrid[$index]['row_style']   = 'font-style: italic;';
               $dataGrid[$index]['tipe']        = 'referensi';
            }elseif((int)$dataList[$i]['program_id'] === (int)$program
               && (int)$dataList[$i]['kegiatan_id'] === (int)$kegiatan
               && (int)$subkegiatan !== (int)$dataList[$i]['sub_kegiatan_id']){
               $subkegiatan      = (int)$dataList[$i]['sub_kegiatan_id'];
               $kodeSistem       = $program.'.'.$kegiatan.'.'.$subkegiatan;
               $dataRincian[$kodeSistem]['nominal_satuan']  = 0;
               $dataRincian[$kodeSistem]['nominal_total']   = 0;
               $dataGrid[$index]['kode_sistem'] = $kodeSistem;
               $dataGrid[$index]['nomor']       = '';
               $dataGrid[$index]['kode']        = $dataList[$i]['sub_kegiatan_kode'];
               $dataGrid[$index]['nama']        = $dataList[$i]['sub_kegiatan_nama'];
               $dataGrid[$index]['class_name']  = 'table-common-even2';
               $dataGrid[$index]['row_style']   = 'font-weight: bold;';
               $dataGrid[$index]['tipe']        = 'sub_kegiatan';
               $dataGrid[$index]['rkakl']       = 'sub_kegiatan';
               $dataGrid[$index]['rkakl_nama']  = $dataList[$i]['rkakl_sub_kegiatan_nama'];
            }elseif((int)$dataList[$i]['program_id'] === (int)$program
               && (int)$dataList[$i]['kegiatan_id'] !== (int)$kegiatan){
               $kegiatan         = (int)$dataList[$i]['kegiatan_id'];
               $kodeSistem       = $program.'.'.$kegiatan;
               $dataRincian[$kodeSistem]['nominal_satuan']  = 0;
               $dataRincian[$kodeSistem]['nominal_total']   = 0;
               $dataGrid[$index]['kode_sistem'] = $kodeSistem;
               $dataGrid[$index]['nomor']       = '';
               $dataGrid[$index]['kode']        = $dataList[$i]['kegiatan_kode'];
               $dataGrid[$index]['nama']        = $dataList[$i]['kegiatan_nama'];
               $dataGrid[$index]['class_name']  = 'table-common-even1';
               $dataGrid[$index]['row_style']   = 'font-weight: bold; font-style: italic;';
               $dataGrid[$index]['tipe']        = 'kegiatan';
               $dataGrid[$index]['rkakl']       = 'output';
               $dataGrid[$index]['rkakl_nama']  = $dataList[$i]['rkakl_output_nama'];
               $dataGrid[$index]['ikk_nama']    = $dataList[$i]['ikk_nama'];
               $dataGrid[$index]['iku_nama']    = $dataList[$i]['iku_nama'];
               $dataGrid[$index]['output']      = '-';
            }else{
               $program          = (int)$dataList[$i]['program_id'];
               $kodeSistem       = $program;
               $dataRincian[$kodeSistem]['nominal_satuan']  = 0;
               $dataRincian[$kodeSistem]['nominal_total']   = 0;
               $dataGrid[$index]['kode_sistem'] = $kodeSistem;
               $dataGrid[$index]['kode']        = $dataList[$i]['program_kode'];
               $dataGrid[$index]['nama']        = $dataList[$i]['program_nama'];
               $dataGrid[$index]['class_name']  = 'table-common-even1';
               $dataGrid[$index]['row_style']   = 'font-weight: bold;';
               $dataGrid[$index]['nomor']       = '';
               $dataGrid[$index]['tipe']        = 'program';
               $dataGrid[$index]['rkakl']       = 'kegiatan';
               $dataGrid[$index]['rkakl_nama']  = $dataList[$i]['rkakl_kegiatan_nama'];
            }
            $index++;
         }

         foreach ($dataGrid as $list) {
            $this->mrTemplate->clearTemplate('rkakl');
            switch (strtoupper($list['tipe'])) {
               case 'PROGRAM':
                  $this->mrTemplate->AddVar('rkakl', 'JENIS', strtoupper($list['rkakl']));
                  $this->mrTemplate->AddVar('rkakl', 'RKAKL_NAMA', $list['rkakl_nama']);

                  $list['nominal_satuan']    = number_format($dataRincian[$list['kode_sistem']]['nominal_satuan'], 0, ',','.');
                  $list['nominal_total']     = number_format($dataRincian[$list['kode_sistem']]['nominal_total'], 0, ',','.');
                  break;
               case 'KEGIATAN':
                  $this->mrTemplate->AddVar('rkakl', 'JENIS', strtoupper($list['rkakl']));
                  $this->mrTemplate->AddVar('rkakl', 'RKAKL_NAMA', $list['rkakl_nama']);

                  $list['nominal_satuan']    = number_format($dataRincian[$list['kode_sistem']]['nominal_satuan'], 0, ',','.');
                  $list['nominal_total']     = number_format($dataRincian[$list['kode_sistem']]['nominal_total'], 0, ',','.');
                  break;
               case 'SUB_KEGIATAN':
                  $this->mrTemplate->AddVar('rkakl', 'JENIS', strtoupper($list['rkakl']));
                  $this->mrTemplate->AddVar('rkakl', 'RKAKL_NAMA', $list['rkakl_nama']);

                  $list['nominal_satuan']    = number_format($dataRincian[$list['kode_sistem']]['nominal_satuan'], 0, ',','.');
                  $list['nominal_total']     = number_format($dataRincian[$list['kode_sistem']]['nominal_total'], 0, ',','.');
                  break;
               case 'REFERENSI':
                  break;
               case 'RKAT':
                  $list['nominal_satuan']    = number_format($dataRincian[$list['kode_sistem']]['nominal_satuan'], 0, ',','.');
                  $list['nominal_total']     = number_format($dataRincian[$list['kode_sistem']]['nominal_total'], 0, ',','.');
                  break;
               case 'KOMPONEN':
                  $list['nominal_satuan']    = number_format($list['nominal_satuan'], 0, ',','.');
                  $list['nominal_total']     = number_format($list['nominal_total'], 0, ',','.');
                  break;
               default:
                  $list['nominal_satuan']    = number_format($list['nominal_satuan'], 0, ',','.');
                  $list['nominal_total']     = number_format($list['nominal_total'], 0, ',','.');
                  break;
            }
            $this->mrTemplate->AddVars('data_list', $list);
            $this->mrTemplate->parseTemplate('data_list', 'a');
         }
      }
   }
}
?>