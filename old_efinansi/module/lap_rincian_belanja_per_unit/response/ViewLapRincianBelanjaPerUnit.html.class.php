<?php

/**
 * 
 * class ViewLapRincianBelanjaPerUnit
 * @package lap_belanja_per_unit
 * @todo Untuk menampilkan data
 * @subpackage response
 * @since 12 September 2012
 * @SystemAnalyst Nanang Ruswianto <nanang@gamatechno.com>
 * @author noor hadi <noor.hadi@gamatechno.com>
 * @copyright 2012 Gamatechno Indonesia
 * 
 */
 
require_once GTFWConfiguration::GetValue('application', 'docroot') . 
            'module/lap_rincian_belanja_per_unit/business/LapRincianBelanjaPerUnit.class.php';

require_once GTFWConfiguration::GetValue('application', 'docroot') . 
	'module/user_unit_kerja/business/UserUnitKerja.class.php';            

class ViewLapRincianBelanjaPerUnit extends HtmlResponse
{
	
	/**
	 * untuk menginstanskan class database object
	 */
	protected $mDBObj; 
	protected $mModulName;
	protected $mData;
	
	public function __construct()
	{
		parent::__construct();
		$this->mDBObj = new LapRincianBelanjaPerUnit();
		$this->mModulName = 'lap_rincian_belanja_per_unit';
	}
	
