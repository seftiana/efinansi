<?php

require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
    'module/lap_realisasi_anggaran_program/business/AppLapRekapProgram.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'main/function/date.php';

class ViewExcelLapRekapProgram extends XlsResponse 
{
   var $mWorksheets = array('Data');
   
   function GetFileName() 
   {
      // name it whatever you want
      $label =str_replace(' ','_',$this->L('laporan_realisasi_anggaran_program_pengeluaran'));    
      // name it whatever you want
      return $label.'.xls';//LapRealisasiAnggaranProgram.xls';
   }
   
   function L($indexLangName = '')
   {
   		$lang = GTFWConfiguration::GetValue('language',$indexLangName);
   		if(!empty($lang)){
   			return $lang;	
   		}
   		return '';
   }
   function ProcessRequest() 
   {
	    if(isset($_GET)) { //pasti dari form pencarian :p	  
	     if(is_object($_GET))
		    $v = $_GET->AsArray();
		 else
		    $v = $_GET;        
        }    
		$Obj = new AppLapRekapProgram();
		$tahun_anggaran = Dispatcher::Instance()->Decrypt($v['tahun_anggaran']);
		$program = Dispatcher::Instance()->Decrypt($v['program']);
		$unitkerja = Dispatcher::Instance()->Decrypt($v['unitkerja']);
		$jenis_kegiatan = Dispatcher::Instance()->Decrypt($v['jenis_kegiatan']);
		$bulan = Dispatcher::Instance()->Decrypt($v['bulan']);
        $start_rec = Dispatcher::Instance()->Decrypt($v['start_rec']);
        $item_viewed = Dispatcher::Instance()->Decrypt($v['item_viewed']);
		//$result = $Obj->GetCetakData($tahun_anggaran, $program, $jenis_kegiatan, $unitkerja,$role, $bulan);
        $result = $Obj->GetData(
                                $start_rec, 
                                $item_viewed, 
                                $tahun_anggaran, 
                                $program, 
                                $jenis_kegiatan, 
                                $unitkerja, 
                                $bulan);
		if($tahun_anggaran)
			$data_tahun_anggaran = $Obj->GetTahunAnggaranById($tahun_anggaran);
		else $data_tahun_anggaran['nama'] = "-";

		if($program)
			$data_program = $Obj->GetProgramById($program);
		else $data_program['nama'] = " Semua ";

		if($unitkerja)
			$data_unitkerja = $Obj->GetUnitkerjaById($unitkerja);
		else $data_unitkerja['nama'] = " Semua ";

		if($jenis_kegiatan != '') {
			$data_jenis_kegiatan = $Obj->GetJenisKegiatanById($jenis_kegiatan);
		} else {
			$data_jenis_kegiatan['nama'] = " Semua ";
		}
		//$return['data'] = $data;
		//return $return;

      if (empty($result)) {
         $this->mWorksheets['Data']->write(0, 0,  $this->L('data_kosong'));
      } else {
            $fTitle = $this->mrWorkbook->add_format();
	        $fTitle->set_bold();
            $fTitle->set_size(12);
            $fTitle->set_align('vcenter');

		    $formatHeader = $this->mrWorkbook->add_format();
            $formatHeader->set_border(1);
            $formatHeader->set_bold();
            $formatHeader->set_size(11);
            $formatHeader->set_align('center');
            $formatHeader->set_align('vcenter');
            $formatHeader->set_text_wrap();
    
            $formatProgram = $this->mrWorkbook->add_format();
            $formatProgram->set_border(1);
            $formatProgram->set_bold();
            $formatProgram->set_size(11);
            $formatProgram->set_align('left');
            $formatProgram->set_align('vcenter');
            $formatProgram->set_text_wrap();

            $formatCurrencyProgram = $this->mrWorkbook->add_format();
            $formatCurrencyProgram->set_border(1);
            $formatCurrencyProgram->set_bold();
            $formatCurrencyProgram->set_size(11);
            $formatCurrencyProgram->set_align('right');
            $formatCurrencyProgram->set_align('vcenter');
    
            $formatKegiatan = $this->mrWorkbook->add_format();
            $formatKegiatan->set_border(1);
            $formatKegiatan->set_bold();
            $formatKegiatan->set_italic();
            $formatKegiatan->set_size(10);
            $formatKegiatan->set_align('left');
            $formatKegiatan->set_align('vcenter');
            $formatKegiatan->set_text_wrap();
    
            $formatCurrencyKegiatan = $this->mrWorkbook->add_format();
            $formatCurrencyKegiatan->set_border(1);
            $formatCurrencyKegiatan->set_bold();
            $formatCurrencyKegiatan->set_italic();
            $formatCurrencyKegiatan->set_size(10);
            $formatCurrencyKegiatan->set_align('right');
            $formatCurrencyKegiatan->set_align('vcenter');
    
            $format = $this->mrWorkbook->add_format();
            $format->set_border(1);
            $format->set_align('left');
            $format->set_align('vcenter');
            $format->set_text_wrap();
    
            $formatBold = $this->mrWorkbook->add_format();
            $formatBold->set_align('left');
            $formatBold->set_bold();
            $formatBold->set_text_wrap();

            $formatCurrency = $this->mrWorkbook->add_format();
            $formatCurrency->set_border(1);
            $formatCurrency->set_align('right');
            $formatCurrency->set_align('vcenter');

		 //$fColData->set_text_wrap(1);

	     //$this->mWorksheets['Data']->set_columns(array(6, 20, 20, 30, 30, 30, 20, 10));

         $this->mWorksheets['Data']->write(0, 0, 
                                $this->L('laporan_realisasi_anggaran_program_pengeluaran'), $fTitle);
         $this->mWorksheets['Data']->write(2, 0, 
                                $this->L('tahun_periode').' : '.$data_tahun_anggaran['nama']);
         $this->mWorksheets['Data']->write(3, 0, $this->L('program').' : '.$data_program['nama']);
         $this->mWorksheets['Data']->write(4, 0, $this->L('unit').' / '.$this->L('sub_unit').
                                        ' : '.$data_unitkerja['nama']);
         //$this->mWorksheets['Data']->write(5, 0, 
          //                          $this->L('jenis_kegiatan').' : '.$data_jenis_kegiatan['nama']);
         $no=7;
         $this->mWorksheets['Data']->set_column(0,0,15);
         $this->mWorksheets['Data']->write($no, 0, $this->L('kode'), $formatHeader);
         $this->mWorksheets['Data']->set_column(1,1,40);
         $this->mWorksheets['Data']->write($no, 1, $this->L('nama')."\n".
                            '('.$this->L('program').','.$this->L('kegiatan').','.
                            $this->L('sub_kegiatan').')', $formatHeader);
         $this->mWorksheets['Data']->set_column(2,2,25);
         $this->mWorksheets['Data']->write($no, 2, 
                                    $this->L('unit').' / '.$this->L('sub_unit'), $formatHeader);
         $this->mWorksheets['Data']->set_column(3,3,25);
         $this->mWorksheets['Data']->write($no, 3, $this->L('deskripsi'), $formatHeader);
         $this->mWorksheets['Data']->set_column(4,4,20);
         $this->mWorksheets['Data']->write($no, 4, $this->L('nominal_setuju_rp'), $formatHeader);
         $this->mWorksheets['Data']->set_column(5,5,20);
         $this->mWorksheets['Data']->write($no, 5, $this->L('nominal_pencairan_rp'), $formatHeader);
         $this->mWorksheets['Data']->set_column(6,6,20);
         $this->mWorksheets['Data']->write($no, 6, $this->L('nominal_realisasi_rp'), $formatHeader);
         $this->mWorksheets['Data']->set_column(7,7,20);
         $this->mWorksheets['Data']->write($no, 7, $this->L('sisa_rp'), $formatHeader);
         $no = 8;
         $x=0;
		 //print_r($result);
		 $kodeProg = ''; $kodeKegiatan = ''; $kodeSubKegiatan='';
		 for($i=0;$i<sizeof($result);) {
			 if(($result[$i]['kodeProg'] == $kodeProg) && ($result[$i]['kodeKegiatan'] == $kodeKegiatan)) {
					$send[$x]['kode'] = $result[$i]['kodeSubKegiatan'];
					$send[$x]['program_kegiatan'] = $result[$i]['namaSubKegiatan'];
					$send[$x]['unitkerja'] = $result[$i]['unitName'];
					$send[$x]['nominal_usulan'] = $result[$i]['nominalUsulan'];
					$send[$x]['nominal_setuju'] = $result[$i]['nominalSetuju'];
               $send[$x]['nominal_pencairan'] = $result[$i]['nominal_pencairan'];
               $send[$x]['nominal_realisasi'] = $result[$i]['nominal_realisasi'];
               $send[$x]['sisa'] = $result[$i]['sisa'];
					$send[$x]['deskripsi'] = $result[$i]['deskripsi'];
					//$send[$x]['class_button'] = "";
					$send[$x]['jenis'] = "subkegiatan";
               $send[$x]['format'] = $format;
               $send[$x]['formatCur'] = $formatCurrency;
			
				$i++;
			 } elseif($result[$i]['kodeProg'] != $kodeProg) {
					$kodeProg = $result[$i]['kodeProg'];
				

					$send[$x]['kode'] = $result[$i]['kodeProg'];
					$send[$x]['program_kegiatan'] = $result[$i]['namaProgram'];
					$send[$x]['unitkerja'] = "";
					$send[$x]['nominal_usulan'] = "";
					$send[$x]['nominal_setuju'] = "";
               $send[$x]['nominal_realisasi'] = "";
               $send[$x]['nominal_pencairan'] = "";
               $send[$x]['sisa'] = "";   
					//$send[$x]['class_button'] = "table-common-even1";
					$send[$x]['jenis'] = "program";
               $send[$x]['format'] = $formatProgram;
               $send[$x]['formatCur'] = $formatCurrencyProgram;
				
			 } elseif($result[$i]['kodeKegiatan'] != $kodeKegiatan) {
					$kodeProg = $result[$i]['kodeProg'];
					$kodeKegiatan = $result[$i]['kodeKegiatan'];
					//$namaProgram = $result[$i]['namaProgram'];

					$send[$x]['kode'] = $result[$i]['kodeKegiatan'];
					$send[$x]['program_kegiatan'] = $result[$i]['namaKegiatan'];
					$send[$x]['unitkerja'] = "";
					$send[$x]['nominal_usulan'] = "";
					$send[$x]['nominal_setuju'] = "";
               $send[$x]['nominal_pencairan'] = "";
               $send[$x]['nominal_realisasi'] = "";
               $send[$x]['sisa'] = "";  
					//$send[$x]['class_button'] = "table-common-even";
					$send[$x]['jenis'] = "kegiatan";
               $send[$x]['format'] = $formatKegiatan;
               $send[$x]['formatCur'] = $formatCurrencyKegiatan;
				
			 }
			 $x++;
		 }
			$i = sizeof($send)-1;
			$nominal_usulan=0;
			$nominal_setuju=0;
         $nominal_pencairan=0;
         $nominal_realisasi=0;
         $sisa=0;   
			while($i >= 0) {
				if($send[$i]['jenis'] == 'subkegiatan') {
					$nominal_usulan = $send[$i]['nominal_usulan'];
					$nominal_setuju += $send[$i]['nominal_setuju'];
               $nominal_pencairan += $send[$i]['nominal_pencairan'];
               $nominal_realisasi += $send[$i]['nominal_realisasi'];
               $sisa = ($nominal_setuju - $nominal_realisasi);
				}
				if($send[$i]['jenis'] == 'kegiatan') {
					$send[$i]['nominal_usulan'] = $nominal_usulan;
					$send[$i]['nominal_setuju'] = $nominal_setuju;
               $send[$i]['nominal_pencairan'] = $nominal_pencairan;
               $send[$i]['nominal_realisasi'] = $nominal_realisasi;
               $send[$i]['sisa'] = $sisa;
					$nominal_usulan_program = $nominal_usulan;
					$nominal_setuju_program = $nominal_setuju;
               $nominal_pencairan_program += $nominal_pencairan;
               $nominal_realisasi_program += $nominal_realisasi;
               $sisa_program = ($nominal_setuju_program - $nominal_realisasi_program);
					$nominal_usulan=0;
					$nominal_setuju=0;
               $nominal_realisasi=0;
               $nominal_pencairan=0;
               $sisa=0;
				}
				if($send[$i]['jenis'] == 'program') {
					$send[$i]['nominal_usulan'] = $nominal_usulan_program;
					$send[$i]['nominal_setuju'] = $nominal_setuju_program;
               $send[$i]['nominal_pencairan'] = $nominal_pencairan_program;
               $send[$i]['nominal_realisasi'] = $nominal_realisasi_program;
               $send[$i]['sisa'] = $sisa_program;
					$nominal_usulan_program = 0;
					$nominal_setuju_program = 0;
               $nominal_realisasi_program = 0;
               $nominal_pencairan_program = 0;
               $sisa_program = 0;   
				}
				$i--;
			}

			for($j=0;$j<sizeof($send);$j++) {
                if($send[$j]['jenis'] == 'program') {
                    $resume[] = $send[$j];
                }
                $this->mWorksheets['Data']->write($no, 0, $send[$j]['kode'], $send[$j]['format']);
                $this->mWorksheets['Data']->write($no, 1, $send[$j]['program_kegiatan'], $send[$j]['format']);
				$this->mWorksheets['Data']->write($no, 2, $send[$j]['unitkerja'], $send[$j]['format']);
				$this->mWorksheets['Data']->write($no, 3, $send[$j]['deskripsi'], $send[$j]['format']);
				$this->mWorksheets['Data']->write($no, 4, $send[$j]['nominal_setuju'], $send[$j]['formatCur']);
                $this->mWorksheets['Data']->write($no, 5, $send[$j]['nominal_pencairan'], $send[$j]['formatCur']);
                $this->mWorksheets['Data']->write($no, 6, $send[$j]['nominal_realisasi'], $send[$j]['formatCur']);
                $this->mWorksheets['Data']->write($no, 7, $send[$j]['sisa'], $send[$j]['formatCur']);
                $no++;
				 
			}
			
         $no += 2;
         $this->mWorksheets['Data']->write($no, 0, $this->L('resume'),$formatBold);
         $no += 1;
         $this->mWorksheets['Data']->write($no, 0, $this->L('kode'), $formatHeader);
         $this->mWorksheets['Data']->merge_cells($no,1,$no,2);
         $this->mWorksheets['Data']->write($no, 1, $this->L('program'), $formatHeader);
         $this->mWorksheets['Data']->merge_cells($no,1,$no,2);
         $this->mWorksheets['Data']->write($no, 2, '',$formatHeader);
         $this->mWorksheets['Data']->write($no, 3, $this->L('nominal_usulan_rp'),$formatHeader);
         $this->mWorksheets['Data']->write($no, 4, $this->L('nominal_setuju_rp'), $formatHeader);
         $this->mWorksheets['Data']->write($no, 5, $this->L('nominal_pencairan_rp'), $formatHeader);
         $this->mWorksheets['Data']->write($no, 6, $this->L('nominal_realisasi_rp'), $formatHeader);
         $this->mWorksheets['Data']->write($no, 7, $this->L('sisa_rp'), $formatHeader);
         $no += 1;
         for($k=0;$k<sizeof($resume);$k++) {
            $this->mWorksheets['Data']->write($no, 0, $resume[$k]['kode'], $format);
            $this->mWorksheets['Data']->merge_cells($no,1,$no,2);
			$this->mWorksheets['Data']->write($no, 1, $resume[$k]['program_kegiatan'],$format);
            $this->mWorksheets['Data']->merge_cells($no,1,$no,2);
            $this->mWorksheets['Data']->write($no, 2, '',$format);
			$this->mWorksheets['Data']->write($no, 3, $resume[$k]['nominal_usulan'],$formatCurrency);
			$this->mWorksheets['Data']->write($no, 4, $resume[$k]['nominal_setuju'],$formatCurrency);
            $this->mWorksheets['Data']->write($no, 5, $resume[$k]['nominal_pencairan'],$formatCurrency);
            $this->mWorksheets['Data']->write($no, 6, $resume[$k]['nominal_realisasi'],$formatCurrency);
            $this->mWorksheets['Data']->write($no, 7, $resume[$k]['sisa'],$formatCurrency);
            $no++;           
         }

         $unitData = $Obj->GetUnitIdentity($unitkerja);
         $pimpinan = $unitData['0']['unitkerjaNamaPimpinan'];
         
         $date=date('d-m-Y');
         $date=IndonesianDate($date,'dd-mm-yyyy');
   
         $sign = $this->mrWorkbook->add_format();
         $sign->set_align('center');
   
         $no+=3;
         $kota = GTFWConfiguration::GetValue('organization', 'city');
         $this->mWorksheets['Data']->write($no, 6, $kota.', '.$date,$sign);
         $no++;
         $this->mWorksheets['Data']->write($no, 6,  $this->L('pimpinan').' '.
                    $this->L('unit').' / '.$this->L('sub_unit'),$sign);
         $no++;
         $this->mWorksheets['Data']->write($no, 6, $unitData['0']['unitkerjaNama'], $sign);
         $no+=5;
         if(!empty($pimpinan))
            $this->mWorksheets['Data']->write($no, 6, '('.$pimpinan.')' ,$sign);
         else
            $this->mWorksheets['Data']->write($no, 6, '(............................................................)',$sign);
		 
      }
   }
  
}
?>
