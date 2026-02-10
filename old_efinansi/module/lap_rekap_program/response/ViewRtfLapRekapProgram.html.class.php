<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/lap_rekap_program/business/AppLapRekapProgram.class.php';

'module/tahun_pembukuan/business/TahunPembukuanPeriode.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'main/function/date.php';

class ViewRtfLapRekapProgram extends HtmlResponse {

	function ProcessRequest() {
		$Obj = new AppLapRekapProgram();
		$tahun_anggaran = Dispatcher::Instance()->Decrypt($_GET['tahun_anggaran']);
		$program = Dispatcher::Instance()->Decrypt($_GET['program']);
		$unitkerja = Dispatcher::Instance()->Decrypt($_GET['unitkerja']);
		$jenis_kegiatan = Dispatcher::Instance()->Decrypt($_GET['jenis_kegiatan']);
		$data = $Obj->GetCetakData($tahun_anggaran, $program, $jenis_kegiatan, $unitkerja);

		$data_tahun_anggaran = $Obj->GetTahunAnggaranById($tahun_anggaran);
		if(empty($data_tahun_anggaran)) $data_tahun_anggaran['nama'] = ' Semua ';

		$data_program = $Obj->GetProgramById($program);
		if(empty($data_program)) $data_program['nama'] = ' Semua ';

		$data_unitkerja = $Obj->GetUnitkerjaById($unitkerja);

		if(empty($data_unitkerja)) $data_unitkerja['nama'] = ' Semua ';

      $unitData = $Obj->GetUnitIdentity($_GET['unitkerja']);

		$data_jenis_kegiatan = $Obj->GetTahunAnggaranById($jenis_kegiatan);
		if(empty($data_jenis_kegiatan)) $data_jenis_kegiatan['nama'] = ' Semua ';

		$return['data'] = $data;
		$return['data_tahun_anggaran'] = $data_tahun_anggaran;
		$return['data_program'] = $data_program;
		$return['data_unitkerja'] = $data_unitkerja;
		$return['data_jenis_kegiatan'] = $data_jenis_kegiatan;
      $return['unit_kerja'] = $unitData['0'];
		return $return;
	}

