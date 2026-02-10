<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/lap_realisasi_anggaran_unitkerja/business/RekapUnitKerja.class.php';

class ViewCetakUnitKerja extends HtmlResponse
{
   protected $RekapUnitKerja;
   protected $data;
   function ViewCetakUnitKerja()
   {
      $this->RekapUnitKerja = new RekapUnitKerja();
   }

   function TemplateModule()
   {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').'module/lap_realisasi_anggaran_unitkerja/template');
      $this->SetTemplateFile('view_cetak_unitkerja.html');
   }

   function TemplateBase()
   {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
      $this->SetTemplateFile('document-print-custom-wide.html');
      $this->SetTemplateFile('layout-common-print.html');
   }

   function ProcessRequest()
   {
      $mObj          = new RekapUnitKerja();
      $requestData   = array();
      $requestData['ta_id']            = Dispatcher::Instance()->Decrypt($mObj->_GET['ta_id']);
      $requestData['unit_id']          = Dispatcher::Instance()->Decrypt($mObj->_GET['unit_id']);
      $requestData['unit_nama']        = Dispatcher::Instance()->Decrypt($mObj->_GET['unit_nama']);
      $requestData['program_id']       = Dispatcher::Instance()->Decrypt($mObj->_GET['program_id']);
      $requestData['program_nama']     = Dispatcher::Instance()->Decrypt($mObj->_GET['program_nama']);
      $requestData['jenis_kegiatan']   = Dispatcher::Instance()->Decrypt($mObj->_GET['jenis_kegiatan']);
      $requestData['bulan']            = Dispatcher::Instance()->Decrypt($mObj->_GET['bulan']);
      $requestData['ta_nama']          = Dispatcher::Instance()->Decrypt($mObj->_GET['ta_nama']);

      if(!empty($requestData['bulan'])){
            $requestData['nama_bulan']  = $mObj->indonesianMonth[$requestData['bulan']]['name'];
      } else {
            $requestData['nama_bulan'] = 'Semua Bulan';
      }
      
      $dataUnit         = $mObj->GetUnitIdentity($requestData['unit_id']);
      $dataList         = $mObj->GetData(0, 100000, (array)$requestData);
      $dataResume       = $mObj->GetDataResume((array)$requestData);

      $return['data_list']    = $mObj->ChangeKeyName($dataList);
      $return['data_resume']  = $mObj->ChangeKeyName($dataResume);
      $return['request_data'] = $requestData;
      $return['data_unit']    = $dataUnit[0];
      return $return;
   }

