<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
	'module/unitkerja_tree/business/AppUnitkerja.class.php';

class ViewUnitkerjaCari extends HtmlResponse {

	protected $Pesan;
	protected $mUnitKerja;

	public function TemplateModule() {
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
				'module/unitkerja_tree/template');
		$this->SetTemplateFile('view_unitkerja_cari.html');
	}

	public function ProcessRequest() {
		$this->mUnitKerja = new AppUnitkerja();
		if($_POST || isset($_GET['cari'])) {

			if(isset($_POST['kode'])) {
				$kode = $_POST['kode'];
			} elseif(isset($_GET['kode'])) {
				$kode = Dispatcher::Instance()->Decrypt($_GET['kode']);
			} else {
				$kode = '';
			}

			if(isset($_POST['nama'])) {
				$unitkerja = $_POST['nama'];
			} elseif(isset($_GET['nama'])) {
				$unitkerja = Dispatcher::Instance()->Decrypt($_GET['nama']);
			} else {
				$unitkerja = '';
			}

			if($_POST['tipeunit'] != "all") {
				$tipeunit = $_POST['tipeunit'];
			} elseif(isset($_GET['tipeunit'])) {
				$tipeunit = Dispatcher::Instance()->Decrypt($_GET['tipeunit']);
			} else {
				$tipeunit = '';
			}
		}


	//view
		$totalData = $this->mUnitKerja->GetCountDataUnitkerja($unitkerja, $kode,$tipeunit);

		$itemViewed = 20;
		$currPage = 1;
		$startRec = 0 ;
		if(isset($_GET['page'])) {
			$currPage = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
			$startRec =($currPage-1) * $itemViewed;
		}

		//$arrUnitKerja = $this->mUnitKerja->GetUnitKerja($startRec, $itemViewed, $kode,$unitkerja,$tipeunit);

		$dataUnitkerja = $this->mUnitKerja->getDataUnitkerja(
				$startRec, $itemViewed, $unitkerja, $kode, $tipeunit);

		$url = Dispatcher::Instance()->GetUrl(
						Dispatcher::Instance()->mModule,
						Dispatcher::Instance()->mSubModule,
						Dispatcher::Instance()->mAction,
						Dispatcher::Instance()->mType .
						'&kode=' . Dispatcher::Instance()->Encrypt($kode) .
						'&nama=' . Dispatcher::Instance()->Encrypt($unitkerja) .
						'&tipeunit=' . Dispatcher::Instance()->Encrypt($tipeunit) .
						'&cari=' . Dispatcher::Instance()->Encrypt(1));

		$return['url_params'] = '&kode=' . Dispatcher::Instance()->Encrypt($kode)
					.'&nama=' . Dispatcher::Instance()->Encrypt($unitkerja)
					.'&tipeunit=' . Dispatcher::Instance()->Encrypt($tipeunit);

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


		$arr_tipeunit = $this->mUnitKerja->GetDataTipeunit();

		Messenger::Instance()->SendToComponent(
					'combobox',
					'Combobox',
					'view',
					'html',
					'tipeunit',
					array(
							'tipeunit',
							$arr_tipeunit,
							$tipeunit,
							'true',
							' style="width:200px;" '),
					Messenger::CurrentRequest);

		$msg = Messenger::Instance()->Receive(__FILE__);

		$this->Pesan = $msg[0][1];
		$this->css = $msg[0][2];

		$return['dataUnitkerja'] = $dataUnitkerja;
		$return['start'] = $startRec+1;

		$return['search']['satker'] = $satker;
		$return['search']['kode'] = $kode;
		$return['search']['unitkerja'] = $unitkerja;
		$return['search']['tipeunit'] = $tipeunit;
		$return['itemViewed'] = $itemViewed;
		$return['currPage'] = $currPage;
		$return['startRec'] = $startRec;
		return $return;
	}

	function ParseTemplate($data = NULL) {
		$search = $data['search'];
		$this->mrTemplate->AddVar('content', 'SATKER', $search['satker']);
		$this->mrTemplate->AddVar('content', 'UNITKERJA_KODE', $search['kode']);
		$this->mrTemplate->AddVar('content', 'UNITKERJA', $search['unitkerja']);
		$this->mrTemplate->AddVar('content', 'TIPEUNIT', $search['tipeunit']);

        $this->mrTemplate->AddVar('content', 'URL_TREE',
					Dispatcher::Instance()->GetUrl('unitkerja_tree', 'unitkerja', 'view', 'html'));
		$this->mrTemplate->AddVar('content', 'URL_SEARCH',
					Dispatcher::Instance()->GetUrl('unitkerja_tree', 'unitkerjaCari', 'view', 'html'));
		$this->mrTemplate->AddVar('content', 'URL_EXCEL',
					Dispatcher::Instance()->GetUrl('unitkerja_tree', 'unitkerja', 'view', 'xlsx').
							$data['url_params']);
		$this->mrTemplate->AddVar('content', 'URL_ADD_UNIT',
					Dispatcher::Instance()->GetUrl('unitkerja_tree', 'inputUnitkerja', 'view', 'html') .
							'&jenis=unit'.
                            '&p='.Dispatcher::Instance()->Encrypt('list'));


		if($this->Pesan) {
			$this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
			$this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->Pesan);
			$this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $this->css);
		}

		if (empty($data['dataUnitkerja'])) {
			$this->mrTemplate->AddVar('data_unitkerja', 'UNITKERJA_EMPTY', 'YES');
		} else {
			$decPage = Dispatcher::Instance()->Decrypt($_REQUEST['page']);
			$encPage = Dispatcher::Instance()->Encrypt($decPage);
			$this->mrTemplate->AddVar('data_unitkerja', 'UNITKERJA_EMPTY', 'NO');
			$dataUnitkerja = $data['dataUnitkerja'];
         //print_r($dataUnitkerja);
//mulai bikin tombol delete
			$label = "Manajemen Unit/ Sub Unit Kerja";
			$urlDelete = Dispatcher::Instance()->GetUrl('unitkerja_tree', 'deleteUnitkerja', 'do', 'html');
			$urlReturn = Dispatcher::Instance()->GetUrl('unitkerja_tree', 'UnitkerjaCari', 'view', 'html');
			Messenger::Instance()->Send(
							'confirm',
							'confirmDelete',
							'do',
							'html',
							array(
									$label,
									$urlDelete,
									$urlReturn,
									'Penghapusan data Unit akan menghapus Sub unit dibawahnya'),
							Messenger::NextRequest);
			$this->mrTemplate->AddVar(
					'content',
					'URL_DELETE',
					Dispatcher::Instance()->GetUrl(
							'confirm',
							'confirmDelete',
							'do',
							'html'));
//selesai bikin tombol delete

         //$this->mrTemplate->AddVar('content', 'MESSAGE', 'Penghapusan Data ini akan menghapus sub unit dibawahnya');
         $x=0; $no=1;
         $unitkerjaId='';
         $nomor_satuankerja=1;
         //echo "<pre>";
         //print_r($dataUnitkerja);
         //echo "</pre>";


			for ($i=0; $i<sizeof($dataUnitkerja); $i++) {
				$no = $i+$data['start'];
				$dataUnitkerja[$i]['number'] =$no;
				if($i == 0) $this->mrTemplate->AddVar('content', 'FIRST_NUMBER', $no);
				if($i == sizeof($dataUnitkerja)-1) $this->mrTemplate->AddVar('content', 'LAST_NUMBER', $no);

				$getCountChild = $this->mUnitKerja->GetCountChild($dataUnitkerja[$i]['id']);
				if($getCountChild > 0) {
					$dataUnitkerja[$i]['is_disabled']='disabled="disabled"';
				} else {
					$dataUnitkerja[$i]['is_disabled']='';
				}

			//if($dataUnitkerja[$i]['parentId'] == 0) {
			if(	$dataUnitkerja[$i]['parentId'] == 0) {
               //print_r($dataUnitkerja[$i]);
               //unit/SATKER
               $this->mrTemplate->SetAttribute('is_satker', 'visibility', 'visible');
               $this->mrTemplate->AddVar('is_satker', 'UNITKERJA_ID', $dataUnitkerja[$i]['id']);
               //$this->mrTemplate->AddVar('is_satker', 'IS_SATKER', $dataUnitkerja[$i]['id']);
               $dataUnitkerja[$i]['class_name'] = 'table-common-even1';
               //$dataUnitkerja[$i]['class_parent_child'] = 'CheckBoxFW_parent';
   			   $dataUnitkerja[$i]['url_edit'] = Dispatcher::Instance()->GetUrl(
				  									'unitkerja_tree',
							  						'inputUnitkerja',
								  					'view', 'html') .
							  						'&jenis=' .
							  						Dispatcher::Instance()->Encrypt('unit') .
				  									'&dataId=' . $dataUnitkerja[$i]['id'].
                                                    '&p='.Dispatcher::Instance()->Encrypt('list');
			  // $this->mrTemplate->AddVar('data_unitkerja_is_parent', 'IS_PARENT', 'YES');

            } else {
               //subunit
               $this->mrTemplate->SetAttribute('is_satker', 'visibility', 'hidden');
               if($this->mUnitKerja->cekUnitParent($dataUnitkerja[$i]['id']) > 0 ){
               		$dataUnitkerja[$i]['class_name'] = 'table-common-even1';
               } else {
               		$dataUnitkerja[$i]['class_name'] = '';
               	}
               //$dataUnitkerja[$i]['class_parent_child'] = 'CheckBoxFW_child';
   			   $dataUnitkerja[$i]['url_edit'] = Dispatcher::Instance()->GetUrl(
				  			                    	'unitkerja_tree',
													'inputUnitkerja',
													'view',
													'html') .
													'&jenis=' .
													Dispatcher::Instance()->Encrypt('subunit') .
													'&dataId=' . $dataUnitkerja[$i]['id'].
                                                    '&p='.Dispatcher::Instance()->Encrypt('list');
            }
 				$dataUnitkerja[$i]['url_add_subunit'] = Dispatcher::Instance()->GetUrl(
				   									'unitkerja_tree',
			        								'inputUnitkerja',
													'view', 'html') .
													'&jenis=subunit'.
													'&parentUnitId=' . $dataUnitkerja[$i]['id'].
                                                    '&p='.Dispatcher::Instance()->Encrypt('list');
			  	//$this->mrTemplate->AddVar('data_unitkerja_item', 'URL_ADD_SUBUNIT', $urlAddSubUnit);

				$this->mrTemplate->AddVars('data_unitkerja_item', $dataUnitkerja[$i], 'UNITKERJA_');
				$this->mrTemplate->parseTemplate('data_unitkerja_item', 'a');
			}

			//$this->ListUnitKerja(0,$data,$data['startRec'],$data['itemViewed'],
			//	$search['unitkerja'], $search['kode'],$search['tipeunit']);
		}
	}


}
