<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/lap_transaksi_harian/business/AppLapTransaksiHarian.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'main/function/date.php';

class ViewExcelLapTransaksiHarian extends XlsResponse {
   var $mWorksheets = array('Data');
   
   function GetFileName() {
      // name it whatever you want
      return 'LapTransaksiHarian.xls';
   }

   function ProcessRequest() {
		$Obj = new AppLapTransaksiHarian();
		$tgl_transaksi = Dispatcher::Instance()->Decrypt($_GET['tgl_transaksi']);
		$result = $Obj->GetDataCetak($tgl_transaksi);
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
         $fColNomor->set_size(10);
         $fColNomor->set_align('center');
         
         $fColCtn = $this->mrWorkbook->add_format();
         $fColCtn->set_border(1);
         $fColCtn->set_size(10);
         $fColCtn->set_align('left');
         
         $fColCtnBold = $this->mrWorkbook->add_format();
         $fColCtnBold->set_border(1);
         $fColCtnBold->set_size(10);
         $fColCtnBold->set_bold();
         $fColCtnBold->set_align('left');
         
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
      	 $this->mWorksheets['Data']->write(1, 0, 'Laporan Transaksi Harian', $fTitle);
         $this->mWorksheets['Data']->write(2, 0, 'Tanggal Transaksi '.$this->date2string($tgl_transaksi));
         
         $no=4;
         $this->mWorksheets['Data']->set_column(0, 0, 5);
         $this->mWorksheets['Data']->set_column(1, 1, 15);
         $this->mWorksheets['Data']->set_column(2, 2, 50);
         $this->mWorksheets['Data']->set_column(3, 3, 20);
         $this->mWorksheets['Data']->set_column(4, 4, 20);
         $this->mWorksheets['Data']->set_column(5, 5, 20);
         
         $this->mWorksheets['Data']->write($no, 0, 'No.', $fColNomorbold);
         $this->mWorksheets['Data']->write($no, 1, 'No. Rekening', $fColNomorbold);
         $this->mWorksheets['Data']->write($no, 2, 'Nama Akun / Keterangan', $fColNomorbold);
         $this->mWorksheets['Data']->write($no, 3, 'Debet (Rp.)', $fColNomorbold);
         $this->mWorksheets['Data']->write($no, 4, 'Kredit (Rp.)', $fColNomorbold);
         $this->mWorksheets['Data']->write($no, 5, 'Saldo (Rp.)', $fColNomorbold);
         
         $num = 5;
         
         $nomer = 1;
			for ($i=0; $i<sizeof($result); $i++) {
				if ($result[$i]['coa_kode_akun']!=$result[$i-1]['coa_kode_akun']) {
					$this->mWorksheets['Data']->write($num, 0, '', $fColNomor);
					$this->mWorksheets['Data']->write($num, 1, $result[$i]['coa_kode_akun'], $fColCtn);
					$this->mWorksheets['Data']->write($num, 2, $result[$i]['coa_nama_akun'], $fColCtn);
					$this->mWorksheets['Data']->write($num, 3, '', $fColNilai);
					$this->mWorksheets['Data']->write($num, 4, '', $fColNilai);
					
            	//$saldoTran = $Obj->GetSaldoTransaksi($result[$i]['coa_id'], $tgl_transaksi);
            	#print_r($saldoTran); exit;
            	//if (!empty($saldoTran['saldo_awal_transaksi'])) $saldo = $saldoTran['saldo_awal_transaksi']; else $saldo = 0;

                if (!empty($result[$i]['saldo_awal']))
                    $saldo = $result[$i]['saldo_awal'];//$saldoTran['saldo_awal_transaksi'];
                    else $saldo = 0;                
            	$this->mWorksheets['Data']->write($num, 5, $saldo, $fColNilai);
            	$num++;
          	}
          	#echo "harus nya ketulis"; exit;
         	$this->mWorksheets['Data']->write($num, 0, $nomer, $fColNomor);
         	$this->mWorksheets['Data']->write($num, 1, ' - ', $fColCtn);
         	$this->mWorksheets['Data']->write($num, 2, $result[$i]['transaksi_catatan'], $fColCtn); 
         	if ($result[$i]['status_pembukuan']=='D') {
           		if ($result[$i]['coa_status_debet']!=1) $kDebet += (2*$result[$i]['transaksi_nilai']);
           		$debet += $result[$i]['transaksi_nilai'];
         		#$debKred = 'DEBET'; 
         		$this->mWorksheets['Data']->write($num, 3, $result[$i]['transaksi_nilai'], $fColNilai);
         		$this->mWorksheets['Data']->write($num, 4, '', $fColNilai);
         	} else {
         		if ($result[$i]['coa_status_debet']!=0) $dKredit += (2*$result[$i]['transaksi_nilai']);
            	$kredit += $result[$i]['transaksi_nilai'];
            	#$debKred = 'KREDIT';
            	$this->mWorksheets['Data']->write($num, 3, '', $fColNilai);
         		$this->mWorksheets['Data']->write($num, 4, $result[$i]['transaksi_nilai'], $fColNilai);
         	}
         	$this->mWorksheets['Data']->write($num, 5, '', $fColNilai);   
         	$nomer++;
         	if ($result[$i]['coa_kode_akun']!=$result[$i+1]['coa_kode_akun']) {
         		$num++;  
         		$this->mWorksheets['Data']->write($num, 0, '', $fColCtnBold);
         		$this->mWorksheets['Data']->write($num, 1, '', $fColCtnBold);
         		$this->mWorksheets['Data']->write($num, 2, 'Sub Total', $fColCtnBold);         		
            	#$this->mrTemplate->AddVar("data_transaksi_item", "KETERANGAN", 'Sub Total');
            	if ($debet!=0) $debetRp=$debet; else $debetRp='';
            	if ($kredit!=0) $kreditRp=$kredit; else $kreditRp='';
            	$this->mWorksheets['Data']->write($num, 3, $debetRp, $fColNilaiB);
            	$this->mWorksheets['Data']->write($num, 4, $kreditRp, $fColNilaiB);
            	$this->mWorksheets['Data']->write($num, 5, $saldo+$debet+$kredit-$kDebet-$dKredit, $fColNilaiB);     
            	$nomer=1;
            	$totalDebet += $debet;
            	$totalKredit += $kredit;
            	$debet = $kredit = $saldo = $kDebet = $dKredit = 0;
            	#$num++;
         	}
         	$num++;
			}
			$this->mWorksheets['Data']->write($num, 0, '', $fColCtnBold);
			$this->mWorksheets['Data']->write($num, 1, '', $fColCtnBold);
			$this->mWorksheets['Data']->write($num, 2, 'Grand Total', $fColCtnBold);
			$this->mWorksheets['Data']->write($num, 3, $totalDebet, $fColNilaiB);
			$this->mWorksheets['Data']->write($num, 4, $totalKredit, $fColNilaiB);
			$this->mWorksheets['Data']->write($num, 5, '', $fColNilaiB);
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