   function ParseTemplate($data = NULL) {
      $mObj             = new RekapUnitKerja();
      $dataList         = $data['data_list'];
      $dataResume       = $data['data_resume'];
      $requestData      = $data['request_data'];
      $pimpinan         = $data['data_unit']['unitkerjaNamaPimpinan'];
      $date             = date('Y-m-d', time());
      if(!empty($pimpinan)){
         $this->mrTemplate->AddVar('content', 'NAMA_PIMPINAN', $pimpinan);
      }else{
         $this->mrTemplate->AddVar('content', 'NAMA_PIMPINAN', str_repeat('.', 50));
      }
      $this->mrTemplate->AddVar('content', 'date', $mObj->indonesianDate($date));
      $this->mrTemplate->AddVars('content', $requestData);
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
                        $dataGrid[$index]['nominal_lppa']  = '';
                        $dataGrid[$index]['nominal_lppa_sisa']  = '';
                } else {
                    $dataGrid[$index]['kode']  = $dataList[$i]['sub_kegiatan_kode'];
                    $dataGrid[$index]['nama']  = $dataList[$i]['sub_kegiatan_nama'];
                    $dataGrid[$index]['nominal_usulan']    = $dataList[$i]['nominal_usulan'];
                    $dataGrid[$index]['nominal_setuju']    = $dataList[$i]['nominal_setuju'];
                    $dataGrid[$index]['nominal_revisi']    = $dataList[$i]['nominal_revisi'];
                    $dataGrid[$index]['nominal_setelah_revisi']   = $dataList[$i]['nominal_setelah_revisi'];
                    $dataGrid[$index]['nominal_lppa']  = '';
                    $dataGrid[$index]['nominal_lppa_sisa']  = '';
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
                    $dataGrid[$index]['nominal_lppa']       =  $dataList[$i]['nominal_lppa'];
                    $dataGrid[$index]['nominal_lppa_sisa']  =  0;
                    if($dataList[$i]['nominal_lppa'] > 0) {
                     $dataGrid[$index]['nominal_lppa_sisa']  =  $dataList[$i]['nominal_realisasi'] - $dataList[$i]['nominal_lppa'] ;
                    }
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
               $dataRekap[$kodeProgram]['nominal_lppa']        += $dataGrid[$index]['nominal_lppa'];
               $dataRekap[$kodeProgram]['nominal_lppa_sisa']   += $dataGrid[$index]['nominal_lppa_sisa'];

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
               $dataRekap[$kodeKegiatan]['nominal_lppa']        += $dataGrid[$index]['nominal_lppa'];
               $dataRekap[$kodeKegiatan]['nominal_lppa_sisa']   += $dataGrid[$index]['nominal_lppa_sisa'];

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
               $dataRekap[$kodeSistem]['nominal_pencairan']    = 0;
               $dataRekap[$kodeSistem]['nominal_realisasi']    = 0;

               $dataGrid[$index]['id']          = $dataList[$i]['kegiatan_id'];
               $dataGrid[$index]['kode']        = $dataList[$i]['kegiatan_kode'];
               $dataGrid[$index]['nama']        = $dataList[$i]['kegiatan_nama'];
               $dataGrid[$index]['kode_sistem'] = $kodeSistem;
               $dataGrid[$index]['tipe']        = 'kegiatan';
               $dataGrid[$index]['class_name']  = 'table-common-even2';
               $dataGrid[$index]['row_style']   = 'background-color:#DCDCDC;';
            }elseif((int)$unit === (int)$dataList[$i]['unit_id'] && (int)$program !== (int)$dataList[$i]['program_id']){
               $program                   = (int)$dataList[$i]['program_id'];
               $index--;
            }else{
               $unit             = (int)$dataList[$i]['unit_id'];
               $kodeSistem       = $unit.'.'.$dataList[$i]['program_id'];
               $dataRekap[$kodeSistem]['nominal_usulan']       = 0;
               $dataRekap[$kodeSistem]['nominal_setuju']       = 0;
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
               $dataGrid[$index]['row_style']   = 'font-weight: bold; background-color:#CCCCCC;';
            }

