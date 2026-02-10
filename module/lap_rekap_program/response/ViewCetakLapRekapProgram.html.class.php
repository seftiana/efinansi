<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/lap_rekap_program/business/AppLapRekapProgram.class.php';

'module/tahun_pembukuan/business/TahunPembukuanPeriode.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'main/function/date.php';

class ViewCetakLapRekapProgram extends HtmlResponse {

	var $Pesan;

	function TemplateModule() {
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').'module/lap_rekap_program/template');
		$this->SetTemplateFile('view_cetak_lap_rekap_program.html');
	}

    function TemplateBase() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
      $this->SetTemplateFile('document-print.html');
      $this->SetTemplateFile('layout-common-print.html');
   }

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
      //print_r($data_unitkerja);
		if(empty($data_unitkerja)) $data_unitkerja['nama'] = ' Semua ';
     // print_r($data_unitkerja);

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
		if (empty($data['data'])) {
			$this->mrTemplate->AddVar('data', 'DATA_EMPTY', 'YES');
			$this->mrTemplate->AddVar('resume', 'RESUME_EMPTY', 'YES');
		} else {
			$this->mrTemplate->AddVar('data', 'DATA_EMPTY', 'NO');
			$this->mrTemplate->AddVar('resume', 'RESUME_EMPTY', 'NO');
			$this->mrTemplate->AddVar('content', 'TAHUN_ANGGARAN', $data['data_tahun_anggaran']['nama']);
			$this->mrTemplate->AddVar('content', 'PROGRAM', $data['data_program']['nama']);
			$this->mrTemplate->AddVar('content', 'UNITKERJA', $data['data_unitkerja']['nama']);
			$this->mrTemplate->AddVar('content', 'JENIS_KEGIATAN', $data['data_jenis_kegiatan']['nama']);

         $this->mrTemplate->AddVar('content', 'UNIT_KERJA', $data['unit_kerja']['unitkerjaNama']);
         $date=date('d-m-Y');
         $date = IndonesianDate($date,'dd-mm-yyyy');
         $this->mrTemplate->AddVar('content', 'DATE', $date);

         $pimpinan = $data['unit_kerja']['unitkerjaNamaPimpinan'];

         if(!empty($pimpinan))
            $this->mrTemplate->AddVar('content', 'NAMA_PIMPINAN', $pimpinan);
         else
            $this->mrTemplate->AddVar('content', 'NAMA_PIMPINAN', "..........................................");

			$x=0;
			$kodeProg = ''; $kodeKegiatan = ''; $kodeSubKegiatan='';

         $data_list = $data['data'];

			for ($i=0; $i<sizeof($data_list);) {
				//print_r($data_list[$i]);
				 if(($data_list[$i]['kodeProg'] == $kodeProg) && ($data_list[$i]['kodeKegiatan'] == $kodeKegiatan)) {
					$send[$x]['kode'] = $data_list[$i]['kodeSubKegiatan'];
					$send[$x]['program_kegiatan'] = $data_list[$i]['namaSubKegiatan'];
					$send[$x]['unitkerja'] = $data_list[$i]['unitName'];
					$send[$x]['nominal_usulan'] = $data_list[$i]['nominalUsulan'];
					$send[$x]['nominal_setuju'] = $data_list[$i]['nominalSetuju'];
					$send[$x]['deskripsi'] = $data_list[$i]['deskripsi'];
					$send[$x]['class_button'] = "";
					$send[$x]['jenis'] = "subkegiatan";
					$i++;//$x++;
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
				 $this->mrTemplate->AddVars('data_lap_rekap_program_item', $send[$j], 'DATA_');
				 $this->mrTemplate->parseTemplate('data_lap_rekap_program_item', 'a');
			}
         for($k=0;$k<sizeof($resume);$k++) {
            if($k % 2 == 0) $resume[$k]['class_button'] = 'style="background-color:#DCDCDC"';
            else $resume[$k]['class_button'] = "";
				$this->mrTemplate->AddVars('resume_item', $resume[$k], 'RESUME_');
				$this->mrTemplate->parseTemplate('resume_item', 'a');
         }
		}
	}
}
?>
