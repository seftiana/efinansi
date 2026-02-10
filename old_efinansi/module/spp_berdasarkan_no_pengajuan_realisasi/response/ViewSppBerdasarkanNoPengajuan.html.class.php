<?php

/**
 * 
 * @package realisasi_pencairan_2
 * @sub_package response
 * @class ViewSppBerdasarkanNoPengajuan 
 * @author noor hadi <noor.hadi@gamatechno.com>
 * @analyst nanang ruswianto <nanang@gamatechno.com>
 * @copyright 2013 gamatechno indonesia
 *  
 */
 
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/realisasi_pencairan_2/business/SppBerdasarkanNoPengajuan.class.php';

require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/user_unit_kerja/business/UserUnitKerja.class.php';

require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
'main/function/date.php';
	
class ViewSppBerdasarkanNoPengajuan extends HtmlResponse 
{

	
	protected $mData;
  
	protected $mDBObj;
	protected $mUserUnitKerja;
	protected $mTotalSubUnitKerja;
  
	protected $mUserId;
	protected $mRole;

	public function __construct()
	{  
		$this->mDBObj = new SppBerdasarkanNoPengajuan();
		$this->mUserId = Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
		$this->mUserUnitKerja = new UserUnitKerja();

		//$this->mRole         = $this->objUnitKerja->GetRoleUser($this->mUserId);
	}

