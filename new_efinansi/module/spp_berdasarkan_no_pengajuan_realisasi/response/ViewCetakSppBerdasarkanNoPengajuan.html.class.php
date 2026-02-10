<?php

/**
 * 
 * @package realisasi_pencairan_2
 * @sub_package response
 * @class ViewCetakSppBerdasarkanNoPengajuan
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
	
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
	'main/function/terbilang.php';

class ViewCetakSppBerdasarkanNoPengajuan extends HtmlResponse 
{
	
	protected $mData;   
	protected $mDBObj;
	protected $mUserId;
	protected $mRealName;
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
		$this->mRealName = Security::Instance()->mAuthentication->GetCurrentUser()->GetRealName();
		$this->mNumber = new Number();

		if(is_object($_GET)){
			$this->_GET = $_GET->AsArray();
		}else{
			$this->_GET = $_GET;
		}
	}
   
   
	public function TemplateModule() 
	{
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot') .
		'module/realisasi_pencairan_2/template');
		$this->SetTemplateFile('cetak_spp_berdasarkan_no_pengajuan.html');
	}

	public function TemplateBase() 
	{
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
		$this->SetTemplateFile('document-print.html');
		$this->SetTemplateFile('layout-common-print.html');
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
			$this->mData['pengajuan'] = $this->mDBObj->GetDataNoPengajuanGroup($this->mId);
			$this->mData['dataIndex'] = $this->mDBObj->GetDataIndex($this->mId);
			$this->mData['dataIndexDetail'] = $this->mDBObj->GetDataIndexDetail($this->mId);

		} 
				
		return null;
	}

	public function ParseTemplate($data = NULL) 
	{	
		/** 
		 * untuk identitas pencetak
		 */
		$timeStamp        = date('Y/m/d H:i:s', time());
		$this->mrTemplate->AddVar('content', 'TIMESTAMP', $timeStamp);
		$this->mrTemplate->AddVar('content', 'USERNAME', $this->mRealName);
		/**
		 * end
		 */
		 
		$this->mrTemplate->Addvar('content', 'TAHUN_ANGGARAN_NAMA', $this->mData['tahun_anggaran_nama']);
		$this->mrTemplate->Addvar('content', 'UNIT_KERJA_NAMA', $this->mData['unit_kerja_nama']);
		$this->mrTemplate->Addvar('content', 'NOMOR_SPP_NO_PENGAJUAN', $this->mData['nomor_spp_no_pengajuan']);
		$this->mrTemplate->Addvar('content', 'NAMA_BENDAHARA', 
					GTFWConfiguration::GetValue('organization', 'pejabat_bendahara_nama'));
		$this->mrTemplate->Addvar('content', 'PROGRAM_NAMA', $this->mData['program_nama']);
		$this->mrTemplate->Addvar('content', 'PROGRAM_KODE', $this->mData['program_kode']);
		$this->mrTemplate->Addvar('content', 'NAMA_PIMPINAN',
					strtoupper(GTFWConfiguration::GetValue('organization', 'pejabat_kepala_biro_nama')));
		$this->mrTemplate->Addvar('content', 'NAMA_JABATAN_PIMPINAN',
					GTFWConfiguration::GetValue('organization', 'kep_biro_umum'));
		$this->mrTemplate->Addvar('content', 'TANGGAL_PENGAJUAN_LABEL',IndonesianDate($this->mData['tanggal'],"YYYY-MM-DD"));
		$this->mrTemplate->Addvar('content', 'KEPADA',
				strtoupper(GTFWConfiguration::GetValue('organization', 'unit_bendahara_universitas')));
		$this->mrTemplate->Addvar('content', 'JUMLAH_TOTAL_LABEL', number_format((float) $this->mData['jumlah_total'],0,',','.'));
		
		
		if(($this->mData['jumlah_total'] != 0) || ($this->mData['jumlah_total'] != '')){
			$this->mrTemplate->Addvar('content', 'TERBILANG', 
					$this->mNumber->Terbilang($this->mData['jumlah_total'],3).' Rupiah');
		} else {
				$this->mrTemplate->Addvar('content', 'TERBILANG','');
		}	
		
		$this->mrTemplate->Addvar('content', 'KETERANGAN', $this->mData['keterangan']);       
		
		/**
		 * daftar index
		 */

		if(!empty($this->mData['dataIndex'])) {
			$dataIndex = $this->mData['dataIndex'];			
			$kodeIndex = '';
			
			foreach($dataIndex as $key => $value){
				$kodeIndex .= 'MA&nbsp;'.$dataIndex[$key]['ma_kode'].'&nbsp;&nbsp;Rp.&nbsp'.
								number_format($dataIndex[$key]['ma_nominal'],0,',','.').'<br />';
				
			}
        }
        /**
         * end
         */
        
        /**
         * daftar no pengajuan
         */
		if(!empty($this->mData['pengajuan'])) {
			$dataPengajuan = $this->mData['pengajuan'];			
			$noPengajuan = '';
			
			foreach($dataPengajuan as $key => $value){
				$noPengajuan .= $dataPengajuan[$key]['pengajuan_no'].'&nbsp;tanggal&nbsp'.
								IndonesianDate($dataPengajuan[$key]['tanggal_pengajuan'],"YYYY-MM-DD").'<br />';
				
			}
        }
		
		$this->mrTemplate->AddVar('content', 'INDEX', $kodeIndex);
		$this->mrTemplate->AddVar('content', 'BERDASARKAN', $noPengajuan);
    		
		/**
		 * end
		 */ 
		 
		/**
		 * daftar index detail
		 */
