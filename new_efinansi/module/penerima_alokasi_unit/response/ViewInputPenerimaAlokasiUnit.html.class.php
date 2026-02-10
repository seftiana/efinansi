<?php  if ( ! defined('GTFW_BASE_DIR')) exit('No direct script access allowed');

require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
		'module/penerima_alokasi_unit/business/PenerimaAlokasiUnit.class.php';
        
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
        'module/user_unit_kerja/business/UserUnitKerja.class.php';

class ViewInputPenerimaAlokasiUnit extends HtmlResponse 
{
	protected $mData;
	protected $mPesan;
	protected $mObj;
	protected $mUUK;
	
	protected $mModuleName = 'penerima_alokasi_unit';

	public function __construct()
	{
		parent::__construct();
		$this->mObj = new PenerimaAlokasiUnit();
		$this->mUUK = new UserUnitKerja();
		
	}
	
	public function TemplateModule() 
	{
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot') .
                'module/'.$this->mModuleName.'/template');
		$this->SetTemplateFile('view_input_'.$this->mModuleName.'.html');
	}
	
	public function ProcessRequest() 
	{
		$_POST = $_POST->AsArray();
		$alokasiId = Dispatcher::Instance()->Decrypt($_REQUEST['data_id']);
		
		$alokasiUnitId = Dispatcher::Instance()->Decrypt($_REQUEST['unit_id']);
		$alokasiPusatId = Dispatcher::Instance()->Decrypt($_REQUEST['pusat_id']);
		
		$msg = Messenger::Instance()->Receive(__FILE__);
		$this->mPesan = $msg[0][1];
		
		$userLoginId = Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
		$unitKerja= $this->mUUK->GetUnitKerjaUser($userLoginId);
		

		if($_REQUEST['data_id']=='') {
            $this->mData = $_POST;
				
		} else {
			$this->mData = $this->mObj->GetDataById($alokasiId);
			$this->mData['p_unit']= $this->mObj->GetUnitKerjaPenerima($alokasiId,$alokasiPusatId);
			$this->mData['unit']= $this->mObj->GetUnitKerjaPenerima($alokasiId,$alokasiUnitId);
			
		}
		if(isset($msg[0][0])){
			$this->mData = $msg[0][0];
		}
		
		
		$return['decDataId'] = $alokasiId;
		$return['dataUnitKerjaPenerima'] = $dataUnitKerjaPenerima;
		
		return $return;
	}

	public function ParseTemplate($data = NULL) 
    {
		
				
		if ($_REQUEST['data_id']=='') {
			$url="addPenerimaAlokasiUnit";
			$tambah="Tambah";
			$this->mrTemplate->AddVar('is_edit','IS_EDIT','NO');
		} else {
			$url="updatePenerimaAlokasiUnit";
			$tambah="Ubah";
			$this->mrTemplate->AddVar('is_edit','IS_EDIT','YES');
            //$this->mrTemplate->AddVar('content', 'DATA_ID', $data['decDataId']);
		}
				
		/**
		 * get unit kerja penerima alokasi unit
		 */
		 $no = 0;
		 $totalAlokasi = 0;
		if (empty($this->mData['unit'])) {
			$this->mrTemplate->AddVar('list_data', 'IS_DATA_EMPTY', 'YES');
		} else {
			$this->mrTemplate->AddVar('list_data', 'IS_DATA_EMPTY', 'NO');
			$dataGrid = $this->mData['unit'];
			
            foreach($dataGrid as $key => $value){   	
                $no++ ;
                $totalAlokasi += $dataGrid[$key]['nilai'];
				$dataGrid[$key]['nomor']=$no;
				$dataGrid[$key]['index']=$key;
				$this->mrTemplate->AddVars('list_data_item', $dataGrid[$key], 'DATA_');
				$this->mrTemplate->parseTemplate('list_data_item', 'a');
				
			}
		}
		$mak_key = (isset($key) ? $key : 0);
		$this->mrTemplate->AddVar('content', 'MAX', $mak_key);		 
		$this->mrTemplate->AddVar('content', 'MAX_NOMOR', $no);		 
		$this->mrTemplate->AddVar('content', 'TOTAL_ALOKASI', $totalAlokasi);		 
		/**
		 * end
		 */
		 
		/**
		 * get unit kerja penerima alokasi pusat
		 */
		 //print_r($this->mData['p_unit']);
		 $pNo = 0;
		 $pTotalAlokasi = 0;
		if (empty($this->mData['p_unit'])) {
			$this->mrTemplate->AddVar('p_list_data', 'P_IS_DATA_EMPTY', 'YES');
		} else {
			$this->mrTemplate->AddVar('p_list_data', 'P_IS_DATA_EMPTY', 'NO');
			$pDataGrid = $this->mData['p_unit'];
			
            foreach($pDataGrid as $pKey => $value){   	
                $pNo++ ;
                $pTotalAlokasi += $pDataGrid[$pKey]['nilai'];
				$pDataGrid[$pKey]['nomor']=$pNo;
				$pDataGrid[$pKey]['index']=$pKey;
				$this->mrTemplate->AddVars('p_list_data_item', $pDataGrid[$pKey], 'PDATA_');
				$this->mrTemplate->parseTemplate('p_list_data_item', 'a');
				
			}
		}
		$p_mak_key = (isset($pKey) ? $pKey : 0);
		$this->mrTemplate->AddVar('content', 'P_MAX', $p_mak_key);		 
		$this->mrTemplate->AddVar('content', 'P_MAX_NOMOR', $pNo);		 
		$this->mrTemplate->AddVar('content', 'P_TOTAL_ALOKASI', $pTotalAlokasi);		 
		/**
		 * end
		 */
		if ($this->mPesan) {
			$this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
			$this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->mPesan);
		}
	
		$this->mrTemplate->AddVar('content', 'JUDUL', $tambah);
		
		$this->mrTemplate->AddVar('is_edit', 'KODE_PENERIMAAN_NAMA', $this->mData['kode_penerimaan_nama']);
		$this->mrTemplate->AddVar('content', 'UNIT_KERJA_NAMA', $this->mData['unit_kerja_nama']);
		$this->mrTemplate->AddVar('content', 'ALOKASI_ID', $this->mData['alokasi_id']);
		$this->mrTemplate->AddVar('content', 'ALOKASI_UNIT_ID', $this->mData['alokasi_unit_id']);
		$this->mrTemplate->AddVar('content', 'ALOKASI_PUSAT_ID', $this->mData['alokasi_pusat_id']);
		
		$alokasiNilaiUnit = (isset($this->mData['alokasi_unit_nilai']) ? $this->mData['alokasi_unit_nilai']: 0);
		$alokasiNilaiPusat =  (isset($this->mData['alokasi_pusat_nilai']) ? $this->mData['alokasi_pusat_nilai']: 0); 
		
		$this->mrTemplate->AddVar('content', 'ALOKASI_UNIT_NILAI', $alokasiNilaiUnit);
		$this->mrTemplate->AddVar('content', 'ALOKASI_PUSAT_NILAI', $alokasiNilaiPusat);
		$this->mrTemplate->AddVar('content', 'ALOKASI_UNIT_NILAI_LABEL', $alokasiNilaiUnit);
		$this->mrTemplate->AddVar('content', 'ALOKASI_PUSAT_NILAI_LABEL', $alokasiNilaiPusat);
		if($this->mData['jenis_alokasi'] == 'U'){
			$this->mrTemplate->AddVar('content', 'IS_U_CHECK','checked="checked"');
			$this->mrTemplate->AddVar('content', 'IS_P_CHECK','');
		}else{
			$this->mrTemplate->AddVar('content', 'IS_U_CHECK','');
			$this->mrTemplate->AddVar('content', 'IS_P_CHECK','checked="checked"');
		}
		
		
		$this->mrTemplate->AddVar('is_edit', 'URL_POPUP_KODE_PENERIMAAN', 
                                    Dispatcher::Instance()->GetUrl(
                                                            $this->mModuleName,
                                                            'popupKodePenerimaan', 
                                                            'view', 
                                                            'html'));
															
		$this->mrTemplate->AddVar('content', 'URL_POPUP_UNITKERJA', 
                                    Dispatcher::Instance()->GetUrl(
                                                            $this->mModuleName,
                                                            'popupUnitKerja', 
                                                            'view', 
                                                            'html'));
                                                            
		$this->mrTemplate->AddVar('content', 'URL_POPUP_UNITKERJA_PUSAT', 
                                    Dispatcher::Instance()->GetUrl(
                                                            $this->mModuleName,
                                                            'popupUnitKerjaPusat', 
                                                            'view', 
                                                            'html'));
                                                            
		//$this->mrTemplate->AddVar('content', 'NILAI',$this->mData['nilai']);


		$this->mrTemplate->AddVar('content', 'URL_ACTION', 
                                    Dispatcher::Instance()->GetUrl(
                                                                $this->mModuleName,
                                                                $url, 
                                                                'do', 
                                                                'html') . 
                                                                "&data_id=" . 
                                    Dispatcher::Instance()->Encrypt($data['decDataId']));
		$this->mrTemplate->AddVar('content', 'JUDUL', $tambah);
		$this->mrTemplate->AddVar('content', 'DATA_ID', Dispatcher::Instance()->Decrypt($_REQUEST['data_id']));
		$this->mrTemplate->AddVar('content', 'PAGE', Dispatcher::Instance()->Decrypt($_GET['page']));
	}
}

?>