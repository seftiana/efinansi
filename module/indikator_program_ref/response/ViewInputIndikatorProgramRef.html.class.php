<?php

/**
 * Class ViewInputIndikatorProgramRef
 * @package indikator_program_ref
 * @todo Untuk input data indikator program
 * @subpackage response
 * @since 21 Juni 2012
 * @SystemAnalyst Dyan Galih Nugroho Wicaksi <galih@gamatechno.com>
 * @author noor hadi <noor.hadi@gamatechno.com>
 * @copyright 2012 Gamatechno Indonesia
 * 
 */
 
require_once GTFWConfiguration::GetValue('application', 'docroot') . 
    'module/indikator_program_ref/business/IndikatorProgramRef.class.php';

class ViewInputIndikatorProgramRef extends HtmlResponse 
{

	protected $mIndikatorProgramRef;
    
	public function __construct()
	{
		$this->mIndikatorProgramRef = new IndikatorProgramRef();
	}
    
	public function TemplateModule() 
    {
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 
				'module/indikator_program_ref/template');
		$this->SetTemplateFile('view_input_indikator_program_ref.html');
	}
	
	public function ProcessRequest() 
    {
		$idDec = Dispatcher::Instance()->Decrypt($_REQUEST['dataId']);
		
		$msg = Messenger::Instance()->Receive(__FILE__);
		$return['Pesan'] = $msg[0][1];
		$return['Data'] = $msg[0][0];

		$data_ip = $this->mIndikatorProgramRef->GetDataById($idDec);

		$return['id'] = $idDec;
		$return['data_ip'] = $data_ip;
		return $return;
	}

	public function ParseTemplate($data = NULL) 
    {
		if ($data['Pesan']) {
			$this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
			$this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $data['Pesan']);
		}
		$data_ip = $data['data_ip'];
		
		if ($_REQUEST['dataId']=='') {
			$tambah = GTFWConfiguration::GetValue('language','tambah');
		} else {
			$tambah = GTFWConfiguration::GetValue('language','ubah');
		}
		
		$this->mrTemplate->AddVar('content', 'JUDUL', $tambah);
		$this->mrTemplate->AddVar('content', 'NAMA', 
                                empty($data_ip[0]['nama']) ? $data['Data']['nama'] : $data_ip[0]['nama']);
        $this->mrTemplate->AddVar('content', 'KODE_LAMA', 
                                empty($data_ip[0]['kode']) ? $data['Data']['kode_lama'] : $data_ip[0]['kode']);                                
      	$this->mrTemplate->AddVar('content', 'KODE', 
	  	                        empty($data_ip[0]['kode']) ? $data['Data']['kode'] : $data_ip[0]['kode']);
	  	$id = (empty($data_ip[0]['id']) ? $data['Data']['dataId'] : $data_ip[0]['id']);
        
		$this->mrTemplate->AddVar('content', 'URL_ACTION', 
                                Dispatcher::Instance()->GetUrl(
                                                         'indikator_program_ref', 
                                                         'indikatorProgramRef', 
                                                         'do', 
                                                         'html') . 
                                                          "&dataId=" . 
                                                          Dispatcher::Instance()->Encrypt($id));
		$this->mrTemplate->AddVar('content', 'IP_ID', $id);
	}
}
