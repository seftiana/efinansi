<?php


/**
 * @package lap_realisasi_penerimaan_pnbp_pusat
 * Class ViewLapPenerimaanPNBPPusat
 * @since 27 Maret 2012
 * @copyright 2012 Gamatechno
 */
 
 
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
	'module/lap_realisasi_penerimaan_pnbp_pusat/business/AppLapPenerimaanPNBPPusat.class.php';
    
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
	'module/user_unit_kerja/business/UserUnitKerja.class.php';

class ViewLapPenerimaanPNBPPusat extends HtmlResponse 
{

    protected $Pesan;

	public function TemplateModule() 
    {
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
			'module/lap_realisasi_penerimaan_pnbp_pusat/template');
		$this->SetTemplateFile('view_lap_penerimaan_pnbp.html');
	}
	
	public function ProcessRequest() 
    {
		$_POST = $_POST->AsArray();
		$Obj = new AppLapPenerimaanPNBPPusat();
		if($_POST['btncari']) {
			$this->Data['tahun_anggaran'] = $_POST['tahun_anggaran'];
		} elseif($_GET['cari'] != "") {
			$get = $_GET->AsArray();
			$this->Data['tahun_anggaran'] = Dispatcher::Instance()->Decrypt($_GET['tahun_anggaran']);
		} else {
			$tahun_anggaran = $Obj->GetTahunAnggaranAktif();
			$this->Data = $_POST;
			$this->Data['tahun_anggaran'] = $tahun_anggaran['id'];
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
        $totalData = $Obj->GetCountData($this->Data['tahun_anggaran']);
        $itemViewed = 20;
		$currPage = 1;
		$startRec = 0 ;
		if(isset($_GET['page'])) {
			$currPage = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
			$startRec =($currPage-1) * $itemViewed;
		}
        
        $data_pnbp = $Obj->GetDataRealisasiPNBP($this->Data['tahun_anggaran'],$startRec,$itemViewed);
        $total_data_pnbp_perbulan = $Obj->GetTotalDataRealisasiPNBPPerBulan($this->Data['tahun_anggaran']);
        
		$url = Dispatcher::Instance()->GetUrl(
                                Dispatcher::Instance()->mModule, 
                                Dispatcher::Instance()->mSubModule, 
                                Dispatcher::Instance()->mAction, 
                                Dispatcher::Instance()->mType . 
                                '&tahun_anggaran=' . 
                                Dispatcher::Instance()->Encrypt($this->Data['tahun_anggaran']) . 
                                '&cari=' . Dispatcher::Instance()->Encrypt(1));

		Messenger::Instance()->SendToComponent('paging', 'Paging', 'view', 'html', 'paging_top',
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
		$return['data'] = $data_pnbp;
        $return['total_data_pnbp_perbulan'] = $total_data_pnbp_perbulan;
		$return['penerimaan'] = $tot_jumlah;
		$return['jumlah'] = $tot_terima;
		$return['start'] = $startRec+1;
        $return['unit_kerja_nama'] = $Obj->GetUnitKerjaPusat();
        $return['startRec']= $startRec;
        $return['itemViewed'] = $itemViewed;
		return $return;
	}


	public function ParseTemplate($data = NULL) 
    {		
	
		$this->mrTemplate->AddVar('content', 'URL_SEARCH', 
                    Dispatcher::Instance()->GetUrl(
                                        'lap_realisasi_penerimaan_pnbp_pusat', 
                                        'lapPenerimaanPNBPPusat', 
                                        'view', 
                                        'html'));
		/**
		$this->mrTemplate->AddVar('content', 'URL_RTF', 
                    Dispatcher::Instance()->GetUrl(
                                        'lap_realisasi_penerimaan_pnbp_pusat', 
                                        'rtfLapPenerimaanPNBPPusat', 
                                        'view', 
                                        'html').
                                        "&tgl=".Dispatcher::Instance()->Encrypt($data['tgl']));		
		*/
		$this->mrTemplate->AddVar('content', 'URL_CETAK', 
                    Dispatcher::Instance()->GetUrl(
                                        'lap_realisasi_penerimaan_pnbp_pusat', 
                                        'cetakLapPenerimaanPNBPPusat', 
                                        'view', 
                                        'html').
                                        "&tgl=".Dispatcher::Instance()->Encrypt($data['tgl']));	
		
		$this->mrTemplate->AddVar('content', 'URL_EXCEL', 
                    Dispatcher::Instance()->GetUrl(
                                        'lap_realisasi_penerimaan_pnbp_pusat', 
                                        'excelLapPenerimaanPNBPPusat', 
                                        'view', 
                                        'xls') . 
                                        "&tgl=".Dispatcher::Instance()->Encrypt($data['tgl']));
                                        	
        $this->mrTemplate->AddVar('content', 'URL_RESET', 
                    Dispatcher::Instance()->GetUrl(
                                        'lap_realisasi_penerimaan_pnbp_pusat', 
                                        'lapPenerimaanPNBPPusat', 
                                        'view', 
                                        'html'));
		
		$this->mrTemplate->AddVar('content', 'UNITKERJA', $data['unit_kerja_nama']);
		
        /**
		if($this->Pesan) {
			$this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
			$this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->Pesan);
			$this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $this->css);
		}
		*/	
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
					$send['target_pnbp'] = number_format($data_list[$i]['target_pnbp'], 0, ',', '.');
					$send['realjan'] = number_format($data_list[$i]['realJan'], 0, ',', '.');
					$send['realfeb'] = number_format($data_list[$i]['realFeb'], 0, ',', '.');
					$send['realmar'] = number_format($data_list[$i]['realMar'], 0, ',', '.');
					$send['realapr'] = number_format($data_list[$i]['realApr'], 0, ',', '.');
					$send['realmei'] = number_format($data_list[$i]['realMei'], 0, ',', '.');
					$send['realjun'] = number_format($data_list[$i]['realJun'], 0, ',', '.');
					$send['realjul'] = number_format($data_list[$i]['realJul'], 0, ',', '.');
					$send['realags'] = number_format($data_list[$i]['realAgs'], 0, ',', '.');
					$send['realsep'] = number_format($data_list[$i]['realSep'], 0, ',', '.');
					$send['realokt'] = number_format($data_list[$i]['realOkt'], 0, ',', '.');
					$send['realnov'] = number_format($data_list[$i]['realNov'], 0, ',', '.');
					$send['realdes'] = number_format($data_list[$i]['realDes'], 0, ',', '.');
					$send['total_realisasi'] = number_format($data_list[$i]['total_realisasi'], 0, ',', '.');
					
					$send['class_name'] = "";
					$send['nomor'] = $no;
					$send['class_button'] = "links";


					$this->mrTemplate->AddVar('cekbox', 'data_number', $number);
					$this->mrTemplate->AddVar('cekbox', 'data_idrencana', $data_list[$i]['idrencana']);
					$this->mrTemplate->AddVar('cekbox', 'data_nama', $data_list[$i]['nama']);
					$this->mrTemplate->AddVar('cekbox', 'IS_SHOW', 'YES');
					$i++;$no++;$number++;
				 } elseif($data_list[$i]['kode_satker'] != $kode_satker && 
                            $data_list[$i]['nama_satker'] == $data_list[$i]['nama_unit']) {
					 $kode_satker = $data_list[$i]['kode_satker'];
					 $kode_unit = $data_list[$i]['kode_unit'];
					 $nama_satker = $data_list[$i]['nama_satker'];
					 $nama_unit = $data_list[$i]['nama_unit'];
					 $send['kode'] = "<b>".$kode_unit."</b>";
					 $send['nama'] = "<b>".$data_list[$i]['nama_unit']."</b>";
					 
					 $send['target_pnbp'] = "";
					 $send['realjan'] = "";
					 $send['realfeb'] = "";
					 $send['realmar'] = "";
					 $send['realapr'] = "";
					 $send['realmei'] = "";
					 $send['realjun'] = "";
					 $send['realjul'] = "";
					 $send['realags'] = "";
					 $send['realsep'] = "";
					 $send['realokt'] = "";
					 $send['realnov'] = "";
					 $send['realdes'] = "";
					 $send['total_realisasi'] = "";
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
					 $send['target_pnbp'] = "";
					 $send['realjan'] = "";
					 $send['realfeb'] = "";
					 $send['realmar'] = "";
					 $send['realapr'] = "";
					 $send['realmei'] = "";
					 $send['realjun'] = "";
					 $send['realjul'] = "";
					 $send['realags'] = "";
					 $send['realsep'] = "";
					 $send['realokt'] = "";
					 $send['realnov'] = "";
					 $send['realdes'] = "";
					 $send['total_realisasi'] = "";
					 $send['tarif'] = "";
					 $send['nomor'] = "";
					 $send['class_button'] = "toolbar";

					 $no=1;
				 }	
				 	$this->mrTemplate->AddVars('data_item', $send, 'DATA_');
					$this->mrTemplate->parseTemplate('data_item', 'a');
			}
			
			$total_target = $data['total_data_pnbp_perbulan']['t_target_pnbp'];
			$total_jan = $data['total_data_pnbp_perbulan']['t_realJan'];
			$total_feb = $data['total_data_pnbp_perbulan']['t_realFeb'];
			$total_mar = $data['total_data_pnbp_perbulan']['t_realMar'];
			$total_apr = $data['total_data_pnbp_perbulan']['t_realApr'];
			$total_mei = $data['total_data_pnbp_perbulan']['t_realMei'];
			$total_jun = $data['total_data_pnbp_perbulan']['t_realJun'];
			$total_jul = $data['total_data_pnbp_perbulan']['t_realJul'];
			$total_ags = $data['total_data_pnbp_perbulan']['t_realAgs'];
			$total_sep = $data['total_data_pnbp_perbulan']['t_realSep'];
			$total_okt = $data['total_data_pnbp_perbulan']['t_realOkt'];
			$total_nov = $data['total_data_pnbp_perbulan']['t_realNov'];
			$total_des = $data['total_data_pnbp_perbulan']['t_realDes'];
			$total_real = $data['total_data_pnbp_perbulan']['t_total_realisasi'];
            
         $this->mrTemplate->AddVar('data_total', 'TOTAL_TARGET_PNBP', number_format($total_target, 0, ',', '.'));
         $this->mrTemplate->AddVar('data_total', 'TOTAL_JAN', number_format($total_jan, 0, ',', '.'));
         $this->mrTemplate->AddVar('data_total', 'TOTAL_FEB', number_format($total_feb, 0, ',', '.'));
         $this->mrTemplate->AddVar('data_total', 'TOTAL_MAR', number_format($total_mar, 0, ',', '.'));
         $this->mrTemplate->AddVar('data_total', 'TOTAL_APR', number_format($total_apr, 0, ',', '.'));
         $this->mrTemplate->AddVar('data_total', 'TOTAL_MEI', number_format($total_mei, 0, ',', '.'));
         $this->mrTemplate->AddVar('data_total', 'TOTAL_JUN', number_format($total_jun, 0, ',', '.'));
         $this->mrTemplate->AddVar('data_total', 'TOTAL_JUL', number_format($total_jul, 0, ',', '.'));
         $this->mrTemplate->AddVar('data_total', 'TOTAL_AGS', number_format($total_ags, 0, ',', '.'));
         $this->mrTemplate->AddVar('data_total', 'TOTAL_SEP', number_format($total_sep, 0, ',', '.'));
         $this->mrTemplate->AddVar('data_total', 'TOTAL_OKT', number_format($total_okt, 0, ',', '.'));
         $this->mrTemplate->AddVar('data_total', 'TOTAL_NOV', number_format($total_nov, 0, ',', '.'));
         $this->mrTemplate->AddVar('data_total', 'TOTAL_DES', number_format($total_des, 0, ',', '.'));
         $this->mrTemplate->AddVar('data_total', 'TOTAL_TOTAL_REALISASI', number_format($total_real, 0, ',', '.'));
         
		}

	}
}