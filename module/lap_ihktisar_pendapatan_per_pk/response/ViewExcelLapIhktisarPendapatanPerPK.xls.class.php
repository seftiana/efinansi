<?php

/**
 * 
 * class ViewExcelLapIhktisarPendapatanPerPK
 * @package lap_ihktisar_perndapatan_per_pk
 * @subpackage response
 * @since 12 September 2012
 * @SystemAnalyst Nanang Ruswianto <nanang@gamatechno.com>
 * @author noor hadi <noor.hadi@gamatechno.com>
 * @copyright 2012 Gamatechno Indonesia
 * 
 */
 
require_once GTFWConfiguration::GetValue('application', 'docroot') . 
            'module/lap_ihktisar_pendapatan_per_pk/business/LapIhktisarPendapatanPerPK.class.php';

require_once GTFWConfiguration::GetValue('application', 'docroot') . 
	'module/user_unit_kerja/business/UserUnitKerja.class.php';     

class ViewExcelLapIhktisarPendapatanPerPK extends XlsResponse 
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
		$this->mDBObj = new LapIhktisarPendapatanPerPK();
		$this->mModulName = 'lap_ihktisar_pendapatan_per_pk';
	}
	  
    function GetFileName() 
    {
      // name it whatever you want
      $label = 'Laporan_Ihktisar_Pendapatan_Per_Program_Kegiatan';
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
		
		

		$dataLaporan = $this->mDBObj->GetDataLaporan($tahun_anggaran,$unitkerja);                                                   
	

		if (empty($dataLaporan)) {
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
            $formatCurrencyKegiatan->set_italic();
            $formatCurrencyKegiatan->set_size(10);
            $formatCurrencyKegiatan->set_align('right');
            $formatCurrencyKegiatan->set_align('vcenter');
            
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
            
            $formatSubKegiatan2 = $this->mrWorkbook->add_format();
            $formatSubKegiatan2->set_border(1);
            $formatSubKegiatan2->set_italic();
            $formatSubKegiatan2->set_underline(1);
            $formatSubKegiatan2->set_size(10);
            $formatSubKegiatan2->set_align('left');
            $formatSubKegiatan2->set_align('vcenter');
            $formatSubKegiatan2->set_text_wrap();
            
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
	
			
		   $this->mWorksheets['Data']->write(0, 0, 'Laporan Ihktisar Perndapatan Per Program Kegiatan', $fTitle);
		   $this->mWorksheets['Data']->write(2, 0,  $this->L('tahun_periode').' : '.$ta['name']);
		   $this->mWorksheets['Data']->write(3, 0, $this->L('unit').' / '.$this->L('sub_unit').' : '.$unitkerja_label);		   
		   $num=6;
		   
           $this->mWorksheets['Data']->set_column(0,0,15);
           $this->mWorksheets['Data']->write($num, 0,  $this->L('kode'), $formatHeader);
           $this->mWorksheets['Data']->set_column(1,1,60);
           $this->mWorksheets['Data']->write($num, 1, $this->L('uraian'), $formatHeader);
           $this->mWorksheets['Data']->set_column(2,2,15);
		   $this->mWorksheets['Data']->write($num, 2, $this->L('target'), $formatHeader);
           
		   $num=9;
			for($k = 0;$k < sizeof($dataLaporan);$k++){

				$total += $dataLaporan[$k]['target'];
			   
			   $this->mWorksheets['Data']->write_string($num, 0, $dataLaporan[$k]['kode'], $formatR);
			   $this->mWorksheets['Data']->write_string($num, 1, $dataLaporan[$k]['nama'], $format);
			   $this->mWorksheets['Data']->write($num, 2, $dataLaporan[$k]['target'], $formatCurrency);
			   $num++;
			}
			
			$this->mWorksheets['Data']->write($num, 0, '',$formatRProgram);
			$this->mWorksheets['Data']->write($num, 1, 'TOTAL',$formatProgram);
			$this->mWorksheets['Data']->write($num, 2, $total,$formatCurrencyProgram);

		   $num=7;
     	   $this->mWorksheets['Data']->write_string($num, 0, '023.04.08', $formatRProgram);
		   $this->mWorksheets['Data']->write_string($num, 1, 'Pendidikan Tinggi', $formatProgram);
		   $this->mWorksheets['Data']->write($num, 2, $total, $formatCurrencyProgram);
		   $num=8;
     	   $this->mWorksheets['Data']->write_string($num, 0, '4078', $formatRProgram);
		   $this->mWorksheets['Data']->write_string($num, 1, 'Layanan Pendidikan', $formatProgram);
		   $this->mWorksheets['Data']->write($num, 2, $total, $formatCurrencyProgram);			
		}
		
	
	}
}
