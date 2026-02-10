<?php
require_once GTFWConfiguration::GetValue('application','docroot').
'module/misi/business/Misi.class.php';

#doc
#    classname:    ViewMisi
#    scope:        PUBLIC
#
#/doc

class ViewMisi extends HtmlResponse
{
    #    internal variables
    public $misiObj;
    public $data;
    public $dataList;
    public $post;
    
    public $message;
    public $style;
    #    Constructor
    function __construct ()
    {
        $this->misiObj      = new Misi();
        $this->post         = $_POST->AsArray();
    }
    ###
    
    function TemplateModule()
    {
        $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
        'module/misi/template/');
        $this->SetTemplateFile('view_misi.html');
    }
    
    function ProcessRequest()
    {
        if (isset($this->post['btnSearch']))
        {
            $kode       = trim($this->post['txt_search']);
            $visi       = trim($this->post['visi_id']);
            $visi_label = trim($this->post['visi_nama']);
        }
        elseif (isset($_GET['search']) AND $_GET['search'] != '')
        {
            # code...
            $kode       = $_GET['kd'];
            $visi       = $_GET['v'];
            $visi_label = $_GET['v_l'];
        }
        else
        {
            $kode       = '';
            $visi       = '';
            $visi_label = '';
        }
        
        $total_data     = $this->misiObj->GetCountData($visi,$kode);
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
                  '&kd='.$kode.
                  '&v='.Dispatcher::Instance()->Encrypt($visi).
                  '&v_l='.$visi_label;

        $destination_id = "subcontent-element"; # options: {popup-subcontent,subcontent-element}
        
        #send data to pagging component
		Messenger::Instance()->SendToComponent('paging', 'Paging', 'view', 'html', 'paging_top', 
		array($limit,$total_data, $url, $page, $destination_id),
		Messenger::CurrentRequest);
        
        $this->dataList     = $this->misiObj->GetData($visi,$kode,$offset,$limit);
        
        $msg = Messenger::Instance()->Receive(__FILE__);
        $this->data     = $msg[0][0];
		$this->message  = $msg[0][1];
		$this->style    = $msg[0][2];
        
        $return['txt_search']   = $kode;
        $return['visi_id']      = $visi;
        $return['visi_nama']    = $visi_label;
        $return['start']        = $offset+1;
        return $return;
    }
    
    function ParseTemplate($data = null)
    {
        $url_search     = Dispatcher::Instance()->GetUrl(
            'misi',
            'Misi',
            'view',
            'html'
        );
        
        $url_add        = Dispatcher::Instance()->GetUrl(
            'misi',
            'InputMisi',
            'view',
            'html'
        );
        
        $this->mrTemplate->AddVar('content','URL_SEARCH',$url_search);
        $this->mrTemplate->AddVar('content','URL_ADD',$url_add);
        $this->mrTemplate->AddVar('content','TXT_SEARCH',$data['txt_search']);
        $this->mrTemplate->AddVar('content','VISI_ID',$data['visi_id']);
        $this->mrTemplate->AddVar('content','VISI_NAMA',$data['visi_nama']);
        $url_visi       = Dispatcher::Instance()->GetUrl(
            'misi',
            'Visi',
            'view',
            'html'
        );
        $this->mrTemplate->AddVar('content','URL_VISI',$url_visi);
        
        # delete
		$label          = "Misi";
		$urlDelete      = Dispatcher::Instance()->GetUrl(
		    'misi', 
		    'DeleteMisi', 
		    'do', 
		    'html'
		);
		$urlReturn      = Dispatcher::Instance()->GetUrl(
		    'misi', 
		    'Misi', 
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
		
		if($this->message) {
			$this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
			$this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->message);
			$this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $this->style);
		}
		
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
		        $dataList[$i]['nomor']      = $data['start']+$i;
		        if ($i % 2 == 0)
		        {
		            # code...
		            $dataList[$i]['class_name'] = 'table_common_even';
		        }
		        else
		        {
		            # code...
		            $dataList[$i]['class_name'] = '';
		        }
		        $dataList[$i]['url_edit']   = $url_add.'&id='.$dataList[$i]['id'];
		        $this->mrTemplate->AddVars('data_list',$dataList[$i],'MISI_');
		        $this->mrTemplate->parseTemplate('data_list','a');
		    }
		}
    }
}
?>
