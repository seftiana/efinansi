<?php
require_once GTFWConfiguration::GetValue('application','docroot').
'module/visi/business/Renstra.class.php';
require_once GTFWConfiguration::GetValue('application','docroot').
'module/visi/business/Visi.class.php';

#doc
#    classname:    ViewListVisi
#    scope:        PUBLIC
#
#/doc

class ViewListVisi extends HtmlResponse
{
    #    internal variables
    public $visiObj;
    public $renstraObj;
    public $data;
    public $dataList;
    public $post;
    
    public $message;
    public $style;
    #    Constructor
    function __construct ()
    {
        # code...
        $this->visiObj      = new Visi();
        $this->renstraObj   = new renstra();
        $this->post         = $_POST->AsArray();
    }
    
    
    function TemplateModule()
    {
        $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
        'module/visi/template/');
        $this->SetTemplateFile('view_list_visi.html');
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
        
        $total_data     = $this->visiObj->CountData($kode);
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
        
        $this->dataList     = $this->visiObj->GetData($kode,$offset,$limit);
        
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
        $url_add    = Dispatcher::Instance()->GetUrl(
            'visi',
            'InputVisi',
            'view',
            'html'
        );
        $url_search = Dispatcher::Instance()->GetUrl(
            'visi',
            'ListVisi',
            'view',
            'html'
        );
        $this->mrTemplate->AddVar('content','URL_SEARCH',$url_search);
        $this->mrTemplate->AddVar('content','URL_ADD',$url_add);
        
        # error
        if($this->message) {
			$this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
			$this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->message);
			$this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $this->style);
		}
		
		$this->mrTemplate->AddVar('content','TXT_SEARCH',$data['txt_search']);
		
		# delete
		$label          = "Visi";
		$urlDelete      = Dispatcher::Instance()->GetUrl(
		    'visi', 
		    'DeleteVisi', 
		    'do', 
		    'html'
		);
		$urlReturn      = Dispatcher::Instance()->GetUrl(
		    'visi', 
		    'ListVisi', 
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
		
		$dataList       = $this->dataList;
		if(empty($dataList))
		{
		    $this->mrTemplate->AddVar('data_grid','DATA_EMPTY','YES');
		}
		else
		{
		    $this->mrTemplate->AddVar('data_grid','DATA_EMPTY','NO');
		    
		    for ($i = 0; $i < count($dataList); $i++)
		    {
		        # code...
		        $dataList[$i]['nomor']  = $data['start']+$i;
		        if ($i % 2 == 0)
		        {
		            # code...
		            $dataList[$i]['class_name'] = "table-common-even";
		        }
		        else
		        {
		            # code...
		            $dataList[$i]['class_name'] = "";
		        }
		        
		        $dataList[$i]['url_edit']   = $url_add.'&id='.$dataList[$i]['id'];
		        
		        if($dataList[$i]['misi'] <> 0){
                    $dataList[$i]['disabled']   = 'disabled';
                    $dataList[$i]['onclick']    = 'onclick="javascript:alert(\'Disable dulu\')"';
                }else{
                    $dataList[$i]['disabled']   = '';
                    $dataList[$i]['onclick']    = "";
                }
		        
		        $this->mrTemplate->AddVars('data_list',$dataList[$i],'VISI_');
		        $this->mrTemplate->parseTemplate('data_list','a');
		    }
		}
    }
}

?>