            $index++;
         }

         foreach ($dataGrid as $list) {
            switch (strtoupper($list['tipe'])) {
               case 'PROGRAM':
                  $list['nominal_usulan']    = number_format($dataRekap[$list['kode_sistem']]['nominal_usulan'], 0, ',','.');
                  $list['nominal_setuju']    = number_format($dataRekap[$list['kode_sistem']]['nominal_setuju'], 0, ',','.');
                  $list['nominal_revisi']    = number_format($dataRekap[$list['kode_sistem']]['nominal_revisi'], 0, ',','.');
                  $list['nominal_setelah_revisi']    = number_format($dataRekap[$list['kode_sistem']]['nominal_setelah_revisi'], 0, ',','.');
                  $list['nominal_pencairan'] = number_format($dataRekap[$list['kode_sistem']]['nominal_pencairan'], 0, ',','.');
                  $list['nominal_realisasi'] = number_format($dataRekap[$list['kode_sistem']]['nominal_realisasi'], 0, ',','.');
                  $list['sisa_dana']         = number_format($dataRekap[$list['kode_sistem']]['sisa_dana'], 0, ',','.');
                  $list['nominal_lppa']      = number_format($dataRekap[$list['kode_sistem']]['nominal_lppa'], 0, ',','.');
                  $list['nominal_lppa_sisa'] = number_format($dataRekap[$list['kode_sistem']]['nominal_lppa_sisa'], 0, ',','.');
                  break;
               case 'KEGIATAN':
                  $list['nominal_usulan']    = number_format($dataRekap[$list['kode_sistem']]['nominal_usulan'], 0, ',','.');
                  $list['nominal_setuju']    = number_format($dataRekap[$list['kode_sistem']]['nominal_setuju'], 0, ',','.');
                  $list['nominal_revisi']    = number_format($dataRekap[$list['kode_sistem']]['nominal_revisi'], 0, ',','.');
                  $list['nominal_setelah_revisi']    = number_format($dataRekap[$list['kode_sistem']]['nominal_setelah_revisi'], 0, ',','.');
                  $list['nominal_pencairan'] = number_format($dataRekap[$list['kode_sistem']]['nominal_pencairan'], 0, ',','.');
                  $list['nominal_realisasi'] = number_format($dataRekap[$list['kode_sistem']]['nominal_realisasi'], 0, ',','.');
                  $list['sisa_dana']         = number_format($dataRekap[$list['kode_sistem']]['sisa_dana'], 0, ',','.');
                  $list['nominal_lppa']      = number_format($dataRekap[$list['kode_sistem']]['nominal_lppa'], 0, ',','.');
                  $list['nominal_lppa_sisa'] = number_format($dataRekap[$list['kode_sistem']]['nominal_lppa_sisa'], 0, ',','.');
                  break;
               case 'SUB_KEGIATAN':
                   if($list['nominal_usulan'] > 0) {
                      $list['nominal_usulan']    = number_format($list['nominal_usulan'], 0, ',','.');
                  } else {
                      $list['nominal_usulan'] ='';
                  }
                  if($list['nominal_setuju']  > 0) {
                     $list['nominal_setuju']    = number_format($list['nominal_setuju'], 0, ',','.');
                  } else {
                      $list['nominal_setuju'] ='';
                  }
                  if( $list['nominal_revisi'] > 0){
                    $list['nominal_revisi'] = number_format($list['nominal_revisi'], 0, ',','.');
                  } else {
                      $list['nominal_revisi'] = '';
                  }
                  if( $list['nominal_setelah_revisi'] > 0){
                    $list['nominal_setelah_revisi'] = number_format($list['nominal_setelah_revisi'], 0, ',','.');
                  } else {
                      $list['nominal_setelah_revisi']='';
                  }
                  if( $list['nominal_pencairan'] > 0){
                    $list['nominal_pencairan'] = number_format($list['nominal_pencairan'], 0, ',','.');
                  } else {
                      $list['nominal_pencairan'] = '';
                  }
                  if( $list['nominal_realisasi'] > 0){
                    $list['nominal_realisasi'] = number_format($list['nominal_realisasi'], 0, ',','.');
                  } else {
                      $list['nominal_realisasi']='';
                  }

                  if($list['nominal_lppa']  !=''){
                    $list['nominal_lppa']      = number_format($list['nominal_lppa'], 0, ',','.');
                  }
                  if($list['nominal_lppa_sisa']  !=''){
                     $list['nominal_lppa_sisa']      = number_format($list['nominal_lppa_sisa'], 0, ',','.');
                   }

                  if( $list['sisa_dana'] > 0) {
                    $list['sisa_dana']         = number_format($list['sisa_dana'], 0, ',','.');
                  } else {
                       $list['sisa_dana'] = '0';
                  }  
                  break;
               default:
                  if($list['nominal_usulan'] > 0) {
                      $list['nominal_usulan']    = number_format($list['nominal_usulan'], 0, ',','.');
                  } else {
                      $list['nominal_usulan'] ='';
                  }
                  if($list['nominal_setuju']  > 0) {
                     $list['nominal_setuju']    = number_format($list['nominal_setuju'], 0, ',','.');
                  } else {
                      $list['nominal_setuju'] ='';
                  }
                  if( $list['nominal_pencairan'] > 0){
                    $list['nominal_pencairan'] = number_format($list['nominal_pencairan'], 0, ',','.');
                  } else {
                      $list['nominal_pencairan'] = '';
                  }
                  if( $list['nominal_realisasi'] > 0){
                    $list['nominal_realisasi'] = number_format($list['nominal_realisasi'], 0, ',','.');
                  } else {
                      $list['nominal_realisasi']='';
                  }
                  if($list['nominal_lppa']  !=''){
                    $list['nominal_lppa']      = number_format($list['nominal_lppa'], 0, ',','.');
                  }


                  if($list['nominal_lppa_sisa']  !=''){
                     $list['nominal_lppa_sisa']  = number_format($list['nominal_lppa_sisa'], 0, ',','.');
                   }
 
                  if( $list['sisa_dana'] > 0) {
                    $list['sisa_dana']         = number_format($list['sisa_dana'], 0, ',','.');
                  } else {
                       $list['sisa_dana'] = '';
                  }  
                  break;
            }
            $this->mrTemplate->AddVars('data_item', $list);
            $this->mrTemplate->parseTemplate('data_item', 'a');
         }


         
         
         $unitId     = 0;
         $rIndex     = 0;
         $resume     = array();
         $totalAll   = array();
         $totalUnit  = array();
         for($r = 0 ; $r < (sizeof($dataResume));) {      
            if($dataResume[$r]['unit_id'] == $unitId) {               
               $resume[$rIndex]['nama']                     = $dataResume[$r]['program_nama'];
               $resume[$rIndex]['nominal_usulan']           = $dataResume[$r]['nominal_usulan'];
               $resume[$rIndex]['nominal_setuju']           = $dataResume[$r]['nominal_setuju'];
               $resume[$rIndex]['nominal_revisi']           = $dataResume[$r]['nominal_revisi'];
               $resume[$rIndex]['nominal_setelah_revisi']   = $dataResume[$r]['nominal_setelah_revisi'];
               $resume[$rIndex]['nominal_pencairan']        = $dataResume[$r]['nominal_pencairan'];
               $resume[$rIndex]['nominal_realisasi']        = $dataResume[$r]['nominal_realisasi'] ;
               $resume[$rIndex]['nominal_lppa']             = $dataResume[$r]['nominal_lppa'];
               $resume[$rIndex]['nominal_lppa_sisa']        = 0;
               if($dataResume[$r]['nominal_lppa'] > 0 ) {
                  $resume[$rIndex]['nominal_lppa_sisa']        = $dataResume[$r]['nominal_realisasi'] -  $dataResume[$r]['nominal_lppa'];
               }
               $resume[$rIndex]['sisa_dana']                = $dataResume[$r]['sisa_dana'];
               $resume[$rIndex]['class_name']               = '';
               $resume[$rIndex]['tipe']                     = 'program';
               $resume[$rIndex]['style']                    = 'font-weight:normal';

               // hitung total unit
               $totalUnit[$unitId]['nominal_usulan']           += $dataResume[$r]['nominal_usulan'];
               $totalUnit[$unitId]['nominal_setuju']           += $dataResume[$r]['nominal_setuju'];
               $totalUnit[$unitId]['nominal_revisi']           += $dataResume[$r]['nominal_revisi'];
               $totalUnit[$unitId]['nominal_setelah_revisi']   += $dataResume[$r]['nominal_setelah_revisi'];
               $totalUnit[$unitId]['nominal_pencairan']        += $dataResume[$r]['nominal_pencairan'];
               $totalUnit[$unitId]['nominal_realisasi']        += $dataResume[$r]['nominal_realisasi'] ;
               $totalUnit[$unitId]['nominal_lppa']             += $dataResume[$r]['nominal_lppa'];           
               
               if($dataResume[$r]['nominal_lppa'] > 0 ) {
                  $totalUnit[$unitId]['nominal_lppa_sisa']        += ($dataResume[$r]['nominal_realisasi'] -  $dataResume[$r]['nominal_lppa']);
               } else {
                  $totalUnit[$unitId]['nominal_lppa_sisa']        += 0;
               }
               $totalUnit[$unitId]['sisa_dana']                += $dataResume[$r]['sisa_dana'];
               
               // hitung total all
               $totalAll['nominal_usulan']           += $dataResume[$r]['nominal_usulan'];
               $totalAll['nominal_setuju']           += $dataResume[$r]['nominal_setuju'];
               $totalAll['nominal_revisi']           += $dataResume[$r]['nominal_revisi'];
               $totalAll['nominal_setelah_revisi']   += $dataResume[$r]['nominal_setelah_revisi'];
               $totalAll['nominal_pencairan']        += $dataResume[$r]['nominal_pencairan'];
               $totalAll['nominal_realisasi']        += $dataResume[$r]['nominal_realisasi'] ;
               $totalAll['nominal_lppa']             += $dataResume[$r]['nominal_lppa'];            
               
               if($dataResume[$r]['nominal_lppa'] > 0 ) {
                  $totalAll['nominal_lppa_sisa']        += ($dataResume[$r]['nominal_realisasi'] -  $dataResume[$r]['nominal_lppa']);
               } else {
                  $totalAll['nominal_lppa_sisa']        += 0;
               } 
               $totalAll['sisa_dana']                += $dataResume[$r]['sisa_dana'];
               // end
               $r++;
            } else {
               $unitId = $dataResume[$r]['unit_id'];               
               $resume[$rIndex]['unit_id']                  = $dataResume[$r]['unit_id'];       
               $resume[$rIndex]['nama']                     = $dataResume[$r]['unit_nama'];               
               $resume[$rIndex]['nominal_usulan']           = 0;
               $resume[$rIndex]['nominal_setuju']           = 0;
               $resume[$rIndex]['nominal_revisi']           = 0;
               $resume[$rIndex]['nominal_setelah_revisi']   = 0;
               $resume[$rIndex]['nominal_pencairan']        = 0;
               $resume[$rIndex]['nominal_realisasi']        = 0;
               $resume[$rIndex]['nominal_lppa']             = 0;
               $resume[$rIndex]['nominal_lppa_sisa']        = 0;
               $resume[$rIndex]['sisa_dana']                = 0;
               $resume[$rIndex]['class_name']               = 'table-common-even1';
               $resume[$rIndex]['tipe']                     = 'unit';
               $resume[$rIndex]['style']                    = 'font-weight:bold; background-color:#CCCCCC;';
            }
            $rIndex++;
         }


         foreach ($resume as $resume) {

            if($resume['tipe'] === 'unit') {
               $resume['nominal_usulan']           = isset($totalUnit[$resume['unit_id']]['nominal_usulan']) ? $totalUnit[$resume['unit_id']]['nominal_usulan'] : 0;
               $resume['nominal_setuju']           = isset($totalUnit[$resume['unit_id']]['nominal_setuju']) ? $totalUnit[$resume['unit_id']]['nominal_setuju'] : 0;
               $resume['nominal_revisi']           = isset($totalUnit[$resume['unit_id']]['nominal_revisi']) ? $totalUnit[$resume['unit_id']]['nominal_revisi'] : 0;
               $resume['nominal_setelah_revisi']   = isset($totalUnit[$resume['unit_id']]['nominal_setelah_revisi']) ? $totalUnit[$resume['unit_id']]['nominal_setelah_revisi'] : 0;
               $resume['nominal_pencairan']        = isset($totalUnit[$resume['unit_id']]['nominal_pencairan']) ? $totalUnit[$resume['unit_id']]['nominal_pencairan'] : 0;
               $resume['nominal_realisasi']        = isset($totalUnit[$resume['unit_id']]['nominal_realisasi']) ? $totalUnit[$resume['unit_id']]['nominal_realisasi'] : 0;
               $resume['nominal_lppa']             = isset($totalUnit[$resume['unit_id']]['nominal_lppa']) ? $totalUnit[$resume['unit_id']]['nominal_lppa'] : 0;
               $resume['nominal_lppa_sisa']        = isset($totalUnit[$resume['unit_id']]['nominal_lppa_sisa']) ? $totalUnit[$resume['unit_id']]['nominal_lppa_sisa'] : 0;
               $resume['sisa_dana']                = isset($totalUnit[$resume['unit_id']]['sisa_dana']) ? $totalUnit[$resume['unit_id']]['sisa_dana'] : 0;
            }

            $resume['nominal_usulan']           = number_format( $resume['nominal_usulan'] ,0,',','.');
            $resume['nominal_setuju']           = number_format( $resume['nominal_setuju'] ,0,',','.');
            $resume['nominal_revisi']           = number_format( $resume['nominal_revisi'] ,0,',','.');
            $resume['nominal_setelah_revisi']   = number_format( $resume['nominal_setelah_revisi'] ,0,',','.');
            $resume['nominal_pencairan']        = number_format( $resume['nominal_pencairan'] ,0,',','.');
            $resume['nominal_realisasi']        = number_format( $resume['nominal_realisasi'] ,0,',','.'); 
            $resume['nominal_lppa']             = number_format( $resume['nominal_lppa'] ,0,',','.');
            $resume['nominal_lppa_sisa']        = number_format( $resume['nominal_lppa_sisa'] ,0,',','.');
            $resume['sisa_dana']                = number_format( $resume['sisa_dana'] ,0,',','.');  

            $this->mrTemplate->AddVars('resume_item', $resume);
            $this->mrTemplate->parseTemplate('resume_item', 'a'); 
         }

         // total all
         $totalAllResume['nominal_usulan']           = isset($totalAll['nominal_usulan']) ? $totalAll['nominal_usulan'] : 0;
         $totalAllResume['nominal_setuju']           = isset($totalAll['nominal_setuju']) ? $totalAll['nominal_setuju'] : 0;
         $totalAllResume['nominal_revisi']           = isset($totalAll['nominal_revisi']) ? $totalAll['nominal_revisi'] : 0;
         $totalAllResume['nominal_setelah_revisi']   = isset($totalAll['nominal_setelah_revisi']) ? $totalAll['nominal_setelah_revisi'] : 0;
         $totalAllResume['nominal_pencairan']        = isset($totalAll['nominal_pencairan']) ? $totalAll['nominal_pencairan'] : 0;
         $totalAllResume['nominal_realisasi']        = isset($totalAll['nominal_realisasi']) ? $totalAll['nominal_realisasi'] : 0;
         $totalAllResume['nominal_lppa']             = isset($totalAll['nominal_lppa']) ? $totalAll['nominal_lppa'] : 0;
         $totalAllResume['nominal_lppa_sisa']        = isset($totalAll['nominal_lppa_sisa']) ? $totalAll['nominal_lppa_sisa'] : 0;
         $totalAllResume['sisa_dana']                = isset($totalAll['sisa_dana']) ? $totalAll['sisa_dana'] : 0; 

         $this->mrTemplate->AddVar('resume', 'NOMINAL_USULAN', number_format($totalAllResume['nominal_usulan'],0,',','.'));
         $this->mrTemplate->AddVar('resume', 'NOMINAL_SETUJU', number_format($totalAllResume['nominal_setuju'],0,',','.'));
         $this->mrTemplate->AddVar('resume', 'NOMINAL_REVISI', number_format($totalAllResume['nominal_revisi'],0,',','.'));
         $this->mrTemplate->AddVar('resume', 'NOMINAL_SETELAH_REVISI', number_format($totalAllResume['nominal_setelah_revisi'],0,',','.'));
         $this->mrTemplate->AddVar('resume', 'NOMINAL_PENCAIRAN', number_format($totalAllResume['nominal_pencairan'],0,',','.'));
         $this->mrTemplate->AddVar('resume', 'NOMINAL_REALISASI', number_format($totalAllResume['nominal_realisasi'],0,',','.'));
         $this->mrTemplate->AddVar('resume', 'NOMINAL_LPPA', number_format($totalAllResume['nominal_lppa'],0,',','.'));
         $this->mrTemplate->AddVar('resume', 'NOMINAL_LPPA_SISA', number_format($totalAllResume['nominal_lppa_sisa'],0,',','.'));
         $this->mrTemplate->AddVar('resume', 'SISA_DANA', number_format($totalAllResume['sisa_dana'],0,',','.'));

      }
   }
}
?>