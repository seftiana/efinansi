<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/lap_saldomaster/business/AppLapSaldomaster.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'main/function/date.php';

class ViewExcelLapSaldomaster extends XlsResponse {
   var $mWorksheets = array('Data');
   
   function GetFileName() {
      // name it whatever you want
      return 'LapSaldoMaster.xls';
   }

   function ProcessRequest() {
		$Obj = new AppLapSaldoMaster();
		$tgl_akhir = Dispatcher::Instance()->Decrypt($_GET['tgl_akhir']);
		$tgl_awal = Dispatcher::Instance()->Decrypt($_GET['tgl_awal']);
		$data = $Obj->GetSaldo($tgl_awal,$tgl_akhir);
		#print_r($data); exit;
		if (empty($data)) {
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
         //$fColKegiatan->set_column(1,1, 300);
         $fColNomorbold->set_align('center');
         
         $fColNomor = $this->mrWorkbook->add_format();
         $fColNomor->set_border(1);
		 	#$fColNomor->set_bold();
         $fColNomor->set_size(10);
         //$fColKegiatan->set_column(1,1, 300);
         $fColNomor->set_align('center');
         
         $fColCtn = $this->mrWorkbook->add_format();
         $fColCtn->set_border(1);
         $fColCtn->set_size(10);
         $fColCtn->set_align('left');
         
         $fColNilai = $this->mrWorkbook->add_format();
    		$fColNilai->set_border(1);   
    		$fColNilai->set_size(10);
    		$fColNilai->set_align('right');
    		
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
         
         #set header
      	$this->mWorksheets['Data']->write(0, 0, 'Laporan Saldo Master', $fTitle);
         $this->mWorksheets['Data']->write(2, 0, 'Tanggal Transaksi '. IndonesianDate($tgl_akhir, 'yyyy-mm-dd'));
         
         $no=4;
         $this->mWorksheets['Data']->write($no, 0, 'No.', $fColNomorbold);
         $this->mWorksheets['Data']->write($no, 1, 'No. Rekening', $fColNomorbold);
         $this->mWorksheets['Data']->write($no, 2, 'Nama Rekening', $fColNomorbold);
         $this->mWorksheets['Data']->write($no, 3, 'Saldo Awal', $fColNomorbold);
         $this->mWorksheets['Data']->write($no, 4, 'Mutasi Debet (Rp.)', $fColNomorbold);
         $this->mWorksheets['Data']->write($no, 5, 'Mutasi Kredit (Rp.)', $fColNomorbold);
         $this->mWorksheets['Data']->write($no, 6, 'Saldo Akhir (Rp.)', $fColNomorbold);
         
         $num = 5;
         
         #$nomer = 1;
         $saldoAwal = $mutasiDebet = $mutasiKredit = $saldoAkhir =0;
      	$no=1;
      	$saldoAwal = $debet = $kredit = $saldoAkhir = 0;
      	for ($i=0;$i<sizeof($data);$i++) {
        		if ($i==0) $saldoAwal = $data[$i]['saldo_awal'];
        		$debet += $data[$i]['debet'];
        		$kredit += $data[$i]['kredit'];
        	
        		if ($data[$i]['coa_kode_akun']!=$data[$i+1]['coa_kode_akun']) {
          		$saldoAkhir = $data[$i]['saldo_akhir'];
          		$kode = explode(".", $data[$i]['coa_kode_akun']);
          		$kodeNext = explode(".", $data[$i+1]['coa_kode_akun']);
          		$this->mWorksheets['Data']->write($num, 0, $no, $fColNomor);
          		$this->mWorksheets['Data']->write($num, 1, $data[$i]['coa_kode_akun'], $fColCtn);
          		$this->mWorksheets['Data']->write($num, 2, $data[$i]['coa_nama_akun'], $fColCtn);
          		$this->mWorksheets['Data']->write($num, 3, number_format($saldoAwal,2,',','.'), $fColNilai);
          		$this->mWorksheets['Data']->write($num, 4, number_format($debet,2,',','.'), $fColNilai);
          		$this->mWorksheets['Data']->write($num, 5, number_format($kredit,2,',','.'), $fColNilai);
          		$this->mWorksheets['Data']->write($num, 6,number_format( $saldoAkhir,2,',','.'), $fColNilai);
          		
          		$num++;
          		$no++;
          		$subSAwal += $saldoAwal;
          		$subSAkhir += $saldoAkhir;
          		$mDebet += $debet;
          		$mKredit += $kredit;
          		if ($kode[0]!=$kodeNext[0]) {
          			$this->mWorksheets['Data']->write($num, 0, '', $fColNomor);
          			$this->mWorksheets['Data']->write($num, 1, '', $fColNomor);
          			$this->mWorksheets['Data']->write($num, 2, 'Sub Total', $fColNomor);
          			$this->mWorksheets['Data']->write($num, 3, number_format($subSAwal,2,',','.'), $fColNilai);
          			$this->mWorksheets['Data']->write($num, 4, number_format($mDebet,2,',','.'), $fColNilai);
          			$this->mWorksheets['Data']->write($num, 5, number_format($mKredit,2,',','.'), $fColNilai);
          			$this->mWorksheets['Data']->write($num, 6, number_format($subSAkhir,2,',','.'), $fColNilai);
            		$subSAwal = $subSAkhir = $mDebet = $mKredit = 0;
            		$no=1;
          		}
          		$saldoAwal = $debet = $kredit = $saldoAkhir = 0;
          		$saldoAwal = $data[$i+1]['saldo_awal'];        
        		}
      	}
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