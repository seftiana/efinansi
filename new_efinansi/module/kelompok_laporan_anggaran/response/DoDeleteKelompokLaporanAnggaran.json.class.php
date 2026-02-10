<?php

/**
 *
 * class DoDeleteKelompokLaporanAnggaran (JSON)
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

class DoDeleteKelompokLaporanAnggaran extends JsonResponse 
{

	public function ProcessRequest() 
	{
		$Obj = new ProcessKelompokLaporanAnggaran();
		$urlRedirect = $Obj->Delete();
		return array( 
		   'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlRedirect.'&ascomponent=1")'
		);
	}
	
}
