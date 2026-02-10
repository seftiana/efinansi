<?php

/**
 * 
 * class ViewLapIhktisarPendapatanPerPK
 * @package lap_ihktisar_perndapatan_per_pk
 * @todo Untuk menampilkan data
 * @subpackage response
 * @since 12 September 2012
 * @SystemAnalyst Nanang Ruswianto <nanang@gamatechno.com>
 * @author noor hadi <noor.hadi@gamatechno.com>
 * @copyright 2012 Gamatechno Indonesia
 * 
 */
 
require_once GTFWConfiguration::GetValue('application', 'docroot') . 
            'module/lap_ihktisar_pendapatan_per_pk/business/LapIhktisarPendapatanPerPK.class.php';

require_once GTFWConfiguration::GetValue('application', 'docroot') . 
	'module/user_unit_kerja/business/UserUnitKerja.class.php';            

class ViewLapIhktisarPendapatanPerPK extends HtmlResponse
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
		$this->mDBObj = new LapIhktisarPendapatanPerPK();
		$this->mModulName = 'lap_ihktisar_pendapatan_per_pk';
	}
	
	public function TemplateModule()
	{
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') .     
                'module/'.$this->mModulName.'/template');
		$this->SetTemplateFile('view_lap_ihktisar_pendapatan_per_pk.html');
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
		$return['get_data'] = $this->mDBObj->GetDataLaporan($this->mData['tahun_anggaran'],$this->mData['unitkerja']);
		$return['userId']  = $userId;
		return $return;
   }
   
   public function ParseTemplate($data = NULL)
   {
	   
		$this->mrTemplate->AddVar('content', 'URL_SEARCH', 
				Dispatcher::Instance()->GetUrl(
												$this->mModulName, 
												'lapIhktisarPendapatanPerPK', 
												'view', 
												'html'));
												
		$this->mrTemplate->AddVar('content', 'URL_RESET', 
				Dispatcher::Instance()->GetUrl(
												$this->mModulName, 
												'lapIhktisarPendapatanPerPK', 
												'view', 
												'html'));
		
		$this->mrTemplate->AddVar('content', 'URL_CETAK', 
				Dispatcher::Instance()->GetUrl($this->mModulName, 
												'CetakLapIhktisarPendapatanPerPK', 
												'view', 
												'html').
												'&tgl=' . Dispatcher::Instance()->Encrypt($this->mData['tahun_anggaran']) . 
												'&id='. Dispatcher::Instance()->Encrypt($data['userId']) . 
												'&unitkerja=' . Dispatcher::Instance()->Encrypt($this->mData['unitkerja']) . 
												'&unitkerja_label=' . Dispatcher::Instance()->Encrypt($this->mData['unitkerja_label']));	
		
		$this->mrTemplate->AddVar('content', 'URL_EXCEL', 
				Dispatcher::Instance()->GetUrl($this->mModulName, 
												'ExcelLapIhktisarPendapatanPerPK', 
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
																


		$dataLaporan = $data['get_data'];
		if(empty($dataLaporan)){
			$this->mrTemplate->AddVar('list_data', 'IS_EMPTY', 'YES');		
	    } else {
			$this->mrTemplate->AddVar('list_data', 'IS_EMPTY', 'NO');		
		
			for($k = 0;$k < sizeof($dataLaporan);$k++){

				$total += $dataLaporan[$k]['target'];
				$dataLaporan[$k]['target'] = $this->mDBObj->SetFormatAngka($dataLaporan[$k]['target']);
				$this->mrTemplate->AddVars('list_data_item', $dataLaporan[$k], '');
				$this->mrTemplate->parseTemplate('list_data_item', 'a');
			}
			$total = $this->mDBObj->SetFormatAngka($total);
			$this->mrTemplate->AddVar('list_data', 'TOTAL_NOMINAL', $total);
		}
        
   }
}
