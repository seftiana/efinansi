<?php

/**
 * @package alokasi_penerimaan
 * @since 27 Maret 2012
 * @copyright 2012 Gamatechno
 */ 

require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
    'module/alokasi_penerimaan/business/AlokasiPenerimaan.class.php';

class ProcessAlokasiPenerimaan 
{
    protected $mPost;
	protected $mPageView;
	protected $mPageInput;
    protected $mErrorMessage = array();
	
	/**
	 * variable css untuk menampilkan notifikasi warning
	 */
	protected $mCssDone = "notebox-done";
	protected $mCssFail = "notebox-warning";

	protected $mReturn;
	protected $mDecId;
	protected $mEncId;
   
    private $_mAlokasiPenerimaan;
   
    public function __construct()
    {	  
        $this->_mAlokasiPenerimaan = new AlokasiPenerimaan();
        $this->mPost = $_POST->AsArray();
		
		/**
		 * digunakan untuk proses update data
		 * dataId merupakan data yang diambil dari kode sub account
		 */
		$this->mDecId = Dispatcher::Instance()->Decrypt($_GET['dataId']);
		$this->mEncId = Dispatcher::Instance()->Encrypt($this->mDecId);
		
		/**
		 * sebagai tujuan halaman dalam proses redirect ke page view
		 */
		$this->mPageView = Dispatcher::Instance()->GetUrl(
					'alokasi_penerimaan', 
					'AlokasiPenerimaan', 
					'view', 
					'html');
					
		/**
		 * sebagai tujuan halaman dalam proses redirect ke page input
		 */
		$this->mPageInput = Dispatcher::Instance()->GetUrl(
					'alokasi_penerimaan',
					'InputAlokasiPenerimaan',  
					'view', 
					'html');
		 
    }
   
    /**
     * ShowMessage
     * @param string $msg : isi pesan
     * @param string $type : ( s = sukses [tipe default], w = warning/error) tipe pesan/warning
     * @param string $url : ( h = home [ default value ]), i = input ) sub modul yang di tuju
     */
    protected function ShowMessage($msg='',$type='s',$url = 'h')
    {
        if($type=='w'){
            $style = $this->mCssFail;
        }
        if($type =='s'){
            $style = $this->mCssDone;
        }
        
        if($url == 'h'){
            $subModule = 'AlokasiPenerimaan';
        }
        
        if($url == 'i'){
            $subModule = 'InputAlokasiPenerimaan';
        }
        Messenger::Instance()->Send(
						'alokasi_penerimaan', 
						$subModule, 
						'view', 
						'html', 
						array(
								$this->mPost,
								$msg, 
								$style),
                      Messenger::NextRequest);
    }
    
    public function Add () 
    {
	   
	   if(isset($_POST['btnsimpan'])){
	      //kalo yang diklik tombol simpan
		  if($this->isValidate() == true){
		  	if($this->_mAlokasiPenerimaan->IsAlokasiExist($this->mPost) == false){
                $add=$this->_mAlokasiPenerimaan->Insert($this->mPost);
                if($add) {
                        $this->ShowMessage(
                                            'Penambahan data Berhasil Dilakukan ');
                } else {
                        $this->ShowMessage('Gagal Menambah Data ','w','i');
                        return $this->mPageInput;
                }
			} else {
			     $this->ShowMessage('Alokasi Penerimaan Sudah Ada ','w','i');
                 return $this->mPageInput;
			}
		  } else {
		       $this->ShowMessage($this->GetErrorMessage(),'w','i');
		       return $this->mPageInput;
		  } 
          return $this->mPageView;
	   } else {
	      //kalo yang ditekan tombol balik
		  $urlRedirect = $this->mPageView;
	   }
	   return $urlRedirect;
	}
	
	public function Delete() 
	{
	   if(is_array($this->mPost['idDelete'])){
	       $arrId = $this->mPost['idDelete'];   
	   } else {
	       $arrId[] = $this->mPost['idDelete'];
	   }
		
		
		$deleteArrData = $this->_mAlokasiPenerimaan->DeleteArray($arrId);
		if($deleteArrData === true) {
		  $this->ShowMessage('Penghapusan Data Berhasil Dilakukan');
		} else {
			/**
			 * jika masuk disini, berarti ada salah satu atau lebih data yang gagal dihapus
			 */
			for($i=0;$i<sizeof($arrId);$i++) {
				$deleteData = false;
				$deleteData = $this->_mAlokasiPenerimaan->Delete($arrId[$i]);
				if($deleteData === true) $sukses += 1;
				else $gagal += 1;
			}
            $this->ShowMessage($gagal . ' Data Tidak Dapat Dihapus.','w');
		}
		return $this->mPageView;
	}  
	
	public function Update() 
    {  
	   if(isset($_POST['btnsimpan'])){
           if($this->isValidate() == true){
               if($this->_mAlokasiPenerimaan->IsAlokasiExist($this->mPost) == false){  
                 $update=$this->_mAlokasiPenerimaan->Update($this->mPost);
			     if($update) {
                        $this->ShowMessage('Perubahan data berhasil dilakukan ');
			     } else {
                        $this->ShowMessage('Perubahan data gagal dilakukan silahkan ulangi lagi ','w','i');
                        return $this->mPageInput;			 		  
			     }	
               } else {
                    $this->ShowMessage('Alokasi Penerimaan Sudah Ada','w','i');
                    return $this->mPageInput;
               } 
           }  else {
                $this->ShowMessage($this->GetErrorMessage(),'w','i');
                return $this->mPageInput;
           }
           return $this->mPageView; 	  
	   } else {
	      //kalo yang ditekan tombol balik
		  $urlRedirect = $this->mPageView;
	   }
	   return $urlRedirect;
	}
	
	protected function isValidate() 
    {
        if(empty($this->mPost['nama_unit'])){
            $this->mErrorMessage[] ='Unit belum diisi';
        }
       
        if(empty($this->mPost['nama_terima'])){
            $this->mErrorMessage[] ='Kode penerimaan belum diisi';
        }
        /**
        if(empty($this->mPost['alokasi_unit'])){
            $this->mErrorMessage[] ='Alokasi unit belum diisi';
        }
        if(empty($this->mPost['alokasi_pusat'])){
            $this->mErrorMessage[] ='Alokasi pusat belum diisi';
        }*/
        
        if((!empty($this->mPost['alokasi_pusat']) && !empty($this->mPost['alokasi_unit']))||
                (empty($this->mPost['alokasi_pusat']) && !empty($this->mPost['alokasi_unit'])) ||
                (!empty($this->mPost['alokasi_pusat']) && empty($this->mPost['alokasi_unit'])) 
            ){
            $jumlah_lokasi = $this->mPost['alokasi_pusat'] + $this->mPost['alokasi_unit'];
            if($jumlah_lokasi != 100){
                $this->mErrorMessage[] ='Jumlah Alokasi Unit dengan Pusat tidak 100 %';
            }
        }
        
        if(empty($this->mPost['nama_unit_pusat'])){
            $this->mErrorMessage[] ='Unit Pusat belum diisi';
        }
        
        if(count($this->mErrorMessage) > 0){
            return false;
        } else {
            return true;
        }                       
	}
    
    protected function GetErrorMessage()
    {
        if(count($this->mErrorMessage)> 0){
            $msg = implode('<br />',$this->mErrorMessage);
        } else{
            $msg ='';
        }
        return $msg;
    }
}

?>