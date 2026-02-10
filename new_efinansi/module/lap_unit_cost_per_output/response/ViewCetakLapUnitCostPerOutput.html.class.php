<?php

/**
 * 
 * class ViewCetakLapUnitCostPerOutput
 * @package lap_unit_cost_per_unit
 * @todo Untuk cetak
 * @subpackage response
 * @since june 24 2013
 * @analyst Nanang Ruswianto <nanang@gamatechno.com>
 * @author noor hadi <noor.hadi@gamatechno.com>
 * @copyright 2013 Gamatechno Indonesia
 * 
 */

require_once GTFWConfiguration::GetValue('application', 'docroot') . 
            'module/lap_unit_cost_per_output/business/LapUnitCostPerOutput.class.php';


class ViewCetakLapUnitCostPerOutput extends HtmlResponse
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
		$this->mDBObj = new LapUnitCostPerOutput();
		$this->mModulName = 'lap_unit_cost_per_output';
	}
	
	public function TemplateModule()
	{
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') .     
                'module/'.$this->mModulName.'/template');
		$this->SetTemplateFile('view_cetak_lap_unit_cost_per_output.html');
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
			$outputId ='';
			$langsungId ='';
			$no =0;
			for($i = 0 ; $i < sizeof($listDataItem); )
			{
				if( ($listDataItem[$i]['unit_kerja_id'] == $unitId) && 
					($listDataItem[$i]['output_id'] == $outputId) && 
					($listDataItem[$i]['langsung_id'] == $langsungId) 
					){
						$send[$i]['nomor']='';
						$send[$i]['kode']= $listDataItem[$i]['mak_kode'];
						$send[$i]['uraian']= $listDataItem[$i]['mak_nama'];
						$send[$i]['volume']=$this->mDBObj->SetFormatAngka($listDataItem[$i]['volume']);
						$send[$i]['jumlah']= $this->mDBObj->SetFormatAngka($listDataItem[$i]['nominal']) ;
						$send[$i]['font_style'] = '';
						$this->mrTemplate->AddVars('list_data_item', $send[$i], '');
						$this->mrTemplate->parseTemplate('list_data_item', 'a');
					$i++;	
				}elseif($listDataItem[$i]['unit_kerja_id'] != $unitId){
						$unitId = $listDataItem[$i]['unit_kerja_id'] ;
						$no++;
						$send[$i]['nomor']=$no;
						$send[$i]['kode']=$listDataItem[$i]['unit_kerja_kode'];
						$send[$i]['uraian']=$listDataItem[$i]['unit_kerja_nama'] ;
						$send[$i]['volume']='';
						$send[$i]['jumlah']= $this->mDBObj->SetFormatAngka($this->mDBObj->GetTotalPerUnit(
														$listDataItem[$i]['tahun_anggaran_id'],
														$listDataItem[$i]['unit_kerja_id'] 
														));
						$send[$i]['font_style'] = 'font-weight: bold;';
						$this->mrTemplate->AddVars('list_data_item', $send[$i], '');
						$this->mrTemplate->parseTemplate('list_data_item', 'a');
				}elseif($listDataItem[$i]['output_id'] != $outputId){
						$outputId = $listDataItem[$i]['output_id'];
						$send[$i]['nomor']='';
						$send[$i]['kode']=$listDataItem[$i]['output_kode'];
						$send[$i]['uraian']= $listDataItem[$i]['output_nama'];
						$send[$i]['volume']='';
						$send[$i]['jumlah']= $this->mDBObj->SetFormatAngka($this->mDBObj->GetTotalPerOutput(
														$listDataItem[$i]['tahun_anggaran_id'],
														$listDataItem[$i]['output_id'] ,
														$listDataItem[$i]['unit_kerja_id'] 
														));
						$send[$i]['font_style'] = 'font-style:italic; font-weight: bold;';
						$this->mrTemplate->AddVars('list_data_item', $send[$i], '');
						$this->mrTemplate->parseTemplate('list_data_item', 'a');
				}elseif($listDataItem[$i]['langsung_id'] != $langsungId){
						$send[$i]['nomor']='';
						$send[$i]['kode']='';
						$langsungId = $listDataItem[$i]['langsung_id'];
						$send[$i]['uraian']= ($listDataItem[$i]['langsung_kode'] ==0) ? 'Biaya Tak Langsung' : 'Biaya Langsung';
						$send[$i]['volume']='';
						$send[$i]['jumlah']=$this->mDBObj->SetFormatAngka($this->mDBObj->GetTotalPerBiaya(
														$listDataItem[$i]['tahun_anggaran_id'],
														$listDataItem[$i]['langsung_id'] ,
														$listDataItem[$i]['unit_kerja_id'] 
														));
						$send[$i]['font_style'] = 'text-decoration:underline; ';
						$this->mrTemplate->AddVars('list_data_item', $send[$i], '');
						$this->mrTemplate->parseTemplate('list_data_item', 'a');
				}
				
			}
			$total = $this->mDBObj->SetFormatAngka($this->mDBObj->GetTotalAll(
																    $data['ta_id'],
																	$data['unit_kerja_id'] ));
			$this->mrTemplate->AddVar('list_data', 'TOTAL_NOMINAL', $total);
		}
        
   }
   
}
