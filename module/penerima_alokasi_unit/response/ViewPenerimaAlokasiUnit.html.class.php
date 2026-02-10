<?php 

require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
	'module/penerima_alokasi_unit/business/PenerimaAlokasiUnit.class.php';
        
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
        'module/user_unit_kerja/business/UserUnitKerja.class.php';
      
class ViewPenerimaAlokasiUnit extends HtmlResponse 
{

	protected $mPesan;
	protected $mData;
	protected $mSearch;
	protected $mObj;
	protected $mUUK;
	protected $mModuleName ='penerima_alokasi_unit';
	

	public function __construct()
	{
		parent::__construct();
		$this->mObj = new PenerimaAlokasiUnit();
		$this->mUUK = new UserUnitKerja();
		
	}
	public function TemplateModule() 
	{
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
                'module/'.$this->mModuleName.'/template');
		$this->SetTemplateFile('view_'.$this->mModuleName.'.html');
	}

	public function ProcessRequest() 
	{
		$_POST = $_POST->AsArray();
		
		$userLoginId = Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
		$unitKerja= $this->mUUK->GetUnitKerjaUser($userLoginId);
		
		if(isset($_POST['btncari'])) {
				$this->mData['unit_kerja_id'] = $_POST['unit_kerja_id'];
				$this->mData['unit_kerja_nama'] = $_POST['unit_kerja_nama'];
				$this->mData['kode_penerimaan_id'] = $_POST['kode_penerimaan_id'];
				$this->mData['kode_penerimaan_nama'] = $_POST['kode_penerimaan_nama'];
		} elseif(isset($_GET['cari'])) {
				$this->mData['unit_kerja_id'] = 
							Dispatcher::Instance()->Decrypt($_GET['unit_kerja_id']);
				$this->mData['unit_kerja_nama'] = 
							Dispatcher::Instance()->Decrypt($_GET['unit_kerja_nama']);
				$this->mData['kode_penerimaan_id'] = 
							Dispatcher::Instance()->Decrypt($_GET['kode_penerimaan_id']);
				$this->mData['kode_penerimaan_nama'] = 
							Dispatcher::Instance()->Decrypt($_GET['kode_penerimaan_nama']);
		} else {
            $this->mData['unit_kerja_id'] = $unitKerja['unit_kerja_id'];
			$this->mData['unit_kerja_nama'] = $unitKerja['unit_kerja_nama'];
			$this->mData['kode_penerimaan_id'] = '';
			$this->mData['kode_penerimaan_nama'] = '';
			
		}
		

		/**
		 * combo tahun anggaran
		 */
		$tahunAnggaran = $this->mObj->GetDataTahunAnggaran();
		Messenger::Instance()->SendToComponent(
										'combobox', 
										'Combobox', 
										'view',
										'html', 
										'tahun_anggaran',
										array(
												'tahun_anggaran',
												$tahunAnggaran,
												$this->mData['tahun_anggaran_id'],
												'-',
												' style="width:150px;" '
											) , 
										Messenger::CurrentRequest);			
    
	
		$itemViewed = 20;
		$currPage = 1;
		$startRec = 0 ;
		if(isset($_GET['page'])) {
			$currPage = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
			$startRec =($currPage-1) * $itemViewed;
		}
		
		$url = Dispatcher::Instance()->GetUrl(
									Dispatcher::Instance()->mModule, 
									Dispatcher::Instance()->mSubModule, 
									Dispatcher::Instance()->mAction, 
									Dispatcher::Instance()->mType . 
									'&unit_kerja_id=' . Dispatcher::Instance()->Encrypt($this->mData['unit_kerja_id']) . 
									'&unit_kerja_nama=' . Dispatcher::Instance()->Encrypt($this->mData['unit_kerja_nama']) . 
									'&kode_penerimaan_id=' . Dispatcher::Instance()->Encrypt($this->mData['kode_penerimaan_id']) . 
									'&kode_penerimaan_nama=' . Dispatcher::Instance()->Encrypt($this->mData['kode_penerimaan_nama']) . 
									'&cari=' . Dispatcher::Instance()->Encrypt(1));
											
		$data = $this->mObj->GetData(
										$startRec, 
										$itemViewed,
										$this->mData['unit_kerja_id'],
										$this->mData['kode_penerimaan_id']
									);
                                        
        $totalData = $this->mObj->GetCountData();

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
   	    if(!empty($msg)){
			$this->mPesan = $msg[0][1];
			$this->mCss = $msg[0][2];
		}		

		$return['currPage'] = $currPage;
		$return['data'] = $data;
		$return['startRec'] = $startRec;
        $return['totalSubUnitKerja'] = $this->mUUK->GetTotalSubUnitKerja($unitKerja['unit_kerja_id']);
		
		return $return;
	}
	
	public function ParseTemplate($data = NULL) 
    {
		
		$this->mrTemplate->AddVar('content', 'URL_SEARCH', 
							Dispatcher::Instance()->GetUrl(
														Dispatcher::Instance()->mModule, 
														Dispatcher::Instance()->mSubModule, 
														Dispatcher::Instance()->mAction, 
														Dispatcher::Instance()->mType ));
														
        $this->mrTemplate->AddVar('content', 'URL_RESET', 
							Dispatcher::Instance()->GetUrl(
														Dispatcher::Instance()->mModule, 
														Dispatcher::Instance()->mSubModule, 
														Dispatcher::Instance()->mAction, 
														Dispatcher::Instance()->mType ));

		$this->mrTemplate->AddVar('content', 'URL_ADD', 
							Dispatcher::Instance()->GetUrl(
														$this->mModuleName, 
														'InputPenerimaAlokasiUnit', 
														'view', 
														'html'));
														
		/**
		 * untuk pengaturan display unit kerja pada form pencarian
		 * jika unit punya sub unit maka tampilkan tombol popup
		 * jika unit tudak punya sub unit maka hanya muncul label nama unit
		 */
		
		if($data['totalSubUnitKerja'] > 0) {
			$this->mrTemplate->AddVar('unit_kerja', 'IS_PARENT', 'YES');
		} else {
			$this->mrTemplate->AddVar('unit_kerja', 'IS_PARENT', 'NO');
			
		}	
		
		$this->mrTemplate->AddVar('content', 'UNIT_KERJA_ID', $this->mData['unit_kerja_id']);
		$this->mrTemplate->AddVar('unit_kerja', 'UNIT_KERJA_NAMA', $this->mData['unit_kerja_nama']);
		$this->mrTemplate->AddVar('unit_kerja', 'URL_POPUP_UNIT_KERJA', 
								Dispatcher::Instance()->GetUrl(
														$this->mModuleName, 
														'PopupUnitKerjaCari', 
														'view', 
														'html'));		     
														
		$this->mrTemplate->AddVar('content', 'KODE_PENERIMAAN_ID', $this->mData['kode_penerimaan_id']);
		$this->mrTemplate->AddVar('content', 'KODE_PENERIMAAN_NAMA', $this->mData['kode_penerimaan_nama']);
		$this->mrTemplate->AddVar('content', 'URL_POPUP_KODE_PENERIMAAN', 
								Dispatcher::Instance()->GetUrl(
														$this->mModuleName, 
														'PopupKodePenerimaanCari', 
														'view', 
														'html'));		     
        /**
         * untuk menampilkan pesan/message di halaman view / utama
         */
        if($this->mPesan) {
			$this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
			$this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->mPesan);
			$this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $this->mCss);
		}
        
		/**
         * untuk mekanisme hapus banyak
		 */
		$label = 'Penerima Alokasi Unit';
         /**
          * untuk hapus satu persatu
          */
          $urlSingleDelete = $this->mModuleName.'|deletePenerimaAlokasiUnit|do|html';
          $urlSingleReturn = $this->mModuleName.'|PenerimaAlokasiUnit|view|html';
         /**
          * end
          */
        	     
	    /**
	     * untuk menampilkan data
	     */ 
		if(empty($data['data'])){
			$this->mrTemplate->AddVar('list_data', 'IS_DATA_EMPTY', 'YES');
		} else {
			$this->mrTemplate->AddVar('list_data', 'IS_DATA_EMPTY', 'NO');
			
			$nomor =($data['currPage'] > 1) ? ($_SESSION['no_urut']) : 1;
			
			$max = sizeof($data['data']);
			$kodePenerimaanId = '';
			$unitKerjaSumberId = '';
			$jenisAlokasi ='';
			$getTotalAlokasi=array();
			$x=0;
			//echo '<pre>';
			//print_r($data['data']);
			//echo '</pre>';
			$labelPusat = 'Alokasi Untuk Pusat';
			$labelUnit = 'Alokasi Untuk Unit';
			for($i= 0;$i < $max;){				
				if(
					($unitKerjaSumberId == $data['data'][$i]['unit_kerja_sumber_id']) &&
				    ($kodePenerimaanId  == $data['data'][$i]['kode_penerimaan_id']) && 
				    ($jenisAlokasi  == $data['data'][$i]['jenis_alokasi']) 
				 ){ /**
					    $jenisAlokasi = $data['data'][$i]['jenis_alokasi'];
					 	$dataList[$x]['kode']= '';
						$dataList[$x]['nama']= ($jenisAlokasi == 1) ? 
											'<strong>'.$labelPusat.'</strong>' : '<strong>'.$labelUnit.'</strong>';
						$dataList[$x]['alokasi_sumber_nilai']= $data['data'][$i]['besar_alokasi_sumber'];
						if($i > 0){
							if($data['data'][$i -1]['jenis_alokasi'] == $data['data'][$i]['jenis_alokasi']){
								$dataList[$x]['nama']='';
								$dataList[$x]['alokasi_sumber_nilai']= '';
							} else{
								$nomor = 1;
							}
						}
						**/
						$dataList[$x]['kode']= '';
						$dataList[$x]['nama']= '';
						$dataList[$x]['alokasi_sumber_nilai']= '';
						$dataList[$x]['kode_unit_alokasi']= $data['data'][$i]['unit_kerja_kode'];
						$dataList[$x]['nama_unit_alokasi']= $data['data'][$i]['unit_kerja_nama'];
						$dataList[$x]['alokasi_nilai']= $data['data'][$i]['alokasi_nilai'];
						//$dataList[$x]['group_id'] = '';
						$dataList[$x]['nomor'] = $nomor;
						$dataList[$x]['bold'] ='normal';
						$dataList[$x]['tipe'] ='';
						$dataList[$x]['id'] =''; 
						$dataList[$x]['pusat_id'] = '';
						$dataList[$x]['unit_id'] = '';
						$nomor++;
						$i++;
				} elseif($unitKerjaSumberId != $data['data'][$i]['unit_kerja_sumber_id']){
						$unitKerjaSumberId = $data['data'][$i]['unit_kerja_sumber_id'];
						$dataList[$x]['kode']= $data['data'][$i]['unit_kerja_sumber_kode'];
						$dataList[$x]['nama']= $data['data'][$i]['unit_kerja_sumber_nama'];
						$dataList[$x]['alokasi_sumber_nilai']= '';
						$dataList[$x]['kode_unit_alokasi']= '';
						$dataList[$x]['nama_unit_alokasi']= '';
						$dataList[$x]['alokasi_nilai']= '';
						$dataList[$x]['nomor'] = '';
						$dataList[$x]['bold'] ='bold';
						$dataList[$x]['tipe'] ='';
						$dataList[$x]['id'] = '';
						$dataList[$x]['pusat_id'] = '';
						$dataList[$x]['unit_id'] = '';
						//$totalAlokasi =0;
				} elseif($kodePenerimaanId != $data['data'][$i]['kode_penerimaan_id']){
						$kodePenerimaanId = $data['data'][$i]['kode_penerimaan_id'];
				
						$dataList[$x]['kode']= $data['data'][$i]['kode_penerimaan_kode'];
						$dataList[$x]['nama']= $data['data'][$i]['kode_penerimaan_nama'];
						$dataList[$x]['alokasi_sumber_nilai']= '';
						$dataList[$x]['kode_unit_alokasi']= '';
						$dataList[$x]['nama_unit_alokasi']= '';
						$dataList[$x]['alokasi_nilai']= '';
						$dataList[$x]['nomor'] = '';
						$dataList[$x]['bold'] ='bold';
						$dataList[$x]['tipe'] =1;
						$dataList[$x]['id'] = $data['data'][$i]['alokasi_id'];
						$dataList[$x]['pusat_id'] = $data['data'][$i]['alokasi_pusat_id'];
						$dataList[$x]['unit_id'] = $data['data'][$i]['alokasi_unit_id'];
						//$totalAlokasi =0;
				} elseif($jenisAlokasi != $data['data'][$i]['jenis_alokasi']){
						$jenisAlokasi = $data['data'][$i]['jenis_alokasi'];
						$jenisAlokasiKode = $data['data'][$i]['jenis_alokasi_kode'];
						$dataList[$x]['kode']= '';
						$jenisAlokasi = $data['data'][$i]['jenis_alokasi'];
					 	$dataList[$x]['kode']= '';
						$dataList[$x]['nama']= ($jenisAlokasiKode == 1) ? 
											'<strong>'.$labelPusat.'</strong>' : '<strong>'.$labelUnit.'</strong>';
						$dataList[$x]['alokasi_sumber_nilai']= $data['data'][$i]['besar_alokasi_sumber'];
						$dataList[$x]['kode_unit_alokasi']= '';
						$dataList[$x]['nama_unit_alokasi']= '';
						$dataList[$x]['alokasi_nilai']= '';
						$dataList[$x]['nomor'] = '';
						$dataList[$x]['bold'] ='bold';
						$dataList[$x]['tipe'] ='';
						$dataList[$x]['id'] = '';
						$dataList[$x]['pusat_id'] ='';
						$dataList[$x]['unit_id'] = '';
						$nomor = 1;
						//$totalAlokasi =0;
				
				}
				$x++;
			}
			//print_r($dataList);
			
			$_SESSION['no_urut'] = $nomor;
			unset($data['data']);
			//$dataList = $data['data'];
			foreach($dataList as $key => $value){
				//$no = $nomor + $key + $data['startRec'];
				//$dataList[$key]['nomor']  = $no;
				
				if($dataList[$key]['tipe']== 1){
					//$dataList[$key]['alokasi_nilai'] = $getTotalAlokasi[$dataList[$key]['group_id']];
					$dataList[$key]['is_display']="";
					$dataList[$key]['is_bold']="bold";
					$dataList[$key]['url_edit']= Dispatcher::Instance()->GetUrl(
                                                        'penerima_alokasi_unit', 
                                                        'inputPenerimaAlokasiUnit', 
                                                        'view', 
                                                        'html').
                                                        '&data_id='.
                                                        Dispatcher::Instance()->Encrypt($dataList[$key]['id']).
                                                        '&pusat_id='.
                                                        Dispatcher::Instance()->Encrypt($dataList[$key]['pusat_id']).
                                                        '&unit_id='.
                                                        Dispatcher::Instance()->Encrypt($dataList[$key]['unit_id']);
                   
					$dataList[$key]['url_delete']= Dispatcher::Instance()->GetUrl(
   															'confirm', 
														    'confirmDelete', 
															'do', 
															'html') . 
															'&urlDelete='. $urlSingleDelete.
															'&urlReturn='.$urlSingleReturn .
            												'&id='.
															Dispatcher::Instance()->Encrypt($dataList[$key]['id']).
            												'&label='.$label.
															'&dataName='.$dataList[$key]['kode'].' '.$dataList[$key]['nama'];				
				} else {
					$dataList[$key]['is_display']="display:none";
					$dataList[$key]['is_bold']="normal";
				}
				
				if($dataList[$key]['alokasi_sumber_nilai'] !=''){
					$dataList[$key]['alokasi_sumber_nilai'] = '<strong>'.
					$dataList[$key]['alokasi_sumber_nilai'].'</strong>';
				}
							
				if($dataList[$key]['alokasi_nilai'] != ''){ 
					$dataList[$key]['alokasi_nilai'] = $dataList[$key]['alokasi_nilai'];
				}	
				$this->mrTemplate->AddVars('list_data_item', $dataList[$key], 'DATA_');
				$this->mrTemplate->parseTemplate('list_data_item', 'a');
			}
			
		}
		/**
		 * end tampilkan data
		 */		
	}
}
?>