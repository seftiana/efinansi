<?php

/**
 * 
 * class ViewExcelLapPengadaanRup
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

require_once GTFWConfiguration::GetValue('application', 'docroot') . 
	'module/user_unit_kerja/business/UserUnitKerja.class.php';       

class ViewExcelLapPengadaanRup extends XlsResponse 
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
		$this->mDBObj = new LapPengadaanRup();
		$this->mModulName = 'lap_pengadaan_rup';
	}
	  
    function GetFileName() 
    {
      // name it whatever you want
      $label = 'lap_pengadaan_rup';
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
		
		$listDataItem = $this->mDBObj->GetDataLaporan($tahun_anggaran,$unitkerja);
		$getTotalPerSk =$this->mDBObj->GetTotalPerSk($tahun_anggaran,$unitkerja);
		$getTotalPerK =$this->mDBObj->GetTotalPerk($tahun_anggaran,$unitkerja);
		$getTotalAll =$this->mDBObj->GetTotalAll($tahun_anggaran,$unitkerja);

		if (empty($listDataItem)) {
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
    
            $formatUnit = $this->mrWorkbook->add_format();
            $formatUnit->set_border(1);
            $formatUnit->set_bold();
            $formatUnit->set_size(10);
            $formatUnit->set_align('left');
            $formatUnit->set_align('vcenter');
            $formatUnit->set_text_wrap();

            $formatCurrencyUnit = $this->mrWorkbook->add_format();
            $formatCurrencyUnit->set_border(1);
            $formatCurrencyUnit->set_bold();
            $formatCurrencyUnit->set_size(10);
            $formatCurrencyUnit->set_align('right');
            $formatCurrencyUnit->set_align('vcenter');
            $formatCurrencyUnit->set_num_format(3);
                        
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
            $formatKegiatan->set_italic();
            $formatKegiatan->set_size(10);
            $formatKegiatan->set_align('left');
            $formatKegiatan->set_align('vcenter');
            $formatKegiatan->set_text_wrap();
            
            $formatRKegiatan = $this->mrWorkbook->add_format();
            $formatRKegiatan->set_border(1);
            $formatRKegiatan->set_bold();
            $formatRKegiatan->set_italic();
            $formatRKegiatan->set_size(10);
            $formatRKegiatan->set_align('right');
            $formatRKegiatan->set_align('vcenter');
            $formatRKegiatan->set_text_wrap();
    
            $formatCurrencyKegiatan = $this->mrWorkbook->add_format();
            $formatCurrencyKegiatan->set_border(1);
            $formatCurrencyKegiatan->set_bold();
            $formatCurrencyKegiatan->set_italic();
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
            
            $formatBas = $this->mrWorkbook->add_format();
            $formatBas->set_border(1);
            $formatBas->set_underline(1);
            $formatBas->set_size(10);
            $formatBas->set_align('left');
            $formatBas->set_align('vcenter');
            $formatBas->set_text_wrap();
            
            $formatCurrencyBas = $this->mrWorkbook->add_format();
            $formatCurrencyBas->set_border(1);
            $formatCurrencyBas->set_underline(1);
            $formatCurrencyBas->set_size(10);
            $formatCurrencyBas->set_align('right');
            $formatCurrencyBas->set_align('vcenter');
            $formatCurrencyBas->set_num_format(3);
            
            $formatMak = $this->mrWorkbook->add_format();
            $formatMak->set_border(1);
            $formatMak->set_italic();
            $formatMak->set_underline(1);
            $formatMak->set_size(10);
            $formatMak->set_align('left');
            $formatMak->set_align('vcenter');
            $formatMak->set_text_wrap();
            
            $formatCurrencyMak = $this->mrWorkbook->add_format();
            $formatCurrencyMak->set_border(1);
            $formatCurrencyMak->set_italic();
            $formatCurrencyMak->set_underline(1);
            $formatCurrencyMak->set_size(10);
            $formatCurrencyMak->set_align('right');
            $formatCurrencyMak->set_align('vcenter');
            $formatCurrencyMak->set_num_format(3);
                        
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
            $formatCurrency->set_num_format(3);
		
			
		   $this->mWorksheets['Data']->write(0, 0,  $this->L('lap_pengadaan_rup'), $fTitle);
		   $this->mWorksheets['Data']->write(2, 0,  $this->L('tahun_periode').' : '.$ta['name']);
		   $this->mWorksheets['Data']->write(3, 0,  $this->L('unit').' / '.$this->L('sub_unit').' : '.$unitkerja_label);		   
		   $num=6;
		   
           $this->mWorksheets['Data']->set_column(0,0,15);
           $this->mWorksheets['Data']->write($num, 0,  $this->L('kode'), $formatHeader);
           
           $this->mWorksheets['Data']->set_column(1,1,40);
           $this->mWorksheets['Data']->write($num, 1,  
									$this->L('kegiatan').'/'. 
									$this->L('sub_kegiatan').'/'. 
									$this->L('mak'), $formatHeader);
           
           $this->mWorksheets['Data']->set_column(2,2,15);
		   $this->mWorksheets['Data']->write($num, 2, $this->L('volume'), $formatHeader);
           
           $this->mWorksheets['Data']->set_column(3,3,25);
		   $this->mWorksheets['Data']->write($num, 3,$this->L('total'), $formatHeader);
           
           $this->mWorksheets['Data']->set_column(4,4,30);
		   $this->mWorksheets['Data']->write($num, 4, $this->L('sumber_dana'), $formatHeader);
		   $num=7;
		   
			$kegiatanId ='';
			$subKegiatanId ='';
			
			$x = 0;
			$no=0;
			
		

			for($i = 0 ; $i < sizeof($listDataItem); )
			{
				if(($kegiatanId == $listDataItem[$i]['output_id']) && 
					($subKegiatanId == $listDataItem[$i]['komponen_id'])
					){	
						
					$dataLaporan[$i]['kode'] = $listDataItem[$i]['mak_kode'];
					$dataLaporan[$i]['nama'] = $listDataItem[$i]['mak_nama'];
					$dataLaporan[$i]['volume'] = $listDataItem[$i]['db_volume'];
					$dataLaporan[$i]['nominal_total'] = $listDataItem[$i]['db_nominal_total'];
					if(!empty($listDataItem[$i]['sumber_dana_nama'])){
							$sumberDana = str_replace(',',"\n\r",$listDataItem[$i]['sumber_dana_nama']);
					}
					$dataLaporan[$i]['sumber_dana_nama'] = $sumberDana;										
					
					$this->mWorksheets['Data']->write_string($num, 0, $dataLaporan[$i]['kode'],$format);
					$this->mWorksheets['Data']->write_string($num, 1, $dataLaporan[$i]['nama'],$format);
					$this->mWorksheets['Data']->write($num, 2, $dataLaporan[$i]['volume'], $formatCurrency);
					$this->mWorksheets['Data']->write($num, 3, $dataLaporan[$i]['nominal_total'], $formatCurrency);
					$this->mWorksheets['Data']->write($num, 4, $dataLaporan[$i]['sumber_dana_nama'], $format);
					
					$i++;
				} elseif($kegiatanId != $listDataItem[$i]['output_id']){
					$kegiatanId = $listDataItem[$i]['output_id'];
					$dataLaporan[$i]['kode'] = $listDataItem[$i]['output_kode'];
					$dataLaporan[$i]['nama'] = $listDataItem[$i]['output_nama'];
					$dataLaporan[$i]['volume'] = NULL;
					$dataLaporan[$i]['nominal_total'] = $getTotalPerK[$kegiatanId];
					$dataLaporan[$i]['sumber_dana_nama'] = NULL;
					
					$this->mWorksheets['Data']->write_string($num, 0, $dataLaporan[$i]['kode'],$formatProgram);
					$this->mWorksheets['Data']->write_string($num, 1, $dataLaporan[$i]['nama'],$formatProgram);
					$this->mWorksheets['Data']->write($num, 2, $dataLaporan[$i]['volume'], $formatCurrencyProgram);
					$this->mWorksheets['Data']->write($num, 3, $dataLaporan[$i]['nominal_total'], $formatCurrencyProgram);
					$this->mWorksheets['Data']->write($num, 4, $dataLaporan[$i]['sumber_dana_nama'], $formatProgram);
				} elseif($subKegiatanId != $listDataItem[$i]['komponen_id']){
					$subKegiatanId = $listDataItem[$i]['komponen_id'];
					$dataLaporan[$i]['kode'] = $listDataItem[$i]['komponen_kode'];
					$dataLaporan[$i]['nama'] = $listDataItem[$i]['komponen_nama'];
					$dataLaporan[$i]['volume'] = NULL;
					$dataLaporan[$i]['nominal_total'] = $getTotalPerSk[$subKegiatanId];
					$dataLaporan[$i]['sumber_dana_nama'] = NULL;
					
					$this->mWorksheets['Data']->write_string($num, 0, $dataLaporan[$i]['kode'],$formatKegiatan);
					$this->mWorksheets['Data']->write_string($num, 1, $dataLaporan[$i]['nama'],$formatKegiatan);
					$this->mWorksheets['Data']->write($num, 2, $dataLaporan[$i]['volume'], $formatCurrencyKegiatan);
					$this->mWorksheets['Data']->write($num, 3, $dataLaporan[$i]['nominal_total'], $formatCurrencyKegiatan);
					$this->mWorksheets['Data']->write($num, 4, $dataLaporan[$i]['sumber_dana_nama'], $formatKegiatan);
				} 
				
				$num++;
			}
			
	
			
			$this->mWorksheets['Data']->write($num, 0, '',$formatHeader);
			$this->mWorksheets['Data']->write($num, 1, 'TOTAL BELANJA',$formatHeader);
			$this->mWorksheets['Data']->write($num, 2, '',$formatCurrencyProgram);
			$this->mWorksheets['Data']->write($num, 3, $getTotalAll,$formatCurrencyProgram);
			$this->mWorksheets['Data']->write($num, 4, '',$formatCurrencyProgram);

		    $num += 2;
		   

		}		
		
	
	}
}

?>