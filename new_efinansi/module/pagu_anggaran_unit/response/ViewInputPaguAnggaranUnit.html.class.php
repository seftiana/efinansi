<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
        'module/pagu_anggaran_unit/business/PaguAnggaranUnit.class.php';
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
        'module/user_unit_kerja/business/UserUnitKerja.class.php';

class ViewInputPaguAnggaranUnit extends HtmlResponse 
{
	var $Data;
	var $Pesan;
	var $Role;

	function TemplateModule() {
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot') .
                'module/pagu_anggaran_unit/template');
		$this->SetTemplateFile('input_pagu_anggaran_unit.html');
	}
	
	function ProcessRequest() {
		$_POST = $_POST->AsArray();
		$idDec = Dispatcher::Instance()->Decrypt($_REQUEST['dataId']);
		//$pagu_anggaran_unitObj = new PaguAnggaranUnit();
		$this->pagu_anggaran_unitObj = new PaguAnggaranUnit();
		$msg = Messenger::Instance()->Receive(__FILE__);
		$this->Pesan = $msg[0][1];
		
		
		$userUnitKerja = new UserUnitKerja();
		$userId = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
		$this->Role = $userUnitKerja->GetRoleUser($userId);
        $unit = $userUnitKerja->GetUnitKerjaUser($userId);
        $unit_parent = $userUnitKerja->GetUnitKerja($unit['unit_kerja_parent_id']);

		if($_REQUEST['dataId']=='') {
		  /**
			if($this->Role['role_name'] == "Administrator") {
		 */
                $this->Data = $_POST;
				//tahun anggaran dari $_POST dan $_GET, unitkerja dan sub-unit dari $_POST dan $_GET
				$tahun_anggaran = $this->pagu_anggaran_unitObj->GetTahunAnggaranAktif();
				$this->Data['tahun_anggaran'] = $tahun_anggaran['id'];
				$this->Data['tahun_anggaran_label'] = $tahun_anggaran['name'];
                $this->Data['unitkerja'] = $unit['unit_kerja_id'];
				$this->Data['unitkerja_label'] = $unit['unit_kerja_nama'];
                $this->Data['satker'] = (($unit['unit_kerja_parent_id'] == 0) ? 
                                        $unit['unit_kerja_id'] : $unit['unit_kerja_parent_id']);
                $this->Data['satker_label'] = (empty($unit_parent['unit_kerja_nama']) ? 
                                        $unit['unit_kerja_nama'] :  $unit_parent['unit_kerja_nama']);
				$arr_tahun_anggaran = $this->pagu_anggaran_unitObj->GetComboTahunAnggaran();
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
			} 
            elseif($this->Role['role_name'] == "OperatorUnit") {
				$this->Data = $_POST;
				//tahun anggaran dan unitkerja dari database, subunit dari $_POST dan $_GET
				$tahun_anggaran = $this->pagu_anggaran_unitObj->GetTahunAnggaranAktif();
				$this->Data['tahun_anggaran'] = $tahun_anggaran['id'];
				$this->Data['tahun_anggaran_label'] = $tahun_anggaran['name'];
				
				$satker = $userUnitKerja->GetSatkerUnitKerjaUser($userId);
				$this->Data['satker'] = $satker['satker_id'];
				$this->Data['satker_label'] = $satker['satker_nama'];
				$this->Data['unitkerja'] = $satker['unit_kerja_id'];
				$this->Data['unitkerja_label'] = $satker['unit_kerja_nama'];
			} elseif($this->Role['role_name'] == "OperatorSubUnit") {
				$this->Data = $_POST;
				//tahun anggaran, unitkerja, dan subunit dari database
				$tahun_anggaran = $this->pagu_anggaran_unitObj->GetTahunAnggaranAktif();
				$this->Data['tahun_anggaran'] = $tahun_anggaran['id'];
				$this->Data['tahun_anggaran_label'] = $tahun_anggaran['name'];
				$satker = $userUnitKerja->GetSatkerUnitKerjaUser($userId);
				$this->Data['satker'] = $satker['satker_id'];
				$this->Data['satker_label'] = $satker['satker_nama'];
				$this->Data['unitkerja'] = $satker['unit_kerja_id'];
				$this->Data['unitkerja_label'] = $satker['unit_kerja_nama'];
			}
            */
		} else {
			//edit data
			$this->Data= $this->pagu_anggaran_unitObj->GetDataPaguAnggaranUnitById($idDec);
		}
		if(isset($msg[0][0])):
		$this->Data = $msg[0][0];
		$this->Data['bas_id'] = $this->Data['bas'];
		$this->Data['nominal'] = $this->Data['nominal_pagu'];
		endif;
		 /* foreach($this->Data AS $key=>$val):
			echo $key." - ".$val."<br />";
		endforeach;  */
		$arr_bas = $this->pagu_anggaran_unitObj->GetComboBas();
		Messenger::Instance()->SendToComponent(
                                            'combobox', 
                                            'Combobox', 
                                            'view', 
                                            'html', 
                                            'bas', 
                                            array(
                                                    'bas', 
                                                    $arr_bas, 
                                                    $this->Data['bas_id'], '-', 
                                                    ' style="width:200px;" id="bas"'), 
                                            Messenger::CurrentRequest);
				
		//print_r($this->Data);
		/*
		if(empty($_POST))
			$this->Data= $this->pagu_anggaran_unitObj->GetDataPaguAnggaranUnitById($idDec);
		*/
		$return['decDataId'] = $idDec;
        $return['total_sub_unit'] = $userUnitKerja->GetTotalSubUnitKerja($unit['unit_kerja_id']);
		//$return['dataPaguAnggaranUnit'] = $dataPaguAnggaranUnit;
		return $return;
	}

	function ParseTemplate($data = NULL) 
    {
		if ($this->Pesan) {
			$this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
			$this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->Pesan);
		}
		$dataPaguAnggaranUnit = $data['dataPaguAnggaranUnit'];
		
		$this->mrTemplate->AddVar('content', 'JUDUL', $tambah);
		if($_REQUEST['dataId']=='') {
			//add data
            /**
			if($this->Role['role_name'] == "Administrator") {
				//PAK ADMIN, tahun_anggaran:combo, unitkerja-subunitkerja:textbox
				$this->mrTemplate->AddVar('role', 'WHOAMI', 'ADMINISTRATOR');
            
				$this->mrTemplate->AddVar('role', 'URL_POPUP_SATKER', 
                Dispatcher::Instance()->GetUrl('pagu_anggaran_unit', 'popupSatker', 'view', 'html'));
                */
                 if($data['total_sub_unit'] > 0){
                    $this->mrTemplate->AddVar('role', 'WHOAMI', 'ALL_INPUT');
                    $this->mrTemplate->AddVar('role', 'URL_POPUP_UNITKERJA', 
                                    Dispatcher::Instance()->GetUrl(
                                                            'pagu_anggaran_unit', 
                                                            'popupUnitkerja', 
                                                            'view', 
                                                            'html'));
                 } else {
                    $this->mrTemplate->AddVar('role', 'WHOAMI', 'IS_SUB_UNIT');
                 }
            /**                                                
			} elseif($this->Role['role_name'] == "OperatorUnit") {
				//OP UNIT, tahun_anggaran:label, unitkerja:label, subunitkerja:textbox
				$this->mrTemplate->AddVar('role', 'WHOAMI', 'OPERATORUNIT');
				$this->mrTemplate->AddVar('role', 'TAHUN_ANGGARAN', $this->Data['tahun_anggaran']);
				$this->mrTemplate->AddVar('role', 'TAHUN_ANGGARAN_LABEL', $this->Data['tahun_anggaran_label']);
				$this->mrTemplate->AddVar('role', 'URL_POPUP_SATKER', 
                    Dispatcher::Instance()->GetUrl('pagu_anggaran_unit', 'popupSatker', 'view', 'html'));
				$this->mrTemplate->AddVar('role', 'URL_POPUP_UNITKERJA', 
                    Dispatcher::Instance()->GetUrl('pagu_anggaran_unit', 'popupUnitkerja', 'view', 'html'));
			} elseif($this->Role['role_name'] == "OperatorSubUnit") {
				//echo "asdf";
				//print_r($this->Data);
				//OP SUB UNIT, tahun_anggaran:label, unitkerja:label, subunitkerja:label
				$this->mrTemplate->AddVar('role', 'WHOAMI', 'OPERATORSUBUNIT');
				$this->mrTemplate->AddVar('role', 'TAHUN_ANGGARAN', $this->Data['tahun_anggaran']);
				$this->mrTemplate->AddVar('role', 'TAHUN_ANGGARAN_LABEL', $this->Data['tahun_anggaran_label']);
			}
            */
             
		} else {
			$this->mrTemplate->AddVar('role', 'WHOAMI', 'ALL_EDIT');
			$this->mrTemplate->AddVar('role', 'TAHUN_ANGGARAN', $this->Data['tahun_anggaran']);
			$this->mrTemplate->AddVar('role', 'TAHUN_ANGGARAN_LABEL', $this->Data['tahun_anggaran_label']);
		}
		// echo 'Satker '.$this->Data['satker_label'];
		$this->mrTemplate->AddVar('role', 'SATKER', $this->Data['satker']);
        $this->mrTemplate->AddVar('role', 'SATKER_LABEL', $this->Data['satker_label']);
	
		$this->mrTemplate->AddVar('role', 'UNITKERJA', $this->Data['unitkerja']);
		$this->mrTemplate->AddVar('role', 'UNITKERJA_LABEL', $this->Data['unitkerja_label']);
		
		$this->mrTemplate->AddVar('content', 'NOMINAL',$this->Data['nominal']);
		$this->mrTemplate->AddVar('content', 'NOMINAL_TERSEDIA',$this->Data['nominal_tersedia']);
		$this->mrTemplate->AddVar('content', 'SUMBER_DANA', $this->Data['sumber_dana']);
		$this->mrTemplate->AddVar('content', 'SUMBER_DANA_LABEL', $this->Data['sumber_dana_label']);
        $this->mrTemplate->AddVar('content', 'BAS_ID', $this->Data['bas_id']);
        $this->mrTemplate->AddVar('content', 'BAS_NAMA', $this->Data['bas_nama']);
		$this->mrTemplate->AddVar('content', 'URL_POPUP_PROGRAM', 
                                        Dispatcher::Instance()->GetUrl(
                                                                'pagu_anggaran_unit', 
                                                                'popupProgram', 
                                                                'view', 
                                                                'html'));
                                                                
       $this->mrTemplate->AddVar('content', 'URL_POPUP_SUMBER_DANA', 
                                        Dispatcher::Instance()->GetUrl(
                                                                'pagu_anggaran_unit', 
                                                                'popupSumberDana', 
                                                                'view', 
                                                                'html'));
       
       $this->mrTemplate->AddVar('content', 'URL_POPUP_PAGU_BAS', 
                                        Dispatcher::Instance()->GetUrl(
                                                                'pagu_anggaran_unit', 
                                                                'popupPaguBas', 
                                                                'view', 
                                                                'html'));
      
				
		if ($_REQUEST['dataId']=='') {
			$url="addPaguAnggaranUnit";
			$tambah="Tambah";
		} else {
			$url="updatePaguAnggaranUnit";
			$tambah="Ubah";
            $this->mrTemplate->AddVar('content', 'PAGU_ID', $data['decDataId']);
		}

		$this->mrTemplate->AddVar('content', 'URL_ACTION', 
                                    Dispatcher::Instance()->GetUrl(
                                                                'pagu_anggaran_unit', 
                                                                $url, 
                                                                'do', 
                                                                'html') . 
                                                                "&dataId=" . 
                                    Dispatcher::Instance()->Encrypt($data['decDataId']));
		$this->mrTemplate->AddVar('content', 'JUDUL', $tambah);
		$this->mrTemplate->AddVar('content', 'DATAID', Dispatcher::Instance()->Decrypt($_GET['dataId']));
		$this->mrTemplate->AddVar('content', 'PAGE', Dispatcher::Instance()->Decrypt($_GET['page']));
	}
}