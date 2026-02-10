<?php

/**
 * 
 * class ViewLapPengadaanRup
 * @package lap_pengadaan_rup
 * @todo Untuk menampilkan data
 * @subpackage response
 * @since 29 April 2013
 * @SystemAnalyst Nanang Ruswianto <nanang@gamatechno.com>
 * @author noor hadi <noor.hadi@gamatechno.com>
 * @copyright 2013 Gamatechno Indonesia
 * 
 */
 
require_once GTFWConfiguration::GetValue('application', 'docroot') . 
     'module/lap_pengadaan_rup/business/LapPengadaanRup.class.php';

require_once GTFWConfiguration::GetValue('application', 'docroot') . 
	'module/user_unit_kerja/business/UserUnitKerja.class.php';            

class ViewLapPengadaanRup extends HtmlResponse
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
		$this->mDBObj = new LapPengadaanRup();
		$this->mModulName = 'lap_pengadaan_rup';
	}
	
	public function TemplateModule()
	{
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') .     
                'module/'.$this->mModulName.'/template');
		$this->SetTemplateFile('view_lap_pengadaan_rup.html');
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
		$return['get_total_per_sk'] =$this->mDBObj->GetTotalPerSk($this->mData['tahun_anggaran'],$this->mData['unitkerja']);
		$return['get_total_per_k'] =$this->mDBObj->GetTotalPerk($this->mData['tahun_anggaran'],$this->mData['unitkerja']);		
		$return['get_total_all'] =$this->mDBObj->GetTotalAll($this->mData['tahun_anggaran'],$this->mData['unitkerja']);
		
		$return['userId']  = $userId;
		$return['ta'] = $this->mDBObj->GetTahunAnggaran($this->mData['tahun_anggaran']);
		
		return $return;
   }
   
   public function ParseTemplate($data = NULL)
   {	 		
	    $this->mrTemplate->AddVar('content', 'TAHUN_ANGGARAN', ($data['ta']['name']));
		$this->mrTemplate->AddVar('content', 'URL_SEARCH', 
				Dispatcher::Instance()->GetUrl(
												$this->mModulName, 
												'LapPengadaanRup', 
												'view', 
												'html'));
												
		$this->mrTemplate->AddVar('content', 'URL_RESET', 
				Dispatcher::Instance()->GetUrl(
												$this->mModulName, 
												'LapPengadaanRup', 
												'view', 
												'html'));
		
		$this->mrTemplate->AddVar('content', 'URL_CETAK', 
				Dispatcher::Instance()->GetUrl($this->mModulName, 
												'CetakLapPengadaanRup', 
												'view', 
												'html').
												'&tgl=' . Dispatcher::Instance()->Encrypt($this->mData['tahun_anggaran']) . 
												'&id='. Dispatcher::Instance()->Encrypt($data['userId']) . 
												'&unitkerja=' . Dispatcher::Instance()->Encrypt($this->mData['unitkerja']) . 
												'&unitkerja_label=' . Dispatcher::Instance()->Encrypt($this->mData['unitkerja_label']));	
		
		$this->mrTemplate->AddVar('content', 'URL_EXCEL', 
						Dispatcher::Instance()->GetUrl($this->mModulName, 
												'ExcelLapPengadaanRup', 
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
						
			$kegiatanId ='';
			$subKegiatanId ='';
			
			$x = 0;
			$no=0;
			
			$getTotalPerSk = $data['get_total_per_sk'];
			$getTotalPerK = $data['get_total_per_k'];

			for($i = 0 ; $i < sizeof($listDataItem); )
			{
				if(($kegiatanId == $listDataItem[$i]['output_id']) && 
					($subKegiatanId == $listDataItem[$i]['komponen_id'])
					){						
					
					$dataLaporan[$x]['kode'] = $listDataItem[$i]['mak_kode'];
					$dataLaporan[$x]['nama'] = $listDataItem[$i]['mak_nama'];
					$dataLaporan[$x]['volume'] = $this->mDBObj->SetFormatAngka($listDataItem[$i]['db_volume']);
					$dataLaporan[$x]['nominal_total'] = $this->mDBObj->SetFormatAngka($listDataItem[$i]['db_nominal_total']);
					if(!empty($listDataItem[$i]['sumber_dana_nama'])){
							$sumberDana = str_replace(',','<br />',$listDataItem[$i]['sumber_dana_nama']);
					}
					$dataLaporan[$x]['sumber_dana_nama'] = $sumberDana;
					$dataLaporan[$x]['font_style'] ='';
					$i++;
					   
				} elseif($kegiatanId != $listDataItem[$i]['output_id']){
					$kegiatanId = $listDataItem[$i]['output_id'];
					$dataLaporan[$x]['kode'] = $listDataItem[$i]['output_kode'];
					$dataLaporan[$x]['nama'] = $listDataItem[$i]['output_nama'];
					$dataLaporan[$x]['volume'] = NULL;
					$dataLaporan[$x]['nominal_total'] = $this->mDBObj->SetFormatAngka($getTotalPerK[$kegiatanId]);
					$dataLaporan[$x]['sumber_dana_nama'] = NULL;
					$dataLaporan[$x]['class_name'] ='table-common-even1';
					$dataLaporan[$x]['font_style'] ='font-weight:bold';
				} elseif($subKegiatanId != $listDataItem[$i]['komponen_id']){
					$subKegiatanId = $listDataItem[$i]['komponen_id'];
					$dataLaporan[$x]['kode'] = $listDataItem[$i]['komponen_kode'];
					$dataLaporan[$x]['nama'] = $listDataItem[$i]['komponen_nama'];
					$dataLaporan[$x]['volume'] = NULL;
					$dataLaporan[$x]['nominal_total'] = $this->mDBObj->SetFormatAngka($getTotalPerSk[$subKegiatanId]);
					$dataLaporan[$x]['sumber_dana_nama'] = NULL;
					$dataLaporan[$x]['class_name'] ='table-common-even1';
					$dataLaporan[$x]['font_style'] ='font-weight:bold; font-style:italic;';
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
			$this->mrTemplate->AddVar('list_data', 'TOTAL_NOMINAL', $this->mDBObj->SetFormatAngka($total));
		}
	
        
   }
   
}


?>