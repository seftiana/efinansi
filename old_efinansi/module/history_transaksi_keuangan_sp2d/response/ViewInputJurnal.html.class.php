<?php

/**
 * 
 * @class ViewInputJurnal
 * @package history_transaksi_keuangan_sp2d
 * @description untuk menjalankan query daftar transaksi keuagan sp2d
 * @analyst dyah fajar <dyah@gamatechno.com>
 * @author noor hadi <noor.hadi@gamatechno.com>
 * @since Januari 2014 
 * @copyright 2014 Gamatechno Indonedia
 * 
 */

require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
	'module/history_transaksi_keuangan_sp2d/business/Jurnal.class.php';

class ViewInputJurnal extends HtmlResponse 
{
   
	protected $mData;
	protected $mDBObj;
	
	protected $mPesan;
	protected $mCss;
   
	public function __construct() 
	{
		$this->mDBObj = new Jurnal();
	}
   
	public function TemplateModule() 
	{
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot') . 
						'module/history_transaksi_keuangan_sp2d/template');
		$this->SetTemplateFile('view_input_jurnal.html');		
   } 
   
   
	public function ProcessRequest() 
	{	
		$id = Dispatcher::Instance()->Decrypt($_GET['dataId']);			
		
		$msg = Messenger::Instance()->Receive(__FILE__);
		
		$this->mData = $msg[0][0];
		$this->mPesan = $msg[0][1];
		$this->mCss = $msg[0][2];
		/*
		echo '<pre>'; 
		print_r($this->mData);
		echo '</pre>';
		*/ 
		if(empty($this->mData)){
			$return['dataTransaksi'] = $this->mDBObj->GetTransksiById($id);
		} else {
			$return['dataTransaksi'] = $this->mData;
		}
		$return['dataId'] = $id;
		return $return;
	}
   
	public function ParseTemplate($data = NULL) 
	{
		//print_r($data['dataTransaksi']);		
		
		if($this->mPesan) {
			$this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
			$this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->mPesan);
			$this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $this->mCss);
		}
				
		$url_list_transaksi = Dispatcher::Instance()->GetUrl(
														'history_transaksi_keuangan_sp2d', 
														'HistoryTransaksiKeuanganSP2D', 
														'view', 
														'html');
														
		$url_popup_sekenario_jurnal = Dispatcher::Instance()->GetUrl(
														'history_transaksi_keuangan_sp2d', 
														'PopupSekenarioJurnal', 
														'view', 
														'html');
		
		$url_action_add = Dispatcher::Instance()->GetUrl(
														'history_transaksi_keuangan_sp2d', 
														'AddJurnal', 
														'do', 
														'html') . 
														'&dataId='. $data['dataId'];
														
		$url_action_edit =  Dispatcher::Instance()->GetUrl(
														'history_transaksi_keuangan_sp2d', 
														'EditJurnal', 
														'do', 
														'html');
		
		$this->mrTemplate->AddVar('content', 'URL_ACTION',$url_action_add);
		$this->mrTemplate->AddVar('content', 'URL_BATAL',$url_list_transaksi);
		
		$this->mrTemplate->AddVar('content', 'URL_LIST_TRANSAKSI',$url_list_transaksi);
		$this->mrTemplate->AddVar('content', 'URL_POPUP_SKENARIO_JURNAL',$url_popup_sekenario_jurnal);
		
		$this->mrTemplate->AddVar('content', 'TRANSAKSI_ID',$data['dataTransaksi']['transaksi_id']);
		$this->mrTemplate->AddVar('content', 'TRANSAKSI_TANGGAL_F',$data['dataTransaksi']['transaksi_tanggal']);
		$this->mrTemplate->AddVar('content', 'TRANSAKSI_TANGGAL',$data['dataTransaksi']['transaksi_tanggal']);
		$this->mrTemplate->AddVar('content', 'TRANSAKSI_NO_BUKTI',$data['dataTransaksi']['transaksi_no_bukti']);
		$this->mrTemplate->AddVar('content', 'TRANSAKSI_URAIAN',$data['dataTransaksi']['transaksi_uraian']);
		$this->mrTemplate->AddVar('content', 'TRANSAKSI_PENANGGUNG_JAWAB',$data['dataTransaksi']['transaksi_penanggung_jawab']);
		$this->mrTemplate->AddVar('content', 'TRANSAKSI_NOMINAL',$data['dataTransaksi']['transaksi_nominal']);
		$this->mrTemplate->AddVar('content', 'TRANSAKSI_NOMINAL_F',
										number_format($data['dataTransaksi']['transaksi_nominal'],2,',','.')
										);		
										
		$data['dataJurnal'] = $data['dataTransaksi']['COA'];
		if($data['dataTransaksi']['coa_total_nominal_show']=='1'){
			$this->mrTemplate->AddVar('content', 'DATA_IS_TOTAL_DISPLAY', '');
			$this->mrTemplate->AddVar('content', 'DATA_TOTAL_NOMINAL_SHOW', '1');
		} else {
			$this->mrTemplate->AddVar('content', 'DATA_TOTAL_NOMINAL_SHOW', '0');
			$this->mrTemplate->AddVar('content', 'DATA_IS_TOTAL_DISPLAY', 'display:none;');
		}
		
		
		if(empty($data['dataJurnal'])){
			$this->mrTemplate->AddVar('data', 'DATA_EMPTY', 'YES');
		} else {
			$this->mrTemplate->AddVar('data', 'DATA_EMPTY', 'NO');		
			
			$totalDebet = 0;
			$totalKredit = 0;
			
			foreach($data['dataJurnal'] as $key => $v){		
						
				$data['dataJurnal'][$key]['akun_no'] = $key;			
				
				if($data['dataJurnal'][$key]['akun_dk'] == 'D') {
					$totalDebet += $data['dataJurnal'][$key]['akun_nominal'];
					$this->mrTemplate->SetAttribute('nominal_d', 'visibility', 'visible');
					$this->mrTemplate->SetAttribute('nominal_k', 'visibility', 'hidden');
					$this->mrTemplate->AddVar('nominal_d', 'DATA_AKUN_NOMINAL',$data['dataJurnal'][$key]['akun_nominal']);
				} else {
					$totalKredit += $data['dataJurnal'][$key]['akun_nominal'];
					$this->mrTemplate->SetAttribute('nominal_d', 'visibility', 'hidden');
					$this->mrTemplate->SetAttribute('nominal_k', 'visibility', 'visible');
					$this->mrTemplate->AddVar('nominal_k', 'DATA_AKUN_NOMINAL',$data['dataJurnal'][$key]['akun_nominal']);
				}			
				
				$this->mrTemplate->AddVar('nominal_d', 'DATA_AKUN_NO',$data['dataJurnal'][$key]['akun_no']);				
				$this->mrTemplate->AddVar('nominal_k', 'DATA_AKUN_NO',$data['dataJurnal'][$key]['akun_no']);
				
				$this->mrTemplate->AddVars('data_item', $data['dataJurnal'][$key], 'DATA_');
				$this->mrTemplate->parseTemplate('data_item', 'a');
				
			}
			
			$this->mrTemplate->AddVar('content', 'DATA_TOTAL_NOMINAL_D',$totalDebet);
			$this->mrTemplate->AddVar('content', 'DATA_TOTAL_NOMINAL_K',$totalKredit);
			
		}
												
	}

}

?>