<?php


/**
 *
 * class DoUpdateKelompokLaporanAnggaran (HTML)
 * @package kelompok_laporan_anggaran
 * @subpackage business
 * @analyst nanang ruswianto <nanang@gamatechno.com>
 * @author noor hadi <noor.hadi@gamatechno.com>
 * @since 6 september 2012
 * @copyright 2012 Gamatechno Indonesia
 * 
 */
 
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
	'module/kelompok_laporan_anggaran/response/ProcessKelompokLaporanAnggaran.proc.class.php';

class DoUpdateKelompokLaporanAnggaran extends HtmlResponse 
{

	public function TemplateModule() {}
	
	public function ProcessRequest() 
	{
		$Obj = new ProcessKelompokLaporanAnggaran();
		$urlRedirect = $Obj->Update();
		$this->RedirectTo($urlRedirect);
		return NULL;
	}

	public function ParseTemplate($data = NULL) {}
	
}
