<?php
require_once GTFWConfiguration::GetValue('application','docroot').
'module/sasaran/business/Sasaran.class.php';

#doc
#    classname:    ViewSasaran
#    scope:        PUBLIC
#
#/doc

class ViewSasaran extends HtmlResponse
{
    #    internal variables
    public $obj;
    public $data;
    public $message;
    public $style;
    public $data_list;
    
    #    Constructor
    function __construct ()
    {
        # code...
        $this->obj      = new Sasaran();
        $this->post     = $_POST->AsArray();
    }
    
    function TemplateModule()
    {
        $this->SetTemplateBasedir(
            GTFWConfiguration::GetValue('application','docroot').
            'module/sasaran/template/'
        );
        $this->SetTemplateFile('view_sasaran.html');
    }
    
    function ProcessRequest()
    {
        if (isset($this->post['btnSearch']))
        {
            $kode       = trim($this->post['txt_search']);
        }
        elseif (isset($_GET['search']) AND $_GET['search'] != '')
        {
            # code...
            $kode       = $_GET['kd'];
        }
        else
        {
            $kode       = '';
        }
        
        $total_data     = $this->obj->CountData($kode);
        $offset         = 0;
        $limit          = 20;
        $page           = 0;
        if(isset($_GET['page'])){
			$page 		= (string) $_GET['page']->StripHtmlTags()->SqlString()->Raw();
			$offset 	= ($page - 1) * $limit;
		}
        
        #paging url
		$url    = Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule,
                  Dispatcher::Instance()->mSubModule,
                  Dispatcher::Instance()->mAction,
                  Dispatcher::Instance()->mType).
                  '&search='.Dispatcher::Instance()->Encrypt(1).
                  '&kd='.$kode;

        $destination_id = "subcontent-element"; # options: {popup-subcontent,subcontent-element}
		
		#send data to pagging component
		Messenger::Instance()->SendToComponent('paging', 'Paging', 'view', 'html', 'paging_top', 
		array($limit,$total_data, $url, $page, $destination_id),
		Messenger::CurrentRequest);
        
        $this->dataList     = $this->obj->GetData($kode,$offset,$limit);
        
        $msg = Messenger::Instance()->Receive(__FILE__);
        $this->data     = $msg[0][0];
		$this->message  = $msg[0][1];
		$this->style    = $msg[0][2];
		
		$return['txt_search']   = $kode;
		$return['start']        = $offset+1;
        return $return;
    }
    
    function ParseTemplate($data = null)
    {
        $url_search     = Dispatcher::Instance()->GetUrl(
            'sasaran',
            'Sasaran',
            'view',
            'html'
        );

        $url_reset     = Dispatcher::Instance()->GetUrl(
            'sasaran',
            'Sasaran',
            'view',
            'html'
        );
                
        $url_add        = Dispatcher::Instance()->GetUrl(
            'sasaran',
            'InputSasaran',
            'view',
            'html'
        );
        
        $this->mrTemplate->AddVar('content','URL_SEARCH',$url_search);
        $this->mrTemplate->AddVar('content','URL_RESET',$url_reset);
        $this->mrTemplate->AddVar('content','URL_ADD',$url_add);
        
        # error
        if($this->message) {
			$this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
			$this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->message);
			$this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $this->style);
		}
		
		$this->mrTemplate->AddVar('content','TXT_SEARCH',$data['txt_search']);
		
		# delete
		$label          = "Sasaran";
		$urlDelete      = Dispatcher::Instance()->GetUrl(
		    'sasaran', 
		    'DeleteSasaran', 
		    'do', 
		    'html'
		);
		$urlReturn      = Dispatcher::Instance()->GetUrl(
		    'sasaran', 
		    'Sasaran', 
		    'view', 
		    'html'
		);
		Messenger::Instance()->Send(
		    'confirm', 
		    'confirmDelete', 
		    'do', 
		    'html', 
		    array(
		        $label, 
		        $urlDelete, 
		        $urlReturn
		    ),
		    Messenger::NextRequest
		);
			
		$this->mrTemplate->AddVar(
		    'content', 
		    'URL_DELETE', 
		    Dispatcher::Instance()->GetUrl(
		        'confirm', 
		        'confirmDelete', 
		        'do', 
		        'html'
		    )
		);
		
		$data_list      = $this->dataList;
		if(empty($data_list))
		{
		    $this->mrTemplate->AddVar('data_grid','DATA_EMPTY','YES');
		}
		else
		{
		    $this->mrTemplate->AddVar('data_grid','DATA_EMPTY','NO');
		    
		    for ($i = 0; $i < count($data_list); $i++)
		    {
		        $data_list[$i]['nomor']     = $data['start']+$i;
		        $data_list[$i]['url_edit']  = $url_add.'&id='.Dispatcher::Instance()->Encrypt(
		            $data_list[$i]['id']
		        );
		        $this->mrTemplate->AddVars('data_list',$data_list[$i],'SASARAN_');
		        $this->mrTemplate->ParseTemplate('data_list','a');
		    }
		}
			
    }
}
?>
