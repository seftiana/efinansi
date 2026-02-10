<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/lap_transaksi/business/AppLapTransaksi.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'main/function/date.php';

class ViewExcelLapTransaksi extends XlsResponse {
   var $mWorksheets = array('Data');
   
   function GetFileName() {
      // name it whatever you want
      return 'LapTransaksi.xls';
   }

   function ProcessRequest() {
		$Obj = new AppLapTransaksi();
		$tgl_awal = Dispatcher::Instance()->Decrypt($_GET['tgl_awal']);
		$tgl_akhir = Dispatcher::Instance()->Decrypt($_GET['tgl_akhir']);
		$key = Dispatcher::Instance()->Decrypt($_GET['key']);
		$result = $Obj->GetDataCetak($tgl_awal,$tgl_akhir,$key);
		#print_r($result); exit;
		if (empty($result)) {
         $this->mWorksheets['Data']->write(0, 0, 'Data kosong');
      } else {
		  
      	 $fTitle = $this->mrWorkbook->add_format();
		 $fTitle->set_bold();
         $fTitle->set_size(12);
         $fTitle->set_align('vcenter');
         
         #set colom
         $fColNomorbold = $this->mrWorkbook->add_format();
         $fColNomorbold->set_border(1);
		 $fColNomorbold->set_bold();
         $fColNomorbold->set_size(10);
         $fColNomorbold->set_align('center');
         
         $fColNomor = $this->mrWorkbook->add_format();
         $fColNomor->set_border(1);
		 #$fColNomor->set_bold();
         $fColNomor->set_size(10);
         $fColNomor->set_align('center');
         
         $fColCtn = $this->mrWorkbook->add_format();
         $fColCtn->set_border(1);
         $fColCtn->set_size(10);
         $fColCtn->set_align('left');
         
         $fColNilai = $this->mrWorkbook->add_format();
    	 $fColNilai->set_border(1);   
    	 $fColNilai->set_size(10);
    	 $fColNilai->set_align('right');
    	 $fColNilai->set_num_format(4);
    	 
         $fColNilaiB = $this->mrWorkbook->add_format();
    	 $fColNilaiB->set_border(1);   
    	 $fColNilaiB->set_bold();
    	 $fColNilaiB->set_size(10);
    	 $fColNilaiB->set_align('right');
    	 $fColNilaiB->set_num_format(4);
    		
    	 $fColCtnitalic = $this->mrWorkbook->add_format();
         $fColCtnitalic->set_border(1);
         $fColCtnitalic->set_italic();
         $fColCtnitalic->set_size(10);
         $fColCtnitalic->set_align('left');
         
         $fColNilaitalic = $this->mrWorkbook->add_format();
    	 $fColNilaitalic->set_border(1);   
    	 $fColNilaitalic->set_italic();
    	 $fColNilaitalic->set_size(10);
    	 $fColNilaitalic->set_align('right');
    	 $fColNilaitalic->set_num_format(4);
         
         #set header
      	 $this->mWorksheets['Data']->write(0, 0, 'Laporan Transaksi', $fTitle);
         $this->mWorksheets['Data']->write(1, 0, 'Interval waktu '.$this->date2string($tgl_awal).' s/d '.$this->date2string($tgl_akhir));
         
         $no=3;
         			#format widht column
		 $this->mWorksheets['Data']->set_column(0, 0, 5);
		 $this->mWorksheets['Data']->set_column(1, 1, 20);
		 $this->mWorksheets['Data']->set_column(2, 2, 30);
		 $this->mWorksheets['Data']->set_column(3, 3, 50);
		 $this->mWorksheets['Data']->set_column(4, 4, 20);
		 $this->mWorksheets['Data']->set_column(5, 5, 40);
         $this->mWorksheets['Data']->write($no, 0, 'No.', $fColNomorbold);
         $this->mWorksheets['Data']->write($no, 1, 'Tanggal', $fColNomorbold);
         $this->mWorksheets['Data']->write($no, 2, 'Referensi', $fColNomorbold);
         $this->mWorksheets['Data']->write($no, 3, 'Catatan', $fColNomorbold);
         $this->mWorksheets['Data']->write($no, 4, 'Nilai (Rp.)', $fColNomorbold);
         $this->mWorksheets['Data']->write($no, 5, 'Tipe', $fColNomorbold);
         
         $num = 4;
         $rAwal = $num+1;
         for($i=0; $i<count($result); $i++) {
         	if(strtoupper($result[$i]['transaksi_is_jurnal']) == 'Y') {
         		$format = $fColCtnitalic;
         		$format_nilai = $fColNilaitalic;
         	} else {
         		$format = $fColCtn;
         		$format_nilai = $fColNilai;
         	}
         	    $number = $i+1;
         	    $this->mWorksheets['Data']->write($num, 0, $number, $fColNomor);
				$this->mWorksheets['Data']->write($num, 1, $this->date2string($result[$i]['transaksi_tanggal']), $format);
				$this->mWorksheets['Data']->write($num, 2, $this->date2string($result[$i]['transaksi_referensi']), $format);
				$this->mWorksheets['Data']->write($num, 3, $this->date2string($result[$i]['transaksi_catatan']), $format);
				$this->mWorksheets['Data']->write($num, 4, $result[$i]['transaksi_nilai'], $format_nilai);
				$this->mWorksheets['Data']->write($num, 5, $result[$i]['transaksi_tipe'], $format);
				$num++;
        }
        $rAkhir = $num; 
        $this->mWorksheets['Data']->write($num, 0, 'Total', $fColNomorbold);
        $this->mWorksheets['Data']->write($num, 1, '', $fColNomorbold);
        $this->mWorksheets['Data']->write($num, 2, '', $fColNomorbold);
        $this->mWorksheets['Data']->write($num, 3, '', $fColNomorbold);
        $this->mWorksheets['Data']->write($num, 4, '=SUM(E'.$rAwal.':E'.$rAkhir.')', $fColNilaiB);
        $this->mWorksheets['Data']->write($num, 5, '', $fColNomorbold);
        
        $this->mWorksheets['Data']->merge_cells($num, 0, $num, 3);
      }
		
	}
	
	function date2string($date) {
	   $bln = array(
	                1  => 'Januari',
					2  => 'Februari',
					3  => 'Maret',
					4  => 'April',
					5  => 'Mei',
					6  => 'Juni',
					7  => 'Juli',
					8  => 'Agustus',
					9  => 'September',
					10 => 'Oktober',
					11 => 'November',
					12 => 'Desember'					
	               );
	   $arrtgl = explode('-',$date);
	   return $arrtgl[2].' '.$bln[(int) $arrtgl[1]].' '.$arrtgl[0];
	}
}
?>