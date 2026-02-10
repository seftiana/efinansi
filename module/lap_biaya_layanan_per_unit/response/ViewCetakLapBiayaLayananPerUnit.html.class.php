<?php

/**
 * 
 * class ViewCetakLapBiayaLayananPerUnit
 * @package lap_biaya_layanan_per_unit
 * @todo Untuk menampilkan data
 * @subpackage response
 * @since 12 September 2012
 * @SystemAnalyst Nanang Ruswianto <nanang@gamatechno.com>
 * @author noor hadi <noor.hadi@gamatechno.com>
 * @copyright 2012 Gamatechno Indonesia
 * 
 */
 
require_once GTFWConfiguration::GetValue('application', 'docroot') . 
            'module/lap_biaya_layanan_per_unit/business/LapBiayaLayananPerUnit.class.php';


class ViewCetakLapBiayaLayananPerUnit extends HtmlResponse
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
		$this->mDBObj = new LapBiayaLayananPerUnit();
		$this->mModulName = 'lap_biaya_layanan_per_unit';
	}
	
	public function TemplateModule()
	{
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') .     
                'module/'.$this->mModulName.'/template');
		$this->SetTemplateFile('view_cetak_lap_biaya_layanan_per_unit.html');
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
			$bl ='';
			$makId='';			
			$no =0;
			for($i = 0 ; $i < sizeof($listDataItem); )
			{
				if( ($listDataItem[$i]['unit_kerja_id'] == $unitId) && 
					($listDataItem[$i]['biaya_id'] == $bl) && 
					($listDataItem[$i]['mak_id'] == $makId) 
					){
						$send[$i]['nomor']='';
						$send[$i]['uraian']=' - '.$listDataItem[$i]['komp_nama'] ;
						$send[$i]['volume']=$listDataItem[$i]['komp_volume'] ;
						$send[$i]['jumlah']= $this->mDBObj->SetFormatAngka($listDataItem[$i]['komp_jumlah']) ;
						$send[$i]['font_style'] = '';
						$this->mrTemplate->AddVars('list_data_item', $send[$i], '');
						$this->mrTemplate->parseTemplate('list_data_item', 'a');
					$i++;	
				}elseif($listDataItem[$i]['unit_kerja_id'] != $unitId){
						$unitId = $listDataItem[$i]['unit_kerja_id'] ;
						$no++;
						$send[$i]['nomor']=$no;
						$send[$i]['uraian']=$listDataItem[$i]['unit_kerja_kode'].' - '.
											$listDataItem[$i]['unit_kerja_nama'] ;;
						$send[$i]['volume']='';
						$send[$i]['jumlah']= $this->mDBObj->SetFormatAngka($this->mDBObj->GetTotalPerUnit(
														$listDataItem[$i]['tahun_anggaran_id'],
														$listDataItem[$i]['unit_kerja_id'] 
														));
						$send[$i]['font_style'] = 'font-weight: bold;';
						$this->mrTemplate->AddVars('list_data_item', $send[$i], '');
						$this->mrTemplate->parseTemplate('list_data_item', 'a');
				//}elseif($listDataItem[$i]['biaya_langsung'] != $bl){
				}elseif($listDataItem[$i]['biaya_id'] != $bl){
						$bl = $listDataItem[$i]['biaya_id'] ;
						$send[$i]['nomor']='';
						$send[$i]['uraian']= ( $listDataItem[$i]['biaya_langsung'] == 0) ? 
										'Biaya Tak Langsung' : 'Biaya Langsung';
						$send[$i]['volume']='';
						$send[$i]['jumlah']= $this->mDBObj->SetFormatAngka($this->mDBObj->GetTotalPerBiaya(
														$listDataItem[$i]['tahun_anggaran_id'],
														$listDataItem[$i]['biaya_langsung'] ,
														$listDataItem[$i]['unit_kerja_id'] 
														));
						$send[$i]['font_style'] = 'font-style:italic; font-weight: bold;';
						$this->mrTemplate->AddVars('list_data_item', $send[$i], '');
						$this->mrTemplate->parseTemplate('list_data_item', 'a');
				}elseif($listDataItem[$i]['mak_id'] != $makId){
						$send[$i]['nomor']='';
						$makId = $listDataItem[$i]['mak_id'] ;
						$send[$i]['uraian']=$listDataItem[$i]['mak_kode'].' - '.$listDataItem[$i]['mak_nama']  ;
						$send[$i]['volume']='';
						$send[$i]['jumlah']= $this->mDBObj->SetFormatAngka($this->mDBObj->GetTotalPerMak(
														$listDataItem[$i]['tahun_anggaran_id'],
														$listDataItem[$i]['mak_id'] ,
														$listDataItem[$i]['unit_kerja_id'] 
														));
						$send[$i]['font_style'] = 'font-style:italic; ';
						$this->mrTemplate->AddVars('list_data_item', $send[$i], '');
						$this->mrTemplate->parseTemplate('list_data_item', 'a');
				}
				
			}
//			$total = $this->mDBObj->SetFormatAngka($total);
//			$this->mrTemplate->AddVar('list_data', 'TOTAL_NOMINAL', $total);
		}
        
   }
   
}
