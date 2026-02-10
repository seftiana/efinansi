<?php

/**
 * 
 * class ViewHistoryTransaksiKeuanganSPJ
 * @package history_transaksi_keuangan_spj
 * @description untuk menjalankan query daftar transaksi keuagan spj
 * @analyst dyah fajar <dyah@gamatechno.com>
 * @author noor hadi <noor.hadi@gamatechno.com>
 * @since Januari 2014 
 * @copyright 2014 Gamatechno Indonedia
 * 
 */
  
 
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
		'module/history_transaksi_keuangan_spj/business/HistoryTransaksiKeuanganSPJ.class.php';

class ViewHistoryTransaksiKeuanganSPJ extends HtmlResponse 
{

	protected $mPesan;
	protected $mCss;

	public function TemplateModule() 
	{
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
						'module/history_transaksi_keuangan_spj/template');
		$this->SetTemplateFile('view_history_transaksi_keuangan_spj.html');
	}
	
	public function ProcessRequest() 
	{
		
		$Obj = new HistoryTransaksiKeuanganSPJ();
      
		if(isset($_POST['mulai_day'])) {
			$decMulaiTanggal = $_POST['mulai_day'];
			$decMulaiBulan = $_POST['mulai_mon'];
			$decMulaiTahun = $_POST['mulai_year'];

			$decSelesaiTanggal = $_POST['selesai_day'];
			$decSelesaiBulan = $_POST['selesai_mon'];
			$decSelesaiTahun = $_POST['selesai_year'];
			$nomor = $_POST['nomor_bukti'];
			
			$posting = $_POST['posting'];

		} elseif(isset($_GET['mulai_day'])) {
			$decMulaiTanggal = Dispatcher::Instance()->Decrypt($_GET['mulai_day']);
			$decMulaiBulan = Dispatcher::Instance()->Decrypt($_GET['mulai_mon']);
			$decMulaiTahun = Dispatcher::Instance()->Decrypt($_GET['mulai_year']);

			$decSelesaiTanggal = Dispatcher::Instance()->Decrypt($_GET['selesai_day']);
			$decSelesaiBulan = Dispatcher::Instance()->Decrypt($_GET['selesai_mon']);
			$decSelesaiTahun = Dispatcher::Instance()->Decrypt($_GET['selesai_year']);

			$nomor = Dispatcher::Instance()->Decrypt($_GET['nomor_bukti']);
			
			$posting = Dispatcher::Instance()->Decrypt($_GET['posting']);
		
			
		} else {
			$decMulaiTanggal = date("01");
			$decMulaiBulan = date("01");
			$decMulaiTahun = date("Y");

			$decSelesaiTanggal = date("d");
			$decSelesaiBulan = date("m");
			$decSelesaiTahun = date("Y");
			
			$nomor = '';
			
			$posting = 'all';		
		}
		
		$mulai_selected = $decMulaiTahun . "-" . $decMulaiBulan . "-" . $decMulaiTanggal;
		$selesai_selected = $decSelesaiTahun . "-" . $decSelesaiBulan . "-" . $decSelesaiTanggal;

		//view
		$itemViewed = 20;
		$currPage = 1;
		$startRec = 0 ;
		
		if(isset($_GET['page'])) {
			$currPage = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
			$startRec =($currPage-1) * $itemViewed;
		}

		$dataDetilTransaksi = $Obj->getData($startRec, $itemViewed, $mulai_selected, $selesai_selected, $nomor, $posting);
										
		$totalData = $Obj->GetCountData();
		$statusJurnalYa = GTFWConfiguration::GetValue('language','status_jurnal_ya');
		$statusJurnalTidak = GTFWConfiguration::GetValue('language','status_jurnal_tidak');
		$arr_is_posting = array(
								array('id'=>'Y','name'=>$statusJurnalYa), 
								array('id'=>'T','name'=>$statusJurnalTidak));
								
      	Messenger::Instance()->SendToComponent(
	  									  'combobox', 
										  'Combobox', 
										  'view', 
										  'html', 
										  'posting', 
										  array(
										  		'posting', 
												$arr_is_posting, 
												$posting, 
												true, 
												' style="width:100px;" id="posting"'), 
										Messenger::CurrentRequest);
		
		$url = Dispatcher::Instance()->GetUrl(	
										Dispatcher::Instance()->mModule, 
										Dispatcher::Instance()->mSubModule, 
										Dispatcher::Instance()->mAction, 
										Dispatcher::Instance()->mType . 
										'&mulai_day=' . Dispatcher::Instance()->Encrypt($decMulaiTanggal).
										'&mulai_mon=' . Dispatcher::Instance()->Encrypt($decMulaiBulan). 
										'&mulai_year=' . Dispatcher::Instance()->Encrypt($decMulaiTahun). 
										'&selesai_day=' . Dispatcher::Instance()->Encrypt($decSelesaiTanggal). 
										'&selesai_mon=' . Dispatcher::Instance()->Encrypt($decSelesaiBulan).
										'&selesai_year=' . Dispatcher::Instance()->Encrypt($decSelesaiTahun). 
										'&nomor_bukti=' . Dispatcher::Instance()->Encrypt($nomor) .
										'&posting=' . Dispatcher::Instance()->Encrypt($posting) .
										'&tipe_transaksi=' . Dispatcher::Instance()->Encrypt($tipe) . 
										'&cari=' . Dispatcher::Instance()->Encrypt(1));

		Messenger::Instance()->SendToComponent(
											'paging', 
											'Paging', 
											'view', 
											'html', 
											'paging_top', 
											array(
													$itemViewed,
													$totalData, 
													$url, 
													$currPage), 
											Messenger::CurrentRequest);

		$msg = Messenger::Instance()->Receive(__FILE__);
		$this->mPesan = $msg[0][1];
		$this->mCss = $msg[0][2];

      	$tahun['start'] = date("Y")-5;
      	$tahun['end'] = date("Y")+5;
      	Messenger::Instance()->SendToComponent(
							                  'tanggal', 
											  'Tanggal', 
											  'view', 
											  'html', 
											  'mulai', 
											  array(
											  		$mulai_selected, 
													$tahun['start'], 
													$tahun['end'], '', '', 
													'mulai'), 
											  Messenger::CurrentRequest);
											  
      	Messenger::Instance()->SendToComponent(
											  'tanggal', 
											  'Tanggal', 
											  'view', 
											  'html', 
											  'selesai', 
											  array(
											  		$selesai_selected, 
													$tahun['start'], 
													$tahun['end'], '', '', 
													'selesai'), 
											  Messenger::CurrentRequest);
											  
		$return['dataDetilTransaksi'] = $dataDetilTransaksi;
		$return['nomor'] = $nomor;
		$return['statusJurnalYa'] = $statusJurnalYa;
		$return['statusJurnalTidak'] = $statusJurnalTidak;
		$return['start'] = $startRec+1;
      	return $return;
      	
	}

	public function ParseTemplate($data = NULL) 
	{
		$this->mrTemplate->AddVar('content', 'URL_SEARCH', 
									Dispatcher::Instance()->GetUrl(
														'history_transaksi_keuangan_spj', 
														'HistoryTransaksiKeuanganSPJ', 
														'view', 
														'html'));
														
		$this->mrTemplate->AddVar('content', 'NOMOR_BUKTI', $data['nomor']);
		$this->mrTemplate->AddVar('content', 'URAIAN', $data['uraian']);
		
		if($this->mPesan) {
			$this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
			$this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->mPesan);
			$this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $this->mCss);
		}

		if (empty($data['dataDetilTransaksi'])) {
			$this->mrTemplate->AddVar('data', 'DATA_EMPTY', 'YES');
		} else {
			$this->mrTemplate->AddVar('data', 'DATA_EMPTY', 'NO');
         
         
         $dataDetilTransaksi = $data['dataDetilTransaksi'];
         $arr_periode = array();
         $periode = "";
         $j=0;
		
		for ($i=0; $i<sizeof($dataDetilTransaksi); $i++) {
            $idEnc = Dispatcher::Instance()->Encrypt($dataDetilTransaksi[$i]['id']);  
            if($dataDetilTransaksi[$i]['tanggal'] != $periode) {
               $arr_periode[$j]['periode'] = $dataDetilTransaksi[$i]['tanggal'];
               $periode = $dataDetilTransaksi[$i]['tanggal'];
               $j++;
            }
			
			$no = $i+$data['start'];
			$dataDetilTransaksi[$i]['number'] = $no;
			if($i == 0) $this->mrTemplate->AddVar('content', 'FIRST_NUMBER', $no);				
			if($i == sizeof($dataDetilTransaksi)-1) 
				$this->mrTemplate->AddVar('content', 'LAST_NUMBER', $no);
			if ($no % 2 != 0) 
				$dataDetilTransaksi[$i]['class_name'] = 'table-common-even';
			else 
				$dataDetilTransaksi[$i]['class_name'] = '';

				$dataDetilTransaksi[$i]['nominal_label'] = number_format($dataDetilTransaksi[$i]['nominal'], 2, ',', '.');

				$url_popup_detail = Dispatcher::Instance()->GetUrl(
			   											'history_transaksi_keuangan_spj', 
												   		'PopupDetailJurnal', 
												   		'view', 
												   		'html') . 
												   		'&dataId=' . $idEnc;
												   		
				$url_edit = Dispatcher::Instance()->GetUrl(
			   											'history_transaksi_keuangan_spj', 
												   		'EditJurnal', 
												   		'view', 
												   		'html') . 
												   		'&dataId=' . $idEnc;
					
				$url_jurnal = Dispatcher::Instance()->GetUrl(
			   											'history_transaksi_keuangan_spj', 
												   		'InputJurnal', 
												   		'view', 
												   		'html') . 
												   		'&dataId=' . $idEnc;

				$urlAccept = 'history_transaksi_keuangan_spj|DeleteJurnal|do|html';
                $urlReturn = 'history_transaksi_keuangan_spj|HistoryTransaksiKeuanganSPJ|view|html';
                $label = 'Jurnal Transaksi SPJ';
                $dataName = $dataDetilTransaksi[$i]['no_bukti'] .' : ';
				$dataName .= $dataDetilTransaksi[$i]['uraian'];
				$url_hapus = Dispatcher::Instance()->GetUrl(
																'confirm', 
                                                                'confirmDelete', 
                                                                'do', 
                                                                'html') .
                                                                '&urlDelete=' . $urlAccept.
                                                                '&urlReturn=' . $urlReturn.
                                                                '&id=' . $dataDetilTransaksi[$i]['id'].
                                                                '&label=' . $label.
                                                                '&dataName=' . $dataName;
                                                                
				$dataDetilTransaksi[$i]['url_add_jurnal'] = $url_jurnal;
				$dataDetilTransaksi[$i]['url_popup_detail_jurnal'] = $url_popup_detail;
				$dataDetilTransaksi[$i]['url_edit_jurnal'] = $url_edit;												   		
				$dataDetilTransaksi[$i]['url_hapus_jurnal'] = $url_hapus;
				
				if($dataDetilTransaksi[$i]['is_jurnal'] == "Y") {					
					$dataDetilTransaksi[$i]['status_jurnal'] = $data['statusJurnalYa'];   
					$this->mrTemplate->AddVar('is_jurnal', 'IS_JURNAL', 'YES');
					$this->mrTemplate->AddVar('is_jurnal_action', 'IS_JURNAL', 'YES');
				
				} else {
					$dataDetilTransaksi[$i]['status_jurnal'] = $data['statusJurnalTidak'];
					$this->mrTemplate->AddVar('is_jurnal', 'IS_JURNAL', 'NO');
					$this->mrTemplate->AddVar('is_jurnal_action', 'IS_JURNAL', 'NO');										
				}
				
				if($dataDetilTransaksi[$i]['approval_jurnal'] == "Y") {					
					$dataDetilTransaksi[$i]['url_edit'] = '';
					$dataDetilTransaksi[$i]['url_delete'] = '';										
					$this->mrTemplate->AddVar('is_jurnal_approved', 'IS_JURNAL_APPROVED', 'YES');				
				} else {					
					$this->mrTemplate->AddVar('is_jurnal_approved', 'IS_JURNAL_APPROVED', 'NO');
				}
                                                               
				$this->mrTemplate->AddVar('is_jurnal_action', 'DATA_URL_ADD_JURNAL',$dataDetilTransaksi[$i]['url_add_jurnal']);
				$this->mrTemplate->AddVar('is_jurnal_action', 'DATA_URL_POPUP_DETAIL_JURNAL',$dataDetilTransaksi[$i]['url_popup_detail_jurnal']);
				$this->mrTemplate->AddVar('is_jurnal_approved', 'DATA_URL_EDIT_JURNAL',$dataDetilTransaksi[$i]['url_edit_jurnal']);
				$this->mrTemplate->AddVar('is_jurnal_approved', 'DATA_URL_HAPUS_JURNAL',$dataDetilTransaksi[$i]['url_hapus_jurnal']);
				
				$this->mrTemplate->AddVars('data_item', $dataDetilTransaksi[$i], 'DATA_');
				$this->mrTemplate->parseTemplate('data_item', 'a');	 
			}
			
			
         	for($j=0;$j<sizeof($arr_periode);$j++) {
				$this->mrTemplate->AddVar('periode_item', 'LIST_PERIODE',$arr_periode[$j]['periode']);
				$this->mrTemplate->parseTemplate('periode_item', 'a');	          
         	}
		}
	}
	
}

?>