<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
    'module/pagu_anggaran_unit/business/PaguAnggaranUnit.class.php';
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
    'module/user_unit_kerja/business/UserUnitKerja.class.php';

class ViewCopyPaguAnggaranUnit extends HtmlResponse {
	var $Data;
	var $Pesan;
	var $Role;
	
	function TemplateModule() {
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot') .  
            'module/pagu_anggaran_unit/template');
		$this->SetTemplateFile('copy_pagu_anggaran_unit.html');
	}
	
	function ProcessRequest() {
		$this->pagu_anggaran_unitObj = new PaguAnggaranUnit();
		$msg = Messenger::Instance()->Receive(__FILE__);
		$this->Pesan = $msg[0][1];
		$this->Data = $msg[0][0];

		$userUnitKerja = new UserUnitKerja();
		$userId = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
		$this->Role = $userUnitKerja->GetRoleUser($userId);
        $unit = $userUnitKerja->GetUnitKerjaUser($userId);
        $unit_parent = $userUnitKerja->GetUnitKerja($unit['unit_kerja_parent_id']);
                
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
                                            'tahun_anggaran_asal', 
                                            array(
                                                'tahun_anggaran_asal', 
                                                $arr_tahun_anggaran, 
                                                $this->Data['tahun_anggaran_asal'], '-', 
                                                ' style="width:200px;" id="tahun_anggaran"'), 
                                            Messenger::CurrentRequest);
                                            
		Messenger::Instance()->SendToComponent(
                                            'combobox', 
                                            'Combobox', 
                                            'view', 
                                            'html', 
                                            'tahun_anggaran_tujuan', 
                                            array(
                                                    'tahun_anggaran_tujuan', 
                                                    $arr_tahun_anggaran, 
                                                    $this->Data['tahun_anggaran_tujuan'], '-', 
                                                    ' style="width:200px;" id="tahun_anggaran"'), 
                                            Messenger::CurrentRequest);
		  /**	
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
        */
        $list_status = array( 
                            array('id'=>'Naik','name'=>'Naik'),
                            array('id'=>'Turun','name'=>'Turun')
                            );
         Messenger::Instance()->SendToComponent(
                                            'combobox', 
                                            'Combobox', 
                                            'view', 
                                            'html', 
                                            'perubahan_pagu', 
                                            array(
                                                    'perubahan_pagu', 
                                                    $list_status,'', '', 
                                                    'style="width:70px;"'), 
                                            Messenger::CurrentRequest);
         
	    $return['total_sub_unit'] = $userUnitKerja->GetTotalSubUnitKerja($unit['unit_kerja_id']);
		return $return;
	}

	function ParseTemplate($data = NULL) 
    {
		if ($this->Pesan) {
			$this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
			$this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->Pesan);
		}
		
         if($data['total_sub_unit'] > 0){
            $this->mrTemplate->AddVar('role', 'IS_SUB_UNIT', 'NO');
         
        /**
		$this->mrTemplate->AddVar('content', 'URL_POPUP_SATKER', 
                                    Dispatcher::Instance()->GetUrl(
                                                        'pagu_anggaran_unit', 
                                                        'popupSatker', 
                                                        'view', 
                                                        'html'));
        */
                                                        
		          $this->mrTemplate->AddVar('role', 'URL_POPUP_UNITKERJA', 
                                    Dispatcher::Instance()->GetUrl(
                                                        'pagu_anggaran_unit', 
                                                        'popupUnitkerja', 
                                                        'view', 
                                                        'html'));
         } else {
            $this->mrTemplate->AddVar('role', 'IS_SUB_UNIT', 'YES');
         }
         $this->mrTemplate->AddVar('role', 'UNITKERJA_LABEL', $this->Data['unitkerja_label']);				
		 $this->mrTemplate->AddVar('content', 'SATKER', $this->Data['satker']);
		 $this->mrTemplate->AddVar('content', 'SATKER_LABEL', $this->Data['satker_label']);
		 $this->mrTemplate->AddVar('content', 'UNITKERJA', $this->Data['unitkerja']);
		
				
		$url="copyPaguAnggaranUnit";
		$tambah="Salin";
	
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
