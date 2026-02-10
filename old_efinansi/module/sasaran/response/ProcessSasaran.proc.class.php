<?php
require_once GTFWConfiguration::GetValue('application','docroot').
'module/sasaran/business/Sasaran.class.php';

#doc
#    classname:    ProcessSasaran
#    scope:        PUBLIC
#
#/doc

class ProcessSasaran 
{
    #    internal variables
    public $obj;
    public $url_input;
    public $url_return;
    public $data;
    protected $user_id;
    
    public $css_done    = "notebox-done";
    public $css_fail    = "notebox-warning";
    #    Constructor
    function __construct ()
    {
        $this->obj          = new Sasaran();
        $this->url_input    = Dispatcher::Instance()->GetUrl(
            'sasaran',
            'InputSasaran',
            'view',
            'html'
        );
        $this->url_return   = Dispatcher::Instance()->GetUrl(
            'sasaran',
            'Sasaran',
            'view',
            'html'
        );
        
        $this->data         = $_POST->AsArray();
        $this->user_id      = Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
    }
    
    function Check()
    {
        if(isset($this->data['btnsimpan']))
        {
            
            if (empty($this->data['kode']))
            {
                # code...
                $err_msg[]  = 'Kode tidak boleh kosong';
            }

			if (empty($this->data['tujuan']))
            {
                # code...
                $err_msg[]  = 'Tujuan Tidak boleh kosong';
            }            
            
            if (empty($this->data['nama']))
            {
                # code...
                $err_msg[]  = 'Uraian Tidak boleh kosong';
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
    }
    
    function Save()
    {
        $check      = $this->Check();
        
        if (isset($this->data['btnsimpan']) AND $check === true)
        {
            $kode = trim($this->data['kode']);
            $nama = trim($this->data['nama']);
            $tujuan_id = trim($this->data['tujuan_id']);
            $save = $this->obj->InsertIntoSasaran($kode,$nama,$tujuan_id,$this->user_id);
            
            if ($save)
            {
                # code...
                Messenger::Instance()->
		        Send(
				    'sasaran',
				    'Sasaran',
				    'view',
				    'html',
				    array(
				        $this->data,
				        'Proses Penyimpanan data berhasil di jalankan',
				        $this->css_done
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
				    'sasaran',
				    'InputSasaran',
				    'view',
				    'html',
				    array(
				        $this->data,
				        'Gagal melakukan penyimpanan data '.$save,
				        $this->css_fail
				    ),
				        Messenger::NextRequest
		        );
		        return $this->url_input;
            }
            
        }
        elseif($check[0] == 'err')
        {
           Messenger::Instance()->
		   Send(
				'sasaran',
				'InputSasaran',
				'view',
				'html',
				array(
				    $this->data,
				    $check[1],
				    $this->css_fail
				),
				Messenger::NextRequest
			);
				
			return $this->url_input;
        }else{
            return $this->url_return;
        }
        return $this->url_return;
    }
    
    function Update()
    {
        $check      = $this->Check();
        
        if (isset($this->data['btnsimpan']) AND $check === true)
        {
            $kode           = trim($this->data['kode']);
            $nama           = trim($this->data['nama']);
            $data_id        = trim($this->data['data_id']);
            $tujuan_id 	= trim($this->data['tujuan_id']);
            $save           = $this->obj->UpdateSasaran($kode,$nama,$tujuan_id,$this->user_id,$data_id);
            
            if ($save)
            {
                # code...
                Messenger::Instance()->
		        Send(
				    'sasaran',
				    'Sasaran',
				    'view',
				    'html',
				    array(
				        $this->data,
				        'Proses Update data berhasil di jalankan',
				        $this->css_done
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
				    'sasaran',
				    'InputSasaran',
				    'view',
				    'html',
				    array(
				        $this->data,
				        'Gagal melakukan update data '.$save,
				        $this->css_fail
				    ),
				        Messenger::NextRequest
		        );
		        return $this->url_input;
            }
            
        }
        elseif($check[0] == 'err')
        {
           Messenger::Instance()->
		   Send(
				'sasaran',
				'InputSasaran',
				'view',
				'html',
				array(
				    $this->data,
				    $check[1],
				    $this->css_fail
				),
				Messenger::NextRequest
			);
				
			return $this->url_input;
        }else{
            return $this->url_return;
        }
        return $this->url_return;
    }
    
    /**
     * old method
     *
    function Delete()
    {
        $idDelete   = $this->data['idDelete'];
        $nameDelete = $this->data['nameDelete'];
        
        for ($i = 0; $i < sizeof($idDelete); $i++)
        {
            # code...
            $delete[$i]     = $this->obj->DeleteSasaran($idDelete[$i]);
            if($delete[$i]){
                $pesan[$i]  = 'ID. '.$idDelete[$i].' Kode/Nama ='.$nameDelete[$i];
            }
            
        }
        
        if ($delete)
        {
            # code...
            $message    = 'Data sasaran dengan keterangan di bawah ini berhasil di hapus <br />';
            $message    .= implode('<br />',$pesan);
            Messenger::Instance()->
		    Send(
				'sasaran',
				'Sasaran',
				'view',
				'html',
				array(
				    '',
				    $message,
				    $this->css_done
				),
				Messenger::NextRequest
			);
        }else{
            Messenger::Instance()->
		    Send(
				'sasaran',
				'Sasaran',
				'view',
				'html',
				array(
				    '',
				    'Gagal melakukan penghapusan data',
				    $this->css_fail
				),
				Messenger::NextRequest
			);
        }
        return $this->url_return;
    }
    */
    
    /**
     * new method
     */
    public function Delete()
    {
		$arrId = $this->data['idDelete'];
		
		$deleteArrData = $this->obj->DeleteByArrayId($arrId);
		if($deleteArrData === true) {
			Messenger::Instance()->Send(
					'sasaran', 
					'Sasaran', 
					'view', 
					'html', 
					array(
						$this->data,
						'Penghapusan Data Berhasil Dilakukan', 
						$this->css_done),
					Messenger::NextRequest);
		} else {
			/**
			 * jika masuk disini, berarti ada salah satu atau lebih data yang gagal dihapus
			 */
			for($i=0;$i<sizeof($arrId);$i++) {
				$deleteData = false;
				$deleteData = $this->obj->Delete($arrId[$i]);
				if($deleteData === true) $sukses += 1;
				else $gagal += 1;
			}
			Messenger::Instance()->Send(
						'sasaran', 
						'Sasaran', 
						'view', 
						'html', 
						array(
							$this->data, 
							$gagal . ' Data Tidak Dapat Dihapus.', 
							$this->css_fail),
						Messenger::NextRequest);
		}
		return $this->url_return;
	}
	/*
	 * end
	 */     
}
