<?php
require_once GTFWConfiguration::GetValue('application','docroot').
'module/misi/business/Misi.class.php';

#doc
#    classname:    ViewInputMisi
#    scope:        PUBLIC
#
#/doc

class ViewInputMisi extends HtmlResponse
{
    #    internal variables
    public $obj;
    public $data;
    public $message;
    public $style;
    public $idDec;
    #    Constructor
    function __construct ()
    {
        $this->obj      = new Misi();
        $this->idDec    = Dispatcher::Instance()->Decrypt($_GET['id']);
    }
    ###
    
    function TemplateModule()
    {
        $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
        'module/misi/template/');
        $this->SetTemplateFile('view_input_misi.html');
    }
    
    function ProcessRequest()
    {
        $msg = Messenger::Instance()->Receive(__FILE__);
        $this->data     = $msg[0][0];
		$this->message  = $msg[0][1];
		$this->style    = $msg[0][2];
		
		if($this->message){
		    $this->data['id']   = $this->data['data_id'];
		}elseif(isset($_GET['id'])){
		    $this->data         = $this->obj->GetDataById($this->idDec);
		}else{
		    $this->data         = '';
		}
		
        return $return;
    }
    
    function ParseTemplate($data = null)
    {
        if((isset($_GET['id']) AND $_GET['id'] != '') OR $this->data['id'] != '')
        {
            $action     = 'UpdateMisi';
            $title      = 'Ubah';
            $button     = 'Update';
        }
        else
        {
            $action     = 'SaveMisi';
            $title      = 'Tambah';
            $button     = 'Simpan';
        }
        $url_action     = Dispatcher::Instance()->GetUrl(
            'misi',
            $action,
            'do',
            'html'
        );
        $this->mrTemplate->AddVar('content','URL_ACTION',$url_action);
        $this->mrTemplate->AddVar('content','BUTTON',$button);
        $this->mrTemplate->AddVar('content','TITLE',$title);
        $url_visi       = Dispatcher::Instance()->GetUrl(
            'misi',
            'Visi',
            'view',
            'html'
        );
        $this->mrTemplate->AddVar('content','URL_VISI',$url_visi);
        if($this->message) {
			$this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
			$this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->message);
			$this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $this->style);
		}
        
        $this->mrTemplate->AddVar('content','VISI_ID',$this->data['visi_id']);
        $this->mrTemplate->AddVar('content','VISI_NAMA',$this->data['visi_nama']);
        $this->mrTemplate->AddVar('content','MISI_KODE',$this->data['kode']);
        $this->mrTemplate->AddVar('content','MISI_NAMA',$this->data['nama']);
        $this->mrTemplate->AddVar('content','MISI_ID',$this->data['id']);
    }

}
?>