	function ParseTemplate($data = NULL) {

		$contents = file_get_contents(GTFWConfiguration::GetValue( 'application', 'docroot')."doc/template_LapRekapProgram.rtf");
		$contents = str_replace('[NAMA_COMPANY]',GTFWConfiguration::GetValue('organization', 'company_name'), $contents);
		$contents = str_replace('[KOTA]',GTFWConfiguration::GetValue('application', 'city'), $contents);
		$contents = str_replace('[HEADER]','LAPORAN REKAPITULASI PROGRAM PENGELUARAN', $contents);
		$contents = str_replace('[TAHUN_ANGGARAN]', $data['data_tahun_anggaran']['nama'], $contents);
		$contents = str_replace('[PROGRAM]', $data['data_program']['nama'], $contents);
		$contents = str_replace('[UNITKRJA]', $data['data_unitkerja']['nama'], $contents);
		$contents = str_replace('[JENIS_KEGIATAN]', $data['data_jenis_kegiatan']['nama'], $contents);

      $contents = str_replace('[CITY]',GTFWConfiguration::GetValue('organization', 'city'), $contents);

		$contents = str_replace('[UNIT_KERJA]', $data['unit_kerja']['unitkerjaNama'], $contents);
         $date=date('d-m-Y');
         $date = IndonesianDate($date,'dd-mm-yyyy');
		$contents = str_replace('[DATE]', $date, $contents);

         $pimpinan = $data['unit_kerja']['unitkerjaNamaPimpinan'];

         if(!empty($pimpinan))
			$contents = str_replace('[NAMA_PIMPINAN]', $pimpinan, $contents);
         else
            $contents = str_replace('[NAMA_PIMPINAN]', '..........................................', $contents);

			$x=0;
			$kodeProg = ''; $kodeKegiatan = ''; $kodeSubKegiatan='';

         $data_list = $data['data'];

			for ($i=0; $i<sizeof($data_list);) {

				 if(($data_list[$i]['kodeProg'] == $kodeProg) && ($data_list[$i]['kodeKegiatan'] == $kodeKegiatan)) {

					$send[$x]['kode'] = $data_list[$i]['kodeSubKegiatan'];
					$send[$x]['program_kegiatan'] = $data_list[$i]['namaSubKegiatan'];
					$send[$x]['unitkerja'] = $data_list[$i]['unitName'];
					$send[$x]['nominal_usulan'] = $data_list[$i]['nominalUsulan'];
					$send[$x]['nominal_setuju'] = $data_list[$i]['nominalSetuju'];
					$send[$x]['deskripsi'] = $data_list[$i]['deskripsi'];
					$send[$x]['class_button'] = "";
					$send[$x]['jenis'] = "subkegiatan";
					$i++;
				 } elseif($data_list[$i]['kodeProg'] != $kodeProg) {
					$kodeProg = $data_list[$i]['kodeProg'];

					$send[$x]['kode'] = $data_list[$i]['kodeProg'];
					$send[$x]['program_kegiatan'] = $data_list[$i]['namaProgram'];
					$send[$x]['unitkerja'] = "";
					$send[$x]['nominal_usulan'] = "";
					$send[$x]['nominal_setuju'] = "";
					$send[$x]['deskripsi'] = '';
					$send[$x]['class_button'] = 'style="background-color:#CCCCCC"';
					$send[$x]['jenis'] = "program";

				 } elseif($data_list[$i]['kodeKegiatan'] != $kodeKegiatan) {
					$kodeProg = $data_list[$i]['kodeProg'];
					$kodeKegiatan = $data_list[$i]['kodeKegiatan'];

					$send[$x]['kode'] = $data_list[$i]['kodeKegiatan'];
					$send[$x]['program_kegiatan'] = $data_list[$i]['namaKegiatan'];
					$send[$x]['unitkerja'] = "";
					$send[$x]['nominal_usulan'] = "";
					$send[$x]['nominal_setuju'] = "";
					$send[$x]['deskripsi'] = '';
					$send[$x]['class_button'] = 'style="background-color:#DCDCDC"';
					$send[$x]['jenis'] = "kegiatan";
				 }
				 $x++;

			}

			$i = sizeof($send)-1;
			$nominal_usulan=0;
			$nominal_setuju=0;
			while($i >= 0) {
				if($send[$i]['jenis'] == 'subkegiatan') {
					$nominal_usulan += $send[$i]['nominal_usulan'];
					$nominal_setuju += $send[$i]['nominal_setuju'];
				}
				if($send[$i]['jenis'] == 'kegiatan') {
					$send[$i]['nominal_usulan'] = $nominal_usulan;
					$send[$i]['nominal_setuju'] = $nominal_setuju;
					$nominal_usulan_program += $nominal_usulan;
					$nominal_setuju_program += $nominal_setuju;
					$nominal_usulan=0;
					$nominal_setuju=0;
				}
				if($send[$i]['jenis'] == 'program') {
					$send[$i]['nominal_usulan'] = $nominal_usulan_program;
					$send[$i]['nominal_setuju'] = $nominal_setuju_program;
					$nominal_usulan_program = 0;
					$nominal_setuju_program = 0;
				}
				$i--;
			}

			for($j=0;$j<sizeof($send);$j++) {
				 $send[$j]['nominal_usulan'] = number_format($send[$j]['nominal_usulan'], 0, ',', '.');
				 $send[$j]['nominal_setuju'] = number_format($send[$j]['nominal_setuju'], 0, ',', '.');
				 if($send[$j]['jenis'] == 'program') {
				   $resume[] = $send[$j];
				 }
				$kode .= ' '.$send[$j]['kode'].'\par';
				$program .='\f1\fs16'.$send[$j]['program_kegiatan'].'\par';
				$unitKerja .='\f1\fs16'.$send[$j]['unitkerja'].'\par';
				$deskripsi .='\f1\fs16'.$send[$j]['deskripsi'].'\par';
				$nominalSetuju .= ' '.$send[$j]['nominal_setuju'].'\par';
				$nominalUsulan .= ' '.$send[$j]['nominal_usulan'].'\par';
			}

         for($k=0;$k<sizeof($resume);$k++) {
            if($k % 2 == 0) $resume[$k]['class_button'] = 'style="background-color:#DCDCDC"';
            else $resume[$k]['class_button'] = "";
				$resumeKode .= ' '.$resume[$k]['kode'].'\par';
				$resumeProgram .='\f1\fs16'.$resume[$k]['program_kegiatan'].'\par';
				$resumeNominalSetuju .= ' '.$resume[$k]['nominal_setuju'].'\par';
				$resumeNominalUsulan .= ' '.$resume[$k]['nominal_usulan'].'\par';
         }

		$contents = str_replace('[KODE]', $kode, $contents);
		$contents = str_replace('[PROGRAM_KEGIATAN]', $program, $contents);
		$contents = str_replace('[NOMINAL_SETUJU]', $nominalSetuju, $contents);
		$contents = str_replace('[NOMINAL_USULAN]', $nominalUsulan, $contents);
		$contents = str_replace('[UNITKERJA]', $unitKerja, $contents);
		$contents = str_replace('[DESKRIPSI]', $deskripsi, $contents);

		$contents = str_replace('[RESUME_KODE]', $resumeKode, $contents);
		$contents = str_replace('[RESUME_PROGRAM]', $resumeProgram, $contents);
		$contents = str_replace('[RESUME_NOMINAL_SETUJU]', $resumeNominalSetuju, $contents);
		$contents = str_replace('[RESUME_NOMINAL_USULAN]', $resumeNominalUsulan, $contents);

		header("Content-type: application/msword");
		header("Content-disposition: inline; filename=laporanRekapProgram_".date('d-m-Y').".rtf");
		header("Content-length: " . strlen($contents));
		print $contents;

	}
}
?>