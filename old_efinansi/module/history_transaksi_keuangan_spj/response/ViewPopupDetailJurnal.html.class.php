<?php

/**
 * 
 * @class ViewPopupDetailJurnal
 * @package history_transaksi_keuangan_spj
 * @description untuk menjalankan query daftar transaksi keuagan spj
 * @analyst dyah fajar <dyah@gamatechno.com>
 * @author noor hadi <noor.hadi@gamatechno.com>
 * @since Januari 2014 
 * @copyright 2014 Gamatechno Indonedia
 * 
 */
 
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
	'module/history_transaksi_keuangan_spj/business/Jurnal.class.php';

class ViewPopupDetailJurnal extends HtmlResponse 
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
						'module/history_transaksi_keuangan_spj/template');
		$this->SetTemplateFile('view_popup_detail_jurnal.html');		
   } 
   
   
	public function ProcessRequest() 
	{	
		$id = Dispatcher::Instance()->Decrypt($_GET['dataId']);			
		
		$msg = Messenger::Instance()->Receive(__FILE__);
		
		$this->mData = $msg[0][0];
		$this->mPesan = $msg[0][1];
		$this->mCss = $msg[0][2];
	  
		$return['dataTransaksi'] = $this->mDBObj->GetTransksiById($id);
		$return['dataJurnal'] = $this->mDBObj->GetTransksiJurnalById($id);		
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
		$this->mrTemplate->AddVar('content', 'TRANSAKSI_NOMINAL_F',number_format($data['dataTransaksi']['transaksi_nominal'],2,',','.'));
		
		
		if(empty($data['dataJurnal'])){
			$this->mrTemplate->AddVar('data', 'DATA_EMPTY', 'YES');
		} else {
			$this->mrTemplate->AddVar('data', 'DATA_EMPTY', 'NO');

			$totalDebet = 0;
			$totalKredit = 0;			
			
			foreach($data['dataJurnal'] as $key => $value){
				
				if($data['dataJurnal'][$key]['akun_dk'] == 'D') {
					$totalDebet += $data['dataJurnal'][$key]['akun_nominal'];
					$this->mrTemplate->SetAttribute('nominal_d', 'visibility', 'visible');
					$this->mrTemplate->SetAttribute('nominal_k', 'visibility', 'hidden');
					$this->mrTemplate->AddVar('nominal_d', 'DATA_AKUN_NOMINAL', 
								number_format($data['dataJurnal'][$key]['akun_nominal'],2,',','.'));
				} else {
					$totalKredit += $data['dataJurnal'][$key]['akun_nominal'];
					$this->mrTemplate->SetAttribute('nominal_d', 'visibility', 'hidden');
					$this->mrTemplate->SetAttribute('nominal_k', 'visibility', 'visible');
					$this->mrTemplate->AddVar('nominal_k', 'DATA_AKUN_NOMINAL',
								number_format($data['dataJurnal'][$key]['akun_nominal'],2,',','.'));
				}	
								
				$this->mrTemplate->AddVars('data_item', $data['dataJurnal'][$key], 'DATA_');
				$this->mrTemplate->parseTemplate('data_item', 'a');	 
			}

			$this->mrTemplate->AddVar('content', 'DATA_TOTAL_NOMINAL_D', number_format($totalDebet,2,',','.'));
			$this->mrTemplate->AddVar('content', 'DATA_TOTAL_NOMINAL_K', number_format($totalKredit,2,',','.'));
		}		
	}
}

?>