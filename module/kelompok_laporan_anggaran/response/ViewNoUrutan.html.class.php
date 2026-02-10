<?php

/**
 *
 * class ViewNoUrutan
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

class ViewNoUrutan extends HtmlResponse 
{
	
	public function TemplateModule() 
    {
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot') . 
                'module/kelompok_laporan_anggaran/template');
		$this->SetTemplateFile('input_no_urutan.html');
	}
	
	public function ProcessRequest() 
    {
		$idDec = Dispatcher::Instance()->Decrypt($_REQUEST['dataId']);
		$Obj = new KelompokLaporanAnggaran();
		 
		 $return['no_urutan'] = $Obj->GenerateNoUrutan($_REQUEST['dataId']);
		 return $return;
	}

	public function ParseTemplate($data = NULL) 
    {
            $this->mrTemplate->addVar('content','NO_URUTAN',$data['no_urutan']);
	}
}
