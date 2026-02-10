<?php

/**
 * 
 * class ViewCetakLapPengadaanRup
 * @package lap_pengadaan_rup
 * @todo Untuk menampilkan data
 * @subpackage response
 * @since 29 April 2013
 * @SystemAnalyst Nanang Ruswianto <nanang@gamatechno.com>
 * @author noor hadi <noor.hadi@gamatechno.com>
 * @copyright 2013 Gamatechno Indonesia
 * 
 * 
 */
 
require_once GTFWConfiguration::GetValue('application', 'docroot') . 
     'module/lap_pengadaan_rup/business/LapPengadaanRup.class.php';

class ViewCetakLapPengadaanRup extends HtmlResponse
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
		$this->mDBObj = new LapPengadaanRup();
		$this->mModulName = 'lap_pengadaan_rup';
	}
	
	public function TemplateModule()
	{
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') .     
                'module/'.$this->mModulName.'/template');
		$this->SetTemplateFile('view_cetak_lap_pengadaan_rup.html');
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
		
		$tahun_anggaran = Dispatcher::Instance()->Decrypt($_GET['tgl']);
		$unitkerja_label = Dispatcher::Instance()->Decrypt($_GET['unitkerja_label']);
		$unitkerja = Dispatcher::Instance()->Decrypt($_GET['unitkerja']);
		$userId = Dispatcher::Instance()->Decrypt($_GET['id']);
		$return['unit_kerja_id'] = $unitkerja;
		$return['unit_kerja_nama'] = $unitkerja_label;
			
		

		$return['get_data'] = $this->mDBObj->GetDataLaporan($tahun_anggaran,$unitkerja);
		$return['get_total_per_mak'] =$this->mDBObj->GetTotalPerMak($tahun_anggaran,$unitkerja);
		$return['get_total_per_pagu'] =$this->mDBObj->GetTotalPerPagu($tahun_anggaran,$unitkerja);
		$return['get_total_per_sk'] =$this->mDBObj->GetTotalPerSk($tahun_anggaran,$unitkerja);
		$return['get_total_per_k'] =$this->mDBObj->GetTotalPerk($tahun_anggaran,$unitkerja);
		$return['get_total_per_p'] =$this->mDBObj->GetTotalPerP($tahun_anggaran,$unitkerja);
		$return['get_total_per_u'] =$this->mDBObj->GetTotalPerU($tahun_anggaran,$unitkerja);
		$return['get_total_all'] =$this->mDBObj->GetTotalAll($tahun_anggaran,$unitkerja);
		$return['get_sd'] =$this->mDBObj->GetSumberDana($tahun_anggaran,$unitkerja);		

		$return['userId']  = $userId;
		$return['ta'] = $this->mDBObj->GetTahunAnggaran($tahun_anggaran);
		$return['ta_kemarin'] = $this->mDBObj->GetTahunAnggaranKemarin($tahun_anggaran);
						
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

						
			$kegiatanId ='';
			$subKegiatanId ='';
			
			$x = 0;
			$no=0;
			
			$getTotalPerSk = $data['get_total_per_sk'];
			$getTotalPerK = $data['get_total_per_k'];

			for($i = 0 ; $i < sizeof($listDataItem); )
			{
				if(($kegiatanId == $listDataItem[$i]['output_id']) && 
					($subKegiatanId == $listDataItem[$i]['komponen_id'])
					){						
					
					$dataLaporan[$x]['kode'] = $listDataItem[$i]['mak_kode'];
					$dataLaporan[$x]['nama'] = $listDataItem[$i]['mak_nama'];
					$dataLaporan[$x]['volume'] = $this->mDBObj->SetFormatAngka($listDataItem[$i]['db_volume']);
					$dataLaporan[$x]['nominal_total'] = $this->mDBObj->SetFormatAngka($listDataItem[$i]['db_nominal_total']);
					if(!empty($listDataItem[$i]['sumber_dana_nama'])){
							$sumberDana = str_replace(',','<br />',$listDataItem[$i]['sumber_dana_nama']);
					}
					$dataLaporan[$x]['sumber_dana_nama'] = $sumberDana;
					$dataLaporan[$x]['font_style'] ='';
					$i++;
					   
				} elseif($kegiatanId != $listDataItem[$i]['output_id']){
					$kegiatanId = $listDataItem[$i]['output_id'];
					$dataLaporan[$x]['kode'] = $listDataItem[$i]['output_kode'];
					$dataLaporan[$x]['nama'] = $listDataItem[$i]['output_nama'];
					$dataLaporan[$x]['volume'] = NULL;
					$dataLaporan[$x]['nominal_total'] = $this->mDBObj->SetFormatAngka($getTotalPerK[$kegiatanId]);
					$dataLaporan[$x]['sumber_dana_nama'] = NULL;
					$dataLaporan[$x]['class_name'] ='table-common-even1';
					$dataLaporan[$x]['font_style'] ='font-weight:bold';
				} elseif($subKegiatanId != $listDataItem[$i]['komponen_id']){
					$subKegiatanId = $listDataItem[$i]['komponen_id'];
					$dataLaporan[$x]['kode'] = $listDataItem[$i]['komponen_kode'];
					$dataLaporan[$x]['nama'] = $listDataItem[$i]['komponen_nama'];
					$dataLaporan[$x]['volume'] = NULL;
					$dataLaporan[$x]['nominal_total'] = $this->mDBObj->SetFormatAngka($getTotalPerSk[$subKegiatanId]);
					$dataLaporan[$x]['sumber_dana_nama'] = NULL;
					$dataLaporan[$x]['class_name'] ='table-common-even1';
					$dataLaporan[$x]['font_style'] ='font-weight:bold; font-style:italic;';
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
			$this->mrTemplate->AddVar('list_data', 'TOTAL_NOMINAL', $this->mDBObj->SetFormatAngka($total));
		}
        
   }
   
}

?>