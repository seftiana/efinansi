<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/lap_transaksi_harian/business/AppLapTransaksiHarian.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'main/function/date.php';

class ViewRtfLapTransaksiHarian extends HtmlResponse {
	function ProcessRequest() {
		$Obj = new AppLapTransaksiHarian();
		$_GET = $_GET->AsArray();
		$tgl_transaksi = Dispatcher::Instance()->Decrypt($_GET['tgl_transaksi']);
		$jenis_transaksi = Dispatcher::Instance()->Decrypt($_GET['jenis_transaksi']);
		
		$data_cetak = $Obj->GetDataCetak($tgl_transaksi,$jenis_transaksi);
		$data['tgl_transaksi'] = $tgl_transaksi;
		$data['jenis_transaksi'] = $jenis_transaksi;
		$data['transaksi'] = $data_cetak;
		return $data;
	}
	
	function ParseTemplate($data = NULL) {
		$contents = file_get_contents(GTFWConfiguration::GetValue( 'application', 'docroot')."doc/template_LapTransaksiHarian.rtf");
		$contents = str_replace('[NAMA_COMPANY]',GTFWConfiguration::GetValue('organization', 'company_name'), $contents);
		$contents = str_replace('[HEADER]','LAPORAN TRANSAKSI HARIAN', $contents);
		$contents = str_replace('[TGL_TRANSAKSI]', $this->date2string($data['tgl_transaksi']), $contents);
		
		$Obj = new AppLapTransaksiHarian();
		if (empty($data['transaksi'])) {
         	$contents = str_replace('[NO]', '', $contents);
         	$contents = str_replace('[NO_REKENING]', 'Data Kosong', $contents);
         	//$contents = str_replace('[CATATAN]', 'Data Kosong', $contents);
         	$contents = str_replace('[KETERANGAN]', 'Data Kosong', $contents);
         	$contents = str_replace('[DEBET]', 'Data Kosong', $contents);
         	$contents = str_replace('[KREDIT]', 'Data Kosong', $contents);
         	$contents = str_replace('[SALDO]', 'Data Kosong', $contents);
			$contents = str_replace('[TOT_DEBET]', 'Data Kosong', $contents);
			$contents = str_replace('[TOT_KREDIT]', 'Data Kosong', $contents);
			
		} else {
			#print_r($data['transaksi']); exit;
			$kode_akun = ''; 
			$nama_akun = ''; 
			$coa_id = ''; 
			$no = 1;
			$x = 0;
			$i = 0;
			for ($i=0; $i<sizeof($data['transaksi']); $i++) {
			if ($data['transaksi'][$i]['coa_kode_akun']!=$data['transaksi'][$i-1]['coa_kode_akun']) {
				$send[$x]['no'] = '';
				$send[$x]['no_rekening'] = $data['transaksi'][$i]['coa_kode_akun'];
				$send[$x]['keterangan'] = $data['transaksi'][$i]['coa_nama_akun'];
				$send[$x]['debet'] = 'NULL';
				$send[$x]['kredit'] = 'NULL';
				//$send[$x]['saldo'] = '';
				//$contents = str_replace('[NO]', '', $contents);
				//$contents = str_replace('[NO_REKENING]', //$data['transaksi'][$i]['coa_kode_akun'], $contents);
				//$contents = str_replace('[KETERANGAN]', $data['transaksi'][$i]['coa_nama_akun'], $contents);
				//$contents = str_replace('[DEBET]', "", $contents);
				//$contents = str_replace('[KREDIT]', "", $contents);
                if (!empty($data['transaksi'][$i]['saldo_awal']))
                    $send[$x]['saldo']= $data['transaksi'][$i]['saldo_awal'];//$saldoTran['saldo_awal_transaksi'];
                    else $send[$x]['saldo'] = 0;				
            	//$saldoTran = $Obj->GetSaldoTransaksi($data['transaksi'][$i]['coa_id'], $data['tgl_transaksi']);
            	//if (!empty($saldoTran['saldo_awal_transaksi'])) $send[$x]['saldo'] = $saldoTran['saldo_awal_transaksi']; else $send[$x]['saldo'] = 0;
				$saldo = $send[$x]['saldo'];
				//$contents = str_replace('[SALDO]', $saldo, $contents);
				//$i++;
          	} 
				$x++;
				$send[$x]['no'] = $no;
				$send[$x]['no_rekening'] = ' - ';
				$send[$x]['keterangan'] = $data['transaksi'][$i]['transaksi_catatan'];
				$send[$x]['debet'] = 'NULL';
				$send[$x]['debet'] = 'NULL';
				//$contents = str_replace('[NO]', $no, $contents);
				//$contents = str_replace('[NO_REKENING]', ' - ', $contents);
				//$contents = str_replace('[KETERANGAN]', $data['transaksi'][$i]['transaksi_catatan'], $contents);
				if ($data['transaksi'][$i]['status_pembukuan']=='D') {
					if ($data['transaksi'][$i]['coa_status_debet']!=1) $kDebet += (2*$data['transaksi'][$i]['transaksi_nilai']);
					$debet += $data['transaksi'][$i]['transaksi_nilai'];
					$debKred = '[DEBET]'; 
					$send[$x]['debet'] = $data['transaksi'][$i]['transaksi_nilai'];
					$send[$x]['kredit'] = 'NULL';
				} else {
					if ($data['transaksi'][$i]['coa_status_debet']!=0) $dKredit += (2*$data['transaksi'][$i]['transaksi_nilai']);
					$kredit += $data['transaksi'][$i]['transaksi_nilai'];
					$debKred = '[KREDIT]';
					$send[$x]['debet'] = 'NULL';
					$send[$x]['kredit'] = $data['transaksi'][$i]['transaksi_nilai'];
				}
				$send[$x]['saldo'] = 'NULL';
				//$contents = str_replace($debKred, $data['transaksi'][$i]['transaksi_nilai'], $contents);
				//$contents = str_replace('[SALDO]', "", $contents);
				$no++;
			
         	if ($data['transaksi'][$i]['coa_kode_akun']!=$data['transaksi'][$i+1]['coa_kode_akun']) {
				$x++;
				$send[$x]['no'] = '';
				$send[$x]['no_rekening'] = '';
				$send[$x]['keterangan'] = '{\b   Sub Total}';
				$send[$x]['debet'] = 'NULL';
				$send[$x]['debet'] = 'NULL';
				//$contents = str_replace('[NO]', '', $contents);
				//$contents = str_replace('[NO_REKENING]', '', $contents);
				//$contents = str_replace('[KETERANGAN]', 'Sub Total', $contents);
				if ($debet!=0) $debetRp=$debet; else $debetRp='';
            	if ($kredit!=0) $kreditRp=$kredit; else $kreditRp='';
				$send[$x]['debet'] = $debetRp;
				$send[$x]['kredit'] = $kreditRp;
				$send[$x]['saldo'] = $saldo+$debet+$kredit-$kDebet-$dKredit;
				//$contents = str_replace('[DEBET]', $debetRp, $contents);
				//$contents = str_replace('[KREDIT]', $kreditRp, $contents);
				//$contents = str_replace('[SALDO]', $saldo+$debet+$kredit-$kDebet-$dKredit, $contents);       
            	$no=1;
            	$totalDebet += $debet;
            	$totalKredit += $kredit;
				$x++;
            	$debet = $kredit = $saldo = $kDebet = $dKredit = '';
         	}
			//$x++;
			}
			//$contents = str_replace('[STATUS]', 'total', $contents);
			//$send[$x]['no'] = '';
			//$send[$x]['no_rekening'] = '';
			//$send[$x]['keterangan'] = 'Grand Total';
			//$send[$x]['debet'] = $totalDebet;
			//$send[$x]['kredit'] = $totalKredit;
			//$send[$x]['saldo'] = '';
			//$contents = str_replace('[NO]', '', $contents);
			//$contents = str_replace('[NO_REKENING]', '', $contents);
			//$contents = str_replace('[KETERANGAN]', 'Grand Total', $contents);
			//$contents = str_replace('[DEBET]', $totalDebet, $contents);
			//$contents = str_replace('[KREDIT]', $totalKredit, $contents);
			//$contents = str_replace('[SALDO]', '', $contents);
			//print_r($send);exit();
			for($j=0;$j<sizeof($send);$j++) {
			    $nomor .= ' '.$send[$j]['no'].'\par';
				$rekening .= ' '.$send[$j]['no_rekening'].'\par';
				$keterangan .= ' '.$send[$j]['keterangan'].'\par';
				if($send[$j]['debet'] == 'NULL') $debet .= '\par';
				else $debet .= ' '.number_format($send[$j]['debet'], 2, ',', '.').'\par';
				if($send[$j]['kredit'] == 'NULL') $kredit .= '\par';
				else $kredit .= ' '.number_format($send[$j]['kredit'], 2, ',', '.').'\par';
				if($send[$j]['saldo'] == 'NULL') $saldo .= '\par';
				else $saldo .= ' '.number_format($send[$j]['saldo'], 2, ',', '.').'\par';
				//$debet .= ' '.$send[$j]['debet'].'\par';
				//$kredit .= ' '.$send[$j]['kredit'].'\par';
				//$saldo .= ' '.$send[$j]['saldo'].'\par';
			}
			$contents = str_replace('[NO]', $nomor, $contents);
			$contents = str_replace('[NO_REKENING]', $rekening, $contents);
			$contents = str_replace('[KETERANGAN]', $keterangan, $contents);
			$contents = str_replace('[DEBET]', $debet, $contents);
			$contents = str_replace('[KREDIT]', $kredit, $contents);
			$contents = str_replace('[SALDO]', $saldo, $contents);
			
			$totDebet .= ' '.number_format($totalDebet, 2, ',', '.').'\par';
			$totKredit .= ' '.number_format($totalKredit, 2, ',', '.').'\par';
			$contents = str_replace('[TOT_DEBET]', $totDebet, $contents);
			$contents = str_replace('[TOT_KREDIT]', $totKredit, $contents);

		}
		
		header("Content-type: application/msword");
		header("Content-disposition: inline; filename=laporanTransaksiHarian_".date('d-m-Y').".rtf");
		header("Content-length: " . strlen($contents));
		print $contents;
	
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
