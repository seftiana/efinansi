<?php
require_once GTFWConfiguration::GetValue('application','docroot').
'module/spp_cetak/business/spp.class.php';

class ViewCetakSpp extends HtmlResponse{
	function TemplateModule(){
		$this->setTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
		'module/spp_cetak/template/');
		$this->setTemplateFile('cetak_spp.html');
	}
	function TemplateBase() {
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
		$this->SetTemplateFile('document-print.html');
		$this->SetTemplateFile('layout-common-print.html');
	}
	function ProcessRequest(){
		$id_spp		= Dispatcher::Instance()->Decrypt($_GET['id']);
		$ta			= $_GET['ta'];
		$unit		= $_GET['unit'];
		$obj		= new Spp();
		$data		= $obj->GetDataById($id_spp,$ta,$unit);
		$bln_array	= array('01'=>'Januari',
							'02'=>'Februari',
							'03'=>'Maret',
							'04'=>'April',
							'05'=>'Mei',
							'06'=>'Juni',
							'07'=>'Juli',
							'08'=>'Agustus',
							'09'=>'September',
							'10'=>'Oktober',
							'11'=>'Nopember',
							'12'=>'Desember');
		$bln		= date('m', time());
		$return['tanggal']	= date('d', time()).' '.$bln_array[$bln].' '.date('Y', time());
		$return['data']		= $data;
		return $return;
	}

	function ParseTemplate($data = null){
		$dataList	= $data['data'];
		
		$id							= $dataList['id'];
		$nomor_spp					= $dataList['nomor_spp'].'/H46.PPK/SPP/IV/'.date('Y', time());
		$kode_sifat_pembayaran 		= $dataList['kode_sifat_pembayaran'];
		$nama_sifat_pembayaran 		= $dataList['nama_sifat_pembayaran'];
		$kode_jenis_pembayaran 		= $dataList['kode_jenis_pembayaran'];
		$nama_jenis_pembayaran 		= $dataList['nama_jenis_pembayaran'];
		$id_spp_det 				= $dataList['id_spp_det'];
		$nilai_pengeluaran_approve 	= number_format($dataList['nilai_pengeluaran_approve'],2,'.',',');
		$keperluan 					= $dataList['keperluan'];
		$jenis_belanja 				= $dataList['jenis_belanja'];
		$spp_nama 					= $dataList['spp_nama'];
		$alamat						= $dataList['alamat'];
		$rekening 					= $dataList['rekening'];
		$nilai_spk 					= ($dataList['nilai_spk'] == 0)? '' : $dataList['nilai_spk'];
		$spp_total 					= number_format($dataList['spp_total'],2,',','.');
		$nominal_detail 			= number_format($dataList['nominal_detail'],2,',','.');
		$mak_kode					= $dataList['mak_kode'];
		$mak_nama					= $dataList['mak_nama'];
		$tanggal					= $data['tanggal'];
		$terbilang					= $this->terbilang($dataList['spp_total'],3);
		$pagu_dipa					= number_format($dataList['nominal_dipa'],2,',','.');
		$spp_saat_ini				= number_format($dataList['jml_nominal_spp'],2,',','.');
		$sisa_dana					= number_format(($dataList['nominal_dipa']-$dataList['jml_nominal_spp']),2,'.',',');
		
		$this->mrTemplate->AddVar('content','TANGGAL',$tanggal);
		$this->mrTemplate->AddVar('content','NOMOR_SPP',$nomor_spp);
		$this->mrTemplate->AddVar('content','KD_SIFAT_PEMBAYARAN',$kode_sifat_pembayaran);
		$this->mrTemplate->AddVar('content','SIFAT_PEMBAYARAN',$nama_sifat_pembayaran);
		$this->mrTemplate->AddVar('content','KD_JENIS_PEMBAYARAN',$kode_jenis_pembayaran);
		$this->mrTemplate->AddVar('content','JENIS_PEMBAYARAN',$nama_jenis_pembayaran);
		$this->mrTemplate->AddVar('content','NILAI_PENGELUARAN_APP',$nilai_pengeluaran_approve);
		$this->mrTemplate->AddVar('content','KEPERLUAN',$keperluan);
		$this->mrTemplate->AddVar('content','JENIS_BELANJA',$jenis_belanja);
		$this->mrTemplate->AddVar('content','ATAS_NAMA',$spp_nama);
		$this->mrTemplate->AddVar('content','ALAMAT',$alamat);
		$this->mrTemplate->AddVar('content','REKENING',$rekening);
		$this->mrTemplate->AddVar('content','NILAI_SPK',$nilai_spk);
		$this->mrTemplate->AddVar('content','SPP_TOTAL',$spp_total);
		$this->mrTemplate->AddVar('content','TERBILANG',$terbilang);
		$this->mrTemplate->AddVar('content','NOMINAL_DETIL',$nominal_detail);
		$this->mrTemplate->AddVar('content','MAK_KODE',$mak_kode);
		$this->mrTemplate->AddVar('content','MAK_NAMA',$mak_nama);
		$this->mrTemplate->AddVar('content','NOMINAL_DIPA',$pagu_dipa);
		$this->mrTemplate->AddVar('content','SPP_SAAT_INI',$spp_saat_ini);
		$this->mrTemplate->AddVar('content','SISA_DANA',$sisa_dana);
	}
	function bilang($x) {
		$x = abs($x);
		$angka = array("", "satu", "dua", "tiga", "empat", "lima",
		"enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas");
		$result = "";
		if ($x <12) {
			$result = " ". $angka[$x];
		} else if ($x <20) {
			$result = $this->bilang($x - 10). " belas";
		} else if ($x <100) {
			$result = $this->bilang($x/10)." puluh". $this->bilang($x % 10);
		} else if ($x <200) {
			$result = " seratus" . bilang($x - 100);
		} else if ($x <1000) {
			$result = $this->bilang($x/100) . " ratus" . $this->bilang($x % 100);
		} else if ($x <2000) {
			$result = " seribu" . bilang($x - 1000);
		} else if ($x <1000000) {
			$result = $this->bilang($x/1000) . " ribu" . $this->bilang($x % 1000);
		} else if ($x <1000000000) {
			$result = $this->bilang($x/1000000) . " juta" . $this->bilang($x % 1000000);
		} else if ($x <1000000000000) {
			$result = $this->bilang($x/1000000000) . " milyar" . $this->bilang(fmod($x,1000000000));
		} else if ($x <1000000000000000) {
			$result = $this->bilang($x/1000000000000) . " trilyun" . $this->bilang(fmod($x,1000000000000));
		}      
			return $result;
	}
	function terbilang($x, $style=4) {
		if($x<0) {
			$hasil = "minus ". trim($this->bilang($x));
		} else {
			$hasil = trim($this->bilang($x));
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
		return $hasil.' Rupiah';
	}
}
?>