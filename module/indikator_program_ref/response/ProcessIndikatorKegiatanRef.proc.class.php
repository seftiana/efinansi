<?php

/**
 * Class ProcessIndikatorKegiatanRef
 * @package indikator_program_ref
 * @todo Untuk proses data indikator kegiatan
 * @subpackage response
 * @since 21 Juni 2012
 * @SystemAnalyst Dyan Galih Nugroho Wicaksi <galih@gamatechno.com>
 * @author noor hadi <noor.hadi@gamatechno.com>
 * @copyright 2012 Gamatechno Indonesia
 * 
 */
 
require_once GTFWConfiguration::GetValue('application', 'docroot') .
    'module/indikator_program_ref/business/IndikatorKegiatanRef.class.php';

class ProcessIndikatorKegiatanRef 
{
    protected $mPost;
    protected $mIndikatorKegiatanRef;
    protected $mPageView;
    protected $mPageInput;
    
    protected $mCssDone = "notebox-done";
	protected $mCssFail = "notebox-warning";

	protected $mReturn;
	protected $mDecId;
	protected $mEncId;

    public function __construct() 
    {
	   $this->mIndikatorKegiatanRef 	= new IndikatorKegiatanRef();
	   $this->mPost 	= $_POST->AsArray();
	   $this->decId 	= Dispatcher::Instance()->Decrypt($_REQUEST['dataId']);
	   $this->encId 	= Dispatcher::Instance()->Encrypt($this->decId);
	   $this->mPageView = Dispatcher::Instance()->GetUrl(
                                                        'indikator_program_ref', 
                                                        'indikatorProgramRef', 
                                                        'view', 
                                                        'html');
	   $this->mPageInput = Dispatcher::Instance()->GetUrl(
                                                        'indikator_program_ref', 
                                                        'inputIndikatorKegiatanRef', 
                                                        'view', 
                                                        'html');
                                                              
    }

    public function Check() 
    {
	   if(isset($this->mPost['btnsimpan'])){
	   	   if(trim($this->mPost['kode']) == ""){
			     return "emptyKode";
            }elseif(trim($this->mPost['nama']) == ""){
			     return "emptyNama";
            }elseif(trim($this->mPost['value']) == ""){
			     return "emptyNilai";
            }elseif(
                ($this->mPost['kode']!='' && empty($this->mPost['dataId'])) ||
                (!empty($this->mPost['dataId']) && ($this->mPost['kode'] != $this->mPost['kodeLama']))){
                    if($this->mIndikatorKegiatanRef->GetCountKode($this->mPost['kode']) > 0){
                        return "existKode";
                    }else {
					   return true;
				    }
            }else{
			     return true;	
            }
	   }
	   return false;
    }

    public function Add() 
    {
        $cek = $this->Check();
        if($cek === true) {
            $add = $this->mIndikatorKegiatanRef->Add($this->mPost);		
            if($add === true) {
                $this->ShowMessage('Penambahan data Berhasil Dilakukan');
            }else {
			    $this->ShowMessage('Gagal Menambah Data','fail');
            }
	   }elseif($cek == "emptyNama") {
	       $this->ShowMessage('Nama wajib diisi','fail','inputIndikatorKegiatanRef');
           return $this->mPageInput.'&ipId='.$this->mPost['ipId'];
	   }elseif($cek == "emptyKode") {
	       $this->ShowMessage('Kode wajib diisi','fail','inputIndikatorKegiatanRef');
	       return $this->mPageInput.'&ipId='.$this->mPost['ipId'];
	   }elseif($cek == "emptyNilai") {
	       $this->ShowMessage('Nilai wajib diisi','fail','inputIndikatorKegiatanRef');
	       return $this->mPageInput.'&ipId='.$this->mPost['ipId'];
	   }elseif($cek == "existKode") {
	       $this->ShowMessage('Kode -'.$this->mPost['kode'].'- sudah ada','fail','inputIndikatorKegiatanRef');
	       return $this->mPageInput.'&ipId='.$this->mPost['ipId'];
	   }
    
       if($this->mPost['btnbalik']){
	       $url ='';
       } else {
            $url ='&ipId='.$this->mPost['ipId'].'&ipNama='.$this->mPost['ipNama'].'&cari=1';
       }
       return $this->mPageView.$url;
       
    }

	public function Update() 
    {
		$cek = $this->Check();
		if($cek === true) {
			$update	= $this->mIndikatorKegiatanRef->Update($this->mPost);
			if($update){
                $this->ShowMessage('Perubahan Data berhasil di lakukan');
			} else { 
			    $this->ShowMessage('Perubahan Data Gagal di lakukan','fail');
			}
		
	   }elseif($cek == "emptyNama") {
	       $this->ShowMessage('Nama wajib diisi','fail','inputIndikatorKegiatanRef');
           return $this->mPageInput;
	   }elseif($cek == "emptyKode") {
	       $this->ShowMessage('Kode wajib diisi','fail','inputIndikatorKegiatanRef');
	       return $this->mPageInput;
	   }elseif($cek == "emptyNilai") {
	       $this->ShowMessage('Nilai wajib diisi','fail','inputIndikatorKegiatanRef');
	       return $this->mPageInput;
	   }elseif($cek == "existKode") {
	       $this->ShowMessage('Kode -'.$this->mPost['kode'].'- sudah ada','fail','inputIndikatorKegiatanRef');
	       return $this->mPageInput;
	   }
       if($this->mPost['btnbalik']){
	       $url ='';
       } else {
            $url ='&ipId='.$this->mPost['ipId'].'&ipNama='.$this->mPost['ipNama'].'&cari=1';
       }
       return $this->mPageView.$url;
	}

    protected function ShowMessage($pesan,$status='done',$subModul='indikatorProgramRef')
    {
            if($status =='done'){
                $style = $this->mCssDone;
            }elseif($status =='fail'){
                $style = $this->mCssFail;
            }
            
                    
        	Messenger::Instance()->Send(
                                        'indikator_program_ref', 
                                        $subModul, 
                                        'view', 
                                        'html', 
                                        array(
                                                $this->mPost,
                                                $pesan, 
                                                $style),
                                        Messenger::NextRequest);
    }
	public function Delete() 
    {
		$id = $this->mPost['idDelete'];
        $getData = $this->mIndikatorKegiatanRef->GetDataById($id);
        if(!empty($getData)){
            $url = '&ipId='.$getData[0]['ipId'].'&ipNama='.$getData[0]['ipNama'].'&cari=1';
        } else {
            $url = '';
        }
		$deleteData = $this->mIndikatorKegiatanRef->Delete($id);
		if($deleteData === true) {
		  $this->ShowMessage('Penghapusan Data Berhasil Dilakukan');
        
		} else {
            $this->ShowMessage('Data Tidak Dapat Dihapus','fail'); 
		}
		return $this->mPageView.$url;
	}
}
