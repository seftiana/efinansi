<?php

/**
 * 
 * class ViewExcelLapNeracaLajur
 * @package lap_neraca_lajur
 * @subpackage response
 * @since 12 September 2012
 * @SystemAnalyst Nanang Ruswianto <nanang@gamatechno.com>
 * @author noor hadi <noor.hadi@gamatechno.com>
 * @copyright 2012 Gamatechno Indonesia
 * 
 */

 
require_once GTFWConfiguration::GetValue('application', 'docroot') . 
            'module/lap_neraca_lajur/business/LapNeracaLajur.class.php';

require_once GTFWConfiguration::GetValue('application', 'docroot') . 
	'module/user_unit_kerja/business/UserUnitKerja.class.php';     

class ViewExcelLapNeracaLajur extends XlsResponse 
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
		
		$this->mDBObj = new LapNeracaLajur();
		$this->mModulName = 'lap_neraca_lajur';
	}
	  
    function GetFileName() 
    {
      // name it whatever you want
      $label = 'Laporan_Neraca_Lajur';
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
		
		$dataLaporan= $this->mDBObj->GetDataLaporan($tahun_anggaran,$unitkerja);
		$get_level_coa = $this->mDBObj->GetLevelCoa();
		$get_header_kolom = $this->mDBObj->GetHeaderKolom();
		//$data['ta'] = $this->mDBObj->GetTahunAnggaran($tahun_anggaran);		

		//$dataLaporan = $this->mDBObj->GetDataLaporan($tahun_anggaran,$unitkerja);                                                   
	

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
            $formatCurrencyProgram->set_num_format(4);
    
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
            
            $formatTB = $this->mrWorkbook->add_format();
            $formatTB->set_bottom(1);
            $formatTB->set_align('left');
            $formatTB->set_align('vcenter');
            $formatTB->set_text_wrap();
            
            $formatTBB = $this->mrWorkbook->add_format();
            $formatTBB->set_bottom(1);
            $formatTBB->set_bold();
            $formatTBB->set_align('left');
            $formatTBB->set_align('vcenter');
            $formatTBB->set_text_wrap();
            
            $formatR = $this->mrWorkbook->add_format();
            $formatR->set_border(1);
            $formatR->set_align('right');
            $formatR->set_align('vcenter');
            $formatR->set_text_wrap();
    

            $formatCurrency = $this->mrWorkbook->add_format();
            $formatCurrency->set_border(1);
            $formatCurrency->set_align('right');
            $formatCurrency->set_align('vcenter');
            $formatCurrency->set_num_format(4);
	
			
		   $this->mWorksheets['Data']->write(0, 0, 'Laporan Neraca Lajur', $fTitle);
		   $this->mWorksheets['Data']->write(2, 0, $this->L('tahun_periode').' : '.$ta['name']);
		   $this->mWorksheets['Data']->write(3, 0, $this->L('unit').' / '.$this->L('sub_unit').' : '.$unitkerja_label);		   
		   $num=5;
		   
		   
		   /**
		    * buat header
		    */
           $this->mWorksheets['Data']->set_column(0,0,10);
           $this->mWorksheets['Data']->write($num, 0, $this->L('kode'), $formatHeader);
           $this->mWorksheets['Data']->write($num, 1, $this->L('uraian'), $formatHeader);
           
           $max_level = sizeof($get_level_coa);
           	if($max_level > 0){
				for($col = 1;$col < $max_level; $col++){
					$this->mWorksheets['Data']->write($num, $col,'', $formatHeader);
				}
				$this->mWorksheets['Data']->merge_cells($num,1,$num, $max_level);
		   } 
           /*
		   $colStart1 = $col +1;
		   $this->mWorksheets['Data']->write($num, $colStart1,'Neraca Saldo Awal', $formatHeader);
		   $colStart1 +=1;
		   $this->mWorksheets['Data']->write($num, $colStart1,'', $formatHeader);
		   $this->mWorksheets['Data']->merge_cells($num,($colStart1 -1),$num, $colStart1);
		   
		   $colStart1 +=1;
		   $this->mWorksheets['Data']->write($num, $colStart1,'Aktivitas', $formatHeader);
		   $colStart1 +=1;
		   $this->mWorksheets['Data']->write($num, $colStart1,'', $formatHeader);
		   $this->mWorksheets['Data']->merge_cells($num,($colStart1 -1),$num, $colStart1);
		   
		   $colStart1 +=1;
		   $this->mWorksheets['Data']->write($num, $colStart1,'Ayat Jurnal Penyesuaian', $formatHeader);
		   $colStart1 +=1;
		   $this->mWorksheets['Data']->write($num, $colStart1,'', $formatHeader);
		   $this->mWorksheets['Data']->merge_cells($num,($colStart1 -1),$num, $colStart1);
		   
		   $colStart1 +=1;
		   $this->mWorksheets['Data']->write($num, $colStart1,'Neraca Saldo Disesuaikan', $formatHeader);
		   $colStart1 +=1;
		   $this->mWorksheets['Data']->write($num, $colStart1,'', $formatHeader);
		   $this->mWorksheets['Data']->merge_cells($num,($colStart1 -1),$num, $colStart1);
		   
		   $colStart1 +=1;
		   $this->mWorksheets['Data']->write($num, $colStart1,'Rugi/Laba (Defisit/Surplus)', $formatHeader);
		   $colStart1 +=1;
		   $this->mWorksheets['Data']->write($num, $colStart1,'', $formatHeader);
		   $this->mWorksheets['Data']->merge_cells($num,($colStart1 -1),$num, $colStart1);
		   */ 
		   $colStart1 +=1;
		   $this->mWorksheets['Data']->write($num, $colStart1,'Neraca', $formatHeader);
		   $colStart1 +=1;
		   $this->mWorksheets['Data']->write($num, $colStart1,'', $formatHeader);
		   $this->mWorksheets['Data']->merge_cells($num,($colStart1 -1),$num, $colStart1);
		   
		   $num = 6;
		   $this->mWorksheets['Data']->write($num, 0,'', $formatHeader);
           $this->mWorksheets['Data']->write($num, 1, '', $formatHeader);
           $this->mWorksheets['Data']->merge_cells(5,0,6, 0);
           $this->mWorksheets['Data']->merge_cells(5,1,6,  $max_level);
           /*
           $colStart2 = $col + 1;
		   $this->mWorksheets['Data']->write($num, $colStart2,'Debet', $formatHeader);
		   $this->mWorksheets['Data']->set_column($colStart2,$colStart2,20);
		   $colStart2 +=1;
		   $this->mWorksheets['Data']->write($num, $colStart2,'Kredit', $formatHeader);
		   $this->mWorksheets['Data']->set_column($colStart2,$colStart2,20);
           $colStart2 +=1;
		   $this->mWorksheets['Data']->write($num, $colStart2,'Debet', $formatHeader);
		   $this->mWorksheets['Data']->set_column($colStart2,$colStart2,20);
		   $colStart2 +=1;
		   $this->mWorksheets['Data']->write($num, $colStart2,'Kredit', $formatHeader);
		   $this->mWorksheets['Data']->set_column($colStart2,$colStart2,20);
           $colStart2 +=1;
		   $this->mWorksheets['Data']->write($num, $colStart2,'Debet', $formatHeader);
		   $this->mWorksheets['Data']->set_column($colStart2,$colStart2,20);
		   $colStart2 +=1;
		   $this->mWorksheets['Data']->write($num, $colStart2,'Kredit', $formatHeader);
		   $this->mWorksheets['Data']->set_column($colStart2,$colStart2,20);
           $colStart2 +=1;
		   $this->mWorksheets['Data']->write($num, $colStart2,'Debet', $formatHeader);
		   $this->mWorksheets['Data']->set_column($colStart2,$colStart2,20);
		   $colStart2 +=1;
		   $this->mWorksheets['Data']->write($num, $colStart2,'Kredit', $formatHeader);
		   $this->mWorksheets['Data']->set_column($colStart2,$colStart2,20);
           $colStart2 +=1;
		   $this->mWorksheets['Data']->write($num, $colStart2,'Debet', $formatHeader);
		   $this->mWorksheets['Data']->set_column($colStart2,$colStart2,20);
		   $colStart2 +=1;
		   $this->mWorksheets['Data']->write($num, $colStart2,'Kredit', $formatHeader);
		   $this->mWorksheets['Data']->set_column($colStart2,$colStart2,20);
           */
           $colStart2 +=1;
		   $this->mWorksheets['Data']->write($num, $colStart2,'Debet', $formatHeader);
		   $this->mWorksheets['Data']->set_column($colStart2,$colStart2,20);
		   $colStart2 +=1;
		   $this->mWorksheets['Data']->write($num, $colStart2,'Kredit', $formatHeader);
		   $this->mWorksheets['Data']->set_column($colStart2,$colStart2,20);
           
			/**
			 * buat kolom level
			 */
		    $num = 7;
		    $colAwal = 0;
			$this->mWorksheets['Data']->write($num, $colAwal, 0, $formatHeader);
			$col = $colAwal + 1;		
			$wide = 0;
			if(sizeof($get_level_coa) > 0){
				for($k = 0;$k < sizeof($get_level_coa); $k++){
					if($k == (sizeof($get_level_coa) -1)){
						$wide = 50;
					} else{
						$wide = 3;
					}	
					$this->mWorksheets['Data']->set_column($col,$col,$wide);
					$this->mWorksheets['Data']->write($num, $col, $get_level_coa[$k]['level_coa'], $formatHeader);
					$col++;
				}
			} 
			
			
			$number = $max_level + 1;
			$this->mWorksheets['Data']->write($num, $col, $number, $formatHeader);
			/*
			$col+=1;
			$number+=1;
			$this->mWorksheets['Data']->write($num, $col, $number, $formatHeader);
			$col+=1;
			$number+=1;
			$this->mWorksheets['Data']->write($num, $col, $number, $formatHeader);
			$col+=1;
			$number+=1;
			$this->mWorksheets['Data']->write($num, $col, $number, $formatHeader);
			$col+=1;
			$number+=1;
			$this->mWorksheets['Data']->write($num, $col, $number, $formatHeader);
			$col+=1;
			$number+=1;
			$this->mWorksheets['Data']->write($num, $col, $number, $formatHeader);
			$col+=1;
			$number+=1;
			$this->mWorksheets['Data']->write($num, $col, $number, $formatHeader);
			$col+=1;
			$number+=1;
			$this->mWorksheets['Data']->write($num, $col, $number, $formatHeader);
			$col+=1;
			$number+=1;
			$this->mWorksheets['Data']->write($num, $col, $number, $formatHeader);
			$col+=1;
			$number+=1;
			$this->mWorksheets['Data']->write($num, $col, $number, $formatHeader);
			*/
            $col+=1;
			$number+=1;
			$this->mWorksheets['Data']->write($num, $col, $number, $formatHeader);
			$col+=1;
			$number+=1;
			$this->mWorksheets['Data']->write($num, $col, $number, $formatHeader);
	
			
			$num +=1;
			for($k = 0;$k < sizeof($dataLaporan);$k++){		
				   
				if($this->mDBObj->GetChildAkun($dataLaporan[$k]['id_akun']) > 0){
					$f = $formatProgram;
					$fB = $formatTBB;
					$fcr = $formatCurrencyProgram;
				} else{
					$f = $format;
					$fB = $formatTB;
					$fcr = $formatCurrency;
				}	
				$this->mWorksheets['Data']->write($num,0,$dataLaporan[$k]['kode_akun'], $f);
				if(sizeof($get_level_coa) > 0){
					for($c = 0;$c < sizeof($get_level_coa); $c++){
						if($dataLaporan[$k]['level_akun'] == $get_level_coa[$c]['level_coa']){
							$colPos = $dataLaporan[$k]['level_akun'];
							$this->mWorksheets['Data']->write($num, $get_level_coa[$c]['level_coa'],$dataLaporan[$k]['nama_akun'], $fB);
						} else{
							$this->mWorksheets['Data']->write($num, $get_level_coa[$c]['level_coa'],'', $fB);
						}
					}
					$this->mWorksheets['Data']->merge_cells($num,$colPos,$num, $max_level);
				} 					
				/** neraca saldo awal **/
				/*$col = $max_level + 1;
				$this->mWorksheets['Data']->write($num, $col,$dataLaporan[$k]['neraca_sa_debet'], $fcr);
				$col+=1;
				$this->mWorksheets['Data']->write($num, $col,$dataLaporan[$k]['neraca_sa_kredit'], $fcr);
				*/
				/** aktivitas **/
				/*$col+=1;
				$this->mWorksheets['Data']->write($num, $col,$dataLaporan[$k]['aktivitas_debet'],  $fcr);
				$col+=1;
				$this->mWorksheets['Data']->write($num, $col,$dataLaporan[$k]['aktivitas_kredit'], $fcr);
				*/
				/** jurnal penyesuaian **/
				/*$col+=1;
				$this->mWorksheets['Data']->write($num, $col,$dataLaporan[$k]['jp_debet'], $fcr);
				$col+=1;
				$this->mWorksheets['Data']->write($num, $col,$dataLaporan[$k]['jp_kredit'],$fcr);
                */
				/** neraca saldo disesuaikan **/
				/*if(($dataLaporan[$k]['kelompok_akun'] == 1) || ($dataLaporan[$k]['kelompok_akun']== 5 )){
					$neraca_sa_disesuaikan_debet =  ($dataLaporan[$k]['neraca_sa_debet'] - $dataLaporan[$k]['neraca_sa_kredit']) +
												($dataLaporan[$k]['aktivitas_debet'] - $dataLaporan[$k]['aktivitas_kredit']) +
												($dataLaporan[$k]['jp_debet'] - $dataLaporan[$k]['jp_kredit']);	
					$neraca_sa_disesuaikan_kredit = 0;							
				} else{
					$neraca_sa_disesuaikan_debet =0;
					$neraca_sa_disesuaikan_kredit =  ($dataLaporan[$k]['neraca_sa_kredit'] - $dataLaporan[$k]['neraca_sa_debet']) +
												($dataLaporan[$k]['aktivitas_kredit'] - $dataLaporan[$k]['aktivitas_debet']) +
												($dataLaporan[$k]['jp_kredit'] - $dataLaporan[$k]['jp_debet']);	
				}
								
				
				
				$col+=1;
				$this->mWorksheets['Data']->write($num, $col,$neraca_sa_disesuaikan_debet,$fcr);
				$col+=1;
				$this->mWorksheets['Data']->write($num, $col, $neraca_sa_disesuaikan_kredit,$fcr);
				*/
				/** rugi laba **/
				/*if($dataLaporan[$k]['kelompok_akun']== 5 ){
					$rl_d = $neraca_sa_disesuaikan_debet - $neraca_sa_disesuaikan_kredit;
					$rl_k = 0;
				}elseif($dataLaporan[$k]['kelompok_akun']== 4 ){
					$rl_d = 0;
					$rl_k = -($neraca_sa_disesuaikan_debet - $neraca_sa_disesuaikan_kredit);
				}else{
					$rl_d = 0;
					$rl_k = 0;
				}				
				$col+=1;
				$this->mWorksheets['Data']->write($num, $col, $rl_d, $fcr);
				$col+=1;
				$this->mWorksheets['Data']->write($num, $col, $rl_k, $fcr);
				*/
				/** neraca **/
				$col+=1;
				$this->mWorksheets['Data']->write($num, $col,$dataLaporan[$k]['neraca_debet'],  $fcr);
				$col+=1;
				$this->mWorksheets['Data']->write($num, $col, $dataLaporan[$k]['neraca_kredit'],$fcr);
				
				$num++;
			}
		}
	}
}


?>