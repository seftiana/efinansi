<?php

/**
 * 
 * class ViewTujuan
 * @package tujuan
 * @subpackage response
 * @filename ViewTujuan.html.class.php
 * @analyst nanang ruswianto <nanang@gamatechno.com>
 * @author noor hadi <noor.hadi@gamatechno.com>
 * @copyright 2012 Gamatechno Indonesia
 * @since 2 Agustus 2012
 * 
 */
 
require_once GTFWConfiguration::GetValue('application','docroot').
	'module/sasaran/business/PopupTujuan.class.php';

class ViewPopupTujuan extends HtmlResponse
{
    public $obj;
    public $data;
    public $message;
    public $style;
    public $data_list;
 
    public function __construct ()
    {
        $this->obj = new PopupTujuan();
        $this->post = $_POST->AsArray();
    }
    
    public function TemplateModule()
    {
        $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
								'module/sasaran/template/');
        
        $this->SetTemplateFile('view_popup_tujuan.html');
    }
    
    public function ProcessRequest()
    {
        if (isset($this->post['btnSearch'])){
            $kode = trim($this->post['txt_search']);
        } elseif(isset($_GET['search']) AND $_GET['search'] != ''){
            $kode = $_GET['kd'];
        } else {
            $kode = '';
        }
        
        $total_data = $this->obj->CountData($kode);
        $offset = 0;
        $limit = 20;
        $page = 0;
        
        if(isset($_GET['page'])){
			$page 		= (string) $_GET['page']->StripHtmlTags()->SqlString()->Raw();
			$offset 	= ($page - 1) * $limit;
		}
        
		$url    = Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule,
                  Dispatcher::Instance()->mSubModule,
                  Dispatcher::Instance()->mAction,
                  Dispatcher::Instance()->mType).
                  '&search='.Dispatcher::Instance()->Encrypt(1).
                  '&kd='.$kode;

        $destination_id = "popup-subcontent";
		
		#send data to pagging component
		Messenger::Instance()->SendToComponent(
											'paging', 
											'Paging', 
											'view', 
											'html', 
											'paging_top', 
											array(
												$limit,
												$total_data, 
												$url, 
												$page, 
												$destination_id
											),
											Messenger::CurrentRequest
		);
        
        $this->dataList     = $this->obj->GetData($kode,$offset,$limit);
        
        $msg = Messenger::Instance()->Receive(__FILE__);
        $this->data     = $msg[0][0];
		$this->message  = $msg[0][1];
		$this->style    = $msg[0][2];
		
		$return['txt_search']   = $kode;
		$return['start']        = $offset+1;
        return $return;
    }
    
    public function ParseTemplate($data = null)
    {
        $url_search = Dispatcher::Instance()->GetUrl(
													'sasaran',
													'PopupTujuan',
													'view',
													'html'
					  );
        $this->mrTemplate->AddVar('content','URL_SEARCH',$url_search);
        
        
        
		$this->mrTemplate->AddVar('content','TXT_SEARCH',$data['txt_search']);
		
		
		
		$data_list      = $this->dataList;
		if(empty($data_list)){
		    $this->mrTemplate->AddVar('data_grid','DATA_EMPTY','YES');
		} else {
		    $this->mrTemplate->AddVar('data_grid','DATA_EMPTY','NO');
		    
		    for ($i = 0; $i < count($data_list); $i++){
		        $data_list[$i]['nomor'] = $data['start']+$i;
		        $this->mrTemplate->AddVars('data_list',$data_list[$i],'TUJUAN_');
		        $this->mrTemplate->ParseTemplate('data_list','a');
		    }
		}
			
    }
}
