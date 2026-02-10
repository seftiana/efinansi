<?php

/**
 * Class ProcessIndikatorProgramRef
 * @package indikator_program_ref
 * @todo Untuk proses data indikator program
 * @subpackage response
 * @since 21 Juni 2012
 * @SystemAnalyst Dyan Galih Nugroho Wicaksi <galih@gamatechno.com>
 * @author noor hadi <noor.hadi@gamatechno.com>
 * @copyright 2012 Gamatechno Indonesia
 * 
 */
 
require_once GTFWConfiguration::GetValue('application', 'docroot') .
    'module/indikator_program_ref/business/IndikatorProgramRef.class.php';

class ProcessIndikatorProgramRef 
{
    protected $mPost;
    protected $mIndikatorProgramRef;
    protected $mPageView;
    protected $mPageInput;
    
    protected $mCssDone = "notebox-done";
	protected $mCssFail = "notebox-warning";

	protected $mReturn;
	protected $mDecId;
	protected $mEncId;

    public function __construct() 
    {
	   $this->mIndikatorProgramRef 	= new IndikatorProgramRef();
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
                                                        'inputIndikatorProgramRef', 
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
            }elseif(
                ($this->mPost['kode']!='' && empty($this->mPost['dataId'])) ||
                (!empty($this->mPost['dataId']) && ($this->mPost['kode'] != $this->mPost['kode_lama']))){
                    if($this->mIndikatorProgramRef->GetCountKode($this->mPost['kode']) > 0){
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
            $add = $this->mIndikatorProgramRef->Add($this->mPost);		
            if($add === true) {
                $this->ShowMessage('Penambahan data Berhasil Dilakukan');
            }else {
			    $this->ShowMessage('Gagal Menambah Data','fail');
            }
	   }elseif($cek == "emptyNama") {
	       $this->ShowMessage('Nama wajib diisi','fail','inputIndikatorProgramRef');
           return $this->mPageInput;
	   }elseif($cek == "emptyKode") {
	       $this->ShowMessage('Kode wajib diisi','fail','inputIndikatorProgramRef');
	       return $this->mPageInput;
	   }elseif($cek == "existKode") {
	       $this->ShowMessage('Kode -'.$this->mPost['kode'].'- sudah ada','fail','inputIndikatorProgramRef');
	       return $this->mPageInput;
	   }
       
	   return $this->mPageView;
    }

	public function Update() 
    {
		$cek = $this->Check();
		if($cek === true) {
			$update	= $this->mIndikatorProgramRef->Update($this->mPost);
			if($update){
                $this->ShowMessage('Perubahan Data berhasil di lakukan');
			} else { 
			    $this->ShowMessage('Perubahan Data Gagal di lakukan','fail');
			}
		
	   }elseif($cek == "emptyNama") {
	       $this->ShowMessage('Nama wajib diisi','fail','inputIndikatorProgramRef');
           return $this->mPageInput;
	   }elseif($cek == "emptyKode") {
	       $this->ShowMessage('Kode wajib diisi','fail','inputIndikatorProgramRef');
	       return $this->mPageInput;
	   }elseif($cek == "existKode") {
	       $this->ShowMessage('Kode -'.$this->mPost['kode'].'- sudah ada','fail','inputIndikatorProgramRef');
	       return $this->mPageInput;
	   }
	   return $this->mPageView;
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
		$deleteData = $this->mIndikatorProgramRef->Delete($id);
		if($deleteData === true) {
		  $this->ShowMessage('Penghapusan Data Berhasil Dilakukan');
		} else {
            $this->ShowMessage('Data Tidak Dapat Dihapus','fail'); 
		}
		return $this->mPageView;
	}
}
