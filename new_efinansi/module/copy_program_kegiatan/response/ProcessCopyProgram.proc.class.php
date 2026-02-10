<?php

require_once GTFWConfiguration::GetValue('application', 'docroot') . 
'module/copy_program_kegiatan/business/CopyProgramKegiatan.class.php';

class ProcessCopyProgram
{
    protected $mPost;
    protected $mCopyProgram;
    protected $mPageView;
    
    protected $mCssDone = "notebox-done";
	protected $mCssFail = "notebox-warning";

	protected $mReturn;
	protected $mDecId;
	protected $mEncId;
	    
    public function __construct()
    {
        $this->mCopyProgram = new CopyProgramKegiatan();
        $this->mPost = $_POST->AsArray();

		$this->mPageView = Dispatcher::Instance()->GetUrl(
					'copy_program_kegiatan', 
					'copyProgramKegiatan', 
					'view', 
					'html').'&th_anggar_awal='.Dispatcher::Instance()->Encrypt($this->mPost['th_anggaran_sumber']).
                                '&th_anggar_akhir='.
                                Dispatcher::Instance()->Encrypt($this->mPost['th_anggaran_tujuan']).
                                '&cari=' . Dispatcher::Instance()->Encrypt(1);
        
    }
    
    public function Copy()
    {
        if(!empty($this->mPost['kode'])){
            $copy = $this->mCopyProgram->CopyProgramKegiatan($this->mPost);
            //echo $copy;
            if($copy){
				Messenger::Instance()->Send(
						'copy_program_kegiatan', 
						'copyProgramKegiatan', 
						'view', 
						'html', 
						array(
								$this->mPost,
								'Copy Program Berhasil Dilakukan ', 
								$this->mCssDone),
						Messenger::NextRequest);            
            } else {
				Messenger::Instance()->Send(
						'copy_program_kegiatan', 
						'copyProgramKegiatan', 
						'view', 
						'html', 
						array(
								$this->mPost,
								'Gagal Melakukan Copy Program ', 
								$this->mCssFail),
						Messenger::NextRequest);
		  }
        } else {
				Messenger::Instance()->Send(
						'copy_program_kegiatan', 
						'copyProgramKegiatan', 
						'view', 
						'html', 
						array(
								$this->mPost,
								'Gagal Melakukan Copy Program, Data Program belum dipilih / tidak tersedia', 
								$this->mCssFail),
						Messenger::NextRequest);
            
        }          

        return $this->mPageView;
    }
}