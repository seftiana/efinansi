<?php

/**
 * 
 * class ViewExcelLapPendapatanBelanjaAgregat
 * @package lap_pandapatan_belanja_agregat
 * @subpackage response
 * @since 12 September 2012
 * @SystemAnalyst Nanang Ruswianto <nanang@gamatechno.com>
 * @author noor hadi <noor.hadi@gamatechno.com>
 * @copyright 2012 Gamatechno Indonesia
 * 
 */

require_once GTFWConfiguration::GetValue('application', 'docroot') . 
            'module/lap_pendapatan_belanja_agregat/business/LapPendapatanBelanjaAgregat.class.php'; 
          //'module/lap_pandapatan_belanja_agregat/business/LapPendapatanBelanjaAgregat.class.php';

require_once GTFWConfiguration::GetValue('application', 'docroot') . 
	'module/user_unit_kerja/business/UserUnitKerja.class.php';     

class ViewExcelLapPendapatanBelanjaAgregat extends XlsResponse 
{

    public $mWorksheets = array('Data');
 
 	/**
	 * untuk menginstanskan class database object
	 */
	protected $mDBObj; 
	protected $mModulName;
	protected $mData;
	
	public function __construct()
	{
		parent::__construct();
		$this->mDBObj = new LapPendapatanBelanjaAgregat();
		$this->mModulName = 'lap_pandapatan_belanja_agregat';
	}
	  
    function GetFileName() 
    {
      // name it whatever you want
      $label = 'Lap_Pandapatan_Belanja_Agregat';
      //str_replace(' ','_',$this->L('lap_rinci_pengeluaran'));    
      // name it whatever you want
      return $label.'.xls'; //'LapRincianPengeluaran.xls';
    }
    
    function L($indexLangName = '')
    {
   		$lang = GTFWConfiguration::GetValue('language',$indexLangName);
   		if(!empty($lang)){
   			return $lang;	
   		}
   		return '';
    }
    
