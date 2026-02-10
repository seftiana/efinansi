<?php

/**
 * 
 * class ViewLapBiayaLayananPerUnit
 * @package lap_biaya_layanan_per_unit
 * @todo Untuk menampilkan data
 * @subpackage response
 * @since 12 September 2012
 * @SystemAnalyst Nanang Ruswianto <nanang@gamatechno.com>
 * @author noor hadi <noor.hadi@gamatechno.com>
 * @copyright 2012 Gamatechno Indonesia
 * 
 */
 
require_once GTFWConfiguration::GetValue('application', 'docroot') . 
            'module/lap_biaya_layanan_per_unit/business/LapBiayaLayananPerUnit.class.php';

require_once GTFWConfiguration::GetValue('application', 'docroot') . 
	'module/user_unit_kerja/business/UserUnitKerja.class.php';            

class ViewLapBiayaLayananPerUnit extends HtmlResponse
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
		$this->mDBObj = new LapBiayaLayananPerUnit();
		$this->mModulName = 'lap_biaya_layanan_per_unit';
	}
	
	public function TemplateModule()
	{
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') .     
                'module/'.$this->mModulName.'/template');
		$this->SetTemplateFile('view_lap_biaya_layanan_per_unit.html');
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
												'LapBiayaLayananPerUnit', 
												'view', 
												'html'));
												
		$this->mrTemplate->AddVar('content', 'URL_RESET', 
				Dispatcher::Instance()->GetUrl(
												$this->mModulName, 
												'LapBiayaLayananPerUnit', 
												'view', 
												'html'));
		
		$this->mrTemplate->AddVar('content', 'URL_CETAK', 
				Dispatcher::Instance()->GetUrl($this->mModulName, 
												'CetakLapBiayaLayananPerUnit', 
												'view', 
												'html').
												'&tgl=' . Dispatcher::Instance()->Encrypt($this->mData['tahun_anggaran']) . 
												'&id='. Dispatcher::Instance()->Encrypt($data['userId']) . 
												'&unitkerja=' . Dispatcher::Instance()->Encrypt($this->mData['unitkerja']) . 
												'&unitkerja_label=' . Dispatcher::Instance()->Encrypt($this->mData['unitkerja_label']));	
		
		$this->mrTemplate->AddVar('content', 'URL_EXCEL', 
			Dispatcher::Instance()->GetUrl($this->mModulName, 
												'ExcelLapBiayaLayananPerUnit', 
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
       // print_r($listDataItem);
		if(empty($listDataItem)){
			$this->mrTemplate->AddVar('list_data', 'IS_EMPTY', 'YES');		
	    } else {
			$this->mrTemplate->AddVar('list_data', 'IS_EMPTY', 'NO');
			
			$unitId ='';
			$bl ='';
			$makId='';			
			$no =0;
			for($i = 0 ; $i < sizeof($listDataItem); )
			{
				if( ($listDataItem[$i]['unit_kerja_id'] == $unitId) && 
					($listDataItem[$i]['biaya_id'] == $bl) && 
					($listDataItem[$i]['mak_id'] == $makId) 
					){
						$send[$i]['nomor']='';
						$send[$i]['uraian']=' - '.$listDataItem[$i]['komp_nama'] ;
						$send[$i]['volume']=$this->mDBObj->SetFormatAngka($listDataItem[$i]['komp_volume']);
						$send[$i]['jumlah']= $this->mDBObj->SetFormatAngka($listDataItem[$i]['komp_jumlah']) ;
						$send[$i]['font_style'] = '';
						$this->mrTemplate->AddVars('list_data_item', $send[$i], '');
						$this->mrTemplate->parseTemplate('list_data_item', 'a');
					$i++;	
				}elseif($listDataItem[$i]['unit_kerja_id'] != $unitId){
						$unitId = $listDataItem[$i]['unit_kerja_id'] ;
						$no++;
						$send[$i]['nomor']=$no;
						$send[$i]['uraian']=$listDataItem[$i]['unit_kerja_kode'].' - '.
											$listDataItem[$i]['unit_kerja_nama'] ;;
						$send[$i]['volume']='';
						$send[$i]['jumlah']= $this->mDBObj->SetFormatAngka($this->mDBObj->GetTotalPerUnit(
														$listDataItem[$i]['tahun_anggaran_id'],
														$listDataItem[$i]['unit_kerja_id'] 
														));
						$send[$i]['font_style'] = 'font-weight: bold;';
						$this->mrTemplate->AddVars('list_data_item', $send[$i], '');
						$this->mrTemplate->parseTemplate('list_data_item', 'a');
				//}elseif($listDataItem[$i]['biaya_langsung'] != $bl){
				}elseif($listDataItem[$i]['biaya_id'] != $bl){
						$bl = $listDataItem[$i]['biaya_id'] ;
						$send[$i]['nomor']='';
						$send[$i]['uraian']= ( $listDataItem[$i]['biaya_langsung'] == 0) ? 
										'Biaya Tak Langsung' : 'Biaya Langsung';
						$send[$i]['volume']='';
						$send[$i]['jumlah']= $this->mDBObj->SetFormatAngka($this->mDBObj->GetTotalPerBiaya(
														$listDataItem[$i]['tahun_anggaran_id'],
														$listDataItem[$i]['biaya_langsung'] ,
														$listDataItem[$i]['unit_kerja_id'] 
														));
						$send[$i]['font_style'] = 'font-style:italic; font-weight: bold;';
						$this->mrTemplate->AddVars('list_data_item', $send[$i], '');
						$this->mrTemplate->parseTemplate('list_data_item', 'a');
				}elseif($listDataItem[$i]['mak_id'] != $makId){
						$send[$i]['nomor']='';
						$makId = $listDataItem[$i]['mak_id'] ;
						$send[$i]['uraian']=$listDataItem[$i]['mak_kode'].' - '.$listDataItem[$i]['mak_nama']  ;
						$send[$i]['volume']='';
						$send[$i]['jumlah']= $this->mDBObj->SetFormatAngka($this->mDBObj->GetTotalPerMak(
														$listDataItem[$i]['tahun_anggaran_id'],
														$listDataItem[$i]['mak_id'] ,
														$listDataItem[$i]['unit_kerja_id'] 
														));
						$send[$i]['font_style'] = 'font-style:italic; ';
						$this->mrTemplate->AddVars('list_data_item', $send[$i], '');
						$this->mrTemplate->parseTemplate('list_data_item', 'a');
				}
				
			}
//			$total = $this->mDBObj->SetFormatAngka($total);
//			$this->mrTemplate->AddVar('list_data', 'TOTAL_NOMINAL', $total);
		}
        
   }
   
}
