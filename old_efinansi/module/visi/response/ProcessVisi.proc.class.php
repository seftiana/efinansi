<?php
require_once GTFWConfiguration::GetValue('application','docroot').
'module/visi/business/Visi.class.php';

#doc
#    classname:    ProcessVisi
#    scope:        PUBLIC
#
#/doc

class ProcessVisi 
{
    #    internal variables
    public $obj;
    public $post;
    public $url_input;
    public $url_return;
    protected $userId;
    
    public $cssDone = 'notebox-done';
    public $cssFail = 'notebox-warning';
    #    Constructor
    function __construct ()
    {
        # code...
        $this->obj          = new Visi();
        $this->post         = $_POST->AsArray();
        $this->userId       = Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
        $this->url_input    = Dispatcher::Instance()->GetUrl(
            'visi',
            'InputVisi',
            'view',
            'html'
        );
        $this->url_return   = Dispatcher::Instance()->GetUrl(
            'visi',
            'ListVisi',
            'view',
            'html'
        );
    }
    
    public function Check()
    {
        if(isset($this->post['btnsimpan']))
        {
            if(empty($this->post['renstra']))
            {
                $err_msg[]  = '- Renstra tidak boleh kosong';
            }
            
            if (empty($this->post['kode']))
            {
                # code...
                $err_msg[]  = '- Kode tidak boleh kosong';
            }
            
            if (empty($this->post['nama']))
            {
                # code...
                $err_msg[]  = '- Nama Tidak boleh kosong';
            }
            
            if(isset($err_msg))
            {
                $err    = implode('<br />', $err_msg);
                return array('err', $err);
            }
            else
            {
                # code...
                return true;
            }
        }
        else
        {
            # code...
            return $this->url_return;
        }
    }
    
    public function Save()
    {
        $check      = $this->Check();
        
        if (isset($this->post['btnsimpan']) AND $check === true)
        {
            $renstra_id     = trim($this->post['renstra']);
            $kode           = trim($this->post['kode']);
            $nama           = trim($this->post['nama']);
            $save           = $this->obj->DoInsertVisi(
                $renstra_id,
                $kode,
                $nama,
                $this->userId
            );
            
            if ($save)
            {
                # code...
                Messenger::Instance()->
		        Send(
				    'visi',
				    'ListVisi',
				    'view',
				    'html',
				    array(
				        $this->post,
				        'Proses Penyimpanan data berhasil di jalankan',
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
				    'visi',
				    'InputVisi',
				    'view',
				    'html',
				    array(
				        $this->post,
				        'Gagal melakukan penyimpanan data '.$save,
				        $this->cssFail
				    ),
				        Messenger::NextRequest
		        );
		        return $this->url_input;
            }
        }
        elseif ($this->obj->CheckVisibility(trim($this->post['kode']), trim($this->post['renstra'])) <> 0)
        {
            Messenger::Instance()->
		    Send(
				'visi',
				'InputVisi',
				'view',
				'html',
				array(
				    $this->post,
				    'Data yang anda masukkan sudah terdaftar dalam database',
				    $this->cssFail
				),
			    Messenger::NextRequest
	        );
	        			
	        return $this->url_input;
        }
        elseif($check[0] == 'err'){
           Messenger::Instance()->
		   Send(
				'visi',
				'InputVisi',
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
            $renstra_id     = trim($this->post['renstra']);
            $kode           = trim($this->post['kode']);
            $nama           = trim($this->post['nama']);
            $id             = trim($this->post['data_id']);
            $save           = $this->obj->DoUpdateData(
                $renstra_id,
                $kode,
                $nama,
                $this->userId,
                $id
            );
            
            if ($save)
            {
                # code...
                Messenger::Instance()->
		        Send(
				    'visi',
				    'ListVisi',
				    'view',
				    'html',
				    array(
				        $this->post,
				        'Proses Penyimpanan data berhasil di jalankan',
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
				    'visi',
				    'InputVisi',
				    'view',
				    'html',
				    array(
				        $this->post,
				        'Gagal melakukan penyimpanan data '.$save,
				        $this->cssFail
				    ),
				        Messenger::NextRequest
		        );
		        return $this->url_input;
            }
        }
        elseif($check[0] == 'err'){
           Messenger::Instance()->
		   Send(
				'visi',
				'InputVisi',
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
                $pesan[$i]  = 'ID. '.$idDelete[$i].' Kode/Nama ='.$nameDelete[$i];
            }
            
        }
        
        if ($delete)
        {
            # code...
            $message    = 'Data visi dengan keterangan di bawah ini berhasil di hapus <br />';
            $message    .= implode('<br />',$pesan);
            Messenger::Instance()->
		    Send(
				'visi',
				'ListVisi',
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
				'visi',
				'ListVisi',
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
