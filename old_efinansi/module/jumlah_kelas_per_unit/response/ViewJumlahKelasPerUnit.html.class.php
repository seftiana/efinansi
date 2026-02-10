<?php 

require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
        'module/jumlah_kelas_per_unit/business/JumlahKelasPerUnit.class.php';
        
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
        'module/user_unit_kerja/business/UserUnitKerja.class.php';

class ViewJumlahKelasPerUnit extends HtmlResponse 
{

	var $Pesan;
	var $Data;
	var $Search;
	var $mObj;

	public function TemplateModule() 
	{
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
                'module/jumlah_kelas_per_unit/template');
		$this->SetTemplateFile('view_jumlah_kelas_per_unit.html');
	}

	public function ProcessRequest() 
	{
		$_POST = $_POST->AsArray();
		
		$this->mObj = new JumlahKelasPerUnit;
		$userUnitKerja = new UserUnitKerja();
		$userId = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
		$role = $userUnitKerja->GetRoleUser($userId);
        $unit = $userUnitKerja->GetUnitKerjaUser($userId);
		
        	if(isset($_POST['btnTampilkan'])) {
				$this->Data = $_POST;
			} elseif($_GET['cari'] != "") {
				$get = $_GET->AsArray();
				if(!empty($get)){
					foreach($get as $arr => $value) {
						$this->Data[$arr] = Dispatcher::Instance()->Decrypt($value);
					}
				}
			} else {
				$tahun_anggaran = $this->mObj->GetTahunAnggaranAktif();
				//print_r($tahun_anggaran);
				$this->Data = $_POST;
				$this->Data['tahun_anggaran'] = $tahun_anggaran['id'];
				$this->Data['tahun_anggaran_label'] = $tahun_anggaran['name'];
                $this->Data['unitkerja'] = $unit['unit_kerja_id'];
			    $this->Data['unitkerja_label'] = $unit['unit_kerja_nama'];
			}

			$arr_tahun_anggaran = $this->mObj->GetComboTahunAnggaran();
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
      
		
		$itemViewed = 20;
		$currPage = 1;
		$startRec = 0 ;
		if(isset($_GET['page'])) {
			$currPage = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
			$startRec =($currPage-1) * $itemViewed;
		}

		$dataJumlahKelasPerUnit = $this->mObj->GetDataJumlahKelasPerUnit(
                                                                $this->Data['tahun_anggaran'], 
                                                                $this->Data['unit_kerja_id'],
                                                                $startRec, 
                                                                $itemViewed);
		//$t = 
		
		$totalData = $this->mObj->GetCountDataJumlahKelasPerUnit();

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

		$return['dataJumlahKelasPerUnit'] = $dataJumlahKelasPerUnit;
		$return['start'] = $startRec+1;
        $return['total_sub_unit'] = $userUnitKerja->GetTotalSubUnitKerja($unit['unit_kerja_id']);

		$return['search']['pagu_anggaran_unit'] = $paguanggaranunit;
		$return['search']['tahun_anggaran'] = $tahun_anggaran;
		$return['search']['arr_tahun_anggaran'] = $arr_tahun_anggaran;
		return $return;
	}
	
	public function ParseTemplate($data = NULL) 
    {
		$search = $data['search'];
		
        		
		if($data['total_sub_unit'] > 0){
				$this->mrTemplate->AddVar('cek_unitkerja_parent', 'IS_PARENT','YES');
		} else {
				$this->mrTemplate->AddVar('cek_unitkerja_parent', 'IS_PARENT', 'NO');
		}	
		
		$this->mrTemplate->AddVar('content', 'UNITKERJA', $this->Data['unitkerja']);
		$this->mrTemplate->AddVar('cek_unitkerja_parent', 'UNITKERJA_LABEL',$this->Data['unitkerja_label']);
		$this->mrTemplate->AddVar('cek_unitkerja_parent', 'URL_POPUP_UNITKERJA', 
                                    Dispatcher::Instance()->GetUrl(
                                                                'jumlah_kelas_per_unit', 
                                                                'popupUnitKerja', 
                                                                'view', 
                                                                'html'));
        
        
        
		$this->mrTemplate->AddVar('content', 'PROGRAM', $this->Data['program']);
		$this->mrTemplate->AddVar('content', 'PROGRAM_LABEL', $this->Data['program_label']);
		$this->mrTemplate->AddVar('content', 'LATARBELAKANG', $this->Data['latarbelakang']);

		$this->mrTemplate->AddVar('content', 'URL_SEARCH', 
                                    Dispatcher::Instance()->GetUrl(
                                                            'jumlah_kelas_per_unit', 
                                                            'JumlahKelasPerUnit', 
                                                            'view', 
                                                            'html'));
 	   $this->mrTemplate->AddVar('content', 'URL_RESET', 
                                    Dispatcher::Instance()->GetUrl(
                                                            'jumlah_kelas_per_unit', 
                                                            'JumlahKelasPerUnit', 
                                                            'view', 
                                                            'html'));
                                                            
		$this->mrTemplate->AddVar('content', 'URL_ADD', 
                                    Dispatcher::Instance()->GetUrl(
                                                            'jumlah_kelas_per_unit', 
                                                            'inputJumlahKelasPerUnit', 
                                                            'view', 
                                                            'html'));
     

		if($this->Pesan) {
			$this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
			$this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->Pesan);
			$this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $this->css);
		}
        
        $dataJumlahKelasPerUnit = $data['dataJumlahKelasPerUnit'];
		
		if (empty($dataJumlahKelasPerUnit)) {
			$this->mrTemplate->AddVar('data_jumlah_kelas_per_unit', 'DATA_EMPTY', 'YES');
		} else {
			$decPage = Dispatcher::Instance()->Decrypt($_REQUEST['page']);
			$encPage = Dispatcher::Instance()->Encrypt($decPage);
			$this->mrTemplate->AddVar('data_jumlah_kelas_per_unit', 'DATA_EMPTY', 'NO');
			

			$label = GTFWConfiguration::GetValue('language', 'jumlah_kelas_unit');
			$urlDelete = Dispatcher::Instance()->GetUrl(
                                                'jumlah_kelas_per_unit', 
                                                'deleteJumlahKelasPerUnit', 
                                                'do', 
                                                'html');
                                                
			$urlReturn = Dispatcher::Instance()->GetUrl(
                                                'jumlah_kelas_per_unit', 
                                                'JumlahKelasPerUnit', 
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
                                                                   
			for ($i=0; $i<sizeof($dataJumlahKelasPerUnit); $i++) {
				$no = $i + $data['start'];
                if($i == 0) $this->mrTemplate->AddVar('content', 'FIRST_NUMBER', $no);              
                if($i == sizeof($dataJumlahKelasPerUnit)-1) 
                            $this->mrTemplate->AddVar('content', 'LAST_NUMBER', $no);
                
                $idEnc = Dispatcher::Instance()->Encrypt($dataJumlahKelasPerUnit[$i]['kelas_id']);

                $dataJumlahKelasPerUnit[$i]['url_edit'] =    
                                    Dispatcher::Instance()->GetUrl(
                                                            'jumlah_kelas_per_unit', 
                                                            'inputJumlahKelasPerUnit', 
                                                            'view', 
                                                            'html') . 
                                                            '&dataId=' . $idEnc . 
                                                            '&page=' . $encPage;
                                                            
				$dataJumlahKelasPerUnit[$i]['number'] = $no;
				$this->mrTemplate->AddVars('data_jumlah_kelas_per_unit_item', $dataJumlahKelasPerUnit[$i], 'DATA_');
				$this->mrTemplate->parseTemplate('data_jumlah_kelas_per_unit_item', 'a');	 
			}
            
		}
	}
}

?>