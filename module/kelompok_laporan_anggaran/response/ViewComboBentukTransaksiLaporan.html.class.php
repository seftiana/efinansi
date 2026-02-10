<?php

/**
 *
 * class ViewComboBentukTransaksi
 * @package kelompok_laporan_anggaran
 * @subpackage business
 * @analyst nanang ruswianto <nanang@gamatechno.com>
 * @author noor hadi <noor.hadi@gamatechno.com>
 * @since 6 september 2012
 * @copyright 2012 Gamatechno Indonesia
 * 
 */
 
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
		'module/kelompok_laporan_anggaran/business/KelompokJenisLaporanAnggaran.class.php';

class ViewComboBentukTransaksiLaporan extends HtmlResponse 
{
	
	public function TemplateModule() 
	{
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot') . 
			'module/kelompok_laporan_anggaran/template');
		$this->SetTemplateFile('combo_bentuk_transaksi.html');
	}
	
	public function ProcessRequest() 
	{
		$idDec = Dispatcher::Instance()->Decrypt($_REQUEST['dataId']);
		$Obj = new KelompokJenisLaporanAnggaran();
		 
		$bentukTransaksi = $Obj->GetBentukTransaksiCombo($_REQUEST['dataId']);
		if(empty($bentukTransaksi)){
		 	$disabledStatus="disabled";
		 	$bentukTransaksi['0']['id']="1";
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
														$disabledStatus.'  onChange="getNoUrut(this.value)" '
														), 
												Messenger::CurrentRequest);

		return $return;
	}

	public function ParseTemplate($data = NULL) {}
}
