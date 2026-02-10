<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/pembalik_approval_pencairan/business/AppPembalikApprovalPencairan.class.php';
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/user_unit_kerja/business/UserUnitKerja.class.php';

class ViewPembalikApprovalPencairan extends HtmlResponse {

   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
         'module/pembalik_approval_pencairan/template');
      $this->SetTemplateFile('view_pembalik_approval_pencairan.html');
   }

   function ProcessRequest() {
      $msg                 = Messenger::Instance()->Receive(__FILE__);
      $Obj                 = new AppPembalikApprovalPencairan();
      $userUnitKerjaObj    = new UserUnitKerja();
      $userId              = $Obj->getUserId();
      $unitKerja           = $userUnitKerjaObj->GetUnitKerjaRefUser($userId);
      $arrPeriodeTahun     = $Obj->getPeriodeTahun();
      $periodeTahun        = $Obj->getPeriodeTahun(array('active' => true));
      $arrJenisKegiatan    = $Obj->GetComboJenisKegiatan();
      $arrProgram          = (array)$Obj->GetDataProgram();
      $requestData         = array();
      $queryString         = '';   
      $months           = $Obj->indonesianMonth;

     if(isset($Obj->_POST['btncari'])){
         $requestData['kode']             = $Obj->_POST['kode'];
         $requestData['nama']             = $Obj->_POST['nama'];
         $requestData['ta_id']            = $Obj->_POST['tahun_anggaran'];
         $requestData['unit_id']          = $Obj->_POST['unitkerja'];
         $requestData['unit_nama']        = $Obj->_POST['unitkerja_label'];
         $requestData['program_id']       = $Obj->_POST['program'];
         $requestData['nomor_pengajuan']  = $Obj->_POST['nomor_pengajuan'];
         $requestData['jenis_kegiatan']   = $Obj->_POST['jenis_kegiatan'];
         $requestData['bulan']            = $Obj->_POST['bulan'];
         $requestData['bulan_anggaran']   = $Obj->_POST['bulan_anggaran'];
      }elseif(isset($Obj->_GET['search'])){
         $requestData['kode']             = Dispatcher::Instance()->Decrypt($Obj->_GET['kode']);
         $requestData['nama']             = Dispatcher::Instance()->Decrypt($Obj->_GET['nama']);
         $requestData['ta_id']            = Dispatcher::Instance()->Decrypt($Obj->_GET['ta_id']);
         $requestData['unit_id']          = Dispatcher::Instance()->Decrypt($Obj->_GET['unit_id']);
         $requestData['unit_nama']        = Dispatcher::Instance()->Decrypt($Obj->_GET['unit_nama']);
         $requestData['program_id']       = Dispatcher::Instance()->Decrypt($Obj->_GET['program_id']);
         $requestData['nomor_pengajuan']  = Dispatcher::Instance()->Decrypt($Obj->_GET['nomor_pengajuan']);
         $requestData['jenis_kegiatan']   = Dispatcher::Instance()->Decrypt($Obj->_GET['jenis_kegiatan']);
         $requestData['bulan']            = Dispatcher::Instance()->Decrypt($Obj->_GET['bulan']);
         $requestData['bulan_anggaran']   = Dispatcher::Instance()->Decrypt($Obj->_GET['bulan_anggaran']);
      }else{
         $requestData['kode']             = '';
         $requestData['nama']             = '';
         $requestData['ta_id']            = $periodeTahun[0]['id'];
         $requestData['unit_id']          = $unitKerja['id'];
         $requestData['unit_nama']        = $unitKerja['nama'];
         $requestData['program_id']       = '';
         $requestData['nomor_pengajuan']  = '';
         $requestData['jenis_kegiatan']   = '';
         $requestData['bulan']            = '';
         $requestData['bulan_anggaran']   = '';
      }

      foreach ($arrPeriodeTahun as $ta) {
         if((int)$ta['id'] === (int)$requestData['ta_id']){
            $requestData['ta_nama']    = $ta['name'];
         }
      }

      if(method_exists(Dispatcher::Instance(), 'getQueryString')){
         # @param array
         $queryString   = Dispatcher::instance()->getQueryString($requestData);
      }else{
         $query         = array();
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
      ).'&search='.Dispatcher::Instance()->Encrypt(1) .'&'.$queryString;

      $destination_id   = "subcontent-element";
      $dataList         = $Obj->getDataRealisasi($offset, $limit, (array)$requestData);
      $total_data       = $Obj->Count();

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

      /**
       * parsing message
       */

      if($msg){
         $message       = $msg[0][1];
         $style         = $msg[0][2];
      }
      /**
       * Combobox
       */
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
         ), Messenger::CurrentRequest
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
            array(),
            NULL,
            true,
            'style="width:200px;" id="cmb_program"'
         ),
         Messenger::CurrentRequest
      );

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
            ' style="width:200px;" id="jenis_kegiatan"'
         ), Messenger::CurrentRequest
      );

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

      # Combobox
      Messenger::Instance()->SendToComponent(
         'combobox',
         'Combobox',
         'view',
         'html',
         'bulan_anggaran',
         array(
            'bulan_anggaran',
            $months,
            $requestData['bulan_anggaran'],
            true,
            'id="cmb_bulan_anggaran"'
         ),
         Messenger::CurrentRequest
      );
      
      $return['months']          = $months ;
      $return['message']         = $message;
      $return['style']           = $style;
      $return['unit_kerja']      = $unitKerja;
      $return['program']['data'] = json_encode($arrProgram);
      $return['request_data']    = $requestData;
      $return['query_string']    = $queryString;
      $return['data_list']       = $dataList;
      $return['start']           = $offset+1;
      return $return;
   }

   function ParseTemplate($data = NULL) {
      $months        = $data['months'];
      $queryString   = $data['query_string'];
      $requestData   = $data['request_data'];
      $unitKerja     = $data['unit_kerja'];
      $program       = $data['program'];
      $message       = $data['message'];
      $style         = $data['style'];
      $dataList      = $data['data_list'];
      $start         = $data['start'];
      $urlSearch     = Dispatcher::Instance()->GetUrl(
         'pembalik_approval_pencairan',
         'pembalikApprovalPencairan',
         'view',
         'html'
      );

      $urlPopupUnit  =  Dispatcher::Instance()->GetUrl(
         'pembalik_approval_pencairan',
         'popupUnitkerja',
         'view',
         'html'
      );

      $urlDetail     = Dispatcher::Instance()->GetUrl(
         'pembalik_approval_pencairan',
         'detailPencairan',
         'view',
         'html'
      );

      $urlInputPembalikApproval  = Dispatcher::Instance()->GetUrl(
         'pembalik_approval_pencairan',
         'inputPembalikApprovalPencairan',
         'view',
         'html'
      ).'&'.$queryString;


      $this->mrTemplate->AddVar('content', 'URL_SEARCH', $urlSearch);
      $this->mrTemplate->AddVar('data_unit', 'LEVEL', strtoupper($unitKerja['status']));
      $this->mrTemplate->AddVar('data_unit', 'UNIT_ID', $requestData['unit_id']);
      $this->mrTemplate->AddVar('data_unit', 'UNITKERJA_LABEL', $requestData['unit_nama']);
      $this->mrTemplate->AddVar('data_unit', 'URL_POPUP_UNIT_KERJA', $urlPopupUnit);
      $this->mrTemplate->AddVars('content', $requestData);
      $this->mrTemplate->AddVars('content', $program, 'PROGRAM_');

      if($message AND !is_null($message)){
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $message);
         $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $style);
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
               $dataGrid[$index]['tanggal']  = date('Y-m-d', strtotime($dataList[$i]['tanggal']));
               $dataGrid[$index]['bulan_anggaran'] = $months[($dataList[$i]['bulan_anggaran'] - 1 )]['name'];
               $dataGrid[$index]['kode']           = $dataList[$i]['sub_kegiatan_kode'];
               $dataGrid[$index]['nama']           = $dataList[$i]['sub_kegiatan_nama'];
               $dataGrid[$index]['nominal_usulan'] = $dataList[$i]['nominal_usulan'];
               $dataGrid[$index]['nominal_setuju'] = $dataList[$i]['nominal_setuju'];
               $dataGrid[$index]['status']         = strtoupper($dataList[$i]['status']);
               $dataGrid[$index]['spp']            = $dataList[$i]['spp'];
               $dataGrid[$index]['spp_id']         = $dataList[$i]['spp_id'];
               $dataGrid[$index]['spm']            = $dataList[$i]['spm'];
               $dataGrid[$index]['spm_id']         = $dataList[$i]['spm_id'];
               $dataGrid[$index]['sppu']           = $dataList[$i]['sppu'];
               $dataGrid[$index]['status']         = strtoupper($dataList[$i]['status']);
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
            $this->mrTemplate->clearTemplate('content_status');
            $this->mrTemplate->SetAttribute('link_status', 'visibility', 'hidden');
            $this->mrTemplate->SetAttribute('link_unapproval', 'visibility', 'hidden');
            switch (strtoupper($list['tipe'])) {
               case 'PROGRAM':
                  $this->mrTemplate->clearTemplate('link_status');
                  $list['nominal_usulan']    = number_format($dataRealisasi[$list['kode_sistem']]['nominal_usulan'], 0, ',','.');
                  $list['nominal_setuju']    = number_format($dataRealisasi[$list['kode_sistem']]['nominal_setuju'], 0, ',','.');
                  break;
               case 'KEGIATAN':
                  $this->mrTemplate->clearTemplate('link_status');
                  $list['nominal_usulan']    = number_format($dataRealisasi[$list['kode_sistem']]['nominal_usulan'], 0, ',','.');
                  $list['nominal_setuju']    = number_format($dataRealisasi[$list['kode_sistem']]['nominal_setuju'], 0, ',','.');
                  break;
               case 'SUB_KEGIATAN':
                  $list['nominal_usulan']    = number_format($list['nominal_usulan'], 0, ',','.');
                  $list['nominal_setuju']    = number_format($list['nominal_setuju'], 0, ',','.');
                  $this->mrTemplate->SetAttribute('link_status', 'visibility', 'show');
                  $this->mrTemplate->AddVar('link_status', 'URL_DETAIL', $urlDetail.'&data_id='.Dispatcher::Instance()->Encrypt($list['id']));
                  switch ($list['status']) {
                     case 'YA':
                        $this->mrTemplate->AddVar('content_status', 'STATUS', 'YA');
                        if((int)$list['sppu'] === 0){
                           $this->mrTemplate->SetAttribute('link_unapproval', 'visibility', 'show');
                           $this->mrTemplate->AddVar('link_unapproval', 'URL_AKSI', $urlInputPembalikApproval.'&data_id='.Dispatcher::Instance()->Encrypt($list['id']));
                        }
                        break;
                     case 'BELUM':
                        $this->mrTemplate->AddVar('content_status', 'STATUS', 'BELUM');
                        break;
                     case 'TIDAK':
                        $this->mrTemplate->AddVar('content_status', 'STATUS', 'TIDAK');
                        break;
                     default:
                        $this->mrTemplate->clearTemplate('content_status');
                        $this->mrTemplate->clearTemplate('link_status');
                        break;
                  }
                  break;
            }
            $this->mrTemplate->AddVars('data_list', $list);
            $this->mrTemplate->parseTemplate('data_list', 'a');
         }
      }
   }
}
?>