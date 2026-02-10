<?php

/**
 * Class ViewInputIndikatorKegiatanRef
 * @package indikator_program_ref
 * @todo Untuk input data indikator kegiatan
 * @subpackage response
 * @since 22 Juni 2012
 * @SystemAnalyst Dyan Galih Nugroho Wicaksi <galih@gamatechno.com>
 * @author noor hadi <noor.hadi@gamatechno.com>
 * @copyright 2012 Gamatechno Indonesia
 * 
 */
 
require_once GTFWConfiguration::GetValue('application', 'docroot') . 
    'module/indikator_program_ref/business/IndikatorKegiatanRef.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') . 
    'module/indikator_program_ref/business/IndikatorProgramRef.class.php';

class ViewInputIndikatorKegiatanRef extends HtmlResponse 
{

	protected $mIndikatorKegiatanRef;
    protected $mIndikatorProgramRef;
    
	public function __construct()
	{
		$this->mIndikatorKegiatanRef = new IndikatorKegiatanRef();
        $this->mIndikatorProgramRef = new IndikatorProgramRef();
	}
    
	public function TemplateModule() 
    {
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 
				'module/indikator_program_ref/template');
		$this->SetTemplateFile('view_input_indikator_kegiatan_ref.html');
	}
	
	public function ProcessRequest() 
    {
		$idDec = Dispatcher::Instance()->Decrypt($_REQUEST['dataId']);
        $ipId = Dispatcher::Instance()->Decrypt($_REQUEST['ipId']);
		
		$msg = Messenger::Instance()->Receive(__FILE__);
		$return['Pesan'] = $msg[0][1];
		$return['Data'] = $msg[0][0];

		$data_ik = $this->mIndikatorKegiatanRef->GetDataById($idDec);
        if($ipId != ''){
            $data_ip = $this->mIndikatorProgramRef->GetDataById($ipId);
        } else {
            $data_ip = array();
        }            

		$return['id'] = $idDec;
		$return['data_ik'] = $data_ik;
        $return['data_ip'] = $data_ip;
		return $return;
	}

	public function ParseTemplate($data = NULL) 
    {
		if ($data['Pesan']) {
			$this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
			$this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $data['Pesan']);
		}
       
		$data_ik = $data['data_ik'];
        if(isset($data['data_ip'][0]['id']) && ($data['data_ip'][0]['id'] !='')){
            $this->mrTemplate->AddVar('data_ip', 'IS_DATA_IP', 'YES');
        
            $data['Data']['ipId'] = empty($data['Data']['ipId']) ? 
                                    $data['data_ip'][0]['id'] : $data['Data']['ipId']; 
            $data['Data']['ipNama'] = empty($data['Data']['ipNama']) ? 
                                    $data['data_ip'][0]['nama'] : $data['Data']['ipNama'];
         } else {
            $this->mrTemplate->AddVar('data_ip', 'IS_DATA_IP', 'NO');
         }
		if ($_REQUEST['dataId']=='') {
			$tambah = GTFWConfiguration::GetValue('language','tambah');
		} else {
			$tambah = GTFWConfiguration::GetValue('language','ubah');
		}
		
        $url_popup	= Dispatcher::Instance()->GetUrl('indikator_program_ref', 
                                                     'popupIndikatorProgramRef', 
													 'view', 
                                                     'html');
        $this->mrTemplate->AddVar('data_ip', 'URL_POPUP', $url_popup);
		$this->mrTemplate->AddVar('content', 'JUDUL', $tambah);
		$this->mrTemplate->AddVar('content', 'NAMA', 
                                empty($data_ik[0]['nama']) ? $data['Data']['nama'] : $data_ik[0]['nama']);
        $this->mrTemplate->AddVar('content', 'KODE_LAMA', 
                                empty($data_ik[0]['kode']) ? $data['Data']['kodeLama'] : $data_ik[0]['kode']);
        $this->mrTemplate->AddVar('content', 'VALUE', 
                            empty($data_ik[0]['value']) ? $data['Data']['value'] : $data_ik[0]['value']);
      	$this->mrTemplate->AddVar('content', 'KODE', 
	  	                        empty($data_ik[0]['kode']) ? $data['Data']['kode'] : $data_ik[0]['kode']);
	  	$id = (empty($data_ik[0]['id']) ? $data['Data']['dataId'] : $data_ik[0]['id']);
        
        $this->mrTemplate->AddVar('data_ip', 'IP_ID', 
                            empty($data_ik[0]['ipId']) ? $data['Data']['ipId'] : $data_ik[0]['ipId']);  
        $this->mrTemplate->AddVar('data_ip', 'IP_NAMA', 
                            empty($data_ik[0]['ipNama']) ? $data['Data']['ipNama'] : $data_ik[0]['ipNama']);
        
		$this->mrTemplate->AddVar('content', 'URL_ACTION', 
                                Dispatcher::Instance()->GetUrl(
                                                         'indikator_program_ref', 
                                                         'indikatorKegiatanRef', 
                                                         'do', 
                                                         'html') . 
                                                          "&dataId=" . 
                                                          Dispatcher::Instance()->Encrypt($id));
		$this->mrTemplate->AddVar('content', 'IK_ID', $id);
	}
}
