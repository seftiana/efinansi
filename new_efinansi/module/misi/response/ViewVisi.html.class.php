<?php
require_once GTFWConfiguration::GetValue('application','docroot').
'module/misi/business/Misi.class.php';
#doc
#    classname:    ViewVisi
#    scope:        PUBLIC
#
#/doc

class ViewVisi extends HtmlResponse
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
        $this->visiObj      = new Misi();
        $this->post         = $_POST->AsArray();
    }
    
    function TemplateModule()
    {
        $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
        'module/misi/template/');
        $this->SetTemplateFile('view_visi.html');
    }
    
    function TemplateBase() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') .
      'main/template/');
      $this->SetTemplateFile('document-common-popup.html');
      $this->SetTemplateFile('layout-common-popup.html');
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
        
        $total_data     = $this->visiObj->CountDataVisi($kode);
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

        $destination_id = "popup-subcontent"; # options: {popup-subcontent,subcontent-element}
		
		#send data to pagging component
		Messenger::Instance()->SendToComponent('paging', 'Paging', 'view', 'html', 'paging_top', 
		array($limit,$total_data, $url, $page, $destination_id),
		Messenger::CurrentRequest);
        
        $this->dataList     = $this->visiObj->GetDataVisi($kode,$offset,$limit);
		
		$return['txt_search']   = $kode;
		$return['start']        = $offset+1;
		return $return;
    }
    
    function ParseTemplate($data = null)
    {
        
        $url_search = Dispatcher::Instance()->GetUrl(
            'misi',
            'Visi',
            'view',
            'html'
        );
        $this->mrTemplate->AddVar('content','URL_SEARCH',$url_search);
		
		$this->mrTemplate->AddVar('content','TXT_SEARCH',$data['txt_search']);
		
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
