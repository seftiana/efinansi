<?php

/**
 * @package lap_rencana_penerimaan
 * @since 24 Februari 2012
 * @copyright (c) 2012 Gamatechno
 */

require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
	'module/lap_rencana_penerimaan/business/AppLapRencanaPenerimaan.class.php';
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
	'module/user_unit_kerja/business/UserUnitKerja.class.php';

class ViewLapRencanaPenerimaan extends HtmlResponse
{

	protected $Pesan;
	protected $Data;

	public function TemplateModule()
	{
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
					'module/lap_rencana_penerimaan/template');
		$this->SetTemplateFile('view_lap_rencana_penerimaan.html');
	}

	public function ProcessRequest()
	{
		$_POST = $_POST->AsArray();
		$Obj = new AppLapRencanaPenerimaan();
		$userUnitKerjaObj = new UserUnitKerja();

		$userId= trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
		$role = $userUnitKerjaObj->GetRoleUser($userId);
		$unit = $userUnitKerjaObj->GetUnitKerjaUser($userId);
	    $statusApprove = array(
	        array('id' => '1','name' => 'Belum Disetujui'),
	        array('id' => '2','name' => 'Disetujui'),
	        array('id' => '4','name' => 'Ditolak')
	     );
			if($_POST['btncari']) {
				$this->Data['tahun_anggaran'] 	= $_POST['tahun_anggaran'];
				$this->Data['status_approve'] 	= $_POST['status_approve'];
				$this->Data['unitkerja'] 		= $_POST['unitkerja'];
				$this->Data['unitkerja_label'] 	= $_POST['unitkerja_label'];

				$unitkerja 						= $userUnitKerjaObj->GetUnitKerja($this->Data['unitkerja']);
				$this->Data['is_satker'] 		= $unitkerja['is_unit_kerja'];
			} elseif($_GET['cari'] != "") {
				$get = $_GET->AsArray();
				$this->Data['tahun_anggaran']	= Dispatcher::Instance()->Decrypt($_GET['tahun_anggaran']);
				$this->Data['status_approve'] 	= Dispatcher::Instance()->Decrypt($_GET['status_approve']);
				$this->Data['unitkerja'] 		= Dispatcher::Instance()->Decrypt($_GET['unitkerja']);
				$this->Data['unitkerja_label'] 	= Dispatcher::Instance()->Decrypt($_GET['unitkerja_label']);

				$unitkerja = $userUnitKerjaObj->GetUnitKerja($this->Data['unitkerja']);
				$this->Data['is_satker'] 		= $unitkerja['is_unit_kerja'];
			} else {
				$tahun_anggaran 				= $Obj->GetTahunAnggaranAktif();
				//$this->Data = $_POST;
				$this->Data['tahun_anggaran'] 	= $tahun_anggaran['id'];
				$this->Data['status_approve'] 	= '';

				//$this->Data['tahun_anggaran_label'] = $tahun_anggaran['name'];
				$this->Data['unitkerja'] 		= $unit['unit_kerja_id'];
				$this->Data['unitkerja_label'] 	= $unit['unit_kerja_nama'];
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
															$this->Data['tahun_anggaran'], '-',
															' style="width:200px;" id="tahun_anggaran"'),
													Messenger::CurrentRequest);


			//view
		$totalData = $Obj->GetCountData($this->Data['tahun_anggaran'],$this->Data['unitkerja'],$this->Data['status_approve']);
      	$itemViewed = 20;
		$currPage = 1;
		$startRec = 0 ;
		if(isset($_GET['page'])) {
			$currPage = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
			$startRec =($currPage-1) * $itemViewed;
		}

		$data_ren_panerimaan = $Obj->GetDataRencanaPenerimaan(
													$this->Data['tahun_anggaran'],
													$this->Data['unitkerja'],
													$this->Data['status_approve'],
													$startRec,
													$itemViewed
													);
        $total_data_ren_penerimaan  = $Obj->GetTotalRencanaPenerimaanPerBulan(
                                                                   $this->Data['tahun_anggaran'],
													               $this->Data['unitkerja'],
																   $this->Data['status_approve']
                                                                    );
		$url = Dispatcher::Instance()->GetUrl(
					Dispatcher::Instance()->mModule,
					Dispatcher::Instance()->mSubModule,
					Dispatcher::Instance()->mAction,
					Dispatcher::Instance()->mType .
					'&tahun_anggaran=' . Dispatcher::Instance()->Encrypt($this->Data['tahun_anggaran']) .
					'&unitkerja=' . Dispatcher::Instance()->Encrypt($this->Data['unitkerja']) .
					'&unitkerja_label=' . Dispatcher::Instance()->Encrypt($this->Data['unitkerja_label']) .
					'&status_approve=' . Dispatcher::Instance()->Encrypt($this->Data['status_approve']) .
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

      # Combobox
      Messenger::Instance()->SendToComponent(
							        'combobox',
							        'Combobox',
							        'view',
							        'html',
							        'status_approve',
							        array(
							            'status_approve',
							            $statusApprove,
							            $this->Data['status_approve'],
							            false,
							            'style="width: 135px;" id="status_approve"'),
							        Messenger::CurrentRequest);

		$msg = Messenger::Instance()->Receive(__FILE__);
		$this->Pesan = $msg[0][1];
		$this->css = $msg[0][2];
		$return['tgl'] = $this->Data['tahun_anggaran'];
		$return['role_name'] = $role['role_name'];
		$return['data'] = $data_ren_panerimaan;
        $return['total_per_bulan'] = $total_data_ren_penerimaan;
		$return['penerimaan'] = $tot_jumlah;
		$return['jumlah'] = $tot_terima;
		$return['start'] = $startRec+1;
		$return['total_sub_unit_kerja'] =$userUnitKerjaObj->GetTotalSubUnitKerja($unit['unit_kerja_id']);
		$return['user_id'] = $userId;
		return $return;
	}


	public function ParseTemplate($data = NULL)
	{
		$userId = $data['user_id'];
		$this->mrTemplate->AddVar('content', 'URL_SEARCH',
							Dispatcher::Instance()->GetUrl(
														'lap_rencana_penerimaan',
														'LapRencanaPenerimaan',
														'view',
														'html'));
       $this->mrTemplate->AddVar('content', 'URL_RESET',
							Dispatcher::Instance()->GetUrl(
														'lap_rencana_penerimaan',
														'LapRencanaPenerimaan',
														'view',
														'html'));

		$this->mrTemplate->AddVar('content', 'URL_RTF',
							Dispatcher::Instance()->GetUrl(
														'lap_rencana_penerimaan',
														'rtfLapRencanaPenerimaan',
														'view',
														'html').
														"&tgl=".
														Dispatcher::Instance()->Encrypt($data['tgl']).
														"&id=".
														Dispatcher::Instance()->Encrypt($userId).
														'&unitkerja=' .
														Dispatcher::Instance()->Encrypt(
																	$this->Data['unitkerja_label']).
														'&status_approve='.Dispatcher::Instance()->Encrypt($this->Data['status_approve']));

		$this->mrTemplate->AddVar('content', 'URL_CETAK',
							Dispatcher::Instance()->GetUrl(
														'lap_rencana_penerimaan',
														'CetakLapRencanaPenerimaan',
														'view',
														'html').
														"&tgl=".Dispatcher::Instance()->Encrypt($data['tgl']).
														"&id=".Dispatcher::Instance()->Encrypt($userId).
														"&unitkerja=".
														Dispatcher::Instance()->Encrypt($this->Data['unitkerja']).
														'&unitkerja_label=' .
														Dispatcher::Instance()->Encrypt(
																$this->Data['unitkerja_label']).
														'&status_approve='.Dispatcher::Instance()->Encrypt($this->Data['status_approve']));

		$this->mrTemplate->AddVar('content', 'URL_EXCEL',
							Dispatcher::Instance()->GetUrl(
														'lap_rencana_penerimaan',
														'ExcelLapRencanaPenerimaan',
														'view',
														'xlsx').
														"&tgl=".
														Dispatcher::Instance()->Encrypt($data['tgl']).
														"&id=".Dispatcher::Instance()->Encrypt($userId).
														"&unitkerja=".
														Dispatcher::Instance()->Encrypt($this->Data['unitkerja']).
														'&unitkerja_label=' .
														Dispatcher::Instance()->Encrypt(
																	$this->Data['unitkerja_label']).
														'&status_approve='.Dispatcher::Instance()->Encrypt($this->Data['status_approve']));


		$this->mrTemplate->AddVar('content', 'UNITKERJA', $this->Data['unitkerja']);
			if($data['total_sub_unit_kerja'] > 0){
				$this->mrTemplate->AddVar('cek_unitkerja_parent', 'IS_PARENT','YES');
			} else {
				$this->mrTemplate->AddVar('cek_unitkerja_parent', 'IS_PARENT', 'NO');
			}

		$this->mrTemplate->AddVar('cek_unitkerja_parent', 'URL_POPUP_UNITKERJA',
								Dispatcher::Instance()->GetUrl(
														'lap_rencana_penerimaan',
														'PopupUnitKerja',
														'view',
														'html'));
		$this->mrTemplate->AddVar('cek_unitkerja_parent', 'UNITKERJA_LABEL', $this->Data['unitkerja_label']);


		if($this->Pesan) {
			$this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
			$this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->Pesan);
			$this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $this->css);
		}

		if (empty($data['data'])) {
			 $this->mrTemplate->AddVar('data_grid', 'IS_DATA_EMPTY', 'YES');
		} else {
			 $this->mrTemplate->AddVar('data_grid', 'IS_DATA_EMPTY', 'NO');

			 $data_list = $data['data'];
			 for ($i=0; $i < sizeof($data_list);) {
			 		$send = $data_list[$i];
			 		$send['class_name'] = '';
 					$send['nomor'] = $data['start'] + $i;
					if($i == 0){
						$send['nama_unit']=$data_list[$i]['unit_kerja_nama'];
					} else{
						$send['nama_unit'] =
							(($data_list[$i - 1]['unit_kerja_id'] != $data_list[$i]['unit_kerja_id']) ?
										$data_list[$i]['unit_kerja_nama']: '');

					}
					$send['nama']=$data_list[$i]['kode_penerimaan_nama'];
					$send['kode']=$data_list[$i]['kode_penerimaan'];
					$send['nama']=$data_list[$i]['kode_penerimaan_nama'];
					$send['keterangan']=$data_list[$i]['keterangan'];
					$send['januari']=number_format($data_list[$i]['januari'],0,',','.');
					$send['februari']=number_format($data_list[$i]['februari'],0,',','.');
					$send['maret']=number_format($data_list[$i]['maret'],0,',','.');
					$send['april']=number_format($data_list[$i]['april'],0,',','.');
					$send['mei']=number_format($data_list[$i]['mei'],0,',','.');
					$send['juni']=number_format($data_list[$i]['juni'],0,',','.');
					$send['juli']=number_format($data_list[$i]['juli'],0,',','.');
					$send['agustus']=number_format($data_list[$i]['agustus'],0,',','.');
					$send['september']=number_format($data_list[$i]['september'],0,',','.');
					$send['oktober']=number_format($data_list[$i]['oktober'],0,',','.');
					$send['november']=number_format($data_list[$i]['november'],0,',','.');
					$send['desember']=number_format($data_list[$i]['desember'],0,',','.');
					$send['total_terima']=number_format($data_list[$i]['total_terima'],0,',','.');
					$i++;
					$this->mrTemplate->AddVars('data_item',$send, 'DATA_');
					$this->mrTemplate->parseTemplate('data_item', 'a');

			 }

			 /**
			  * total
			  */

			$total_terima = $data['total_per_bulan']['t_total_terima'];
			$total_jan = $data['total_per_bulan']['t_januari'];
			$total_feb = $data['total_per_bulan']['t_februari'];
			$total_mar = $data['total_per_bulan']['t_maret'];
			$total_apr = $data['total_per_bulan']['t_april'];
			$total_mei = $data['total_per_bulan']['t_mei'];
			$total_jun = $data['total_per_bulan']['t_juni'];
			$total_jul = $data['total_per_bulan']['t_juli'];
			$total_ags = $data['total_per_bulan']['t_agustus'];
			$total_sep = $data['total_per_bulan']['t_september'];
			$total_okt = $data['total_per_bulan']['t_oktober'];
			$total_nov = $data['total_per_bulan']['t_november'];
			$total_des = $data['total_per_bulan']['t_desember'];

         $this->mrTemplate->AddVar('data_total', 'T_TOTAL_TERIMA', number_format($total_terima, 0, ',', '.'));
         $this->mrTemplate->AddVar('data_total', 'T_JANUARI', number_format($total_jan, 0, ',', '.'));
         $this->mrTemplate->AddVar('data_total', 'T_FEBRUARI', number_format($total_feb, 0, ',', '.'));
         $this->mrTemplate->AddVar('data_total', 'T_MARET', number_format($total_mar, 0, ',', '.'));
         $this->mrTemplate->AddVar('data_total', 'T_APRIL', number_format($total_apr, 0, ',', '.'));
         $this->mrTemplate->AddVar('data_total', 'T_MEI', number_format($total_mei, 0, ',', '.'));
         $this->mrTemplate->AddVar('data_total', 'T_JUNI', number_format($total_jun, 0, ',', '.'));
         $this->mrTemplate->AddVar('data_total', 'T_JULI', number_format($total_jul, 0, ',', '.'));
         $this->mrTemplate->AddVar('data_total', 'T_AGUSTUS', number_format($total_ags, 0, ',', '.'));
         $this->mrTemplate->AddVar('data_total', 'T_SEPTEMBER', number_format($total_sep, 0, ',', '.'));
         $this->mrTemplate->AddVar('data_total', 'T_OKTOBER', number_format($total_okt, 0, ',', '.'));
         $this->mrTemplate->AddVar('data_total', 'T_NOVEMBER', number_format($total_nov, 0, ',', '.'));
         $this->mrTemplate->AddVar('data_total', 'T_DESEMBER', number_format($total_des, 0, ',', '.'));

		    /**
		     * end total
		     */

		}
	}
}