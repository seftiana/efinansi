<?php

/**
 * Class ViewIndikatorProgramRef
 * @package indikator_program_ref
 * @todo Untuk menampilkan data
 * @subpackage response
 * @since 21 Juni 2012
 * @SystemAnalyst Dyan Galih Nugroho Wicaksi <galih@gamatechno.com>
 * @author noor hadi <noor.hadi@gamatechno.com>
 * @copyright 2012 Gamatechno Indonesia
 * 
 */
 
require_once GTFWConfiguration::GetValue('application', 'docroot') . 
    'module/indikator_program_ref/business/IndikatorProgramRef.class.php';

class ViewIndikatorProgramRef extends HtmlResponse
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
		$this->SetTemplateFile('view_indikator_program_ref.html');
	}
    
	public function ProcessRequest()
	{
	   $_POST = $_POST->AsArray();
       
       if(isset($_POST['btncari'])){
            $kode = $_POST['kode'];
            $nama = $_POST['nama'];
            $ipId = $_POST['ipId'];
            $ipNama = $_POST['ipNama'];
        } elseif(isset($_GET['cari'])){
            $kode = Dispatcher::Instance()->Decrypt($_GET['kode']);
            $nama = Dispatcher::Instance()->Decrypt($_GET['nama']);
            $ipId = Dispatcher::Instance()->Decrypt($_GET['ipId']);
            $ipNama = Dispatcher::Instance()->Decrypt($_GET['ipNama']);
        } else{
            $kode ='';
            $nama ='';
            $ipId = '';
            $ipNama ='';
        }           
       
       
        $msg = Messenger::Instance()->Receive(__FILE__);
		$return['pesan']	= $msg[0][1];
		$return['css'] 		= $msg[0][2];
      
  		$totalData = $this->mIndikatorProgramRef->GetDataCount($kode,$nama,$ipId);
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
                                            '&ipId=' . 
                                            Dispatcher::Instance()->Encrypt($ipId) .
										  	'&ipNama=' . 
                                            Dispatcher::Instance()->Encrypt($ipNama) .
                                            '&cari=' . Dispatcher::Instance()->Encrypt(1));

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
														$currPage), 
												Messenger::CurrentRequest);
        
        $return['data'] = $this->mIndikatorProgramRef->GetData($kode,$nama,$ipId,$startRec,$itemViewed);
        $return['kode'] = $kode;
        $return['nama'] = $nama;
        $return['ipId'] = $ipId;
        $return['ipNama'] = $ipNama;
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
                                                     'indikatorProgramRef', 
													 'view', 
                                                     'html');
                                                     
        $url_add	= Dispatcher::Instance()->GetUrl('indikator_program_ref', 
                                                     'inputIndikatorProgramRef', 
													 'view', 
                                                     'html'); 
                                                         
        $url_delete = Dispatcher::Instance()->GetUrl('indikator_program_ref', 
                                                    'indikatorKegiatanRef', 
													'do', 
                                                    'html').
                                                    '&del='. 
                                                    Dispatcher::Instance()->Encrypt(1);
                                                    
        Messenger::Instance()->Send('confirm', 'confirmDelete', 'do', 'html', 
					   array($label, $url_delete, $url_search),Messenger::NextRequest);
		$this->mrTemplate->AddVar('content', 'URL_DELETE', 
				   Dispatcher::Instance()->GetUrl('confirm', 'confirmDelete', 'do', 'html'));
                   
        $url_popup	= Dispatcher::Instance()->GetUrl('indikator_program_ref', 
                                                     'popupIndikatorProgramRef', 
													 'view', 
                                                     'html');
                                                     
        $this->mrTemplate->AddVar('content', 'URL_POPUP_IP', $url_popup);
        $this->mrTemplate->AddVar('content', 'URL_SEARCH', $url_search);
        $this->mrTemplate->AddVar('content', 'URL_ADD', $url_add);
        $this->mrTemplate->addVar('content', 'URL_RESET',$url_search);    
        $this->mrTemplate->AddVar('content', 'KODE', $data['kode']);
        $this->mrTemplate->AddVar('content', 'NAMA', $data['nama']);
        $this->mrTemplate->AddVar('content', 'IP_ID', $data['ipId']);
        $this->mrTemplate->AddVar('content', 'IP_NAMA', $data['ipNama']);

        
		if(empty($data['data'])){
			$this->mrTemplate->AddVar('data_ip', 'IS_DATA_EMPTY', 'YES');
		} else {
			$this->mrTemplate->AddVar('data_ip', 'IS_DATA_EMPTY', 'NO');
                 $x = $data['startRec'] + 1;
                 $this->mrTemplate->AddVar('content', 'FIRST_NUMBER',$x );
                
                $ipId ='';
                $ikId = '';
                $data_ip = $data['data'];
                for($i = 0 ;$i < sizeof($data_ip);){
                    if($ipId != $data_ip[$i]['ipId']){
          			    $urlAccept = 'indikator_program_ref|indikatorProgramRef|do|html-del-'.(1);
                        $urlReturn = 'indikator_program_ref|indikatorProgramRef|view|html';
			            $label = GTFWConfiguration::GetValue('language','ip');

                        $data_ip[$i]['nomor'] ='';
                        $data_ip[$i]['id'] =  $data_ip[$i]['ipId'];
                        $data_ip[$i]['kode'] = '<strong>'.$data_ip[$i]['ipKode'].'</strong>';
                        $data_ip[$i]['nama'] = '<strong>'.$data_ip[$i]['ipNama'].'</strong>';
                        $data_ip[$i]['value'] ='';
                        $data_ip[$i]['cek_display']='none';
                        $data_ip[$i]['add_display']='block';
                        $data_ip[$i]['url_add']= Dispatcher::Instance()->GetUrl('indikator_program_ref', 
                                                     'inputIndikatorKegiatanRef', 
													 'view', 
                                                     'html').
                                                     '&ipId='.
                                                     Dispatcher::Instance()->Encrypt($data_ip[$i]['ipId']);
                        $data_ip[$i]['url_edit']= Dispatcher::Instance()->GetUrl('indikator_program_ref', 
                                                     'inputIndikatorProgramRef', 
													 'view', 
                                                     'html').
                                                     '&dataId='.
                                                     Dispatcher::Instance()->Encrypt($data_ip[$i]['ipId']);
                        $data_ip[$i]['url_delete']=  Dispatcher::Instance()->GetUrl(
                                                                    'confirm', 
                                                                    'confirmDelete', 
                                                                    'do', 
                                                                    'html').
                                                                    '&urlDelete='. $urlAccept.
                                                                    '&urlReturn='.$urlReturn.
                                                                    '&id='.$data_ip[$i]['ipId'].
                                                                    '&label='.$label.
                                                                    '&dataName='.$data_ip[$i]['ipKode'].' - '.
                                                                    $data_ip[$i]['ipNama'];   
                        $this->mrTemplate->AddVars('data_ip_item', $data_ip[$i], 'DATA_');
			            $this->mrTemplate->parseTemplate('data_ip_item', 'a');
                        $ipId = $data_ip[$i]['ipId'];
                    } else{
                        if($data_ip[$i]['ikId'] !=''){
                            $urlAccept = 'indikator_program_ref|indikatorKegiatanRef|do|html-del-'.(1);
                            $urlReturn = 'indikator_program_ref|indikatorProgramRef|view|html';
                                         //'-ipId|ipNama|cari-'.
                                         //$data_ip[$i]['ipId'].'|'.$data_ip[$i]['ipNama'].'|'.(1);
			                $label = GTFWConfiguration::GetValue('language','ik');
                            $data_ip[$i]['nomor'] =  $x;
                            $data_ip[$i]['id'] =  $data_ip[$i]['ikId'];
                            $data_ip[$i]['kode'] =$data_ip[$i]['ikKode'];
                            $data_ip[$i]['nama'] =$data_ip[$i]['ikNama'];
                            $data_ip[$i]['value'] =$data_ip[$i]['ikValue'];
                            $data_ip[$i]['cek_display']='block';
                            $data_ip[$i]['add_display']='none';
                            $data_ip[$i]['url_edit']= Dispatcher::Instance()->GetUrl('indikator_program_ref', 
                                                     'inputIndikatorKegiatanRef', 
													 'view', 
                                                     'html').
                                                     '&dataId='.
                                                     Dispatcher::Instance()->Encrypt($data_ip[$i]['ikId']);
                            $data_ip[$i]['url_delete']=  Dispatcher::Instance()->GetUrl(
                                                                    'confirm', 
                                                                    'confirmDelete', 
                                                                    'do', 
                                                                    'html').
                                                                    '&urlDelete='. $urlAccept.
                                                                    '&urlReturn='.$urlReturn.
                                                                    '&id='.$data_ip[$i]['ikId'].
                                                                    '&label='.$label.
                                                                    '&dataName='.$data_ip[$i]['ikKode'].' - '.
                                                                    $data_ip[$i]['ikNama'];   
                                                     
                            $this->mrTemplate->AddVars('data_ip_item', $data_ip[$i], 'DATA_');
			                $this->mrTemplate->parseTemplate('data_ip_item', 'a');
                            $x++;                        
                        }
                        $i++;
                    }
                
            }
            $this->mrTemplate->AddVar('content', 'LAST_NUMBER', $x -1);
		} 
	}
}