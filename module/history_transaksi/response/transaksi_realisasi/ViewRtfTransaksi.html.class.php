<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/transaksi_realisasi/business/AppTransaksi.class.php';

class ViewRtfTransaksi extends HtmlResponse {	
	
	function ProcessRequest() {	
		$Obj = new AppTransaksi();
		$pos = $_GET->AsArray();
		$cetak = $pos;
		
		$contents = file_get_contents(GTFWConfiguration::GetValue( 'application', 'docroot')."doc/template_Transaksi.rtf");
		$contents = str_replace('[NAMA_COMPANY]',GTFWConfiguration::GetValue('organization', 'company_name'), $contents);
		
		$contents = str_replace('[TAHUN_ANGGARAN]', $cetak['tahun_anggaran'], $contents);
		$contents = str_replace('[NOMOR_BUKTI]', $cetak['nomor_bukti'], $contents);
		$contents = str_replace('[MAK]', $cetak['mak'], $contents);
		$contents = str_replace('[JUDUL]', $cetak['judul'], $contents);
		$contents = str_replace('[TERIMA_DARI]', $cetak['terima_dari'], $contents);
		$contents = str_replace('[NILAI_LABEL]', number_format($cetak['nilai'], 2, ',', '.'), $contents);
		$contents = str_replace('[NILAI_TERBILANG]', $this->terbilang($cetak['nilai']), $contents);
		$contents = str_replace('[UNTUK_PEMBAYARAN]', $cetak['untuk_pembayaran'], $contents);
		$contents = str_replace('[TGL_PEMBAYARAN]', $cetak['tgl_pembayaran'], $contents);
		$contents = str_replace('[PEMBUAT_KWITANSI]', $cetak['pembuat_kwitansi'], $contents);
		$contents = str_replace('[TGL_PEMBAYARAN]', $cetak['tgl_pembayaran'], $contents);
		
		header("Content-type: application/msword");
		header("Content-disposition: inline; filename=transaksi_".date('d-m-Y').".rtf");
		header("Content-length: " . strlen($contents));
		print $contents;
    }
	
	function kekata($x) {
		$x = abs($x);
		$angka = array("", "satu", "dua", "tiga", "empat", "lima",
		"enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas");
		$temp = "";
		if ($x <12) {
			$temp = " ". $angka[$x];
		} else if ($x <20) {
			$temp = $this->kekata($x - 10). " belas";
		} else if ($x <100) {
			$temp = $this->kekata($x/10)." puluh". $this->kekata($x % 10);
		} else if ($x <200) {
			$temp = " seratus" . $this->kekata($x - 100);
		} else if ($x <1000) {
			$temp = $this->kekata($x/100) . " ratus" . $this->kekata($x % 100);
		} else if ($x <2000) {
			$temp = " seribu" . $this->kekata($x - 1000);
		} else if ($x <1000000) {
			$temp = $this->kekata($x/1000) . " ribu" . $this->kekata($x % 1000);
		} else if ($x <1000000000) {
			$temp = $this->kekata($x/1000000) . " juta" . $this->kekata($x % 1000000);
		} else if ($x <1000000000000) {
			$temp = $this->kekata($x/1000000000) . " milyar" . $this->kekata(fmod($x,1000000000));
		} else if ($x <1000000000000000) {
			$temp = $this->kekata($x/1000000000000) . " trilyun" . $this->kekata(fmod($x,1000000000000));
		}
		return $temp;
   }
   
	function terbilang($x, $style=4) {
		if($x<0) {
			$hasil = "minus ". trim($this->kekata($x));
		} else {
			$hasil = trim($this->kekata($x));
		}
		switch ($style) {
			case 1:
				$hasil = strtoupper($hasil);
				break;
			case 2:
				$hasil = strtolower($hasil);
				break;
			case 3:
				$hasil = ucwords($hasil);
				break;
			default:
				$hasil = ucfirst($hasil);
				break;
		}
		return $hasil.' Rupiah' ;
	}
	 
}
?>
