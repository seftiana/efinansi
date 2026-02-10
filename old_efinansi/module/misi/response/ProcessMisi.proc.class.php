<?php
require_once GTFWConfiguration::GetValue('application','docroot').
'module/misi/business/Misi.class.php';

#doc
#    classname:    ProcessMisi
#    scope:        PUBLIC
#
#/doc

class ProcessMisi 
{
    #    internal variables
    public $url_input;
    public $url_return;
    public $obj;
    public $post;
    protected $user_id;
    
    public $cssDone = 'notebox-done';
    public $cssFail = 'notebox-warning';
    #    Constructor
    function __construct ()
    {
        $this->obj          = new Misi();
        $this->post         = $_POST->AsArray();
        $this->user_id      = Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
        $this->url_input    = Dispatcher::Instance()->GetUrl(
            'misi',
            'InputMisi',
            'view',
            'html'
        );
        $this->url_return   = Dispatcher::Instance()->GetUrl(
            'misi',
            'Misi',
            'view',
            'html'
        );
        
    }
    
    public function Check()
    {
        if(isset($this->post['btnsimpan']))
        {
            if(empty($this->post['visi_id']))
            {
                $err_msg[]  = '- Pilih visi';
            }
            
            if(empty($this->post['kode']))
            {
                $err_msg[]  = '- Isikan kode misi';
            }
            
            if(empty($this->post['nama']))
            {
                $err_msg[]  = '- Nama misi tidak boleh kosong';
            }
            
            if (isset($err_msg))
            {
                # code...
                return array('err',implode('<br />',$err_msg));
            }
            else
            {
                # code...
                return true;
            }
        }
        else
        {
            return $this->url_return;
        }
    }
    
    public function Save()
    {
        $check      = $this->Check();
        
        if (isset($this->post['btnsimpan']) AND $check === true)
        {
            $visi_id        = $this->post['visi_id'];
            $kode           = trim($this->post['kode']);
            $nama           = trim($this->post['nama']);
            $save           = $this->obj->DoInsertData($visi_id,$kode,$nama,$this->user_id);
            if ($save)
            {
                # code...
                Messenger::Instance()->
		        Send(
				    'misi',
				    'Misi',
				    'view',
				    'html',
				    array(
				        $this->post,
				        'Proses penyimpanan data berhasil di jalankan',
				        $this->cssDone
				    ),
				    Messenger::NextRequest
			    );
			
			    return $this->url_return;
            }
            else
            {
                # code...
                Messenger::Instance()->
		        Send(
				    'misi',
				    'InputMisi',
				    'view',
				    'html',
				    array(
				        $this->post,
				        'Proses penyimpanan data gagal di jalankan '.$save,
				        $this->cssFail
				    ),
				    Messenger::NextRequest
			    );
			
			    return $this->url_input;
            }
            
        }
        elseif ($check[0] == 'err')
        {
            # code...
            Messenger::Instance()->
		    Send(
				'misi',
				'InputMisi',
				'view',
				'html',
				array(
				    $this->post,
				    $check[1],
				    $this->cssFail
				),
				Messenger::NextRequest
			);
				
			return $this->url_input;
        }
        return $this->url_return;
    }
    
    public function Update()
    {
        $check      = $this->Check();
        
        if (isset($this->post['btnsimpan']) AND $check === true)
        {
            $data_id        = $this->post['data_id'];
            $visi_id        = $this->post['visi_id'];
            $kode           = trim($this->post['kode']);
            $nama           = trim($this->post['nama']);
            $save           = $this->obj->DoUpdateData($visi_id,$kode,$nama,$this->user_id,$data_id);
            if ($save)
            {
                # code...
                Messenger::Instance()->
		        Send(
				    'misi',
				    'Misi',
				    'view',
				    'html',
				    array(
				        $this->post,
				        'Proses update data berhasil di jalankan',
				        $this->cssDone
				    ),
				    Messenger::NextRequest
			    );
			
			    return $this->url_return;
            }
            else
            {
                # code...
                Messenger::Instance()->
		        Send(
				    'misi',
				    'InputMisi',
				    'view',
				    'html',
				    array(
				        $this->post,
				        'Proses Update data gagal di jalankan '.$save,
				        $this->cssFail
				    ),
				    Messenger::NextRequest
			    );
			
			    return $this->url_input;
            }
            
        }
        elseif ($check[0] == 'err')
        {
            # code...
            Messenger::Instance()->
		    Send(
				'misi',
				'InputMisi',
				'view',
				'html',
				array(
				    $this->post,
				    $check[1],
				    $this->cssFail
				),
				Messenger::NextRequest
			);
				
			return $this->url_input;
        }
        return $this->url_return;
    }
    
    public function Delete()
    {
        $idDelete   = $this->post['idDelete'];
        $nameDelete = $this->post['nameDelete'];
        
        
        for ($i = 0; $i < sizeof($idDelete); $i++)
        {
            # code...
            $delete[$i]     = $this->obj->DeleteData($idDelete[$i]);
            if($delete[$i]){
                $pesan[$i]  = 'ID. '.$idDelete[$i].' Kode/Nama = '.$nameDelete[$i];
            }
            
        }
        
        if ($delete)
        {
            # code...
            $message    = 'Data misi dengan keterangan di bawah ini berhasil di hapus <br />';
            if(isset($pesan)){
                $message    .= implode('<br />',$pesan);
            }
            Messenger::Instance()->
		    Send(
				'misi',
				'Misi',
				'view',
				'html',
				array(
				    '',
				    $message,
				    $this->cssDone
				),
				Messenger::NextRequest
			); 
        }else{
            Messenger::Instance()->
		    Send(
				'misi',
				'Misi',
				'view',
				'html',
				array(
				    '',
				    'Gagal melakukan penghapusan data',
				    $this->cssFail
				),
				Messenger::NextRequest
			);
        }
        return $this->url_return;
    }
    
}
?>
