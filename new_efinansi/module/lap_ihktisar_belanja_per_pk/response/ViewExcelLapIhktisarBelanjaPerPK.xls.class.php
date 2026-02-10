<?php

/**
 * 
 * class ViewExcelLapIhktisarBelanjaPerPK
 * @package lap_ihktisar_belanja_per_pk
 * @subpackage response
 * @since 12 September 2012
 * @SystemAnalyst Nanang Ruswianto <nanang@gamatechno.com>
 * @author noor hadi <noor.hadi@gamatechno.com>
 * @copyright 2012 Gamatechno Indonesia
 * 
 */
 
require_once GTFWConfiguration::GetValue('application', 'docroot') . 
            'module/lap_ihktisar_belanja_per_pk/business/LapIhktisarBelanjaPerPK.class.php';

require_once GTFWConfiguration::GetValue('application', 'docroot') . 
	'module/user_unit_kerja/business/UserUnitKerja.class.php';     

class ViewExcelLapIhktisarBelanjaPerPK extends XlsResponse 
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
		$this->mDBObj = new LapIhktisarBelanjaPerPK();
		$this->mModulName = 'lap_ihktisar_belanja_per_pk';
	}
	  
    function GetFileName() 
    {
      // name it whatever you want
      $label = 'Laporan_Ihktisar_Belanja_Per_Program_Kegiatan';
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
		
		

		$listDataItem = $this->mDBObj->GetDataLaporan($tahun_anggaran,$unitkerja);
		$header  = $this->mDBObj->GetPaguBasHeader($tahun_anggaran,$unitkerja);
		$getNominalPengeluaran = $this->mDBObj->GetNominalPengeluaran($tahun_anggaran,$unitkerja);		
		$getNominalPengeluaranPerK=$this->mDBObj->GetNominalPengeluaranPerK($tahun_anggaran,$unitkerja);		
		$getNominalPengeluaranPerP=$this->mDBObj->GetNominalPengeluaranPerP($tahun_anggaran,$unitkerja);		
	

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
            $formatKegiatan->set_italic();
            $formatKegiatan->set_size(10);
            $formatKegiatan->set_align('left');
            $formatKegiatan->set_align('vcenter');
            $formatKegiatan->set_text_wrap();
            
            $formatRKegiatan = $this->mrWorkbook->add_format();
            $formatRKegiatan->set_border(1);
            $formatRKegiatan->set_italic();
            $formatRKegiatan->set_size(10);
            $formatRKegiatan->set_align('right');
            $formatRKegiatan->set_align('vcenter');
            $formatRKegiatan->set_text_wrap();
    
            $formatCurrencyKegiatan = $this->mrWorkbook->add_format();
            $formatCurrencyKegiatan->set_border(1);
            $formatCurrencyKegiatan->set_italic();
            $formatCurrencyKegiatan->set_size(10);
            $formatCurrencyKegiatan->set_align('right');
            $formatCurrencyKegiatan->set_align('vcenter');
            $formatCurrencyKegiatan->set_num_format(3);
            
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
	
			
		   $this->mWorksheets['Data']->write(0, 0, 'Laporan Ihktisar Belanja Per Program Kegiatan', $fTitle);
		   $this->mWorksheets['Data']->write(2, 0,  $this->L('tahun_periode').' : '.$ta['name']);
		   $this->mWorksheets['Data']->write(3, 0, $this->L('unit').' / '.$this->L('sub_unit').' : '.$unitkerja_label);		   
		   $num=6;
		   
		   $this->mWorksheets['Data']->merge_cells(6,0,7,0);
		   $this->mWorksheets['Data']->merge_cells(6,1,7,1);

		   
           $this->mWorksheets['Data']->set_column(0,0,15);
           $this->mWorksheets['Data']->write($num, 0,  $this->L('kode'), $formatHeader);
           $this->mWorksheets['Data']->set_column(1,1,40);
           $this->mWorksheets['Data']->write($num, 1, $this->L('uraian'), $formatHeader);
           $this->mWorksheets['Data']->set_column(2,2,15);
		   $this->mWorksheets['Data']->write($num, 2, 'Alokasi', $formatHeader);
           
           $num=7;
		   
           $this->mWorksheets['Data']->merge_cells(6,0,7,0);
           $this->mWorksheets['Data']->merge_cells(6,1,7,1);

		   
           $this->mWorksheets['Data']->write($num, 0,  '', $formatHeader);
           $this->mWorksheets['Data']->set_column(1,1,40);
           $this->mWorksheets['Data']->write($num, 1, '', $formatHeader);


            $max_header = sizeof($header);
            /**
             * membuat header
             */           
            $colom = 2; 
            if($max_header > 0){
            
				for($n=0;$n < $max_header;$n++) {
					$colom += $n;
				 
					$this->mWorksheets['Data']->set_column($colom+1,$colom+1 ,15);				 
					$this->mWorksheets['Data']->write(6, $colom+1 , '', $formatHeader);
				 
					$this->mWorksheets['Data']->set_column($colom,$colom,15);
					$this->mWorksheets['Data']->write(7, $colom, $header[$n]['nama'], $formatHeader);				 
				}
				$this->mWorksheets['Data']->merge_cells(6,2,6,$colom);  
            }
          
           $this->mWorksheets['Data']->set_column($colom+1,$colom+1,15); 
		   $this->mWorksheets['Data']->write(6, $colom+1, 'Target / Volume Satuan', $formatHeader);
           $this->mWorksheets['Data']->set_column($colom+2,$colom+2,35);
		   $this->mWorksheets['Data']->write(6, $colom+2, 'Unit Penanggung Jawab', $formatHeader);            

           $this->mWorksheets['Data']->write(7, $colom+1, '', $formatHeader);
           $this->mWorksheets['Data']->write(7, $colom+2, '', $formatHeader);     
           
           $this->mWorksheets['Data']->merge_cells(6,$colom+1,7,$colom + 1);
		   $this->mWorksheets['Data']->merge_cells(6,$colom+2,7,$colom + 2);     

            /**
             * end
             */ 	
             
             
		              	   
			
			$programId ='';
			$kegiatanId ='';
			$x = 0;
			for($i = 0 ; $i < sizeof($listDataItem); )
			{
				if(($programId == $listDataItem[$i]['program_id']) && 
                   ($kegiatanId == $listDataItem[$i]['kegiatan_id']) ){
					   $dataLaporan[$x]['kode'] = $listDataItem[$i]['sub_kegiatan_kode'];
					   $dataLaporan[$x]['nama'] = $listDataItem[$i]['sub_kegiatan_nama']."\r\n".
												  '[ '.$listDataItem[$i]['rkakl_sub_kegiatan_nama'].' ]';
						$dataLaporan[$x]['volume'] = '';												  
					   $dataLaporan[$x]['sub_keg_id'] = $listDataItem[$i]['sub_kegiatan_id'];
					   $dataLaporan[$x]['keg_id'] ='';
					   $dataLaporan[$x]['prog_id'] ='';
					   $dataLaporan[$x]['f'] =$format;
					   $dataLaporan[$x]['fr'] =$formatR;
					   $dataLaporan[$x]['fc'] =$formatCurrency;
					   $unit = explode(',',$listDataItem[$i]['unit']);
					   $dataLaporan[$x]['unit'] = implode("\r\n",array_unique($unit));
					   //$dataLaporan[$x]['padding'] =20;
					   $i++;
				} elseif($programId != $listDataItem[$i]['program_id']){
					$programId = $listDataItem[$i]['program_id'];
					$dataLaporan[$x]['kode'] = $listDataItem[$i]['program_kode'];
					$dataLaporan[$x]['nama'] = $listDataItem[$i]['program_nama']."\r\n".
												  '[ '.$listDataItem[$i]['rkakl_kegiatan_nama'].' ]';
					$dataLaporan[$x]['volume'] = '';
					 $dataLaporan[$x]['f'] =$formatProgram;
					 $dataLaporan[$x]['fr'] =$formatRProgram;
					 $dataLaporan[$x]['fc'] =$formatCurrencyProgram;
					 $dataLaporan[$x]['sub_keg_id'] = '';
					 $dataLaporan[$x]['keg_id'] ='';
					 $dataLaporan[$x]['prog_id'] =$listDataItem[$i]['program_id'];
					 $dataLaporan[$x]['unit']='';
				} elseif($kegiatanId != $listDataItem[$i]['kegiatan_id']){
					$kegiatanId  = $listDataItem[$i]['kegiatan_id'];
					$dataLaporan[$x]['kode'] = $listDataItem[$i]['kegiatan_kode'];
					$dataLaporan[$x]['nama'] = $listDataItem[$i]['kegiatan_nama']."\r\n".
												  '[ '.$listDataItem[$i]['rkakl_output_nama'].' ] ';
				    $dataLaporan[$x]['volume'] = '';
					$dataLaporan[$x]['f'] =$formatKegiatan;
					$dataLaporan[$x]['fr'] =$formatRKegiatan;
					$dataLaporan[$x]['fc'] =$formatCurrencyKegiatan;
					$dataLaporan[$x]['sub_keg_id'] = '';
					$dataLaporan[$x]['keg_id'] =$listDataItem[$i]['kegiatan_id'];
					$dataLaporan[$x]['prog_id'] ='';
					$dataLaporan[$x]['unit']='';
				}
				$x++;
			}
			
            $n = $getNominalPengeluaran;
            $keg = $getNominalPengeluaranPerK;
            $prog = $getNominalPengeluaranPerP;
            $num=8;  
            $colom = 2;
			for($k = 0;$k < sizeof($dataLaporan);$k++){
				$this->mWorksheets['Data']->write($num, 0, $dataLaporan[$k]['kode'],$dataLaporan[$k]['fr']);
				$this->mWorksheets['Data']->write($num, 1, $dataLaporan[$k]['nama'],$dataLaporan[$k]['f']);				
                if($max_header > 0){
                    for($f=0;$f <sizeof($header);$f++) {	
						
                            if(isset($dataLaporan[$k]['sub_keg_id']) && $dataLaporan[$k]['sub_keg_id'] != ''){
								 $this->mWorksheets['Data']->write($num, $colom + $f, 
                                            $n[$dataLaporan[$k]['sub_keg_id']][$header[$f]['id']],$dataLaporan[$k]['fc']);				
							}elseif(isset($dataLaporan[$k]['keg_id']) && $dataLaporan[$k]['keg_id'] !=''){
								 $this->mWorksheets['Data']->write($num, $colom + $f, 
                                           $keg[$dataLaporan[$k]['keg_id']][$header[$f]['id']],$dataLaporan[$k]['fc']);														
							}elseif(isset($dataLaporan[$k]['prog_id']) && $dataLaporan[$k]['prog_id'] !=''){
								 $this->mWorksheets['Data']->write($num, $colom + $f, 
                                            $prog[$dataLaporan[$k]['prog_id']][$header[$f]['id']],$dataLaporan[$k]['fc']);														
							} else {
								 $this->mWorksheets['Data']->write($num, $colom + $f, '',$dataLaporan[$k]['fc']);			                                
							}
                    }
                }				
				$this->mWorksheets['Data']->write($num, ($colom + $max_header), 
                            $dataLaporan[$k]['volume'],$dataLaporan[$k]['f']);
				$this->mWorksheets['Data']->write($num, ($colom + $max_header+1), 
                            $dataLaporan[$k]['unit'],$dataLaporan[$k]['f']);
			   $num++;
			}			
			
			
		}
	}
}
