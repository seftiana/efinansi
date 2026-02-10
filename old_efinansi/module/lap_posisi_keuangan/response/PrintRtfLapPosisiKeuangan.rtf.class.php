<?php
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'module/lap_posisi_keuangan/business/AppLapPosisiKeuangan.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'main/function/date.php';

class PrintRtfLapPosisiKeuangan extends RtfResponse
{
	function __construct()
	{
		$this->arrBulan = array(
			"Januari",
			"Februari",
			"Maret",
			"April",
			"Mei",
			"Juni",
			"Juli",
			"Agustus",
			"September",
			"Oktober",
			"November",
			"Desember"
		);
	}
	function GetFileName()
	{

		return "laporanPosisiKeuangan_" . date('d-m-Y') . ".rtf";
	}
	function ProcessRequest()
	{
		$Obj = new AppLapPosisiKeuangan;

		#get data from $_GET
		$tgl_awal = Dispatcher::Instance()->Decrypt($_GET['tgl_awal']);
      $tgl_akhir = Dispatcher::Instance()->Decrypt($_GET['tgl_akhir']);

		#get data by tanggal
		$gridList = $Obj->GetLaporanAll($tgl_akhir,$tgl_awal);

		#set rtf contents
		$contents = (GTFWConfiguration::GetValue('application', 'docroot') . "doc/template_LaporanPosisiKeuangan.rtf");

		$this->rtf->SetContent($contents);

		#set company name to rtf content
		$this->rtf->AddVar('COMPANY_NAME',GTFWConfiguration::GetValue('application', 'company_name'));

		#set company address to rtf content
		$this->rtf->AddVar('KOTA',GTFWConfiguration::GetValue('application', 'city'));

      $this->rtf->AddVar('CITY',GTFWConfiguration::GetValue('organization', 'city'));

      $this->rtf->AddVar('NAMA_KEP_BAU',GTFWConfiguration::GetValue('organization', 'kep_bau'));
      $this->rtf->AddVar('NAMA_PUKET1',GTFWConfiguration::GetValue('organization', 'puket1'));
      $this->rtf->AddVar('NAMA_KETUA',GTFWConfiguration::GetValue('organization', 'ketua'));

		#set header
		$this->rtf->AddVar('HEADER','LAPORAN POSISI KEUANGAN');
		$interval_waktu = IndonesianDate($tgl_akhir,'yyyy-mm-dd');
		$this->rtf->AddVar('INTERVAL_WAKTU',$interval_waktu);

		for ($i = 0;$i < count($gridList);$i++){
			if ($gridList[$i]['kelJnsNama'] == 'Aktiva Lancar'){
				$aktivaLancar[$i] = $gridList[$i]['nama_kel_lap'];

				if ($gridList[$i]['nilai'] < 0)
					$nominalAktivaLancar[$i].= ' ' . '(' . number_format(str_replace('-', '', $gridList[$i]['nilai']) , 2, ',', '.') . ')';
				else
					$nominalAktivaLancar[$i].= ' ' . number_format($gridList[$i]['nilai'], 2, ',', '.');

				$totalAktivaLancar+= $gridList[$i]['nilai'];
			}

			if ($gridList[$i]['kelJnsNama'] == 'Aktiva Tidak Lancar')
			{
				$aktivaTidakLancar[$i].= $gridList[$i]['nama_kel_lap'];

				if ($gridList[$i]['nilai'] < 0)
					$nominalAktivaTidakLancar[$i].= ' ' . '(' . number_format(str_replace('-', '', $gridList[$i]['nilai']) , 2, ',', '.') . ')';
				else
					$nominalAktivaTidakLancar[$i].= ' ' . number_format($gridList[$i]['nilai'], 2, ',', '.');

				$totalAktivaTidakLancar+= $gridList[$i]['nilai'];
			}

			if ($gridList[$i]['kelJnsNama'] == 'Kewajiban Jangka Pendek')
			{
				$jangkaPendek[$i].= $gridList[$i]['nama_kel_lap'];

				if ($gridList[$i]['nilai'] < 0)
					$nominalJangkaPendek[$i].= ' ' . '(' . number_format(str_replace('-', '', $gridList[$i]['nilai']) , 2, ',', '.') . ')';
				else
					$nominalJangkaPendek[$i].= ' ' . number_format($gridList[$i]['nilai'], 2, ',', '.');

				$totalJangkaPendek+= $gridList[$i]['nilai'];
			}

			if ($gridList[$i]['kelJnsNama'] == 'Kewajiban Jangka Panjang')
			{
				$jangkaPanjang[$i] = $gridList[$i]['nama_kel_lap'];

				if ($gridList[$i]['nilai'] < 0)
					$nominalJangkaPanjang[$i].= ' ' . '(' . number_format(str_replace('-', '', $gridList[$i]['nilai']) , 2, ',', '.') . ')';
				else
					$nominalJangkaPanjang[$i].= ' ' . number_format($gridList[$i]['nilai'], 2, ',', '.');

				$totalJangkaPanjang+= $gridList[$i]['nilai'];
			}

			if ($gridList[$i]['kelJnsNama'] == 'Aktiva Bersih')
			{
				$aktivaBersih[$i].= $gridList[$i]['nama_kel_lap'];

				if ($gridList[$i]['nilai'] < 0)
					$nominalAktivaBersih[$i].= ' ' . '(' . number_format(str_replace('-', '', $gridList[$i]['nilai']) , 2, ',', '.') . ')';
				else
				$nominalAktivaBersih[$i].= ' ' . number_format($gridList[$i]['nilai'], 2, ',', '.');

				$totalAktivaBersih+= $gridList[$i]['nilai'];
			}
		}

		#set rtf data array aktiva lancar
		$this->rtf->AddVars('AKTIVA_LANCAR',$aktivaLancar);
		$this->rtf->AddVars('NOMINAL_AKTIVA_LANCAR',$nominalAktivaLancar);

		if ($totalAktivaLancar < 0)
			$totalAktivaLancar = '(' . number_format(str_replace('-', '', $totalAktivaLancar) , 2, ',', '.') . ')';
		else
			$totalAktivaLancar = number_format($totalAktivaLancar, 2, ',', '.');
		$this->rtf->AddVar('TOTAL_AKTIVA_LANCAR',$totalAktivaLancar);

		#set rtf data array aktiva tidak lancar
		$this->rtf->AddVars('AKTIVA_TIDAK_LANCAR', $aktivaTidakLancar);
		$this->rtf->AddVars('NOMINAL_TIDAK_LANCAR', $nominalAktivaTidakLancar);

		if ($totalAktivaTidakLancar < 0)
			$this->rtf->AddVar('TOTAL_TIDAK_LANCAR', '(' . number_format(str_replace('-', '', $totalAktivaTidakLancar) , 2, ',', '.') . ')');
		else
			$this->rtf->AddVar('TOTAL_TIDAK_LANCAR', number_format($totalAktivaTidakLancar, 2, ',', '.'));

		if ($totalAktiva < 0)
			$this->rtf->AddVar('TOTAL_AKTIVA', '(' . number_format(str_replace('-', '', $totalAktiva) , 2, ',', '.') . ')');
		else
			$this->rtf->AddVar('TOTAL_AKTIVA', number_format($totalAktiva, 2, ',', '.'));

		$this->rtf->AddVars('KEWAJIBAN_JANGKA_PENDEK', $jangkaPendek);
		$this->rtf->AddVars('NOMINAL_JANGKA_PENDEK', $nominalJangkaPendek);

		if ($totalJangkaPendek < 0)
			$this->rtf->AddVar('TOTAL_JANGKA_PENDEK', '(' . number_format(str_replace('-', '', $totalJangkaPendek) , 2, ',', '.') . ')');
		else
			$this->rtf->AddVar('TOTAL_JANGKA_PENDEK', number_format($totalJangkaPendek, 2, ',', '.'));

		$this->rtf->AddVars('KEWAJIBAN_JANGKA_PANJANG', $jangkaPanjang);
		$this->rtf->AddVars('NOMINAL_JANGKA_PANJANG', $nominalJangkaPanjang);

		if ($totalJangkaPanjang < 0)
			$this->rtf->AddVar('TOTAL_JANGKA_PANJANG', '(' . number_format(str_replace('-', '', $totalJangkaPanjang) , 2, ',', '.') . ')');
		else
			$this->rtf->AddVar('TOTAL_JANGKA_PANJANG', number_format($totalJangkaPanjang, 2, ',', '.'));

		$this->rtf->AddVars('AKTIVA_BERSIH',$aktivaBersih);
		$this->rtf->AddVars('NOMINAL_AKTIVA_BERSIH', $nominalAktivaBersih);

		if ($totalAktivaBersih < 0)
			$this->rtf->AddVar('TOTAL_AKTIVA_BERSIH', '(' . number_format(str_replace('-', '', $totalAktivaBersih) , 2, ',', '.') . ')');
		else
			$this->rtf->AddVar('TOTAL_AKTIVA_BERSIH', number_format($totalAktivaBersih, 2, ',', '.'));
		$totalWajibBersih = $totalJangkaPendek + $totalJangkaPanjang + $totalAktivaBersih;

		if ($totalWajibBersih < 0)
			$this->rtf->AddVar('TOTAL_WAJIB_BERSIH', '(' . number_format(str_replace('-', '', $totalWajibBersih) , 2, ',', '.') . ')');
		else
			$this->rtf->AddVar('TOTAL_WAJIB_BERSIH', number_format($totalWajibBersih, 2, ',', '.'));
	}
}
?>