<?php
require_once GTFWConfiguration::GetValue('application','docroot').
'module/sasaran/business/Sasaran.class.php';

#doc
#    classname:    ViewInputSasaran
#    scope:        PUBLIC
#
#/doc

class ViewInputSasaran extends HtmlResponse
{
    #    internal variables
    public $obj;
    public $post;
    public $data;
    public $data_list;
    public $style;
    public $message;
    public $id_dec;
    #    Constructor
    function __construct ()
    {
        # code...
        $this->obj      = new Sasaran();
        $this->post     = $_POST->AsArray();
        $this->id_dec   = Dispatcher::Instance()->Decrypt($_GET['id']);
    }
    
    function TemplateModule()
    {
        $this->SetTemplateBasedir(
            GTFWConfiguration::GetValue('application','docroot').
            'module/sasaran/template/'
        );
        $this->SetTemplateFile('view_input_sasaran.html');
    }
    
    function ProcessRequest()
    {
        $msg = Messenger::Instance()->Receive(__FILE__);
        $this->data     = $msg[0][0];
		$this->message  = $msg[0][1];
		$this->style    = $msg[0][2];
		
		if(isset($this->message))
		{
		    $this->data['id']   = $this->data['data_id'];
		}
		elseif(isset($_GET['id']) AND $_GET['id'] != '')
		{
		    $this->data         = $this->obj->GetDatabyId($this->id_dec);
		    //print_r($this->data);
		    //echo $this->id_dec;$this->mrTemplate->AddVar('content','KODE',$this->data['kode']);
		    //echo 'edit';
		}
		else
		{
		    # code
		}
        return $return;
    }
    
    function ParseTemplate($data = null)
    {
        if((isset($_GET['id']) AND $_GET['id'] != '') OR $this->data['id'] != '')
        {
            $button = "Update";
            $title  = "Ubah";
            $url    = "UpdateSasaran";
        }
        else
        {
            $title  = "Tambah";
            $button = "Simpan";
            $url    = "SaveSasaran";
        }
        
        $url_action = Dispatcher::Instance()->GetUrl(
            'sasaran',
            $url,
            'do',
            'html'
        );
        $url_tujuan = Dispatcher::Instance()->GetUrl(
													'sasaran',
													'PopupTujuan',
													'view',
													'html'
					  );
        $this->mrTemplate->AddVar('content','URL_ACTION',$url_action);
        $this->mrTemplate->AddVar('content','URL_TUJUAN',$url_tujuan);
        $this->mrTemplate->AddVar('content','TITLE',$title);
        $this->mrTemplate->AddVar('content','BUTTON',$button);
        
        if($this->message) {
			$this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
			$this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->message);
			$this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $this->style);
		}
		
		$this->mrTemplate->AddVar('content','NAMA',$this->data['nama']);
		$this->mrTemplate->AddVar('content','DATA_ID',$this->data['id']);
		$this->mrTemplate->AddVar('content','KODE',$this->data['kode']);
		$this->mrTemplate->AddVar('content','TUJUAN_ID',$this->data['tujuan_id']);
		$this->mrTemplate->AddVar('content','TUJUAN',$this->data['tujuan']);
    }
}
