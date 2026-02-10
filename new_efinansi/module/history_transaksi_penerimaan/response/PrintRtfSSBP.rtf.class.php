<?php

require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
	'module/history_transaksi_penerimaan/business/AppTransaksi.class.php';
	
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'main/function/date.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'main/function/terbilang.php';

class PrintRtfSSBP extends RtfResponse
{
	function __construct() 
	{

	}
	function GetFileName() 
	{
		
		return "ssbp_" . date('d-m-Y') . ".rtf";
	}
	function ProcessRequest() 
	{
		$Obj = new AppTransaksi;
		
		#get data from $_GET
		$dataId = Dispatcher::Instance()->Decrypt($_GET['dataId']);
		
		#get data by tanggal
		$gridList = $Obj->GetTransaksiById($dataId);
		$mak = $Obj->GetTransaksiMAK($dataId);
		#set rtf contents
		$contents = (GTFWConfiguration::GetValue('application', 'docroot') . "doc/template_ssbp.rtf");

		$this->rtf->SetContent($contents);
		
		#set company info to rtf content
		$this->rtf->AddVar('NAMA',GTFWConfiguration::GetValue('organization', 'ssbp_nama_wajib_setor'));
		$this->rtf->AddVar('ALAMAT',GTFWConfiguration::GetValue('organization', 'company_address'));
		$this->rtf->AddVar('KOTA',GTFWConfiguration::GetValue('organization', 'city'));
		$this->rtf->AddVar('NO_KOTA',GTFWConfiguration::GetValue('organization', 'city_number'));
		$this->rtf->AddVar('NPWP',GTFWConfiguration::GetValue('organization', 'npwp'));
		
		
		#set data to rtf content
		$this->rtf->AddVar('KODE_MAP',$mak['kode']);
		$this->rtf->AddVar('URAIAN',$mak['nama']);
		$this->rtf->AddVar('JUM_SETOR', number_format($gridList['nominal'], 2, ',', '.'));
		$this->rtf->AddVar('SETOR_HRF', Number::Terbilang($gridList['nominal'], 2));
		$this->rtf->AddVar('KODE_LMBG', GTFWConfiguration::GetValue('organization', 'ssbp_kementerian_lembaga_no'));
		$this->rtf->AddVar('NAMA_LMBG', GTFWConfiguration::GetValue('organization', 'ssbp_kementerian_lembaga_nama'));
		$this->rtf->AddVar('KODE_UNIT', GTFWConfiguration::GetValue('organization', 'ssbp_unit_org_eselon_no'));
		$this->rtf->AddVar('NAMA_UNIT', GTFWConfiguration::GetValue('organization', 'ssbp_unit_org_eselon_nama'));
		$this->rtf->AddVar('KODE_SATKER', GTFWConfiguration::GetValue('organization', 'ssbp_satker_no'));
		$this->rtf->AddVar('NAMA_SATKER', GTFWConfiguration::GetValue('organization', 'ssbp_satker_nama'));
		$this->rtf->AddVar('ID_FUNGSI', GTFWConfiguration::GetValue('organization', 'ssbp_subfungsi_no'));
		$this->rtf->AddVar('ID_SUB', GTFWConfiguration::GetValue('organization', 'ssbp_fungsi_no'));
		$this->rtf->AddVar('ID_PROG', GTFWConfiguration::GetValue('organization', 'ssbp_program_no'));
		$this->rtf->AddVar('NAMA_PROG', GTFWConfiguration::GetValue('organization', 'ssbp_prog_nama'));
		$this->rtf->AddVar('ID_KEG', GTFWConfiguration::GetValue('organization', 'ssbp_kegiatan_no'));
		$this->rtf->AddVar('ID_SUBKEG', GTFWConfiguration::GetValue('organization', 'ssbp_subkeg_no'));
		$this->rtf->AddVar('NAMA_KEGIATAN', GTFWConfiguration::GetValue('organization', 'ssbp_kegiatan_nama'));
		$this->rtf->AddVar('UNTUK_KEPERLUAN',$gridList['catatan_transaksi']);
		
		$this->rtf->AddVar('KODE_LOKASI', GTFWConfiguration::GetValue('organization', 'city_number'));
		$this->rtf->AddVar('LOKASI', GTFWConfiguration::GetValue('organization', 'city'));
		$this->rtf->AddVar('NAMA_SPN', GTFWConfiguration::GetValue('organization', 'ssbp_nama_spn'));
		$this->rtf->AddVar('NO','');
      $this->rtf->AddVar('NOMOR','');
		$tgl = date("Y-m-d");
		$this->rtf->AddVar('TANGGAL',ConvertDate($tgl,"yyyy-mm-dd","dd/mm/yyyy"));

	}
}
?>
