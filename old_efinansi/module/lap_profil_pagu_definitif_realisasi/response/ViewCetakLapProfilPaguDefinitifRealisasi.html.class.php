<?php

/**
 * 
 * class ViewCetakLapProfilPaguDefinitifRealisasi
 * @package lap_profil_pagu_definitif_realisasi
 * @todo Untuk cetak
 * @subpackage response
 * @since june 24 2013
 * @analyst Nanang Ruswianto <nanang@gamatechno.com>
 * @author noor hadi <noor.hadi@gamatechno.com>
 * @copyright 2013 Gamatechno Indonesia
 * 
 */

require_once GTFWConfiguration::GetValue('application', 'docroot') . 
            'module/lap_profil_pagu_definitif_realisasi/business/LapProfilPaguDefinitifRealisasi.class.php';


class ViewCetakLapProfilPaguDefinitifRealisasi extends HtmlResponse
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
		$this->mDBObj = new LapProfilPaguDefinitifRealisasi();
		$this->mModulName = 'lap_profil_pagu_definitif_realisasi';
	}
	
	public function TemplateModule()
	{
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') .     
                'module/'.$this->mModulName.'/template');
		$this->SetTemplateFile('view_cetak_lap_profil_pagu_definitif_realisasi.html');
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
		
		
		$return['get_data'] = $this->mDBObj->GetData($tahun_anggaran,$unitkerja);
		$return['unitkerja_nama'] = $unitkerja_label;
		$return['ta'] = $this->mDBObj->GetTahunAnggaran($tahun_anggaran);
		$return['unit_kerja_id'] =$unitkerja;
		$return['ta_id'] =$tahun_anggaran;
		return $return;
   }
   
   public function ParseTemplate($data = NULL)
   {
	   		
		$this->mrTemplate->AddVar('content', 'TAHUN_PERIODE', ($data['ta']['name']));
		$this->mrTemplate->AddVar('content', 'UNIT_KERJA_LABEL', ($data['unitkerja_nama']));
		$this->mrTemplate->AddVar('content', 'TAHUN_PERIODE', ($data['ta']['name']));
		
        $listDataItem= $data['get_data'];
       // print_r($listDataItem);
		if(empty($listDataItem)){
			$this->mrTemplate->AddVar('list_data', 'IS_EMPTY', 'YES');		
	    } else {
			$this->mrTemplate->AddVar('list_data', 'IS_EMPTY', 'NO');
			
			$unitId ='';
			$kegiatanId='';
			$outputId ='';
			$komponenId ='';
			$no =0;
			for($i = 0 ; $i < sizeof($listDataItem); )
			{
				if( ($listDataItem[$i]['unit_kerja_id'] == $unitId) && 
					($listDataItem[$i]['kegiatan_id'] == $kegiatanId) && 
					($listDataItem[$i]['output_id'] == $outputId) 
					){
						$send[$i]['kode']= $listDataItem[$i]['komponen_kode'];
						$send[$i]['uraian']= $listDataItem[$i]['komponen_nama'];
						$send[$i]['total_pagu_def']=$this->mDBObj->SetFormatAngka($listDataItem[$i]['total_pagu_def']);
						$send[$i]['total_realisasi']= $this->mDBObj->SetFormatAngka($listDataItem[$i]['total_realisasi']) ;
						$send[$i]['font_style'] = '';
						$this->mrTemplate->AddVars('list_data_item', $send[$i], '');
						$this->mrTemplate->parseTemplate('list_data_item', 'a');
					$i++;	
				}elseif($listDataItem[$i]['unit_kerja_id'] != $unitId){
						$unitId = $listDataItem[$i]['unit_kerja_id'] ;
						$no++;
						$send[$i]['kode']=$listDataItem[$i]['unit_kerja_kode'];
						$send[$i]['uraian'] = $listDataItem[$i]['unit_kerja_nama'];
						$send[$i]['total_pagu_def'] = $this->mDBObj->SetFormatAngka($this->mDBObj->GetTotalPerUnit(
														$listDataItem[$i]['tahun_anggaran_id'],
														$listDataItem[$i]['unit_kerja_id'] 
														));
						$send[$i]['total_realisasi']= $this->mDBObj->SetFormatAngka(0);
						$send[$i]['font_style'] = 'font-weight: bold;';
						$this->mrTemplate->AddVars('list_data_item', $send[$i], '');
						$this->mrTemplate->parseTemplate('list_data_item', 'a');
				}elseif($listDataItem[$i]['kegiatan_id'] != $kegiatanId){
						$kegiatanId = $listDataItem[$i]['kegiatan_id'];
						$send[$i]['kode']=  $listDataItem[$i]['kegiatan_kode'];
						$send[$i]['uraian']= $listDataItem[$i]['kegiatan_nama'];
						$send[$i]['total_pagu_def'] = $this->mDBObj->SetFormatAngka($this->mDBObj->GetTotalPerKegiatan(
														$listDataItem[$i]['tahun_anggaran_id'],
														$listDataItem[$i]['kegiatan_id'] ,
														$listDataItem[$i]['unit_kerja_id'] 
														));
						$send[$i]['total_realisasi']= $this->mDBObj->SetFormatAngka(0);
						$send[$i]['font_style'] = 'font-style:italic; font-weight: bold;';
						$this->mrTemplate->AddVars('list_data_item', $send[$i], '');
						$this->mrTemplate->parseTemplate('list_data_item', 'a');
				}elseif($listDataItem[$i]['output_id'] != $outputId){
						$outputId = $listDataItem[$i]['output_id'];
						
						$send[$i]['kode'] =  $listDataItem[$i]['output_kode'];
						$send[$i]['uraian'] = $listDataItem[$i]['output_nama'];
						$send[$i]['total_pagu_def'] = $this->mDBObj->SetFormatAngka($this->mDBObj->GetTotalPerOutput(
														$listDataItem[$i]['tahun_anggaran_id'],
														$listDataItem[$i]['output_id'] ,
														$listDataItem[$i]['unit_kerja_id'] 
														));
						$send[$i]['total_realisasi']= $this->mDBObj->SetFormatAngka(0);
						$send[$i]['font_style'] = 'font-style:italic;';
						$this->mrTemplate->AddVars('list_data_item', $send[$i], '');
						$this->mrTemplate->parseTemplate('list_data_item', 'a');
				}
				
			}
			$total = $this->mDBObj->SetFormatAngka($this->mDBObj->GetTotalAll(
																    $data['ta_id'],
																	$data['unit_kerja_id'] ));
			$this->mrTemplate->AddVar('list_data', 'TOTAL_NOMINAL_PAGU_DEF', $total);
			$this->mrTemplate->AddVar('list_data', 'TOTAL_NOMINAL_REALISASI', 0);
		}
        
   }
   
}

?>