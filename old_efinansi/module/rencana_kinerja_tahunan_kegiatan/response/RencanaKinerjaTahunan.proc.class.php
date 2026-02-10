<?php
require_once GTFWConfiguration::GetValue('application','docroot').
'module/rencana_kinerja_tahunan_kegiatan/business/RencanaKinerjaTahunan.class.php';
#doc
#    classname:    RencanaKinerjaTahunan
#    scope:        PUBLIC
#
#/doc

class RencanaKinerjaTahunanKegiatan 
{
    #    internal variables
    public $url_input;
    public $url_return;
    public $user_id;
    public $data;
    public $obj;
    public $css_done    = 'notebox-done';
    public $css_warning = 'notebox-warning';
    #    Constructor
    function __construct ()
    {
        # code...
        $this->url_input    = Dispatcher::Instance()->GetUrl(
            'rencana_kinerja_tahunan_kegiatan',
            'InputKinerjaTahunan',
            'view',
            'html'
        );
        $this->url_return   = Dispatcher::Instance()->GetUrl(
            'rencana_kinerja_tahunan_kegiatan',
            'RencanaKinerjaTahunan',
            'view',
            'html'
        );
        $this->user_id      = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
        $this->data         = $_POST->AsArray();
        $this->obj          = new RencanaKinerjaTahunan();           
    }
    

    function Check()
    {
        $tahun_anggaran     = $this->data['tahun_anggaran'];
        $id_unit            = $this->data['id_unit'];
        $program            = $this->data['program'];
        $kegiatan           = $this->data['kegiatan'];
        $sub_kegiatan       = $this->data['sub_kegiatan'];
        $latar_belakang     = trim($this->data['latar_belakang']);
        $prioritas          = $this->data['prioritas'];
        
        if(isset($this->data['btnsimpan'])){
            if(empty($tahun_anggaran)){
                $err[]      = '- Pilih tahun anggaran';
            }
            if(empty($id_unit)){
                $err[]      = '- Pilih Unit yang sudah ada';
            }
            if(empty($program)){
                $err[]      = '- Pilih program kegiatan';
            }
            if(empty($kegiatan)){
                $err[]      = '- Pilih Kegiatan yang sudah ada';
            }
            if(empty($sub_kegiatan)){
                $err[]      = '- Pilih sub kegiatan yang sudah ada';
            }
            if(empty($latar_belakang)){
                $err[]      = '- Isikan Latar Belakang';
            }
            if(empty($prioritas)){
                $err[]      = '- Pilih prioritas';
            }
            
            if(isset($err)){
                return array('err', implode('<br />',$err));
            }else{
                return true;
            }
        }
        
        return $this->url_return;
    }
    
    function Save()
    {
        $check                  = $this->Check();
        $this->data['user_id']  = $this->user_id;
        
        if ($check === true){
            // proses penyimpanan data
            $save       = $this->obj->InsertIntoKegiatan($this->data);
            if($save === true){
                Messenger::Instance()->
		        Send(
				    'rencana_kinerja_tahunan_kegiatan',
                    'RencanaKinerjaTahunan',
                    'view',
                    'html',
				    array(
				        $this->data,
				        'Penyimpanan data berhasil di laksanakan',
				        $this->css_done
				    ),
			        Messenger::NextRequest
	            );
	            			
	            return $this->url_return;
            }else{
                Messenger::Instance()->
		        Send(
				    'rencana_kinerja_tahunan_kegiatan',
                    'InputKinerjaTahunan',
                    'view',
                    'html',
				    array(
				        $this->data,
				        'Penyimpanan data gagal di laksanakan '.$save,
				        $this->css_warning
				    ),
			        Messenger::NextRequest
	            );
	            			
	            return $this->url_input;
            }
            
        }elseif($check[0] == 'err'){
            Messenger::Instance()->
		    Send(
				'rencana_kinerja_tahunan_kegiatan',
                'InputKinerjaTahunan',
                'view',
                'html',
				array(
				    $this->data,
				    $check[1],
				    $this->css_warning
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
        $check                  = $this->Check();
        $this->data['user_id']  = $this->user_id;
        if ($check === true){
            // proses penyimpanan data
            $save       = $this->obj->UpdateDataKegiatan($this->data);
            if($save === true){
                Messenger::Instance()->
		        Send(
				    'rencana_kinerja_tahunan_kegiatan',
                    'RencanaKinerjaTahunan',
                    'view',
                    'html',
				    array(
				        $this->data,
				        'Proses update data berhasil di laksanakan',
				        $this->css_done
				    ),
			        Messenger::NextRequest
	            );
	            			
	            return $this->url_return;
            }else{
                Messenger::Instance()->
		        Send(
				    'rencana_kinerja_tahunan_kegiatan',
                    'InputKinerjaTahunan',
                    'view',
                    'html',
				    array(
				        $this->data,
				        'Proses update data gagal di laksanakan '.$save,
				        $this->css_warning
				    ),
			        Messenger::NextRequest
	            );
	            			
	            return $this->url_input;
            }
            
        }elseif($check[0] == 'err'){
            Messenger::Instance()->
		    Send(
				'rencana_kinerja_tahunan_kegiatan',
                'InputKinerjaTahunan',
                'view',
                'html',
				array(
				    $this->data,
				    $check[1],
				    $this->css_warning
				),
			    Messenger::NextRequest
	        );
	        			
	        return $this->url_input;
        }else{
            return $this->url_return;
        }
        return $this->url_return;
    }
    
    function Delete()
    {
        $idDelete   = $this->data['idDelete'];
        $nameDelete = $this->data['nameDelete'];
        for ($i = 0; $i < count($idDelete); $i++)
        {
            # code...
            list($id[$i],$id_kegiatan[$i]) = explode('|',$idDelete[$i]);
            $delete[$i]     = $this->obj->DeleteKegiatan($id[$i], $id_kegiatan[$i]);
            if($delete[$i]){
                $pesan[$i]  = 'Kode/Nama ='.str_replace('|',' ',$nameDelete[$i]);
            }
        }
        if ($delete)
        {
            # code...
            $message    = 'Data visi dengan keterangan di bawah ini berhasil di hapus <br />';
            $message    .= implode('<br />',$pesan);
            Messenger::Instance()->
		    Send(
				'rencana_kinerja_tahunan_kegiatan',
				'RencanaKinerjaTahunan',
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
				'rencana_kinerja_tahunan_kegiatan',
				'RencanaKinerjaTahunan',
				'view',
				'html',
				array(
				    '',
				    'Gagal melakukan penghapusan data',
				    $this->css_warning
				),
				Messenger::NextRequest
			);
        }
        return $this->url_return;
    }

}
?>
