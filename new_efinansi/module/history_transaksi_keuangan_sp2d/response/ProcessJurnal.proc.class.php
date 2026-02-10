<?php

/**
 * 
 * @class ProcessJurnal
 * @package history_transaksi_keuangan_sp2d
 * @subpackage business 
 * @author noor hadi <noor.hadi@gamatechno.com>
 * @analyst dyah fajar <dyah@gamatechno.com>
 * @copyright 2014 gamatechno indonesia
 * 
 */
 
 
require_once GTFWConfiguration::GetValue('application', 'docroot') . 
	'module/history_transaksi_keuangan_sp2d/business/Jurnal.class.php';

class ProcessJurnal
{	
	protected $mPesan;
    protected $mCssDone = "notebox-done";
	protected $mCssFail = "notebox-warning";
	protected $mError = 0;
	protected $mErrorMessage = array();
	protected $_POST;
	
	protected $mPageInput;
	protected $mPageView;
	
	protected $mModuleName = 'history_transaksi_keuangan_sp2d';	
	
	protected $mDBObj;
	
	protected $mReturn;
	protected $mDecId;
	protected $mEncId;	
	
	public function __construct()
	{
		$this->mDBObj = new Jurnal;
		$this->_POST = $_POST->AsArray();

		$this->decId = Dispatcher::Instance()->Decrypt($_REQUEST['dataId']);
		$this->encId = Dispatcher::Instance()->Encrypt($this->decId);
	   		
		$this->mPageView = Dispatcher::Instance()->GetUrl(
                                                        $this->mModuleName,
                                                        'HistoryTransaksiKeuanganSP2D', 
                                                        'view', 
                                                        'html');
                                                        
		$this->mPageInput = Dispatcher::Instance()->GetUrl(
                                                        $this->mModuleName,
                                                        'InputJurnal', 
                                                        'view', 
                                                        'html') . 
                                                        '&dataId='.$this->encId;		
                                                        
		$this->mPageEdit = Dispatcher::Instance()->GetUrl(
                                                        $this->mModuleName,
                                                        'EditJurnal', 
                                                        'view', 
                                                        'html') . 
                                                        '&dataId='.$this->encId;		
		
	}	
		
	public function Validate()
	{
		
		//var_dump($this->_POST);
		//if(empty($this->_POST['transaksi_sekenario_jurnal_id'])){
		if(empty($this->_POST['COA'])){
			$this->mError = 1;
			$this->mErrorMessage[] =  GTFWConfiguration::GetValue('language', 'skenario_auto_jurnal') . ' Belum Dipilih.';
		} else {
			$totalNominalD = 0;
			$totalNominalK = 0;
			foreach($this->_POST['COA'] as $key => $v){
				if($v['akun_dk'] ==='D'){
					$totalNominalD += $v['akun_nominal'];
				} else {
					$totalNominalK += $v['akun_nominal'];
				}				
			}
			
			if($totalNominalD > $this->_POST['transaksi_nominal']){
				$this->mError = 1;
				$this->mErrorMessage[] = 'Total nominal akun debet melebihi nominal transaksi.';
			} elseif($totalNominalD < $this->_POST['transaksi_nominal']) {
				$this->mError = 1;
				$this->mErrorMessage[] = 'Total nominal akun debit lebih kecil dari nominal transaksi.';
			} elseif($totalNominalD != $this->_POST['transaksi_nominal']) {
				$this->mError = 1;
				$this->mErrorMessage[] = 'Total nominal akun debit tidak sama dengan nominal transaksi.';
			}
			
			if($totalNominalK > $this->_POST['transaksi_nominal']){
				$this->mError = 1;
				$this->mErrorMessage[] = 'Total nominal akun kredit melebihi nominal transaksi.';
			} elseif($totalNominalK < $this->_POST['transaksi_nominal']) {
				$this->mError = 1;
				$this->mErrorMessage[] = 'Total nominal akun kredit lebih kecil dari nominal transaksi.';
			} elseif($totalNominalK != $this->_POST['transaksi_nominal']) {
				$this->mError = 1;
				$this->mErrorMessage[] = 'Total nominal akun kredit tidak sama dengan nominal transaksi.';
			}
			
		}
		
		if($this->mError > 0){
			return FALSE;
		} else {
			return TRUE;
		}
	}
	
	public function Add() 
	{
		if($this->Validate() === TRUE) {	
		
			$result = $this->mDBObj->Save($this->_POST);
			$returnPage = NULL;
		
			if($result === true){
				$this->ShowMessage('Proses Jurnal Berhasil Dilakukan.');
				$returnPage = $this->mPageView;
			} else {
				$this->ShowMessage('Gagal melakukan proses jurnal.','fail','InputJurnal');
				$returnPage = $this->mPageInput;
			}
		} else {
			$msg = implode('<br />',$this->mErrorMessage);
			$this->ShowMessage($msg,'fail','InputJurnal');
			$returnPage = $this->mPageInput;
		}	
				
		return $returnPage;
		
	}
	
	public function Delete() 
	{
		$id = $this->_POST['idDelete'];
		$result = $this->mDBObj->Delete($id);
		
		if($result === true){
			$this->ShowMessage('Proses Hapus Jurnal Berhasil Dilakukan.');
			$returnPage = $this->mPageView;
		} else {
			$this->ShowMessage('Gagal melakukan hapus jurnal.','fail');
			$returnPage = $this->mPageView;
		}
					
		return $returnPage;
	}	
	
	public function Update() 
	{
		if($this->Validate() === TRUE) {	
		
			$result = $this->mDBObj->Update($this->_POST);
			$returnPage = NULL;
		
			if($result === true){
				$this->ShowMessage('Proses Update Jurnal Berhasil Dilakukan.');
				$returnPage = $this->mPageView;
			} else {
				$this->ShowMessage('Gagal melakukan proses update jurnal.','fail','EditJurnal');
				$returnPage = $this->mPageEdit;
			}
			
		} else {
			$msg = implode('<br />',$this->mErrorMessage);
			$this->ShowMessage($msg,'fail','EditJurnal');
			$returnPage = $this->mPageEdit;
		}	
				
		return $returnPage;
	}
	
	public function validation($action) {}
	
    protected function ShowMessage($pesan,$status='done',$subModul='HistoryTransaksiKeuanganSP2D')
    {
            if($status =='done'){
                $style = $this->mCssDone;
            }elseif($status =='fail'){
                $style = $this->mCssFail;
            }
            
                    
        	Messenger::Instance()->Send(
                                        $this->mModuleName,
                                        $subModul, 
                                        'view', 
                                        'html', 
                                        array(
                                                $this->_POST,
                                                $pesan, 
                                                $style),
                                        Messenger::NextRequest);
    }	
}

?>