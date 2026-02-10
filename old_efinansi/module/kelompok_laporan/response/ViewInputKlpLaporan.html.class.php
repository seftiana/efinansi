<?php

require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
    'module/kelompok_laporan/business/AppKlpLaporan.class.php';

class ViewInputKlpLaporan extends HtmlResponse {
	
	function TemplateModule() {
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot') . 
            'module/kelompok_laporan/template');
		$this->SetTemplateFile('input_klp_laporan.html');
	}
	
	function ProcessRequest() {
		$idDec = Dispatcher::Instance()->Decrypt($_REQUEST['dataId']);
		$Obj = new AppKelpLaporan();
		$msg = Messenger::Instance()->Receive(__FILE__);
		$this->Pesan = $msg[0][1];
		$this->Data = $msg[0][0];

		$data_klp_lap = $Obj->GetDataById($idDec);
		
		$idJenisLaporan = $data_klp_lap['0']['jenis_laporan_id'];
		$idBentukTransaksi=$data_klp_lap['0']['bentuk_transaksi_id'];
		$jenisLaporan = $Obj->GetJenisLaporan();
		Messenger::Instance()->SendToComponent('combobox', 'Combobox', 'view', 'html', 'jns_lap', 
	     array('jns_lap', $jenisLaporan, $idJenisLaporan, 'none', 'onChange="getBentukTransaksi(this.value)"'), 
		 Messenger::CurrentRequest);
		 
		 if(empty($idJenisLaporan))
		 	$idJenisLaporan=$jenisLaporan['0']['id'];
		 
		 $bentukTransaksi = $Obj->GetBentukTransaksi($idJenisLaporan);
		 if(empty($bentukTransaksi)){
		 	$disabledStatus="disabled";
		 	$bentukTransaksi['0']['name']="Tidak Ada Data";
		}
		  Messenger::Instance()->SendToComponent('combobox', 'Combobox', 'view', 'html', 'bentuk_transaksi', 
	     array('bentuk_transaksi', $bentukTransaksi, $idBentukTransaksi, 'none', $disabledStatus), 
		 Messenger::CurrentRequest);        
        $return['no_urutan'] = $Obj->GenerateNoUrutan($idJenisLaporan);        
		$return['decDataId'] = $idDec;
		$return['data_klp_lap'] = $data_klp_lap;
		return $return;
	}

	function ParseTemplate($data = NULL) {
		if ($this->Pesan) {
			$this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
			$this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->Pesan);
		}
		$klp_lap = $data['data_klp_lap'];
        
		$selected =' selected="selected"';
        
		if ($_REQUEST['dataId']=='') {
			$url="addKlpLaporan";
			$tambah="Tambah";
            $selected_ya = $selected;
            $selected_tidak ='';
            $no_urutan = empty($this->Data['no_urutan']) ? $data['no_urutan'] : $this->Data['no_urutan'];
		} else {
			$url="updateKlpLaporan";
			$tambah="Ubah";
            $no_urutan = empty($klp_lap[0]['no_urutan']) ? $data['no_urutan'] : $klp_lap[0]['no_urutan'];
            if($klp_lap[0]['is_tambah']=='Ya'){
                $selected_ya = $selected;
                $selected_tidak ='';
            } else {
                $selected_ya = '';
                $selected_tidak =$selected;
            }
		}
        
         
        $this->mrTemplate->AddVar('content', 'SLC_TB_YA', $selected_ya);
        $this->mrTemplate->AddVar('content', 'SLC_TB_TIDAK',$selected_tidak);
        
		$this->mrTemplate->AddVar('content', 'JUDUL', $tambah);
		$this->mrTemplate->AddVar('content', 'KLP_LAPORAN', 
                                empty($klp_lap[0]['nama']) ? $this->Data['klp_lap'] : $klp_lap[0]['nama']);
        
         $this->mrTemplate->AddVar('content', 'NO_URUTAN', $no_urutan);
		$this->mrTemplate->AddVar('content', 'URL_ACTION', 
                                Dispatcher::Instance()->GetUrl(
                                                               'kelompok_laporan', 
                                                               $url, 
                                                               'do', 
                                                               'html') . 
                                                               "&dataId=" . 
                                                               Dispatcher::Instance()->Encrypt($data['decDataId']));

		$this->mrTemplate->AddVar('content', 'DATAID', Dispatcher::Instance()->Decrypt($_GET['dataId']));
		$this->mrTemplate->AddVar('content', 'PAGE', Dispatcher::Instance()->Decrypt($_GET['page']));
	}
}