//		echo '<pre>'; 
//		print_r($this->mData['dataIndexDetail']); 
//		echo '</pre>';

		if(empty($this->mData['dataIndexDetail'])){
			$this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
		}else {
			$this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');
			$dataIndexDetail = $this->mData['dataIndexDetail'];
			
			$totalPagu = 0;
			$totalSpptLalu = 0;
			$totalSpptIni = 0;
			$totalSpptSampaiIni = 0;
			$totalSisa = 0;
			
			foreach($dataIndexDetail as $key => $value){
					
					/**
					 * hitung
					 */
					$spptSampaiIni = ($value['sppLalu']  + $value['sppIni']);
					$spptSisa = ($value['nominalPagu'] - $spptSampaiIni);
					$totalPagu += $value['nominalPagu'];
					$totalSpptLalu += $value['sppLalu'];
					$totalSpptIni += $value['sppIni'];
					$totalSpptSampaiIni += $spptSampaiIni;
					$totalSisa += $spptSisa;
					
				    $dataIndexDetail[$key]['pagu_anggaran'] = number_format($value['nominalPagu'],0,',','.');
					$dataIndexDetail[$key]['sppt_lalu'] = number_format($value['sppLalu'],0,',','.');
					$dataIndexDetail[$key]['sppt_ini'] = number_format($value['sppIni'],0,',','.');
					$dataIndexDetail[$key]['sppt_sampai_ini'] = number_format($spptSampaiIni,0,',','.');
					$dataIndexDetail[$key]['sisa'] = number_format($spptSisa,0,',','.');
					$this->mrTemplate->AddVars('data_index_detail', $dataIndexDetail[$key], '');
					$this->mrTemplate->parseTemplate('data_index_detail', 'a');
			}
			
			$this->mrTemplate->AddVar('data_grid', 'TOTAL_PAGU_ANGGARAN', number_format($totalPagu,0,',','.'));
			$this->mrTemplate->AddVar('data_grid', 'TOTAL_SPPT_LALU', number_format($totalSpptLalu,0,',','.'));
			$this->mrTemplate->AddVar('data_grid', 'TOTAL_SPPT_INI', number_format($totalSpptIni,0,',','.'));
			$this->mrTemplate->AddVar('data_grid', 'TOTAL_SPPT_SAMPAI_INI', number_format($totalSpptSampaiIni,0,',','.'));
			$this->mrTemplate->AddVar('data_grid', 'TOTAL_SISA', number_format($totalSisa,0,',','.'));
        }

   }
}

?>