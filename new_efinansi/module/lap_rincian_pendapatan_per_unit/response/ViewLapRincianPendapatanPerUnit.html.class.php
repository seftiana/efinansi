<?php

require_once GTFWConfiguration::GetValue('application', 'docroot') . 
	'module/lap_rincian_pendapatan_per_unit/business/AppLapRincianPendapatanPerUnit.class.php';
	
require_once GTFWConfiguration::GetValue('application', 'docroot') . 
	'module/user_unit_kerja/business/UserUnitKerja.class.php';

class ViewLapRincianPendapatanPerUnit extends HtmlResponse 
{
	
	protected $Pesan;
	
	private $mLRPPU;

	public function TemplateModule() 
	{
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
			'module/lap_rincian_pendapatan_per_unit/template');
			
		$this->SetTemplateFile('view_lap_rincian_pendapatan_per_unit.html');
	}
	
	public function ProcessRequest() 
	{
		$_POST = $_POST->AsArray();
		$this->mLRPPU = new AppLapRincianPendapatanPerUnit();
		$userUnitKerjaObj = new UserUnitKerja();

		$userId = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
		$role = $userUnitKerjaObj->GetRoleUser($userId);
		$unit = $userUnitKerjaObj->GetUnitKerjaUser($userId);
		
			if(isset($_POST['btncari'])) {
				$this->Data['tahun_anggaran'] = $_POST['tahun_anggaran'];
				$this->Data['unitkerja'] = $_POST['unitkerja'];
				$this->Data['unitkerja_label'] = $_POST['unitkerja_label'];
				$unitkerja = $userUnitKerjaObj->GetSatkerUnitKerja($this->Data['unitkerja']);
				$this->Data['is_satker'] = $unitkerja['is_unit_kerja'];
			} elseif($_GET['cari'] != "") {
				$get = $_GET->AsArray();
				$this->Data['tahun_anggaran'] = Dispatcher::Instance()->Decrypt($_GET['tahun_anggaran']);
				$this->Data['unitkerja'] = Dispatcher::Instance()->Decrypt($_GET['unitkerja']);
				$this->Data['unitkerja_label'] = Dispatcher::Instance()->Decrypt($_GET['unitkerja_label']);
				$unitkerja = $userUnitKerjaObj->GetSatkerUnitKerja($this->Data['unitkerja']);
				$this->Data['is_satker'] = $unitkerja['is_unit_kerja'];
			} else {
				$tahun_anggaran = $this->mLRPPU->GetTahunAnggaranAktif();
				$this->Data = $_POST;
				$this->Data['tahun_anggaran'] = $tahun_anggaran['id'];
				//$this->Data['tahun_anggaran_label'] = $tahun_anggaran['name'];
				$this->Data['unitkerja'] =$unit['unit_kerja_id'];// $unit['satker_id'];
				$this->Data['unitkerja_label'] =  $unit['unit_kerja_nama'];//$unit['satker_nama'];
			}
			
			$this->Data['total_sub_unit_kerja'] = 
					$userUnitKerjaObj->GetTotalSubUnitKerja($unit['unit_kerja_id']);
					
			$arr_tahun_anggaran = $this->mLRPPU->GetComboTahunAnggaran();
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
	 
		//view
		
		$totalData = $this->mLRPPU->GetCountData($this->Data['tahun_anggaran'], $this->Data['unitkerja']);
		$itemViewed =20;
		$currPage = 1;
		$startRec = 0 ;
		if(isset($_GET['page'])) {
			$currPage = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
			$startRec =($currPage-1) * $itemViewed;
		}
		
		$data_rppu = $this->mLRPPU->GetDataPage($this->Data['tahun_anggaran'], $this->Data['unitkerja'],$startRec,$itemViewed);
		
		$url = Dispatcher::Instance()->GetUrl(
											Dispatcher::Instance()->mModule, 
											Dispatcher::Instance()->mSubModule, 
											Dispatcher::Instance()->mAction, 
											Dispatcher::Instance()->mType . 
											'&tahun_anggaran=' . 
											Dispatcher::Instance()->Encrypt($this->Data['tahun_anggaran']) . 
											'&unitkerja=' . Dispatcher::Instance()->Encrypt($this->Data['unitkerja']) . 
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

		//$msg = Messenger::Instance()->Receive(__FILE__);
		//$this->Pesan = $msg[0][1];
		//$this->css = $msg[0][2];
		$return['tgl'] = $this->Data['tahun_anggaran'];
		$return['data'] = $data_rppu;
		//$return['penerimaan'] = $tot_jumlah;
		//$return['jumlah'] = $tot_terima;
		$return['start'] = $startRec+1;
		$return['ta'] = $this->mLRPPU->GetTahunAnggaran($this->Data['tahun_anggaran']);
		$return['total_sd'] = $this->mLRPPU->GetTotalRincianPendapatanPerSD($this->Data['tahun_anggaran'],$this->Data['unitkerja']);
		$return['total_all'] = $this->mLRPPU->GetTotalRincianPendapatanAll($this->Data['tahun_anggaran'],$this->Data['unitkerja']);

		$return['ta'] = $this->mLRPPU->GetTahunAnggaran($this->Data['tahun_anggaran']);
		$return['ta_kemarin'] = $this->mLRPPU->GetTahunAnggaranKemarin($this->Data['tahun_anggaran']);		
		return $return;
	}
	
	public function ParseTemplate($data = NULL) 
	{	
		$taSebelum = !empty($data['ta_kemarin']['name']) ? $data['ta_kemarin']['name'] :$data['ta']['name'];
	    $this->mrTemplate->AddVar('content', 'TAHUN_SEBELUM_TH_ANGGAR', $taSebelum);
	    $this->mrTemplate->AddVar('content', 'TAHUN_SEKARANG_TH_ANGGAR', ($data['ta']['name']));
		$userId = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
		$this->mrTemplate->AddVar('content', 'URL_SEARCH', 
				Dispatcher::Instance()->GetUrl('lap_rincian_pendapatan_per_unit', 
												'lapRincianPendapatanPerUnit', 
												'view', 
												'html'));
												
		$this->mrTemplate->AddVar('content', 'URL_RESET', 
				Dispatcher::Instance()->GetUrl('lap_rincian_pendapatan_per_unit', 
												'lapRincianPendapatanPerUnit', 
												'view', 
												'html'));												
		
		$this->mrTemplate->AddVar('content', 'URL_CETAK', 
				Dispatcher::Instance()->GetUrl('lap_rincian_pendapatan_per_unit', 
												'cetakLapRincianPendapatanPerUnit', 
												'view', 
												'html').
												'&tgl=' . Dispatcher::Instance()->Encrypt($data['tgl']) . 
												'&id='. Dispatcher::Instance()->Encrypt($userId) . 
												'&unitkerja=' . Dispatcher::Instance()->Encrypt($this->Data['unitkerja']) . 
												'&unitkerja_label=' . Dispatcher::Instance()->Encrypt($this->Data['unitkerja_label']));	
		
		$this->mrTemplate->AddVar('content', 'URL_EXCEL', 
				Dispatcher::Instance()->GetUrl('lap_rincian_pendapatan_per_unit', 
												'excelLapRincianPendapatanPerUnit', 
												'view', 
												'xls').
												'&tgl=' . Dispatcher::Instance()->Encrypt($data['tgl']). 
												'&id='. Dispatcher::Instance()->Encrypt($userId) . 
												'&unitkerja=' . Dispatcher::Instance()->Encrypt($this->Data['unitkerja']). 
												'&unitkerja_label=' . Dispatcher::Instance()->Encrypt($this->Data['unitkerja_label']));	
		
			$this->mrTemplate->AddVar('content', 'UNITKERJA', $this->Data['unitkerja']);
			if($this->Data['total_sub_unit_kerja'] > 0){
				$this->mrTemplate->AddVar('cek_unitkerja_parent', 'IS_PARENT','YES');
			} else {
				$this->mrTemplate->AddVar('cek_unitkerja_parent', 'IS_PARENT', 'NO');
			}
				$this->mrTemplate->AddVar('cek_unitkerja_parent', 'UNITKERJA_LABEL', 
													$this->Data['unitkerja_label']);
				$this->mrTemplate->AddVar('cek_unitkerja_parent', 'URL_POPUP_UNITKERJA', 
								 Dispatcher::Instance()->GetUrl('lap_rincian_pendapatan_per_unit', 
																'popupUnitkerja', 
																'view', 
																'html'));
			$total_all = $data['total_all'];
			if (empty($data['data'])) {
				$this->mrTemplate->AddVar('data_grid', 'IS_DATA_EMPTY', 'YES');
			} else {
				$this->mrTemplate->AddVar('data_grid', 'IS_DATA_EMPTY', 'NO');
				
				$this->mrTemplate->AddVar('data_grid', 'TOTAL_ALL_TARGET', $this->mLRPPU->SetFormatAngka($total_all['target_sebelum']));
				$this->mrTemplate->AddVar('data_grid', 'TOTAL_ALL_REALISASI', $this->mLRPPU->SetFormatAngka($total_all['realisasi_sebelum']));
				$this->mrTemplate->AddVar('data_grid', 'TOTAL_ALL_TARGET_SEKARANG', $this->mLRPPU->SetFormatAngka($total_all['target_sekarang']));
			 $dataLaporan = $data['data'];
			 $totalSd  = $data['total_sd'];
			// print_r($totalSd);
			 $sdId ='';
			 $unitKerjaId ='';
			 $total ='';
			 $send = array();
			 $no =0;
			 for($k = 0; $k < sizeof($dataLaporan);){
				$total += $dataLaporan[$k]['target_sekarang'];
				if(($dataLaporan[$k]['sd_id'] == $sdId) &&
					($dataLaporan[$k]['unit_kerja_id'] == $unitKerjaId)
					){
					$send[$k]['kode'] = $dataLaporan[$k]['mak_kode'];
					$send[$k]['nama'] = $dataLaporan[$k]['mak_nama'];
					$send[$k]['target_sekarang'] = $this->mLRPPU->SetFormatAngka($dataLaporan[$k]['target_sebelum']);
					$send[$k]['real_sekarang'] = $this->mLRPPU->SetFormatAngka($dataLaporan[$k]['realisasi_sebelum']);
					$send[$k]['persen'] = 0;
					$send[$k]['target_depan'] = $this->mLRPPU->SetFormatAngka($dataLaporan[$k]['target_sekarang']);
					$send[$k]['font_style'] = '';
					$this->mrTemplate->AddVars('list_data_item', $send[$k], '');
					$this->mrTemplate->parseTemplate('list_data_item', 'a');
					$k++;
				}elseif($dataLaporan[$k]['unit_kerja_id'] != $unitKerjaId){
					$unitKerjaId = $dataLaporan[$k]['unit_kerja_id'];
					$no++;
					$send[$k]['kode'] = $dataLaporan[$k]['unit_kerja_kode'];
					$send[$k]['nama'] = $dataLaporan[$k]['unit_kerja_nama'];
					$send[$k]['target_sekarang'] =NULL;
					$send[$k]['real_sekarang'] = NULL;
					$send[$k]['persen'] =NULL;
					$send[$k]['target_depan'] = NULL;
					$send[$k]['font_style'] = 'font-style: italic;';
					$this->mrTemplate->AddVars('list_data_item', $send[$k], '');
					$this->mrTemplate->parseTemplate('list_data_item', 'a');
				}elseif($dataLaporan[$k]['sd_id'] != $sdId){
					$sdId = $dataLaporan[$k]['sd_id'];
					$no++;
					$send[$k]['kode'] = $no;
					$send[$k]['nama'] = $dataLaporan[$k]['sd_nama'];
					$send[$k]['target_sekarang'] =$this->mLRPPU->SetFormatAngka($totalSd[$sdId]['target_sebelum']);
					$send[$k]['real_sekarang'] = $this->mLRPPU->SetFormatAngka($totalSd[$sdId]['realisasi_sebelum']);
					$send[$k]['persen'] = '';
					$send[$k]['target_depan'] =$this->mLRPPU->SetFormatAngka($totalSd[$sdId]['target_sekarang']);
					$send[$k]['font_style'] = 'font-style: italic;';
					$this->mrTemplate->AddVars('list_data_item', $send[$k], '');
					$this->mrTemplate->parseTemplate('list_data_item', 'a');
				}		
			 }
			 
		}




	}
}
