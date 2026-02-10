<?php

/**
 * 
 * class ViewLapIhktisarBelanjaPerPK
 * @package lap_ihktisar_belanja_per_pk
 * @todo Untuk menampilkan data
 * @subpackage response
 * @since 12 September 2012
 * @SystemAnalyst Nanang Ruswianto <nanang@gamatechno.com>
 * @author noor hadi <noor.hadi@gamatechno.com>
 * @copyright 2012 Gamatechno Indonesia
 * 
 */
 
require_once GTFWConfiguration::GetValue('application', 'docroot') . 
            'module/lap_ihktisar_belanja_per_pk/business/LapIhktisarBelanjaPerPK.class.php';

require_once GTFWConfiguration::GetValue('application', 'docroot') . 
	'module/user_unit_kerja/business/UserUnitKerja.class.php';            

class ViewLapIhktisarBelanjaPerPK extends HtmlResponse
{
	
	/**
	 * untuk menginstanskan class database object
	 */
	protected $mDBObj; 
	protected $mModulName;
	protected $mData;
	
	public function __construct()
	{
		parent::__construct();
		$this->mDBObj = new LapIhktisarBelanjaPerPK();
		$this->mModulName = 'lap_ihktisar_belanja_per_pk';
	}
	
	public function TemplateModule()
	{
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') .     
                'module/'.$this->mModulName.'/template');
		$this->SetTemplateFile('view_lap_ihktisar_belanja_per_pk.html');
	}
	
	public function ProcessRequest()
	{
		$_POST = $_POST->AsArray();
		$userUnitKerjaObj = new UserUnitKerja();

		$userId = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
		$unit = $userUnitKerjaObj->GetUnitKerjaUser($userId);
		
			if($_POST['btncari']) {
				$this->mData['tahun_anggaran'] = $_POST['tahun_anggaran'];
				$this->mData['unitkerja'] = $_POST['unitkerja'];
				$this->mData['unitkerja_label'] = $_POST['unitkerja_label'];
				$unitkerja = $userUnitKerjaObj->GetSatkerUnitKerja($this->mData['unitkerja']);
			} elseif($_GET['cari'] != "") {
				$get = $_GET->AsArray();
				$this->mData['tahun_anggaran'] = Dispatcher::Instance()->Decrypt($_GET['tahun_anggaran']);
				$this->mData['unitkerja'] = Dispatcher::Instance()->Decrypt($_GET['unitkerja']);
				$this->mData['unitkerja_label'] = Dispatcher::Instance()->Decrypt($_GET['unitkerja_label']);
				$unitkerja = $userUnitKerjaObj->GetSatkerUnitKerja($this->mData['unitkerja']);
			} else {
				$tahun_anggaran = $this->mDBObj->GetTahunAnggaranAktif();
				$this->mData = $_POST;
				$this->mData['tahun_anggaran'] = $tahun_anggaran['id'];
				$this->mData['unitkerja'] =$unit['unit_kerja_id'];// $unit['satker_id'];
				$this->mData['unitkerja_label'] =  $unit['unit_kerja_nama'];//$unit['satker_nama'];
			}
			
			$this->mData['total_sub_unit_kerja'] = 
					$userUnitKerjaObj->GetTotalSubUnitKerja($unit['unit_kerja_id']);
					
			$arr_tahun_anggaran = $this->mDBObj->GetComboTahunAnggaran();
			Messenger::Instance()->SendToComponent(
												'combobox', 
												'Combobox', 
												'view', 
												'html', 
												'tahun_anggaran',
												 array(
												 		'tahun_anggaran', 
														 $arr_tahun_anggaran, 
														 $this->mData['tahun_anggaran'], '-', 
														 ' style="width:200px;" id="tahun_anggaran"'), 
												 Messenger::CurrentRequest);
	 
		//view
			$itemViewed = 20;
			$currPage = 1;
			$startRec = 0 ;
			if(isset($_GET['page'])) {
				$currPage = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
				$startRec =($currPage-1) * $itemViewed;
			}
					
			$get_data = $this->mDBObj->GetDataLaporan(
												$this->mData['tahun_anggaran'],
												$this->mData['unitkerja'],
												$startRec,
												$itemViewed);
			$totalData = $this->mDBObj->GetCountDataLaporan();
			$url = Dispatcher::Instance()->GetUrl(
										Dispatcher::Instance()->mModule, 
									  	Dispatcher::Instance()->mSubModule, 
								  		Dispatcher::Instance()->mAction, 
								  		Dispatcher::Instance()->mType . 
								  		'&tahun_anggaran=' . 
								  		Dispatcher::Instance()->Encrypt($this->mData['tahun_anggaran']) . 
								  		'&unitkerja=' . 
								  		Dispatcher::Instance()->Encrypt($this->mData['unitkerja']) . 
								  		'&unitkerja_label=' . 
								  		Dispatcher::Instance()->Encrypt($this->mData['unitkerja_label']) . 
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
		$return['get_data'] = $get_data;
		$return['get_header']  = $this->mDBObj->GetPaguBasHeader($this->mData['tahun_anggaran'],$this->mData['unitkerja']);
		$return['get_nominal_pengeluaran'] = $this->mDBObj->GetNominalPengeluaran($this->mData['tahun_anggaran'],$this->mData['unitkerja']);		
		$return['get_nominal_pengeluaran_per_k']=$this->mDBObj->GetNominalPengeluaranPerK($this->mData['tahun_anggaran'],$this->mData['unitkerja']);		
		$return['get_nominal_pengeluaran_per_p']=$this->mDBObj->GetNominalPengeluaranPerP($this->mData['tahun_anggaran'],$this->mData['unitkerja']);		
		
		$return['userId']  = $userId;
		return $return;
   }
   
   public function ParseTemplate($data = NULL)
   {
	   
		$this->mrTemplate->AddVar('content', 'URL_SEARCH', 
				Dispatcher::Instance()->GetUrl(
												$this->mModulName, 
												'lapIhktisarBelanjaPerPK', 
												'view', 
												'html'));
												
		$this->mrTemplate->AddVar('content', 'URL_RESET', 
				Dispatcher::Instance()->GetUrl(
												$this->mModulName, 
												'lapIhktisarBelanjaPerPK', 
												'view', 
												'html'));
		
		$this->mrTemplate->AddVar('content', 'URL_CETAK', 
				Dispatcher::Instance()->GetUrl($this->mModulName, 
												'CetakLapIhktisarBelanjaPerPK', 
												'view', 
												'html').
												'&tgl=' . Dispatcher::Instance()->Encrypt($this->mData['tahun_anggaran']) . 
												'&id='. Dispatcher::Instance()->Encrypt($data['userId']) . 
												'&unitkerja=' . Dispatcher::Instance()->Encrypt($this->mData['unitkerja']) . 
												'&unitkerja_label=' . Dispatcher::Instance()->Encrypt($this->mData['unitkerja_label']));	
		
		$this->mrTemplate->AddVar('content', 'URL_EXCEL', 
				Dispatcher::Instance()->GetUrl($this->mModulName, 
												'ExcelLapIhktisarBelanjaPerPK', 
												'view', 
												'xls').
												'&tgl=' . Dispatcher::Instance()->Encrypt($this->mData['tahun_anggaran']) . 
												'&id='. Dispatcher::Instance()->Encrypt($data['userId']) . 
												'&unitkerja=' . Dispatcher::Instance()->Encrypt($this->mData['unitkerja']) . 
												'&unitkerja_label=' . Dispatcher::Instance()->Encrypt($this->mData['unitkerja_label']));	
		
		$this->mrTemplate->AddVar('content', 'UNITKERJA', $this->mData['unitkerja']);
		if($this->mData['total_sub_unit_kerja'] > 0){
			$this->mrTemplate->AddVar('cek_unitkerja_parent', 'IS_PARENT','YES');
		} else {
			$this->mrTemplate->AddVar('cek_unitkerja_parent', 'IS_PARENT', 'NO');
		}
			$this->mrTemplate->AddVar('cek_unitkerja_parent', 'UNITKERJA_LABEL', 
												$this->mData['unitkerja_label']);
			$this->mrTemplate->AddVar('cek_unitkerja_parent', 'URL_POPUP_UNITKERJA', 
							 Dispatcher::Instance()->GetUrl($this->mModulName ,
																'popupUnitkerja', 
																'view', 
																'html'));	
																
         $listDataItem= $data['get_data'];
        
		if(empty($listDataItem)){
			$this->mrTemplate->AddVar('list_data', 'IS_EMPTY', 'YES');		
	    } else {
			$this->mrTemplate->AddVar('list_data', 'IS_EMPTY', 'NO');
			
			$programId ='';
			$kegiatanId ='';
			$x = 0;
			for($i = 0 ; $i < sizeof($listDataItem); )
			{
				if(($programId == $listDataItem[$i]['program_id']) && 
                   ($kegiatanId == $listDataItem[$i]['kegiatan_id']) ){
					   $dataLaporan[$x]['kode'] = $listDataItem[$i]['sub_kegiatan_kode'];
					   $dataLaporan[$x]['nama'] = $listDataItem[$i]['sub_kegiatan_nama'].'<br />'.
												  '[ '.$listDataItem[$i]['rkakl_sub_kegiatan_nama'].' ]';
					   $dataLaporan[$x]['sub_keg_id'] = $listDataItem[$i]['sub_kegiatan_id'];
					   $dataLaporan[$x]['keg_id'] ='';
					   $dataLaporan[$x]['prog_id'] ='';
					   $dataLaporan[$x]['class_name'] ='';
					   $dataLaporan[$x]['font_style'] ='';
					   $unit = explode(',',$listDataItem[$i]['unit']);
					   $dataLaporan[$x]['unit'] = implode('<br />',array_unique($unit));
					   //$dataLaporan[$x]['padding'] =20;
					   $i++;
				} elseif($programId != $listDataItem[$i]['program_id']){
					$programId = $listDataItem[$i]['program_id'];
					$dataLaporan[$x]['kode'] = $listDataItem[$i]['program_kode'];
					$dataLaporan[$x]['nama'] = $listDataItem[$i]['program_nama'].'<br />'.
												  '[ '.$listDataItem[$i]['rkakl_kegiatan_nama'].' ]';
					$dataLaporan[$x]['font_style'] ='font-weight:bold';
					$dataLaporan[$x]['class_name'] ='table-common-even1';
					 $dataLaporan[$x]['sub_keg_id'] = '';
					 $dataLaporan[$x]['keg_id'] ='';
					 $dataLaporan[$x]['prog_id'] =$listDataItem[$i]['program_id'];
					 $dataLaporan[$x]['unit']='';
				} elseif($kegiatanId != $listDataItem[$i]['kegiatan_id']){
					$kegiatanId  = $listDataItem[$i]['kegiatan_id'];
					$dataLaporan[$x]['kode'] = $listDataItem[$i]['kegiatan_kode'];
					$dataLaporan[$x]['nama'] = $listDataItem[$i]['kegiatan_nama'].'<br />'.
												  '[ '.$listDataItem[$i]['rkakl_output_nama'].' ] ';
					$dataLaporan[$x]['class_name'] ='table-common-even1';
					$dataLaporan[$x]['font_style'] ='font-style:italic';
					$dataLaporan[$x]['sub_keg_id'] = '';
					$dataLaporan[$x]['keg_id'] =$listDataItem[$i]['kegiatan_id'];
					$dataLaporan[$x]['prog_id'] ='';
					$dataLaporan[$x]['unit']='';
				}
				$x++;
			}
		
            $header = $data['get_header'];
            //var_dump($header);
            $max_header = sizeof($header);
            /**
             * membuat header
             */           
            if($max_header > 0){
            $this->mrTemplate->AddVar('content', 'MAX_HEADER', ($max_header));
            for($n=0;$n < $max_header;$n++) {
                 $this->mrTemplate->AddVars('data_header_item', $header[$n], '');
				 $this->mrTemplate->parseTemplate('data_header_item', 'a');
			}
            }
            /**
             * end
             */ 
           $n = $data['get_nominal_pengeluaran'];
           $keg = $data['get_nominal_pengeluaran_per_k'];
           $prog = $data['get_nominal_pengeluaran_per_p'];
           
			for($k = 0;$k < sizeof($dataLaporan);$k++){
                if($max_header > 0){
                    for($f=0;$f <sizeof($header);$f++) {	
						
                            if(isset($dataLaporan[$k]['sub_keg_id']) && $dataLaporan[$k]['sub_keg_id'] != ''){
								$dataLaporan[$k]['colom'].='<td align="right">'.
											number_format($n[$dataLaporan[$k]['sub_keg_id']][$header[$f]['id']],0,',','.').'</td>';
							}elseif(isset($dataLaporan[$k]['keg_id']) && $dataLaporan[$k]['keg_id'] !=''){
								$dataLaporan[$k]['colom'].='<td align="right">'.
											number_format($keg[$dataLaporan[$k]['keg_id']][$header[$f]['id']],0,',','.').'</td>';
							}elseif(isset($dataLaporan[$k]['prog_id']) && $dataLaporan[$k]['prog_id'] !=''){
								$dataLaporan[$k]['colom'].='<td align="right">'.
											number_format($prog[$dataLaporan[$k]['prog_id']][$header[$f]['id']],0,',','.').'</td>';
							} else {
                                $dataLaporan[$k]['colom'].='<td align="right">'.
                                number_format(0,0,',','.').'</td>';								
							}
                    }
                }				
				$this->mrTemplate->AddVars('list_data_item', $dataLaporan[$k], '');
				$this->mrTemplate->parseTemplate('list_data_item', 'a');
			}
		}
        
   }
}
