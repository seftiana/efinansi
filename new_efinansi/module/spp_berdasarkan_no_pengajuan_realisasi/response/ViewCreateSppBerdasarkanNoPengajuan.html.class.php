<?php

/**
 * 
 * @package realisasi_pencairan_2
 * @sub_package response
 * @class ViewCreateSppBerdasarkanNoPengajuan
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
	
//require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
//	'main/function/terbilang.php';

class ViewCreateSppBerdasarkanNoPengajuan extends HtmlResponse 
{
	
	protected $mData;   
	protected $mDBObj;
	protected $mUserId;
	protected $mUserUnitKerja;
	protected $mTotalSubUnitKerja;
   
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
	
		
		if(is_object($_POST)){
			$this->_POST            = $_POST->AsArray();
		}else{
			$this->_POST            = $_POST;
		}
		
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
		$this->SetTemplateFile('create_spp_berdasarkan_no_pengajuan.html');
	}

	public function ProcessRequest() 
	{		
		
		$startYear        = $this->mDBObj->GetMinTahun(); 
		$endYear          = $this->mDBObj->GetMaxTahun();
		
		$tahunAnggaranAktif = $this->mDBObj->GetTahunAnggaranAktif();
		$unitKerja = $this->mUserUnitKerja->GetUnitKerjaUser($this->mUserId);
		$this->mTotalSubUnitKerja = $this->mUserUnitKerja->GetTotalSubUnitKerja($unitKerja['unit_kerja_id']);
		$dataTahunAnggaran = $this->mDBObj->GetTahunAnggaran();		
		
		// check jika ada id yang di refer
		if (isset($this->_GET['id']) AND $this->_GET['id'] != '') {
			$this->mId = Dispatcher::Instance()->Decrypt($_GET['id']);
			$this->mData = $this->mDBObj->GetDataById($this->mId);
			$this->mData['pengajuan'] = $this->mDBObj->GetDataNoPengajuan($this->mId);

		} else {
			$this->mData['tahun_anggaran_id'] = $tahunAnggaranAktif;
			//$dataList['ta_nama'] = $tahunAnggaranAktif['nama'];
			$this->mData['unit_kerja_id'] = $unitKerja['unit_kerja_id'];
			$this->mData['unit_kerja_nama'] = $unitKerja['unit_kerja_nama'];
			$this->mData['tanggal'] = date('Y-m-d', time());	
			$this->mData['nomor_spp_no_pengajuan'] = $this->mDBObj->GetGenerateNomorSppNoPengajuan();
		}
		
		
		/**
		 * get data from messanger ( data setelah terjadi submit)
		 */		 
		$msg = Messenger::Instance()->Receive(__FILE__);
		if(!empty($msg)){
			$this->mData= $msg[0][0];
			$this->mPesan = $msg[0][1];
			$this->mCss = $msg[0][2];
		}		

		//echo'<pre>' ;
		//print_r($this->mData);
		// start: combobox
		
		//echo'</pre>';
		// combobox : Tahun Anggaran
		Messenger::Instance()->SendToComponent(
												'combobox', 
												'Combobox', 
												'view', 
												'html', 
												'tahun_anggaran_id',
												array(
													'tahun_anggaran_id',
													$dataTahunAnggaran,
													$this->mData['tahun_anggaran_id'],
													'kosong', 
													' onchange="clearPengajuan();getProgram(this.value);"'
												), Messenger::CurrentRequest);

		// Combobox: Program Kegiatan
		$dataProgram = $this->mDBObj->GetDataProgram($this->mData['tahun_anggaran_id']);
		Messenger::Instance()->SendToComponent(
											'combobox', 
											'Combobox', 
											'view', 
											'html', 
											'program_id',
											array(
												'program_id', 
												$dataProgram, 
												$this->mData['program_id'],
												'false', 
												'id="program_id" onchange="clearPengajuan();"'
											), Messenger::CurrentRequest);												

		# GTFW Tanggal
		Messenger::Instance()->SendToComponent(
												'tanggal', 
												'Tanggal', 
												'view', 
												'html', 
												'tanggal', 
												array(
													$this->mData['tanggal'],
													$startYear, 
													$endYear, 
													false, 
													false, 
													false
												), Messenger::CurrentRequest
											);


		return $ret;
	}

	public function ParseTemplate($data = NULL) 
	{    		

		$popup_unitkerja    = Dispatcher::Instance()->GetUrl(
													'realisasi_pencairan_2', 
													'PopupUnitkerja', 
													'view', 
													'html'
													);

		$popupNoPengajuan  = Dispatcher::Instance()->GetUrl(
													'realisasi_pencairan_2', 
													'PopupNoPengajuan', 
													'view', 
													'html'
													);

		$urlProgram          = Dispatcher::Instance()->GetUrl(
													'realisasi_pencairan_2', 
													'Program', 
													'view', 
													'json'
													);
													
		$urlDaftarSppBerdasarkanNoPengajuan = Dispatcher::Instance()->GetUrl(
													'realisasi_pencairan_2', 
													'SppBerdasarkanNoPengajuan',
													'view', 
													'html'
													);            

		$this->mrTemplate->Addvar('content', 'UNIT_KERJA_ID', $this->mData['unit_kerja_id']);
		
		if($this->mTotalSubUnitKerja > 0){
			$this->mrTemplate->AddVar('cek_unitkerja_parent', 'IS_PARENT','YES');
		} else {
			$this->mrTemplate->AddVar('cek_unitkerja_parent', 'IS_PARENT', 'NO');
		}			
			$this->mrTemplate->AddVar('cek_unitkerja_parent', 'UNIT_KERJA_NAMA', $this->mData['unit_kerja_nama']);
			$this->mrTemplate->AddVar('cek_unitkerja_parent', 'POPUP_UNIT_KERJA', $popup_unitkerja);
		
		
		$this->mrTemplate->AddVar('content', 'POPUP_NO_PENGAJUAN', $popupNoPengajuan);
		$this->mrTemplate->AddVar('content', 'URL_DAFTAR_SPP_BERDASARKAN_NO_PENGAJUAN', 
											$urlDaftarSppBerdasarkanNoPengajuan);				
		$this->mrTemplate->AddVar('content', 'URL_PROGRAM', $urlProgram);
		$this->mrTemplate->Addvar('content', 'SPP_NO_PENGAJUAN_ID', $this->mData['spp_no_pengajuan_id']);
		$this->mrTemplate->Addvar('content', 'NOMOR_SPP_NO_PENGAJUAN', $this->mData['nomor_spp_no_pengajuan']);
		$this->mrTemplate->Addvar('content', 'JUMLAH_TOTAL', $this->mData['jumlah_total']);
		$this->mrTemplate->Addvar('content', 'JUMLAH_TOTAL_LABEL', 
										number_format((float) $this->mData['jumlah_total'],0,',','.'));
		/**									
		if(($this->mData['jumlah_total'] != 0) || ($this->mData['jumlah_total'] != '')){
			$this->mrTemplate->Addvar('content', 'TERBILANG', 
					$this->mNumber->Terbilang($this->mData['jumlah_total'],3).' Rupiah');
		} else {
				$this->mrTemplate->Addvar('content', 'TERBILANG','');
		}	
		*/ 
		$this->mrTemplate->Addvar('content', 'KETERANGAN', $this->mData['keterangan']);
       
		if(isset($this->mPesan)){
			$this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
			$this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->mPesan);
			$this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $this->mCss);
		}

		//if(isset($this->mId) AND ($this->mId  !== NULL) AND ($this->mId  != '')){
		if(isset($this->mData['spp_no_pengajuan_id']) AND 
				($this->mData['spp_no_pengajuan_id']  !== NULL) AND 
					($this->mData['spp_no_pengajuan_id']  != '')){
			$label_action = 'Update';
			$btnLabel = 'Update';
			$title = 'Ubah';
			$action = 'edit';
			$urlAction = Dispatcher::Instance()->GetUrl(
										'realisasi_pencairan_2',
										'updateSppBerdasarkanNoPengajuan',
										'do',
										'json'
										);
		}else{
			$btnLabel = 'Simpan';
			$label_action = "Tambah";
			$title = "Tambah";
			$action = 'add';
			$urlAction = Dispatcher::Instance()->GetUrl(
									'realisasi_pencairan_2',
									'addSppBerdasarkanNoPengajuan',
									'do',
									'json'
									);
		}

		$this->mrTemplate->AddVar('content', 'DATA_ACTION', $action);
		$this->mrTemplate->AddVar('content', 'URL_ACTION', $urlAction);
		$this->mrTemplate->AddVar('content', 'LABEL_ACTION', $label_action);
		$this->mrTemplate->Addvar('content', 'BTN_LABEL', $btnLabel);
      
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
			//echo'<pre>'; 
			//print_r($dataPengajuan);
			//echo '</pre>';
			foreach($dataPengajuan as $key => $value){				
				$nomor++;
				$jumlah_total += $value['nominal'];
				$dataPengajuan[$key]['no'] =$nomor;
				$dataPengajuan[$key]['index'] =$key;
				$dataPengajuan[$key]['pengajuan_tanggal'] = IndonesianDate($dataPengajuan[$key]['tanggal_pengajuan'],"YYYY-MM-DD");
				//$dataPengajuan[$key]['kode_ma'] = '';
				//$dataPengajuan[$key]['nama_index'] = '';
				$dataPengajuan[$key]['nominal_label'] = number_format($value['nominal'],0,',','.');
				$this->mrTemplate->AddVars('data_item', $dataPengajuan[$key], '');
				$this->mrTemplate->parseTemplate('data_item', 'a');				
			}
			
        }	
		$mak_key = (isset($key) ? $key : 0);
		$mak_nomor = (isset($nomor) ? $nomor : 0);
		$this->mrTemplate->AddVar('content', 'MAKS', $mak_key);
		$this->mrTemplate->AddVar('content', 'MAKS_NOMOR', $mak_nomor);
		$this->mrTemplate->AddVar('content', 'JUMLAH_TOTAL_LABEL', number_format($jumlah_total,0,',','.'));
		$this->mrTemplate->AddVar('content', 'JUMLAH_TOTAL', $jumlah_total);
		
    		
		/**
		 * end
		 */ 

   }
}

?>