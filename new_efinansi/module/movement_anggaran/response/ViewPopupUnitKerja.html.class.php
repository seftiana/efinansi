<?php
/**
* Module : movement_anggaran
* FileInclude : MovementAnggaran.class.php
* Class : ViewPopupUnitKerja
* Extends : HtmlResponse
*/
    require_once GTFWConfiguration::GetValue('application','docroot').
    'module/movement_anggaran/business/AppPopupUnitKerja.class.php';
    
    require_once GTFWConfiguration::GetValue('application','docroot').
    'module/user_unit_kerja/business/UserUnitKerja.class.php';

    class ViewPopupUnitKerja extends HtmlResponse{
        
        public $unitObj;
        public $userUnitObj;
        public $data;
        public $POST;
        protected $userId;
        public $Pesan;
        public $css;
        
        function __construct()
        {
            $this->unitObj      = new AppPopupUnitKerja();
            $this->userUnitObj  = new UserUNitKerja();
            $this->POST         = $_POST->AsArray();
            $this->userId       = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
            
        }
        
        function TemplateModule(){
            $this->setTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
            'module/movement_anggaran/template/');
            $this->setTemplateFile('popup_unit_kerja.html');
        }
        
        function TemplateBase() {
            $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 
	      		'main/template/');
            $this->SetTemplateFile('document-common-popup.html');
            $this->SetTemplateFile('layout-common-popup.html');
        }
        
        function ProcessRequest(){
            $role 				= $this->userUnitObj->GetRoleUser($this->userId);
		    $unitkerjaUserId 	= $this->userUnitObj->GetUnitKerjaUser($this->userId);
		    
		    if($this->POST || isset($_GET['cari'])) {
			    if(isset($this->POST['unitkerja_kode'])) {
				    $kode 		= $this->POST['unitkerja_kode'];
			    } elseif(isset($_GET['kode'])) {
				    $kode		= Dispatcher::Instance()->Decrypt($_GET['kode']);
			    } else {
				    $kode 		= '';
			    }
		      //echo $kode;
			    if(isset($this->POST['unitkerja'])) {
				    $unitkerja 	= $this->POST['unitkerja'];
			    } elseif(isset($_GET['unitkerja'])) {
				    $unitkerja 	= Dispatcher::Instance()->Decrypt($_GET['unitkerja']);
			    } else {
				    $unitkerja 	= '';
			    }

			    if($this->POST['tipeunit'] != "all") {
				    $tipeunit 	= $this->POST['tipeunit'];
			    } elseif(isset($_GET['tipeunit'])) {
				    $tipeunit 	= Dispatcher::Instance()->Decrypt($_GET['tipeunit']);
			    } else {
				    $tipeunit 	= '';
			    }
		    }
		    $totalData 			= $this->unitObj->GetCountDataUnitkerja(
													$kode, 
													$unitkerja, 
													$tipeunit, 
													$unitkerjaUserId['unit_kerja_id']);
		    $itemViewed 		= 20;
		    $currPage 			= 1;
		    $startRec 			= 0 ;
		    if(isset($_GET['page'])) {
			    $currPage 		= (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
			    $startRec 		=($currPage-1) * $itemViewed;
		    }
		    
		    $this->data 		= $this->unitObj->GetDataUnitkerja(
													$startRec, 
													$itemViewed, 
													$kode, 
						 	      					$unitkerja, 
			                                        $tipeunit,
						                            $unitkerjaUserId['unit_kerja_id']);
						                            
			$url 				= Dispatcher::Instance()->GetUrl(
							      Dispatcher::Instance()->mModule, 
							      Dispatcher::Instance()->mSubModule, 
							      Dispatcher::Instance()->mAction, 
							      Dispatcher::Instance()->mType . 
							      '&kode=' . Dispatcher::Instance()->Encrypt($kode) . 
							      '&unitkerja=' . Dispatcher::Instance()->Encrypt($unitkerja) . 
							      '&tipeunit=' . Dispatcher::Instance()->Encrypt($tipeunit) . 
							      '&cari=' . Dispatcher::Instance()->Encrypt(1));
		    $dest 				= "popup-subcontent";
		    Messenger::Instance()->SendToComponent
				       ('paging', 'Paging', 'view', 'html', 'paging_top', 
				       array($itemViewed,$totalData, $url, $currPage, $dest), 
				       Messenger::CurrentRequest);
		    $arr_tipeunit 		= $this->unitObj->GetDataTipeunit();

		    Messenger::Instance()->SendToComponent
				       ('combobox', 'Combobox', 'view', 'html', 'tipeunit', 
				       array('tipeunit', $arr_tipeunit, $tipeunit, 
				       'true', ' style="width:200px;" '), Messenger::CurrentRequest);
		    $msg 				= Messenger::Instance()->Receive(__FILE__);

		    $this->Pesan 		= $msg[0][1];
		    $this->css 			= $msg[0][2];
		    
		    $return['start'] 				= $startRec+1;

		    $return['search']['kode'] 		= $kode;
		    $return['search']['unitkerja'] 	= $unitkerja;
		    $return['search']['tipeunit'] 	= $tipeunit;

		    return $return;
        }
        
        function ParseTemplate($data = null){
            $search 		= $data['search'];
		    $this->mrTemplate->AddVar('content', 'UNITKERJA_KODE', $search['kode']);
		    $this->mrTemplate->AddVar('content', 'UNITKERJA', $search['unitkerja']);
		    $this->mrTemplate->AddVar('content', 'TYPE', $_GET['type']);
		    $this->mrTemplate->AddVar('content', 'URL_SEARCH', 
								      Dispatcher::Instance()->
								      GetUrl('movement_anggaran', 'popupUnitKerja', 'view', 'html') . 
								      "&type=".$_GET['type'] .
								      "&satker=" . Dispatcher::Instance()->
								      Encrypt($search['satker']));
		    if($this->Pesan) {
			    $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
			    $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->Pesan);
			    $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $this->css);
		    }
		    
		    $dataList       = $this->data;
		    if (empty($dataList)) {
			    $this->mrTemplate->AddVar('data_unitkerja', 'UNITKERJA_EMPTY', 'YES');
		    } else {
			    //$decPage = Dispatcher::Instance()->Decrypt($_REQUEST['page']);
			    //$encPage = Dispatcher::Instance()->Encrypt($decPage);
			    $this->mrTemplate->AddVar('data_unitkerja', 'UNITKERJA_EMPTY', 'NO');
			
			    $dataUnitkerja 	= $dataList;

			    for ($i=0;$i<sizeof($dataUnitkerja);$i++) {
				    $dataUnitkerja[$i]['enc_unitkerja_id'] 		= Dispatcher::Instance()->
														      	  Encrypt($dataUnitkerja[$i]
														      	  ['unitkerja_id']);
				    $dataUnitkerja[$i]['enc_unitkerja_nama']	= Dispatcher::Instance()->
															      Encrypt($dataUnitkerja[$i]
															      ['unitkerja_nama']);
			    }
			    for ($i=0;$i<sizeof($dataUnitkerja);$i++) {
				    $no 							= $i+$data['start'];
				    $dataUnitkerja[$i]['number'] 	= $no;
				    //if($dataUnitkerja[$i]['parentId'] == 0) {
			    	if($this->unitObj->GetTotalSubUnitKerja($dataUnitkerja[$i]['id'])){
					    $dataUnitkerja[$i]['class_name'] 	= 'table-common-even1';
				    }
				    $dataUnitkerja[$i]['link']	= str_replace("'","\'",$dataUnitkerja[$i]['unit']);
				    $this->mrTemplate->AddVars('data_unitkerja_item',$dataUnitkerja[$i], 'UNITKERJA_');
				    $this->mrTemplate->parseTemplate('data_unitkerja_item', 'a');	 
			    }
		    }
        }
    }
?>
