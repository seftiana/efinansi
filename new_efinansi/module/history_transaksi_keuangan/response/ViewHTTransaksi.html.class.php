<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
		'module/history_transaksi_keuangan/business/HTTransaksi.class.php';

class ViewHTTransaksi extends HtmlResponse 
{

	protected $Pesan;

	public function TemplateModule() 
	{
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
						'module/history_transaksi_keuangan/template');
		$this->SetTemplateFile('view_ht_transaksi.html');
	}
	
	public function ProcessRequest() 
	{
		$Obj = new HTTransaksi();
      //$decDataId = Dispatcher::Instance()->Decrypt($_GET['dataId']);
      if(isset($_POST['mulai_day'])) {
         $decMulaiTanggal = $_POST['mulai_day'];
         $decMulaiBulan = $_POST['mulai_mon'];
         $decMulaiTahun = $_POST['mulai_year'];

         $decSelesaiTanggal = $_POST['selesai_day'];
         $decSelesaiBulan = $_POST['selesai_mon'];
         $decSelesaiTahun = $_POST['selesai_year'];
			$nomor = $_POST['nomor_bukti'];
			
			$posting = $_POST['posting'];
			$tipe = $_POST['tipe_transaksi'];

      } elseif(isset($_GET['mulai_day'])) {
         $decMulaiTanggal = Dispatcher::Instance()->Decrypt($_GET['mulai_day']);
         $decMulaiBulan = Dispatcher::Instance()->Decrypt($_GET['mulai_mon']);
         $decMulaiTahun = Dispatcher::Instance()->Decrypt($_GET['mulai_year']);

         $decSelesaiTanggal = Dispatcher::Instance()->Decrypt($_GET['selesai_day']);
         $decSelesaiBulan = Dispatcher::Instance()->Decrypt($_GET['selesai_mon']);
         $decSelesaiTahun = Dispatcher::Instance()->Decrypt($_GET['selesai_year']);

			$nomor = Dispatcher::Instance()->Decrypt($_GET['nomor_bukti']);
			
			$posting = Dispatcher::Instance()->Decrypt($_GET['posting']);
			$tipe  = Dispatcher::Instance()->Decrypt($_GET['tipe_transaksi']);
			
      } else {
         $decMulaiTanggal = date("01");
         $decMulaiBulan = date("01");
         $decMulaiTahun = date("Y");

         $decSelesaiTanggal = date("d");
         $decSelesaiBulan = date("m");
         $decSelesaiTahun = date("Y");
			
			$nomor = '';
			
			$posting = 'T';
			$tipe ='';
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

		$dataDetilTransaksi = $Obj->getData(
										$startRec, 
										$itemViewed, 
										$mulai_selected, 
										$selesai_selected, 
										$nomor, 
										$posting,$tipe);
										
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
		$this->Pesan = $msg[0][1];
		$this->css = $msg[0][2];

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
		/**
		 * combo tipe transaksi
		 */
	 	$arr_tipe_transaksi = $Obj->GetComboTipeTransaksi();
	   	Messenger::Instance()->SendToComponent(
	   											'combobox', 
											    'Combobox', 
												'view', 
												'html', 
												'tipe_transaksi', 
	   											array(
						   								'tipe_transaksi', 
						   								$arr_tipe_transaksi, 
						   								$tipe, 
						   								"false", 
						   								' style="width:200px;" id="tipe_transaksi"'), 
						   						Messenger::CurrentRequest);
	 	/**
	 	 * end
	 	 */
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
														'history_transaksi_keuangan', 
														'HTTransaksi', 
														'view', 
														'html'));
		
		/**												
		$this->mrTemplate->AddVar('content', 'URL_KEMBALI', 
									Dispatcher::Instance()->GetUrl(
														'transaksi', 
														'Transaksi', 
														'view', 
														'html'));
		*/												
		$this->mrTemplate->AddVar('content', 'NOMOR_BUKTI', $data['nomor']);
		$this->mrTemplate->AddVar('content', 'URAIAN', $data['uraian']);
		
		if($this->Pesan) {
			$this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
			$this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->Pesan);
			$this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $this->css);
		}

		if (empty($data['dataDetilTransaksi'])) {
			$this->mrTemplate->AddVar('data', 'DATA_EMPTY', 'YES');
		} else {
			//$decPage = Dispatcher::Instance()->Decrypt($_REQUEST['page']);
			//$encPage = Dispatcher::Instance()->Encrypt($decPage);
			$this->mrTemplate->AddVar('data', 'DATA_EMPTY', 'NO');
         
         //untuk confirm delete
         $label = GTFWConfiguration::GetValue('language','transaksi_keuangan');
         $urlDelete = 'history_transaksi_keuangan|deleteHTTransaksi|do|html';
         $urlReturn = 'history_transaksi_keuangan|HTTransaksi|view|html';
         $URLDELETE = Dispatcher::Instance()->GetUrl('history_transaksi', 'deleteHTTransaksi', 'do', 'html');

//mulai bikin tombol delete
			#$label = "Transaksi";
			#$urlDelete = Dispatcher::Instance()->GetUrl('transaksi', 'deleteTransaksi', 'do', 'html');
			#$urlReturn = Dispatcher::Instance()->GetUrl('transaksi', 'detilTransaksi', 'view', 'html');
			#Messenger::Instance()->Send('confirm', 'confirmDelete', 'do', 'html', array($label, $urlDelete, $urlReturn),Messenger::NextRequest);
			#$this->mrTemplate->AddVar('content', 'URL_DELETE', Dispatcher::Instance()->GetUrl('confirm', 'confirmDelete', 'do', 'html'));

         $dataDetilTransaksi = $data['dataDetilTransaksi'];
         $arr_periode = array();
         $periode = "";
         $j=0; #print_r($dataDetilTransaksi);
			for ($i=0; $i<sizeof($dataDetilTransaksi); $i++) {
            $idEnc = Dispatcher::Instance()->Encrypt($dataDetilTransaksi[$i]['id']);  
            if($dataDetilTransaksi[$i]['tanggal'] != $periode) {
               $arr_periode[$j]['periode'] = $dataDetilTransaksi[$i]['tanggal'];
               $periode = $dataDetilTransaksi[$i]['tanggal'];
               $j++;
            }
            #untuk cetak bkk bkm bm
            #peneriman / bkm
            if($dataDetilTransaksi[$i]['tipe_id'] == 1) {
               $dataDetilTransaksi[$i]['url_cetak_bukti'] = 
			   								Dispatcher::Instance()->GetUrl(
											   						'history_transaksi_keuangan', 
											   						'FormCetakBKM', 
											   						'view', 
											   						'html') . 
											   						'&dataId=' . $idEnc . 
											   						'&tipe=bkm'.
																	'&request=transaksi';
											   						
               $dataDetilTransaksi[$i]['label_cetak_bukti'] = 'Cetak BKM';
            }elseif($dataDetilTransaksi[$i]['tipe_id'] == 2) { // bkk pengeluaran
               $dataDetilTransaksi[$i]['url_cetak_bukti'] = 
			   								Dispatcher::Instance()->GetUrl(
											   						'history_transaksi_keuangan', 
																   	'FormCetakBKK', 
											   						'view', 
											   						'html') . 
											   						'&dataId=' . $idEnc . 
											   						'&tipe=bkk'.
																	'&request=transaksi';
											   						
               $dataDetilTransaksi[$i]['label_cetak_bukti'] = 'Cetak BKK';
            }elseif($dataDetilTransaksi[$i]['tipe_id'] == 3) { // bm umum
               $dataDetilTransaksi[$i]['url_cetak_bukti'] = 	
			   								Dispatcher::Instance()->GetUrl(
											   						'history_transaksi_keuangan', 
											   						'FormCetakBM', 
											   						'view', 
											   						'html') . 
											   						'&dataId=' . $idEnc . 
											   						'&tipe=bm'.
																	'&request=transaksi';
											   						
               $dataDetilTransaksi[$i]['label_cetak_bukti'] = 'Cetak BM';
               
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

            $dataDetilTransaksi[$i]['nominal_label'] = 
									number_format($dataDetilTransaksi[$i]['nominal'], 2, ',', '.');

			$dataDetilTransaksi[$i]['url_cetak'] = Dispatcher::Instance()->GetUrl(
																		'history_transaksi_keuangan', 
																		'FormCetakTransaksi', 
																		'view', 
																		'html') . 
																		'&dataId=' . $idEnc;
																		
            if($dataDetilTransaksi[$i]['is_jurnal'] == "Y") {
               $dataDetilTransaksi[$i]['url_edit'] = '';
               $dataDetilTransaksi[$i]['url_delete'] = '';
               $dataDetilTransaksi[$i]['status_jurnal'] = $data['statusJurnalYa'];
               /*$dataDetilTransaksi[$i]['url_delete'] = 
			   		Dispatcher::Instance()->GetUrl('confirm', 'confirmDelete', 'do', 'html') .
            		'&urlDelete='. $urlDelete.'&urlReturn='.$urlReturn.
            		'&id='.Dispatcher::Instance()->Encrypt($dataDetilTransaksi[$i]['id']).
            		'&label='.$label.'&dataName='.$dataDetilTransaksi[$i]['kkb'];*/
            } else {
            	$dataDetilTransaksi[$i]['status_jurnal'] = $data['statusJurnalTidak'];
                $url_deletee = Dispatcher::Instance()->GetUrl(
			   											'confirm', 
												   		'confirmDelete', 
												   		'do', 
												   		'html') .
            											'&urlDelete='. $urlDelete.
														'&urlReturn='.$urlReturn.
            											'&id='.	Dispatcher::Instance()->Encrypt(
																			$dataDetilTransaksi[$i]['id']).
														'&label='.$label.
														'&dataName='.$dataDetilTransaksi[$i]['kkb'];
														 
                $url_edit = Dispatcher::Instance()->GetUrl(
			   											'history_transaksi_keuangan', 
												   		'HTFormTransaksi', 
												   		'view', 
												   		'html') . 
												   		'&dataId=' . $idEnc;
												   		
   				$dataDetilTransaksi[$i]['url_edit'] =  '<a class="xhr dest_subcontent-element" href="'
														.$url_edit.
														'" title="Edit">'.
														'<img src="images/button-edit.gif" alt="Edit"/></a>';
               
               $dataDetilTransaksi[$i]['url_delete'] =  '<a class="xhr dest_subcontent-element" '.
   														'onClick="javascript: return showBoxConfirmDelete("'.
												   		$dataDetilTransaksi[$i]['id'].'", "'.
												   		$dataDetilTransaksi[$i]['kkb'].'", "'.
												   		$URLDELETE.'");" href="'.$url_deletee.
												   		'" title="Hapus">'.
												   		'<img src="images/button-delete.gif"'.
												   		' alt="Hapus"/></a>';
               
            #$this->mrTemplate->AddVar("data_item", "IDDELETE", $data['fasilitas'][$i]['fasilitas_id']);
            $dataDetilTransaksi[$i]['iddelete'] = $dataDetilTransaksi[$i]['id'];
            #$this->mrTemplate->AddVar("data_item", "URLDELETE", $URLDELETE);
            $dataDetilTransaksi[$i]['urldelete'] = $URLDELETE;
            /*$dataDetilTransaksi[$i]['url_delete'] = Dispatcher::Instance()->GetUrl(
			'confirm', 'confirmDelete', 'do', 'html') .
            '&urlDelete='. $urlDelete.'&urlReturn='.$urlReturn.
            '&id='.Dispatcher::Instance()->Encrypt($dataDetilTransaksi[$i]['id']).
            '&label='.$label.'&dataName='.$dataDetilTransaksi[$i]['kkb'];*/
            /*$this->mrTemplate->AddVar("data_item", "URL_DELETE", Dispatcher::Instance()->GetUrl(
			'confirm', 'confirmDelete', 'do', 'html') .
            '&urlDelete='. $urlDelete.'&urlReturn='.$urlReturn.
            '&id='.Dispatcher::Instance()->Encrypt($data['fasilitas'][$i]['fasilitas_id']).
            '&label='.$label.'&dataName='.$data['fasilitas'][$i]['fasilitas_nama']);*/
            }

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