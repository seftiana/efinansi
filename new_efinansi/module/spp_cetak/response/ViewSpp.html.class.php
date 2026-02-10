<?php
require_once GTFWConfiguration::GetValue('application','docroot').
'module/spp_cetak/business/spp.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'module/user_unit_kerja/business/UserUnitKerja.class.php';

class ViewSpp extends HtmlResponse{
function TemplateModule(){
    $this->setTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
	'module/spp_cetak/template/');
    $this->setTemplateFile('spp.html');
}
function ProcessRequest(){
	$obj					= new Spp();
	$userUnitKerja			= new UserUnitKerja();
	$userId 				= trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
	$return['role']			= $userUnitKerja->GetRoleUser($userId);
	$return['unitkerja']	= $obj->GetUnitKerjaByUser();
	$ta_array				= $obj->GetTa();
	
	
	
	if(isset($_POST['btnTampilkan'])){
		$return['ta_active']		= $_POST['ta_id'];
		$return['kode']				= trim($_POST['kode']);
		$return['nama']				= trim($_POST['nama']);
		$return['unit_nama']		= trim($_POST['unit_nama']);
		$return['unit_id']			= trim($_POST['unit_id']);
		$return['sub_unit_nama'] 	= trim($_POST['sub_unit_nama']);
		$return['sub_unit_id']		= trim($_POST['sub_unit_id']);
	}else{
		$return['ta_active']		= $obj->GetTaAktif();
		$return['kode']				= '';
		$return['nama']				= '';
		$return['unit_nama']		= $return['unitkerja'][0]['unit_nama'];
		$return['unit_id']			= $return['unitkerja'][0]['unit_id'];
		$return['sub_unit_nama'] 	= '';
		$return['sub_unit_id']		= '';	
	}
	Messenger::Instance()->SendToComponent('combobox', 'Combobox', 'view', 'html', 'ta_id', 
						   array(
								'ta_id',
								$ta_array,
								$return['ta_active'],
								'kosong',
								' style="width:125px;" '
								) , 
						  Messenger::CurrentRequest);
	
	$total_data				= $obj->Count($return['ta_active'], $return['unit_id']);
	$itemViewed = 20;
	$currPage = 1;
	$startRec = 0 ;
	if(isset($_GET['page'])) {
		$currPage = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
		$startRec =($currPage-1) * $itemViewed;
	}
	$return['data']			= $obj->GetData($return['ta_active'],$return['unit_id'],$startRec,$itemViewed);
	$url = Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule, 
		   Dispatcher::Instance()->mSubModule, Dispatcher::Instance()->mAction, 
		   Dispatcher::Instance()->mType . 
		   '&ta=' . $return['ta_active'] . 
		   '&unit=' . $return['unit_id'] .
		   '&unit_nama='. $return['unit_nama'].
		   '&cari=' . Dispatcher::Instance()->Encrypt(1));

	Messenger::Instance()->SendToComponent('paging', 'Paging', 'view', 'html', 'paging_top', array($itemViewed, $total_data, $url, $currPage), Messenger::CurrentRequest);
	
	$return['nomor']	= $startRec+1;
    return $return;
}

function ParseTemplate($data = null){
	$dataList	= $data['data'];

	if($data['role']['role_name'] == 'Administrator'){
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
	
	// url search
	$url_search	= Dispatcher::Instance()->GetUrl('spp_cetak','Spp','view','html');
	$this->mrTemplate->AddVar('content','URL_SEARCH',$url_search);
	
	// data search
	if(isset($_POST['btnTampilkan'])){
		$search_unit		= trim($_POST['unit_nama']);
		$search_unitid		= trim($_POST['unit_id']);
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
	$popup_unit		= Dispatcher::Instance()->GetUrl('spp_cetak','PopupUnitKerja','view','html');
	$popup_subunit	= Dispatcher::Instance()->GetUrl('spp_cetak','PopupSubUnitKerja','view','html');
	
	$this->mrTemplate->AddVar('content','POPUP_UNIT',$popup_unit);
	$this->mrTemplate->AddVar('content','POPUP_SUBUNIT',$popup_subunit);
	
	if(sizeof($dataList) == 0){
		$this->mrTemplate->AddVar('dataGrid','IS_EMPTY','YES');
	}else{
		$this->mrTemplate->AddVar('dataGrid','IS_EMPTY','NO');
		for($i=0;$i<sizeof($dataList);$i++){
			$dataList[$i]['no']			= $i+$data['nomor'];
			$dataList[$i]['no_spp']		= $dataList[$i]['nomor_spp'].'/H46.PPK/SPP/IV/'.date('Y', time());
			$dataList[$i]['spp_total']	= number_format($dataList[$i]['spp_total'],2,',','.');
			$dataList[$i]['id']			= Dispatcher::Instance()->Encrypt($dataList[$i]['id']);
			$url_cetak	= Dispatcher::Instance()->GetUrl('spp_cetak','CetakSpp','view','html&id='.$dataList[$i]['id'].'&ta='.$data['ta_active'].'&unit='.$data['unit_id']);
			$dataList[$i]['cetak_spp']	= $url_cetak;
			$this->mrTemplate->AddVars('data_item',$dataList[$i],'_');
			$this->mrTemplate->parseTemplate('data_item','a');
		}
	}
}
}
?>