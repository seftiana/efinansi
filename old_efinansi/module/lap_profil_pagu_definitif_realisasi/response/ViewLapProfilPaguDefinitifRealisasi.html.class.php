<?php

/**
 * 
 * class ViewLapProfilPaguDefinitifRealisasi
 * @package lap_profil_pagu_definitif_realisasi
 * @todo Untuk menampilkan data
 * @subpackage response
 * @since june 24 2013
 * @analyst Nanang Ruswianto <nanang@gamatechno.com>
 * @author noor hadi <noor.hadi@gamatechno.com>
 * @copyright 2013 Gamatechno Indonesia
 * 
 */
 
require_once GTFWConfiguration::GetValue('application', 'docroot') . 
            'module/lap_profil_pagu_definitif_realisasi/business/LapProfilPaguDefinitifRealisasi.class.php';

require_once GTFWConfiguration::GetValue('application', 'docroot') . 
	'module/user_unit_kerja/business/UserUnitKerja.class.php';            

class ViewLapProfilPaguDefinitifRealisasi extends HtmlResponse
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
		$this->mDBObj = new LapProfilPaguDefinitifRealisasi();
		$this->mModulName = 'lap_profil_pagu_definitif_realisasi';
	}
	
	public function TemplateModule()
	{
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') .     
                'module/'.$this->mModulName.'/template');
		$this->SetTemplateFile('view_lap_profil_pagu_definitif_realisasi.html');
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
			
			$get_data = $this->mDBObj->GetData($this->mData['tahun_anggaran'],$this->mData['unitkerja'],$startRec,$itemViewed);
			$totalData = $this->mDBObj->GetCountData();
			
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
		$return['userId']  = $userId;
		return $return;
   }
   
   public function ParseTemplate($data = NULL)
   {
	   
		$this->mrTemplate->AddVar('content', 'URL_SEARCH', 
				Dispatcher::Instance()->GetUrl(
												$this->mModulName, 
												'LapProfilPaguDefinitifRealisasi', 
												'view', 
												'html'));
												
		$this->mrTemplate->AddVar('content', 'URL_RESET', 
				Dispatcher::Instance()->GetUrl(
												$this->mModulName, 
												'LapProfilPaguDefinitifRealisasi', 
												'view', 
												'html'));
		
		$this->mrTemplate->AddVar('content', 'URL_CETAK', 
				Dispatcher::Instance()->GetUrl($this->mModulName, 
												'CetakLapProfilPaguDefinitifRealisasi', 
												'view', 
												'html').
												'&tgl=' . Dispatcher::Instance()->Encrypt($this->mData['tahun_anggaran']) . 
												'&id='. Dispatcher::Instance()->Encrypt($data['userId']) . 
												'&unitkerja=' . Dispatcher::Instance()->Encrypt($this->mData['unitkerja']) . 
												'&unitkerja_label=' . Dispatcher::Instance()->Encrypt($this->mData['unitkerja_label']));	
		
		$this->mrTemplate->AddVar('content', 'URL_EXCEL', 
			Dispatcher::Instance()->GetUrl($this->mModulName, 
												'ExcelLapProfilPaguDefinitifRealisasi', 
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
																'popupUnitKerja', 
																'view', 
																'html'));	
        $listDataItem= $data['get_data'];
       // print_r($listDataItem);
		if(empty($listDataItem)){
			$this->mrTemplate->AddVar('list_data', 'IS_EMPTY', 'YES');		
	    } else {
			$this->mrTemplate->AddVar('list_data', 'IS_EMPTY', 'NO');
			
			$unitId ='';
			$kegiatanId='';
			$outputId ='';
			$komponenId ='';
			$no =0;
			for($i = 0 ; $i < sizeof($listDataItem); )
			{
				if( ($listDataItem[$i]['unit_kerja_id'] == $unitId) && 
					($listDataItem[$i]['kegiatan_id'] == $kegiatanId) && 
					($listDataItem[$i]['output_id'] == $outputId) 
					){
						$send[$i]['kode']= $listDataItem[$i]['komponen_kode'];
						$send[$i]['uraian']= $listDataItem[$i]['komponen_nama'] ;
						$send[$i]['total_pagu_def']=$this->mDBObj->SetFormatAngka($listDataItem[$i]['total_pagu_def']);
						$send[$i]['total_realisasi']= $this->mDBObj->SetFormatAngka($listDataItem[$i]['total_realisasi']) ;
						$send[$i]['font_style'] = '';
						$this->mrTemplate->AddVars('list_data_item', $send[$i], '');
						$this->mrTemplate->parseTemplate('list_data_item', 'a');
					$i++;	
				}elseif($listDataItem[$i]['unit_kerja_id'] != $unitId){
						$unitId = $listDataItem[$i]['unit_kerja_id'] ;
						$no++;
						$send[$i]['kode'] = $listDataItem[$i]['unit_kerja_kode'];
						$send[$i]['uraian'] = $listDataItem[$i]['unit_kerja_nama'];
						$send[$i]['total_pagu_def'] = $this->mDBObj->SetFormatAngka($this->mDBObj->GetTotalPerUnit(
														$listDataItem[$i]['tahun_anggaran_id'],
														$listDataItem[$i]['unit_kerja_id'] 
														));
						$send[$i]['total_realisasi']= $this->mDBObj->SetFormatAngka(0);
						$send[$i]['font_style'] = 'font-weight: bold;';
						$this->mrTemplate->AddVars('list_data_item', $send[$i], '');
						$this->mrTemplate->parseTemplate('list_data_item', 'a');
				}elseif($listDataItem[$i]['kegiatan_id'] != $kegiatanId){
						$kegiatanId = $listDataItem[$i]['kegiatan_id'];
						$send[$i]['kode']= $listDataItem[$i]['kegiatan_kode'];
						$send[$i]['uraian']=  $listDataItem[$i]['kegiatan_nama'];
						$send[$i]['total_pagu_def'] = $this->mDBObj->SetFormatAngka($this->mDBObj->GetTotalPerKegiatan(
														$listDataItem[$i]['tahun_anggaran_id'],
														$listDataItem[$i]['kegiatan_id'] ,
														$listDataItem[$i]['unit_kerja_id'] 
														));
						$send[$i]['total_realisasi']= $this->mDBObj->SetFormatAngka(0);
						$send[$i]['font_style'] = 'font-style:italic; font-weight: bold;';
						$this->mrTemplate->AddVars('list_data_item', $send[$i], '');
						$this->mrTemplate->parseTemplate('list_data_item', 'a');
						
				}elseif($listDataItem[$i]['output_id'] != $outputId){
						$outputId = $listDataItem[$i]['output_id'];
						
						$send[$i]['kode'] =  $listDataItem[$i]['output_kode'];
						$send[$i]['uraian'] = $listDataItem[$i]['output_nama'];
						$send[$i]['total_pagu_def'] = $this->mDBObj->SetFormatAngka($this->mDBObj->GetTotalPerOutput(
														$listDataItem[$i]['tahun_anggaran_id'],
														$listDataItem[$i]['output_id'] ,
														$listDataItem[$i]['unit_kerja_id'] 
														));
						$send[$i]['total_realisasi']= $this->mDBObj->SetFormatAngka(0);
						$send[$i]['font_style'] = 'font-style:italic;';
						$this->mrTemplate->AddVars('list_data_item', $send[$i], '');
						$this->mrTemplate->parseTemplate('list_data_item', 'a');
				}
				
			}
			$total = $this->mDBObj->SetFormatAngka($this->mDBObj->GetTotalAll(
																$this->mData['tahun_anggaran'],
																$this->mData['unitkerja']));
			$this->mrTemplate->AddVar('list_data', 'TOTAL_NOMINAL_PAGU_DEF', $total);
			$this->mrTemplate->AddVar('list_data', 'TOTAL_NOMINAL_REALISASI', 0);
		}
        
   }
   
}

?>