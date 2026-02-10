<?php

/**
 * 
 * class ProcessTujuan
 * @package tujuan
 * @subpackage response
 * @filename ProcessTujuan.proc.class.php
 * @analyst nanang ruswianto <nanang@gamatechno.com>
 * @author noor hadi <noor.hadi@gamatechno.com>
 * @copyright 2012 Gamatechno Indonesia
 * @since 2 Agustus 2012
 * 
 */
 
require_once GTFWConfiguration::GetValue('application','docroot').
	'module/tujuan/business/Tujuan.class.php';

class ProcessTujuan
{

    public $obj;
    public $url_input;
    public $url_return;
    public $data;
    protected $user_id;
    
    public $css_done    = "notebox-done";
    public $css_fail    = "notebox-warning";


    public function __construct ()
    {
        $this->obj = new Tujuan();
        $this->url_input = Dispatcher::Instance()->GetUrl(
														'tujuan',
														'InputTujuan',
														'view',
														'html'
							);
							
        $this->url_return = Dispatcher::Instance()->GetUrl(
														'tujuan',
														'Tujuan',
														'view',
														'html'
							);
        
        $this->data         = $_POST->AsArray();
        $this->user_id      = Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
    }
    
    public function Check()
    {
        if(isset($this->data['btnsimpan'])){
            
            if (empty($this->data['kode'])){
                $err_msg[]  = 'Kode tidak boleh kosong';
            }
            
            if (empty($this->data['nama'])){
                $err_msg[]  = 'Uraian Tidak boleh kosong';
            }
            
            if(isset($err_msg)){
                $err    = implode('<br />', $err_msg);
                return array('err', $err);
            } else {
                return true;
            }
        }
    }
    
    public function Add()
    {
        $check      = $this->Check();
        
        if (isset($this->data['btnsimpan']) AND $check === true){
            $kode = trim($this->data['kode']);
            $nama = trim($this->data['nama']);
            $add = $this->obj->Add($kode,$nama,$this->user_id);
            
            if ($add){
                Messenger::Instance()->Send(
											'tujuan',
											'Tujuan',
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
            } else {
                Messenger::Instance()->Send(
											'tujuan',
											'InputTujuan',
											'view',
											'html',
											array(
												$this->data,
												'Gagal melakukan penyimpanan data '.$add,
												$this->css_fail
											),
											Messenger::NextRequest
		        );
		        return $this->url_input;
            }
            
        } elseif($check[0] == 'err'){
           Messenger::Instance()->Send(
									'tujuan',
									'InputTujuan',
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
        } else {
            return $this->url_return;
        }
        
        return $this->url_return;
    }
    
    public function Update()
    {
        $check      = $this->Check();
        
        if (isset($this->data['btnsimpan']) AND $check === true){
            $kode = trim($this->data['kode']);
            $nama = trim($this->data['nama']);
            $data_id = trim($this->data['data_id']);
            $update = $this->obj->Update($kode,$nama,$this->user_id, $data_id);
            
            if ($update){
                Messenger::Instance()->Send(
											'tujuan',
											'Tujuan',
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
            } else {
                Messenger::Instance()->Send(
											'tujuan',
											'InputTujuan',
											'view',
											'html',
											array(
												$this->data,
												'Gagal melakukan update data '.$update,
												$this->css_fail
											),
											Messenger::NextRequest
		        );
		        return $this->url_input;
            }
            
        } elseif($check[0] == 'err') {
           Messenger::Instance()->Send(
									'tujuan',
									'InputTujuan',
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
        } else {
            return $this->url_return;
        }
        return $this->url_return;
    }
    
    /***
     * 
    public function Delete()
    {
        $idDelete   = $this->data['idDelete'];
        $nameDelete = $this->data['nameDelete'];
        
        for ($i = 0; $i < sizeof($idDelete); $i++){
            
            $delete[$i] = $this->obj->Delete($idDelete[$i]);
            if($delete[$i]) {
                $pesan[$i]  = 'ID. '.$idDelete[$i].' Kode/Nama ='.$nameDelete[$i];
            }
        }
        
        if ($delete){
            $message = 'Data sasaran dengan keterangan di bawah ini berhasil di hapus <br />';
            $message .= implode('<br />',$pesan);
            Messenger::Instance()->Send(
										'tujuan',
										'Tujuan',
										'view',
										'html',
										array(
												'',
												$message,
												$this->css_done
										),
										Messenger::NextRequest
			);
        } else {
            Messenger::Instance()->Send(
									'tujuan',
									'Tujuan',
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
    **/
    
    public function Delete()
    {
		$arrId = $this->data['idDelete'];
		
		$deleteArrData = $this->obj->DeleteByArrayId($arrId);
		if($deleteArrData === true) {
			Messenger::Instance()->Send(
					'tujuan', 
					'Tujuan', 
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
						'tujuan', 
						'Tujuan', 
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

}
