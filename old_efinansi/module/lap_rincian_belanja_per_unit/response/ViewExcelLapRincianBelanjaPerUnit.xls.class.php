<?php

/**
 * 
 * class ViewExcelLapRincianBelanjaPerUnit
 * @package lap_rincian_belanja_per_unit
 * @subpackage response
 * @since 12 September 2012
 * @SystemAnalyst Nanang Ruswianto <nanang@gamatechno.com>
 * @author noor hadi <noor.hadi@gamatechno.com>
 * @copyright 2012 Gamatechno Indonesia
 * 
 */
 
require_once GTFWConfiguration::GetValue('application', 'docroot') . 
'module/lap_rincian_belanja_per_unit/business/LapRincianBelanjaPerUnit.class.php';

require_once GTFWConfiguration::GetValue('application', 'docroot') . 
'module/user_unit_kerja/business/UserUnitKerja.class.php';     

class ViewExcelLapRincianBelanjaPerUnit extends XlsResponse 
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
      $this->mDBObj     = new LapRincianBelanjaPerUnit();
      $this->mModulName = 'lap_rincian_belanja_per_unit';
   }
     
   function GetFileName() 
   {
      // name it whatever you want
      $label      = 'Laporan_Rincian_Belanja_Per_Unit';
      //str_replace(' ','_',$this->L('lap_rinci_pengeluaran'));    
      // name it whatever you want
      return $label.'.xls'; //'LapRincianPengeluaran.xls';
   }
    
   function L($indexLangName = '')
   {
      $lang    = GTFWConfiguration::GetValue('language',$indexLangName);
      if(!empty($lang)){
         return $lang;  
      }
      return '';
   }
    
   function ProcessRequest() 
   {
      if(isset($_GET)) { //pasti dari form pencarian :p     
         if(is_object($_GET)):
            $v = $_GET->AsArray();
         else:
            $v = $_GET;
         endif;
      } 
        
      
      $tahun_anggaran   = Dispatcher::Instance()->Decrypt($v['tgl']);
      $unitkerja_label  = Dispatcher::Instance()->Decrypt($v['unitkerja_label']);
      $unitkerja        = Dispatcher::Instance()->Decrypt($v['unitkerja']);
      $userId           = Dispatcher::Instance()->Decrypt($v['id']);
      
      $ta               = $this->mDBObj->GetTahunAnggaran($tahun_anggaran);
		
      $listDataItem     = $this->mDBObj->GetDataLaporan($tahun_anggaran,$unitkerja);
                                                    
   

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
         $formatHeader->set_size(11);
         $formatHeader->set_align('center');
         $formatHeader->set_align('vcenter');
         $formatHeader->set_text_wrap();
    
         $formatProgram = $this->mrWorkbook->add_format();
         $formatProgram->set_border(1);
         $formatProgram->set_bold();
         $formatProgram->set_size(11);
         $formatProgram->set_align('left');
         $formatProgram->set_align('vcenter');
         $formatProgram->set_text_wrap();
           
            
         $formatRProgram = $this->mrWorkbook->add_format();
         $formatRProgram->set_border(1);
         $formatRProgram->set_bold();
         $formatRProgram->set_size(11);
         $formatRProgram->set_align('right');
         $formatRProgram->set_align('vcenter');
         $formatRProgram->set_text_wrap();

         $formatCurrencyProgram = $this->mrWorkbook->add_format();
         $formatCurrencyProgram->set_border(1);
         $formatCurrencyProgram->set_bold();
         $formatCurrencyProgram->set_size(11);
         $formatCurrencyProgram->set_align('right');
         $formatCurrencyProgram->set_align('vcenter');
 
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
   
         
         $this->mWorksheets['Data']->write(0, 0, 'Laporan Rincian Belanja Per Unit', $fTitle);
         $this->mWorksheets['Data']->write(2, 0,  $this->L('tahun_periode').' : '.$ta['name']);
         $this->mWorksheets['Data']->write(3, 0, $this->L('unit').' / '.$this->L('sub_unit').' : '.$unitkerja_label);         
         $num=6;
         $this->mWorksheets['Data']->merge_cells(6,0,8,0);
         $this->mWorksheets['Data']->merge_cells(6,1,8,1);
           
         $this->mWorksheets['Data']->merge_cells(6,2,6,8);
         $this->mWorksheets['Data']->merge_cells(6,9,7,11);
         
         $this->mWorksheets['Data']->set_column(0,0,15);
         $this->mWorksheets['Data']->write($num, 0,  'Kode', $formatHeader);
           
         $this->mWorksheets['Data']->set_column(1,1,40);
         $this->mWorksheets['Data']->write($num, 1, 'Uraian Unit / Program / Kegiatan / Sub Kegiatan', $formatHeader);
           
         $this->mWorksheets['Data']->set_column(2,2,15);
         $this->mWorksheets['Data']->write($num, 2, 'TA 2011', $formatHeader);
           
         $this->mWorksheets['Data']->set_column(3,3,15);
         $this->mWorksheets['Data']->write($num, 3, '', $formatHeader);
           
         $this->mWorksheets['Data']->set_column(4,4,10);
         $this->mWorksheets['Data']->write($num, 4, '', $formatHeader);
           
         $this->mWorksheets['Data']->set_column(5,5,15);
         $this->mWorksheets['Data']->write($num, 5, '', $formatHeader);
         $this->mWorksheets['Data']->set_column(6,6,15);
         $this->mWorksheets['Data']->write($num, 6, '', $formatHeader);
         $this->mWorksheets['Data']->set_column(7,7,10);
         $this->mWorksheets['Data']->write($num, 7, '', $formatHeader);
         $this->mWorksheets['Data']->set_column(8,8,15);
         $this->mWorksheets['Data']->write($num, 8, '', $formatHeader);
         $this->mWorksheets['Data']->set_column(9,9,15);
         $this->mWorksheets['Data']->write($num, 9, 'TA 2012', $formatHeader);
         $this->mWorksheets['Data']->set_column(10,10,15);
         $this->mWorksheets['Data']->write($num, 10, '', $formatHeader);
         $this->mWorksheets['Data']->set_column(11,11,15);
         $this->mWorksheets['Data']->write($num, 11, '', $formatHeader);
         
         $num=7;
         
         $this->mWorksheets['Data']->merge_cells(6,0,8,0);
         $this->mWorksheets['Data']->merge_cells(6,1,8,1);
           
         $this->mWorksheets['Data']->merge_cells(7,2,7,4);
         $this->mWorksheets['Data']->merge_cells(7,5,7,8);
           
         $this->mWorksheets['Data']->merge_cells(6,9,7,11);
         
         $this->mWorksheets['Data']->set_column(0,0,15);
         $this->mWorksheets['Data']->write($num, 0,  '', $formatHeader);
           
         $this->mWorksheets['Data']->set_column(1,1,40);
         $this->mWorksheets['Data']->write($num, 1, '', $formatHeader);
           
         $this->mWorksheets['Data']->set_column(2,2,15);
         $this->mWorksheets['Data']->write($num, 2, 'Volume', $formatHeader);
           
         $this->mWorksheets['Data']->set_column(3,3,15);
         $this->mWorksheets['Data']->write($num, 3, '', $formatHeader);
           
         $this->mWorksheets['Data']->set_column(4,4,10);
         $this->mWorksheets['Data']->write($num, 4, '%', $formatHeader);
           
         $this->mWorksheets['Data']->set_column(5,5,15);
         $this->mWorksheets['Data']->write($num, 5, 'Dana', $formatHeader);
         $this->mWorksheets['Data']->set_column(6,6,15);
         $this->mWorksheets['Data']->write($num, 6, '', $formatHeader);
         $this->mWorksheets['Data']->set_column(7,7,10);
         $this->mWorksheets['Data']->write($num, 7, '', $formatHeader);
         $this->mWorksheets['Data']->set_column(8,8,15);
         $this->mWorksheets['Data']->write($num, 8, '', $formatHeader);
         $this->mWorksheets['Data']->set_column(9,9,15);
         $this->mWorksheets['Data']->write($num, 9, '', $formatHeader);
         $this->mWorksheets['Data']->set_column(10,10,15);
         $this->mWorksheets['Data']->write($num, 10, '', $formatHeader);
         $this->mWorksheets['Data']->set_column(11,11,15);
         $this->mWorksheets['Data']->write($num, 11, '', $formatHeader);
           
         $num=8;
         
         $this->mWorksheets['Data']->merge_cells(6,0,8,0);
         $this->mWorksheets['Data']->merge_cells(6,1,8,1);
         
         $this->mWorksheets['Data']->write($num, 0,  '', $formatHeader);
         $this->mWorksheets['Data']->set_column(1,1,40);
         $this->mWorksheets['Data']->write($num, 1, '', $formatHeader);
         $this->mWorksheets['Data']->set_column(2,2,15);
         $this->mWorksheets['Data']->write($num, 2, 'Target', $formatHeader);
         $this->mWorksheets['Data']->set_column(3,3,15);
         $this->mWorksheets['Data']->write($num, 3, 'Prognosa / Realisasi', $formatHeader);
         $this->mWorksheets['Data']->set_column(4,4,10);
         $this->mWorksheets['Data']->write($num, 4, '%', $formatHeader);
         $this->mWorksheets['Data']->set_column(5,5,15);
         $this->mWorksheets['Data']->write($num, 5, 'Target', $formatHeader);
         $this->mWorksheets['Data']->set_column(6,6,15);
         $this->mWorksheets['Data']->write($num, 6, 'Prognosa / Realisasi', $formatHeader);
         $this->mWorksheets['Data']->set_column(7,7,10);
         $this->mWorksheets['Data']->write($num, 7, '%', $formatHeader);
         $this->mWorksheets['Data']->set_column(8,8,15);
         $this->mWorksheets['Data']->write($num, 8, 'SD', $formatHeader);
         $this->mWorksheets['Data']->set_column(9,9,15);
         $this->mWorksheets['Data']->write($num, 9, 'Vol. Satuan', $formatHeader);
         $this->mWorksheets['Data']->set_column(10,10,15);
         $this->mWorksheets['Data']->write($num, 10, 'Target', $formatHeader);
         $this->mWorksheets['Data']->set_column(11,11,15);
         $this->mWorksheets['Data']->write($num, 11, 'SD', $formatHeader);
         $num=9;
         
         $unitId ='';
         $programId ='';
         $kegiatanId ='';
         $subKegiatanId ='';
         $x = 0;
         $no=0;
         for($i = 0 ; $i < sizeof($listDataItem); )
         {
            if(($unitId == $listDataItem[$i]['unit_id']) && 
               ($programId == $listDataItem[$i]['program_id']) && 
               ($kegiatanId == $listDataItem[$i]['kegiatan_id']) &&
               ($subKegiatanId == $listDataItem[$i]['sub_kegiatan_id']) ){
                  $dataLaporan[$x]['kode']   = '';
                  $dataLaporan[$x]['nama']   = 'A. Belanja BLU';
                  $dataLaporan[$x]['target'] = '';
                  $dataLaporan[$x]['format'] = $format;
                  $dataLaporan[$x]['format_kode'] = $formatR;
                  $dataLaporan[$x]['format_curr'] = $formatCurrency;
                  
                  $dataPaguBasMak = $this->mDBObj->GetPaguBasMak(7);
                  $x+=1;
                  if(!empty($dataPaguBasMak)){
                     for($mak = 0 ; $mak < sizeof($dataPaguBasMak);$mak++){
                        $dataLaporan[$x]['kode'] ='';//$dataPaguBasMak[$mak]['mak_kode'];
                        $dataLaporan[$x]['nama'] = ($mak+1).' - '.$dataPaguBasMak[$mak]['mak_nama'];
                        $dataLaporan[$x]['target'] = 0;
                        $dataLaporan[$x]['format'] =$format;
                        $dataLaporan[$x]['format_kode'] =$formatR;
                        $dataLaporan[$x]['format_curr'] =$formatCurrency;
                        $x++;
                     }
                   }          
                  
                  $dataLaporan[$x]['kode'] = '';
                  $dataLaporan[$x]['nama'] = 'B. Belanja RM/PHLN/PHDN';
                  $dataLaporan[$x]['target'] ='';
                  $dataLaporan[$x]['format'] =$format;
                  $dataLaporan[$x]['format_kode'] =$formatR;
                  $dataLaporan[$x]['format_curr'] =$formatCurrency;
                  
                  $dataPaguBasMak = $this->mDBObj->GetPaguBasMak(0);
                  $x+=1;
                  if(!empty($dataPaguBasMak)){
                     for($mak = 0 ; $mak < sizeof($dataPaguBasMak);$mak++){
                        $dataLaporan[$x]['kode'] ='';//$dataPaguBasMak[$mak]['mak_kode'];
                        $dataLaporan[$x]['nama'] = ($mak+1).' - '.$dataPaguBasMak[$mak]['mak_nama'];
                        $dataLaporan[$x]['target'] = 0;
                        $dataLaporan[$x]['class_name'] ='';
                        $dataLaporan[$x]['format'] =$format;
                        $dataLaporan[$x]['format_kode'] =$formatR;
                        $dataLaporan[$x]['format_curr'] =$formatCurrency;
                        $x++;
                     }
                   }
                   
                  $i++;
            } elseif($unitId != $listDataItem[$i]['unit_id']){
               $unitId = $listDataItem[$i]['unit_id'];
               
               $programId ='';
               $kegiatanId ='';
               $subKegiatanId ='';
               $dataLaporan[$x]['kode'] = (++$no);
               $dataLaporan[$x]['nama'] = $listDataItem[$i]['unit_nama'];
               $dataLaporan[$x]['target'] = '';
               $dataLaporan[$x]['format'] =$formatProgram;
               $dataLaporan[$x]['format_kode'] =$formatRProgram;
               $dataLaporan[$x]['format_curr'] =$formatCurrencyProgram;
            } elseif($programId != $listDataItem[$i]['program_id']){
               $programId = $listDataItem[$i]['program_id'];
               $kegiatanId ='';
               $subKegiatanId ='';
               $dataLaporan[$x]['kode'] =$listDataItem[$i]['program_kode'];
               $dataLaporan[$x]['nama'] = $listDataItem[$i]['program_nama'];
               $dataLaporan[$x]['target'] = '';
               $dataLaporan[$x]['format'] =$formatProgram;
               $dataLaporan[$x]['format_kode'] =$formatRProgram;
               $dataLaporan[$x]['format_curr'] =$formatCurrencyProgram;
            } elseif($kegiatanId != $listDataItem[$i]['kegiatan_id']){
               $kegiatanId  = $listDataItem[$i]['kegiatan_id'];
               $subKegiatanId ='';
               $dataLaporan[$x]['kode'] = $listDataItem[$i]['kegiatan_kode'];
               $dataLaporan[$x]['nama'] = $listDataItem[$i]['kegiatan_nama'];
               $dataLaporan[$x]['target'] = '';
               $dataLaporan[$x]['format'] =$formatKegiatan;
               $dataLaporan[$x]['format_kode'] =$formatRKegiatan;
               $dataLaporan[$x]['format_curr'] =$formatCurrencyKegiatan;
            } elseif($subKegiatanId != $listDataItem[$i]['sub_kegiatan_id']){
               $subKegiatanId  = $listDataItem[$i]['sub_kegiatan_id'];
               $dataLaporan[$x]['kode'] = $listDataItem[$i]['sub_kegiatan_kode'];
               $dataLaporan[$x]['nama'] = $listDataItem[$i]['sub_kegiatan_nama'];
               $dataLaporan[$x]['target'] = '';
               $dataLaporan[$x]['format'] =$formatSubKegiatan;
               $dataLaporan[$x]['format_kode'] =$formatRSubKegiatan;
               $dataLaporan[$x]['format_curr'] =$formatCurrencySubKegiatan;
            }
            
            $x++;
         }
      
         for($k = 0;$k < sizeof($dataLaporan);$k++){
            $this->mWorksheets['Data']->write_string($num, 0, $dataLaporan[$k]['kode'], $dataLaporan[$k]['format_kode']);
            $this->mWorksheets['Data']->write_string($num, 1, $dataLaporan[$k]['nama'], $dataLaporan[$k]['format']);
            $this->mWorksheets['Data']->write_string($num, 2, 0, $dataLaporan[$k]['format_curr']);
            $this->mWorksheets['Data']->write_string($num, 3, 0, $dataLaporan[$k]['format_curr']);
            $this->mWorksheets['Data']->write_string($num, 4, 0, $dataLaporan[$k]['format_curr']);
            $this->mWorksheets['Data']->write_string($num, 5, 0, $dataLaporan[$k]['format_curr']);
            $this->mWorksheets['Data']->write_string($num, 6, 0, $dataLaporan[$k]['format_curr']);
            $this->mWorksheets['Data']->write_string($num, 7, 0, $dataLaporan[$k]['format_curr']);
            $this->mWorksheets['Data']->write_string($num, 8, 0, $dataLaporan[$k]['format_curr']);
            $this->mWorksheets['Data']->write_string($num, 9, 0, $dataLaporan[$k]['format_curr']);
            $this->mWorksheets['Data']->write_string($num, 10, 0, $dataLaporan[$k]['format_curr']);
            $this->mWorksheets['Data']->write_string($num, 11, 0, $dataLaporan[$k]['format_curr']);
            $num++;
         }
         
         
         
         $this->mWorksheets['Data']->write($num, 0, '',$formatHeader);
         $this->mWorksheets['Data']->write($num, 1, 'TOTAL BELANJA',$formatHeader);
         $this->mWorksheets['Data']->write($num, 2, 0,$formatCurrencyProgram);
         $this->mWorksheets['Data']->write($num, 3, 0,$formatCurrencyProgram);
         $this->mWorksheets['Data']->write($num, 4, 0,$formatCurrencyProgram);
         $this->mWorksheets['Data']->write($num, 5, 0,$formatCurrencyProgram);
         $this->mWorksheets['Data']->write($num, 6, 0,$formatCurrencyProgram);
         $this->mWorksheets['Data']->write($num, 7, 0,$formatCurrencyProgram);
         $this->mWorksheets['Data']->write($num, 8, 0,$formatCurrencyProgram);
         $this->mWorksheets['Data']->write($num, 9, 0,$formatCurrencyProgram);
         $this->mWorksheets['Data']->write($num, 10, 0,$formatCurrencyProgram);
         $this->mWorksheets['Data']->write($num, 11, 0,$formatCurrencyProgram);
      }     
      
   
   }
}
?>