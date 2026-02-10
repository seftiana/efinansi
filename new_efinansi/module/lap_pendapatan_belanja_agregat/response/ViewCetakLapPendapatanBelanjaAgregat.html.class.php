<?php

/**
 * 
 * class ViewCetakLapPendapatanBelanjaAgregat
 * @package lap_pendapatan_belanja_agregat
 * @todo Untuk menampilkan data
 * @subpackage response
 * @since 10 September 2012
 * @SystemAnalyst Nanang Ruswianto <nanang@gamatechno.com>
 * @author noor hadi <noor.hadi@gamatechno.com>
 * @copyright 2012 Gamatechno Indonesia
 * 
 */
 
require_once GTFWConfiguration::GetValue('application', 'docroot') . 
            'module/lap_pendapatan_belanja_agregat/business/LapPendapatanBelanjaAgregat.class.php';
            

class ViewCetakLapPendapatanBelanjaAgregat extends HtmlResponse
{
	
	protected $mLPA;
	protected $jmlNominal;
	
	public function __construct()
	{
		parent::__construct();
		$this->mLPA = new LapPendapatanBelanjaAgregat();
	}
	
	public function TemplateModule()
	{
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') .     
                'module/lap_pendapatan_belanja_agregat/template');
		$this->SetTemplateFile('view_cetak_lap_pendapatan_belanja_agregat.html');
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
		$return['ta'] = $this->mLPA->GetTahunAnggaran($tahun_anggaran);
		
		//view	
				
		$return['laporan_p'] = $this->mLPA->GetDataPendapatan($tahun_anggaran,$unitkerja);
		$return['laporan_b'] = $this->mLPA->GetDataBelanja($tahun_anggaran,$unitkerja);
		$return['total_laporan_b_per_sd'] = $this->mLPA->GetTotalLaporanBPerSd($tahun_anggaran,$unitkerja);
		$return['total_laporan_p_per_map'] = $this->mLPA->GetTotalLaporanPPerMap($tahun_anggaran,$unitkerja);
				
		//$return['laporan_all'] = $this->mLPA->GetData();
		$return['ta'] = $this->mLPA->GetTahunAnggaran($tahun_anggaran);
		$return['ta_kemarin'] = $this->mLPA->GetTahunAnggaranKemarin($tahun_anggaran);		
			
