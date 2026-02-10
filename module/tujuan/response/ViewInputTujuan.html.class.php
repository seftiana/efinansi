<?php

/**
 * 
 * class ViewInputTujuan
 * @package tujuan
 * @subpackage response
 * @filename ViewInputTujuan.html.class.php
 * @analyst nanang ruswianto <nanang@gamatechno.com>
 * @author noor hadi <noor.hadi@gamatechno.com>
 * @copyright 2012 Gamatechno Indonesia
 * @since 2 Agustus 2012
 * 
 */

require_once GTFWConfiguration::GetValue('application','docroot').
	'module/tujuan/business/Tujuan.class.php';


class ViewInputTujuan extends HtmlResponse
{
    public $obj;
    public $post;
    public $data;
    public $data_list;
    public $style;
    public $message;
    public $id_dec;
 
    public function __construct ()
    {
        $this->obj      = new Tujuan();
        $this->post     = $_POST->AsArray();
        $this->id_dec   = Dispatcher::Instance()->Decrypt($_GET['id']);
    }
    
    public function TemplateModule()
    {
        $this->SetTemplateBasedir(
            GTFWConfiguration::GetValue('application','docroot').
            'module/tujuan/template/'
        );
        $this->SetTemplateFile('view_input_tujuan.html');
    }
    
    public function ProcessRequest()
    {
        $msg = Messenger::Instance()->Receive(__FILE__);
        $this->data     = $msg[0][0];
		$this->message  = $msg[0][1];
		$this->style    = $msg[0][2];
		
		if(isset($this->message)){
		    $this->data['id']   = $this->data['data_id'];
		} elseif (isset($_GET['id']) AND $_GET['id'] != ''){
		    $this->data         = $this->obj->GetDatabyId($this->id_dec);
		} else {
		    # code
		}
		
        return $return;
    }
    
    public function ParseTemplate($data = null)
    {
        if((isset($_GET['id']) AND $_GET['id'] != '') OR $this->data['id'] != ''){
            $button = "Update";
            $title  = "Ubah";
            $url    = "UpdateTujuan";
        } else {
            $title  = "Tambah";
            $button = "Simpan";
            $url    = "AddTujuan";
        }
        
        $url_action = Dispatcher::Instance()->GetUrl(
													'tujuan',
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
		$this->mrTemplate->AddVar('content','KODE',$this->data['kode']);
		$this->mrTemplate->AddVar('content','NAMA',$this->data['nama']);
		$this->mrTemplate->AddVar('content','DATA_ID',$this->data['id']);
    }
}