	public function TemplateModule() 
	{
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
			'module/realisasi_pencairan_2/template');
		$this->SetTemplateFile('view_spp_berdasarkan_no_pengajuan.html');
	}

	public function ProcessRequest() 
	{
		$tahunAnggaranAktif = $this->mDBObj->GetTahunAnggaranAktif();
		$unitkerja = $this->mUserUnitKerja->GetUnitKerjaUser($this->mUserId);
		$this->mTotalSubUnitKerja = $this->mUserUnitKerja->GetTotalSubUnitKerja($unitkerja['unit_kerja_id']);
	
		$dataTahunAnggaran = $this->mDBObj->GetTahunAnggaran();

		if(isset($_POST)){
			if(is_object($_POST)){
				$_POST      = $_POST->AsArray();
			}else{	
				$_POST      = $_POST;
			}
		}
		if(is_object($_GET)){
			$GET           = $_GET->AsArray();
		}else{
			$GET           = $_GET;
		}
		
		$post             = array();

		if(isset($_POST['btnTampilkan'])) {
			$post['ta_id'] = $_POST['data']['ta_id'];
			$post['unit_id'] = $_POST['data']['unit_id'];
			$post['unit_nama'] = $_POST['data']['unit_nama'];
			$post['nomor'] = trim($_POST['data']['nomor']);
			$post['program_id'] = $_POST['data']['program_id'];
		} elseif(isset($_GET['search'])) {
			$post['ta_id'] = Dispatcher::Instance()->Decrypt($GET['ta_id']);
			$post['unit_id'] = Dispatcher::Instance()->Decrypt($GET['unit_id']);
			$post['unit_nama'] = Dispatcher::Instance()->Decrypt($GET['unit_nama']);
			$post['nomor'] = Dispatcher::Instance()->Decrypt($GET['nomor']);
			$post['program_id'] = Dispatcher::Instance()->Decrypt($GET['program_id']);
		} else {
			$post['ta_id'] = $tahunAnggaranAktif;
			$post['unit_id'] = $unitkerja['unit_kerja_id'];
			$post['unit_nama'] = $unitkerja['unit_kerja_nama'];
			$post['nomor'] = '';
			$post['program_id'] = '';
		}

		// start build uri
		//$uri = urldecode(http_build_query($query));
		// inisialisasi data program berdasarkan tahun anggaran   

		// start: combobox
		// combobox : Tahun Anggaran
		Messenger::Instance()->SendToComponent(
												'combobox', 
												'Combobox', 
												'view', 
												'html', 
												'data[ta_id]',
												array(
													'data[ta_id]',
													$dataTahunAnggaran,
													$post['ta_id'],
													'kosong', 
													' onchange="getProgram(this.value);"'
												), Messenger::CurrentRequest);
		// Combobox: Program Kegiatan
		$dataProgram = $this->mDBObj->GetDataProgram($post['ta_id']);
		Messenger::Instance()->SendToComponent(
											'combobox', 
											'Combobox', 
											'view', 
											'html', 
											'data[program_id]',
											array(
												'data[program_id]',
												$dataProgram, 
												$post['program_id'],
												'true', 
												'id="program_id"'
											), Messenger::CurrentRequest);		
		$offset = 0;
		$limit = 20;
		$page = 0;
		
		if(isset($_GET['page'])){
			$page    = (string) $_GET['page']->StripHtmlTags()->SqlString()->Raw();
			$offset  = ($page - 1) * $limit;
		}
		#paging url
		$url    = Dispatcher::Instance()->GetUrl(
											Dispatcher::Instance()->mModule,
											Dispatcher::Instance()->mSubModule,
											Dispatcher::Instance()->mAction,
											Dispatcher::Instance()->mType
										).
										'&ta_id='. Dispatcher::Instance()->Encrypt($post['ta_id']).
										'&unit_id=' .Dispatcher::Instance()->Encrypt($post['unit_id']).
										'&unit_nama=' .Dispatcher::Instance()->Encrypt($post['unit_nama']).
										'&nomor='.Dispatcher::Instance()->Encrypt($post['nomor']).
										'&program_id='.Dispatcher::Instance()->Encrypt($post['program_id']).
										'&search='.Dispatcher::Instance()->Encrypt(1);
      
      $destination_id      = "subcontent-element";
      $dataList            = $this->mDBObj->GetData($post['ta_id'],$post['unit_id'],$post['program_id'],$post['nomor'],$offset, $limit);

      $total_data          = $this->mDBObj->GetCountData();
      #send data to pagging component
      Messenger::Instance()->SendToComponent(
											'paging', 
											'Paging', 
											'view', 
											'html', 
											'paging_top', 
												array(
													$limit,
													$total_data, 
													$url, 
													$page, 
													$destination_id
												),	Messenger::CurrentRequest
											);
      // check status tahun periode  
      
      // parsing messenger
      $msg                 = Messenger::Instance()->Receive(__FILE__);
      if ($msg) {
         $return['msg']['message']     = $msg[0][1];
         $return['style']              = $msg[0][2];
      }
    
      $return['dataList']           = $dataList;
      $return['post']               = $post;
      $return['start']              = $offset+1;
      $return['periodeTahun']       = $checkTa;     
      
      
      return $return;
   }

	public function ParseTemplate($data = NULL) 
	{
		$role          = $this->mRole;
		$post          = $data['post'];
		$dataList      = $data['dataList'];
		$start         = $data['start'];
		$periodeTahun  = $data['periodeTahun'];
		$statusTA      = strtoupper($periodeTahun['is_aktif']) === 'Y' ? 'YES' : 'NO';
      
		
		$urlSearch = Dispatcher::Instance()->GetUrl(
													'realisasi_pencairan_2', 
													'SppBerdasarkanNoPengajuan',
													'view', 
													'html'
												);
      
		$urlKembali = Dispatcher::Instance()->GetUrl(
													'realisasi_pencairan_2', 
													'realisasiPencairan', 
													'view', 
													'html'
												);            
      
		$urlCreate = Dispatcher::Instance()->GetUrl(
													'realisasi_pencairan_2', 
													'CreateSppBerdasarkanNoPengajuan', 
													'view', 
													'html'
												);
      
		$urlPopupDetail = Dispatcher::Instance()->GetUrl(
													'realisasi_pencairan_2', 
													'PopupSppBerdasarkanNoPengajuanDetail', 
													'view', 
													'html'
												);
      
		$urlPopupUnitKerja = Dispatcher::Instance()->GetUrl(
													'realisasi_pencairan_2', 
													'PopupUnitkerja', 
													'view', 
													'html'
												).'&pop=home';
      
		$urlCetak = Dispatcher::Instance()->GetUrl(
													'realisasi_pencairan_2', 
													'CetakSppBerdasarkanNoPengajuan',
													'view', 
													'html'
												);
												
		$urlReset = Dispatcher::Instance()->GetUrl(
													'realisasi_pencairan_2', 
													'SppBerdasarkanNoPengajuan',
													'view', 
													'html'
												);

		$urlProgram          = Dispatcher::Instance()->GetUrl(
													'realisasi_pencairan_2', 
													'Program', 
													'view', 
													'json'
													);      
		// end popup
		$this->mrTemplate->AddVar('periode_tahun', 'IS_AKTIF', $statusTA);
		$this->mrTemplate->AddVar('periode_tahun', 'URL_ADD', $urlAdd);      
		$this->mrTemplate->AddVar('periode_tahun', 'URL_CETAK_SPP_BERDASARKAN_PENGAJUAN', $urlCetakSppBerdasarkanPengajuan);
		$this->mrTemplate->AddVar('content', 'URL_KEMBALI', $urlKembali);
		$this->mrTemplate->AddVar('content', 'URL_CREATE', $urlCreate);
		$this->mrTemplate->AddVars('content', $post, '');
		$this->mrTemplate->Addvar('content', 'UNIT_KERJA_ID', $this->mData['unit_kerja_id']);
		
		if($this->mTotalSubUnitKerja > 0){
			$this->mrTemplate->AddVar('cek_unitkerja_parent', 'IS_PARENT','YES');
		} else {
			$this->mrTemplate->AddVar('cek_unitkerja_parent', 'IS_PARENT', 'NO');
		}			
			$this->mrTemplate->AddVar('cek_unitkerja_parent', 'UNIT_NAMA', $post['unit_nama']);
			$this->mrTemplate->AddVar('cek_unitkerja_parent', 'POPUP_UNIT_KERJA', $urlPopupUnitKerja);
				
		
		$this->mrTemplate->AddVar('content', 'URL_ADD', $urlAdd);
		$this->mrTemplate->AddVar('content', 'POPUP_SUBUNIT_KERJA', $urlPopupSubUnit);
		$this->mrTemplate->AddVar('content', 'POPUP_DETAIL', $urlPopupDetail);
		$this->mrTemplate->AddVar('content', 'URL_RESET', $urlReset);
		$this->mrTemplate->AddVar('content', 'URL_SEARCH', $urlSearch);
		$this->mrTemplate->AddVar('content', 'URL_PROGRAM', $urlProgram);
	
		if (isset ($data['msg'])) {
			$this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
			$this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $data['msg']['message']);
			if($data['msg']['action']=='msg'):
				$class   ='notebox-done';
			else:
				$class   = 'notebox-warning';
			endif;
			
			if($data['style']){
				$class   = $data['style'];
			}
			
			$this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $class);
		}

			//mulai bikin tombol delete
			$label = "Surat Permintaan Pembayaran Tagihan";
			$urlDelete = Dispatcher::Instance()->GetUrl(
												'realisasi_pencairan_2', 
												'deleteSppBerdasarkanNoPengajuan', 
												'do', 
												'html');

															
			$urlReturn = Dispatcher::Instance()->GetUrl(
												'realisasi_pencairan_2',  
												'SppBerdasarkanNoPengajuan',
												'view', 
												'html') . 
												'&ta_id=' . Dispatcher::Instance()->Encrypt($post['ta_id']) .
												'&unit_id=' . Dispatcher::Instance()->Encrypt($post['unit_id']) .
												'&unit_nama='. Dispatcher::Instance()->Encrypt($post['unit_nama']) .
												'&nomor=' . Dispatcher::Instance()->Encrypt($post['nomor']) .
												'&search=' . Dispatcher::Instance()->Encrypt(1);
												
			Messenger::Instance()->Send(
										'confirm', 
										'confirmDelete', 
										'do', 
										'html', 
										array(
												$label, 
												$urlDelete, 
												$urlReturn),
										Messenger::NextRequest);
										
			$this->mrTemplate->AddVar('content', 'URL_DELETE', 
									Dispatcher::Instance()->GetUrl(
																	'confirm', 
																	'confirmDelete', 
																	'do', 
																	'html'));
	
		if(empty($dataList)){
			$this->mrTemplate->AddVar('data_grid', 'IS_DATA_EMPTY', 'YES');
		}else{
			$this->mrTemplate->AddVar('data_grid', 'IS_DATA_EMPTY', 'NO');
			//print_r($dataList);
			/**
			 * untuk hapus data satu per satu
			 */
			 
			$urlAccept     = 'realisasi_pencairan_2|deleteSppBerdasarkanNoPengajuan|do|html-cari-';
			$urlReturn     = 'realisasi_pencairan_2|SppBerdasarkanNoPengajuan|view|html-cari-';
			$label         = 'SPPT';          
			$urlDelete     = Dispatcher::Instance()->GetUrl(
										'confirm', 
										'confirmDelete', 
										'do', 
										'html') .
										'&urlDelete=' . $urlAccept.
										'&urlReturn=' .$urlReturn.
										'&label=' . $label;
			/**
			 * end
			 */               
			$programId =NULL; 
			$no= 0;
			$x= 0;
			//foreach ($dataList as $key => $list) {
			for( $i =0; $i < sizeof($dataList);) {
				if($programId == $dataList[$i]['program_id']){										
					if( $i == 0) $this->mrTemplate->AddVar('content', 'FIRST_NUMBER', $no + $start );
					if( $i == sizeof($dataList)-1) $this->mrTemplate->AddVar('content', 'LAST_NUMBER', $no + $start );
			
					
					$list[$x]['no'] = $no + $start;		
					$list[$x]['row_style'] = 'font-weight:normal';					 
					$list[$x]['tanggal'] = IndonesianDate($dataList[$i]['tanggal'],"YYYY-MM-DD");					
					$list[$x]['nomor_sppt'] = $dataList[$i]['nomor_sppt'];
					$list[$x]['kode'] = $dataList[$i]['unit_kerja_kode'];
					$list[$x]['nama'] = $dataList[$i]['unit_kerja_nama'];					
					$list[$x]['nominal_f'] = number_format($dataList[$i]['nominal'],0,',','.');
					$list[$x]['keterangan'] = $dataList[$i]['keterangan'];
					
					
					if($dataList[$i]['count_approval'] < 0){
						$list[$x]['display_item'] = 'none';
						$list[$x]['url_edit']= '';
						$list[$x]['url_hapus']= '';
						$list[$x]['id'] = '';
						$list[$x]['ch'] = '';
						$list[$x]['nomor_sppt_ch'] = '';
					} else {
						$list[$x]['display_item'] = 'block';
						$list[$x]['url_edit']= $urlCreate.'&id='.Dispatcher::Instance()->Encrypt($dataList[$i]['id']);
						$list[$x]['url_hapus']= $urlDelete.'&id='.Dispatcher::Instance()->Encrypt($dataList[$i]['id']).
									'&dataName='.
									Dispatcher::Instance()->Encrypt('No SPPT: '.$dataList[$i]['nomor_sppt']);
						$list[$x]['id'] = $dataList[$i]['id'];
						$list[$x]['ch'] = $no + $start;
						$list[$x]['nomor_sppt_ch'] = $dataList[$i]['nomor_sppt'];
					}
					
					
					$list[$x]['url_detail']= $urlPopupDetail.'&id='.Dispatcher::Instance()->Encrypt($dataList[$i]['id']);
					$list[$x]['url_cetak']= $urlCetak.'&id='.Dispatcher::Instance()->Encrypt($dataList[$i]['id']);
					
					$this->mrTemplate->AddVars('data_item', $list[$x], 'DATA_');
					$this->mrTemplate->parseTemplate('data_item', 'a');
					$no++;
					$i++;
				} elseif($programId != $dataList[$i]['program_id']){
					$programId = $dataList[$i]['program_id'];
					$list[$x]['display'] = 'none';
					$list[$x]['row_style'] = 'font-weight:bold';
					$list[$x]['tanggal'] = '';
					$list[$x]['nomor_sppt'] = '';
					$list[$x]['kode'] = $dataList[$i]['program_kode'];
					$list[$x]['nama'] = $dataList[$i]['program_nama'];					
					$list[$x]['nominal_f'] =  number_format($dataList[$i]['total_nominal'],0,',','.');
					$list[$x]['keterangan'] = '';
					$list[$x]['id'] = '';
					$list[$x]['url_edit']= '';
					$list[$x]['url_detail']= '';
					$list[$x]['url_cetak']= '';
					$list[$x]['url_hapus']= '';
					$this->mrTemplate->AddVars('data_item', $list[$x], 'DATA_');
					$this->mrTemplate->parseTemplate('data_item', 'a');						
				}
				$x++;
			}
		}
	}
}

?>