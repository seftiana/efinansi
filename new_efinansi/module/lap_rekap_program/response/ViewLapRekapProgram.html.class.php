<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
	'module/lap_rekap_program/business/AppLapRekapProgram.class.php';
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
	'module/user_unit_kerja/business/UserUnitKerja.class.php';

class ViewLapRekapProgram extends HtmlResponse
{

	var $Pesan;

	function TemplateModule()
	{
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
			'module/lap_rekap_program/template');
		$this->SetTemplateFile('view_lap_rekap_program.html');
	}

	function ProcessRequest()
	{
		$_POST = $_POST->AsArray();
		$Obj = new AppLapRekapProgram();
		$userUnitKerjaObj = new UserUnitKerja();
		$userId = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
		$role = $userUnitKerjaObj->GetRoleUser($userId);
		$unit = $userUnitKerjaObj->GetUnitKerjaUser($userId);
		if($_POST['btncari']) {
			$tahun_anggaran = $_POST['tahun_anggaran'];
			$program = $_POST['program'];
			$program_label = $_POST['program_label'];
			$unitkerja = $_POST['unitkerja'];
			$unitkerja_label = $_POST['unitkerja_label'];
			$jenis_kegiatan = $_POST['jenis_kegiatan'];
		} elseif(isset($_GET['cari'])) {
			$tahun_anggaran = Dispatcher::Instance()->Decrypt($_GET['tahun_anggaran']);
			$program = Dispatcher::Instance()->Decrypt($_GET['program']);
			$program_label = Dispatcher::Instance()->Decrypt($_GET['program_label']);
			$unitkerja = Dispatcher::Instance()->Decrypt($_GET['unitkerja']);
			$unitkerja_label = Dispatcher::Instance()->Decrypt($_GET['unitkerja_label']);
			$jenis_kegiatan = Dispatcher::Instance()->Decrypt($_GET['jenis_kegiatan']);
		} else {
			$arr_tahun_anggaran = $Obj->GetTahunAnggaranAktif();
         	$tahun_anggaran = $arr_tahun_anggaran['id'];

			$program = "";
			$program_label = "";
			$jenis_kegiatan = "";

         	$unitkerja = $unit['unit_kerja_id'];
         	$unitkerja_label = $unit['unit_kerja_nama'];
		}

		$arr_tahun_anggaran = $Obj->GetComboTahunAnggaran();
		Messenger::Instance()->SendToComponent(
										'combobox',
										'Combobox',
										'view',
										'html',
										'tahun_anggaran',
									 	array(
										 		'tahun_anggaran',
												 $arr_tahun_anggaran,
												 $tahun_anggaran, '-',
												 ' style="width:200px;" id="tahun_anggaran"'),
										 Messenger::CurrentRequest);

		$arr_jenis_kegiatan = $Obj->GetComboJenisKegiatan();
		Messenger::Instance()->SendToComponent(
										'combobox',
										'Combobox',
										'view',
										'html',
										'jenis_kegiatan',
										array(
												'jenis_kegiatan',
												$arr_jenis_kegiatan,
												$jenis_kegiatan, true,
												' style="width:200px;" id="jenis_kegiatan"'),
										Messenger::CurrentRequest);

	//view
		$totalData = $Obj->GetCountData($tahun_anggaran, $program, $jenis_kegiatan, $unitkerja, $operator_role);
		$itemViewed = 20;
		$currPage = 1;
		$startRec = 0 ;
		if(isset($_GET['page'])) {
			$currPage = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
			$startRec =($currPage-1) * $itemViewed;
		}
		$data = $Obj->GetData(
								$startRec,
								$itemViewed,
								$tahun_anggaran,
								$program,
								$jenis_kegiatan,
								$unitkerja,
								$operator_role,$sql);
		$dataC = $Obj->GetCetakData($tahun_anggaran, $program, $jenis_kegiatan, $unitkerja);
		// echo sizeof($data);
		//print_r($data);
      $url = Dispatcher::Instance()->GetUrl(
	  										Dispatcher::Instance()->mModule,
				  							Dispatcher::Instance()->mSubModule,
				  							Dispatcher::Instance()->mAction,
				  							Dispatcher::Instance()->mType .
		  									'&tahun_anggaran=' .
										  	Dispatcher::Instance()->Encrypt($tahun_anggaran) .
										  	'&program=' . Dispatcher::Instance()->Encrypt($program) .
			  								'&program_label=' . Dispatcher::Instance()->Encrypt($program_label) .
										  	'&unitkerja=' . Dispatcher::Instance()->Encrypt($unitkerja) .
										  	'&unitkerja_label=' .
										  	Dispatcher::Instance()->Encrypt($unitkerja_label) .
										  	'&jenis_kegiatan=' .
										  	Dispatcher::Instance()->Encrypt($jenis_kegiatan) .
										  	'&cari=' . Dispatcher::Instance()->Encrypt(1));

		Messenger::Instance()->SendToComponent(
												'paging',
												'Paging',
												'view',
												'html',
												'paging_top',
												array(
														$itemViewed,
														$totalData,
														$url,
														$currPage),
												Messenger::CurrentRequest);

      $resume = $Obj->GetResume($tahun_anggaran, $program, $jenis_kegiatan, $unitkerja, $operator_role);
      $resume_kegiatan = $Obj->GetResumeKegiatan(
	  											$tahun_anggaran,
				  								$program,
											  	$jenis_kegiatan,
											  	$unitkerja,
											  	$operator_role);
      //echo $unitkerja;
      //print_r($resume);
		$msg = Messenger::Instance()->Receive(__FILE__);
		$this->Pesan = $msg[0][1];
		$this->css = $msg[0][2];

		$return['total_sub_unit_kerja'] = $userUnitKerjaObj->GetTotalSubUnitKerja($unit['unit_kerja_id']);
		$return['role_name'] = $role['role_name'];
		$return['data'] = $data;
		$return['resume'] = $resume;
		$return['resume_kegiatan'] = $resume_kegiatan;
		$return['start'] = $startRec+1;

		$return['search']['tahun_anggaran'] = $tahun_anggaran;
		$return['search']['program'] = $program;
		$return['search']['program_label'] = $program_label;
		$return['search']['unitkerja'] = $unitkerja;
		$return['search']['unitkerja_label'] = $unitkerja_label;
		$return['search']['jenis_kegiatan'] = $jenis_kegiatan;
		return $return;
	}

	function ParseTemplate($data = NULL)
	{
		//echo 'laporan rekap program';
		$search = $data['search'];
      //print_r($search);
		$this->mrTemplate->AddVar('content', 'PROGRAM', $search['program']);
		$this->mrTemplate->AddVar('content', 'PROGRAM_LABEL', $search['program_label']);
		//$this->mrTemplate->AddVar('content', 'UNITKERJA', $search['unitkerja']);
		//$this->mrTemplate->AddVar('content', 'UNITKERJA_LABEL', $search['unitkerja_label']);
		$this->mrTemplate->AddVar('content', 'URL_RESET',
									Dispatcher::Instance()->GetUrl(
															'lap_rekap_program',
															'lapRekapProgram',
															'view',
															'html'));

		$this->mrTemplate->AddVar('content', 'URL_SEARCH',
									Dispatcher::Instance()->GetUrl(
															'lap_rekap_program',
															'lapRekapProgram',
															'view',
															'html'));

		$this->mrTemplate->AddVar('content', 'URL_CETAK',
									Dispatcher::Instance()->GetUrl(
															'lap_rekap_program',
															'CetakLapRekapProgram',
															'view',
															'html') .
															'&tahun_anggaran=' .
															Dispatcher::Instance()->Encrypt(
																		$search['tahun_anggaran']) .
															'&program=' .
															Dispatcher::Instance()->Encrypt($search['program']) .
															'&unitkerja=' .
															Dispatcher::Instance()->Encrypt($search['unitkerja']).
															'&jenis_kegiatan=' .
															Dispatcher::Instance()->Encrypt(
																			$search['jenis_kegiatan']));

		$this->mrTemplate->AddVar('content', 'URL_EXCEL',
									Dispatcher::Instance()->GetUrl(
															'lap_rekap_program',
															'ExcelLapRekapProgram',
															'view',
															'xlsx') .
															'&tahun_anggaran=' .
															Dispatcher::Instance()->Encrypt(
																			$search['tahun_anggaran']) .
															'&program=' .
															Dispatcher::Instance()->Encrypt($search['program']) .
															'&unitkerja=' .
															Dispatcher::Instance()->Encrypt($search['unitkerja']).
															'&jenis_kegiatan=' .
															Dispatcher::Instance()->Encrypt(
																			$search['jenis_kegiatan']));

		$this->mrTemplate->AddVar('content', 'URL_RTF',
									Dispatcher::Instance()->GetUrl(
															'lap_rekap_program',
															'RtfLapRekapProgram',
															'view',
															'html') .
															'&tahun_anggaran=' .
															Dispatcher::Instance()->Encrypt(
																		$search['tahun_anggaran']) .
															'&program=' .
															Dispatcher::Instance()->Encrypt($search['program']) .
															'&unitkerja=' .
															Dispatcher::Instance()->Encrypt($search['unitkerja']).
															'&jenis_kegiatan=' .
															Dispatcher::Instance()->Encrypt(
																	$search['jenis_kegiatan']));

		$this->mrTemplate->AddVar('content', 'URL_POPUP_PROGRAM',
									Dispatcher::Instance()->GetUrl(
															'lap_rekap_program',
															'popupProgram',
															'view',
															'html'));
		//$this->mrTemplate->AddVar('content', 'URL_POPUP_UNITKERJA',
		//Dispatcher::Instance()->GetUrl('lap_rekap_program', 'popupUnitkerja', 'view', 'html'));
		$this->mrTemplate->AddVar('content', 'UNITKERJA', $search['unitkerja']);

			if($data['total_sub_unit_kerja'] > 0){
				$this->mrTemplate->AddVar('cek_unitkerja_parent', 'IS_PARENT','YES');
			} else {
				$this->mrTemplate->AddVar('cek_unitkerja_parent', 'IS_PARENT', 'NO');
			}
				$this->mrTemplate->AddVar('cek_unitkerja_parent', 'UNITKERJA_LABEL',
													$search['unitkerja_label']);
				$this->mrTemplate->AddVar('cek_unitkerja_parent', 'URL_POPUP_UNITKERJA',
								Dispatcher::Instance()->GetUrl(
													'lap_rekap_program',
													'popupUnitkerja',
													'view',
													'html'));

		if($this->Pesan) {
			$this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
			$this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->Pesan);
			$this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $this->css);
		}

		if (empty($data['data'])) {
			$this->mrTemplate->AddVar('data', 'DATA_EMPTY', 'YES');
			$this->mrTemplate->AddVar('resume', 'RESUME_EMPTY', 'YES');
		} else {
			$decPage = Dispatcher::Instance()->Decrypt($_REQUEST['page']);
			$encPage = Dispatcher::Instance()->Encrypt($decPage);
			$this->mrTemplate->AddVar('data', 'DATA_EMPTY', 'NO');
			$this->mrTemplate->AddVar('resume', 'RESUME_EMPTY', 'NO');
			$data_list = $data['data'];

			$x=0;
			$kodeProg = ''; $kodeKegiatan = ''; $kodeSubKegiatan='';
			//echo "<pre style='font-size:12px;'>";
			//print_r($data_list);
         //echo "</pre>";

			for ($i=0; $i<sizeof($data_list);) {
				//print_r($data_list[$i]);
				 if(($data_list[$i]['kodeProg'] == $kodeProg) && ($data_list[$i]['kodeKegiatan'] == $kodeKegiatan) ) {
					$send[$x]['kode'] = $data_list[$i]['kodeSubKegiatan'];
					$send[$x]['program_id'] = "";
					$send[$x]['kegiatan_id'] = "";
					$send[$x]['program_kegiatan'] = $data_list[$i]['namaSubKegiatan'];
					$send[$x]['unitkerja'] = $data_list[$i]['unitName'];
					$send[$x]['nominal_usulan'] = $data_list[$i]['nominalUsulan'];
					$send[$x]['nominal_setuju'] = $data_list[$i]['nominalSetuju'];
					$send[$x]['class_button'] = "";
					$send[$x]['jenis'] = "subkegiatan";
					$i++;//$x++;
				 } elseif($data_list[$i]['kodeProg'] != $kodeProg) {
					$kodeProg = $data_list[$i]['kodeProg'];

					$send[$x]['kode'] = $data_list[$i]['kodeProg'];
					$send[$x]['program_id'] = $data_list[$i]['program_id'];
					$send[$x]['kegiatan_id'] = "";
					$send[$x]['program_kegiatan'] = $data_list[$i]['namaProgram'];
					$send[$x]['unitkerja'] = "";
					$send[$x]['nominal_usulan'] = "";
					$send[$x]['nominal_setuju'] = "";
					$send[$x]['class_button'] = "table-common-even1";
					$send[$x]['jenis'] = "program";
				 } elseif($data_list[$i]['kodeKegiatan'] != $kodeKegiatan) {
					$kodeKegiatan = $data_list[$i]['kodeKegiatan'];
					$send[$x]['kode'] = $data_list[$i]['kodeKegiatan'];
					$send[$x]['program_id'] = "";
					$send[$x]['kegiatan_id'] = $data_list[$i]['kegiatan_id'];
					$send[$x]['program_kegiatan'] = $data_list[$i]['namaKegiatan'];
					$send[$x]['unitkerja'] = "";
					$send[$x]['nominal_usulan'] = "";
					$send[$x]['nominal_setuju'] = "";
					$send[$x]['class_button'] = "table-common-even";
					$send[$x]['jenis'] = "kegiatan";
				 }
				 $x++;
			}
			//print_r($send);
			$i = sizeof($send)-1;
			$nominal_usulan=0;
			$nominal_setuju=0;

         $resume = $data['resume'];
         $resume_kegiatan = $data['resume_kegiatan'];

			for($j=0;$j<sizeof($send);$j++) {
            if($send[$j]['program_id']) {
               for($n=0;$n<sizeof($resume);$n++) {
                  if($resume[$n]['kode'] == $send[$j]['kode']) {
                     $send[$j]['nominal_usulan'] = $resume[$n]['nominal_usulan'];
                     $send[$j]['nominal_setuju'] = $resume[$n]['nominal_setuju'];
                     break;
                  }
               }
            } elseif($send[$j]['kegiatan_id']) {
               for($m=0;$m<sizeof($resume_kegiatan);$m++) {
                  if($resume_kegiatan[$m]['kode'] == $send[$j]['kode']) {
                     $send[$j]['nominal_usulan'] = $resume_kegiatan[$m]['nominal_usulan'];
                     $send[$j]['nominal_setuju'] = $resume_kegiatan[$m]['nominal_setuju'];
                     break;
                  }
               }
            }
				if($send[$j]['nominal_usulan'] != ''):
				 $send[$j]['nominal_usulan'] = number_format($send[$j]['nominal_usulan'], 0, ',', '.');
				endif;
				if($send[$j]['nominal_setuju'] != ''):
				 $send[$j]['nominal_setuju'] = number_format($send[$j]['nominal_setuju'], 0, ',', '.');
				endif;
				 $this->mrTemplate->AddVars('data_lap_rekap_program_item', $send[$j], 'DATA_');
				 $this->mrTemplate->parseTemplate('data_lap_rekap_program_item', 'a');
			}

         for($k=0;$k<sizeof($resume);$k++) {
				$resume[$k]['nominal_usulan'] = number_format($resume[$k]['nominal_usulan'], 0, ',', '.');
				$resume[$k]['nominal_setuju'] = number_format($resume[$k]['nominal_setuju'], 0, ',', '.');
				$this->mrTemplate->AddVars('resume_item', $resume[$k], 'RESUME_');
				$this->mrTemplate->parseTemplate('resume_item', 'a');
         }
		}
	}
}
?>
