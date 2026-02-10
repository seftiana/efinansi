<?php

/**
 * @package lap_realisasi_penerimaan_pnbp_pusat
 * Class ViewExcelLapPenerimaanPNBPPusat
 * @since 27 Maret 2012
 * @copyright 2012 Gamatechno
 */
 
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
		'module/lap_realisasi_penerimaan_pnbp_pusat/business/AppLapPenerimaanPNBPPusat.class.php';
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
		'module/user_unit_kerja/business/UserUnitKerja.class.php';

class ViewExcelLapPenerimaanPNBPPusat extends XlsResponse 
{

   public $mWorksheets = array('Data');
   
   public function GetFileName() 
   {
      // name it whatever you want
      $string_nama = $this->L('lap_penerimaan_pnbp_alokasi_pusat');
      return str_replace(' ','_',$string_nama).date('d-m-Y').'.xls';
   }

   public function L($indexLangName = '')
   {
   		$lang = GTFWConfiguration::GetValue('language',$indexLangName);
   		if(!empty($lang)){
   			return $lang;	
   		}
   		return '';
   }
   
   public function ProcessRequest() 
   {
		$Obj = new AppLapPenerimaanPNBPPusat();
		$tahun_anggaran = Dispatcher::Instance()->Decrypt($_GET['tgl']);
        
		$data = $Obj->GetDataRealisasiPNBPCetak($tahun_anggaran); 
		
		$tahunanggaran = $Obj->GetTahunAnggaran($tahun_anggaran);
        
		$tahunanggaran_nama = $tahunanggaran['name'];
		$unitkerja_nama = $Obj->GetUnitKerjaPusat();
		
		 if (empty($data)) {
			$this->mWorksheets['Data']->write(0, 0, 'Data kosong');
		} else {
			 $fTitle = $this->mrWorkbook->add_format();
			 $fTitle->set_bold();
			 $fTitle->set_size(12);
			 $fTitle->set_align('vcenter');
			 
			 $fColKegiatan = $this->mrWorkbook->add_format();
			 $fColKegiatan->set_border(1);
			 $fColKegiatan->set_bold();
			 $fColKegiatan->set_size(12);	
			 $fColKegiatan->set_align('center');
			 

			$formatProgram = $this->mrWorkbook->add_format();
			$formatProgram->set_border(1);
			//$formatProgram->set_bold();
			$formatProgram->set_size(11);
			$formatProgram->set_align('left');
			
			$formatProgram2 = $this->mrWorkbook->add_format();
			$formatProgram2->set_border(1);
			$formatProgram2->set_bold();
			$formatProgram2->set_size(11);
			$formatProgram2->set_align('left');

			$formatCurrencyProgram = $this->mrWorkbook->add_format();
			$formatCurrencyProgram->set_border(1);   
			//$formatCurrencyProgram->set_bold();
			$formatCurrencyProgram->set_size(11);
			$formatCurrencyProgram->set_align('right');
			
			$formatJumlah = $this->mrWorkbook->add_format();
			$formatJumlah->set_border(1);   
			$formatJumlah->set_bold();
			$formatJumlah->set_size(12);
			$formatJumlah->set_align('center');
		   
			$formatCurrencyJumlah = $this->mrWorkbook->add_format();
			$formatCurrencyJumlah->set_border(1);   
			$formatCurrencyJumlah->set_bold();
			$formatCurrencyJumlah->set_size(11);
			$formatCurrencyJumlah->set_align('right');
			
            $fColHeader = $this->mrWorkbook->add_format();
			$fColHeader->set_border(1);
			$fColHeader->set_bold();
			$fColHeader->set_size(12);	
            $fColHeader->set_text_wrap();
			$fColHeader->set_align('center');
            $fColHeader->set_align('vcenter');
            
            $fColMerge = $this->mrWorkbook->add_format();
			$fColMerge->set_border(1);
			$fColMerge->set_bold();
			$fColMerge->set_size(12);
            $fColMerge->set_merge();
             
			/**
			 * set label
			 */
			 
			$lap_penerimaan_label = $this->L('lap_penerimaan_pnbp_alokasi_pusat');
			$unit_sub_unit_label = $this->L('unit').'/'. $this->L('sub_unit');
		    $tahun_periode_label = $this->L('tahun_periode');		    
		    $no_label = $this->L('no');
		    $kode_label = $this->L('kode');
		    $unit_kerja_label = $this->L('unit_kerja');
		    $jenis_penerimaan_label = $this->L('jenis_penerimaan');
		    $target_pnbp_label = $this->L('target_pnbp');
		    $realisasi_pnbp_label = $this->L('realisasi_pnbp');
		    $total_label  = $this->L('total');
		    $januarai_label = $this->L('januari');
		    $februari_label = $this->L('februari');
		    $maret_label = $this->L('maret');
		    $april_label = $this->L('april');
			$mei_label = $this->L('mei');
		    $juni_label = $this->L('juni');
		    $juli_label = $this->L('juli');
		    $agustus_label = $this->L('agustus');
		    $september_label = $this->L('september');
		    $oktober_label = $this->L('oktober');
		    $november_label = $this->L('november');
		    $desember_label = $this->L('desember');
		    $total_realisasi_label = $this->L('total_realisasi');
		    
		    
		   	
		   	
			/**
			 * end set label
			 */
			
		   $this->mWorksheets['Data']->write(0, 0, $lap_penerimaan_label, $fTitle);
		   $this->mWorksheets['Data']->write(2, 0, $tahun_periode_label.' : '.$tahunanggaran_nama);
		   $this->mWorksheets['Data']->write(3, 0, $unit_sub_unit_label.' : '.$unitkerja_nama);

	    	$num=6;
			$this->mWorksheets['Data']->merge_cells(6,0,7,0);
			$this->mWorksheets['Data']->merge_cells(6,1,7,1);
			$this->mWorksheets['Data']->merge_cells(6,2,7,2);
			$this->mWorksheets['Data']->merge_cells(6,3,7,3);
			$this->mWorksheets['Data']->merge_cells(6,4,6,15);
			$this->mWorksheets['Data']->merge_cells(6,16,7,16);
			
			$this->mWorksheets['Data']->set_column(0,0,8);
			$this->mWorksheets['Data']->write($num, 0, $no_label, $fColHeader);
			$this->mWorksheets['Data']->set_column(1,1,17);
			$this->mWorksheets['Data']->write($num, 1, $kode_label, $fColHeader);
			$this->mWorksheets['Data']->set_column(2,2,60);
			$this->mWorksheets['Data']->write($num, 2, $unit_kerja_label.' / '.
										$jenis_penerimaan_label, $fColHeader);
			$this->mWorksheets['Data']->set_column(3,3,20);
			$this->mWorksheets['Data']->write($num, 3, $target_pnbp_label, $fColHeader);
			$this->mWorksheets['Data']->write($num, 4, $realisasi_pnbp_label, $fColMerge);
			$this->mWorksheets['Data']->write($num, 5, '',  $fColMerge);
			$this->mWorksheets['Data']->write($num, 6, '',  $fColMerge);
			$this->mWorksheets['Data']->write($num, 7, '',  $fColMerge);
			$this->mWorksheets['Data']->write($num, 8, '',  $fColMerge);
			$this->mWorksheets['Data']->write($num, 9, '',  $fColMerge);
			$this->mWorksheets['Data']->write($num, 10, '',  $fColMerge);
			$this->mWorksheets['Data']->write($num, 11, '',  $fColMerge);
			$this->mWorksheets['Data']->write($num, 12, '',  $fColMerge);
			$this->mWorksheets['Data']->write($num, 13, '',  $fColMerge);
			$this->mWorksheets['Data']->write($num, 14, '',  $fColMerge);
			$this->mWorksheets['Data']->write($num, 15, '',  $fColMerge);
			$this->mWorksheets['Data']->set_column(16,16,20);
			$this->mWorksheets['Data']->write($num, 16, $total_realisasi_label, $fColHeader);
			$num=7;
            $this->mWorksheets['Data']->merge_cells(6,0,7,0);
			$this->mWorksheets['Data']->merge_cells(6,1,7,1);
			$this->mWorksheets['Data']->merge_cells(6,2,7,2);
			$this->mWorksheets['Data']->merge_cells(6,3,7,3);
			$this->mWorksheets['Data']->merge_cells(6,16,7,16);
			$this->mWorksheets['Data']->write($num, 0, '', $fColHeader);
			$this->mWorksheets['Data']->write($num, 1, '', $fColHeader);
			$this->mWorksheets['Data']->write($num, 2, '', $fColHeader);
			$this->mWorksheets['Data']->write($num, 3, '', $fColHeader);
			$this->mWorksheets['Data']->set_column(4,4,16);
			$this->mWorksheets['Data']->write($num, 4, $januarai_label, $fColKegiatan);
			$this->mWorksheets['Data']->set_column(5,5,16);
			$this->mWorksheets['Data']->write($num, 5, $februari_label, $fColKegiatan);
			$this->mWorksheets['Data']->set_column(6,6,16);
			$this->mWorksheets['Data']->write($num, 6, $maret_label, $fColKegiatan);
			$this->mWorksheets['Data']->set_column(7,7,16);
			$this->mWorksheets['Data']->write($num, 7, $april_label, $fColKegiatan);
			$this->mWorksheets['Data']->set_column(8,8,16);
			$this->mWorksheets['Data']->write($num, 8, $mei_label, $fColKegiatan);
			$this->mWorksheets['Data']->set_column(9,9,16);
			$this->mWorksheets['Data']->write($num, 9, $juni_label, $fColKegiatan);
			$this->mWorksheets['Data']->set_column(10,10,16);
			$this->mWorksheets['Data']->write($num, 10, $juli_label, $fColKegiatan);
			$this->mWorksheets['Data']->set_column(11,11,16);
			$this->mWorksheets['Data']->write($num, 11, $agustus_label, $fColKegiatan);
			$this->mWorksheets['Data']->set_column(12,12,16);
			$this->mWorksheets['Data']->write($num, 12, $september_label, $fColKegiatan);
			$this->mWorksheets['Data']->set_column(13,13,16);
			$this->mWorksheets['Data']->write($num, 13, $oktober_label, $fColKegiatan);
			$this->mWorksheets['Data']->set_column(14,14,16);
			$this->mWorksheets['Data']->write($num, 14, $november_label, $fColKegiatan);
			$this->mWorksheets['Data']->set_column(15,15,16);
			$this->mWorksheets['Data']->write($num, 15,$desember_label, $fColKegiatan);
			$this->mWorksheets['Data']->write($num, 16, '', $fColHeader);
            $num += 1;
			$data_list = $data;
			 for ($i=0; $i<sizeof($data_list);) {
					
			 if(($data_list[$i]['kode_satker'] == $kode_satker) && ($data_list[$i]['kode_unit'] == $kode_unit)) {
					if($data_list[$i]['idrencana'] == "") {
						$i++; continue;
					}
					$send = $data_list[$i];
					$send['nomor'] = $no;
					$send['format'] = $formatProgram;

					$i++;$no++;
				 } elseif($data_list[$i]['kode_satker'] != $kode_satker && 
                        $data_list[$i]['nama_satker'] == $data_list[$i]['nama_unit']) {
					 $kode_satker = $data_list[$i]['kode_satker'];
					 $kode_unit = $data_list[$i]['kode_unit'];
					 $nama_satker = $data_list[$i]['nama_satker'];
					 $nama_unit = $data_list[$i]['nama_unit'];
					 $send['kode'] = $kode_unit;
					 $send['nama'] = $data_list[$i]['nama_unit'];
					 
					 $send['target_pnbp'] = "";
					 $send['realjan'] = "";
					 $send['realfeb'] = "";
					 $send['realmar'] = "";
					 $send['realapr'] = "";
					 $send['realmei'] = "";
					 $send['realjun'] = "";
					 $send['realjul'] = "";
					 $send['realags'] = "";
					 $send['realsep'] = "";
					 $send['realokt'] = "";
					 $send['realnov'] = "";
					 $send['realdes'] = "";
					 $send['total_realisasi'] = "";
                     $send['alokasi_unit'] = "";
                     $send['alokasi_pusat'] = "";
					 //print_r($send['jumlah_total']."<br/>");
					 
					 $send['nomor'] = "";
					 $send['format'] =$formatProgram2;
						
					 $no=1;
					// }
				 } elseif($data_list[$i]['kode_unit'] != $kode_unit) {
					 $kode_satker = $data_list[$i]['kode_satker'];
					 $kode_unit = $data_list[$i]['kode_unit'];
					 $nama_satker = $data_list[$i]['nama_satker'];
					 $nama_unit = $data_list[$i]['nama_unit'];
					 $send['kode'] = $kode_unit;
					 $send['nama'] = $data_list[$i]['nama_unit'];
					 $send['target_pnbp'] = "";
					 $send['realjan'] = "";
					 $send['realfeb'] = "";
					 $send['realmar'] = "";
					 $send['realapr'] = "";
					 $send['realmei'] = "";
					 $send['realjun'] = "";
					 $send['realjul'] = "";
					 $send['realags'] = "";
					 $send['realsep'] = "";
					 $send['realokt'] = "";
					 $send['realnov'] = "";
					 $send['realdes'] = "";
					 $send['total_realisasi'] = "";
					 $send['tarif'] = "";
					 $send['nomor'] = "";
					 $send['format'] =$formatProgram2;

					 $no=1;
				 }	
				$this->mWorksheets['Data']->write($num, 0, $send['nomor'], $send['format']);
				$this->mWorksheets['Data']->write($num, 1, $send['kode'], $send['format']);
				$this->mWorksheets['Data']->write($num, 2, $send['nama'], $send['format']);
				$this->mWorksheets['Data']->write($num, 3, $send['target_pnbp'], $formatCurrencyProgram);
				$this->mWorksheets['Data']->write($num, 4, $send['realjan'], $formatCurrencyProgram);
				$this->mWorksheets['Data']->write($num, 5, $send['realfeb'], $formatCurrencyProgram);
				$this->mWorksheets['Data']->write($num, 6, $send['realmar'], $formatCurrencyProgram);
				$this->mWorksheets['Data']->write($num, 7, $send['realapr'], $formatCurrencyProgram);
				$this->mWorksheets['Data']->write($num, 8, $send['realmei'], $formatCurrencyProgram);
				$this->mWorksheets['Data']->write($num, 9, $send['realjun'], $formatCurrencyProgram);
				$this->mWorksheets['Data']->write($num, 10, $send['realjul'], $formatCurrencyProgram);
				$this->mWorksheets['Data']->write($num, 11, $send['realags'], $formatCurrencyProgram);
				$this->mWorksheets['Data']->write($num, 12, $send['realsep'], $formatCurrencyProgram);
				$this->mWorksheets['Data']->write($num, 13, $send['realokt'], $formatCurrencyProgram);
				$this->mWorksheets['Data']->write($num, 14, $send['realnov'], $formatCurrencyProgram);
				$this->mWorksheets['Data']->write($num, 15, $send['realdes'], $formatCurrencyProgram);
				$this->mWorksheets['Data']->write($num, 16, $send['total_realisasi'], $formatCurrencyProgram);
				$num++;
			}
			
			$total_target = 0;
			$total_jan = 0;
			$total_feb = 0;
			$total_mar = 0;
			$total_apr = 0;
			$total_mei = 0;
			$total_jun = 0;
			$total_jul = 0;
			$total_ags = 0;
			$total_sep = 0;
			$total_okt = 0;
			$total_nov = 0;
			$total_des = 0;
			$total_real = 0;
			foreach($data_list as $key => $value){
				$total_target+= $value['target_pnbp'];
			   $total_jan+= $value['realJan'];
			   $total_feb+= $value['realFeb'];
			   $total_mar+= $value['realMar'];
			   $total_apr+= $value['realApr'];
			   $total_mei+= $value['realMei'];
			   $total_jun+= $value['realJun'];
			   $total_jul+= $value['realJul'];
			   $total_ags+= $value['realAgs'];
			   $total_sep+= $value['realSep'];
			   $total_okt+= $value['realOkt'];
			   $total_nov+= $value['realNov'];
			   $total_des+= $value['realDes'];
			   $total_real+= $value['total_realisasi'];
         }
       			$this->mWorksheets['Data']->merge_cells($num,0,$num,2);
         		$this->mWorksheets['Data']->write($num, 0, '', $formatJumlah);
				$this->mWorksheets['Data']->write($num, 1, '', $formatJumlah);
				$this->mWorksheets['Data']->write($num, 2, $total_label, $formatJumlah);
				$this->mWorksheets['Data']->write($num, 3, $total_target, $formatCurrencyJumlah);
				$this->mWorksheets['Data']->write($num, 4, $total_jan, $formatCurrencyJumlah);
				$this->mWorksheets['Data']->write($num, 5, $total_feb, $formatCurrencyJumlah);
				$this->mWorksheets['Data']->write($num, 6, $total_mar, $formatCurrencyJumlah);
				$this->mWorksheets['Data']->write($num, 7, $total_apr, $formatCurrencyJumlah);
				$this->mWorksheets['Data']->write($num, 8, $total_mei, $formatCurrencyJumlah);
				$this->mWorksheets['Data']->write($num, 9, $total_jun, $formatCurrencyJumlah);
				$this->mWorksheets['Data']->write($num, 10, $total_jul, $formatCurrencyJumlah);
				$this->mWorksheets['Data']->write($num, 11, $total_ags, $formatCurrencyJumlah);
				$this->mWorksheets['Data']->write($num, 12, $total_sep, $formatCurrencyJumlah);
				$this->mWorksheets['Data']->write($num, 13, $total_okt, $formatCurrencyJumlah);
				$this->mWorksheets['Data']->write($num, 14, $total_nov, $formatCurrencyJumlah);
				$this->mWorksheets['Data']->write($num, 15, $total_des, $formatCurrencyJumlah);
				$this->mWorksheets['Data']->write($num, 16, $total_real, $formatCurrencyJumlah);

		}
	}

}
