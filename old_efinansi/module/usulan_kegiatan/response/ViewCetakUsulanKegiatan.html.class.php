<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/usulan_kegiatan/business/AppCetakUsulanKegiatan.class.php';

class ViewCetakUsulanKegiatan extends HtmlResponse {

	var $Pesan;

	function TemplateModule() {
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').'module/usulan_kegiatan/template');
		$this->SetTemplateFile('view_cetak_usulankegiatan.html');
	}
   
    function TemplateBase() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
      $this->SetTemplateFile('document-print.html');
      $this->SetTemplateFile('layout-common-print.html');
   }
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
		//print_r($data_kegiatan);
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
		//print_r($data);

		//$data_non_rutin = $Obj->GetDataKegiatanRutin($decDataId);
		$return['data_program'] = $data_program;
		$return['data_kegiatan'] = $data;
		//print_r($data);
		//$return['data_non_rutin'] = $data_non_rutin;
		//$return['start'] = $startRec+1;
		return $return;
	}
	
	function ParseTemplate($data = NULL) {
		$this->mrTemplate->AddVar('content', 'PROGRAM_KODE', (int) $data['data_program']['program_kode']);
		$this->mrTemplate->AddVar('content', 'PROGRAM_KODE_LENGKAP', $data['data_program']['program_kode_lengkap']);
		$this->mrTemplate->AddVar('content', 'TAHUN_ANGGARAN_LABEL', $data['data_program']['tahun_anggaran_label']);
		//$this->mrTemplate->AddVar('content', 'SATKER_LABEL', $data['data_program']['satker_label']);
		//$this->mrTemplate->AddVar('content', 'SATKER_KODE', $data['data_program']['satker_kode']);
		$this->mrTemplate->AddVar('content', 'UNITKERJA_LABEL', $data['data_program']['unitkerja_label']);
		$this->mrTemplate->AddVar('content', 'UNITKERJA_KODE', $data['data_program']['unitkerja_kode']);
		$this->mrTemplate->AddVar('content', 'PROGRAM_LABEL', $data['data_program']['program_label']);
		$this->mrTemplate->AddVar('content', 'LATARBELAKANG', $data['data_program']['latarbelakang']);
		$this->mrTemplate->AddVar('content', 'INDIKATOR', $data['data_program']['indikator']);
		$this->mrTemplate->AddVar('content', 'BASELINE', $data['data_program']['baseline']);
		$this->mrTemplate->AddVar('content', 'FINAL', $data['data_program']['final']);
		//$this->mrTemplate->AddVar('content', 'SATKER_PIMPINAN_LABEL', $data['data_program']['satker_pimpinan_label']);
		$this->mrTemplate->AddVar('content', 'UNITKERJA_PIMPINAN_LABEL', $data['data_program']['unitkerja_pimpinan_label']);

		if (empty($data['data_kegiatan'])) {
			$this->mrTemplate->AddVar('data_kegiatan', 'DATA_KEGIATAN_EMPTY', 'YES');
		} else {
			//$decPage = Dispatcher::Instance()->Decrypt($_REQUEST['page']);
			//$encPage = Dispatcher::Instance()->Encrypt($decPage);
			$this->mrTemplate->AddVar('data_kegiatan', 'DATA_KEGIATAN_EMPTY', 'NO');
			$data_kegiatan = $data['data_kegiatan'];
			for ($i=0; $i<sizeof($data_kegiatan); $i++) {
				//$data_kegiatan[$i]['number'] = ($i+1);
				$this->mrTemplate->AddVars('data_kegiatan_item', $data_kegiatan[$i], 'KEGIATAN_');
				$this->mrTemplate->parseTemplate('data_kegiatan_item', 'a');	 
			}
		}
	}
}
?>