		return $return;
   }
   
   public function ParseTemplate($data = NULL)
   {
   		
		$this->mrTemplate->AddVar('content', 'TAHUN_PERIODE', ($data['ta']['name']));
		$this->mrTemplate->AddVar('content', 'UNIT_KERJA_LABEL', ($data['unit_kerja_nama']));
		$taSebelum = !empty($data['ta_kemarin']['name']) ? $data['ta_kemarin']['name'] :$data['ta']['name'];
	    $this->mrTemplate->AddVar('content', 'TAHUN_SEBELUM_TH_ANGGAR', $taSebelum);
	    $this->mrTemplate->AddVar('content', 'TAHUN_SEKARANG_TH_ANGGAR', ($data['ta']['name']));
			$listDataItem = $data['laporan_p'];		
			$listDataItem_b = $data['laporan_b'];		
										
		if(empty($listDataItem) && empty($listDataItem_b)){
			$this->mrTemplate->AddVar('list_data', 'IS_EMPTY', 'YES');		
	    } else {
			$this->mrTemplate->AddVar('list_data', 'IS_EMPTY', 'NO');
			//$nomor = 0;
			//$listDataItem = $data['laporan_p'];
			
			/**
			 * untuk laporan pendapatan
			 */
			$totalPSekarang = 0; 
			$totalPSebelum = 0; 
			$map_id = NULL;
			$nomor =0;
			$x=0;
			$getTotalPerMAP = $data['total_laporan_p_per_map'] ;
			
			for($i = 0 ; $i < sizeof($listDataItem); )
			{
				
				if($map_id == $listDataItem[$i]['map_id']){
					////$totalRP += $listDataItem[$i]['realisasi'];
					//$totalPP += $listDataItem[$i]['realisasi'];
					$lap[$x]['uraian'] ='[ '.$listDataItem[$i]['kode_penerimaan_kode'].' ]'. ' - '.$listDataItem[$i]['kode_penerimaan_nama'];
					$lap[$x]['realisasi'] =$this->mLPA->SetFormatAngka($listDataItem[$i]['realisasi_sebelum']);
					$lap[$x]['proyeksi'] = $this->mLPA->SetFormatAngka($listDataItem[$i]['nominal_sekarang']);
					$lap[$x]['font_style'] ='font-style:normal;font-weight:normal';
					$this->mrTemplate->AddVars('list_data_item', $lap[$x], '');
					$this->mrTemplate->parseTemplate('list_data_item', 'a');
					$i++;
				}elseif($map_id != $listDataItem[$i]['map_id']){
					$nomor++;
					$map_id = $listDataItem[$i]['map_id'];
					$totalPSebelum += $getTotalPerMAP[$map_id]['realisasi_sebelum'];
					$totalPSekarang += $getTotalPerMAP[$map_id]['nominal_sekarang'];
					//$totalRP += $listDataItem[$i]['realisasi'];
					//$totalPP += $listDataItem[$i]['realisasi'];
					$lap[$x]['uraian'] =$nomor .'. '.$listDataItem[$i]['map_nama'];
					$lap[$x]['realisasi'] = $this->mLPA->SetFormatAngka($getTotalPerMAP[$listDataItem[$i]['map_id']]['realisasi_sebelum']);
					$lap[$x]['proyeksi'] =$this->mLPA->SetFormatAngka($getTotalPerMAP[$listDataItem[$i]['map_id']]['nominal_sekarang']);
					$lap[$x]['font_style'] ='font-style:italic;font-weight:bold';
					$this->mrTemplate->AddVars('list_data_item', $lap[$x], '');
					$this->mrTemplate->parseTemplate('list_data_item', 'a');
					
				}	
				$x++;
				
			}

			/**
			 * untuk laporan belanja
			 */			
			$sdId ='';
			$getTotalPerSD = $data['total_laporan_b_per_sd'];
			$totalRB = 0;
			$totalPB = 0;
			$nomor =1;
			$totalBSekarang = 0; 
			$totalBSebelum = 0; 
			for($j = 0 ; $j < sizeof($listDataItem_b); )
			{
			
			     if($listDataItem_b[$j]['mak_parent_id'] == $sdId){					 
					$send[$j]['uraian'] = $nomor.'. '.$listDataItem_b[$j]['mak_nama'];
					$send[$j]['realisasi'] = $this->mLPA->SetFormatAngka($listDataItem_b[$j]['realisasi_sebelum']);
					$send[$j]['proyeksi'] = $this->mLPA->SetFormatAngka($listDataItem_b[$j]['nominal_sekarang']);
					$send[$j]['font_style'] ='';
					$this->mrTemplate->AddVars('list_data_item_b', $send[$j], '');
					$this->mrTemplate->parseTemplate('list_data_item_b', 'a');
					$j++;
					$nomor++;
				} /*elseif($listDataItem_b[$j]['sumber_dana_id'] != $sdId){
					$sdId = $listDataItem_b[$j]['sumber_dana_id'];
					$send[$j]['uraian'] = $listDataItem_b[$j]['sumber_dana_nama'];
					$send[$j]['realisasi'] =$this->mLPA->SetFormatAngka($getTotalPerSD[$sdId]);
					$send[$j]['proyeksi'] ='';
					$send[$j]['font_style'] ='font-style:italic;font-weight:bold';
					$this->mrTemplate->AddVars('list_data_item_b', $send[$j], '');
					$this->mrTemplate->parseTemplate('list_data_item_b', 'a');
					*/
				 elseif($listDataItem_b[$j]['mak_parent_id'] != $sdId){
					$sdId = $listDataItem_b[$j]['mak_parent_id'];
					$nomor =1;
					$totalBSebelum += $getTotalPerSD[$sdId]['realisasi_sebelum'];
					$totalBSekarang += $getTotalPerSD[$sdId]['nominal_sekarang'];
					$send[$j]['uraian'] = $listDataItem_b[$j]['mak_parent_nama'];
					$send[$j]['realisasi'] =$this->mLPA->SetFormatAngka($getTotalPerSD[$sdId]['realisasi_sebelum']);
					$send[$j]['proyeksi'] =$this->mLPA->SetFormatAngka($getTotalPerSD[$sdId]['nominal_sekarang']);
					$send[$j]['font_style'] ='font-style:italic;font-weight:bold';
					$this->mrTemplate->AddVars('list_data_item_b', $send[$j], '');
					$this->mrTemplate->parseTemplate('list_data_item_b', 'a');
				}	
				
			}
			
			$this->mrTemplate->AddVar('list_data', 'TOTAL_REALISASI', $this->mLPA->SetFormatAngka($totalPSebelum));
			$this->mrTemplate->AddVar('list_data', 'TOTAL_PROYEKSI', $this->mLPA->SetFormatAngka($totalPSekarang));
		    $this->mrTemplate->AddVar('list_data', 'TOTAL_REALISASI_B', $this->mLPA->SetFormatAngka($totalBSebelum));
			$this->mrTemplate->AddVar('list_data', 'TOTAL_PROYEKSI_B', $this->mLPA->SetFormatAngka($totalBSekarang));	
			
		}		
   }
   
}
