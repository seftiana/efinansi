<?php
require_once GTFWConfiguration::GetValue('application','docroot').
'module/spp/business/spp.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'module/user_unit_kerja/business/UserUnitKerja.class.php';

class ViewListSpp extends HtmlResponse{
	protected $Data;
	public $role;
	public $_POST;
	protected $userunitkerja;
	protected $sppObj;
function __construct(){
	$this->_POST			= $_POST->AsArray();
	$this->userunitkerja	= new UserUnitKerja();
	$this->sppObj			= new Spp();
}
function TemplateModule(){
    $this->setTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').'module/spp/template/');
    $this->setTemplateFile('list_spp.html');
}
function ProcessRequest(){
    $userId 			= trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
	$this->role				= $this->userunitkerja->GetRoleUser($userId);
	$return['unitkerja']	= $this->sppObj->GetUnitKerjaByUser();
	$ta_array				= $this->sppObj->GetTa();
	$msg = Messenger::Instance()->Receive(__FILE__);
	$return['Pesan'] 		= $msg[0][0];
	$return['css']			= $msg[0][1];
	
	if(isset($this->_POST['btnTampilkan'])){
		$return['ta_active']		= $this->_POST['ta_id'];
		$return['kode']				= trim($this->_POST['kode']);
		$return['nama']				= trim($this->_POST['nama']);
		$return['unit_nama']		= trim($this->_POST['unit_nama']);
		$return['unit_id']			= trim($this->_POST['unit_id']);
		$return['sub_unit_nama'] 	= trim($this->_POST['sub_unit_nama']);
		$return['sub_unit_id']		= trim($this->_POST['sub_unit_id']);
	}else{
		$return['ta_active']		= $this->sppObj->GetTaAktif();
		$return['kode']				= '';
		$return['nama']				= '';
		$return['unit_nama']		= $return['unitkerja'][0]['unit_nama'];
		$return['unit_id']			= $return['unitkerja'][0]['unit_id'];
		$return['sub_unit_nama'] 	= '';
		$return['sub_unit_id']		= '';	}
	
	// get data
	
	$total_data		= $this->sppObj->CountData($return['ta_active'], $return['unit_id']);
	$itemViewed = 20;
	$currPage = 1;
	$startRec = 0 ;
	if(isset($_GET['page'])) {
		$currPage = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
		$startRec =($currPage-1) * $itemViewed;
	}
	$this->Data		= $this->sppObj->GetData($return['ta_active'],$return['unit_id'], $startRec, $itemViewed);
	
	$url = Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule, 
		   Dispatcher::Instance()->mSubModule, Dispatcher::Instance()->mAction, 
		   Dispatcher::Instance()->mType . 
		   '&ta=' . $return['ta_active'] . 
		   '&unit=' . $return['unit_id'] .
		   '&unit_nama='. $return['unit_nama'].
		   '&cari=' . Dispatcher::Instance()->Encrypt(1));

	Messenger::Instance()->SendToComponent('paging', 'Paging', 'view', 'html', 'paging_top', array($itemViewed, $total_data, $url, $currPage), Messenger::CurrentRequest);
	
	Messenger::Instance()->SendToComponent('combobox', 'Combobox', 'view', 'html', 'ta_id', 
						   array(
								'ta_id',
								$ta_array,
								$return['ta_active'],
								'kosong',
								' style="width:125px;" '
								) , 
						  Messenger::CurrentRequest);
	
	$return['nomer']	= $startRec+1;
	return $return;
}

function ParseTemplate($data = null){
	if($this->role['role_name'] == 'Administrator'){
		$label_style	= 'display:none;';
		$input_type		= 'text';
		$button_style	= '';
	}else{
		$label_style	= '';
		$input_type		= 'hidden';
		$button_style	= 'display:none;';
	}
	$this->mrTemplate->AddVar('content','LABEL_STYLE',$label_style);
	$this->mrTemplate->AddVar('content','INPUT_TYPE',$input_type);
	$this->mrTemplate->AddVar('content','BUTTON_STYLE',$button_style);
	
	if ($data['Pesan']) {
		$this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
		$this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $data['Pesan']);
		$this->mrTemplate->AddVar('warning_box', 'CSS', $data['css']);
	}
	
	// url search
	$url_search	= Dispatcher::Instance()->GetUrl('spp','ListSpp','view','html');
	$this->mrTemplate->AddVar('content','URL_SEARCH',$url_search);
	
	// data search
	if(isset($this->_POST['btnTampilkan'])){
		$search_kode		= trim($this->_POST['kode']);
		$search_nama		= trim($this->_POST['nama']);
		$search_unit		= trim($this->_POST['unit_nama']);
		$search_unitid		= trim($this->_POST['unit_id']);
		$search_subunit		= trim($this->_POST['sub_unit_nama']);
		$search_subunit_id	= trim($this->_POST['sub_unit_id']);
	}else{
		$search_unit		= $data['unitkerja'][0]['unit_nama'];
		$search_unitid		= $data['unitkerja'][0]['unit_id'];
	}
	$this->mrTemplate->AddVar('content','SEARCH_KODE',$search_kode);
	$this->mrTemplate->AddVar('content','SEARCH_NAMA', $search_nama);
	$this->mrTemplate->AddVar('content','SEARCH_UNIT',$search_unit);
	$this->mrTemplate->AddVar('content','SEARCH_UNITID',$search_unitid);
	$this->mrTemplate->AddVar('content','SEARCH_SUBUNIT',$search_subunit);
	$this->mrTemplate->AddVar('content','SEARCH_SUBUNIT_ID',$search_subnit_id);
	
	// url popup
	$popup_unit		= Dispatcher::Instance()->GetUrl('spp','PopupUnitKerja','view','html');
	$popup_subunit	= Dispatcher::Instance()->GetUrl('spp','PopupSubUnitKerja','view','html');
	
	$this->mrTemplate->AddVar('content','POPUP_UNIT',$popup_unit);
	$this->mrTemplate->AddVar('content','POPUP_SUBUNIT',$popup_subunit);
	
	$dataList	= $this->Data;
	//data
	if(empty($dataList)){
		$this->mrTemplate->AddVar('dataGrid','IS_EMPTY','YES');
	}else{
		// jika data tidak kosong
		$this->mrTemplate->AddVar('dataGrid','IS_EMPTY','NO');
		for($i=0;$i<sizeof($dataList);$i++){
			$dataList[$i]['nomer']		= $data['nomer']+$i;
			$dataList[$i]['pagu_dipa']	= number_format($dataList[$i]['pagu_dipa'],2,',','.');
			$dataList[$i]['nominal_approve']	= number_format($dataList[$i]
												  ['nominal_approve'],2,',','.');
			// link add spp
			$url_add	= Dispatcher::Instance()->GetUrl('spp','AddSpp','view','html&id='.
						  $dataList[$i]['id'].'&ta='.$data['ta_active'].'&unit='.$search_unitid);
			$dataList[$i]['url_add']	= $url_add;
			$this->mrTemplate->AddVars('data_item', $dataList[$i], 'DATA_');
			$this->mrTemplate->parseTemplate('data_item', 'a');
		}
	}
}
}
?>