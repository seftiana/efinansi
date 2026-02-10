<?php

/**
 * 
 * class ViewCetakLapRincianBelanjaPerUnit
 * @package lap_rincian_belanja_per_unit
 * @todo Untuk menampilkan data
 * @subpackage response
 * @since 12 September 2012
 * @SystemAnalyst Nanang Ruswianto <nanang@gamatechno.com>
 * @author noor hadi <noor.hadi@gamatechno.com>
 * @copyright 2012 Gamatechno Indonesia
 * 
 */
 
require_once GTFWConfiguration::GetValue('application', 'docroot') . 
'module/lap_rincian_belanja_per_unit/business/LapRincianBelanjaPerUnit.class.php';


class ViewCetakLapRincianBelanjaPerUnit extends HtmlResponse
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
		$this->mDBObj 		= new LapRincianBelanjaPerUnit();
		$this->mModulName = 'lap_rincian_belanja_per_unit';
	}
	
	public function TemplateModule()
	{
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') .   
			'module/'.$this->mModulName.'/template');
		$this->SetTemplateFile('view_cetak_lap_rincian_belanja_per_unit.html');
	}

   public function TemplateBase() 
   {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 
		'main/template/');
	   $this->SetTemplateFile('document-print.html');
      $this->SetTemplateFile('layout-common-print.html');
   }
   	
	public function ProcessRequest()
	{
		$tahun_anggaran 	= Dispatcher::Instance()->Decrypt($_GET['tgl']);
		$unitkerja_label 	= Dispatcher::Instance()->Decrypt($_GET['unitkerja_label']);
		$unitkerja 			= Dispatcher::Instance()->Decrypt($_GET['unitkerja']);
		$userId 				= Dispatcher::Instance()->Decrypt($_GET['id']);
		$return['unit_kerja_id'] 	= $unitkerja;
		$return['unit_kerja_nama'] = $unitkerja_label;
		$return['ta'] 		= $this->mDBObj->GetTahunAnggaran($tahun_anggaran);		
		

		$return['get_data'] 				= $this->mDBObj->GetDataLaporan($tahun_anggaran,$unitkerja);
		$return['get_total_per_mak'] 	= $this->mDBObj->GetTotalPerMak($tahun_anggaran,$unitkerja);
		$return['get_total_per_pagu'] = $this->mDBObj->GetTotalPerPagu($tahun_anggaran,$unitkerja);
		$return['get_total_per_sk'] 	= $this->mDBObj->GetTotalPerSk($tahun_anggaran,$unitkerja);
		$return['get_total_per_k'] 	= $this->mDBObj->GetTotalPerk($tahun_anggaran,$unitkerja);
		$return['get_total_per_p'] 	= $this->mDBObj->GetTotalPerP($tahun_anggaran,$unitkerja);
		$return['get_total_per_u'] 	= $this->mDBObj->GetTotalPerU($tahun_anggaran,$unitkerja);
		$return['get_total_all'] 		= $this->mDBObj->GetTotalAll($tahun_anggaran,$unitkerja);
		$return['get_sd'] =$this->mDBObj->GetSumberDana($tahun_anggaran,$unitkerja);		
		return $return;
   }
   
   public function ParseTemplate($data = NULL)
   {
		$taSebelum = !empty($data['ta_kemarin']['name']) ? $data['ta_kemarin']['name'] :$data['ta']['name'];
	    $this->mrTemplate->AddVar('content', 'TAHUN_SEBELUM_TH_ANGGAR', $taSebelum);
	    $this->mrTemplate->AddVar('content', 'TAHUN_SEKARANG_TH_ANGGAR', ($data['ta']['name']));	   		
		
		$this->mrTemplate->AddVar('content', 'TAHUN_PERIODE', ($data['ta']['name']));
		
		$this->mrTemplate->AddVar('content', 'UNIT_KERJA_LABEL', ($data['unit_kerja_nama']));	
		
         $listDataItem= $data['get_data'];
        
		if(empty($listDataItem)){
			$this->mrTemplate->AddVar('list_data', 'IS_EMPTY', 'YES');		
	    } else {
			$this->mrTemplate->AddVar('list_data', 'IS_EMPTY', 'NO');
			
			$unitId ='';
			$programId ='';
			$kegiatanId ='';
			$subKegiatanId ='';
			$makpId = '';
			$makId = '';
			
			$x = 0;
			$no=0;
			$getTotalPerMak = $data['get_total_per_mak'];
			$getTotalPerPagu = $data['get_total_per_pagu'];
			$getTotalPerSk = $data['get_total_per_sk'];
			$getTotalPerK = $data['get_total_per_k'];
			$getTotalPerP = $data['get_total_per_p'];
			$getTotalPerU = $data['get_total_per_u'];
			
			for($i = 0 ; $i < sizeof($listDataItem); )
			{
				if(($unitId == $listDataItem[$i]['unit_id']) && 
					($programId == $listDataItem[$i]['program_id']) && 
					($kegiatanId == $listDataItem[$i]['kegiatan_id']) &&
					($subKegiatanId == $listDataItem[$i]['sub_kegiatan_id']) &&
					($makpId == $listDataItem[$i]['mak_parent_id']) &&
					($makId == $listDataItem[$i]['mak_id']) 
					){
						
					$dataLaporan[$x]['kode'] ='';
					if(!empty($listDataItem[$i]['sumber_dana'])){
						$sd = explode(',',$listDataItem[$i]['sumber_dana']);
						$dataLaporan[$x]['sumber_dana_sebelum'] =($listDataItem[$i]['nominal_sebelum'] > 0) ? $sd[0] : '';
						$dataLaporan[$x]['sumber_dana_sekarang'] =($listDataItem[$i]['nominal_sekarang'] > 0) ? (isset($sd[1]) ? $sd[1] : $sd[0]) : '';
						
						//$dataLaporan[$x]['sumber_dana_sebelum'] = $sd[0];
						//$dataLaporan[$x]['sumber_dana_sekarang'] =  $sd[1];
					}else{
						$dataLaporan[$x]['sumber_dana_sebelum'] ='';
						$dataLaporan[$x]['sumber_dana_sekarang'] ='';
					}
					
					$dataLaporan[$x]['nama'] = ' - '.$listDataItem[$i]['komponen_nama'];
					$dataLaporan[$x]['volume_sebelum'] = $listDataItem[$i]['volume_sebelum'];
					$dataLaporan[$x]['volume_sekarang'] = $listDataItem[$i]['volume_sekarang'];
					$dataLaporan[$x]['target_sebelum'] = $this->mDBObj->SetFormatAngka($listDataItem[$i]['nominal_sebelum']);
					$dataLaporan[$x]['target_sekarang'] = $this->mDBObj->SetFormatAngka($listDataItem[$i]['nominal_sekarang']);
					$dataLaporan[$x]['font_style'] ='';
					$i++;
					   
				} elseif($unitId != $listDataItem[$i]['unit_id']){
					$unitId = $listDataItem[$i]['unit_id'];
					$dataLaporan[$x]['sumber_dana_sebelum'] ='';
					$dataLaporan[$x]['sumber_dana_sekarang'] ='';
					$dataLaporan[$x]['kode'] =$listDataItem[$i]['unit_kode'];
					$dataLaporan[$x]['nama'] = $listDataItem[$i]['unit_nama'];
					$dataLaporan[$x]['target_sebelum'] = $this->mDBObj->SetFormatAngka($getTotalPerU[$unitId]['sebelum']);
					$dataLaporan[$x]['target_sekarang'] = $this->mDBObj->SetFormatAngka($getTotalPerU[$unitId]['sekarang']);
					$dataLaporan[$x]['volume_sebelum'] ='';
					$dataLaporan[$x]['volume_sekarang'] ='';
					$dataLaporan[$x]['padding'] =0;
					$dataLaporan[$x]['class_name'] ='table-common-even1';
					$dataLaporan[$x]['font_style'] ='font-weight:bold';
				} elseif($programId != $listDataItem[$i]['program_id']){
					$programId = $listDataItem[$i]['program_id'];
					$dataLaporan[$x]['sumber_dana_sebelum'] ='';
					$dataLaporan[$x]['sumber_dana_sekarang'] ='';
					$dataLaporan[$x]['kode'] =$listDataItem[$i]['program_kode'];
					$dataLaporan[$x]['nama'] =$listDataItem[$i]['program_nama'];
					$dataLaporan[$x]['target_sebelum'] = $this->mDBObj->SetFormatAngka($getTotalPerP[$programId]['sebelum']);
					$dataLaporan[$x]['target_sekarang'] = $this->mDBObj->SetFormatAngka($getTotalPerP[$programId]['sekarang']);
					$dataLaporan[$x]['volume_sebelum'] ='';
					$dataLaporan[$x]['volume_sekarang'] ='';
					$dataLaporan[$x]['padding'] =0;
					$dataLaporan[$x]['class_name'] ='table-common-even1';
					$dataLaporan[$x]['font_style'] ='font-weight:bold';
				} elseif($kegiatanId != $listDataItem[$i]['kegiatan_id']){
					$kegiatanId  = $listDataItem[$i]['kegiatan_id'];
					$dataLaporan[$x]['sumber_dana_sebelum'] ='';
					$dataLaporan[$x]['sumber_dana_sekarang'] ='';
					$dataLaporan[$x]['kode'] = $listDataItem[$i]['kegiatan_kode'];
					$dataLaporan[$x]['nama'] = $listDataItem[$i]['kegiatan_nama'];
					$dataLaporan[$x]['target_sebelum'] = $this->mDBObj->SetFormatAngka($getTotalPerK[$kegiatanId]['sebelum']);
					$dataLaporan[$x]['target_sekarang'] = $this->mDBObj->SetFormatAngka($getTotalPerK[$kegiatanId]['sekarang']);
					$dataLaporan[$x]['volume_sebelum'] ='';
					$dataLaporan[$x]['volume_sekarang'] ='';
					$dataLaporan[$x]['class_name'] ='table-common-even1';
					$dataLaporan[$x]['font_style'] ='font-weight:bold;font-style:italic';
				} elseif($subKegiatanId != $listDataItem[$i]['sub_kegiatan_id']){
					$subKegiatanId  = $listDataItem[$i]['sub_kegiatan_id'];
					$dataLaporan[$x]['sumber_dana_sebelum'] ='';
					$dataLaporan[$x]['sumber_dana_sekarang'] ='';
					$dataLaporan[$x]['kode'] = $listDataItem[$i]['sub_kegiatan_kode'];
					$dataLaporan[$x]['nama'] = $listDataItem[$i]['sub_kegiatan_nama'];
					$dataLaporan[$x]['target_sebelum'] = $this->mDBObj->SetFormatAngka($getTotalPerSk[$subKegiatanId]['sebelum']);
					$dataLaporan[$x]['target_sekarang'] = $this->mDBObj->SetFormatAngka($getTotalPerSk[$subKegiatanId]['sekarang']);
					$dataLaporan[$x]['volume_sebelum'] ='';
					$dataLaporan[$x]['volume_sekarang'] ='';
					$dataLaporan[$x]['class_name'] ='table-common-even1';
					$dataLaporan[$x]['font_style'] ='font-style:italic';
				} elseif($makpId != $listDataItem[$i]['mak_parent_id']){
					$makpId  = $listDataItem[$i]['mak_parent_id'];
					$dataLaporan[$x]['sumber_dana_sebelum'] ='';
					$dataLaporan[$x]['sumber_dana_sekarang'] ='';
					$dataLaporan[$x]['kode'] =$listDataItem[$i]['mak_parent_kode'];
					$dataLaporan[$x]['nama'] =$listDataItem[$i]['mak_parent_nama'];
					$dataLaporan[$x]['target_sebelum'] = $this->mDBObj->SetFormatAngka($getTotalPerPagu[$makpId]['sebelum']);
					$dataLaporan[$x]['target_sekarang'] = $this->mDBObj->SetFormatAngka($getTotalPerPagu[$makpId]['sekarang']);
					$dataLaporan[$x]['volume'] ='';
					$dataLaporan[$x]['class_name'] ='table-common-even1';
					$dataLaporan[$x]['font_style'] ='text-decoration:underline" ';
				} elseif($makId != $listDataItem[$i]['mak_id']){
					$makId  = $listDataItem[$i]['mak_id'];
					$dataLaporan[$x]['sumber_dana_sebelum'] ='';
					$dataLaporan[$x]['sumber_dana_sekarang'] ='';
					$dataLaporan[$x]['kode'] = '';
					$dataLaporan[$x]['nama'] = $listDataItem[$i]['mak_kode'].' - '.$listDataItem[$i]['mak_nama'];
					$dataLaporan[$x]['target_sebelum'] = $this->mDBObj->SetFormatAngka($getTotalPerMak[$makId]['sebelum']);
					$dataLaporan[$x]['target_sekarang'] = $this->mDBObj->SetFormatAngka($getTotalPerMak[$makId]['sekarang']);
					$dataLaporan[$x]['volume_sebelum'] ='';
					$dataLaporan[$x]['volume_sekarang'] ='';
					$dataLaporan[$x]['class_name'] ='table-common-even1';
					$dataLaporan[$x]['font_style'] ='font-style:italic;text-decoration:underline" ';
				}
				
				$x++;
			}
		
		
			//for($k = 0;$k < sizeof($dataLaporan);$k++){
			foreach($dataLaporan as $key => $value)	{
				
				//$this->mrTemplate->AddVars('list_data_item', $dataLaporan[$k], '');
				$this->mrTemplate->AddVars('list_data_item', $dataLaporan[$key], '');
				$this->mrTemplate->parseTemplate('list_data_item', 'a');
			}
			
			
			$total = $data['get_total_all'];
			$this->mrTemplate->AddVar('list_data', 'TOTAL_NOMINAL_SEBELUM', $this->mDBObj->SetFormatAngka($total['nominal_sebelum']));
			$this->mrTemplate->AddVar('list_data', 'TOTAL_NOMINAL_SEKARANG', $this->mDBObj->SetFormatAngka($total['nominal_sekarang']));
		}
 		$listDataItemSD = $data['get_sd'];
		if(empty($listDataItemSD)){
			$this->mrTemplate->AddVar('list_data_sd', 'IS_EMPTY', 'YES');		
	    } else {
			$this->mrTemplate->AddVar('list_data_sd', 'IS_EMPTY', 'NO');
			
			foreach($listDataItemSD as $key => $value)	{
				$listDataItemSD[$key]['nominal_a'] = $this->mDBObj->SetFormatAngka($listDataItemSD[$key]['nominal_sebelum']);
				$listDataItemSD[$key]['nominal_b'] = $this->mDBObj->SetFormatAngka($listDataItemSD[$key]['nominal_sekarang']);
				$this->mrTemplate->AddVars('list_data_sd_item', $listDataItemSD[$key], '');
				$this->mrTemplate->parseTemplate('list_data_sd_item', 'a');
			}
			
		}		       
        
   }
   
}
