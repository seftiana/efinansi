<?php 

require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
        'module/pagu_anggaran_unit/business/PaguAnggaranUnit.class.php';
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
        'module/user_unit_kerja/business/UserUnitKerja.class.php';

class ViewPaguAnggaranUnit extends HtmlResponse 
{

	var $Pesan;
	var $Data;
	var $Search;
	var $paguanggaranunitObj;

	function TemplateModule() {
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
                'module/pagu_anggaran_unit/template');
		$this->SetTemplateFile('view_pagu_anggaran_unit.html');
	}

	function ProcessRequest() {
		$_POST = $_POST->AsArray();
		
		$this->paguanggaranunitObj = new PaguAnggaranUnit();
		$userUnitKerja = new UserUnitKerja();
		$userId = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
		$role = $userUnitKerja->GetRoleUser($userId);
        $unit = $userUnitKerja->GetUnitKerjaUser($userId);
		//if($role['role_name'] == "Administrator") {
			//tahun anggaran dari $_POST dan $_GET, unitkerja dan sub-unit dari $_POST dan $_GET
			if($_POST['btnTampilkan']) {
				$this->Data = $_POST;
			} elseif($_GET['cari'] != "") {
				$get = $_GET->AsArray();
				if(!empty($get)){
					foreach($get as $arr => $value) {
						$this->Data[$arr] = Dispatcher::Instance()->Decrypt($value);
					}
				}
			} else {
				$tahun_anggaran = $this->paguanggaranunitObj->GetTahunAnggaranAktif();
				//print_r($tahun_anggaran);
				$this->Data = $_POST;
				$this->Data['tahun_anggaran'] = $tahun_anggaran['id'];
				$this->Data['tahun_anggaran_label'] = $tahun_anggaran['name'];
                $this->Data['unitkerja'] = $unit['unit_kerja_id'];
			    $this->Data['unitkerja_label'] = $unit['unit_kerja_nama'];
			}
			$arr_tahun_anggaran = $this->paguanggaranunitObj->GetComboTahunAnggaran();
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
            /**
		} elseif($role['role_name'] == "OperatorUnit") {
			//tahun anggaran dan unitkerja dari database, subunit dari $_POST dan $_GET
			if(isset($_POST['btnTampilkan'])) {
				$this->Data = $_POST;
			} elseif(isset($_GET['cari'])) {
				$get = $_GET->AsArray();
				if(!empty($get)){
					foreach($get as $arr => $value) {
						$this->Data[$arr] = Dispatcher::Instance()->Decrypt($value);
					}
				}
			} else {
				$this->Data = $_POST;
			}
			$tahun_anggaran = $this->paguanggaranunitObj->GetTahunAnggaranAktif();
			$this->Data['tahun_anggaran'] = $tahun_anggaran['id'];
			$this->Data['tahun_anggaran_label'] = $tahun_anggaran['name'];
			$satker = $userUnitKerja->GetSatkerUnitKerjaUser($userId);
			//print_r($satker);
			$this->Data['satker'] = $satker['satker_id'];
			$this->Data['satker_label'] = $satker['satker_nama'];
		} else {
			//tahun anggaran, unitkerja, dan subunit dari database
			if(isset($_POST['btnTampilkan'])) {
				$this->Data = $_POST;
			} elseif(isset($_GET['cari'])) {
				$get=$_GET->AsArray();
				if(!empty($get)) {
					foreach($get as $arr => $value) {
						$this->Data[$arr] = Dispatcher::Instance()->Decrypt($value);
					}
				}
			} else {
				$this->Data = $_POST;
			}
			$tahun_anggaran = $this->paguanggaranunitObj->GetTahunAnggaranAktif();
			$this->Data['tahun_anggaran'] = $tahun_anggaran['id'];
			$this->Data['tahun_anggaran_label'] = $tahun_anggaran['name'];
			$satker = $userUnitKerja->GetSatkerUnitKerjaUser($userId);
			$this->Data['satker'] = $satker['satker_id'];
			$this->Data['satker_label'] = $satker['satker_nama'];
			$this->Data['unitkerja'] = $satker['unit_kerja_id'];
			$this->Data['unitkerja_label'] = $satker['unit_kerja_nama'];
		}
        */
		$totalData = $this->paguanggaranunitObj->GetCountDataPaguAnggaranUnit(
                                        $this->Data['tahun_anggaran'], 
                                        $this->Data['satker'],
                                        $this->Data['unitkerja']);
		$itemViewed = 20;
		$currPage = 1;
		$startRec = 0 ;
		if(isset($_GET['page'])) {
			$currPage = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
			$startRec =($currPage-1) * $itemViewed;
		}
		$dataPaguAnggaranUnit = $this->paguanggaranunitObj->GetDataPaguAnggaranUnit(
                                                                $startRec, 
                                                                $itemViewed, 
                                                                $this->Data['tahun_anggaran'], 
                                                                $this->Data['satker'], 
                                                                $this->Data['unitkerja']);
                                                                
		$url = Dispatcher::Instance()->GetUrl(    
                                            Dispatcher::Instance()->mModule, 
                                            Dispatcher::Instance()->mSubModule, 
                                            Dispatcher::Instance()->mAction, 
                                            Dispatcher::Instance()->mType . 
                                            '&tahun_anggaran=' . 
                                            Dispatcher::Instance()->Encrypt($this->Data['tahun_anggaran']) . 
                                            '&satker=' . 
                                            Dispatcher::Instance()->Encrypt($this->Data['satker']) . 
                                            '&satker_label=' . 
                                            Dispatcher::Instance()->Encrypt($this->Data['satker_label']) . 
                                            '&unitkerja=' . 
                                            Dispatcher::Instance()->Encrypt($this->Data['unitkerja']) . 
                                            '&unitkerja_label=' . 
                                            Dispatcher::Instance()->Encrypt($this->Data['unitkerja_label']) . 
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

		$return['role_name'] = $role['role_name'];

		$msg = Messenger::Instance()->Receive(__FILE__);
		$this->Pesan = $msg[0][1];
		$this->css = $msg[0][2];

		$return['dataPaguAnggaranUnit'] = $dataPaguAnggaranUnit;
		$return['start'] = $startRec+1;
        $return['total_sub_unit'] = $userUnitKerja->GetTotalSubUnitKerja($unit['unit_kerja_id']);

		$return['search']['pagu_anggaran_unit'] = $paguanggaranunit;
		$return['search']['tahun_anggaran'] = $tahun_anggaran;
		$return['search']['arr_tahun_anggaran'] = $arr_tahun_anggaran;
		return $return;
	}
	
	function ParseTemplate($data = NULL) 
    {
		$search = $data['search'];
		/**
          if($data['role_name'] == "Administrator") {
         
			//PAK ADMIN, tahun_anggaran:combo, unitkerja-subunitkerja:textbox
			$this->mrTemplate->AddVar('role', 'WHOAMI', 'ADMINISTRATOR');
			$this->mrTemplate->AddVar('role', 'URL_POPUP_SATKER', 
                                    Dispatcher::Instance()->GetUrl(
                                                                'pagu_anggaran_unit', 
                                                                'popupSatker', 
                                                                'view', 
                                                                'html'));
            */
        		
		if($data['total_sub_unit'] > 0){
				$this->mrTemplate->AddVar('cek_unitkerja_parent', 'IS_PARENT','YES');
		} else {
				$this->mrTemplate->AddVar('cek_unitkerja_parent', 'IS_PARENT', 'NO');
		}	
		
		$this->mrTemplate->AddVar('content', 'UNITKERJA', $this->Data['unitkerja']);
		$this->mrTemplate->AddVar('cek_unitkerja_parent', 'UNITKERJA_LABEL',$this->Data['unitkerja_label']);
		$this->mrTemplate->AddVar('cek_unitkerja_parent', 'URL_POPUP_UNITKERJA', 
                                    Dispatcher::Instance()->GetUrl(
                                                                'pagu_anggaran_unit', 
                                                                'popupUnitkerja', 
                                                                'view', 
                                                                'html'));
        /**                                                        
		} elseif($data['role_name'] == "OperatorUnit") {
			//OP UNIT, tahun_anggaran:label, unitkerja:label, subunitkerja:textbox
			$this->mrTemplate->AddVar('role', 'WHOAMI', 'OPERATORUNIT');
			$this->mrTemplate->AddVar('role', 'TAHUN_ANGGARAN', $this->Data['tahun_anggaran']);
			$this->mrTemplate->AddVar('role', 'TAHUN_ANGGARAN_LABEL', $this->Data['tahun_anggaran_label']);
			$this->mrTemplate->AddVar('role', 'URL_POPUP_SATKER', 
                        Dispatcher::Instance()->GetUrl('pagu_anggaran_unit', 'popupSatker', 'view', 'html'));
			$this->mrTemplate->AddVar('role', 'URL_POPUP_UNITKERJA', 
                        Dispatcher::Instance()->GetUrl('pagu_anggaran_unit', 'popupUnitkerja', 'view', 'html'));
		} else {
			//OP SUB UNIT, tahun_anggaran:label, unitkerja:label, subunitkerja:label
			$this->mrTemplate->AddVar('role', 'WHOAMI', 'OPERATORSUBUNIT');
			$this->mrTemplate->AddVar('role', 'TAHUN_ANGGARAN', $this->Data['tahun_anggaran']);
			$this->mrTemplate->AddVar('role', 'TAHUN_ANGGARAN_LABEL', $this->Data['tahun_anggaran_label']);
		}
        */
        /**
		$this->mrTemplate->AddVar('role', 'SATKER', $this->Data['satker']);
		$this->mrTemplate->AddVar('role', 'SATKER_LABEL', $this->Data['satker_label']);
        */
        
        
		$this->mrTemplate->AddVar('content', 'PROGRAM', $this->Data['program']);
		$this->mrTemplate->AddVar('content', 'PROGRAM_LABEL', $this->Data['program_label']);
		$this->mrTemplate->AddVar('content', 'LATARBELAKANG', $this->Data['latarbelakang']);

		$this->mrTemplate->AddVar('content', 'URL_SEARCH', 
                                    Dispatcher::Instance()->GetUrl(
                                                            'pagu_anggaran_unit', 
                                                            'paguAnggaranUnit', 
                                                            'view', 
                                                            'html'));
 	   $this->mrTemplate->AddVar('content', 'URL_RESET', 
                                    Dispatcher::Instance()->GetUrl(
                                                            'pagu_anggaran_unit', 
                                                            'paguAnggaranUnit', 
                                                            'view', 
                                                            'html'));
                                                            
		$this->mrTemplate->AddVar('content', 'URL_ADD', 
                                    Dispatcher::Instance()->GetUrl(
                                                            'pagu_anggaran_unit', 
                                                            'inputPaguAnggaranUnit', 
                                                            'view', 
                                                            'html'));
                                                            
		$this->mrTemplate->AddVar('content', 'URL_COPY', 
                                    Dispatcher::Instance()->GetUrl(
                                                            'pagu_anggaran_unit', 
                                                            'copyPaguAnggaranUnit', 
                                                            'view', 
                                                            'html'));
                                                            
      $this->mrTemplate->AddVar('content', 'URL_POPUP_PROGRAM', 
                                    Dispatcher::Instance()->GetUrl(
                                                            'pagu_anggaran_unit', 
                                                            'popupProgram', 
                                                            'view', 
                                                            'html'));

		if($this->Pesan) {
			$this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
			$this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->Pesan);
			$this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $this->css);
		}

		if (empty($data['dataPaguAnggaranUnit'])) {
			$this->mrTemplate->AddVar('data_paguanggaranunit', 'PAU_EMPTY', 'YES');
		} else {
			$decPage = Dispatcher::Instance()->Decrypt($_REQUEST['page']);
			$encPage = Dispatcher::Instance()->Encrypt($decPage);
			$this->mrTemplate->AddVar('data_paguanggaranunit', 'PAU_EMPTY', 'NO');
			$dataPaguAnggaranUnit = $data['dataPaguAnggaranUnit'];

			$label = "Pagu Anggaran Unit";
			$urlDelete = Dispatcher::Instance()->GetUrl(
                                                'pagu_anggaran_unit', 
                                                'deletePaguAnggaranUnit', 
                                                'do', 
                                                'html');
                                                
			$urlReturn = Dispatcher::Instance()->GetUrl(
                                                'pagu_anggaran_unit', 
                                                'paguAnggaranUnit', 
                                                'view', 
                                                'html');
                                                
			Messenger::Instance()->Send(
                                        'confirm', 
                                        'confirmDelete', 
                                        'do', 
                                        'html', 
                                        array(
                                                $label, 
                                                $urlDelete, 
                                                $urlReturn),
                                        Messenger::NextRequest);
                                        
			$this->mrTemplate->AddVar('content', 'URL_DELETE', 
                                        Dispatcher::Instance()->GetUrl(
                                                                    'confirm', 
                                                                    'confirmDelete', 
                                                                    'do', 
                                                                    'html'));
                                                                    
			for ($i=0; $i<sizeof($dataPaguAnggaranUnit); $i++) {
				$no = $i+$data['start'];
				$dataPaguAnggaranUnit[$i]['number'] = $no;
                $dataPaguAnggaranUnit[$i]['unit_hidden']=$dataPaguAnggaranUnit[$i]['unit'];
				if ($no % 2 != 0) $dataPaguAnggaranUnit[$i]['class_name'] = 'table-common-even';
				else $dataPaguAnggaranUnit[$i]['class_name'] = '';
				
				if($i == 0) $this->mrTemplate->AddVar('content', 'FIRST_NUMBER', $no);				
				if($i == sizeof($dataPaguAnggaranUnit)-1) 
                            $this->mrTemplate->AddVar('content', 'LAST_NUMBER', $no);

				if($dataPaguAnggaranUnit[$i]['idsatker'] == $dataPaguAnggaranUnit[$i-1]['idsatker']) {
					$dataPaguAnggaranUnit[$i]['kodesatker']="";
					$dataPaguAnggaranUnit[$i]['SATKER']="";
				}

				if($dataPaguAnggaranUnit[$i]['idunit'] == $dataPaguAnggaranUnit[$i-1]['idunit']) {
					$dataPaguAnggaranUnit[$i]['kodeunit']="";
					$dataPaguAnggaranUnit[$i]['unit']="";
				} 
                /**
				if($dataPaguAnggaranUnit[$i]['idunit'] == '-') {
					$dataPaguAnggaranUnit[$i]['colspan']= ' colspan="3" ';
				} else {
					$dataPaguAnggaranUnit[$i]['colspan']= '';
				}
                */
				$idEnc = Dispatcher::Instance()->Encrypt($dataPaguAnggaranUnit[$i]['id']);

				$dataPaguAnggaranUnit[$i]['url_edit'] =    
                                    Dispatcher::Instance()->GetUrl(
                                                            'pagu_anggaran_unit', 
                                                            'inputPaguAnggaranUnit', 
                                                            'view', 
                                                            'html') . 
                                                            '&dataId=' . $idEnc . 
                                                            '&page=' . $encPage;
                                                            
				$dataPaguAnggaranUnit[$i]['nominal'] = 
                                    number_format($dataPaguAnggaranUnit[$i]['nominal'], 0, ',', '.');
				
				$dataPaguAnggaranUnit[$i]['nominal_tersedia'] = 
                                    number_format($dataPaguAnggaranUnit[$i]['nominal_tersedia'], 0, ',', '.');
                                    
				$dataPaguAnggaranUnit[$i]['bas_nama'] = 
                                    ucwords(strtolower($dataPaguAnggaranUnit[$i]['bas_nama']));
				
				$this->mrTemplate->AddVars('data_paguanggaranunit_item', $dataPaguAnggaranUnit[$i], 'PAU_');
				$this->mrTemplate->parseTemplate('data_paguanggaranunit_item', 'a');	 
			}
		}
	}
}