	public function TemplateModule()
	{
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') .     
                'module/'.$this->mModulName.'/template');
		$this->SetTemplateFile('view_lap_rincian_belanja_per_unit.html');
	}
	
	public function ProcessRequest()
	{
		$_POST = $_POST->AsArray();
		$userUnitKerjaObj = new UserUnitKerja();

		$userId = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
		$unit = $userUnitKerjaObj->GetUnitKerjaUser($userId);
		
			if($_POST['btncari']) {
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
				$tahun_anggaran = $this->mDBObj->GetTahunAnggaranAktif();
				$this->mData = $_POST;
				$this->mData['tahun_anggaran'] = $tahun_anggaran['id'];
				$this->mData['unitkerja'] =$unit['unit_kerja_id'];// $unit['satker_id'];
				$this->mData['unitkerja_label'] =  $unit['unit_kerja_nama'];//$unit['satker_nama'];
			}
			
			$this->mData['total_sub_unit_kerja'] = 
					$userUnitKerjaObj->GetTotalSubUnitKerja($unit['unit_kerja_id']);
					
			$arr_tahun_anggaran = $this->mDBObj->GetComboTahunAnggaran();
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
	 
		//view
			$itemViewed = 20;
			$currPage = 1;
			$startRec = 0 ;
			if(isset($_GET['page'])) {
				$currPage = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
				$startRec =($currPage-1) * $itemViewed;
			}		
		$get_data = $this->mDBObj->GetDataLaporan($this->mData['tahun_anggaran'],$this->mData['unitkerja'],$startRec,$itemViewed);
		
		$totalData = $this->mDBObj->GetCountDataLaporan();

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
		$return['get_data'] = $get_data;
		$return['get_total_per_mak'] =$this->mDBObj->GetTotalPerMak($this->mData['tahun_anggaran'],$this->mData['unitkerja']);
		$return['get_total_per_pagu'] =$this->mDBObj->GetTotalPerPagu($this->mData['tahun_anggaran'],$this->mData['unitkerja']);
		$return['get_total_per_sk'] =$this->mDBObj->GetTotalPerSk($this->mData['tahun_anggaran'],$this->mData['unitkerja']);
		$return['get_total_per_k'] =$this->mDBObj->GetTotalPerk($this->mData['tahun_anggaran'],$this->mData['unitkerja']);
		$return['get_total_per_p'] =$this->mDBObj->GetTotalPerP($this->mData['tahun_anggaran'],$this->mData['unitkerja']);
		$return['get_total_per_u'] =$this->mDBObj->GetTotalPerU($this->mData['tahun_anggaran'],$this->mData['unitkerja']);
		$return['get_total_all'] =$this->mDBObj->GetTotalAll($this->mData['tahun_anggaran'],$this->mData['unitkerja']);
		$return['get_sd'] =$this->mDBObj->GetSumberDana($this->mData['tahun_anggaran'],$this->mData['unitkerja']);
		
		$return['userId']  = $userId;
		$return['ta'] = $this->mDBObj->GetTahunAnggaran($this->mData['tahun_anggaran']);
		$return['ta_kemarin'] = $this->mDBObj->GetTahunAnggaranKemarin($this->mData['tahun_anggaran']);
		return $return;
   }
   
   public function ParseTemplate($data = NULL)
   {	   
		$taSebelum = !empty($data['ta_kemarin']['name']) ? $data['ta_kemarin']['name'] :$data['ta']['name'];
	    $this->mrTemplate->AddVar('content', 'TAHUN_SEBELUM_TH_ANGGAR', $taSebelum);
	    $this->mrTemplate->AddVar('content', 'TAHUN_SEKARANG_TH_ANGGAR', ($data['ta']['name']));
		$this->mrTemplate->AddVar('content', 'URL_SEARCH', 
				Dispatcher::Instance()->GetUrl(
												$this->mModulName, 
												'LapRincianBelanjaPerUnit', 
												'view', 
												'html'));
												
		$this->mrTemplate->AddVar('content', 'URL_RESET', 
				Dispatcher::Instance()->GetUrl(
												$this->mModulName, 
												'LapRincianBelanjaPerUnit', 
												'view', 
												'html'));
		
		$this->mrTemplate->AddVar('content', 'URL_CETAK', 
				Dispatcher::Instance()->GetUrl($this->mModulName, 
												'CetakLapRincianBelanjaPerUnit', 
												'view', 
												'html').
												'&tgl=' . Dispatcher::Instance()->Encrypt($this->mData['tahun_anggaran']) . 
												'&id='. Dispatcher::Instance()->Encrypt($data['userId']) . 
												'&unitkerja=' . Dispatcher::Instance()->Encrypt($this->mData['unitkerja']) . 
												'&unitkerja_label=' . Dispatcher::Instance()->Encrypt($this->mData['unitkerja_label']));	
		
		$this->mrTemplate->AddVar('content', 'URL_EXCEL', 
						Dispatcher::Instance()->GetUrl($this->mModulName, 
												'ExcelLapRincianBelanjaPerUnit', 
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
        $listDataItem= $data['get_data'];
        
		if(empty($listDataItem)){
			$this->mrTemplate->AddVar('list_data', 'IS_EMPTY', 'YES');		
	    } else {
			$this->mrTemplate->AddVar('list_data', 'IS_EMPTY', 'NO');
			
			$unitId ='';
			$programId ='';
			$kegiatanId ='';
			$subKegiatanId ='';
			$makpId = '';
			$makId = '';
			
			$x = 0;
			$no=0;
			$getTotalPerMak = $data['get_total_per_mak'];
			$getTotalPerPagu = $data['get_total_per_pagu'];
			$getTotalPerSk = $data['get_total_per_sk'];
			$getTotalPerK = $data['get_total_per_k'];
			$getTotalPerP = $data['get_total_per_p'];
			$getTotalPerU = $data['get_total_per_u'];

			for($i = 0 ; $i < sizeof($listDataItem); )
			{
				if(($unitId == $listDataItem[$i]['unit_id']) && 
					($programId == $listDataItem[$i]['program_id']) && 
					($kegiatanId == $listDataItem[$i]['kegiatan_id']) &&
					($subKegiatanId == $listDataItem[$i]['sub_kegiatan_id']) &&
					($makpId == $listDataItem[$i]['mak_parent_id']) &&
					($makId == $listDataItem[$i]['mak_id']) 
					){
						
					$dataLaporan[$x]['kode'] ='';
					if(!empty($listDataItem[$i]['sumber_dana'])){
						$sd = explode(',',$listDataItem[$i]['sumber_dana']);
						$dataLaporan[$x]['sumber_dana_sebelum'] =($listDataItem[$i]['nominal_sebelum'] > 0) ? $sd[0] : '';
						$dataLaporan[$x]['sumber_dana_sekarang'] =($listDataItem[$i]['nominal_sekarang'] > 0) ? (isset($sd[1]) ? $sd[1] : $sd[0]) : '';
						
						//$dataLaporan[$x]['sumber_dana_sebelum'] = $sd[0];
						//$dataLaporan[$x]['sumber_dana_sekarang'] =  $sd[1];
					}else{
						$dataLaporan[$x]['sumber_dana_sebelum'] ='';
						$dataLaporan[$x]['sumber_dana_sekarang'] ='';
					}
					
					$dataLaporan[$x]['nama'] = ' - '.$listDataItem[$i]['komponen_nama'];
					$dataLaporan[$x]['volume_sebelum'] = $listDataItem[$i]['volume_sebelum'];
					$dataLaporan[$x]['volume_sekarang'] = $listDataItem[$i]['volume_sekarang'];
					$dataLaporan[$x]['target_sebelum'] = $this->mDBObj->SetFormatAngka($listDataItem[$i]['nominal_sebelum']);
					$dataLaporan[$x]['target_sekarang'] = $this->mDBObj->SetFormatAngka($listDataItem[$i]['nominal_sekarang']);
					$dataLaporan[$x]['font_style'] ='';
					$i++;
					   
				} elseif($unitId != $listDataItem[$i]['unit_id']){
					$unitId = $listDataItem[$i]['unit_id'];
					$dataLaporan[$x]['sumber_dana_sebelum'] ='';
					$dataLaporan[$x]['sumber_dana_sekarang'] ='';
					$dataLaporan[$x]['kode'] =$listDataItem[$i]['unit_kode'];
					$dataLaporan[$x]['nama'] = $listDataItem[$i]['unit_nama'];
					$dataLaporan[$x]['target_sebelum'] = $this->mDBObj->SetFormatAngka($getTotalPerU[$unitId]['sebelum']);
					$dataLaporan[$x]['target_sekarang'] = $this->mDBObj->SetFormatAngka($getTotalPerU[$unitId]['sekarang']);
					$dataLaporan[$x]['volume_sebelum'] ='';
					$dataLaporan[$x]['volume_sekarang'] ='';
					$dataLaporan[$x]['padding'] =0;
					$dataLaporan[$x]['class_name'] ='table-common-even1';
					$dataLaporan[$x]['font_style'] ='font-weight:bold';
				} elseif($programId != $listDataItem[$i]['program_id']){
					$programId = $listDataItem[$i]['program_id'];
					$dataLaporan[$x]['sumber_dana_sebelum'] ='';
					$dataLaporan[$x]['sumber_dana_sekarang'] ='';
					$dataLaporan[$x]['kode'] =$listDataItem[$i]['program_kode'];
					$dataLaporan[$x]['nama'] =$listDataItem[$i]['program_nama'];
					$dataLaporan[$x]['target_sebelum'] = $this->mDBObj->SetFormatAngka($getTotalPerP[$programId]['sebelum']);
					$dataLaporan[$x]['target_sekarang'] = $this->mDBObj->SetFormatAngka($getTotalPerP[$programId]['sekarang']);
					$dataLaporan[$x]['volume_sebelum'] ='';
					$dataLaporan[$x]['volume_sekarang'] ='';
					$dataLaporan[$x]['padding'] =0;
					$dataLaporan[$x]['class_name'] ='table-common-even1';
					$dataLaporan[$x]['font_style'] ='font-weight:bold';
				} elseif($kegiatanId != $listDataItem[$i]['kegiatan_id']){
					$kegiatanId  = $listDataItem[$i]['kegiatan_id'];
					$dataLaporan[$x]['sumber_dana_sebelum'] ='';
					$dataLaporan[$x]['sumber_dana_sekarang'] ='';
					$dataLaporan[$x]['kode'] = $listDataItem[$i]['kegiatan_kode'];
					$dataLaporan[$x]['nama'] = $listDataItem[$i]['kegiatan_nama'];
					$dataLaporan[$x]['target_sebelum'] = $this->mDBObj->SetFormatAngka($getTotalPerK[$kegiatanId]['sebelum']);
					$dataLaporan[$x]['target_sekarang'] = $this->mDBObj->SetFormatAngka($getTotalPerK[$kegiatanId]['sekarang']);
					$dataLaporan[$x]['volume_sebelum'] ='';
					$dataLaporan[$x]['volume_sekarang'] ='';
					$dataLaporan[$x]['class_name'] ='table-common-even1';
					$dataLaporan[$x]['font_style'] ='font-weight:bold;font-style:italic';
				} elseif($subKegiatanId != $listDataItem[$i]['sub_kegiatan_id']){
					$subKegiatanId  = $listDataItem[$i]['sub_kegiatan_id'];
					$dataLaporan[$x]['sumber_dana_sebelum'] ='';
					$dataLaporan[$x]['sumber_dana_sekarang'] ='';
					$dataLaporan[$x]['kode'] = $listDataItem[$i]['sub_kegiatan_kode'];
					$dataLaporan[$x]['nama'] = $listDataItem[$i]['sub_kegiatan_nama'];
					$dataLaporan[$x]['target_sebelum'] = $this->mDBObj->SetFormatAngka($getTotalPerSk[$subKegiatanId]['sebelum']);
					$dataLaporan[$x]['target_sekarang'] = $this->mDBObj->SetFormatAngka($getTotalPerSk[$subKegiatanId]['sekarang']);
					$dataLaporan[$x]['volume_sebelum'] ='';
					$dataLaporan[$x]['volume_sekarang'] ='';
					$dataLaporan[$x]['class_name'] ='table-common-even1';
					$dataLaporan[$x]['font_style'] ='font-style:italic';
				} elseif($makpId != $listDataItem[$i]['mak_parent_id']){
					$makpId  = $listDataItem[$i]['mak_parent_id'];
					$dataLaporan[$x]['sumber_dana_sebelum'] ='';
					$dataLaporan[$x]['sumber_dana_sekarang'] ='';
					$dataLaporan[$x]['kode'] =$listDataItem[$i]['mak_parent_kode'];
					$dataLaporan[$x]['nama'] =$listDataItem[$i]['mak_parent_nama'];
					$dataLaporan[$x]['target_sebelum'] = $this->mDBObj->SetFormatAngka($getTotalPerPagu[$makpId]['sebelum']);
					$dataLaporan[$x]['target_sekarang'] = $this->mDBObj->SetFormatAngka($getTotalPerPagu[$makpId]['sekarang']);
					$dataLaporan[$x]['volume'] ='';
					$dataLaporan[$x]['class_name'] ='table-common-even1';
					$dataLaporan[$x]['font_style'] ='text-decoration:underline" ';
				} elseif($makId != $listDataItem[$i]['mak_id']){
					$makId  = $listDataItem[$i]['mak_id'];
					$dataLaporan[$x]['sumber_dana_sebelum'] ='';
					$dataLaporan[$x]['sumber_dana_sekarang'] ='';
					$dataLaporan[$x]['kode'] = '';
					$dataLaporan[$x]['nama'] = $listDataItem[$i]['mak_kode'].' - '.$listDataItem[$i]['mak_nama'];
					$dataLaporan[$x]['target_sebelum'] = $this->mDBObj->SetFormatAngka($getTotalPerMak[$makId]['sebelum']);
					$dataLaporan[$x]['target_sekarang'] = $this->mDBObj->SetFormatAngka($getTotalPerMak[$makId]['sekarang']);
					$dataLaporan[$x]['volume_sebelum'] ='';
					$dataLaporan[$x]['volume_sekarang'] ='';
					$dataLaporan[$x]['class_name'] ='table-common-even1';
					$dataLaporan[$x]['font_style'] ='font-style:italic;text-decoration:underline" ';
				}
				
				$x++;
			}
		
		
			//for($k = 0;$k < sizeof($dataLaporan);$k++){
			foreach($dataLaporan as $key => $value)	{
				
				//$this->mrTemplate->AddVars('list_data_item', $dataLaporan[$k], '');
				$this->mrTemplate->AddVars('list_data_item', $dataLaporan[$key], '');
				$this->mrTemplate->parseTemplate('list_data_item', 'a');
			}
			
			
			$total = $data['get_total_all'];
			$this->mrTemplate->AddVar('list_data', 'TOTAL_NOMINAL_SEBELUM', $this->mDBObj->SetFormatAngka($total['nominal_sebelum']));
			$this->mrTemplate->AddVar('list_data', 'TOTAL_NOMINAL_SEKARANG', $this->mDBObj->SetFormatAngka($total['nominal_sekarang']));
		}
		$listDataItemSD = $data['get_sd'];
		if(empty($listDataItemSD)){
			$this->mrTemplate->AddVar('list_data_sd', 'IS_EMPTY', 'YES');		
	    } else {
			$this->mrTemplate->AddVar('list_data_sd', 'IS_EMPTY', 'NO');
			
			foreach($listDataItemSD as $key => $value)	{
				$listDataItemSD[$key]['nominal_a'] = $this->mDBObj->SetFormatAngka($listDataItemSD[$key]['nominal_sebelum']);
				$listDataItemSD[$key]['nominal_b'] = $this->mDBObj->SetFormatAngka($listDataItemSD[$key]['nominal_sekarang']);
				$this->mrTemplate->AddVars('list_data_sd_item', $listDataItemSD[$key], '');
				$this->mrTemplate->parseTemplate('list_data_sd_item', 'a');
			}
			
		}		
        
   }
   
}
