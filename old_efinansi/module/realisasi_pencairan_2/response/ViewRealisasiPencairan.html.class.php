<?php
require_once GTFWConfiguration::GetValue('application','docroot').
'module/'.Dispatcher::Instance()->mModule.'/business/RealisasiPencairan.class.php';
require_once GTFWConfiguration::GetValue('application','docroot').
'module/user_unit_kerja/business/UserUnitKerja.class.php';

class ViewRealisasiPencairan extends HtmlResponse
{
   function TemplateModule(){
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').'module/realisasi_pencairan_2/template');
      $this->SetTemplateFile('view_realisasi_pencairan.html');
   }

   function ProcessRequest() {
      $userid           = Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
      $messenger        = Messenger::Instance()->Receive(__FILE__);
      $mObj             = new RealisasiPencairan();
      $mUnitObj         = new UserUnitKerja();
      $dataUnit         = $mUnitObj->GetUnitKerjaRefUser($userid);
      $arrPeriodeTahun  = $mObj->GetPeriodeTahun();
      $periodeTahun     = $mObj->GetPeriodeTahun(array('active' => true));
      $arrJenisKegiatan = $mObj->GetDataJenisKegiatan();
      $months              = $mObj->indonesianMonth;
      $requestData      = array();

      if(isset($mObj->_POST['btnTampilkan'])){
         $requestData['ta_id']            = $mObj->_POST['data']['ta_id'];
         $requestData['kode']             = $mObj->_POST['data']['kode'];
         $requestData['nama']             = $mObj->_POST['data']['nama'];
         $requestData['unit_id']          = $mObj->_POST['data']['unit_id'];
         $requestData['unit_nama']        = $mObj->_POST['data']['unit_nama'];
         $requestData['program_id']       = $mObj->_POST['data']['program_id'];
         $requestData['jenis_kegiatan']   = $mObj->_POST['data']['jenis_kegiatan'];
         $requestData['bulan']            = $mObj->_POST['data']['bulan'];
         $requestData['no_pengajuan']     = $mObj->_POST['data']['no_pengajuan'];
      }elseif(isset($mObj->_GET['search'])){
         $requestData['ta_id']            = Dispatcher::Instance()->Decrypt($mObj->_GET['ta_id']);
         $requestData['kode']             = Dispatcher::Instance()->Decrypt($mObj->_GET['kode']);
         $requestData['nama']             = Dispatcher::Instance()->Decrypt($mObj->_GET['nama']);
         $requestData['unit_id']          = Dispatcher::Instance()->Decrypt($mObj->_GET['unit_id']);
         $requestData['unit_nama']        = Dispatcher::Instance()->Decrypt($mObj->_GET['unit_nama']);
         $requestData['program_id']       = Dispatcher::Instance()->Decrypt($mObj->_GET['program_id']);
         $requestData['jenis_kegiatan']   = Dispatcher::Instance()->Decrypt($mObj->_GET['jenis_kegiatan']);
         $requestData['bulan']            = Dispatcher::Instance()->Decrypt($mObj->_GET['bulan']);
         $requestData['no_pengajuan']     = Dispatcher::Instance()->Decrypt($mObj->_GET['no_pengajuan']);
      }else{
         $requestData['ta_id']            = $periodeTahun[0]['id'];
         $requestData['kode']             = '';
         $requestData['nama']             = '';
         $requestData['unit_id']          = $dataUnit['id'];
         $requestData['unit_nama']        = $dataUnit['nama'];
         $requestData['program_id']       = '';
         $requestData['jenis_kegiatan']   = '';
         $requestData['bulan']   = '';
         $requestData['no_pengajuan']     = '';
      }

      $arrProgram    = $mObj->GetDataProgram($requestData['ta_id']);

      foreach ($arrPeriodeTahun as $ta) {
         if((int)$ta['id'] === (int)$requestData['ta_id']){
            $requestData['ta_nama']    = $ta['name'];
            $requestData['active']     = $ta['active'];
            $requestData['open']       = $ta['open'];
         }
      }

      foreach ($arrProgram as $prog) {
         if((int)$prog['id'] === (int)$requestData['program_id']){
            $requestData['program_nama']  = $prog['name'];
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


      Messenger::Instance()->SendToComponent(
         'combobox',
         'Combobox',
         'view',
         'html',
         'periode_tahun',
         array(
            'data[ta_id]',
            $arrPeriodeTahun,
            $requestData['ta_id'],
            false,
            ' style="width:150px;" onchange="getProgram(this.value)" id="cmb_tahun_anggaran"'
         ), Messenger::CurrentRequest);

      Messenger::Instance()->SendToComponent(
         'combobox',
         'Combobox',
         'view',
         'html',
         'program',
         array(
            'data[program_id]',
            $arrProgram,
            $requestData['program_id'],
            true,
            'id="cmb_program_id" style="width:150px;"'
         ), Messenger::CurrentRequest);

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
            ' style="width:150px;" id="cmb_jenis_kegiatan"'
         ), Messenger::CurrentRequest);

      # Combobox
      Messenger::Instance()->SendToComponent(
         'combobox',
         'Combobox',
         'view',
         'html',
         'bulan',
         array(
            'data[bulan]',
            $months,
            $requestData['bulan'],
            true,
            'id="cmb_bulan"'
         ),
         Messenger::CurrentRequest
      );
      //start menghandle pesan yang diparsing
      if(isset($messenger)){
         $messengerMessage       = $messenger[0][1];
         $messengerStyle         = $messenger[0][2];
      }
      // end handle

      $return['data_unit']       = $mObj->ChangeKeyName($dataUnit);
      $return['periode_tahun']   = $arrPeriodeTahun;
      $return['request_data']    = $requestData;
      $return['query_string']    = $queryString;
      $return['data_list']       = $mObj->ChangeKeyName($dataList);
      $return['start']           = $offset+1;
      $return['tahun_anggaran']  = $periodeTahun;
      $return['message']         = $messengerMessage;
      $return['style']           = $messengerStyle;



      if($checkTA['is_aktif'] == 'T' AND !($tmp['msg'])){
         $ta_aktif       = $mObj->GetTahunAnggaranAktif();
         $tmp['msg']['message']    .= 'Periode Tahun '.$checkTA['nama'].' berada pada status tidak aktif';
         $tmp['msg']['message']    .= '<br />Anda tidak bisa meng-edit data bersangkutan';
         $tmp['msg']['message']    .= '<br />Periode tahun yang aktif sekarang adalah ';
         $tmp['msg']['message']    .= $ta_aktif['name'];
         $tmp['msg']['action']      = 'err';
         $return['msg']=$tmp['msg'];
      }

      $return['check_ta']     = $checkTA;
      return $return;
   }

