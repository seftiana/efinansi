<?php

/**
 * 
 * class ViewLapPendapatanBelanjaAgregat
 * @package lap_pendapatan_belanja_agregat
 * @todo Untuk menampilkan data
 * @subpackage response
 * @since 10 September 2012
 * @SystemAnalyst Nanang Ruswianto <nanang@gamatechno.com>
 * @author noor hadi <noor.hadi@gamatechno.com>
 * @copyright 2012 Gamatechno Indonesia
 * 
 */
 
require_once GTFWConfiguration::GetValue('application', 'docroot') . 
            'module/lap_pendapatan_belanja_agregat/business/LapPendapatanBelanjaAgregat.class.php';
            
require_once GTFWConfiguration::GetValue('application', 'docroot') . 
	'module/user_unit_kerja/business/UserUnitKerja.class.php';            


class ViewLapPendapatanBelanjaAgregat extends HtmlResponse
{
	
	protected $mLPA;
	protected $jmlNominal;
	protected $mModulName;
	
	public function __construct()
	{
		parent::__construct();
		$this->mLPA = new LapPendapatanBelanjaAgregat();
		$this->mModulName = 'lap_pendapatan_belanja_agregat';
	}
	
	public function TemplateModule()
	{
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') .     
                'module/'.$this->mModulName.'/template');
		$this->SetTemplateFile('view_lap_pendapatan_belanja_agregat.html');
	}
	
	public function ProcessRequest()
	{		
		$_POST = $_POST->AsArray();
		$userUnitKerjaObj = new UserUnitKerja();

		$userId = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
		$unit = $userUnitKerjaObj->GetUnitKerjaUser($userId);
		
			if(isset($_POST['btncari'])) {
				$this->mData['tahun_anggaran'] = $_POST['tahun_anggaran'];
				$this->mData['unitkerja'] = $_POST['unitkerja'];
				$this->mData['unitkerja_label'] = $_POST['unitkerja_label'];
				$unitkerja = $userUnitKerjaObj->GetSatkerUnitKerja($this->mData['unitkerja']);
			} elseif($_GET['cari'] != "") {
				$get = $_GET->AsArray();
				$this->mData['tahun_anggaran'] = Dispatcher::Instance()->Decrypt($_GET['tahun_anggaran']);
				$this->mData['unitkerja'] = Dispatcher::Instance()->Decrypt($_GET['unitkerja']);
				$this->mData['unitkerja_label'] = Dispatcher::Instance()->Decrypt($_GET['unitkerja_label']);
				$unitkerja = $userUnitKerjaObj->GetSatkerUnitKerja($this->mData['unitkerja']);
			} else {
				$tahun_anggaran = $this->mLPA->GetTahunAnggaranAktif();
				$this->mData = $_POST;
				$this->mData['tahun_anggaran'] = $tahun_anggaran['id'];
				$this->mData['unitkerja'] =$unit['unit_kerja_id'];// $unit['satker_id'];
				$this->mData['unitkerja_label'] =  $unit['unit_kerja_nama'];//$unit['satker_nama'];
			}
			
			$this->mData['total_sub_unit_kerja'] = 
					$userUnitKerjaObj->GetTotalSubUnitKerja($unit['unit_kerja_id']);
					
			$arr_tahun_anggaran = $this->mLPA->GetComboTahunAnggaran();
			Messenger::Instance()->SendToComponent(
												'combobox', 
												'Combobox', 
												'view', 
												'html', 
												'tahun_anggaran',
												 array(
												 		'tahun_anggaran', 
														 $arr_tahun_anggaran, 
														 $this->mData['tahun_anggaran'], '-', 
														 ' style="width:200px;" id="tahun_anggaran"'), 
												 Messenger::CurrentRequest);
			
			$totalData = 1;
			$itemViewed = 20;
			$currPage = 1;
			$startRec = 0 ;
			if(isset($_GET['page'])) {
				$currPage = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
				$startRec =($currPage-1) * $itemViewed;
			}
			
			$url = Dispatcher::Instance()->GetUrl(
										Dispatcher::Instance()->mModule, 
									  	Dispatcher::Instance()->mSubModule, 
								  		Dispatcher::Instance()->mAction, 
								  		Dispatcher::Instance()->mType . 
								  		'&tahun_anggaran=' . 
								  		Dispatcher::Instance()->Encrypt($this->mData['tahun_anggaran']) . 
								  		'&unitkerja=' . 
								  		Dispatcher::Instance()->Encrypt($this->mData['unitkerja']) . 
								  		'&unitkerja_label=' . 
								  		Dispatcher::Instance()->Encrypt($this->mData['unitkerja_label']) . 
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
		//view	
				
		$return['laporan_p'] = $this->mLPA->GetDataPendapatan($this->mData['tahun_anggaran'],$this->mData['unitkerja']);
		$return['laporan_b'] = $this->mLPA->GetDataBelanja($this->mData['tahun_anggaran'],$this->mData['unitkerja']);
		$return['total_laporan_b_per_sd'] = $this->mLPA->GetTotalLaporanBPerSd($this->mData['tahun_anggaran'],$this->mData['unitkerja']);
		$return['total_laporan_p_per_map'] = $this->mLPA->GetTotalLaporanPPerMap($this->mData['tahun_anggaran'],$this->mData['unitkerja']);
				
		//$return['laporan_all'] = $this->mLPA->GetData();
		$return['ta'] = $this->mLPA->GetTahunAnggaran($this->mData['tahun_anggaran']);
		$return['ta_kemarin'] = $this->mLPA->GetTahunAnggaranKemarin($this->mData['tahun_anggaran']);
		
		return $return;
   }
   
   public function ParseTemplate($data = NULL)
   {

		$this->mrTemplate->AddVar('content', 'TAHUN_PERIODE', ($data['ta']['name']));
		$this->mrTemplate->AddVar('content', 'UNIT_KERJA_LABEL', ($data['unit_kerja_nama']));
		$taSebelum = !empty($data['ta_kemarin']['name']) ? $data['ta_kemarin']['name'] :$data['ta']['name'];
	    $this->mrTemplate->AddVar('content', 'TAHUN_SEBELUM_TH_ANGGAR', $taSebelum);
	    $this->mrTemplate->AddVar('content', 'TAHUN_SEKARANG_TH_ANGGAR', ($data['ta']['name']));
      
		$this->mrTemplate->AddVar('content', 'URL_SEARCH',
                                     Dispatcher::Instance()->GetUrl(
                                                                $this->mModulName,
                                                                'LapPendapatanBelanjaAgregat',
                                                                'view', 'html'));
                                                                
		$this->mrTemplate->AddVar('content', 'URL_RESET',
                                     Dispatcher::Instance()->GetUrl(
                                                                $this->mModulName,
                                                                'LapPendapatanBelanjaAgregat',
                                                                'view', 'html'));
                                                                
		$this->mrTemplate->AddVar('content', 'URL_CETAK', 
                                    Dispatcher::Instance()->GetUrl(
                                                                $this->mModulName,
                                                                'CetakLapPendapatanBelanjaAgregat', 
                                                                'view', 
                                                                'html') . 
                                                               	'&tgl=' . Dispatcher::Instance()->Encrypt($this->mData['tahun_anggaran']) . 
																'&id='. Dispatcher::Instance()->Encrypt($data['userId']) . 
																'&unitkerja=' . Dispatcher::Instance()->Encrypt($this->mData['unitkerja']) . 
																'&unitkerja_label=' . Dispatcher::Instance()->Encrypt($this->mData['unitkerja_label']));	
   
		$this->mrTemplate->AddVar('content', 'URL_EXCEL', 
                                    Dispatcher::Instance()->GetUrl(
                                                                $this->mModulName,
                                                                'ExcelLapPendapatanBelanjaAgregat', 
                                                                'view', 
                                                                'xls').
                                                                '&tgl=' . Dispatcher::Instance()->Encrypt($this->mData['tahun_anggaran']) . 
																'&id='. Dispatcher::Instance()->Encrypt($data['userId']) . 
																'&unitkerja=' . Dispatcher::Instance()->Encrypt($this->mData['unitkerja']) . 
																'&unitkerja_label=' . Dispatcher::Instance()->Encrypt($this->mData['unitkerja_label']));	

		$this->mrTemplate->AddVar('content', 'UNITKERJA', $this->mData['unitkerja']);
		if($this->mData['total_sub_unit_kerja'] > 0){
			$this->mrTemplate->AddVar('cek_unitkerja_parent', 'IS_PARENT','YES');
		} else {
			$this->mrTemplate->AddVar('cek_unitkerja_parent', 'IS_PARENT', 'NO');
		}
			$this->mrTemplate->AddVar('cek_unitkerja_parent', 'UNITKERJA_LABEL', 
												$this->mData['unitkerja_label']);
			$this->mrTemplate->AddVar('cek_unitkerja_parent', 'URL_POPUP_UNITKERJA', 
							 Dispatcher::Instance()->GetUrl($this->mModulName ,
																'popupUnitkerja', 
																'view', 
																'html'));	
																

		

			$listDataItem = $data['laporan_p'];		
			$listDataItem_b = $data['laporan_b'];		
										
		if(empty($listDataItem) && empty($listDataItem_b)){
			$this->mrTemplate->AddVar('list_data', 'IS_EMPTY', 'YES');		
	    } else {
			$this->mrTemplate->AddVar('list_data', 'IS_EMPTY', 'NO');
			//$nomor = 0;
			//$listDataItem = $data['laporan_p'];
			
			/**
			 * untuk laporan pendapatan
			 */
			$totalPSekarang = 0; 
			$totalPSebelum = 0; 
			$map_id = NULL;
			$nomor =0;
			$x=0;
			$getTotalPerMAP = $data['total_laporan_p_per_map'] ;
			
			for($i = 0 ; $i < sizeof($listDataItem); )
			{
				
				if($map_id == $listDataItem[$i]['map_id']){
					////$totalRP += $listDataItem[$i]['realisasi'];
					//$totalPP += $listDataItem[$i]['realisasi'];
					$lap[$x]['uraian'] ='[ '.$listDataItem[$i]['kode_penerimaan_kode'].' ]'. ' - '.$listDataItem[$i]['kode_penerimaan_nama'];
					$lap[$x]['realisasi'] =$this->mLPA->SetFormatAngka($listDataItem[$i]['realisasi_sebelum']);
					$lap[$x]['proyeksi'] = $this->mLPA->SetFormatAngka($listDataItem[$i]['nominal_sekarang']);
					$lap[$x]['font_style'] ='font-style:normal;font-weight:normal';
					$this->mrTemplate->AddVars('list_data_item', $lap[$x], '');
					$this->mrTemplate->parseTemplate('list_data_item', 'a');
					$i++;
				}elseif($map_id != $listDataItem[$i]['map_id']){
					$nomor++;
					$map_id = $listDataItem[$i]['map_id'];
					$totalPSebelum += $getTotalPerMAP[$map_id]['realisasi_sebelum'];
					$totalPSekarang += $getTotalPerMAP[$map_id]['nominal_sekarang'];
					//$totalRP += $listDataItem[$i]['realisasi'];
					//$totalPP += $listDataItem[$i]['realisasi'];
					$lap[$x]['uraian'] =$nomor .'. '.$listDataItem[$i]['map_nama'];
					$lap[$x]['realisasi'] = $this->mLPA->SetFormatAngka($getTotalPerMAP[$listDataItem[$i]['map_id']]['realisasi_sebelum']);
					$lap[$x]['proyeksi'] =$this->mLPA->SetFormatAngka($getTotalPerMAP[$listDataItem[$i]['map_id']]['nominal_sekarang']);
					$lap[$x]['font_style'] ='font-style:italic;font-weight:bold';
					$this->mrTemplate->AddVars('list_data_item', $lap[$x], '');
					$this->mrTemplate->parseTemplate('list_data_item', 'a');
					
				}	
				$x++;
				
			}

			/**
			 * untuk laporan belanja
			 */			
			$sdId ='';
			$getTotalPerSD = $data['total_laporan_b_per_sd'];
			$totalRB = 0;
			$totalPB = 0;
			$nomor =1;
			for($j = 0 ; $j < sizeof($listDataItem_b); )
			{
			
			     if($listDataItem_b[$j]['mak_parent_id'] == $sdId){					 
					$send[$j]['uraian'] = $nomor.'. '.$listDataItem_b[$j]['mak_nama'];
					$send[$j]['realisasi'] = $this->mLPA->SetFormatAngka($listDataItem_b[$j]['realisasi_sebelum']);
					$send[$j]['proyeksi'] = $this->mLPA->SetFormatAngka($listDataItem_b[$j]['nominal_sekarang']);
					$send[$j]['font_style'] ='';
					$this->mrTemplate->AddVars('list_data_item_b', $send[$j], '');
					$this->mrTemplate->parseTemplate('list_data_item_b', 'a');
					$j++;
					$nomor++;
				} /*elseif($listDataItem_b[$j]['sumber_dana_id'] != $sdId){
					$sdId = $listDataItem_b[$j]['sumber_dana_id'];
					$send[$j]['uraian'] = $listDataItem_b[$j]['sumber_dana_nama'];
					$send[$j]['realisasi'] =$this->mLPA->SetFormatAngka($getTotalPerSD[$sdId]);
					$send[$j]['proyeksi'] ='';
					$send[$j]['font_style'] ='font-style:italic;font-weight:bold';
					$this->mrTemplate->AddVars('list_data_item_b', $send[$j], '');
					$this->mrTemplate->parseTemplate('list_data_item_b', 'a');
					*/
				 elseif($listDataItem_b[$j]['mak_parent_id'] != $sdId){
					$sdId = $listDataItem_b[$j]['mak_parent_id'];
					$nomor =1;
					$totalBSebelum += $getTotalPerSD[$sdId]['realisasi_sebelum'];
					$totalBSekarang += $getTotalPerSD[$sdId]['nominal_sekarang'];
					$send[$j]['uraian'] = $listDataItem_b[$j]['mak_parent_nama'];
					$send[$j]['realisasi'] =$this->mLPA->SetFormatAngka($getTotalPerSD[$sdId]['realisasi_sebelum']);
					$send[$j]['proyeksi'] =$this->mLPA->SetFormatAngka($getTotalPerSD[$sdId]['nominal_sekarang']);
					$send[$j]['font_style'] ='font-style:italic;font-weight:bold';
					$this->mrTemplate->AddVars('list_data_item_b', $send[$j], '');
					$this->mrTemplate->parseTemplate('list_data_item_b', 'a');
				}	
				
			}
			
			$this->mrTemplate->AddVar('list_data', 'TOTAL_REALISASI', $this->mLPA->SetFormatAngka($totalPSebelum));
			$this->mrTemplate->AddVar('list_data', 'TOTAL_PROYEKSI', $this->mLPA->SetFormatAngka($totalPSekarang));
		    $this->mrTemplate->AddVar('list_data', 'TOTAL_REALISASI_B', $this->mLPA->SetFormatAngka($totalBSebelum));
			$this->mrTemplate->AddVar('list_data', 'TOTAL_PROYEKSI_B', $this->mLPA->SetFormatAngka($totalBSekarang));		
			
		}		
   }
   
}
