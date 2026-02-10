<?php

/**
 * 
 * @package realisasi_pencairan_2
 * @sub_package response
 * @class ViewPopupSppBerdasarkanNoPengajuanDetail
 * @description untuk view form input membuat spp berdasarkan no pengajuan
 * @author noor hadi <noor.hadi@gamatechno.com>
 * @copyright 2013 Gamatechno Indonesia
 * 
 */
  
require_once GTFWConfiguration::GetValue('application', 'docroot') .
	'module/realisasi_pencairan_2/business/SppBerdasarkanNoPengajuan.class.php';

require_once GTFWConfiguration::GetValue('application', 'docroot') .
	'module/user_unit_kerja/business/UserUnitKerja.class.php';

require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
	'main/function/date.php';


class ViewPopupSppBerdasarkanNoPengajuanDetail extends HtmlResponse 
{
	
	protected $mData;   
	protected $mDBObj;
	protected $mUserId;
	protected $mUserUnitKerja;
   
	protected $_POST;
	protected $_GET;
	
	protected $mPesan;
	protected $mCss;

	protected $mId;
	
	protected $mNumber;
	
	public function __construct()
	{
		$this->mDBObj = new SppBerdasarkanNoPengajuan();      
		$this->mUserUnitKerja = new UserUnitKerja();
		$this->mUserId = Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();

		if(is_object($_GET)){
			$this->_GET             = $_GET->AsArray();
		}else{
			$this->_GET             = $_GET;
		}
	}

	public function TemplateModule() 
	{
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot') .
		'module/realisasi_pencairan_2/template');
		$this->SetTemplateFile('popup_spp_berdasarkan_no_pengajuan_detail.html');
	}

	public function ProcessRequest() 
	{		
		
		$startYear        = $this->mDBObj->GetMinTahun(); 
		$endYear          = $this->mDBObj->GetMaxTahun();
		
		$tahunAnggaranAktif = $this->mDBObj->GetTahunAnggaranAktif();
		$unitKerja = $this->mUserUnitKerja->GetUnitKerjaUser($this->mUserId);
		$dataTahunAnggaran = $this->mDBObj->GetTahunAnggaran();
		
		// check jika ada id yang di refer
		if (isset($this->_GET['id']) AND $this->_GET['id'] != '') {
			$this->mId = Dispatcher::Instance()->Decrypt($_GET['id']);
			$this->mData = $this->mDBObj->GetDataById($this->mId);
			$this->mData['pengajuan'] = $this->mDBObj->GetDataNoPengajuan($this->mId);
		} 
				
		return null;
	}

	public function ParseTemplate($data = NULL) 
	{    		
	
			
		$this->mrTemplate->Addvar('content', 'TAHUN_ANGGARAN_NAMA', $this->mData['tahun_anggaran_nama']);
		$this->mrTemplate->Addvar('content', 'UNIT_KERJA_NAMA', $this->mData['unit_kerja_nama']);
		$this->mrTemplate->Addvar('content', 'NOMOR_SPP_NO_PENGAJUAN', $this->mData['nomor_spp_no_pengajuan']);
		$this->mrTemplate->Addvar('content', 'PROGRAM_NAMA', $this->mData['program_nama']);	
		$this->mrTemplate->Addvar('content', 'TANGGAL_PENGAJUAN_LABEL',IndonesianDate($this->mData['tanggal'],"YYYY-MM-DD"));
		$this->mrTemplate->Addvar('content', 'JUMLAH_TOTAL_LABEL', 
										number_format((float) $this->mData['jumlah_total'],0,',','.'));
		

		$this->mrTemplate->Addvar('content', 'KETERANGAN', $this->mData['keterangan']);       
		
		/**
		 * daftar nomor pengajuan
		 */

		if(empty($this->mData['pengajuan'])) {
			$this->mrTemplate->AddVar('data_pengajuan', 'IS_DATA_EMPTY', 'YES');
			
		} else {
			$this->mrTemplate->AddVar('data_pengajuan', 'IS_DATA_EMPTY', 'NO');
			$dataPengajuan = $this->mData['pengajuan'];
			$nomor= 0;
			$jumlah_total =0;
			
			foreach($dataPengajuan as $key => $value){				
				$nomor++;
				$jumlah_total += $value['nominal'];
				$dataPengajuan[$key]['no'] =$nomor;
				$dataPengajuan[$key]['index'] =$key;
				$dataPengajuan[$key]['pengajuan_tanggal'] = IndonesianDate($dataPengajuan[$key]['tanggal_pengajuan'],"YYYY-MM-DD");
				$dataPengajuan[$key]['nominal_label'] = number_format($value['nominal'],0,',','.');
				$this->mrTemplate->AddVars('data_item', $dataPengajuan[$key], '');
				$this->mrTemplate->parseTemplate('data_item', 'a');				
			}
			
        }	
        
		$this->mrTemplate->AddVar('content', 'JUMLAH_TOTAL_LABEL', number_format($jumlah_total,0,',','.'));
		$this->mrTemplate->AddVar('content', 'JUMLAH_TOTAL', $jumlah_total);		
    		
		/**
		 * end
		 */ 

   }
}

?>