   function ParseTemplate($data = NULL) {
      $mObj          = new RealisasiPencairan();
      $page          = 1;
      if(isset($_GET['page'])){
         $page       = (string) $_GET['page']->StripHtmlTags()->SqlString()->Raw();
      }
      $dataUnit      = $data['data_unit'];
      $requestData   = $data['request_data'];
      $periodeTahun  = $data['periode_tahun'];
      $queryString   = $data['query_string'].'&page='.$page;
      $dataList      = $data['data_list'];
      $start         = $data['start'];
      $tahunAngaran  = $data['tahun_anggaran'];
      $message       = $data['message'];
      $style         = $data['style'];

      $parseUrl      = parse_url($queryString);
      $urlExploded   = explode('&', $parseUrl['path']);

      $urlIndex      = 0;
      foreach ($urlExploded as $url) {
         list($urlKey[$urlIndex], $urlValue[$urlIndex]) = explode('=', $url);
         $patern     = '/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})/';
         $patern1    = '/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})/';
         if((preg_match($patern, $urlValue[$urlIndex]) || preg_match($patern1, $urlValue[$urlIndex])) && strtotime($urlValue[$urlIndex]) !== false){
            $urlValue[$urlIndex]    = date('Y/m/d', strtotime($urlValue[$urlIndex]));
         }
         $urlIndex   += 1;
      }
      unset($urlIndex);
      $keyUrl     = implode('|', $urlKey);
      $valueUrl   = implode('|', $urlValue);

      $urlSearch     = Dispatcher::Instance()->GetUrl(
         'realisasi_pencairan_2',
         'realisasiPencairan',
         'view',
         'html'
      );
      $urlPopupUnit  = Dispatcher::Instance()->GetUrl(
         'realisasi_pencairan_2',
         'PopupUnitkerja',
         'view',
         'html'
      );

      $urlProgram    = Dispatcher::Instance()->GetUrl(
         'realisasi_pencairan_2',
         'Program',
         'view',
         'json'
      );
      $urlAdd        = Dispatcher::Instance()->GetUrl(
         'realisasi_pencairan_2',
         'inputRealisasiPencairan',
         'view',
         'html'
      ).'&'.$queryString;

      $urlDetail     = Dispatcher::Instance()->GetUrl(
         'realisasi_pencairan_2',
         'detailRealisasiPencairan',
         'popup',
         'html'
      ).'&'.$queryString;
      $urlCetak      = Dispatcher::Instance()->GetUrl(
         'realisasi_pencairan_2',
         'realisasiPencairan',
         'print',
         'html'
      ).'&'.$queryString;

      $urlExportXls  = Dispatcher::Instance()->GetUrl(
         'realisasi_pencairan_2',
         'realisasiPencairan',
         'view',
         'xlsx'
      ).'&'.$queryString;

      $urlCetakSpp   = Dispatcher::Instance()->GetUrl(
         'realisasi_pencairan_2',
         'CetakSpp',
         'view',
         'html'
      ).'&'.$queryString;
      $urlAddSpp     = Dispatcher::Instance()->GetUrl(
         'realisasi_pencairan_2',
         'AddSpp',
         'view',
         'html'
      ).'&'.$queryString;

      $urlEditSpp    = Dispatcher::Instance()->GetUrl(
         'realisasi_pencairan_2',
         'EditSpp',
         'view',
         'html'
      ).'&'.$queryString;

      $urlExcelSpp   = Dispatcher::Instance()->GetUrl(
         'realisasi_pencairan_2',
         'ExcelSpp',
         'view',
         'xlsx'
      ).'&'.$queryString;

      $this->mrTemplate->AddVars('content', $requestData);
      $this->mrTemplate->AddVar('data_unit', 'STATUS', strtoupper($dataUnit['status']));
      $this->mrTemplate->AddVar('data_unit', 'UNIT_ID', $requestData['unit_id']);
      $this->mrTemplate->AddVar('data_unit', 'UNIT_NAMA', $requestData['unit_nama']);
      $this->mrTemplate->AddVar('data_unit', 'POPUP_UNIT_KERJA', $urlPopupUnit);
      $this->mrTemplate->AddVar('content', 'URL_SEARCH', $urlSearch);
      $this->mrTemplate->AddVar('content', 'URL_PROGRAM', $urlProgram);
      $this->mrTemplate->AddVar('content', 'POPUP_DETAIL', $urlDetail);
      $this->mrTemplate->AddVar('content', 'URL_CETAK', $urlCetak);
      $this->mrTemplate->AddVar('content', 'URL_CETAK_SPP', $urlCetakSpp);
      // set visible link toolbar
      $this->mrTemplate->AddVar('link_toolbar', 'VISIBLE', 'YES');
      $this->mrTemplate->AddVar('link_toolbar', 'URL_ADD',  $urlAdd);

      // write message from messenger
      if($message){
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $message);
         $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $style);
      }

      // overwrite messenger
      if($requestData['active'] == 'T'){
         $this->mrTemplate->clearTemplate('link_toolbar');
         $message    = 'Periode Tahun berada pada status tidak aktif <br />Anda tidak bisa meng-edit data bersangkutan';
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $message);
         $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', 'notebox-warning');
      }

      if (empty($dataList)) {
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
      } else {
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');

         $program          = '';
         $kegiatan         = '';
         $index            = 0;
         $dataRealisasi    = array();
         $dataGrid         = array();

         for ($i=0; $i < count($dataList);) {
            if((int)$dataList[$i]['program_id'] === (int)$program && (int)$dataList[$i]['kegiatan_id'] === (int)$kegiatan){
               $kodeSistemProgram            = $program;
               $kodeSistemKegiatan           = $program.'.'.$kegiatan;
               $dataRealisasi[$kodeSistemProgram]['nominal_usulan']  += $dataList[$i]['nominal_usulan'];
               $dataRealisasi[$kodeSistemProgram]['nominal_setuju']  += $dataList[$i]['nominal_setuju'];

               $dataRealisasi[$kodeSistemKegiatan]['nominal_usulan'] += $dataList[$i]['nominal_usulan'];
               $dataRealisasi[$kodeSistemKegiatan]['nominal_setuju'] += $dataList[$i]['nominal_setuju'];

               $dataGrid[$index]['nomor']    = $start;
               $dataGrid[$index]['id']       = $dataList[$i]['id'];
               $dataGrid[$index]['tanggal']  = $dataList[$i]['tanggal'];
               $dataGrid[$index]['kode']           = $dataList[$i]['sub_kegiatan_kode'];
               $dataGrid[$index]['nama']           = $dataList[$i]['sub_kegiatan_nama'];
               $dataGrid[$index]['nominal_usulan'] = $dataList[$i]['nominal_usulan'];
               $dataGrid[$index]['nominal_setuju'] = $dataList[$i]['nominal_setuju'];
               $dataGrid[$index]['status']         = strtoupper($dataList[$i]['status']);
               if(($dataList[$i]['status'] != 'BELUM')){
                    $dataGrid[$index]['user_approval']  = $dataList[$i]['user_approval'];     
               } else {
                    $dataGrid[$index]['user_approval']  = '';
               }
              
               $dataGrid[$index]['spp']            = $dataList[$i]['spp'];
               $dataGrid[$index]['spp_id']         = $dataList[$i]['spp_id'];
               $dataGrid[$index]['tipe']           = 'sub_kegiatan';
               $dataGrid[$index]['class_name']     = $start % 2 <> 0 ? 'table-common-even' : '';
               $dataGrid[$index]['row_style']      = '';
               $i++;
               $start++;
            }elseif((int)$dataList[$i]['program_id'] === (int)$program && (int)$dataList[$i]['kegiatan_id'] !== (int)$kegiatan){
               $kegiatan         = (int)$dataList[$i]['kegiatan_id'];
               $kodeSistem       = $program.'.'.$kegiatan;
               $dataRealisasi[$kodeSistem]['nominal_usulan']   = 0;
               $dataRealisasi[$kodeSistem]['nominal_setuju']   = 0;

               $dataGrid[$index]['id']       = $dataList[$i]['kegiatan_id'];
               $dataGrid[$index]['kode']     = $dataList[$i]['kegiatan_kode'];
               $dataGrid[$index]['nama']     = $dataList[$i]['kegiatan_nama'];
               $dataGrid[$index]['kode_sistem'] = $kodeSistem;
               $dataGrid[$index]['tipe']        = 'kegiatan';
               $dataGrid[$index]['class_name']  = 'table-common-even2';
               $dataGrid[$index]['row_style']   = '';
            }else{
               $program          = (int)$dataList[$i]['program_id'];
               $kodeSistem       = $program;
               $dataRealisasi[$kodeSistem]['nominal_usulan']   = 0;
               $dataRealisasi[$kodeSistem]['nominal_setuju']   = 0;

               $dataGrid[$index]['id']       = $dataList[$i]['program_id'];
               $dataGrid[$index]['kode']     = $dataList[$i]['program_kode'];
               $dataGrid[$index]['nama']     = $dataList[$i]['program_nama'];
               $dataGrid[$index]['kode_sistem'] = $kodeSistem;
               $dataGrid[$index]['tipe']        = 'program';
               $dataGrid[$index]['class_name']  = 'table-common-even1';
               $dataGrid[$index]['row_style']   = 'font-weight: bold;';
            }
            $index++;
         }

         foreach ($dataGrid as $list) {
            $this->mrTemplate->clearTemplate('link_status');
            $this->mrTemplate->clearTemplate('link_edit');
            $this->mrTemplate->AddVar('link_status', 'LEVEL', strtoupper($list['tipe']));
            switch (strtoupper($list['tipe'])) {
               case 'PROGRAM':
                  $list['nominal_usulan']    = number_format($dataRealisasi[$list['kode_sistem']]['nominal_usulan'], 0, ',','.');
                  $list['nominal_setuju']    = number_format($dataRealisasi[$list['kode_sistem']]['nominal_setuju'], 0, ',','.');
                  break;
               case 'KEGIATAN':
                  $list['nominal_usulan']    = number_format($dataRealisasi[$list['kode_sistem']]['nominal_usulan'], 0, ',','.');
                  $list['nominal_setuju']    = number_format($dataRealisasi[$list['kode_sistem']]['nominal_setuju'], 0, ',','.');
                  break;
               case 'SUB_KEGIATAN':
                  $list['nominal_usulan']    = number_format($list['nominal_usulan'], 0, ',','.');
                  $list['nominal_setuju']    = number_format($list['nominal_setuju'], 0, ',','.');

                  // links
                  $this->mrTemplate->AddVar('link_status', 'URL_DETIL', $urlDetail.'&grp='.Dispatcher::Instance()->Encrypt($list['id']));
                  $this->mrTemplate->AddVar('link_status', 'URL_CETAK', $urlCetak.'&id='.Dispatcher::Instance()->Encrypt($list['id']));
                  $this->mrTemplate->AddVar('link_status', 'URL_EXPORT_EXCEL', $urlExportXls.'&id='.Dispatcher::Instance()->Encrypt($list['id']));

                  $this->mrTemplate->AddVar('link_edit', 'STATUS', strtoupper($list['status']));

                  // generate url
                  $urlAccept  = 'realisasi_pencairan_2|deleteRealisasiPencairan|do|html-search|'.$keyUrl.'-1|'.$valueUrl;
                  $urlReturn  = 'realisasi_pencairan_2|realisasiPencairan|view|html-search|'.$keyUrl.'-1|'.$valueUrl;
                  $label      = GTFWConfiguration::GetValue('language', 'rencana_realisasi_pencairan');
                  $message    = 'Penghapusan Data ini akan menghapus Data '.$label.' secara permanen.';
                  $dataName   = 'Sub Kegiatan : '.$list['nama'];
                  $list['url_delete']     = Dispatcher::Instance()->GetUrl(
                     'confirm',
                     'confirmDelete',
                     'do',
                     'html'
                  ).'&urlDelete='. $urlAccept
                  .'&urlReturn='.$urlReturn
                  .'&id='.Dispatcher::Instance()->Encrypt($list['id'])
                  .'&label='.$label
                  .'&dataName='.$dataName
                  .'&message='.$message;

                  $this->mrTemplate->AddVar('link_edit', 'URL_EDIT', $urlAdd.'&grp='.Dispatcher::Instance()->Encrypt($list['id']));
                  $this->mrTemplate->AddVar('link_edit', 'URL_DELETE', $list['url_delete']);

                  // end url

                  if((int)$list['spp'] <> 0 AND $list['spp_id'] != NULL){
                     $this->mrTemplate->clearTemplate('link_edit');
                     $this->mrTemplate->AddVar('link_spp', 'HAS_SPP', 'YES');
                     $this->mrTemplate->AddVar('link_spp', 'URL_EDIT', $urlEditSpp.'&id='.Dispatcher::Instance()->Encrypt($list['id']).'&spp_id='.Dispatcher::Instance()->Encrypt($list['spp_id']));
                     $this->mrTemplate->AddVar('link_spp', 'URL_CETAK', $urlCetakSpp.'&id='.Dispatcher::Instance()->Encrypt($list['id']).'&spp_id='.Dispatcher::Instance()->Encrypt($list['spp_id']));
                     $this->mrTemplate->AddVar('link_spp', 'URL_EXPORT', $urlExcelSpp.'&id='.Dispatcher::Instance()->Encrypt($list['id']).'&spp_id='.Dispatcher::Instance()->Encrypt($list['spp_id']));
                  }else{
                     $this->mrTemplate->AddVar('link_spp', 'HAS_SPP', 'NO');
                     $this->mrTemplate->AddVar('link_spp', 'URL_ADD', $urlAddSpp.'&id='.Dispatcher::Instance()->Encrypt($list['id']));
                  }
                  break;
               default:
                  $list['nominal_usulan']    = number_format($list['nominal_usulan'], 0, ',','.');
                  $list['nominal_setuju']    = number_format($list['nominal_setuju'], 0, ',','.');
                  break;
            }
            $this->mrTemplate->clearTemplate('link_spp');
            if(strtoupper($requestData['active']) !== 'Y'){
               // $this->mrTemplate->clearTemplate('link_status');
               $this->mrTemplate->clearTemplate('link_edit');
               $this->mrTemplate->clearTemplate('link_spp');
            }

            $this->mrTemplate->AddVars('data_list', $list);
            $this->mrTemplate->parseTemplate('data_list', 'a');
         }
      }
   }
}
?>