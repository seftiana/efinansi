<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
		'module/rincian_perhitungan_rencana_penerimaan/business/AppRencanaPenerimaan.class.php';
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
		'module/user_unit_kerja/business/UserUnitKerja.class.php';

class ViewRencanaPenerimaan extends HtmlResponse {

	var $Pesan;

	function TemplateModule() {
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
				'module/rincian_perhitungan_rencana_penerimaan/template');
		$this->SetTemplateFile('view_rencana_penerimaan.html');
	}

	function ProcessRequest() {
		$_POST = $_POST->AsArray();
		$Obj = new AppRencanaPenerimaan();
		$userUnitKerjaObj = new UserUnitKerja();

		$userId = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
		$role = $userUnitKerjaObj->GetRoleUser($userId);
		$unit = $userUnitKerjaObj->GetUnitKerjaUser($userId);
		//print_r($role);
		/** if($role['role_name'] == "Administrator") {*/
			if($_POST['btncari']) {
				$this->Data['tahun_anggaran'] = $_POST['tahun_anggaran'];
				$this->Data['unitkerja'] = $_POST['unitkerja'];
				$this->Data['unitkerja_label'] = $_POST['unitkerja_label'];
				$unitkerja = $userUnitKerjaObj->GetUnitKerja($this->Data['unitkerja']);
				$this->Data['is_satker'] = $unitkerja['is_unit_kerja'];
			} elseif($_GET['cari'] != "") {
				$get = $_GET->AsArray();
				$this->Data['tahun_anggaran'] = Dispatcher::Instance()->Decrypt($_GET['tahun_anggaran']);
				$this->Data['unitkerja'] = Dispatcher::Instance()->Decrypt($_GET['unitkerja']);
				$this->Data['unitkerja_label'] = Dispatcher::Instance()->Decrypt($_GET['unitkerja_label']);
				$unitkerja = $userUnitKerjaObj->GetUnitKerja($this->Data['unitkerja']);
				$this->Data['is_satker'] = $unitkerja['is_unit_kerja'];
			} else {
				$tahun_anggaran = $Obj->GetTahunAnggaranAktif();
				$this->Data = $_POST;
				$this->Data['tahun_anggaran'] = $tahun_anggaran['id'];
				//$this->Data['unitkerja'] = '';
				$this->Data['unitkerja'] =$unit['unit_kerja_id'];// $unit['satker_id'];
				$this->Data['unitkerja_label'] =  $unit['unit_kerja_nama'];//$unit['satker_nama'];
				//$this->Data['tahun_anggaran_label'] = $tahun_anggaran['name'];
			}

			$this->Data['total_sub_unit_kerja'] =
					$userUnitKerjaObj->GetTotalSubUnitKerja($unit['unit_kerja_id']);
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
													$this->Data['tahun_anggaran'], '-',
													' style="width:200px;" id="tahun_anggaran"'),
								 Messenger::CurrentRequest);
		/**
		} elseif($role['role_name'] == "OperatorUnit") {
			if(isset($_POST['unitkerja_label'])) {
				//echo "asdf";
				$this->Data['unitkerja'] = $_POST['unitkerja'];
				$this->Data['unitkerja_label'] = $_POST['unitkerja_label'];
			} elseif(isset($_GET['unitkerja_label'])) {
				//echo "asdf";
				$get = $_GET->AsArray();
				$this->Data['unitkerja'] = Dispatcher::Instance()->Decrypt($get['unitkerja']);
				$this->Data['unitkerja_label'] = Dispatcher::Instance()->Decrypt($get['unitkerja_label']);
			} else {
				$unitkerja = $userUnitKerjaObj->GetSatkerUnitKerjaUser($userId);
				$this->Data['unitkerja'] = Dispatcher::Instance()->Decrypt($unitkerja['satker_id']);
				$this->Data['is_satker'] = $unitkerja['is_unit_kerja'];
				//$this->Data['unitkerja_label'] = Dispatcher::Instance()->Decrypt($unitkerja['satker_nama']);
			}
			$tahun_anggaran = $Obj->GetTahunAnggaranAktif();
			$this->Data['tahun_anggaran'] = $tahun_anggaran['id'];
			$this->Data['tahun_anggaran_label'] = $tahun_anggaran['name'];
		} else {
			$tahun_anggaran = $Obj->GetTahunAnggaranAktif();
			$this->Data['tahun_anggaran'] = $tahun_anggaran['id'];
			$this->Data['tahun_anggaran_label'] = $tahun_anggaran['name'];
			$unit = $userUnitKerjaObj->GetSatkerUnitKerjaUser($userId);
			$this->Data['unitkerja'] = $unit['unit_kerja_id'];
			$this->Data['unitkerja_label'] = $unit['satker_nama'] . "/ " . $unit['unit_kerja_nama'];
			$this->Data['is_satker'] = $unit['is_unit_kerja'];
		}

		*/

		$totalData = $Obj->GetCountData($this->Data['tahun_anggaran'],$this->Data['unitkerja']);
		$data_jumlah = $Obj->GetDataForTotal($this->Data['tahun_anggaran'], $userId, $this->Data['unitkerja']);
		//print_r($data_jumlah);
		$jml = count($data_jumlah);
		$tot_jumlah = 0;
		$tot_terima = 0;
		for($i=0;$i<=$jml;$i++){
			$tot_jumlah += $data_jumlah[$i]['tot_jumlah'];
			$tot_terima += $data_jumlah[$i]['tot_terima'];
		}

		$itemViewed = 20;
		$currPage = 1;
		$startRec = 0 ;
		if(isset($_GET['page'])) {
			$currPage = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
			$startRec =($currPage-1) * $itemViewed;
		}
		//view
		$data_unit = $Obj->GetDataUnitkerja(
										$this->Data['tahun_anggaran'],
										$this->Data['unitkerja'],
										$startRec,
										$itemViewed
										);
		$url = Dispatcher::Instance()->GetUrl(
											Dispatcher::Instance()->mModule,
											Dispatcher::Instance()->mSubModule,
											Dispatcher::Instance()->mAction,
											Dispatcher::Instance()->mType .
											'&tahun_anggaran=' .
											Dispatcher::Instance()->Encrypt($this->Data['tahun_anggaran']) .
											'&unitkerja=' .
											Dispatcher::Instance()->Encrypt($this->Data['unitkerja']) .
											'&unitkerja_label=' .
											Dispatcher::Instance()->Encrypt($this->Data['unitkerja_label']) .
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

		$msg = Messenger::Instance()->Receive(__FILE__);
		$this->Pesan = $msg[0][1];
		$this->css = $msg[0][2];
		$return['tgl'] = $this->Data['tahun_anggaran'];
		$return['role_name'] = $role['role_name'];
		$return['data'] = $data_unit;
		$return['penerimaan'] = $tot_jumlah;
		$return['jumlah'] = $tot_terima;
		$return['start'] = $startRec+1;
		return $return;
	}

	function tambahNol($str="0", $jml_char=2) {
		while(strlen($str) < $jml_char) {
			$str = "0" . $str;
		}
		return $str;
	}

	function ParseTemplate($data = NULL) {
		$userId = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
		$this->mrTemplate->AddVar('content', 'URL_SEARCH', Dispatcher::Instance()->GetUrl('rincian_perhitungan_rencana_penerimaan', 'RencanaPenerimaan', 'view', 'html'));

        $this->mrTemplate->AddVar('content', 'URL_RESET', Dispatcher::Instance()->GetUrl('rincian_perhitungan_rencana_penerimaan', 'RencanaPenerimaan', 'view', 'html'));

		$this->mrTemplate->AddVar('content', 'URL_CETAK',Dispatcher::Instance()->GetUrl('rincian_perhitungan_rencana_penerimaan', 'CetakRencanaPenerimaan', 'view', 'html')."&tgl=".Dispatcher::Instance()->Encrypt($data['tgl'])."&id=".Dispatcher::Instance()->Encrypt($userId).'&unitkerjaid=' . Dispatcher::Instance()->Encrypt($this->Data['unitkerja']).'&unitkerja_label=' . Dispatcher::Instance()->Encrypt($this->Data['unitkerja_label']));

		$this->mrTemplate->AddVar('content', 'URL_RTF', Dispatcher::Instance()->GetUrl('rincian_perhitungan_rencana_penerimaan', 'RtfRencanaPenerimaan', 'view', 'html')."&tgl=".Dispatcher::Instance()->Encrypt($data['tgl'])."&id=".Dispatcher::Instance()->Encrypt($userId).'&unitkerjaid=' . Dispatcher::Instance()->Encrypt($this->Data['unitkerja']));

		$this->mrTemplate->AddVar('content', 'URL_EXCEL',
		Dispatcher::Instance()->GetUrl('rincian_perhitungan_rencana_penerimaan', 'ExcelRencanaPenerimaan', 'view', 'xlsx')."&tgl=".Dispatcher::Instance()->Encrypt($data['tgl'])."&id=".Dispatcher::Instance()->Encrypt($userId).'&unitkerjaid=' . Dispatcher::Instance()->Encrypt($this->Data['unitkerja']).'&unitkerja_label=' . Dispatcher::Instance()->Encrypt($this->Data['unitkerja_label']));

			$this->mrTemplate->AddVar('content', 'UNITKERJA', $this->Data['unitkerja']);
			if($this->Data['total_sub_unit_kerja'] > 0){
				$this->mrTemplate->AddVar('cek_unitkerja_parent', 'IS_PARENT','YES');
			} else {
				$this->mrTemplate->AddVar('cek_unitkerja_parent', 'IS_PARENT', 'NO');
			}
				$this->mrTemplate->AddVar('cek_unitkerja_parent', 'UNITKERJA_LABEL',
													$this->Data['unitkerja_label']);
				$this->mrTemplate->AddVar('cek_unitkerja_parent', 'URL_POPUP_UNITKERJA',
								Dispatcher::Instance()->GetUrl(
													'rincian_perhitungan_rencana_penerimaan',
													'popupUnitkerja',
													'view',
													'html'));

		/**
		if($data['role_name'] == "Administrator") {
			$this->mrTemplate->AddVar('role', 'WHOAMI', 'ADMINISTRATOR');
			$this->mrTemplate->AddVar('role', 'URL_POPUP_UNITKERJA', Dispatcher::Instance()->GetUrl('rincian_perhitungan_rencana_penerimaan', 'popupUnitkerja', 'view', 'html'));
			$this->mrTemplate->AddVar('role', 'UNITKERJA', $this->Data['unitkerja']);
			$this->mrTemplate->AddVar('role', 'UNITKERJA_LABEL', $this->Data['unitkerja_label']);
		} elseif($data['role_name'] == "OperatorUnit") {
			$this->mrTemplate->AddVar('role', 'WHOAMI', 'OPERATORUNIT');
			$this->mrTemplate->AddVar('role', 'URL_POPUP_UNITKERJA', Dispatcher::Instance()->GetUrl('rincian_perhitungan_rencana_penerimaan', 'popupUnitkerja', 'view', 'html'));
			$this->mrTemplate->AddVar('role', 'TAHUN_ANGGARAN_LABEL', $this->Data['tahun_anggaran_label']);
			$this->mrTemplate->AddVar('role', 'UNITKERJA', $this->Data['unitkerja']);
			$this->mrTemplate->AddVar('role', 'UNITKERJA_LABEL', $this->Data['unitkerja_label']);
		} else {
			$this->mrTemplate->AddVar('role', 'WHOAMI', 'OPERATORSUBUNIT');
			$this->mrTemplate->AddVar('role', 'TAHUN_ANGGARAN_LABEL', $this->Data['tahun_anggaran_label']);
			$this->mrTemplate->AddVar('role', 'UNITKERJA_LABEL', $this->Data['unitkerja_label']);
		}
		*/
		if($this->Pesan) {
			$this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
			$this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->Pesan);
			$this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $this->css);
		}

		if (empty($data['data'])) {
			 $this->mrTemplate->AddVar('data_grid', 'IS_DATA_EMPTY', 'YES');
		} else {
			 $this->mrTemplate->AddVar('data_grid', 'IS_DATA_EMPTY', 'NO');

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

					if($i == 0) $this->mrTemplate->AddVar('content', 'FIRST_NUMBER', $number);
					if($i == sizeof($data_list)-1) $this->mrTemplate->AddVar('content', 'LAST_NUMBER', $number);

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
					 $send['kode'] = "<b>".$kode_unit."</b>";
					 $send['nama'] = "<b>".$data_list[$i]['nama_unit']."</b>";

					 $send['total_penerimaan'] = "<b>".number_format($data_list[$i]['jumlah_total'], 0, ',', '.')."</b>";
					 $send['volume'] = "";
					 $send['pagu'] = "";
					 $send['tarif'] = "";
					 $send['totalterima'] = "<b>".number_format($data_list[$i]['totalterima'], 0, ',', '.')."</b>";
					 //print_r($send['jumlah_total']."<br/>");

					 $send['class_name'] = "table-common-even1";
					 $send['nomor'] = "";
					 $send['class_button'] = "toolbar";

					 $no=1;
					// }
				 } elseif($data_list[$i]['kode_unit'] != $kode_unit) {
					 $kode_satker = $data_list[$i]['kode_satker'];
					 $kode_unit = $data_list[$i]['kode_unit'];
					 $nama_satker = $data_list[$i]['nama_satker'];
					 $nama_unit = $data_list[$i]['nama_unit'];
					 $send['kode'] = "<b>".$kode_unit."</b>";
					 $send['nama'] = "<b>".$data_list[$i]['nama_unit']."</b>";
					 $send['total_penerimaan'] = "<b>".number_format($data_list[$i]['jumlah_total'], 0, ',', '.')."</b>";
					 $send['class_name'] = "";
					 $send['volume'] = "";
					 $send['pagu'] = "";
					 $send['totalterima'] = "<b>".number_format($data_list[$i]['totalterima'], 0, ',', '.')."</b>";
					 $send['tarif'] = "";
					 $send['nomor'] = "";
					 $send['class_button'] = "toolbar";

					 $no=1;
				 }
				 	$this->mrTemplate->AddVars('data_item', $send, 'DATA_');
					$this->mrTemplate->parseTemplate('data_item', 'a');
			}
			$jumlah = "<b>".number_format($data['jumlah'], 0, ',', '.')."</b>";
			$terima = "<b>".number_format($data['penerimaan'], 0, ',', '.')."</b>";
			$this->mrTemplate->AddVar('content', 'DATA_JUMLAH',$jumlah);
			$this->mrTemplate->AddVar('content', 'DATA_TERIMA',$terima);
		}

	}
}
?>