    function ProcessRequest() 
    {
        if(isset($_GET)) { //pasti dari form pencarian :p	  
	     if(is_object($_GET))
		    $v = $_GET->AsArray();
		 else
		    $v = $_GET;        
        } 
        
		$tahun_anggaran = Dispatcher::Instance()->Decrypt($v['tgl']);
		$unitkerja_label = Dispatcher::Instance()->Decrypt($v['unitkerja_label']);
		$unitkerja = Dispatcher::Instance()->Decrypt($v['unitkerja']);
		$userId = Dispatcher::Instance()->Decrypt($v['id']);
		
		$ta = $this->mDBObj->GetTahunAnggaran($tahun_anggaran);
		$ta_kemarin = $this->mDBObj->GetTahunAnggaranKemarin($tahun_anggaran);
		
				
		$laporanP = $this->mDBObj->GetDataPendapatan($tahun_anggaran,$unitkerja);
		$laporanB = $this->mDBObj->GetDataBelanja($tahun_anggaran,$unitkerja);
		$getTotalPerSD = $this->mDBObj->GetTotalLaporanBPerSd($tahun_anggaran,$unitkerja);	
		$getTotalPerMAP = $this->mDBObj->GetTotalLaporanPPerMap($tahun_anggaran,$unitkerja);
		
		
		if (empty($laporanP) && empty($laporanB)) {
			$this->mWorksheets['Data']->write(0, 0, $this->L('data_kosong'));
		} else {
            $fTitle = $this->mrWorkbook->add_format();
	        $fTitle->set_bold();
            $fTitle->set_size(12);
            $fTitle->set_align('vcenter');

		    $formatHeader = $this->mrWorkbook->add_format();
            $formatHeader->set_border(1);
            $formatHeader->set_bold();
            $formatHeader->set_size(10);
            $formatHeader->set_align('center');
            $formatHeader->set_align('vcenter');
            $formatHeader->set_text_wrap();
    
            $formatProgram = $this->mrWorkbook->add_format();
            $formatProgram->set_border(1);
            $formatProgram->set_bold();
            $formatProgram->set_size(10);
            $formatProgram->set_align('left');
            $formatProgram->set_align('vcenter');
            $formatProgram->set_text_wrap();
            
            $formatRProgram = $this->mrWorkbook->add_format();
            $formatRProgram->set_border(1);
            $formatRProgram->set_bold();
            $formatRProgram->set_size(10);
            $formatRProgram->set_align('right');
            $formatRProgram->set_align('vcenter');
            $formatRProgram->set_text_wrap();

            $formatCurrencyProgram = $this->mrWorkbook->add_format();
            $formatCurrencyProgram->set_border(1);
            $formatCurrencyProgram->set_bold();
            $formatCurrencyProgram->set_size(10);
            $formatCurrencyProgram->set_align('right');
            $formatCurrencyProgram->set_align('vcenter');
            $formatCurrencyProgram->set_num_format(3);
    
            $formatKegiatan = $this->mrWorkbook->add_format();
            $formatKegiatan->set_border(1);
            $formatKegiatan->set_bold();
            $formatKegiatan->set_size(10);
            $formatKegiatan->set_align('left');
            $formatKegiatan->set_align('vcenter');
            $formatKegiatan->set_text_wrap();
            
            $formatRKegiatan = $this->mrWorkbook->add_format();
            $formatRKegiatan->set_border(1);
            $formatRKegiatan->set_bold();
            $formatRKegiatan->set_size(10);
            $formatRKegiatan->set_align('right');
            $formatRKegiatan->set_align('vcenter');
            $formatRKegiatan->set_text_wrap();
    
            $formatCurrencyKegiatan = $this->mrWorkbook->add_format();
            $formatCurrencyKegiatan->set_border(1);
            $formatCurrencyKegiatan->set_bold();
            $formatCurrencyKegiatan->set_size(10);
            $formatCurrencyKegiatan->set_align('right');
            $formatCurrencyKegiatan->set_align('vcenter');
            $formatCurrencyKegiatan->set_num_format(3);
            
            $formatSubKegiatan = $this->mrWorkbook->add_format();
            $formatSubKegiatan->set_border(1);
            $formatSubKegiatan->set_italic();
            $formatSubKegiatan->set_size(10);
            $formatSubKegiatan->set_align('left');
            $formatSubKegiatan->set_align('vcenter');
            $formatSubKegiatan->set_text_wrap();
            
            $formatRSubKegiatan = $this->mrWorkbook->add_format();
            $formatRSubKegiatan->set_border(1);
            $formatRSubKegiatan->set_italic();
            $formatRSubKegiatan->set_size(10);
            $formatRSubKegiatan->set_align('right');
            $formatRSubKegiatan->set_align('vcenter');
            $formatRSubKegiatan->set_text_wrap();
            
            $formatCurrencySubKegiatan = $this->mrWorkbook->add_format();
            $formatCurrencySubKegiatan->set_border(1);
            $formatCurrencySubKegiatan->set_italic();
            $formatCurrencySubKegiatan->set_size(10);
            $formatCurrencySubKegiatan->set_align('right');
            $formatCurrencySubKegiatan->set_align('vcenter');
            $formatCurrencySubKegiatan->set_num_format(3);
            
            
            $format = $this->mrWorkbook->add_format();
            $format->set_border(1);
            $format->set_align('left');
            $format->set_align('vcenter');
            $format->set_text_wrap();
            
            $formatR = $this->mrWorkbook->add_format();
            $formatR->set_border(1);
            $formatR->set_align('right');
            $formatR->set_align('vcenter');
            $formatR->set_text_wrap();
    

            $formatCurrency = $this->mrWorkbook->add_format();
            $formatCurrency->set_border(1);
            $formatCurrency->set_align('right');
            $formatCurrency->set_align('vcenter');
            $formatCurrency->set_num_format(3);;
	
			
		   $this->mWorksheets['Data']->write(1, 0, 'Laporan Pendapatan Belanja Agregat', $fTitle);
		   $this->mWorksheets['Data']->write(2, 0,  $this->L('tahun_periode').' : '.$ta['name'],$fTitle);
		   $this->mWorksheets['Data']->write(3, 0, $this->L('unit').' / '.$this->L('sub_unit').' : '.$unitkerja_label,$fTitle);		   
		   $num=6;
		   
		   $taSebelum = !empty($ta_kemarin['name']) ? $ta_kemarin['name'] :$ta['name'];
           $this->mWorksheets['Data']->set_column(0,0,5);
           $this->mWorksheets['Data']->write($num, 0, 'No', $formatHeader);
           $this->mWorksheets['Data']->set_column(1,1,60);
           $this->mWorksheets['Data']->write($num, 1,  $this->L('uraian'), $formatHeader);
           $this->mWorksheets['Data']->set_column(2,2,20);
           $this->mWorksheets['Data']->write($num, 2, $this->L('realisasi').' TA '.$taSebelum, $formatHeader);
           $this->mWorksheets['Data']->set_column(3,3,20);
		   $this->mWorksheets['Data']->write($num, 3, 'TA '.$ta['name'], $formatHeader);
           
			/**
			 * untuk laporan pendapatan
			 */
           $num = 8;
           //$nomor = 0;
           /*
           $totalRP = 0;
			for($k = 0;$k < sizeof($laporanP);$k++){
						
			   $totalRP += 	 $laporanP[$k]['realisasi'];
			   $this->mWorksheets['Data']->write_string($num, 0, '-', $format);
			   $this->mWorksheets['Data']->write($num, 1, $laporanP[$k]['mak_nama'], $format);
			   $this->mWorksheets['Data']->write($num, 2, $laporanP[$k]['realisasi'],$formatCurrency);
			   $this->mWorksheets['Data']->write($num, 3, $laporanP[$k]['realisasi'],$formatCurrency);
			   $num++;
			}*/
			
			/**
			 * untuk laporan pendapatan
			 */
			$totalPSekarang = 0; 
			$totalPSebelum = 0; 
			$map_id = NULL;
			$nomor =0;
			$x=0;			
			
			$listDataItem = $laporanP;
			for($i = 0 ; $i < sizeof($listDataItem); )
			{				
				if($map_id == $listDataItem[$i]['map_id']){
					$lap[$x]['uraian'] ='[ '.$listDataItem[$i]['kode_penerimaan_kode'].' ]'. ' - '.$listDataItem[$i]['kode_penerimaan_nama'];
					$lap[$x]['realisasi'] = $listDataItem[$i]['realisasi_sebelum'];
					$lap[$x]['proyeksi'] = $listDataItem[$i]['nominal_sekarang'];
					$lap[$x]['font_style'] ='font-style:normal;font-weight:normal';
					
					$this->mWorksheets['Data']->write_string($num, 0, '', $format);
					$this->mWorksheets['Data']->write($num, 1, $lap[$x]['uraian'], $format);
					$this->mWorksheets['Data']->write($num, 2, $lap[$x]['realisasi'],$formatCurrency);
					$this->mWorksheets['Data']->write($num, 3, $lap[$x]['proyeksi'],$formatCurrency);					
					$i++;
				}elseif($map_id != $listDataItem[$i]['map_id']){
					$nomor++;
					$map_id = $listDataItem[$i]['map_id'];
					$totalPSebelum += $getTotalPerMAP[$map_id]['realisasi_sebelum'];
					$totalPSekarang += $getTotalPerMAP[$map_id]['nominal_sekarang'];
					
					$lap[$x]['uraian'] =$nomor .'. '.$listDataItem[$i]['map_nama'];
					$lap[$x]['realisasi'] = $getTotalPerMAP[$listDataItem[$i]['map_id']]['realisasi_sebelum'];
					$lap[$x]['proyeksi'] =$getTotalPerMAP[$listDataItem[$i]['map_id']]['nominal_sekarang'];
					$lap[$x]['font_style'] ='font-style:italic;font-weight:bold';
					
					$this->mWorksheets['Data']->write_string($num, 0, '', $format);
					$this->mWorksheets['Data']->write($num, 1, $lap[$x]['uraian'], $format);
					$this->mWorksheets['Data']->write($num, 2, $lap[$x]['realisasi'],$formatCurrency);
					$this->mWorksheets['Data']->write($num, 3, $lap[$x]['proyeksi'],$formatCurrency);					
					
					
					
				}	
				$x++;
				$num++;
				
			}			
		   $lastNum = $num;
		   	
		   $num=7;
		   $this->mWorksheets['Data']->set_column(0,0,5);
           $this->mWorksheets['Data']->write($num, 0, 'I', $formatProgram);
           $this->mWorksheets['Data']->set_column(1,1,60);
           $this->mWorksheets['Data']->write($num, 1, 'PENDAPATAN', $formatProgram);
           $this->mWorksheets['Data']->set_column(2,2,20);
           $this->mWorksheets['Data']->write($num, 2, $totalPSebelum, $formatCurrencyProgram);
           $this->mWorksheets['Data']->set_column(3,3,20);
		   $this->mWorksheets['Data']->write($num, 3, $totalPSekarang, $formatCurrencyProgram);			
		   			
			
		   /**
		    * untuk laporan belanja
		    */
		    
			$sdId ='';
			$num = ($lastNum + 2);/*
			$totalRB=0;
			for($j = 0 ; $j < sizeof($laporanB); )
			{
			
			     if($laporanB[$j]['sumber_dana_id'] == $sdId){
					 $totalRB = $laporanB[$j]['realisasi'];
					$send[$j]['uraian'] = ' - '.$laporanB[$j]['mak_parent_nama'];
					$send[$j]['realisasi'] =$laporanB[$j]['realisasi'];
					$send[$j]['proyeksi'] =$send[$j]['realisasi'];
					
					$this->mWorksheets['Data']->write_string($num, 0, '', $format);
					$this->mWorksheets['Data']->write($num, 1, $send[$j]['uraian'], $format);
					$this->mWorksheets['Data']->write($num, 2, $send[$j]['realisasi'],$formatCurrency);
					$this->mWorksheets['Data']->write($num, 3, $send[$j]['realisasi'],$formatCurrency);					
					
					$j++;
				} elseif($laporanB[$j]['sumber_dana_id'] != $sdId){
					$sdId = $laporanB[$j]['sumber_dana_id'];
					$send[$j]['uraian'] = $laporanB[$j]['sumber_dana_nama'];
					$send[$j]['realisasi'] =$getTotalPerSD[$sdId];
					$send[$j]['proyeksi'] ='';

					$this->mWorksheets['Data']->write_string($num, 0, '', $formatSubKegiatan);
					$this->mWorksheets['Data']->write($num, 1, $send[$j]['uraian'], $formatSubKegiatan);
					$this->mWorksheets['Data']->write($num, 2, $send[$j]['realisasi'],$formatCurrencySubKegiatan);
					$this->mWorksheets['Data']->write($num, 3, $send[$j]['realisasi'],$formatCurrencySubKegiatan);
				}	
				$num++;
			}		   
*/

			$sdId ='';
			
			$totalBSekarang = 0; 
			$totalBSebelum = 0; 
			$nomor =1;
			$listDataItem_b = $laporanB;
			for($j = 0 ; $j < sizeof($listDataItem_b); )
			{
			
			     if($listDataItem_b[$j]['mak_parent_id'] == $sdId){					 
					$send[$j]['uraian'] = $nomor.'. '.$listDataItem_b[$j]['mak_nama'];
					$send[$j]['realisasi'] = $listDataItem_b[$j]['realisasi_sebelum'];
					$send[$j]['proyeksi'] = $listDataItem_b[$j]['nominal_sekarang'];
					$send[$j]['font_style'] ='';
					$this->mWorksheets['Data']->write_string($num, 0, '', $format);
					$this->mWorksheets['Data']->write($num, 1, $send[$j]['uraian'], $format);
					$this->mWorksheets['Data']->write($num, 2, $send[$j]['realisasi'],$formatCurrency);
					$this->mWorksheets['Data']->write($num, 3, $send[$j]['proyeksi'],$formatCurrency);	
					$j++;
					$nomor++;
				} elseif($listDataItem_b[$j]['mak_parent_id'] != $sdId){
					$sdId = $listDataItem_b[$j]['mak_parent_id'];
					$nomor =1;
					$totalBSebelum += $getTotalPerSD[$sdId]['realisasi_sebelum'];
					$totalBSekarang += $getTotalPerSD[$sdId]['nominal_sekarang'];
					$send[$j]['uraian'] = $listDataItem_b[$j]['mak_parent_nama'];
					$send[$j]['realisasi'] =$getTotalPerSD[$sdId]['realisasi_sebelum'];
					$send[$j]['proyeksi'] = $getTotalPerSD[$sdId]['nominal_sekarang'];
					$send[$j]['font_style'] ='font-style:italic;font-weight:bold';
					$this->mWorksheets['Data']->write_string($num, 0, '', $format);
					$this->mWorksheets['Data']->write($num, 1, $send[$j]['uraian'], $format);
					$this->mWorksheets['Data']->write($num, 2, $send[$j]['realisasi'],$formatCurrency);
					$this->mWorksheets['Data']->write($num, 3, $send[$j]['proyeksi'],$formatCurrency);	
				}	
				$num++;
			}

		   	
		   $num= $lastNum;
		   $this->mWorksheets['Data']->set_column(0,0,5);
           $this->mWorksheets['Data']->write($num, 0, '', $formatProgram);
           $this->mWorksheets['Data']->set_column(1,1,60);
           $this->mWorksheets['Data']->write($num, 1, '', $formatProgram);
           $this->mWorksheets['Data']->set_column(2,2,20);
           $this->mWorksheets['Data']->write($num, 2, '', $formatCurrencyProgram);
           $this->mWorksheets['Data']->set_column(3,3,20);
		   $this->mWorksheets['Data']->write($num, 3, '', $formatCurrencyProgram);			   
		   
		   $num= $lastNum  + 1;
		   $this->mWorksheets['Data']->set_column(0,0,5);
           $this->mWorksheets['Data']->write($num, 0, 'II', $formatProgram);
           $this->mWorksheets['Data']->set_column(1,1,60);
           $this->mWorksheets['Data']->write($num, 1, 'BELANJA', $formatProgram);
           $this->mWorksheets['Data']->set_column(2,2,20);
           $this->mWorksheets['Data']->write($num, 2, $totalBSebelum, $formatCurrencyProgram);
           $this->mWorksheets['Data']->set_column(3,3,20);
		   $this->mWorksheets['Data']->write($num, 3, $totalBSekarang, $formatCurrencyProgram);			   
		   
		    	
		}
		
	
	}
}
