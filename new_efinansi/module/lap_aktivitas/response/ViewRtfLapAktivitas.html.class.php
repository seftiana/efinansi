<?php
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'module/lap_aktivitas/business/AppLapAktifitas.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'main/function/date.php';

class ViewRtfLapAktivitas extends HtmlResponse
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

	function ProcessRequest()
	{
		$Obj = new AppLapAktivitas();
		$get = $_GET->AsArray();

		if (!empty($get['tgl_awal'])) $tglAwal = $get['tgl_awal'];
		else $tglAwal = date("Y-01-01");
		if (!empty($get['tgl_akhir'])) $tgl = $get['tgl_akhir'];
		else $tgl = date("Y-m-d");
		$gridList = $Obj->GetLaporanAll($tglAwal,$tgl);
		$contents = file_get_contents(GTFWConfiguration::GetValue('application', 'docroot') . "doc/template_LaporanAktivitas.rtf");
		$contents = str_replace('[NAMA_COMPANY]', GTFWConfiguration::GetValue('organization', 'company_name') , $contents);
		$contents = str_replace('[HEADER]', '\f1\fs25' . 'LAPORAN AKTIVITAS' . '\par', $contents);
		$contents = str_replace('[INTERVAL_WAKTU]', ' ' . IndonesianDate($_GET['tgl_akhir'], 'yyyy-mm-dd') . '\par', $contents);
		$contents = str_replace('[COMPANY_NAME]',GTFWConfiguration::GetValue('organization', 'company_name'), $contents);

		for ($i = 0;$i < count($gridList);$i++)
		{

			if ($gridList[$i]['kelJnsNama'] == 'Pendapatan dari Mahasiswa')
			{
				$pendMhs.= '\f1\fs16' . $gridList[$i]['nama_kel_lap'] . '\par';

				if ($gridList[$i]['nilai'] < 0) $nominalPendMhs.= ' ' . '(' . number_format(str_replace('-', '', $gridList[$i]['nilai']) , 2, ',', '.') . ')' . '\par';
				else $nominalPendMhs.= ' ' . number_format($gridList[$i]['nilai'], 2, ',', '.') . '\par';
				$totalPendMhs+= $gridList[$i]['nilai'];
			}

			if ($gridList[$i]['kelJnsNama'] == 'Pendapatan')
			{
				$pend.= '\f1\fs16' . $gridList[$i]['nama_kel_lap'] . '\par';

				if ($gridList[$i]['nilai'] < 0) $nominalPend.= ' ' . '(' . number_format(str_replace('-', '', $gridList[$i]['nilai']) , 2, ',', '.') . ')' . '\par';
				else $nominalPend.= ' ' . number_format($gridList[$i]['nilai'], 2, ',', '.') . '\par';
				$totalPend+= $gridList[$i]['nilai'];
			}

			if ($gridList[$i]['kelJnsNama'] == 'Beban Penerimaan Mahasiswa Baru')
			{
				$bebanMaba.= '\f1\fs16' . $gridList[$i]['nama_kel_lap'] . '\par';

				if ($gridList[$i]['nilai'] < 0) $nominalBebanMaba.= ' ' . '(' . number_format(str_replace('-', '', $gridList[$i]['nilai']) , 2, ',', '.') . ')' . '\par';
				else $nominalBebanMaba.= ' ' . number_format($gridList[$i]['nilai'], 2, ',', '.') . '\par';
				$totalBebanMaba+= $gridList[$i]['nilai'];
			}

			if ($gridList[$i]['kelJnsNama'] == 'Beban Penyelenggaraan Akademik')
			{
				$bebanAkademik.= '\f1\fs16' . $gridList[$i]['nama_kel_lap'] . '\par';

				if ($gridList[$i]['nilai'] < 0) $nominalBebanAkademik.= ' ' . '(' . number_format(str_replace('-', '', $gridList[$i]['nilai']) , 2, ',', '.') . ')' . '\par';
				else $nominalBebanAkademik.= ' ' . number_format($gridList[$i]['nilai'], 2, ',', '.') . '\par';
				$totalBebanAkademik+= $gridList[$i]['nilai'];
			}

			if ($gridList[$i]['kelJnsNama'] == 'Beban Lain - lain Non Kuliah')
			{
				$bebanLain.= '\f1\fs16' . $gridList[$i]['nama_kel_lap'] . '\par';

				if ($gridList[$i]['nilai'] < 0) $nominalBebanLain.= ' ' . '(' . number_format(str_replace('-', '', $gridList[$i]['nilai']) , 2, ',', '.') . ')' . '\par';
				else $nominalBebanLain.= ' ' . number_format($gridList[$i]['nilai'], 2, ',', '.') . '\par';
				$totalBebanLain+= $gridList[$i]['nilai'];
			}

			if ($gridList[$i]['kelJnsNama'] == 'Beban Administrasi Umum')
			{
				$bebanAdm.= '\f1\fs16' . $gridList[$i]['nama_kel_lap'] . '\par';

				if ($gridList[$i]['nilai'] < 0) $nominalBebanAdm.= ' ' . '(' . number_format(str_replace('-', '', $gridList[$i]['nilai']) , 2, ',', '.') . ')' . '\par';
				else $nominalBebanAdm.= ' ' . number_format($gridList[$i]['nilai'], 2, ',', '.') . '\par';
				$totalBebanAdm+= $gridList[$i]['nilai'];
			}

			if ($gridList[$i]['kelJnsNama'] == 'Beban Non Kurikuler Lain')
			{
				$bebanNon.= '\f1\fs16' . $gridList[$i]['nama_kel_lap'] . '\par';

				if ($gridList[$i]['nilai'] < 0) $nominalBebanNon.= ' ' . '(' . number_format(str_replace('-', '', $gridList[$i]['nilai']) , 2, ',', '.') . ')' . '\par';
				else $nominalBebanNon.= ' ' . number_format($gridList[$i]['nilai'], 2, ',', '.') . '\par';
				$totalBebanNon+= $gridList[$i]['nilai'];
			}
		}

		//-------------------------------------------------------------------------------
		$contents = str_replace('[PENDAPATAN_MHS]', $pendMhs, $contents);
		$contents = str_replace('[NOMINAL_PNDPTN_MHS]', $nominalPendMhs, $contents);

		if ($totalPendMhs < 0) $contents = str_replace('[TOTAL_PNDPTN_MHS]', '(' . number_format(str_replace('-', '', $totalPendMhs) , 2, ',', '.') . ')', $contents);
		else $contents = str_replace('[TOTAL_PNDPTN_MHS]', number_format($totalPendMhs, 2, ',', '.') , $contents);
		$contents = str_replace('[PENDAPATAN_LAIN]', $pend, $contents);
		$contents = str_replace('[NOMINAL_PNDPTN_LAIN]', $nominalPend, $contents);

		if ($totalPend < 0) $contents = str_replace('[TOTAL_PNDPTN_LAIN]', '(' . number_format(str_replace('-', '', $totalPend) , 2, ',', '.') . ')', $contents);
		else $contents = str_replace('[TOTAL_PNDPTN_LAIN]', number_format($totalPend, 2, ',', '.') , $contents);
		$totalPendapatan = $totalPendMhs + $totalPend;

		if ($totalPendapatan < 0) $contents = str_replace('[TOTAL_PENDPTN]', '(' . number_format(str_replace('-', '', $totalPendapatan) , 2, ',', '.') . ')', $contents);
		else $contents = str_replace('[TOTAL_PENDPTN]', number_format($totalPendapatan, 2, ',', '.') , $contents);
		$contents = str_replace('[TERIMA_MABA]', $bebanMaba, $contents);
		$contents = str_replace('[NOMINAL_TERIMA_MABA]', $nominalBebanMaba, $contents);

		if ($totalBebanMaba < 0) $contents = str_replace('[TOTAL_TERIMA_MABA]', '(' . number_format(str_replace('-', '', $totalBebanMaba) , 2, ',', '.') . ')', $contents);
		else $contents = str_replace('[TOTAL_TERIMA_MABA]', number_format($totalBebanMaba, 2, ',', '.') , $contents);
		$contents = str_replace('[BEBAN_AKADEMIK]', $bebanAkademik, $contents);
		$contents = str_replace('[NOMINAL_BEBAN_AKADEMIK]', $nominalBebanAkademik, $contents);

		if ($totalBebanAkademik < 0) $contents = str_replace('[TOTAL_BEBAN_AKADEMIK]', '(' . number_format(str_replace('-', '', $totalBebanAkademik) , 2, ',', '.') . ')', $contents);
		else $contents = str_replace('[TOTAL_BEBAN_AKADEMIK]', number_format($totalBebanAkademik, 2, ',', '.') , $contents);
		$contents = str_replace('[BEBAN_LAIN]', $bebanLain, $contents);
		$contents = str_replace('[NOMINAL_BEBAN_LAIN]', $nominalBebanLain, $contents);

		if ($totalBebanLain < 0) $contents = str_replace('[TOTAL_BEBAN_LAIN]', '(' . number_format(str_replace('-', '', $totalBebanLain) , 2, ',', '.') . ')', $contents);
		else $contents = str_replace('[TOTAL_BEBAN_LAIN]', number_format($totalBebanLain, 2, ',', '.') , $contents);
		$contents = str_replace('[BEBAN_NON]', $bebanNon, $contents);
		$contents = str_replace('[NOMINAL_BEBAN_NON]', $nominalBebanNon, $contents);

		if ($totalBebanNon < 0) $contents = str_replace('[TOTAL_BEBAN_NON]', '(' . number_format(str_replace('-', '', $totalBebanNon) , 2, ',', '.') . ')', $contents);
		else $contents = str_replace('[TOTAL_BEBAN_NON]', number_format($totalBebanNon, 2, ',', '.') , $contents);
		$contents = str_replace('[BEBAN_ADM]', $bebanAdm, $contents);
		$contents = str_replace('[NOMINAL_BEBAN_ADM]', $nominalBebanAdm, $contents);

		if ($totalBebanAdm < 0) $contents = str_replace('[TOTAL_BEBAN_ADM]', '(' . number_format(str_replace('-', '', $totalBebanAdm) , 2, ',', '.') . ')', $contents);
		else $contents = str_replace('[TOTAL_BEBAN_ADM]', number_format($totalBebanAdm, 2, ',', '.') , $contents);
		$totalBeban = $totalBebanMaba + $totalBebanAkademik + $totalBebanLain + $totalBebanNon + $totalBebanAdm;

		if ($totalBeban < 0) $contents = str_replace('[TOTAL_BEBAN]', '(' . number_format(str_replace('-', '', $totalBeban) , 2, ',', '.') . ')', $contents);
		else $contents = str_replace('[TOTAL_BEBAN]', number_format($totalBeban, 2, ',', '.') , $contents);
		$aktivaBersih = $totalPendapatan - $totalBeban;

		if ($totalBeban < 0) $contents = str_replace('[TOTAL_BEBAN_OPERASI]', '(' . number_format(str_replace('-', '', $totalBeban) , 2, ',', '.') . ')', $contents);
		else $contents = str_replace('[TOTAL_BEBAN_OPERASI]', number_format($totalBeban, 2, ',', '.') , $contents);

		if ($aktivaBersih < 0) $contents = str_replace('[AKTIVA_BERSIH]', '(' . number_format(str_replace('-', '', $aktivaBersih) , 2, ',', '.') . ')', $contents);
		else $contents = str_replace('[AKTIVA_BERSIH]', number_format($aktivaBersih, 2, ',', '.') , $contents);
		header("Content-type: application/msword");
		header("Content-disposition: inline; filename=laporanAktivitas_" . date('d-m-Y') . ".rtf");
		header("Content-length: " . strlen($contents));
		print $contents;
		exit;
	}
}
?>
