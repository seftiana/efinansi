<?php
require_once GTFWConfiguration::GetValue('application','docroot').
'module/rencana_kinerja_tahunan_kegiatan/business/RencanaKinerjaTahunan.class.php';
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/user_unit_kerja/business/UserUnitKerja.class.php';

#doc
#    classname:    ViewRencanaKinerjaTahunan
#    scope:        PUBLIC
#
#/doc

class ViewRencanaKinerjaTahunan extends HtmlResponse
{
   function TemplateModule()
   {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
         'module/rencana_kinerja_tahunan_kegiatan/template/');
      $this->SetTemplateFile('view_rencana_kinerja_tahunan.html');
   }

   function ProcessRequest()
   {
      $messenger           = Messenger::Instance()->Receive(__FILE__);
      $mObj                = new RencanaKinerjaTahunan();
      $mUserUnitObj        = new UserUnitKerja();
      $userId              = $mObj->getUserId();
      $unitKerja           = $mObj->ChangeKeyName($mUserUnitObj->GetUnitKerjaRefUser($userId));
      $arrPeriodeTahun     = $mObj->getPeriodeTahun();
      $periodeTahun        = $mObj->getPeriodeTahun(array('active' => true));
      $arrJenisKegiatan    = $mObj->GetComboJenisKegiatan();
      $arrPrioritas        = $mObj->GetComboPrioritas();
      $requestData         = array();
      $query_string        = '';
      $message             = $style = NULL;
      $months              = $mObj->indonesianMonth;

      if(isset($mObj->_POST['btnTampilkan'])){
         $requestData['ta_id']         = $mObj->_POST['tahun_anggaran'];
         $requestData['unit_id']       = $mObj->_POST['unit_id'];
         $requestData['unit_nama']     = $mObj->_POST['unit_nama'];
         $requestData['program_id']    = $mObj->_POST['program'];
         $requestData['program_nama']  = $mObj->_POST['program_label'];
         $requestData['kode']          = trim($mObj->_POST['kode']);
         $requestData['nama']          = trim($mObj->_POST['nama']);
         $requestData['jenis']         = $mObj->_POST['jenis'];
         $requestData['prioritas']     = $mObj->_POST['prioritas'];
         $requestData['bulan']     = $mObj->_POST['bulan'];
      }elseif(isset($mObj->_GET['search']) OR isset($mObj->_GET['page'])){
         $requestData['ta_id']         = Dispatcher::Instance()->Decrypt($mObj->_GET['ta_id']);
         $requestData['unit_id']       = Dispatcher::Instance()->Decrypt($mObj->_GET['unit_id']);
         $requestData['unit_nama']     = Dispatcher::Instance()->Decrypt($mObj->_GET['unit_nama']);
         $requestData['program_id']    = Dispatcher::Instance()->Decrypt($mObj->_GET['program_id']);
         $requestData['program_nama']  = Dispatcher::Instance()->Decrypt($mObj->_GET['program_nama']);
         $requestData['kode']          = Dispatcher::Instance()->Decrypt($mObj->_GET['kode']);
         $requestData['nama']          = Dispatcher::Instance()->Decrypt($mObj->_GET['nama']);
         $requestData['jenis']         = Dispatcher::Instance()->Decrypt($mObj->_GET['jenis']);
         $requestData['prioritas']     = Dispatcher::Instance()->Decrypt($mObj->_GET['prioritas']);
         $requestData['bulan']         = Dispatcher::Instance()->Decrypt($mObj->_GET['bulan']);
      }else{
         $requestData['ta_id']         = $periodeTahun[0]['id'];
         $requestData['unit_id']       = $unitKerja['id'];
         $requestData['unit_nama']     = $unitKerja['nama'];
         $requestData['program_id']    = '';
         $requestData['program_nama']  = '';
         $requestData['kode']          = '';
         $requestData['nama']          = '';
         $requestData['jenis']         = '';
         $requestData['prioritas']     = '';
         $requestData['bulan']     = '';
      }

      foreach ($arrPeriodeTahun as $ta) {
         if((int)$ta['id'] === (int)$requestData['ta_id']){
            $requestData['ta_nama']    = $ta['name'];
         }
      }

      if(method_exists(Dispatcher::Instance(), 'getQueryString')){
         # @param array
         $query_string     = Dispatcher::instance()->getQueryString($requestData);
      }else{
         $query            = array();
         foreach ($requestData as $key => $value) {
            $query[$key]   = Dispatcher::Instance()->Encrypt($value);
         }
         $query_string     = urldecode(http_build_query($query));
      }

      if($messenger){
         $message          = $messenger[0][1];
         $style            = $messenger[0][2];
      }

      $offset         = 0;
      $limit          = 20;
      $page           = 0;
      $total_data     = total_data;
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
      ).'&search='.Dispatcher::Instance()->Encrypt(1).'&'.$query_string;

      $destination_id   = "subcontent-element";
      $dataList         = $mObj->GetData($offset, $limit, (array)$requestData);
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
         'tahun_anggaran',
         array(
            'tahun_anggaran',
            $arrPeriodeTahun,
            $requestData['ta_id'],
            false,
            'id="cmb_tahun_anggaran"'
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
            'jenis',
            $arrJenisKegiatan,
            $requestData['jenis'],
            true,
            'id="cmb_jenis_kegiatan"'
         ),
         Messenger::CurrentRequest
      );

      # Combobox
      Messenger::Instance()->SendToComponent(
         'combobox',
         'Combobox',
         'view',
         'html',
         'prioritas',
         array(
            'prioritas',
            $arrPrioritas,
            $requestData['prioritas'],
            true,
            'id="cmb_prioritas"'
         ),
         Messenger::CurrentRequest
      );

      # Combobox
      Messenger::Instance()->SendToComponent(
         'combobox',
         'Combobox',
         'view',
         'html',
         'bulan',
         array(
            'bulan',
            $months,
            $requestData['bulan'],
            true,
            'id="cmb_bulan"'
         ),
         Messenger::CurrentRequest
      );

      $return['months']      = $months;
      $return['unit_kerja']      = $unitKerja;
      $return['message']         = $message;
      $return['style']           = $style;
      $return['request_data']    = $requestData;
      $return['data_list']       = $mObj->ChangeKeyName($dataList);
      $return['start']           = $offset+1;
      $return['query_string']    = $query_string;
      
      return $return;
   }

   function ParseTemplate($data = null)
   {
       $months = $data['months'];
      $unitKerja        = $data['unit_kerja'];
      $queryString      = $data['query_string'];
      $requestData      = $data['request_data'];
      $message          = $data['message'];
      $style            = $data['style'];
      $dataList         = $data['data_list'];
      $start            = $data['start'];
      $url_search       = Dispatcher::Instance()->GetUrl(
         'rencana_kinerja_tahunan_kegiatan',
         'RencanaKinerjaTahunan',
         'view',
         'html'
      );
      $url_popup_unit   = Dispatcher::Instance()->GetUrl(
         'rencana_kinerja_tahunan_kegiatan',
         'PopupUnitKerja',
         'view',
         'html'
      );
      $url_program      = Dispatcher::Instance()->GetUrl(
         'rencana_kinerja_tahunan_kegiatan',
         'PopupProgram',
         'view',
         'html'
      );
      $url_add          = Dispatcher::Instance()->GetUrl(
         'rencana_kinerja_tahunan_kegiatan',
         'InputKinerjaTahunan',
         'view',
         'html'
      ).'&'.$queryString;

      $urlEdit          = Dispatcher::Instance()->GetUrl(
         'rencana_kinerja_tahunan_kegiatan',
         'EditKinerjaTahunan',
         'view',
         'html'
      ).'&'.$queryString;

      $label            = "Rencana Kinerja Tahunan";
      $urlDelete        = Dispatcher::Instance()->GetUrl(
         'rencana_kinerja_tahunan_kegiatan',
         'DeleteData',
         'do',
         'html'
      ).'&'.$queryString;
      $urlReturn        = Dispatcher::Instance()->GetUrl(
         'rencana_kinerja_tahunan_kegiatan',
         'RencanaKinerjaTahunan',
         'view',
         'html'
      ).'&search=1&'.$queryString;
      Messenger::Instance()->Send(
         'confirm',
         'confirmDelete',
         'do',
         'html',
         array(
            $label,
            $urlDelete,
            $urlReturn
         ),
         Messenger::NextRequest
      );
      $this->mrTemplate->AddVar('content', 'URL_DELETE', Dispatcher::Instance()->GetUrl(
         'confirm',
         'confirmDelete',
         'do',
         'html'
      ));
      $this->mrTemplate->AddVar('content', 'CONTROL_LABEL', $label);
      $this->mrTemplate->AddVar('content', 'CONTROL_ACTION', $urlDelete);
      $this->mrTemplate->AddVar('content', 'CONTROL_RETURN', $urlReturn);

      $this->mrTemplate->AddVar('content','URL_SEARCH',$url_search);
      $this->mrTemplate->AddVar('content','URL_ADD',$url_add);
      $this->mrTemplate->AddVar('content','URL_POPUP_PROGRAM',$url_program);
      if($message && $message !== NULL) {
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $message);
         $this->mrTemplate->AddVar('warning_box', 'CLASS', $style);
      }
      // DATA UNIT
      $this->mrTemplate->AddVar('data_unit', 'LEVEL', strtoupper($unitKerja['status']));
      $this->mrTemplate->AddVar('data_unit', 'UNIT_ID', $requestData['unit_id']);
      $this->mrTemplate->AddVar('data_unit', 'UNIT_NAMA', $requestData['unit_nama']);
      $this->mrTemplate->AddVar('data_unit', 'URL_POPUP_UNIT', $url_popup_unit);
      $this->mrTemplate->AddVars('content', $requestData);

      if(empty($dataList)){
         $this->mrTemplate->AddVar('data_list', 'DATA_EMPTY', 'YES');
      }else{
         $this->mrTemplate->AddVar('data_list', 'DATA_EMPTY', 'NO');
         $dataGrid      = array();
         $program       = '';
         $kegiatan      = '';
         $subkegiatan   = '';
         $index         = 0;
         $rkt           = array(); // untuk menyimpan nominal rkat
         for ($i=0; $i < count($dataList);) {
            if((int)$program === (int)$dataList[$i]['program_id'] && (int)$kegiatan === (int)$dataList[$i]['kegiatan_id']){
               $programKodeSistem      = $program.'.0.0';
               $kegiatanKodeSistem     = $program.'.'.$kegiatan.'.0';
               $kodeSistem             = $program.'.'.$kegiatan.'.'.$dataList[$i]['id'];;

               $rkt[$programKodeSistem]['nominal']    += $dataList[$i]['nominal'];
               $rkt[$kegiatanKodeSistem]['nominal']   += $dataList[$i]['nominal'];
               $dataGrid[$index]['id']          = $dataList[$i]['id'];
               $dataGrid[$index]['parent_id']   = $dataList[$i]['keg_id'];
               $dataGrid[$index]['program_id']  = $dataList[$i]['program_id'];
               $dataGrid[$index]['kegiatan_id'] = $dataList[$i]['kegiatan_id'];
               $dataGrid[$index]['kode']        = $dataList[$i]['sub_kegiatan_kode'];
               $dataGrid[$index]['nama']        = $dataList[$i]['sub_kegiatan_nama'];
               $dataGrid[$index]['type']        = 'SUB_KEGIATAN';
               $dataGrid[$index]['nomor']       = $start;
               $dataGrid[$index]['kode_sistem'] = $kodeSistem;
               $dataGrid[$index]['deskripsi']   = $dataList[$i]['deskripsi'];
               $dataGrid[$index]['nominal']     = $dataList[$i]['nominal'];
               $dataGrid[$index]['bulan']       = $months[((int) $dataList[$i]['bulan'] - 1)]['name'];
               $dataGrid[$index]['ta_id']       = $dataList[$i]['ta_id'];
               $dataGrid[$index]['ta_nama']     = $dataList[$i]['ta_nama'];
               $dataGrid[$index]['unit_id']     = $dataList[$i]['unit_id'];
               $dataGrid[$index]['unit_kode']   = $dataList[$i]['unit_kode'];
               $dataGrid[$index]['unit_nama']   = $dataList[$i]['unit_nama'];
               $dataGrid[$index]['jenis_kegiatan'] = $dataList[$i]['jenis_kegiatan_nama'];
               $dataGrid[$index]['prioritas']      = strtoupper($dataList[$i]['prioritas']);
               $dataGrid[$index]['status_approve'] = strtoupper($dataList[$i]['approval']);
               $dataGrid[$index]['class_name']     = ($start % 2 <> 0) ? 'table-common-even' : '';
               $dataGrid[$index]['rkat']           = $dataList[$i]['rkat'];
               $dataGrid[$index]['ta_aktif']       = $dataList[$i]['ta_aktif'];
               $dataGrid[$index]['ta_open']        = $dataList[$i]['ta_open'];
               $i++;
               $start++;
            }elseif((int)$program === (int)$dataList[$i]['program_id'] && (int)$kegiatan !== (int)$dataList[$i]['kegiatan_id']){
               $kegiatan      = (int)$dataList[$i]['kegiatan_id'];
               $kodeSistem    = $program.'.'.$kegiatan.'.0';
               $rkt[$kodeSistem]['nominal']     = 0;
               $dataGrid[$index]['kode']        = $dataList[$i]['kegiatan_kode'];
               $dataGrid[$index]['nama']        = $dataList[$i]['kegiatan_nama'];
               $dataGrid[$index]['type']        = 'KEGIATAN';
               $dataGrid[$index]['kode_sistem'] = $kodeSistem;
               $dataGrid[$index]['class_name']  = 'table-common-even2';
               $dataGrid[$index]['row_style']   = 'font-weight: bold; font-style: italic;';
            }else{
               $program       = (int)$dataList[$i]['program_id'];
               $kodeSistem    = $program.'.0.0';

               $rkt[$kodeSistem]['nominal']     = 0;
               $dataGrid[$index]['kode']        = $dataList[$i]['program_kode'];
               $dataGrid[$index]['nama']        = $dataList[$i]['program_nama'];
               $dataGrid[$index]['type']        = 'PROGRAM';
               $dataGrid[$index]['kode_sistem'] = $kodeSistem;
               $dataGrid[$index]['class_name']  = 'table-common-even1';
               $dataGrid[$index]['row_style']   = 'font-weight: bold;';
            }

            $index++;
         }

         foreach ($dataGrid as $grid) {
            $this->mrTemplate->clearTemplate('content_deskripsi');
            $this->mrTemplate->clearTemplate('content_checkbox');
            $this->mrTemplate->clearTemplate('content_approval');
            switch (strtoupper($grid['type'])) {
               case 'PROGRAM':
                  $this->mrTemplate->SetAttribute('content_deskripsi', 'visibility', 'hidden');
                  $this->mrTemplate->AddVar('content_checkbox', 'LEVEL', 'PROGRAM');
                  $this->mrTemplate->AddVar('content_links', 'LEVEL', 'PROGRAM');
                  if($grid['nominal'] < 0){
                     $grid['nominal']  = '('.number_format(abs($rkt[$grid['kode_sistem']]['nominal']), 0, ',','.').')';
                  }else{
                     $grid['nominal']  = number_format($rkt[$grid['kode_sistem']]['nominal'], 0, ',','.');

                  }
                  break;
               case 'KEGIATAN':
                  $this->mrTemplate->SetAttribute('content_deskripsi', 'visibility', 'hidden');
                  $this->mrTemplate->AddVar('content_checkbox', 'LEVEL', 'KEGIATAN');
                  $this->mrTemplate->AddVar('content_links', 'LEVEL', 'KEGIATAN');
                  if($grid['nominal'] < 0){
                     $grid['nominal']  = '('.number_format(abs($rkt[$grid['kode_sistem']]['nominal']), 0, ',','.').')';
                  }else{
                     $grid['nominal']  = number_format($rkt[$grid['kode_sistem']]['nominal'], 0, ',','.');

                  }
                  break;
               case 'SUB_KEGIATAN':
                  $this->mrTemplate->SetAttribute('content_deskripsi', 'visibility', 'visible');
                  switch (strtoupper($grid['status_approve'])) {
                     case 'YA':
                        $this->mrTemplate->AddVar('content_links', 'LEVEL', 'LINK_DISABLED');
                        $this->mrTemplate->AddVar('content_checkbox', 'LEVEL', 'DISABLED');
                        break;
                     case 'BELUM':
                        $this->mrTemplate->AddVar('content_links', 'LEVEL', 'DATA_EDIT');
                        $this->mrTemplate->AddVar('content_checkbox', 'LEVEL', 'DATA');
                        $grid['class_name']     = 'table-yellow';
                        $this->mrTemplate->AddVar('content_links', 'URL_EDIT', $urlEdit.'&data_id='.Dispatcher::Instance()->Encrypt($grid['id']));
                        break;
                     case 'TIDAK':
                        $this->mrTemplate->AddVar('content_links', 'LEVEL', 'DATA_EDIT');
                        $this->mrTemplate->AddVar('content_checkbox', 'LEVEL', 'DISABLED');
                        $this->mrTemplate->AddVar('content_links', 'URL_EDIT', $urlEdit.'&data_id='.Dispatcher::Instance()->Encrypt($grid['id']));
                        break;
                     default:
                        $this->mrTemplate->AddVar('content_links', 'LEVEL', 'LINK_DISABLED');
                        $this->mrTemplate->AddVar('content_checkbox', 'LEVEL', 'DISABLED');
                        break;
                  }
                  // JIKA DATA RKT SUDAH SAMPAI PADA PROSES RKAT DISABLE SEMUA LINK DAN CHECKBOX UNTUK HAPUS DATA
                  if(strtoupper($grid['rkat']) == 'YES' OR ($grid['ta_aktif'] == 'T' AND $grid['ta_open'] == 'T')){
                      $this->mrTemplate->AddVar('content_checkbox', 'LEVEL', 'DISABLED');
                  }
                  
                  if(($grid['ta_aktif'] == 'T' AND $grid['ta_open'] == 'T')){
                     $this->mrTemplate->AddVar('content_links', 'LEVEL', 'LINK_DISABLED');
                     
                  }
                  if($grid['nominal'] < 0){
                     $grid['nominal']  = '('.number_format(abs($grid['nominal']), 0, ',','.').')';
                  }else{
                     $grid['nominal']  = number_format($grid['nominal'], 0, ',','.');
                  }
                  break;
               default:
                  $this->mrTemplate->SetAttribute('content_deskripsi', 'visibility', 'hidden');
                  $this->mrTemplate->SetAttribute('content_checkbox', 'visibility', 'hidden');
                  $grid['nominal']     = 0;
                  break;
            }
            $this->mrTemplate->AddVar('content_approval', 'STATUS', strtoupper($grid['status_approve']));
            $this->mrTemplate->AddVar('content_deskripsi', 'DESKRIPSI',$grid['deskripsi']);
            $this->mrTemplate->AddVars('content_checkbox', $grid);
            $this->mrTemplate->AddVars('data_grid', $grid);
            $this->mrTemplate->parseTemplate('data_grid', 'a');
         }
      }
   }
}
?>