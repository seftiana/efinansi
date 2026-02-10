<?php

require_once GTFWConfiguration::GetValue('application', 'docroot') . 
'module/lap_rincian_pendapatan_per_unit/business/AppLapRincianPendapatanPerUnit.class.php';
      
require_once GTFWConfiguration::GetValue('application', 'docroot') . 
'module/user_unit_kerja/business/UserUnitKerja.class.php';

class ViewExcelLapRincianPendapatanPerUnit extends XlsResponse 
{
   public $mWorksheets = array('Data');
   
   public function GetFileName() 
   {
      // name it whatever you want
      return 'LapRincianPendapatanPerUnit-'.date('d-M-Y').'.xls';
   }
   
   public function ProcessRequest() 
   {  
      $Obj              = new AppLapRincianPendapatanPerUnit();
      $tahun_anggaran   = Dispatcher::Instance()->Decrypt($_GET['tgl']);
      $unitkerja_label  = Dispatcher::Instance()->Decrypt($_GET['unitkerja_label']);
      $unitkerja        = Dispatcher::Instance()->Decrypt($_GET['unitkerja']);
      $userId           = Dispatcher::Instance()->Decrypt($_GET['id']);
      $data             = $Obj->GetData($tahun_anggaran, $unitkerja); 
      $unitkerja        = $Obj->GetUnitKerja($unitkerjaId);
      $tahunanggaran    = $Obj->GetTahunAnggaran($tahun_anggaran);
      
      
      $tahunanggaran_nama  = $tahunanggaran['name'];
      $tahun_ta_anggar     = $tahunanggaran['tahun_tutup'];
      $unitkerja_nama      = $unitkerja_label;
      $penerimaan          = $tot_jumlah;
      $jumlah              = $tot_terima;
      
       if (empty($data)) {
         $this->mWorksheets['Data']->write(0, 0, 'Data kosong');
      } else {
         $fTitle       = $this->mrWorkbook->add_format();
         $fTitle->set_bold();
         $fTitle->set_size(12);
         $fTitle->set_align('vcenter');
          
         $fColKegiatan  = $this->mrWorkbook->add_format();
         $fColKegiatan->set_border(1);
         $fColKegiatan->set_bold();
         $fColKegiatan->set_size(10); 
         $fColKegiatan->set_align('center');
          
        
         //left
         $formatMerge1  = $this->mrWorkbook->add_format();
         $formatMerge1->set_bottom(1);
         $formatMerge1->set_top(1);
         $formatMerge1->set_left(1);
         $formatMerge1->set_right(0);
         $formatMerge1->set_size(10);
         $formatMerge1->set_align('left');
         //right
         $formatMerge2  = $this->mrWorkbook->add_format();
         $formatMerge2->set_bottom(1);
         $formatMerge2->set_top(1);
         $formatMerge2->set_left(0);
         $formatMerge2->set_right(1);
         $formatMerge2->set_size(10);
         $formatMerge2->set_align('left');
         
         
         $formatProgram    = $this->mrWorkbook->add_format();
         $formatProgram->set_border(1);
         //$formatProgram->set_bold();
         $formatProgram->set_size(10);
         $formatProgram->set_align('left');
         
         $formatProgram2 = $this->mrWorkbook->add_format();
         $formatProgram2->set_border(1);
         $formatProgram2->set_bold();
         $formatProgram2->set_size(10);
         $formatProgram2->set_align('left');
         
         $formatCurrencyProgram = $this->mrWorkbook->add_format();
         $formatCurrencyProgram->set_border(1);   
         //$formatCurrencyProgram->set_bold();
         $formatCurrencyProgram->set_size(10);
         $formatCurrencyProgram->set_align('right');
         
         $formatJumlah = $this->mrWorkbook->add_format();
         $formatJumlah->set_border(1);   
         $formatJumlah->set_bold();
         $formatJumlah->set_size(10);
         $formatJumlah->set_align('center');
         
         $formatCurrencyJumlah = $this->mrWorkbook->add_format();
         $formatCurrencyJumlah->set_border(1);   
         $formatCurrencyJumlah->set_bold();
         $formatCurrencyJumlah->set_size(10);
         $formatCurrencyJumlah->set_align('right');
         
         /**
          * set label
          */
          
         $lap_penerimaan_label   = GTFWConfiguration::GetValue(
            'language',
            'lap_rincian_pendapatan_per_unit_kerja'
         );
         $unit_sub_unit_label    = GTFWConfiguration::GetValue(
            'language',
            'unit'
         ).'/'.GTFWConfiguration::GetValue(
            'language',
            'sub_unit'
         );
          $tahun_periode_label   = GTFWConfiguration::GetValue('language','tahun_periode');
          
          $no_label              = GTFWConfiguration::GetValue('language','no');
          $unit_kerja_label      = GTFWConfiguration::GetValue('language','unit_kerja');
          
          $kode_label            = GTFWConfiguration::GetValue('language','kode');
          $target_label          = GTFWConfiguration::GetValue('language','target');
          $realisasi_label       = GTFWConfiguration::GetValue('language','realisasi');
          $ta_label              = GTFWConfiguration::GetValue('language','ta');
          $uraian_label          = GTFWConfiguration::GetValue('language','uraian');
          $pendapatan_pnbp_label       = GTFWConfiguration::GetValue('language','pendapatan_pnbp');
          $penerimaan_non_pnbp_label   = GTFWConfiguration::GetValue('language','penerimaan_non_pnbp');
          $total_label     = GTFWConfiguration::GetValue('language','total');
          $persen_label    ='%';
 
         /**
          * end set label
          */
         
         // label
         $this->mWorksheets['Data']->write(0, 0,$lap_penerimaan_label, $fTitle);
         $this->mWorksheets['Data']->write(2, 0, $tahun_periode_label.' : '.$tahunanggaran_nama);
         $this->mWorksheets['Data']->write(3, 0, $unit_sub_unit_label.' : '.$unitkerja_nama);
         // table head
         $num        = 6;
         $this->mWorksheets['Data']->merge_cells(6,0,7,0);
         $this->mWorksheets['Data']->merge_cells(6,1,7,2);
         $this->mWorksheets['Data']->merge_cells(6,3,6,5);
         
   
         $this->mWorksheets['Data']->set_column(0,0,15);
         $this->mWorksheets['Data']->write($num, 0, $kode_label, $fColKegiatan);
         $this->mWorksheets['Data']->set_column(1,1,3);
         $this->mWorksheets['Data']->write($num, 1, $uraian_label, $fColKegiatan);
         $this->mWorksheets['Data']->set_column(2,2,60);
         $this->mWorksheets['Data']->write($num, 2, '', $fColKegiatan);
         $this->mWorksheets['Data']->set_column(3,3,20);
         $this->mWorksheets['Data']->write($num, 3, $ta_label.' '.$tahunanggaran_nama, $fColKegiatan);
         $this->mWorksheets['Data']->write($num, 4, '', $fColKegiatan);
         $this->mWorksheets['Data']->write($num, 5, '', $fColKegiatan);
         $this->mWorksheets['Data']->write($num, 6, $ta_label.' '.($tahun_ta_anggar +1), $fColKegiatan);
         $num  = 7;
         
         $this->mWorksheets['Data']->write($num, 0, '', $fColKegiatan);
         $this->mWorksheets['Data']->write($num, 1, '', $fColKegiatan);
         $this->mWorksheets['Data']->write($num, 2, '', $fColKegiatan);
         $this->mWorksheets['Data']->set_column(3,3,16);
         $this->mWorksheets['Data']->write($num, 3, $target_label, $fColKegiatan);
         $this->mWorksheets['Data']->set_column(4,4,16);
         $this->mWorksheets['Data']->write($num, 4, $realisasi_label, $fColKegiatan);
         $this->mWorksheets['Data']->set_column(5,5,8);
         $this->mWorksheets['Data']->write($num, 5, $persen_label, $fColKegiatan);
         $this->mWorksheets['Data']->set_column(6,6,16);
         $this->mWorksheets['Data']->write($num, 6, $target_label, $fColKegiatan);
         $num  = 8;
         // end table headers

         $data_all      = $data;
         $data_per_unit = null;
         $nomor = 1;
         
         for($x = 0; $x < sizeof($data_all);$x++){
              
            $data_per_unit[$data_all[$x]['id_unit']]['id_unit']   = $data_all[$x]['id_unit'];
            $data_per_unit[$data_all[$x]['id_unit']]['kode_unit'] = $data_all[$x]['kode_unit'];
            $data_per_unit[$data_all[$x]['id_unit']]['nama_unit'] = $data_all[$x]['nama_unit'];
                
            if($data_all[$x]['kode']==''){
               continue;
            } else{
               if($data_all[$x]['sd']== 1){
                  $data_per_unit[$data_all[$x]['id_unit']]['data_pnbp'][]     = $data_all[$x];
               } else {
                  $data_per_unit[$data_all[$x]['id_unit']]['data_non_pnbp'][] = $data_all[$x];
               }
            }
         }
          
         foreach($data_per_unit as $key => $data_list){
            $numX    = $num;
            $num     += 2;
            /** untuk data pnbp */
            if(!empty($data_list['data_pnbp'])){
               
               for ($i=0; $i<sizeof($data_list['data_pnbp']);$i++) {
                  
                  $target_pnbp_depan                  = $Obj->GetNilaiProyeksi(
                     $data_list['data_pnbp'][$i]['target_pnbp']
                  );
                  $total_target_sekarang_pnbp[$key]   += $data_list['data_pnbp'][$i]['target_pnbp'];
                  $total_real_sekarang_pnbp[$key]     += $data_list['data_pnbp'][$i]['total_real'];
                  $persen_pnbp = $Obj->GetPersen(
                     $data_list['data_pnbp'][$i]['target_pnbp'],
                     $data_list['data_pnbp'][$i]['total_real']
                  );
                  $this->mWorksheets['Data']->write(
                     $num, 
                     0, 
                     $data_list['data_pnbp'][$i]['kode'], 
                     $formatProgram
                  );
                  $this->mWorksheets['Data']->write(
                     $num, 
                     1, 
                     '', 
                     $formatMerge1
                  );
                  $this->mWorksheets['Data']->write(
                     $num, 
                     2, 
                     ($i +1).'. '.$data_list['data_pnbp'][$i]['nama'], 
                     $formatMerge2
                  );
                  $this->mWorksheets['Data']->write(
                     $num, 
                     3, 
                     $data_list['data_pnbp'][$i]['target_pnbp'],
                     $formatCurrencyProgram
                  );
                  $this->mWorksheets['Data']->write(
                     $num, 
                     4, 
                     $data_list['data_pnbp'][$i]['total_real'], 
                     $formatCurrencyProgram
                  );
                  $this->mWorksheets['Data']->write(
                     $num, 
                     5, 
                     $persen_pnbp, 
                     $formatCurrencyProgram
                  );
                  $this->mWorksheets['Data']->write(
                     $num, 
                     6, 
                     $target_pnbp_depan, 
                     $formatCurrencyProgram
                  );
                  $num++;
               }
            }              

            $numX2   = $num;
            //$num++;
            /** untuk data non pnbp */
            $num += 1;
            if(!empty($data_list['data_non_pnbp'])){
               
               for ($i=0; $i<sizeof($data_list['data_non_pnbp']);$i++) {
                  $target_non_pnbp_depan = $Obj->GetNilaiProyeksi(
                     $data_list['data_non_pnbp'][$i]['target_pnbp']
                  );
                  $total_target_sekarang_non_pnbp[$key]  += $data_list['data_non_pnbp'][$i]['target_pnbp'];
                  $total_real_sekarang_non_pnbp[$key]    += $data_list['data_non_pnbp'][$i]['total_real'];
                  $persen_non_pnbp                       = $Obj->GetPersen(
                     $data_list['data_non_pnbp'][$i]['target_pnbp'],
                     $data_list['data_non_pnbp'][$i]['total_real']
                  );
                  $this->mWorksheets['Data']->write(
                     $num, 
                     0, 
                     $data_list['data_non_pnbp'][$i]['kode'],
                     $formatProgram
                  );
                  $this->mWorksheets['Data']->write(
                     $num, 
                     1, 
                     '',
                     $formatMerge1
                  );
                  $this->mWorksheets['Data']->write(
                     $num, 
                     2, 
                     ($i +1).'. '.$data_list['data_non_pnbp'][$i]['nama'], 
                     $formatMerge2
                  );
                  $this->mWorksheets['Data']->write(
                     $num, 
                     3, 
                     $data_list['data_non_pnbp'][$i]['target_pnbp'], 
                     $formatCurrencyProgram
                  );
                  $this->mWorksheets['Data']->write(
                     $num, 
                     4, 
                     $data_list['data_non_pnbp'][$i]['total_real'], 
                     $formatCurrencyProgram
                  );
                  $this->mWorksheets['Data']->write(
                     $num, 
                     5, 
                     $persen_non_pnbp, 
                     $formatCurrencyProgram
                  );
                  $this->mWorksheets['Data']->write(
                     $num, 
                     6, 
                     $target_non_pnbp_depan,
                     $formatCurrencyProgram
                  );
                  $num++;
               }
            }  
            
            $total_target_sekarang_non_pnbp[$key]  = (isset($total_target_sekarang_non_pnbp[$key]) ? $total_target_sekarang_non_pnbp[$key] : 0);
            $total_target_sekarang_pnbp[$key]      = (isset($total_target_sekarang_pnbp[$key]) ? $total_target_sekarang_pnbp[$key] : 0);
            $total_real_sekarang_non_pnbp[$key]    = (isset($total_real_sekarang_non_pnbp[$key]) ? $total_real_sekarang_non_pnbp[$key] : 0);
            $total_real_sekarang_pnbp[$key]        = (isset($total_real_sekarang_pnbp[$key]) ? $total_real_sekarang_pnbp[$key] : 0);
            
            $total_target_sekarang[$key]     = $total_target_sekarang_non_pnbp[$key] + $total_target_sekarang_pnbp[$key];
            $total_real_sekarang[$key]       = $total_real_sekarang_non_pnbp[$key] + $total_real_sekarang_pnbp[$key];
            
            $jml_total_target_pnbp_sekarang     += $total_target_sekarang_pnbp[$key] ;
            $jml_total_target_non_pnbp_sekarang += $total_target_sekarang_non_pnbp[$key] ;
            
            $jml_total_real_pnbp_sekarang       += $total_real_sekarang_pnbp[$key];
            $jml_total_real_non_pnbp_sekarang   += $total_real_sekarang_non_pnbp[$key];
            
            $this->mWorksheets['Data']->merge_cells($numX,1,$numX,2);
            $this->mWorksheets['Data']->write(
               $numX, 
               0, 
               $data_list['kode_unit'], 
               $formatProgram2
            );
            $this->mWorksheets['Data']->write(
               $numX, 
               2, 
               '', 
               $formatProgram2
            );
            $this->mWorksheets['Data']->write(
               $numX, 
               1, 
               $data_list['nama_unit'],  
               $formatProgram2
            );
            $this->mWorksheets['Data']->write(
               $numX, 
               3, 
               $total_target_sekarang[$key],  
               $formatCurrencyJumlah
            );
            $this->mWorksheets['Data']->write(
               $numX, 
               4, 
               $total_real_sekarang[$key], 
               $formatCurrencyJumlah
            );
            $this->mWorksheets['Data']->write(
               $numX, 
               5, 
               $Obj->GetPersen($total_target_sekarang[$key],$total_real_sekarang[$key]),
               $formatCurrencyJumlah
            );
            $this->mWorksheets['Data']->write(
               $numX, 
               6, 
               $Obj->GetNilaiProyeksi($total_target_sekarang[$key]),
               $formatCurrencyJumlah
            );
            $numX += 1;
            $this->mWorksheets['Data']->merge_cells($numX,1,$numX,2);
            $this->mWorksheets['Data']->write(
               $numX, 
               0, 
               '', 
               $formatProgram
            );
            $this->mWorksheets['Data']->write(
               $numX, 
               2, 
               '', 
               $formatProgram
            );
            $this->mWorksheets['Data']->write(
               $numX, 
               1, 
               'A. '.$pendapatan_pnbp_label,  
               $formatProgram
            );
            $this->mWorksheets['Data']->write(
               $numX, 
               3, 
               $total_target_sekarang_pnbp[$key],  
               $formatCurrencyProgram
            );
            $this->mWorksheets['Data']->write(
               $numX, 
               4, 
               $total_real_sekarang_pnbp[$key],  
               $formatCurrencyProgram
            );
            $this->mWorksheets['Data']->write(
               $numX, 
               5, 
               $Obj->GetPersen($total_target_sekarang_pnbp[$key], $total_real_sekarang_pnbp[$key]), 
               $formatCurrencyJumlah
            );
            $this->mWorksheets['Data']->write(
               $numX, 
               6, 
               $Obj->GetNilaiProyeksi($total_target_sekarang_pnbp[$key]),
               $formatCurrencyProgram
            );
            
            $this->mWorksheets['Data']->merge_cells($numX2,1,$numX2,2);
            $this->mWorksheets['Data']->write(
               $numX2, 
               0, 
               '',  
               $formatProgram
            );
            $this->mWorksheets['Data']->write(
               $numX2, 
               2, 
               '', 
               $formatProgram
            );
            $this->mWorksheets['Data']->write(
               $numX2, 
               1, 
               'B. '.$penerimaan_non_pnbp_label, 
               $formatProgram
            );
            $this->mWorksheets['Data']->write(
               $numX2, 
               3, 
               $total_target_sekarang_non_pnbp[$key], 
               $formatCurrencyProgram
            );
            $this->mWorksheets['Data']->write(
               $numX2, 
               4, 
               $total_real_sekarang_non_pnbp[$key], 
               $formatCurrencyProgram
            );
            $this->mWorksheets['Data']->write(
               $numX2, 
               5, 
               $Obj->GetPersen(
                  $total_target_sekarang_non_pnbp[$key],
                  $total_real_sekarang_non_pnbp[$key]
               ),
               $formatCurrencyJumlah
            );
            $this->mWorksheets['Data']->write(
               $numX2, 
               6, 
               $Obj->GetNilaiProyeksi(
                  $total_target_sekarang_non_pnbp[$key]
               ), 
               $formatCurrencyProgram
            );
            
          }
         $this->mWorksheets['Data']->merge_cells($num,0,$num,2);
         $this->mWorksheets['Data']->write($num, 0, '',  $formatProgram2);
         $this->mWorksheets['Data']->write($num, 1, '', $formatProgram2);
         $this->mWorksheets['Data']->write($num, 2, '', $formatProgram2);
         $this->mWorksheets['Data']->write($num, 3, '',   $formatCurrencyJumlah);
         $this->mWorksheets['Data']->write($num, 4, '',   $formatCurrencyJumlah);
         $this->mWorksheets['Data']->write($num, 5, '',  $formatProgram2);
         $this->mWorksheets['Data']->write($num, 6, '', $formatCurrencyJumlah); 
         $num++;
         $this->mWorksheets['Data']->merge_cells($num,0,$num,2);
         $this->mWorksheets['Data']->write($num, 2, '',  $formatProgram2);
         $this->mWorksheets['Data']->write($num, 1, '', $formatProgram2);
         $this->mWorksheets['Data']->write($num, 0, $total_label.' '.$pendapatan_pnbp_label, $formatProgram2);
         $this->mWorksheets['Data']->write($num, 3, $jml_total_target_pnbp_sekarang,   $formatCurrencyJumlah);
         $this->mWorksheets['Data']->write($num, 4, $jml_total_real_pnbp_sekarang,   $formatCurrencyJumlah);
         $this->mWorksheets['Data']->write(
            $num, 
            5, 
            $Obj->GetPersen($jml_total_target_pnbp_sekarang,$jml_total_real_pnbp_sekarang), 
            $formatCurrencyJumlah
         );
         $this->mWorksheets['Data']->write(
            $num, 
            6, 
            $Obj->GetNilaiProyeksi($jml_total_target_pnbp_sekarang), 
            $formatCurrencyJumlah
         );
         $num++;
         $this->mWorksheets['Data']->merge_cells($num,0,$num,2);
         $this->mWorksheets['Data']->write($num, 2, '',  $formatProgram2);
         $this->mWorksheets['Data']->write($num, 1, '', $formatProgram);
         $this->mWorksheets['Data']->write($num, 0, $total_label.' '.$penerimaan_non_pnbp_label, $formatProgram2);
         $this->mWorksheets['Data']->write($num, 3, $jml_total_target_non_pnbp_sekarang,   $formatCurrencyJumlah);
         $this->mWorksheets['Data']->write($num, 4, $jml_total_real_non_pnbp_sekarang,   $formatCurrencyJumlah);
         $this->mWorksheets['Data']->write(
            $num, 
            5, 
            $Obj->GetPersen($jml_total_target_non_pnbp_sekarang,$jml_total_real_non_pnbp_sekarang),  
            $formatCurrencyJumlah
         );
         $this->mWorksheets['Data']->write(
            $num,
            6, 
            $Obj->GetNilaiProyeksi($jml_total_target_non_pnbp_sekarang[$key]), 
            $formatCurrencyJumlah
         );
      }        
   }

}
?>