<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/approval_pencairan/business/AppApprovalPencairan.class.php';
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/user_unit_kerja/business/UserUnitKerja.class.php';

class ViewApprovalPencairan extends HtmlResponse
{
   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
      'module/approval_pencairan/template');
      $this->SetTemplateFile('view_approval_pencairan.html');
   }

   function ProcessRequest() {
      $messenger        = Messenger::Instance()->Receive(__FILE__);
      $Obj              = new AppApprovalPencairan();
      $userUnitKerjaObj = new UserUnitKerja();
      $userId           = $Obj->getUserId();
      $unitKerja        = $userUnitKerjaObj->GetUnitKerjaRefUser($userId);
      $arrPeriodeTahun  = $Obj->getPeriodeTahun();
      $periodeTahun     = $Obj->getPeriodeTahun(array('active' => true));
      $arrJenisKegiatan = $Obj->GetComboJenisKegiatan();
      $arrProgram       = (array)$Obj->GetDataProgram();
      $requestData      = array();
      $queryString      = '';
      $message          = $style = $messengerData = NULL;      
      $months           = $Obj->indonesianMonth;
      
      if(isset($Obj->_POST['btncari'])){
         $requestData['ta_id']            = $Obj->_POST['tahun_anggaran'];
         $requestData['unit_id']          = $Obj->_POST['unit_id'];
         $requestData['unit_nama']        = $Obj->_POST['unit_nama'];
         $requestData['program_id']       = $Obj->_POST['program'];
         $requestData['nomor_pengajuan']  = $Obj->_POST['nomor_pengajuan'];
         $requestData['nama']             = $Obj->_POST['nama'];
         $requestData['kode']             = $Obj->_POST['kode'];
         $requestData['jenis_kegiatan']   = $Obj->_POST['jenis_kegiatan'];
         $requestData['bulan']            = $Obj->_POST['bulan'];
         $requestData['bulan_anggaran']   = $Obj->_POST['bulan_anggaran'];
      }elseif(isset($Obj->_GET['search'])){
         $requestData['ta_id']            = Dispatcher::Instance()->Decrypt($Obj->_GET['ta_id']);
         $requestData['unit_id']          = Dispatcher::Instance()->Decrypt($Obj->_GET['unit_id']);
         $requestData['unit_nama']        = Dispatcher::Instance()->Decrypt($Obj->_GET['unit_nama']);
         $requestData['program_id']       = Dispatcher::Instance()->Decrypt($Obj->_GET['program_id']);
         $requestData['nomor_pengajuan']  = Dispatcher::Instance()->Decrypt($Obj->_GET['nomor_pengajuan']);
         $requestData['nama']             = Dispatcher::Instance()->Decrypt($Obj->_GET['nama']);
         $requestData['kode']             = Dispatcher::Instance()->Decrypt($Obj->_GET['kode']);
         $requestData['jenis_kegiatan']   = Dispatcher::Instance()->Decrypt($Obj->_GET['jenis_kegiatan']);
         $requestData['bulan']            = Dispatcher::Instance()->Decrypt($Obj->_GET['bulan']);
         $requestData['bulan_anggaran']   = Dispatcher::Instance()->Decrypt($Obj->_GET['bulan_anggaran']);
      }else{
         $requestData['ta_id']            = $periodeTahun[0]['id'];
         $requestData['unit_id']          = $unitKerja['id'];
         $requestData['unit_nama']        = $unitKerja['nama'];
         $requestData['program_id']       = '';
         $requestData['nomor_pengajuan']  = '';
         $requestData['nama']             = '';
         $requestData['kode']             = '';
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
      ).'&search='.Dispatcher::Instance()->Encrypt(1) . '&' . $queryString;

      $destination_id   = "subcontent-element";
      $dataList         = $Obj->GetData($offset, $limit, (array)$requestData);
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

      if($messenger){
         $messengerData    = $messenger[0][0];
         $message          = $messenger[0][1];
         $style            = $messenger[0][2];
      }

      // send to combo box tahun anggaran
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
            ' style="width:200px;" id="cmb_program"'
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
      $return['unit_kerja']      = $unitKerja;
      $return['request_data']    = $requestData;
      $return['query_string']    = $queryString;
      $return['program']['data'] = json_encode($arrProgram);
      $return['data_list']       = $dataList;
      $return['start']           = $offset+1;
      $return['message']         = $message;
      $return['style']           = $style;
      return $return;
   }

   function ParseTemplate($data = NULL) {
      $months           = $data['months'];
      $unitKerja        = $data['unit_kerja'];
      $requestData      = $data['request_data'];
      $queryString      = $data['query_string'];
      $program          = $data['program'];
      $dataList         = $data['data_list'];
      $start            = $data['start'];
      $message          = $data['message'];
      $style            = $data['style'];
      $urlSearch        = Dispatcher::Instance()->GetUrl(
         'approval_pencairan',
         'approvalPencairan',
         'view',
         'html'
      );

      $urlPopupUnit     = Dispatcher::Instance()->GetUrl(
         'approval_pencairan',
         'popupUnitkerja',
         'view',
         'html'
      );

      $urlPopupProgram  = Dispatcher::Instance()->GetUrl(
         'approval_pencairan',
         'popupProgram',
         'view',
         'html'
      );

      $urlDetail        = Dispatcher::Instance()->GetUrl(
         'approval_pencairan',
         'detailApprovalPencairan',
         'view',
         'html'
      );

      $urlApproval      = Dispatcher::Instance()->GetUrl(
         'approval_pencairan',
         'InputApprovalPencairan',
         'view',
         'html'
      ). '&' . $queryString;

      $urlCetak         = Dispatcher::Instance()->GetUrl(
         'approval_pencairan',
         'pencairan',
         'print',
         'html'
      );

      $urlInputSpm      = Dispatcher::Instance()->GetUrl(
         'approval_pencairan',
         'InputSpm',
         'view',
         'html'
      ).'&'.$queryString;

      $urlCetakSpm      = Dispatcher::Instance()->GetUrl(
         'approval_pencairan',
         'PrintSpm',
         'view',
         'html'
      );

      $urlExportSpm     = Dispatcher::Instance()->GetUrl(
         'approval_pencairan',
         'ExcelSpm',
         'view',
         'xlsx'
      );

      $this->mrTemplate->AddVar('content', 'URL_SEARCH', $urlSearch);
      $this->mrTemplate->AddVars('content', $requestData);
      $this->mrTemplate->AddVar('data_unit', 'LEVEL', strtoupper($unitKerja['status']));
      $this->mrTemplate->AddVar('data_unit', 'UNIT_ID', $requestData['unit_id']);
      $this->mrTemplate->AddVar('data_unit', 'UNIT_NAMA', $requestData['unit_nama']);
      $this->mrTemplate->AddVar('data_unit', 'URL_POPUP_UNIT', $urlPopupUnit);
      $this->mrTemplate->AddVar('content', 'URL_POPUP_PROGRAM', $urlPopupProgram);
      $this->mrTemplate->AddVars('content', $program, 'PROGRAM_');

      if($message){
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
               $dataGrid[$index]['tanggal']  = $dataList[$i]['tanggal'];
               $dataGrid[$index]['bulan_anggaran']    = $months[($dataList[$i]['bulan_anggaran'] - 1 )]['name'];
               $dataGrid[$index]['kode']           = $dataList[$i]['sub_kegiatan_kode'];
               $dataGrid[$index]['nama']           = $dataList[$i]['sub_kegiatan_nama'];
               $dataGrid[$index]['nominal_usulan'] = $dataList[$i]['nominal_usulan'];
               $dataGrid[$index]['nominal_setuju'] = $dataList[$i]['nominal_setuju'];
               $dataGrid[$index]['status']         = strtoupper($dataList[$i]['status']);
               $dataGrid[$index]['spp']            = $dataList[$i]['spp'];
               $dataGrid[$index]['spp_id']         = $dataList[$i]['spp_id'];
               $dataGrid[$index]['spm']            = $dataList[$i]['spm'];
               $dataGrid[$index]['spm_id']         = $dataList[$i]['spm_id'];
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
            $this->mrTemplate->clearTemplate('data_status');
            $this->mrTemplate->clearTemplate('link_approval');
            $this->mrTemplate->clearTemplate('link_status');
            $this->mrTemplate->clearTemplate('link_spm');
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

                  $this->mrTemplate->AddVar('data_status', 'STATUS', strtoupper($list['status']));
                  $list['spp_id']      = 1;
                  if(is_null($list['spp_id'])){
                     $this->mrTemplate->AddVar('link_approval', 'STATUS', 'EMPTY_SPP');
                  }else{
                     $this->mrTemplate->AddVar('link_approval', 'STATUS', 'SPP');
                     $this->mrTemplate->AddVar('link_status', 'STATUS', strtoupper($list['status']));
                     if(strtoupper($list['status']) == 'YA'){
                        $this->mrTemplate->AddVar('link_status', 'URL_DETAIL', $urlDetail.'&data_id='.Dispatcher::Instance()->Encrypt($list['id']));
                        $this->mrTemplate->AddVar('link_status', 'URL_CETAK', $urlCetak.'&grp='.Dispatcher::Instance()->Encrypt($list['id']));
                        if(is_null($list['spm_id'])){
                           $this->mrTemplate->AddVar('link_spm', 'SPM', 'NO');
                           $this->mrTemplate->AddVar('link_spm', 'URL_INPUT_SPM', $urlInputSpm.'&dataId='.Dispatcher::Instance()->Encrypt($list['id']));
                        }else{
                           $this->mrTemplate->AddVar('link_spm', 'SPM', 'YES');
                           $this->mrTemplate->AddVar('link_spm', 'URL_EDIT_SPM', $urlInputSpm.'&dataId='.Dispatcher::Instance()->Encrypt($list['id']).'&spmId='.Dispatcher::Instance()->Encrypt($list['spm_id']));
                           $this->mrTemplate->AddVar('link_spm', 'URL_CETAK_SPM', $urlCetakSpm.'&dataId='.Dispatcher::Instance()->Encrypt($list['id']).'&spmId='.Dispatcher::Instance()->Encrypt($list['spm_id']));
                           $this->mrTemplate->AddVar('link_spm', 'URL_EXPORT_SPM', $urlExportSpm.'&dataId='.Dispatcher::Instance()->Encrypt($list['id']).'&spmId='.Dispatcher::Instance()->Encrypt($list['spm_id']));
                        }
                     }elseif (strtoupper($list['status']) == 'BELUM') {
                        $this->mrTemplate->AddVar('link_status', 'URL_APPROVAL', $urlApproval . '&data_id=' . Dispatcher::Instance()->Encrypt($list['id']));
                     }
                  }
                  break;
            }

            $this->mrTemplate->clearTemplate('link_spm');
            $this->mrTemplate->AddVars('data_list', $list);
            $this->mrTemplate->parseTemplate('data_list', 'a');
         }
      }

   }
}
?>