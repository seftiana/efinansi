<?php
/**
 * @package lap_rencana_penerimaan_alokasi_unit
 * @since 24 Februari 2012
 * @copyright (c) 2012 Gamatechno
 */
 
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
		'module/lap_rencana_penerimaan_alokasi_unit_v2/business/AppLapRencanaPenerimaanAlokasiUnit.class.php';
		
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
		'module/user_unit_kerja/business/UserUnitKerja.class.php';

class ViewExcelLapRencanaPenerimaanAlokasiUnit extends XlsResponse 
{

   var $mWorksheets = array('Data');
   
   public function GetFileName() 
   {
      // name it whatever you want
      return 'LapRencanaPenerimaanAlokasiUnit'.date('d-m-Y').'.xls';
   }
	
	public function ProcessRequest() 
	{
		$Obj = new AppLapRencanaPenerimaanAlokasiUnit();
		$UserUnitKerja = new UserUnitKerja();
		$tahun_anggaran = Dispatcher::Instance()->Decrypt($_GET['tgl']);
		$unitkerja_label = Dispatcher::Instance()->Decrypt($_GET['unitkerja_label']);
		$unitkerja = Dispatcher::Instance()->Decrypt($_GET['unitkerja']);
		$kodePenerimaanId = Dispatcher::Instance()->Decrypt($_GET['kode_penerimaan_id']);
		$userId = Dispatcher::Instance()->Decrypt($_GET['id']);
		
		$data = $Obj->GetDataRencanaPenerimaan($tahun_anggaran, $unitkerja,$kodePenerimaanId);
		$unitkerja = $UserUnitKerja->GetUnitKerja($unitkerja);
		$tahunanggaran = $Obj->GetTahunAnggaran($tahun_anggaran);
		$unitkerja_nama = $unitkerja['unit_kerja_nama'];
		$tahunanggaran_nama = $tahunanggaran['name'];
		
		
		$data_kosong = GTFWConfiguration::GetValue('language','data_kosong');
		 if (empty($data)) {
			$this->mWorksheets['Data']->write(0, 0, $data_kosong);
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
			 
			 $fColMerge = $this->mrWorkbook->add_format();
			 $fColMerge->set_border(1);
			 $fColMerge->set_bold();
			 $fColMerge->set_size(12);	
			 $fColMerge->set_align('center');
			 
			 
			$formatProgram = $this->mrWorkbook->add_format();
			$formatProgram->set_border(1);
			//$formatProgram->set_bold();
			$formatProgram->set_size(11);
			$formatProgram->set_align('left');
			$formatProgram->set_align('vcenter');
			$formatProgram->set_text_wrap();
			
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
			$formatCurrencyProgram->set_align('vcenter');
			$formatCurrencyProgram->set_text_wrap();
			$formatCurrencyProgram->set_num_format(3);
			
			$formatJumlah = $this->mrWorkbook->add_format();
			$formatJumlah->set_border(1);   
			$formatJumlah->set_bold();
			$formatJumlah->set_size(12);
			$formatJumlah->set_align('center');
			$formatJumlah->set_num_format(3);
			
		   
			$formatCurrencyJumlah = $this->mrWorkbook->add_format();
			$formatCurrencyJumlah->set_border(1);   
			$formatCurrencyJumlah->set_bold();
			$formatCurrencyJumlah->set_size(11);
			$formatCurrencyJumlah->set_align('right');
			$formatCurrencyJumlah->set_num_format(3);
			
			/**
			 * set label
			 */
			 
			$lap_penerimaan_label = GTFWConfiguration::GetValue('language','lap_rencana_penerimaan_alokasi_unit');
			$unit_sub_unit_label = GTFWConfiguration::GetValue('language','unit').'/'.
							 GTFWConfiguration::GetValue('language','sub_unit');
		    $tahun_periode_label = GTFWConfiguration::GetValue('language','tahun_periode');
		    
		    $no_label = GTFWConfiguration::GetValue('language','no');
		    $kode_label = GTFWConfiguration::GetValue('language','kode_penerimaan');
		    $unit_kerja_label = GTFWConfiguration::GetValue('language','unit_kerja');
		    $jenis_penerimaan_label = GTFWConfiguration::GetValue('language','jenis_penerimaan');
		    $t_penerimaan_label = GTFWConfiguration::GetValue('language','total_penerimaan');
		    $dist_penerimaan_label = GTFWConfiguration::GetValue('language','distribusi_penerimaan');
		    $total_label  = GTFWConfiguration::GetValue('language','total');
		    $januarai_label = GTFWConfiguration::GetValue('language','januari');
		    $februari_label = GTFWConfiguration::GetValue('language','februari');
		    $maret_label = GTFWConfiguration::GetValue('language','maret');
		    $april_label = GTFWConfiguration::GetValue('language','april');
			$mei_label = GTFWConfiguration::GetValue('language','mei');
		    $juni_label = GTFWConfiguration::GetValue('language','juni');
		    $juli_label = GTFWConfiguration::GetValue('language','juli');
		    $agustus_label = GTFWConfiguration::GetValue('language','agustus');
		    $september_label = GTFWConfiguration::GetValue('language','september');
		    $oktober_label = GTFWConfiguration::GetValue('language','oktober');
		    $november_label = GTFWConfiguration::GetValue('language','november');
		    $desember_label = GTFWConfiguration::GetValue('language','desember');
		    $total_label = GTFWConfiguration::GetValue('language','total');
		    $persen_label = '%';
		    
		   	
		   	
			/**
			 * end set label
			 */
			 
		 	/**
			 * header
			 */
		    $this->mWorksheets['Data']->write(0, 0, $lap_penerimaan_label, $fTitle);
		   	$this->mWorksheets['Data']->write(2, 0, $tahun_periode_label.' : '.$tahunanggaran_nama);
		   	$this->mWorksheets['Data']->write(3, 0, $unit_sub_unit_label.' : '.$unitkerja_nama);
	    	$num=6;
			
			$this->mWorksheets['Data']->set_column(0,0,8);
			$this->mWorksheets['Data']->write($num, 0, $no_label, $fColMerge);
			$this->mWorksheets['Data']->set_column(1,1,60);
			$this->mWorksheets['Data']->write($num, 1,  $unit_kerja_label.' Sumber', $fColMerge);
			$this->mWorksheets['Data']->set_column(2,2,22);
			$this->mWorksheets['Data']->write($num, 2, $kode_label, $fColMerge);
			$this->mWorksheets['Data']->set_column(3,3,50);
			$this->mWorksheets['Data']->write($num, 3, $jenis_penerimaan_label, $fColMerge);
			$this->mWorksheets['Data']->set_column(4,4,60);
			$this->mWorksheets['Data']->write($num, 4, $unit_kerja_label, $fColMerge);
		 	$this->mWorksheets['Data']->set_column(5,5,20);
			$this->mWorksheets['Data']->write($num, 5, $t_penerimaan_label, $fColMerge);
			//$this->mWorksheets['Data']->write($num, 5, '', $fColKegiatan);
			$this->mWorksheets['Data']->write($num, 6,  $dist_penerimaan_label, $fColKegiatan);
			//$this->mWorksheets['Data']->write($num, 7, '', $fColKegiatan);
			$this->mWorksheets['Data']->write($num, 7, '', $fColKegiatan);
			//$this->mWorksheets['Data']->write($num, 9, '', $fColKegiatan);
			$this->mWorksheets['Data']->write($num, 8, '', $fColKegiatan);
			//$this->mWorksheets['Data']->write($num, 11, '', $fColKegiatan);
			$this->mWorksheets['Data']->write($num, 9, '', $fColKegiatan);
			//$this->mWorksheets['Data']->write($num, 13, '', $fColKegiatan);
			$this->mWorksheets['Data']->write($num, 10, '', $fColKegiatan);
			//$this->mWorksheets['Data']->write($num, 15, '', $fColKegiatan);
			$this->mWorksheets['Data']->write($num, 11,'', $fColKegiatan);
			//$this->mWorksheets['Data']->write($num, 17, '', $fColKegiatan);
			$this->mWorksheets['Data']->write($num, 12, '', $fColKegiatan);
			//$this->mWorksheets['Data']->write($num, 19, '', $fColKegiatan);
			$this->mWorksheets['Data']->write($num, 13, '', $fColKegiatan);
			//$this->mWorksheets['Data']->write($num, 21, '', $fColKegiatan);
			$this->mWorksheets['Data']->write($num, 14, '', $fColKegiatan);
			//$this->mWorksheets['Data']->write($num, 23, '', $fColKegiatan);
			$this->mWorksheets['Data']->write($num, 15, '', $fColKegiatan);
			//$this->mWorksheets['Data']->write($num, 25, '', $fColKegiatan);
			$this->mWorksheets['Data']->write($num, 16, '', $fColKegiatan);
			//$this->mWorksheets['Data']->write($num, 27, '', $fColKegiatan);
			$this->mWorksheets['Data']->write($num, 17, '', $fColKegiatan);
			$num=7;
			$this->mWorksheets['Data']->write($num, 0, '', $fColKegiatan);
			$this->mWorksheets['Data']->write($num, 1, '', $fColKegiatan);
			$this->mWorksheets['Data']->write($num, 2, '', $fColKegiatan);
			$this->mWorksheets['Data']->write($num, 3, '', $fColKegiatan);
			$this->mWorksheets['Data']->write($num, 4, '', $fColKegiatan);
			$this->mWorksheets['Data']->write($num, 5, '', $fColKegiatan);
			
			//$this->mWorksheets['Data']->set_column(5,5,4);
			//$this->mWorksheets['Data']->write($num, 5, $persen_label, $fColKegiatan);
			$this->mWorksheets['Data']->set_column(6,6,16);
			$this->mWorksheets['Data']->write($num, 6, $januarai_label, $fColKegiatan);
			
			//$this->mWorksheets['Data']->set_column(7,7,4);
			//$this->mWorksheets['Data']->write($num, 7, $persen_label, $fColKegiatan);
			$this->mWorksheets['Data']->set_column(7,7,16);
			$this->mWorksheets['Data']->write($num, 7, $februari_label, $fColKegiatan);
			
			//$this->mWorksheets['Data']->set_column(9,9,4);
			//$this->mWorksheets['Data']->write($num, 9, $persen_label, $fColKegiatan);
			$this->mWorksheets['Data']->set_column(8,8,16);
			$this->mWorksheets['Data']->write($num, 8, $maret_label, $fColKegiatan);
			
			//$this->mWorksheets['Data']->set_column(11,11,4);
			//$this->mWorksheets['Data']->write($num, 11, $persen_label, $fColKegiatan);
			$this->mWorksheets['Data']->set_column(9,9,16);
			$this->mWorksheets['Data']->write($num, 9, $april_label, $fColKegiatan);
			
			//$this->mWorksheets['Data']->set_column(13,13,4);
			//$this->mWorksheets['Data']->write($num, 13, $persen_label, $fColKegiatan);
			$this->mWorksheets['Data']->set_column(10,10,16);
			$this->mWorksheets['Data']->write($num, 10, $mei_label, $fColKegiatan);
			
			//$this->mWorksheets['Data']->set_column(15,15,4);
			//$this->mWorksheets['Data']->write($num, 15, $persen_label, $fColKegiatan);
			$this->mWorksheets['Data']->set_column(11,11,16);
			$this->mWorksheets['Data']->write($num, 11, $juni_label, $fColKegiatan);
			
			//$this->mWorksheets['Data']->set_column(17,17,4);
			//$this->mWorksheets['Data']->write($num, 17, $persen_label, $fColKegiatan);
			$this->mWorksheets['Data']->set_column(12,12,16);
			$this->mWorksheets['Data']->write($num, 12, $juli_label, $fColKegiatan);
			
			//$this->mWorksheets['Data']->set_column(19,19,4);
			//$this->mWorksheets['Data']->write($num, 19, $persen_label, $fColKegiatan);
			$this->mWorksheets['Data']->set_column(13,13,16);
			$this->mWorksheets['Data']->write($num, 13, $agustus_label, $fColKegiatan);
			
			//$this->mWorksheets['Data']->set_column(21,21,4);
			//$this->mWorksheets['Data']->write($num, 21, $persen_label, $fColKegiatan);
			$this->mWorksheets['Data']->set_column(14,14,16);
			$this->mWorksheets['Data']->write($num, 14, $september_label, $fColKegiatan);
			
			//$this->mWorksheets['Data']->set_column(23,23,4);
			//$this->mWorksheets['Data']->write($num, 23, $persen_label, $fColKegiatan);
			$this->mWorksheets['Data']->set_column(15,15,16);
			$this->mWorksheets['Data']->write($num, 15, $oktober_label, $fColKegiatan);
			
			//$this->mWorksheets['Data']->set_column(25,25,4);
			//$this->mWorksheets['Data']->write($num, 25, $persen_label, $fColKegiatan);
			$this->mWorksheets['Data']->set_column(16,16,16);
			$this->mWorksheets['Data']->write($num, 16, $november_label, $fColKegiatan);
			
			//$this->mWorksheets['Data']->set_column(27,27,4);
			//$this->mWorksheets['Data']->write($num, 27, $persen_label, $fColKegiatan);
			$this->mWorksheets['Data']->set_column(17,17,16);
			$this->mWorksheets['Data']->write($num, 17, $desember_label, $fColKegiatan);

			$this->mWorksheets['Data']->merge_cells(6,0,7,0);
			$this->mWorksheets['Data']->merge_cells(6,1,7,1);
			$this->mWorksheets['Data']->merge_cells(6,2,7,2);
			$this->mWorksheets['Data']->merge_cells(6,3,7,3);
			$this->mWorksheets['Data']->merge_cells(6,4,7,4);
			$this->mWorksheets['Data']->merge_cells(6,5,7,5);
			$this->mWorksheets['Data']->merge_cells(6,6,6,17);

			$num=8;

			/**
			 * end header
			 */
			$data_list = $data;
			$unit_kerja_id ='';
		    $unit_kerja_nama ='';
		    $barisAwal = $num + 1;
			for ($i=0; $i<sizeof($data_list);) {
			 		$send = $data_list[$i];
			 		
					if($i == 0){
						$send['nama_unit_p']=$data_list[$i]['unit_kerja_sumber_nama'];		
					} else{
						$send['nama_unit_p'] = 
							(($data_list[$i - 1]['unit_kerja_sumber_id'] != $data_list[$i]['unit_kerja_sumber_id']) ? 
										$data_list[$i]['unit_kerja_sumber_nama']: ''); 
						
					}	
						
					$send['nama_unit']=$data_list[$i]['unit_kerja_nama'];	
			 		$send['format']=$formatProgram;
 					$send['nomor'] = $i+1;//$data['start'] + $no;
					$send['kode']=$data_list[$i]['kode_penerimaan'];
					$send['nama']=$data_list[$i]['kode_penerimaan_nama'];
					$send['keterangan']=$data_list[$i]['keterangan'];
					
				$this->mWorksheets['Data']->write($num, 0, $send['nomor'], $formatProgram);
				$this->mWorksheets['Data']->write($num, 1, $send['nama_unit_p'], $formatProgram);
				$this->mWorksheets['Data']->write($num, 2, $send['kode'], $formatProgram);
				$this->mWorksheets['Data']->write_string($num, 3, $send['nama']."\r\n -".$send['keterangan'], $formatProgram);
				$this->mWorksheets['Data']->write($num, 4,  $send['nama_unit'], $formatProgram);
				$this->mWorksheets['Data']->write($num, 5, $send['total_terima'], $formatCurrencyProgram);
				//$this->mWorksheets['Data']->write($num, 5, $send['pjanuari'], $formatCurrencyProgram);
				$this->mWorksheets['Data']->write($num, 6, $send['januari'], $formatCurrencyProgram);
				//$this->mWorksheets['Data']->write($num, 7, $send['pfebruari'], $formatCurrencyProgram);
				$this->mWorksheets['Data']->write($num, 7, $send['februari'], $formatCurrencyProgram);
				//$this->mWorksheets['Data']->write($num, 9, $send['pmaret'], $formatCurrencyProgram);
				$this->mWorksheets['Data']->write($num, 8, $send['maret'], $formatCurrencyProgram);
				//$this->mWorksheets['Data']->write($num, 11, $send['papril'], $formatCurrencyProgram);
				$this->mWorksheets['Data']->write($num, 9, $send['april'], $formatCurrencyProgram);
				//$this->mWorksheets['Data']->write($num, 13, $send['pmei'], $formatCurrencyProgram);
				$this->mWorksheets['Data']->write($num, 10, $send['mei'], $formatCurrencyProgram);
				//$this->mWorksheets['Data']->write($num, 15, $send['pjuni'], $formatCurrencyProgram);
				$this->mWorksheets['Data']->write($num, 11, $send['juni'], $formatCurrencyProgram);
				//$this->mWorksheets['Data']->write($num, 17, $send['pjuli'], $formatCurrencyProgram);
				$this->mWorksheets['Data']->write($num, 12, $send['juli'], $formatCurrencyProgram);
				//$this->mWorksheets['Data']->write($num, 19, $send['pagustus'], $formatCurrencyProgram);
				$this->mWorksheets['Data']->write($num, 13, $send['agustus'], $formatCurrencyProgram);
				//$this->mWorksheets['Data']->write($num, 21, $send['pseptember'], $formatCurrencyProgram);
				$this->mWorksheets['Data']->write($num, 14, $send['september'], $formatCurrencyProgram);
				//$this->mWorksheets['Data']->write($num, 23, $send['poktober'], $formatCurrencyProgram);
				$this->mWorksheets['Data']->write($num, 15, $send['oktober'], $formatCurrencyProgram);
				//$this->mWorksheets['Data']->write($num, 25, $send['pnovember'], $formatCurrencyProgram);
				$this->mWorksheets['Data']->write($num, 16, $send['november'], $formatCurrencyProgram);
				//$this->mWorksheets['Data']->write($num, 27, $send['pdesember'], $formatCurrencyProgram);
				$this->mWorksheets['Data']->write($num, 17, $send['desember'], $formatCurrencyProgram);
				$num++;
				$i++;
			}
			$barisAkhir = $num;
			 /**
			  * total 
			  */
		  
			$total_terima = '=SUM(F'.$barisAwal.':F'.$barisAkhir.')';
			$total_jan = '=SUM(G'.$barisAwal.':G'.$barisAkhir.')';
			$total_feb = '=SUM(H'.$barisAwal.':H'.$barisAkhir.')';
			$total_mar = '=SUM(I'.$barisAwal.':I'.$barisAkhir.')';
			$total_apr = '=SUM(J'.$barisAwal.':J'.$barisAkhir.')';
			$total_mei = '=SUM(K'.$barisAwal.':K'.$barisAkhir.')';
			$total_jun = '=SUM(L'.$barisAwal.':L'.$barisAkhir.')';
			$total_jul = '=SUM(M'.$barisAwal.':M'.$barisAkhir.')';
			$total_ags = '=SUM(N'.$barisAwal.':N'.$barisAkhir.')';
			$total_sep = '=SUM(O'.$barisAwal.':O'.$barisAkhir.')';
			$total_okt = '=SUM(P'.$barisAwal.':P'.$barisAkhir.')';
			$total_nov = '=SUM(Q'.$barisAwal.':Q'.$barisAkhir.')';
			$total_des = '=SUM(R'.$barisAwal.':R'.$barisAkhir.')';
			/**
			foreach($data_list as $key => $value){
			  $total_terima+= $value['total_terima'];
			   $total_jan+= $value['januari'];
			   $total_feb+= $value['februari'];
			   $total_mar+= $value['maret'];
			   $total_apr+= $value['april'];
			   $total_mei+= $value['mei'];
			   $total_jun+= $value['juni'];
			   $total_jul+= $value['juli'];
			   $total_ags+= $value['agustus'];
			   $total_sep+= $value['september'];
			   $total_okt+= $value['oktober'];
			   $total_nov+= $value['november'];
			   $total_des+= $value['desember'];
			   $total_real+= $value['total_realisasi'];
         }*/
         		
         		$this->mWorksheets['Data']->write($num, 0, $total_label, $formatJumlah);
				$this->mWorksheets['Data']->write($num, 1, '', $formatJumlah);
				$this->mWorksheets['Data']->write($num, 2, '', $formatJumlah);
				$this->mWorksheets['Data']->write($num, 3, '', $formatJumlah);
				$this->mWorksheets['Data']->write($num, 4, '' , $formatJumlah);
				$this->mWorksheets['Data']->write($num, 5, $total_terima, $formatCurrencyJumlah);
				//$this->mWorksheets['Data']->write($num, 5, '', $formatCurrencyJumlah);
				$this->mWorksheets['Data']->write($num, 6, $total_jan, $formatCurrencyJumlah);
				//$this->mWorksheets['Data']->write($num, 7, '', $formatCurrencyJumlah);
				$this->mWorksheets['Data']->write($num, 7, $total_feb, $formatCurrencyJumlah);
				//$this->mWorksheets['Data']->write($num, 9, '', $formatCurrencyJumlah);
				$this->mWorksheets['Data']->write($num, 8, $total_mar, $formatCurrencyJumlah);
				//$this->mWorksheets['Data']->write($num, 11, '', $formatCurrencyJumlah);
				$this->mWorksheets['Data']->write($num, 9, $total_apr, $formatCurrencyJumlah);
				//$this->mWorksheets['Data']->write($num, 13, '', $formatCurrencyJumlah);
				$this->mWorksheets['Data']->write($num, 10, $total_mei, $formatCurrencyJumlah);
				//$this->mWorksheets['Data']->write($num, 15, '', $formatCurrencyJumlah);
				$this->mWorksheets['Data']->write($num, 11, $total_jun, $formatCurrencyJumlah);
				//$this->mWorksheets['Data']->write($num, 17, '', $formatCurrencyJumlah);
				$this->mWorksheets['Data']->write($num, 12, $total_jul, $formatCurrencyJumlah);
				//$this->mWorksheets['Data']->write($num, 19, '', $formatCurrencyJumlah);
				$this->mWorksheets['Data']->write($num, 13, $total_ags, $formatCurrencyJumlah);
				//$this->mWorksheets['Data']->write($num, 21, '', $formatCurrencyProgram);
				$this->mWorksheets['Data']->write($num, 14, $total_sep, $formatCurrencyJumlah);
				//$this->mWorksheets['Data']->write($num, 23, '', $formatCurrencyProgram);
				$this->mWorksheets['Data']->write($num, 15, $total_okt, $formatCurrencyJumlah);
				//$this->mWorksheets['Data']->write($num, 25, '', $formatCurrencyProgram);
				$this->mWorksheets['Data']->write($num, 16, $total_nov, $formatCurrencyJumlah);
				//$this->mWorksheets['Data']->write($num, 27, '', $formatCurrencyJumlah);
				$this->mWorksheets['Data']->write($num, 17, $total_des, $formatCurrencyJumlah);
				
				$this->mWorksheets['Data']->merge_cells($num,0,$num,4);
		    /**
		     * end total
		     */		
		
		}
	}

}

?>