<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/usulan_kegiatan/business/AppCetakUsulanKegiatan.class.php';

class ViewRtfUsulanKegiatan extends HtmlResponse {	
	
	function GetTanggalIndonesia($tanggal) {
      $blnarr=array();
	   $blnarr[1]="Januari";
	   $blnarr[2]="Februari";
	   $blnarr[3]="Maret";
	   $blnarr[4]="April";
	   $blnarr[5]="Mei";
	   $blnarr[6]="Juni";
	   $blnarr[7]="Juli";
	   $blnarr[9]="September";
	   $blnarr[8]="Agustus";
	   $blnarr[10]="Oktober";
	   $blnarr[11]="November";
	   $blnarr[12]="Desember";
	
	   $tanggal=explode("-",$tanggal);	   
	   return $tanggal[2]." ".$blnarr[intval($tanggal[1])]." ".$tanggal[0];   
   }
	
	function ProcessRequest() {	
		
		$Obj = new AppCetakUsulanKegiatan();
		$decDataId = Dispatcher::Instance()->Decrypt($_GET['dataId']);
		$data_program = $Obj->GetDataProgram($decDataId);
		if($data_program['program_kode'] < 10) 
			$data_program['program_kode'] = "0" . $data_program['program_kode'];
		else
			$data_program['program_kode'] = $data_program['program_kode'];
		$data_kegiatan = $Obj->GetDataKegiatan($decDataId);
		$data=array();$j=0;$k=0;
		for($i=0;$i<sizeof($data_kegiatan);$i++) {
			if($data_kegiatan[$i]['jenis_kegiatan'] == "Rutin") {
				$data[$j]['rutin_number'] = $j+1;
				if($data_kegiatan[$i]['kegiatan_kode'] < 10) 
					$data[$j]['rutin_kode'] = $data_program['program_kode'] . ".0" . $data_kegiatan[$i]['kegiatan_kode'];
				else
					$data[$j]['rutin_kode'] = $data_program['program_kode'] . "." . $data_kegiatan[$i]['kegiatan_kode'];
				if($data_kegiatan[$i]['subkegiatan_kode'] < 10) 
					$data[$j]['rutin_kode'] = $data[$j]['rutin_kode'] . ".0" . $data_kegiatan[$i]['subkegiatan_kode'];
				else
					$data[$j]['rutin_kode'] = $data[$j]['rutin_kode'] . "." . $data_kegiatan[$i]['subkegiatan_kode'];

				$data[$j]['rutin_kegiatan'] = $data_kegiatan[$i]['kegiatan'];
				$data[$j]['rutin_subkegiatan'] = $data_kegiatan[$i]['subkegiatan'];
				$j++;
			} else {
				$data[$k]['nonrutin_number'] = $k+1;
				if($data_kegiatan[$i]['kegiatan_kode'] < 10) 
					$data[$k]['nonrutin_kode'] = $data_program['program_kode'] . ".0" . $data_kegiatan[$i]['kegiatan_kode'];
				else
					$data[$k]['nonrutin_kode'] = $data_program['program_kode'] . "." . $data_kegiatan[$i]['kegiatan_kode'];
				if($data_kegiatan[$i]['subkegiatan_kode'] < 10) 
					$data[$k]['nonrutin_kode'] = $data[$k]['nonrutin_kode'] . ".0" . $data_kegiatan[$i]['subkegiatan_kode'];
				else
					$data[$k]['nonrutin_kode'] = $data[$k]['nonrutin_kode'] . "." . $data_kegiatan[$i]['subkegiatan_kode'];
				$data[$k]['nonrutin_kegiatan'] = $data_kegiatan[$i]['kegiatan'];
				$data[$k]['nonrutin_subkegiatan'] = $data_kegiatan[$i]['subkegiatan'];
				$data[$k]['nonrutin_output'] = $data_kegiatan[$i]['output'];
				$data[$k]['nonrutin_waktu'] = $this->GetTanggalIndonesia($data_kegiatan[$i]['waktu_mulai']) . " - " . $this->GetTanggalIndonesia($data_kegiatan[$i]['waktu_selesai']);
				$k++;
			}
		}

		$data_program['program_kode_lengkap'] = $data_program['program_kode'] . ".00.00";
		$dataProgramSiap = $data_program;
		$dataKegiatanSiap = $data;
	
		
		$contents = file_get_contents(GTFWConfiguration::GetValue( 'application', 'docroot')."doc/template_UsulanKegiatan.rtf");
		
		$contents = str_replace('[NAMA_COMPANY]',GTFWConfiguration::GetValue('organization', 'company_name'), $contents);
		
				for($i=0;$i<count($dataKegiatanSiap);$i++){
						$nor .= ' '.$dataKegiatanSiap[$i]['rutin_number'].'\par';
						$kodeMAR .= ' '.$dataKegiatanSiap[$i]['rutin_kode'].'\par';
						$namaKegR .= '\f1\fs16'.$dataKegiatanSiap[$i]['rutin_kegiatan'].'\par';
						$namaSubKegR .= '\f1\fs16'.$dataKegiatanSiap[$i]['rutin_subkegiatan'].'\par';
						$noP .= ' '.$dataKegiatanSiap[$i]['nonrutin_number'].'\par';
						$kodeMAP .= ' '.$dataKegiatanSiap[$i]['nonrutin_kode'].'\par';
						$namaKegP .= '\f1\fs16'.$dataKegiatanSiap[$i]['nonrutin_kegiatan'].'\par';
						$namaSubKegP .= '\f1\fs16'.$dataKegiatanSiap[$i]['nonrutin_subkegiatan'].'\par';
						$output .= ' '.$dataKegiatanSiap[$i]['nonrutin_output'].'\par';
						$waktu .= ' '.$dataKegiatanSiap[$i]['nonrutin_waktu'].'\par';
				}
		//-------------------------------------------------------------------------------
		$contents = str_replace('[NO_R]', $nor, $contents);
		$contents = str_replace('[KODE_MA_R]', $kodeMAR, $contents);
		$contents = str_replace('[NAMA_KEG_R]', $namaKegR, $contents);
		$contents = str_replace('[NAMA_SUB_KEG_R]', $namaSubKegR, $contents);
		$contents = str_replace('[NO_P]', $noP, $contents);
		$contents = str_replace('[KODE_MA_P]', $kodeMAP, $contents);
		$contents = str_replace('[NAMA_KEG_P]', $namaKegP, $contents);
		$contents = str_replace('[NAMA_SUB_KEG_P]', $namaSubKegP, $contents);
		$contents = str_replace('[OUTPUT]', $output, $contents);
		$contents = str_replace('[WAKTU]', $waktu, $contents);
		
		
		$contents = str_replace('[PROGRAM_KODE]', (int) $dataProgramSiap['program_kode'], $contents);
		$contents = str_replace('[PROGRAM_KODE_LENGKAP]', $dataProgramSiap['program_kode_lengkap'], $contents);
		$contents = str_replace('[TAHUN_ANGGARAN_LABEL]', $dataProgramSiap['tahun_anggaran_label'], $contents);
		$contents = str_replace('[SATKER_LABEL]', $dataProgramSiap['satker_label'], $contents);
		$contents = str_replace('[SATKER_KODE]', $dataProgramSiap['satker_kode'], $contents);
		$contents = str_replace('[UNITKERJA_LABEL]', $dataProgramSiap['unitkerja_label'], $contents);
		$contents = str_replace('[UNITKERJA_KODE]', $dataProgramSiap['unitkerja_kode'], $contents);
		$contents = str_replace('[PROGRAM_LABEL]', $dataProgramSiap['program_label'], $contents);
		$contents = str_replace('[LATARBELAKANG]', $dataProgramSiap['latarbelakang'], $contents);
		$contents = str_replace('[INDIKATOR]', $dataProgramSiap['indikator'], $contents);
		$contents = str_replace('[BASELINE]', $dataProgramSiap['baseline'], $contents);
		$contents = str_replace('[FINAL]', $dataProgramSiap['final'], $contents);
		$contents = str_replace('[SATKER_PIMPINAN_LABEL]', $dataProgramSiap['satker_pimpinan_label'], $contents);
		$contents = str_replace('[UNITKERJA_PIMPINAN_LABEL]', $dataProgramSiap['unitkerja_pimpinan_label'], $contents);
		
		header("Content-type: application/msword");
		header("Content-disposition: inline; filename=UsulanKegiatan_".date('d-m-Y').".rtf");
		header("Content-length: " . strlen($contents));
		print $contents;
    }
	 
}
?>
