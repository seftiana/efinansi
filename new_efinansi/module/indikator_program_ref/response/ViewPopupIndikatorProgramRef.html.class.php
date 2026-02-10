<?php

/**
 * Class ViewPopupIndikatorProgramRef
 * @package indikator_program_ref
 * @todo Untuk menampilkan data
 * @subpackage response
 * @since 22 Juni 2012
 * @SystemAnalyst Dyan Galih Nugroho Wicaksi <galih@gamatechno.com>
 * @author noor hadi <noor.hadi@gamatechno.com>
 * @copyright 2012 Gamatechno Indonesia
 * 
 */
 
require_once GTFWConfiguration::GetValue('application', 'docroot') . 
    'module/indikator_program_ref/business/PopupIndikatorProgramRef.class.php';

class ViewPopupIndikatorProgramRef extends HtmlResponse
{

	protected $mPopupIndikatorProgramRef;
    
	public function __construct()
	{
		$this->mPopupIndikatorProgramRef = new PopupIndikatorProgramRef();
	}
	
    public function TemplateModule()
	{
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 
				'module/indikator_program_ref/template');
		$this->SetTemplateFile('view_popup_indikator_program_ref.html');
	}
    
	public function ProcessRequest()
	{
	   $_POST = $_POST->AsArray();
       
       if(isset($_POST['btncari'])){
            $kode = $_POST['kode'];
            $nama = $_POST['nama'];
        } elseif(isset($_GET['cari'])){
            $kode = Dispatcher::Instance()->Decrypt($_GET['kode']);
            $nama = Dispatcher::Instance()->Decrypt($_GET['nama']);
        } else{
            $kode ='';
            $nama ='';
        }           
       
       
        $msg = Messenger::Instance()->Receive(__FILE__);
		$return['pesan']	= $msg[0][1];
		$return['css'] 		= $msg[0][2];
      
  		$totalData = $this->mPopupIndikatorProgramRef->GetDataCount($kode,$nama);
		$itemViewed = 20;
		$currPage = 1;
		$startRec = 0 ;
		if(isset($_GET['page'])) {
			$currPage = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
			$startRec =($currPage-1) * $itemViewed;
		}
        
        $url = Dispatcher::Instance()->GetUrl(
	  										Dispatcher::Instance()->mModule, 
				  							Dispatcher::Instance()->mSubModule, 
				  							Dispatcher::Instance()->mAction, 	
				  							Dispatcher::Instance()->mType . 
		  									'&kode=' . 
                                            Dispatcher::Instance()->Encrypt($kode) . 
										  	'&nama=' . 
                                            Dispatcher::Instance()->Encrypt($nama) .
										  	'&cari=' . Dispatcher::Instance()->Encrypt(1));
        $dest = "popup-subcontent";
		Messenger::Instance()->SendToComponent(
												'paging', 
												'Paging', 
												'view', 
												'html', 
												'paging_top', 
												array(
														$itemViewed,
														$totalData, 
														$url, 
														$currPage,
                                                        $dest), 
												Messenger::CurrentRequest);
        
        $return['data'] = $this->mPopupIndikatorProgramRef->GetData($kode,$nama,$startRec,$itemViewed);
        $return['kode'] = $kode;
        $return['nama'] = $nama;
        $return['startRec'] = $startRec;
		return $return;
	}
    
	public function ParseTemplate($data = NULL)
	{
	    if($data['pesan']) {
			$this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
			$this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $data['pesan']);
			$this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $data['css']);
		}
        
        $url_search	= Dispatcher::Instance()->GetUrl('indikator_program_ref', 
                                                     'popupIndikatorProgramRef', 
													 'view', 
                                                     'html');
                                                             
        $this->mrTemplate->AddVar('content', 'URL_SEARCH', $url_search);
        
        $this->mrTemplate->AddVar('content', 'KODE', $data['kode']);
        $this->mrTemplate->AddVar('content', 'NAMA', $data['nama']);
        
		if(empty($data['data'])){
			$this->mrTemplate->AddVar('data_ip', 'IS_DATA_EMPTY', 'YES');
		} else {
			$this->mrTemplate->AddVar('data_ip', 'IS_DATA_EMPTY', 'NO');
                 $x = 1;
                $data_ip = $data['data'];
                for($i = 0 ;$i < sizeof($data_ip);$i++){
                    $data_ip[$i]['nomor'] = $data['startRec'] + $x;
                    $this->mrTemplate->AddVars('data_ip_item', $data_ip[$i], 'DATA_');
			        $this->mrTemplate->parseTemplate('data_ip_item', 'a');
                    $x++;
                
            }
		} 
	}
}