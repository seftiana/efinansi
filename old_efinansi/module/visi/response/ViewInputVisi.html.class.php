<?php
require_once GTFWConfiguration::GetValue('application','docroot').
'module/visi/business/Renstra.class.php';
require_once GTFWConfiguration::GetValue('application','docroot').
'module/visi/business/Visi.class.php';

#doc
#    classname:    ViewInputVisi
#    scope:        PUBLIC
#
#/doc

class ViewInputVisi extends HtmlResponse
{
    #    internal variables
    public $visiObj;
    public $renstraObj;
    public $data;
    public $dataList;
    public $idDec;
    public $userId;
    
    public $message;
    public $style;
    #    Constructor
    function __construct ()
    {
        $this->visiObj      = new Visi();
        $this->renstraObj   = new Renstra();
        $this->userId       = Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
        
        $this->idDec        = Dispatcher::Instance()->Decrypt($_GET['id']);
    }
    
    function TemplateModule()
    {
        $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
        'module/visi/template/');
        $this->SetTemplateFile('view_input_visi.html');
    }
    
    function ProcessRequest()
    {
        $renstra_array      = $this->renstraObj->GetComboRenstra();
        $renstra_aktif      = $this->renstraObj->GetRenstraAktif();
        $msg = Messenger::Instance()->Receive(__FILE__);
        $this->data     = $msg[0][0];
		$this->message  = $msg[0][1];
		$this->style    = $msg[0][2];
		
		if(isset($this->message))
		{
		    $renstra_select = $this->data['renstra'];
		    $this->data['id']   = $this->data['data_id'];
		}
		elseif(isset($_GET['id']) OR $_GET['id'] != '')
		{
		    $this->data         = $this->visiObj->GetDataId($this->idDec);
		    $renstra_select     = $this->data['renstra_id'];
		}
		else
		{
		    $renstra_select     = $renstra_aktif['id'];
		}
        
        Messenger::Instance()->SendToComponent(
            'combobox', 
            'Combobox', 
            'view', 
            'html',
             'renstra', 
             array(
                'renstra', 
                $renstra_array, 
                $renstra_select, 
                '-',
                 ' style="width:200px;" id="renstra"'), 
             Messenger::CurrentRequest
        );
        
        return $return;
    }
    
    function ParseTemplate($data = null)
    {
        if((isset($_GET['id']) AND $_GET['id'] != '') OR $this->data['id'] != '')
        {
            $button = "Update";
            $title  = "Ubah";
            $url    = "UpdateVisi";
        }
        else
        {
            $title  = "Tambah";
            $button = "Simpan";
            $url    = "SaveVisi";
        }
        
        $url_action = Dispatcher::Instance()->GetUrl(
            'visi',
            $url,
            'do',
            'html'
        );
        $this->mrTemplate->AddVar('content','URL_ACTION',$url_action);
        $this->mrTemplate->AddVar('content','TITLE',$title);
        $this->mrTemplate->AddVar('content','BUTTON',$button);
        
        if($this->message) {
			$this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
			$this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->message);
			$this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $this->style);
		}
		
		$this->mrTemplate->AddVar('content','VISI_KODE',$this->data['kode']);
		$this->mrTemplate->AddVar('content','VISI_NAMA',$this->data['nama']);
		$this->mrTemplate->AddVar('content','VISI_ID',$this->data['id']);
    }

}
?>
