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
                  break;
               case 'KEGIATAN':
                  $list['nominal_usulan']    = number_format($dataRekap[$list['kode_sistem']]['nominal_usulan'], 0, ',','.');
                  $list['nominal_setuju']    = number_format($dataRekap[$list['kode_sistem']]['nominal_setuju'], 0, ',','.');
                  $list['nominal_revisi']    = number_format($dataRekap[$list['kode_sistem']]['nominal_revisi'], 0, ',','.');
                  $list['nominal_setelah_revisi']    = number_format($dataRekap[$list['kode_sistem']]['nominal_setelah_revisi'], 0, ',','.');
                  $list['nominal_pencairan'] = number_format($dataRekap[$list['kode_sistem']]['nominal_pencairan'], 0, ',','.');
                  $list['nominal_realisasi'] = number_format($dataRekap[$list['kode_sistem']]['nominal_realisasi'], 0, ',','.');
                  $list['sisa_dana']         = number_format($dataRekap[$list['kode_sistem']]['sisa_dana'], 0, ',','.');
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

         foreach ($dataResume as $resume) {
            $resume['nominal_usulan']     = number_format($resume['nominal_usulan'], 0, ',','.');
            $resume['nominal_setuju']     = number_format($resume['nominal_setuju'], 0, ',','.');
            $resume['nominal_revisi']  = number_format($resume['nominal_revisi'], 0, ',','.');
            $resume['nominal_setelah_revisi']  = number_format($resume['nominal_setelah_revisi'], 0, ',','.');
            $resume['nominal_pencairan']  = number_format($resume['nominal_pencairan'], 0, ',','.');
            $resume['nominal_realisasi']  = number_format($resume['nominal_realisasi'], 0, ',','.');
            $resume['sisa_dana']          = number_format($resume['sisa_dana'], 0, ',','.');
            $this->mrTemplate->AddVars('resume_item', $resume);
            $this->mrTemplate->parseTemplate('resume_item', 'a');
         }
      }
   }
}
?>