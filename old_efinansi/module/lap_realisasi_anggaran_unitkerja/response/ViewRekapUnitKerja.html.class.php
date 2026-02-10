<?php
require_once GTFWConfiguration::GetValue('application', 'docroot') .
'module/lap_realisasi_anggaran_unitkerja/business/RekapUnitKerja.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') .
'module/user_unit_kerja/business/UserUnitKerja.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') .
'module/tanggal/business/Tanggal.class.php';

class ViewRekapUnitKerja extends HtmlResponse
{
   function TemplateModule()
   {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') .
         'module/lap_realisasi_anggaran_unitkerja/template');
      $this->SetTemplateFile('view_rekap_unitkerja.html');
   }

   function ProcessRequest()
   {
      $userid        = Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
      $mObj          = new RekapUnitKerja();
      $mUnitObj      = new UserUnitKerja();
      $dataUnit      = $mUnitObj->GetUnitKerjaRefUser($userid);
      $mDateObj      = new Tanggal();
      $requestData   = array();
      $arrPeriodeTahun        = $mObj->GetPeriodeTahun();
      $periodeTahun           = $mObj->GetPeriodeTahun(array(
         'active' => true
      ));
      $arrJenisKegiatan       = $mObj->GetComboJenisKegiatan();
      $arrBulan               = $mDateObj->GetArrayMonth();
      $bulanKeys              = array_keys($arrBulan);
      $bulanValues            = array_values($arrBulan);
      for ($i = 0;$i < count($bulanKeys);$i++){
         $arrMonth[$i]['id']     = $bulanKeys[$i];
         $arrMonth[$i]['name']   = $bulanValues[$i];
      }
      $requestData            = array();

      if(isset($mObj->_POST['btnTampilkan'])){
         $requestData['ta_id']            = $mObj->_POST['data']['ta_id'];
         $requestData['unit_id']          = $mObj->_POST['data']['unit_id'];
         $requestData['unit_nama']        = $mObj->_POST['data']['unit_nama'];
         $requestData['program_id']       = $mObj->_POST['data']['program_id'];
         $requestData['program_nama']     = $mObj->_POST['data']['program_nama'];
         $requestData['jenis_kegiatan']   = $mObj->_POST['data']['jenis_kegiatan'];
         $requestData['bulan']            = (int)$mObj->_POST['data']['bulan'];
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
      $total_data       = $mObj->GetCount();
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
         'periode_tahun',
         array(
            'data[ta_id]',
            $arrPeriodeTahun,
            $requestData['ta_id'],
            false,
            ' style="width:150px;" id="cmb_tahun_anggaran" '
         ) , Messenger::CurrentRequest);

      Messenger::Instance()->SendToComponent(
         'combobox',
         'Combobox',
         'view',
         'html',
         'jenis_kegiatan', array(
            'data[jenis_kegiatan]',
            $arrJenisKegiatan,
            $requestData['jenis_kegiatan'],
            true,
            ' style="width:200px;" id="cmb_jenis_kegiatan"'
         ) , Messenger::CurrentRequest);

      Messenger::Instance()->SendToComponent(
         'combobox',
         'Combobox',
         'view',
         'html',
         'bulan', array(
            'data[bulan]',
            $arrMonth,
            $requestData['bulan'],
            true,
            'id="cmb_mon"'
         ) , Messenger::CurrentRequest);

      $return['request_data'] = $requestData;
      $return['query_string'] = $queryString;
      $return['data_unit']    = $mObj->ChangeKeyName($dataUnit);
      $return['data_list']    = $mObj->ChangeKeyName($dataList);
      $return['data_resume']  = $mObj->ChangeKeyName($dataResume);
      $return['start']        = $offset+1;
      return $return;
   }

   function ParseTemplate($data = NULL)
   {
      $page          = 1;
      if(isset($_GET['page'])){
         $page       = (string) $_GET['page']->StripHtmlTags()->SqlString()->Raw();
      }
      $dataUnit      = $data['data_unit'];
      $requestData   = $data['request_data'];
      $queryString   = $data['query_string'].'&page='.$page;
      $start         = $data['start'];
      $dataList      = $data['data_list'];
      $dataResume    = $data['data_resume'];
      $urlSearch     = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         Dispatcher::Instance()->mSubModule,
         Dispatcher::Instance()->mAction,
         Dispatcher::Instance()->mType
      );
      $urlPopUnit    = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         'PopupUnitkerja',
         'view',
         'html'
      );
      $urlProgram    = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         'PopupProgram',
         'view',
         'html'
      );
      $urlDetail     = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         'DetailRealisasi',
         'view',
         'html'
      );
      $urlCetak      = Dispatcher::Instance()->GetUrl(
        Dispatcher::Instance()->mModule,
        'cetakUnitKerja',
        'view',
        'html'
      ).'&'.$queryString;
      $urlExcel      =  Dispatcher::Instance()->GetUrl(
        Dispatcher::Instance()->mModule,
        'ExcelUnitKerja',
        'view',
        'xlsx'
      ).'&'.$queryString;

      $this->mrTemplate->AddVar('content', 'URL_SEARCH', $urlSearch);
      $this->mrTemplate->AddVar('content', 'URL_CETAK', $urlCetak);
      $this->mrTemplate->AddVar('content', 'URL_EXCEL', $urlExcel);
      $this->mrTemplate->AddVars('content', $requestData);
      $this->mrTemplate->AddVar('content', 'URL_POPUP_PROGRAM', $urlProgram);
      $this->mrTemplate->AddVar('data_unit', 'STATUS', strtoupper($dataUnit['status']));
      $this->mrTemplate->AddVar('data_unit', 'URL_POPUP_UNIT', $urlPopUnit);
      $this->mrTemplate->AddVar('data_unit', 'UNIT_ID', $requestData['unit_id']);
      $this->mrTemplate->AddVar('data_unit', 'UNIT_NAMA', $requestData['unit_nama']);

      if(empty($dataList)){
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
         $this->mrTemplate->AddVar('resume', 'RESUME_EMPTY', 'YES');
      }else{
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');
         $this->mrTemplate->AddVar('resume', 'RESUME_EMPTY', 'NO');

         $program       = '';
         $kegiatan      = '';
         $unit          = '';
         $dataGrid      = array();
         $index         = 0;
         $dataRekap     = array();

         for ($i=0; $i < count($dataList);) {
            if((int)$unit === (int)$dataList[$i]['unit_id'] && (int)$program === (int)$dataList[$i]['program_id'] && (int)$kegiatan === (int)$dataList[$i]['kegiatan_id']){
               $kodeProgram      = $unit.'.'.$program;
               $kodeKegiatan     = $unit.'.'.$program.'.'.$kegiatan;
              
               
               $dataGrid[$index]['id']    = $dataList[$i]['id'];
               $dataGrid[$index]['idp']    = $dataList[$i]['idp'];
               $dataGrid[$index]['np']    = $dataList[$i]['n_pencairan'];
               if(($i > 0) && ($dataList[$i - 1]['id'] == $dataList[$i]['id'])){
                        
                        $dataGrid[$index]['kode']  = '';
                        $dataGrid[$index]['nama']  = '';
                        $dataGrid[$index]['nominal_usulan']    = '';
                        $dataGrid[$index]['nominal_setuju']    = '';
                        $dataGrid[$index]['nominal_revisi']    = '';
                        $dataGrid[$index]['nominal_setelah_revisi']  = '';
                        $dataGrid[$index]['sisa_dana']         = '';
                } else {
                    $dataGrid[$index]['kode']  = $dataList[$i]['sub_kegiatan_kode'];
                    $dataGrid[$index]['nama']  = $dataList[$i]['sub_kegiatan_nama'];
                    $dataGrid[$index]['nominal_usulan']    = $dataList[$i]['nominal_usulan'];
                    $dataGrid[$index]['nominal_setuju']    = $dataList[$i]['nominal_setuju'];
                    $dataGrid[$index]['nominal_revisi']    = $dataList[$i]['nominal_revisi'];
                    $dataGrid[$index]['nominal_setelah_revisi']   = $dataList[$i]['nominal_setelah_revisi'];
                    $dataGrid[$index]['sisa_dana']         = ($dataList[$i]['nominal_setelah_revisi'] - $dataList[$i]['total_approve'] - $dataList[$i]['total_belum_approve'] == '') ? '0' : $dataList[$i]['nominal_setelah_revisi'] - $dataList[$i]['total_approve'] - $dataList[$i]['total_belum_approve'];
               }
               
               if(($i > 0) && ($dataList[$i - 1]['idp'] == $dataList[$i]['idp']) &&
                    !empty($dataList[$i]['idp']) ){
                    $dataGrid[$index]['no_pengajuan']       = '';
                    $dataGrid[$index]['nominal_pencairan']  = '';
                    $dataGrid[$index]['keterangan']         = ''; 
                } elseif(empty($dataList[$i]['idp'])){
                    $dataGrid[$index]['no_pengajuan']       = '';
                    $dataGrid[$index]['nominal_pencairan']  = 0;
                    $dataGrid[$index]['keterangan']         = ''; 
                } else {
                    $dataGrid[$index]['no_pengajuan']       = $dataList[$i]['no_pengajuan'];
                    if(isset($dataList[$i]['status_approve']) || strtoupper($dataList[$i]['status_approve'] = 'YA') || strtoupper($dataList[$i]['status_approve'] = 'TIDAK')){
                        $dataGrid[$index]['nominal_pencairan']  += $dataList[$i]['nominal_pencairan_belum'] + $dataList[$i]['nominal_pencairan_ya'] + $dataList[$i]['nominal_pencairan_tidak'];
                    }else{
                        $dataGrid[$index]['nominal_pencairan']  += $dataList[$i]['nominal_pencairan_belum'] + $dataList[$i]['nominal_pencairan_ya'] + $dataList[$i]['nominal_pencairan_tidak'];
                    }
                    $dataGrid[$index]['keterangan']         = $dataList[$i]['keterangan'];  
               }
               
               
               $dataGrid[$index]['nominal_realisasi']  = $dataList[$i]['nominal_realisasi'];
               $dataGrid[$index]['tanggal_transaksi']  = $dataList[$i]['tanggal_transaksi'];
               $dataGrid[$index]['no_bukti']           = $dataList[$i]['no_bukti'];                           
               
               
               $dataGrid[$index]['tipe']              = 'sub_kegiatan';
               $dataGrid[$index]['class_name']        = '';
               $dataGrid[$index]['row_style']         = '';


               $dataRekap[$kodeProgram]['nominal_usulan']      += $dataGrid[$index]['nominal_usulan'];
               $dataRekap[$kodeProgram]['nominal_setuju']      += $dataGrid[$index]['nominal_setuju'];
               $dataRekap[$kodeProgram]['nominal_revisi']      += $dataGrid[$index]['nominal_revisi'];
               $dataRekap[$kodeProgram]['nominal_setelah_revisi'] += $dataGrid[$index]['nominal_setelah_revisi'];

               if(isset($dataList[$i]['status_approve']) || strtoupper($dataList[$i]['status_approve'] = 'YA') || strtoupper($dataList[$i]['status_approve'] = 'TIDAK')){
                        $dataRekap[$kodeProgram]['nominal_pencairan']  += $dataList[$i]['nominal_pencairan_belum'] + $dataList[$i]['nominal_pencairan_ya'] + $dataList[$i]['nominal_pencairan_tidak'];
              }else{
                $dataRekap[$kodeProgram]['nominal_pencairan']  += $dataList[$i]['nominal_pencairan_belum'] + $dataList[$i]['nominal_pencairan_ya'] + $dataList[$i]['nominal_pencairan_tidak'];
              }

               $dataRekap[$kodeProgram]['nominal_realisasi']   += $dataGrid[$index]['nominal_realisasi'];

               if(isset($dataList[$i]['status_approve']) || strtoupper($dataList[$i]['status_approve'] = 'YA') || strtoupper($dataList[$i]['status_approve'] = 'TIDAK')){
                  $dataRekap[$kodeProgram]['sisa_dana']          += $dataGrid[$index]['nominal_setelah_revisi'] - $dataList[$i]['nominal_pencairan_ya'] - $dataList[$i]['nominal_pencairan_belum'] - $dataList[$i]['nominal_pencairan_tidak'];
               }else{
                  $dataRekap[$kodeProgram]['sisa_dana']          += $dataGrid[$index]['nominal_setelah_revisi'] - $dataList[$i]['nominal_pencairan_ya'] - $dataList[$i]['nominal_pencairan_belum'] - $dataList[$i]['nominal_pencairan_tidak'];;
               }


               $dataRekap[$kodeKegiatan]['nominal_usulan']     += $dataGrid[$index]['nominal_usulan'];
               $dataRekap[$kodeKegiatan]['nominal_setuju']     += $dataGrid[$index]['nominal_setuju'];
               $dataRekap[$kodeKegiatan]['nominal_revisi']     += $dataGrid[$index]['nominal_revisi'];
               $dataRekap[$kodeKegiatan]['nominal_setelah_revisi']   += $dataGrid[$index]['nominal_setelah_revisi'];

               if(isset($dataList[$i]['status_approve']) || strtoupper($dataList[$i]['status_approve'] = 'YA') || strtoupper($dataList[$i]['status_approve'] = 'TIDAK')){
                        $dataRekap[$kodeKegiatan]['nominal_pencairan']  += $dataList[$i]['nominal_pencairan_belum'] + $dataList[$i]['nominal_pencairan_ya'] + $dataList[$i]['nominal_pencairan_tidak'];
                }else{
                  $dataRekap[$kodeKegiatan]['nominal_pencairan']  += $dataList[$i]['nominal_pencairan_belum'] + $dataList[$i]['nominal_pencairan_ya'] + $dataList[$i]['nominal_pencairan_tidak'];
                }

               $dataRekap[$kodeKegiatan]['nominal_realisasi']  += $dataGrid[$index]['nominal_realisasi'];

               if(isset($dataList[$i]['status_approve']) || strtoupper($dataList[$i]['status_approve'] = 'YA') || strtoupper($dataList[$i]['status_approve'] = 'TIDAK')){
                  $dataRekap[$kodeKegiatan]['sisa_dana']          += $dataGrid[$index]['nominal_setelah_revisi'] - $dataList[$i]['nominal_pencairan_ya'] - $dataList[$i]['nominal_pencairan_belum'] - $dataList[$i]['nominal_pencairan_tidak'];
               }else{
                  $dataRekap[$kodeKegiatan]['sisa_dana']          += $dataGrid[$index]['nominal_setelah_revisi'] - $dataList[$i]['nominal_pencairan_ya'] - $dataList[$i]['nominal_pencairan_belum'] - $dataList[$i]['nominal_pencairan_tidak'];;
               }
                              
               $i++;
            }elseif((int)$unit === (int)$dataList[$i]['unit_id'] && (int)$program === (int)$dataList[$i]['program_id'] && (int)$kegiatan !== (int)$dataList[$i]['kegiatan_id']){
               $kegiatan         = (int)$dataList[$i]['kegiatan_id'];
               $kodeSistem       = $unit.'.'.$program.'.'.$kegiatan;
               $dataRekap[$kodeSistem]['nominal_usulan']       = 0;
               $dataRekap[$kodeSistem]['nominal_setuju']       = 0;
               $dataRekap[$kodeSistem]['nominal_revisi']       = 0;
               $dataRekap[$kodeSistem]['nominal_setelah_revisi']  = 0;
               $dataRekap[$kodeSistem]['nominal_pencairan']    = 0;
               $dataRekap[$kodeSistem]['nominal_realisasi']    = 0;

               $dataGrid[$index]['id']          = $dataList[$i]['kegiatan_id'];
               $dataGrid[$index]['kode']        = $dataList[$i]['kegiatan_kode'];
               $dataGrid[$index]['nama']        = $dataList[$i]['kegiatan_nama'];
               $dataGrid[$index]['kode_sistem'] = $kodeSistem;
               $dataGrid[$index]['tipe']        = 'kegiatan';
               $dataGrid[$index]['class_name']  = 'table-common-even2';
               $dataGrid[$index]['row_style']   = '';
            }elseif((int)$unit === (int)$dataList[$i]['unit_id'] && (int)$program !== (int)$dataList[$i]['program_id']){
               $program                   = (int)$dataList[$i]['program_id'];
               $index--;
            }else{
               $unit             = (int)$dataList[$i]['unit_id'];
               $kodeSistem       = $unit.'.'.$dataList[$i]['program_id'];
               $dataRekap[$kodeSistem]['nominal_usulan']       = 0;
               $dataRekap[$kodeSistem]['nominal_setuju']       = 0;
               $dataRekap[$kodeSistem]['nominal_revisi']       = 0;
               $dataRekap[$kodeSistem]['nominal_setelah_revisi']  = 0;
               $dataRekap[$kodeSistem]['nominal_pencairan']    = 0;
               $dataRekap[$kodeSistem]['nominal_realisasi']    = 0;

               $dataGrid[$index]['unit_id']     = $dataList[$i]['unit_id'];
               $dataGrid[$index]['unit_kode']   = $dataList[$i]['unit_kode'];
               $dataGrid[$index]['unit_nama']   = $dataList[$i]['unit_nama'];
               $dataGrid[$index]['kode']        = $dataList[$i]['program_kode'];
               $dataGrid[$index]['nama']        = $dataList[$i]['program_nama'];
               $dataGrid[$index]['kode_sistem'] = $kodeSistem;
               $dataGrid[$index]['tipe']        = 'program';
               $dataGrid[$index]['class_name']  = 'table-common-even1';
               $dataGrid[$index]['row_style']   = 'font-weight: bold;';
            }

            $index++;
         }

         foreach ($dataGrid as $list) {
            $this->mrTemplate->clearTemplate('button_detail');
            $this->mrTemplate->SetAttribute('button_detail', 'visibility', 'hidden');
            switch (strtoupper($list['tipe'])) {
               case 'PROGRAM':
                  $list['nominal_usulan']    = number_format($dataRekap[$list['kode_sistem']]['nominal_usulan'], 0, ',','.');
                  $list['nominal_setuju']    = number_format($dataRekap[$list['kode_sistem']]['nominal_setuju'], 0, ',','.');
                  $list['nominal_revisi']    = number_format($dataRekap[$list['kode_sistem']]['nominal_revisi'], 0, ',', '.');
                  $list['nominal_setelah_revisi']    = number_format($dataRekap[$list['kode_sistem']]['nominal_setelah_revisi'], 0, ',', '.');
                  $list['nominal_pencairan'] = number_format($dataRekap[$list['kode_sistem']]['nominal_pencairan'], 0, ',','.');
                  $list['nominal_realisasi'] = number_format($dataRekap[$list['kode_sistem']]['nominal_realisasi'], 0, ',','.');
                  $list['sisa_dana']         = number_format($dataRekap[$list['kode_sistem']]['sisa_dana'], 0, ',','.');
                  break;
               case 'KEGIATAN':
                  $list['nominal_usulan']    = number_format($dataRekap[$list['kode_sistem']]['nominal_usulan'], 0, ',','.');
                  $list['nominal_setuju']    = number_format($dataRekap[$list['kode_sistem']]['nominal_setuju'], 0, ',','.');
                  $list['nominal_revisi']    = number_format($dataRekap[$list['kode_sistem']]['nominal_revisi'], 0, ',', '.');
                  $list['nominal_setelah_revisi']    = number_format($dataRekap[$list['kode_sistem']]['nominal_setelah_revisi'], 0, ',', '.');
                  $list['nominal_pencairan'] = number_format($dataRekap[$list['kode_sistem']]['nominal_pencairan'], 0, ',','.');
                  $list['nominal_realisasi'] = number_format($dataRekap[$list['kode_sistem']]['nominal_realisasi'], 0, ',','.');
                  $list['sisa_dana']         = number_format($dataRekap[$list['kode_sistem']]['sisa_dana'], 0, ',','.');
                  break;
               case 'SUB_KEGIATAN':
                 
                  if($list['nominal_usulan'] !='') {
                      $list['nominal_usulan']    = number_format($list['nominal_usulan'], 0, ',','.');
                  } else {
                      $list['nominal_usulan'] ='';
                  }
                  if($list['nominal_setuju']  !='') {
                     $list['nominal_setuju']    = number_format($list['nominal_setuju'], 0, ',','.');
                  } else {
                      $list['nominal_setuju'] ='';
                  }

                  if($list['nominal_revisi'] != '') {
                     $list['nominal_revisi']    = number_format($list['nominal_revisi'], 0, ',', '.');
                  } else {
                     $list['nominal_revisi'];
                  }

                  if($list['nominal_setelah_revisi'] != '') {
                     $list['nominal_setelah_revisi']    = number_format($list['nominal_setelah_revisi'], 0, ',', '.');
                  } else {
                     $list['nominal_setelah_revisi'];
                  }

                  if( $list['nominal_pencairan'] !='' ){
                    $list['nominal_pencairan'] = number_format($list['nominal_pencairan'], 0, ',','.');
                  } else {
                      $list['nominal_pencairan'] = '';
                  }

                
                    $list['nominal_realisasi'] = number_format($list['nominal_realisasi'], 0, ',','.');
                 
                  if( $list['sisa_dana']  !='') {
                    $list['sisa_dana']         = number_format($list['sisa_dana'], 0, ',','.');
                  } else {
                       $list['sisa_dana'] = '';
                  }

                  $this->mrTemplate->SetAttribute('button_detail', 'visibility', 'visible');
                  $this->mrTemplate->AddVar('button_detail', 'URL_DETAIL', 
                             $urlDetail.'&id='.Dispatcher::Instance()->Encrypt($list['id']).
                             '&idp='.Dispatcher::Instance()->Encrypt($list['idp']));
                  break;
               default:
                 
                  if($list['nominal_usulan'] !='') {
                      $list['nominal_usulan']    = number_format($list['nominal_usulan'], 0, ',','.');
                  } else {
                      $list['nominal_usulan'] ='';
                  }
                  if($list['nominal_setuju']  !='') {
                     $list['nominal_setuju']    = number_format($list['nominal_setuju'], 0, ',','.');
                  } else {
                      $list['nominal_setuju'] ='';
                  }
                  if($list['nominal_revisi']  !='') {
                     $list['nominal_revisi']    = number_format($list['nominal_revisi'], 0, ',','.');
                  } else {
                      $list['nominal_revisi'] ='';
                  }
                  if($list['nominal_setelah_revisi']  !='') {
                     $list['nominal_setelah_revisi']    = number_format($list['nominal_setelah_revisi'], 0, ',','.');
                  } else {
                      $list['nominal_setelah_revisi'] ='';
                  }
                  if( $list['nominal_pencairan']  !=''){
                    $list['nominal_pencairan'] = number_format($list['nominal_pencairan'], 0, ',','.');
                  } else {
                      $list['nominal_pencairan'] = '';
                  }
                  if( $list['nominal_realisasi'] !=''){
                    $list['nominal_realisasi'] = number_format($list['nominal_realisasi'], 0, ',','.');
                  } else {
                      $list['nominal_realisasi']='';
                  }
                  if( $list['sisa_dana'] !='') {
                    $list['sisa_dana']         = number_format($list['sisa_dana'], 0, ',','.');
                  } else {
                       $list['sisa_dana'] = '';
                  }  
                  break;
            }
            $this->mrTemplate->AddVars('data_item', $list);
            $this->mrTemplate->parseTemplate('data_item', 'a');
         }

         foreach ($dataResume as $resume) {
            $resume['nominal_usulan']     = number_format($resume['nominal_usulan'], 0, ',','.');
            $resume['nominal_setuju']     = number_format($resume['nominal_setuju'], 0, ',','.');
            $resume['nominal_revisi']     = number_format($resume['nominal_revisi'], 0, ',','.');
            $resume['nominal_setelah_revisi']     = number_format($resume['nominal_setelah_revisi'], 0, ',','.');
            $resume['nominal_pencairan']  = number_format($resume['nominal_pencairan'], 0, ',','.');
            $resume['nominal_realisasi']  = number_format($resume['nominal_realisasi'], 0, ',','.');
            $resume['sisa_dana']          = number_format($resume['sisa_dana'], 0, ',','.');
            $this->mrTemplate->AddVars('resume_item', $resume);
            $this->mrTemplate->parseTemplate('resume_item', 'a');
         }
      }

      /*$this->mrTemplate->AddVar('sub_unit', 'SEARCH_UNIT_ID', $this->data['unit_id']);
      $this->mrTemplate->AddVar('sub_unit', 'SEARCH_UNIT_NAMA', $this->data['unit_nama']);
      $this->mrTemplate->AddVar('content', 'SEARCH_TA_NAMA', $this->data['ta_nama']);

      $this->mrTemplate->AddVar('content', 'SEARCH_PROGRAM_NAMA', $this->data['program_nama']);
      $this->mrTemplate->AddVar('content', 'SEARCH_PROGRAM_ID', $this->data['program_id']);

      $this->mrTemplate->AddVar('content', 'URL_CETAK',
                         .
                                            '&p=' . $this->data['ta_id'] .
                                            '&q=' . $this->data['ta_nama'] .
                                            '&r=' . $this->data['unit_id'] .
                                            '&s=' . $this->data['unit_nama'] .
                                            '&t=' . $data['startRec'] .
                                            '&u=' . $data['itemViewed'] .
                                            '&v=' . $this->data['program_id'] .
                                            '&w=' . $this->data['jenis_kegiatan'] .
                                            '&bulan=' . Dispatcher::Instance()->Encrypt($this->data['bulan']));

      $this->mrTemplate->AddVar('content', 'URL_EXCEL',
                       .
                                            '&p=' . $this->data['ta_id'] .
                                            '&q=' . $this->data['ta_nama'] .
                                            '&r=' . $this->data['unit_id'] .
                                            '&s=' . $this->data['unit_nama'] .
                                            '&t=' . $data['startRec'] .
                                            '&u=' . $data['itemViewed'] .
                                            '&v=' . $this->data['program_id'] .
                                            '&w=' . $this->data['jenis_kegiatan'] .
                                            '&bulan=' . Dispatcher::Instance()->Encrypt($this->data['bulan']));

      $this->mrTemplate->AddVar('content', 'URL_RTF',
                        Dispatcher::Instance()->GetUrl(
                                            'lap_realisasi_anggaran_unitkerja',
                                            'RtfUnitKerja',
                                            'view',
                                            'html') .
                                            '&p=' . $this->data['ta_id'] .
                                            '&q=' . $this->data['ta_nama'] .
                                            '&r=' . $this->data['unit_id'] .
                                            '&s=' . $this->data['unit_nama'] .
                                            '&t=' . $data['startRec'] .
                                            '&u=' . $data['itemViewed'] .
                                            '&v=' . $this->data['program_id'] .
                                            '&w=' . $this->data['jenis_kegiatan'] .
                                            '&bulan=' . Dispatcher::Instance()->Encrypt($this->data['bulan']));



      $url20090120 = Dispatcher::Instance()->GetUrl(
                            Dispatcher::Instance()->mModule, 'detailRealisasi', 'popup', 'html');
      $nomor_program = ''; //inisialisasi nomor program

      $nomor_kegiatan = ''; //inisialisasi nomor kegiatan


      if ($data['total_sub_unit_kerja'] > 0)
      {
         $this->mrTemplate->AddVar('sub_unit', 'IS_UNIT_KERJA', 'YES');
      }
      else
      {
         $this->mrTemplate->AddVar('sub_unit', 'IS_UNIT_KERJA', 'NO');
         $this->mrTemplate->AddVar('sub_unit', 'DATA_SUBUNIT_NAMA', $this->data['unit_nama']);
      }
      $i = 0;

      if (empty($data['data']))
      {
         $this->mrTemplate->AddVar('data_grid', 'IS_DATA_EMPTY', 'YES');
         $this->mrTemplate->AddVar('resume', 'RESUME_EMPTY', 'YES');
      }
      else
      {
         $this->mrTemplate->AddVar('data_grid', 'IS_DATA_EMPTY', 'NO');
         $this->mrTemplate->AddVar('resume', 'RESUME_EMPTY', 'NO');
         $dataGrid = $data['data'];
         $i = 0;
         $index = 0; // inisialisasi data yang akan dikirim

         $no = 1;
         $program_nomor = ''; //inisialisasi program

         $kegiatan_nomor = ''; //inisialisasi kegiatan

         $unit_nama = ''; //inisialisasi nama unit

         $index_program = ''; //inisialisasi index program yang aktif saat ini

         $index_kegiatan = ''; //inisialisasi index kegiatan yang aktif saat ini

         $dataList = array();

         //debug($dataGrid);
         //parsing  tampilan dan membuat menjadi array bertingkat yang ditempatkan pada $dataList


         for ($i = 0;$i < sizeof($dataGrid);)
         {

            //===========kondisi kalo berupa data sub_kegiatan ===========================

            if (($program_nomor == $dataGrid[$i]['kodeProg']) &&
                                ($kegiatan_nomor == $dataGrid[$i]['kodeKegiatan']) &&
                                            ($unit_nama == $dataGrid[$i]['unitName']))
            {
               $dataKirim[$index]['class_name'] = '';
               $dataKirim[$index]['nama_unit'] = '';
               $dataKirim[$index]['kode_kegiatan'] = $dataGrid[$i]['kodeSubKegiatan'];
               $dataKirim[$index]['nama_kegiatan'] = $dataGrid[$i]['namaSubKegiatan'];
               $dataKirim[$index]['nominal_usulan'] = $dataGrid[$i]['nominalUsulan'];
               $dataKirim[$index]['nominal_setujui'] = $dataGrid[$i]['nominalSetuju'];
               $dataKirim[$index]['nominal_pencairan'] = $dataGrid[$i]['nominal_pencairan'];
               $dataKirim[$index]['nominal_realisasi'] = $dataGrid[$i]['nominal_realisasi'];
               $dataKirim[$index]['sisa'] = $dataGrid[$i]['sisa'];
               $dataKirim[$index]['kegdetid'] = $dataGrid[$i]['kegdetid'];

               //$dataKirim[$index] = $dataGrid[$i];
               $dataList[$index_program]['data'][$index_kegiatan]['data'][$index] = $dataKirim[$index];
               $i++;

               //klo informasi program    ====================

            }
            elseif (($program_nomor != $dataGrid[$i]['kodeProg']) &&
                            ($unit_nama == $dataGrid[$i]['unitName']))
            {
               $dataKirim[$index]['class_name'] = 'table-common-even1';
               $dataKirim[$index]['nama_unit'] = '';
               $dataKirim[$index]['kode_kegiatan'] = $dataGrid[$i]['kodeProg'];
               $dataKirim[$index]['nama_kegiatan'] = $dataGrid[$i]['namaProgram'];
               $dataKirim[$index]['nominal_usulan'] = '';
               $dataKirim[$index]['nominal_pencairan'] = '';
               $dataKirim[$index]['nominal_setujui'] = '';
               $dataKirim[$index]['unitName'] = $dataGrid[$i]['unitName'];
               $program_nomor = $dataGrid[$i]['kodeProg'];
               $index_program = $index;
               $dataList[$index_program] = $dataKirim[$index];
            }
            elseif ((($program_nomor != $dataGrid[$i]['kodeProg']) &&
                                    ($unit_nama != $dataGrid[$i]['unitName'])) ||
                                            (($program_nomor == $dataGrid[$i]['kodeProg']) &&
                                                    ($unit_nama != $dataGrid[$i]['unitName'])))
            { //klo informasi program  ====================

               $dataKirim[$index]['class_name'] = 'table-common-even1';
               $dataKirim[$index]['nama_unit'] = $dataGrid[$i]['unitName'];
               $dataKirim[$index]['kode_kegiatan'] = $dataGrid[$i]['kodeProg'];
               $dataKirim[$index]['nama_kegiatan'] = $dataGrid[$i]['namaProgram'];
               $dataKirim[$index]['nominal_usulan'] = '';
               $dataKirim[$index]['nominal_setujui'] = '';
               $dataKirim[$index]['unitName'] = $dataGrid[$i]['unitName'];
               $program_nomor = $dataGrid[$i]['kodeProg'];
               $unit_nama = $dataGrid[$i]['unitName'];
               $index_program = $index;
               $dataList[$index_program] = $dataKirim[$index];
            }
            elseif ($kegiatan_nomor != $dataGrid[$i]['kodeKegiatan'])
            { //klo informasi kegiatan  =============================

               $dataKirim[$index]['class_name'] = 'table-common-even2';
               $dataKirim[$index]['nama_unit'] = '';
               $dataKirim[$index]['kode_kegiatan'] = $dataGrid[$i]['kodeKegiatan'];
               $dataKirim[$index]['nama_kegiatan'] = $dataGrid[$i]['namaKegiatan'];
               $dataKirim[$index]['nominal_usulan'] = '';
               $dataKirim[$index]['nominal_setujui'] = '';
               $dataKirim[$index]['unitName'] = $dataGrid[$i]['unitName'];
               $kegiatan_nomor = $dataGrid[$i]['kodeKegiatan'];
               $index_kegiatan = $index;
               $dataList[$index_program]['data'][$index_kegiatan] = $dataKirim[$index];
            }
            $index++;
         } //end for dataGrid


         for ($j = 0;$j < sizeof($dataKirim);$j++)
         {
            $resume_program = $data['resume_program'];
            $resume_kegiatan = $data['resume_kegiatan'];
            $rp = 0;
            $done = false;

            if (isset($dataKirim[$j]['unitName']))
            {

               //mencari apakah ada untuk jumlah program

               for ($rp = 0;$rp < sizeof($resume_program);$rp++)
               {

                  if (($dataKirim[$j]['kode_kegiatan'] == $resume_program[$rp]['kodeProg']) &&
                                (trim($dataKirim[$j]['unitName']) == trim($resume_program[$rp]['unitName'])))
                  {
                     $dataKirim[$j]['nominal_usulan'] = $resume_program[$rp]['nominalUsulan'];
                     $dataKirim[$j]['nominal_setujui'] = $resume_program[$rp]['nominalSetuju'];
                     $dataKirim[$j]['nominal_pencairan'] = $resume_program[$rp]['nominal_pencairan'];
                     $dataKirim[$j]['nominal_realisasi'] = $resume_program[$rp]['nominal_realisasi'];
                     $dataKirim[$j]['sisa'] = $resume_program[$rp]['sisa'];
                     $done = true;

                     break;
                  }
               }
               $rp = 0;

               if (!$done)
               for ($rp = 0;$rp < sizeof($resume_kegiatan);$rp++)
               {

                  if (($dataKirim[$j]['kode_kegiatan'] == $resume_kegiatan[$rp]['kodeKegiatan']) &&
                                (trim($dataKirim[$j]['unitName']) == trim($resume_kegiatan[$rp]['unitName'])))
                  {
                     $dataKirim[$j]['nominal_usulan'] = $resume_kegiatan[$rp]['nominalUsulan'];
                     $dataKirim[$j]['nominal_setujui'] = $resume_kegiatan[$rp]['nominalSetuju'];
                     $dataKirim[$j]['nominal_pencairan'] = $resume_kegiatan[$rp]['nominal_pencairan'];
                     $dataKirim[$j]['nominal_realisasi'] = $resume_kegiatan[$rp]['nominal_realisasi'];
                     $dataKirim[$j]['sisa'] = $resume_kegiatan[$rp]['sisa'];
                     $done = true;

                     break;
                  }
               }
            } //end of isset

            //tambahan---------------
            $i = sizeof($dataKirim)-1;
         $nominal_usulan=0;
         $nominal_setuju=0;
         $nominal_pencairan=0;
         $nominal_realisasi=0;
         $sisa=0;
         while($i >= 0) {
            if($dataKirim[$i]['class_name'] == '') {
               $nominal_usulan += $dataKirim[$i]['nominal_usulan'];
               $nominal_setuju += $dataKirim[$i]['nominal_setujui'];
               $nominal_pencairan += $dataKirim[$i]['nominal_pencairan'];
               $nominal_realisasi += $dataKirim[$i]['nominal_realisasi'];
               $sisa = ($nominal_setuju - $nominal_realisasi);
            }
            if($dataKirim[$i]['class_name'] == 'table-common-even2') {
               $dataKirim[$i]['nominal_usulan'] = $nominal_usulan;
               $dataKirim[$i]['nominal_setujui'] = $nominal_setuju;
               $dataKirim[$i]['nominal_pencairan'] = $nominal_pencairan;
               $dataKirim[$i]['nominal_realisasi'] = $nominal_realisasi;
               $dataKirim[$i]['sisa'] = $sisa;
               $nominal_usulan_program += $nominal_usulan;
               $nominal_setuju_program += $nominal_setuju;
               $nominal_pencairan_program += $nominal_pencairan;
               $nominal_realisasi_program += $nominal_realisasi;
               $sisa_program = ($nominal_setuju_program - $nominal_realisasi_program);
               $nominal_usulan=0;
               $nominal_setuju=0;
               $nominal_realisasi=0;
               $nominal_pencairan=0;
               $sisa=0;
            }
            if($dataKirim[$i]['class_name'] == 'table-common-even1') {
               $dataKirim[$i]['nominal_usulan'] = $nominal_usulan_program;
               $dataKirim[$i]['nominal_setujui'] = $nominal_setuju_program;
               $dataKirim[$i]['nominal_pencairan'] = $nominal_pencairan_program;
               $dataKirim[$i]['nominal_realisasi'] = $nominal_realisasi_program;
               $dataKirim[$i]['sisa'] = $sisa_program;
               $nominal_usulan_program = 0;
               $nominal_setuju_program = 0;
               $nominal_realisasi_program = 0;
               $nominal_pencairan_program = 0;
               $sisa_program = 0;
            }
            $i--;
         }
            //end of tambahan----------

            $dataKirim[$j]['nominal_usulan'] = number_format($dataKirim[$j]['nominal_usulan'], 0, ',', '.');
            $dataKirim[$j]['nominal_setujui'] = number_format($dataKirim[$j]['nominal_setujui'], 0, ',', '.');
            $dataKirim[$j]['nominal_pencairan'] = number_format($dataKirim[$j]['nominal_pencairan'], 0, ',', '.');
            $dataKirim[$j]['nominal_realisasi'] = number_format($dataKirim[$j]['nominal_realisasi'], 0, ',', '.');
            $dataKirim[$j]['sisa'] = number_format($dataKirim[$j]['sisa'], 0, ',', '.');
            //print_r($dataKirim);

            if (isset($dataKirim[$j]['kegdetid']))
            {
               $this->mrTemplate->setAttribute('button_detail', 'visibility', 'visible');
               $this->mrTemplate->AddVar('button_detail', 'URL_DETAIL_REALISASI', $url20090120 . "&id=" . $dataKirim[$j]['kegdetid']);
            }
            else $this->mrTemplate->setAttribute('button_detail', 'visibility', 'hidden');
            $this->mrTemplate->AddVars('data_item', $dataKirim[$j], 'DATA_');
            $this->mrTemplate->parseTemplate('data_item', 'a');
         }

         //debug($dataKirim);
         //mengirimkan data resume

         $resume_unit_kerja = $data['resume_unit_kerja'];

         //debug($resume_program);

         foreach($resume_unit_kerja as $val)
         {
            $val['nominalUsulan'] = number_format($val['nominalUsulan'], 0, ',', '.');
            $val['nominalSetuju'] = number_format($val['nominalSetuju'], 0, ',', '.');
            $val['nominal_pencairan'] = number_format($val['nominal_pencairan'], 0, ',', '.');
            $val['nominal_realisasi'] = number_format($val['nominal_realisasi'], 0, ',', '.');
            $val['sisa'] = number_format($val['sisa'], 0, ',', '.');
            $this->mrTemplate->AddVars('resume_item', $val, 'RESUME_');
            $this->mrTemplate->parseTemplate('resume_item', 'a');
         }

         //debug($dataKirim);
         //debug($dataGrid);


      }*/
   }
}
?>