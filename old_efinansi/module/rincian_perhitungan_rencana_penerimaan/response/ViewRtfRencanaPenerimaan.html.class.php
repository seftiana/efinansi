<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/rincian_perhitungan_rencana_penerimaan/business/AppRencanaPenerimaan.class.php';

require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/user_unit_kerja/business/UserUnitKerja.class.php';

class ViewRtfRencanaPenerimaan extends HtmlResponse {

	function ProcessRequest() {

		$Obj = new AppRencanaPenerimaan();
		$tahun_anggaran = Dispatcher::Instance()->Decrypt($_GET['tgl']);
		$userId = Dispatcher::Instance()->Decrypt($_GET['id']);
		$unit_id = Dispatcher::Instance()->Decrypt($_GET['unitkerjaid']);
		$data = $Obj->GetDataUnitkerja($tahun_anggaran,$unit_id);
		$data_jumlah = $Obj->GetDataForTotal($tahun_anggaran, $userId, $unit_id);
		$periode = $Obj->GetTahunAnggaran($tahun_anggaran);
		$jml = count($data_jumlah);
		$tot_jumlah = 0;
		$tot_terima = 0;
		for($i=0;$i<=$jml;$i++){
			$tot_jumlah += $data_jumlah[$i]['tot_jumlah'];
			$tot_terima += $data_jumlah[$i]['tot_terima'];
		}
		$return['data'] = $data;
		$return['periode'] = $periode;
		$return['penerimaan'] = $tot_jumlah;
		$return['jumlah'] = $tot_terima;
		return $return;
		}

		function ParseTemplate($data = NULL) {
		$contents = file_get_contents(GTFWConfiguration::GetValue( 'application', 'docroot')."doc/template_LapRencanaPenerimaan.rtf");
		$date = date('d-m-Y');
		$contents = str_replace('[NAMA_COMPANY]',GTFWConfiguration::GetValue('application', 'company_name'), $contents);
		$contents = str_replace('[HEADER]','RINCIAN PERHITUNGAN RENCANA PENERIMAAN', $contents);
      //$date = IndonesianDate($date,'dd-mm-yyyy');
		$contents = str_replace('[PERIODE]', $data['periode']['name'], $contents);
		$contents = str_replace('[TANGGAL]', $date, $contents);
		$contents = str_replace('[KOTA]', GTFWConfiguration::GetValue('organization', 'city'), $contents);

			$total='';
			$jumlah_total='';
			$idrencana='';
			$idkode='';
			$kode='';
			$nama='';

			$data_list = $data['data'];
			$kode_satker = '';
			$kode_unit = '';
			$nama_satker='';
			$nama_unit='';

		for ($i=0; $i<sizeof($data_list);) {
			if(($data_list[$i]['kode_satker'] == $kode_satker) && ($data_list[$i]['kode_unit'] == $kode_unit)) {
				if($data_list[$i]['idrencana'] == "") {
					$i++; continue;
				}
					$send = $data_list[$i];
					$send['total_penerimaan'] = number_format($data_list[$i]['total'], 0, ',', '.');
					$send['volume'] = $data_list[$i]['volume'];
					$send['pagu'] = $data_list[$i]['pagu'];
					$send['tarif'] = number_format($data_list[$i]['tarif'], 0, ',', '.');
					$send['totalterima'] = number_format($data_list[$i]['total_kali'], 0, ',', '.');

					$send['class_name'] = "";
					$send['nomor'] = $no;
					$send['class_button'] = "links";

					$this->mrTemplate->AddVar('cekbox', 'data_number', $number);
					$this->mrTemplate->AddVar('cekbox', 'data_idrencana', $data_list[$i]['idrencana']);
					$this->mrTemplate->AddVar('cekbox', 'data_nama', $data_list[$i]['nama']);
					$this->mrTemplate->AddVar('cekbox', 'IS_SHOW', 'YES');
					$i++;$no++;$number++;
				} elseif($data_list[$i]['kode_satker'] != $kode_satker && $data_list[$i]['nama_satker'] == $data_list[$i]['nama_unit']) {
					$kode_satker = $data_list[$i]['kode_satker'];
					$kode_unit = $data_list[$i]['kode_unit'];
					$nama_satker = $data_list[$i]['nama_satker'];
					$nama_unit = $data_list[$i]['nama_unit'];
					$send['kode'] = $kode_unit;
					$send['nama'] = $data_list[$i]['nama_unit'];

					$send['total_penerimaan'] = number_format($data_list[$i]['jumlah_total'], 0, ',', '.');
					$send['volume'] = "";
					$send['pagu'] = "";
					$send['tarif'] = "";
					$send['totalterima'] = number_format($data_list[$i]['totalterima'], 0, ',', '.');
					$send['class_name'] = "table-common-even1";
					$send['nomor'] = "";
					$send['class_button'] = "toolbar";
					$no=1;
					// }
				}elseif($data_list[$i]['kode_unit'] != $kode_unit) {
					 $kode_satker = $data_list[$i]['kode_satker'];
					 $kode_unit = $data_list[$i]['kode_unit'];
					 $nama_satker = $data_list[$i]['nama_satker'];
					 $nama_unit = $data_list[$i]['nama_unit'];
					 $send['kode'] = $kode_unit;
					 $send['nama'] = $data_list[$i]['nama_unit'];
					 $send['total_penerimaan'] = number_format($data_list[$i]['jumlah_total'], 0, ',', '.');
					 $send['class_name'] = "";
					 $send['volume'] = "";
					 $send['pagu'] = "";
					 $send['totalterima'] = number_format($data_list[$i]['totalterima'], 0, ',', '.');
					 $send['tarif'] = "";
					 $send['nomor'] = "";
					 $send['class_button'] = "toolbar";

					 $no=1;
				}
					$kode .= ' '.$send['kode'].'\par';
					$nama .= ' '.$send['nama'].'\par';
					$totalpenerimaan .= ' '.$send['total_penerimaan'].'\par';
					$volume .= ' '.$send['volume'].'\par';
					$pagu .= ' '.$send['pagu'].'\par';
					$totalterima .= ' '.$send['totalterima'].'\par';
					$tarif .= ' '.$send['tarif'].'\par';

		}
		$jumlah = ' '.number_format($data['jumlah'], 0, ',', '.').'\par';
		$terima = ' '.number_format($data['penerimaan'], 0, ',', '.')."\par";

		$contents = str_replace('[KODE]', $kode, $contents);
		$contents = str_replace('[NAMA]', $nama, $contents);
		$contents = str_replace('[TOTALPENERIMAAN]', $totalpenerimaan, $contents);
		$contents = str_replace('[VOLUME]', $volume, $contents);
		$contents = str_replace('[PAGU]', $pagu, $contents);
		$contents = str_replace('[TOTALTERIMA]', $totalterima, $contents);
		$contents = str_replace('[TARIF]', $tarif, $contents);
		$contents = str_replace('[JMLTOTAL]', $jumlah, $contents);
		$contents = str_replace('[TTLJML]', $terima, $contents);

		header("Content-type: application/msword");
		header("Content-disposition: inline; filename=laporanRencanaPenerimaan_".date('d-m-Y').".rtf");
		header("Content-length: " . strlen($contents));
		print $contents;

	}
}
?>