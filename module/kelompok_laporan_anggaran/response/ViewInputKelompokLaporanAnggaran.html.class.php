<?php

/**
 *
 * class ViewInputKelompokLaporanAnggaran
 * @package kelompok_laporan_anggaran
 * @subpackage business
 * @analyst nanang ruswianto <nanang@gamatechno.com>
 * @author noor hadi <noor.hadi@gamatechno.com>
 * @since 6 september 2012
 * @copyright 2012 Gamatechno Indonesia
 * 
 */
 
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
		'module/kelompok_laporan_anggaran/business/KelompokLaporanAnggaran.class.php';
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
		'module/kelompok_laporan_anggaran/business/KelompokJenisLaporanAnggaran.class.php';

class ViewInputKelompokLaporanAnggaran extends HtmlResponse 
{
	protected $mPesan;
	
	protected $mKLA;
	protected $mKJLA;
	
	protected $mData;
	
	public function TemplateModule() 
	{
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot') . 
            'module/kelompok_laporan_anggaran/template');
		$this->SetTemplateFile('input_kelompok_laporan_anggaran.html');
	}
	
	public function ProcessRequest() 
	{
		$idDec = Dispatcher::Instance()->Decrypt($_REQUEST['dataId']);
		$this->mKLA = new KelompokLaporanAnggaran();
		$this->mKJLA = new KelompokJenisLaporanAnggaran();
		
		$msg = Messenger::Instance()->Receive(__FILE__);
		$this->mPesan = $msg[0][1];
		$this->mData = $msg[0][0];
		
		$data_klp_lap = $this->mKLA->GetDataById($idDec);
		
		$idJenisLaporan = (!empty($this->mData['jns_lap']) ? $this->mData['jns_lap'] : $data_klp_lap['0']['jenis_laporan_id']);
		$idBentukTransaksi =(!empty($this->mData['bentuk_transaksi']) ? $this->mData['bentuk_transaksi'] : $data_klp_lap['0']['bentuk_transaksi_id']);
		
		$jenisLaporan = $this->mKJLA->GetJenisLaporanCombo();
		
		if(!empty($this->mData['pagu_bas_mak'])){
			$dataPaguBasMak = $this->mData['pagu_bas_mak'];
		} else {
			$dataPaguBasMak = $this->mKLA->GetDataPaguBasMak($idDec);
		}

		Messenger::Instance()->SendToComponent(
												'combobox', 
												'Combobox', 
												'view', 
												'html', 
												'jns_lap', 
												array(
														'jns_lap', 
														$jenisLaporan, 
														$idJenisLaporan, 
														'none', 
														'onChange="getBentukTransaksi(this.value)"'
														), 
												Messenger::CurrentRequest);
		 
		 if(empty($idJenisLaporan))
		 	$idJenisLaporan=$jenisLaporan['0']['id'];
		 
		 $bentukTransaksi = $this->mKJLA->GetBentukTransaksiCombo($idJenisLaporan);
		 
		 if(empty($bentukTransaksi)){
		 	$disabledStatus="disabled";
		 	$bentukTransaksi['0']['name']="Tidak Ada Data";
		 } 
		 Messenger::Instance()->SendToComponent(
												'combobox', 
												'Combobox', 
												'view', 
												'html', 
												'bentuk_transaksi', 
												array(
														'bentuk_transaksi', 
														$bentukTransaksi, 
														$idBentukTransaksi, 
														'none', 
														$disabledStatus.' onChange="getNoUrut(this.value)" '
														), 
												Messenger::CurrentRequest);        
												
         $return['no_urutan'] = $this->mKLA->GenerateNoUrutan($idJenisLaporan);        
		 $return['decDataId'] = $idDec;
		 $return['data_klp_lap'] = $data_klp_lap;
		 $return['data_pagu_bas_mak'] = $dataPaguBasMak;
		 return $return;
	}

	function ParseTemplate($data = NULL) 
	{
		if ($this->mPesan) {
			$this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
			$this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->mPesan);
		}
		$klp_lap = $data['data_klp_lap'];
        
		$selected =' selected="selected"';
        
		if ($_REQUEST['dataId']=='') {
			$url="addKelompokLaporanAnggaran";
			$tambah="Tambah";
            $selected_ya = $selected;
            $selected_tidak ='';
            $no_urutan = empty($this->mData['no_urutan']) ? $data['no_urutan'] : $this->mData['no_urutan'];
		} else {
			$url="updateKelompokLaporanAnggaran";
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
                                empty($klp_lap[0]['nama']) ? $this->mData['klp_lap'] : $klp_lap[0]['nama']);
        
         $this->mrTemplate->AddVar('content', 'NO_URUTAN', $no_urutan);
         $this->mrTemplate->AddVar('content', 'URL_POPUP_PAGU_BAS_MAK', 
									Dispatcher::Instance()->GetUrl(
															'kelompok_laporan_anggaran', 
															'PopupPaguBas', 
															'view', 
															'html'));
		$this->mrTemplate->AddVar('content', 'URL_ACTION', 
                                Dispatcher::Instance()->GetUrl(
                                                               'kelompok_laporan_anggaran', 
                                                               $url, 
                                                               'do', 
                                                               'html') . 
                                                               "&dataId=" . 
                                                               Dispatcher::Instance()->Encrypt($data['decDataId']));

		$this->mrTemplate->AddVar('content', 'DATAID', Dispatcher::Instance()->Decrypt($data['decDataId']));
		//$this->mrTemplate->AddVar('content', 'PAGE', Dispatcher::Instance()->Decrypt($_GET['page']));
		
		//detail
		if (empty($data['data_pagu_bas_mak'])){
			$this->mrTemplate->AddVar('data_mak', 'IS_DATA_EMPTY', 'YES');
			$this->mrTemplate->AddVar('content', 'MAKS',0);
		} else {
			
			$this->mrTemplate->AddVar('data_mak', 'IS_DATA_EMPTY', 'NO');
			
			$dataPaguBasMak = $data['data_pagu_bas_mak'];
			$maksKey = max(array_keys($dataPaguBasMak)) + 1;
			
			$this->mrTemplate->AddVar('content', 'MAKS',$maksKey);
						
			for ($i = 0;$i < $maksKey;$i++) {
				if(empty($dataPaguBasMak[$i]['mak_id'])){
					continue;
				}
				$no = $i + 1;
				$dataPaguBasMak[$i]['nomor'] = $no;
				$dataPaguBasMak[$i]['index'] = $i;

				if ($no % 2 != 0) $dataPaguBasMak[$i]['class_name'] = 'table-common-even';
				else $dataPaguBasMak[$i]['class_name'] = '';

																
				$this->mrTemplate->AddVars('data_item', $dataPaguBasMak[$i], 'DATA_');
				$this->mrTemplate->parseTemplate('data_item', 'a');
			}
		
		}
	}
}
