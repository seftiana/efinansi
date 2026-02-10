<?php

require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
				'module/history_transaksi_realisasi_penerimaan/business/AppTransaksi.class.php';



class ViewCetakTransaksi extends HtmlResponse {

	var $Pesan;

	function TemplateModule() {
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot') .
		'module/history_transaksi_realisasi_penerimaan/template');
		$this->SetTemplateFile('view_cetak_transaksi.html');
	}

    function TemplateBase() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
      $this->SetTemplateFile('document-print.html');
      $this->SetTemplateFile('layout-common-print.html');
   }

	function ProcessRequest() {
		$Obj = new AppTransaksi();
      $pos = $_GET->AsArray();
      //print_r($pos);
		$return['transaksi'] = $pos;
		return $return;
	}

	function ParseTemplate($data = NULL) {
      $cetak = $data['transaksi'];
      $this->mrTemplate->AddVar('content', 'TAHUN_ANGGARAN', $cetak['tahun_anggaran']);
      $this->mrTemplate->AddVar('content', 'NOMOR_BUKTI', $cetak['nomor_bukti']);
      $this->mrTemplate->AddVar('content', 'MAK', $cetak['mak']);
      $this->mrTemplate->AddVar('content', 'JUDUL', $cetak['judul']);
      $this->mrTemplate->AddVar('content', 'TERIMA_DARI', $cetak['terima_dari']);
      $this->mrTemplate->AddVar('content', 'NILAI_LABEL', number_format($cetak['nilai'], 2, '.', ','));
      $this->mrTemplate->AddVar('content', 'NILAI_TERBILANG', $this->terbilang($cetak['nilai']));
      $this->mrTemplate->AddVar('content', 'UNTUK_PEMBAYARAN', $cetak['untuk_pembayaran']);
      $this->mrTemplate->AddVar('content', 'TGL_PEMBAYARAN', $cetak['tgl_pembayaran']);
      $this->mrTemplate->AddVar('content', 'PEMBUAT_KWITANSI', $cetak['pembuat_kwitansi']);
      $this->mrTemplate->AddVar('content', 'TGL_PEMBAYARAN', $cetak['tgl_pembayaran']);
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