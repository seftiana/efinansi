<?php

/**
 * class ViewExcelLapProgramKegiatan
 * @package lap_program_kegiatan
 * @subpackage response
 * @todo untuk men-generate ke laporan format excel
 * @since 28 mei 2012
 * @copyright 2012 Gamatechno Indonesia
 * @author noor hadi <noor.hadi@gamatechno.com>
 */
 

require_once GTFWConfiguration::GetValue('application', 'docroot') . 
    'module/lap_program_kegiatan/business/LapProgramKegiatan.class.php';

class ViewExcelLapProgramKegiatan extends XlsResponse 
{
    public $mWorksheets = array('Data');
   
    public function GetFileName() 
    {
        // name it whatever you want
        //$label =str_replace(' ','_',$this->L('laporan_realisasi_anggaran_program_pengeluaran'));    
        // name it whatever you want
        //return $label.'.xls';//LapRealisasiAnggaranProgram.xls';
        return 'LaporanProgramKegiatan.xls';
    }
   
    public function L($indexLangName = '')
    {
   		$lang = GTFWConfiguration::GetValue('language',$indexLangName);
   		if(!empty($lang)){
   			return $lang;	
   		}
   		return '';
    }
   
    public function ProcessRequest() 
    {
        $_POST = $_POST->AsArray();
        $unit_label = $_POST['unit_nama'];    
        $periode_nama =  $_POST['th_anggar_nama'];
        //buat array
        $x=0;
        for($i = 0;$i < sizeof($_POST['pk']['id']); $i++){
                
            if(($_POST['pk']['tipe'][$i] ==  4) &&
                ($_POST['status_expand_'.$_POST['parent_'.$_POST['pk']['id'][$i]]] == 1) && 
                ($_POST['status_expand_'.$_POST['up_parent_'.$_POST['pk']['id'][$i]]] == 1) && 
                ($_POST['status_expand_'.$_POST['top_parent_'.$_POST['pk']['id'][$i]]] == 1)){
                    
                $b =   $_POST['biaya_'. $_POST['pk']['id'][$i]];
                    
                $data_program_kegiatan[$x]['id'] = $_POST['pk']['id'][$i];
                $data_program_kegiatan[$x]['kode'] = $_POST['pk']['kode'][$i];
                $data_program_kegiatan[$x]['nama'] = $_POST['pk']['nama'][$i];
                $data_program_kegiatan[$x]['unit_nama'] =$_POST['unit_nama_'.$_POST['pk']['id'][$i]];
                $data_program_kegiatan[$x]['blt'] =($b == 11) ? $_POST['komponen_'.$_POST['pk']['id'][$i]]:'0';
                $data_program_kegiatan[$x]['bltt'] =($b == 10) ? $_POST['komponen_'.$_POST['pk']['id'][$i]]:'0';
                $data_program_kegiatan[$x]['btlt'] =($b == 01) ? $_POST['komponen_'.$_POST['pk']['id'][$i]]:'0';
                $data_program_kegiatan[$x]['btltt'] =($b == 00) ? $_POST['komponen_'.$_POST['pk']['id'][$i]]:'0';
                $data_program_kegiatan[$x]['kuantitas'] =$_POST['kuantitas_'.$_POST['pk']['id'][$i]];
                $data_program_kegiatan[$x]['biaya_nilai_satuan'] = 
                                                     $_POST['biaya_nilai_satuan_'.$_POST['pk']['id'][$i]];
                $data_program_kegiatan[$x]['jumlah'] = $_POST['komponen_'.$_POST['pk']['id'][$i]];
                $data_program_kegiatan[$x]['tipe'] =$_POST['pk']['tipe'][$i];  
                 
            }elseif(($_POST['pk']['tipe'][$i] ==  3) &&
                        ($_POST['status_expand_'.$_POST['parent_'.$_POST['pk']['id'][$i]]] == 1) && 
                        ($_POST['status_expand_'.$_POST['up_parent_'.$_POST['pk']['id'][$i]]] == 1)){
                            
                $data_program_kegiatan[$x]['id'] =$_POST['pk']['id'][$i];
                $data_program_kegiatan[$x]['kode'] = $_POST['pk']['kode'][$i];
                $data_program_kegiatan[$x]['nama'] = $_POST['pk']['nama'][$i];
                $data_program_kegiatan[$x]['unit_nama'] =$_POST['unit_nama_'.$_POST['pk']['id'][$i]];
                $data_program_kegiatan[$x]['blt'] = $_POST['total_komp_blt_'.$_POST['pk']['id'][$i]];
                $data_program_kegiatan[$x]['bltt'] = $_POST['total_komp_bltt_'.$_POST['pk']['id'][$i]];
                $data_program_kegiatan[$x]['btlt'] = $_POST['total_komp_btlt_'.$_POST['pk']['id'][$i]];
                $data_program_kegiatan[$x]['btltt'] = $_POST['total_komp_btltt_'.$_POST['pk']['id'][$i]];
                $data_program_kegiatan[$x]['kuantitas'] = '';
                $data_program_kegiatan[$x]['biaya_nilai_satuan'] = '';
                $data_program_kegiatan[$x]['jumlah'] = '';                    
                $data_program_kegiatan[$x]['tipe'] =$_POST['pk']['tipe'][$i];                            
            }elseif(($_POST['pk']['tipe'][$i] ==  2) &&
                        ($_POST['status_expand_'.$_POST['parent_'.$_POST['pk']['id'][$i]]] == 1)){
                $data_program_kegiatan[$x]['id'] =$_POST['pk']['id'][$i];
                $data_program_kegiatan[$x]['kode'] = $_POST['pk']['kode'][$i];
                $data_program_kegiatan[$x]['nama'] = $_POST['pk']['nama'][$i];
                $data_program_kegiatan[$x]['unit_nama']='';                                
                $data_program_kegiatan[$x]['blt'] = $_POST['total_komp_blt_'.$_POST['pk']['id'][$i]];
                $data_program_kegiatan[$x]['bltt'] = $_POST['total_komp_bltt_'.$_POST['pk']['id'][$i]];
                $data_program_kegiatan[$x]['btlt'] = $_POST['total_komp_btlt_'.$_POST['pk']['id'][$i]];
                $data_program_kegiatan[$x]['btltt'] = $_POST['total_komp_btltt_'.$_POST['pk']['id'][$i]];
                $data_program_kegiatan[$x]['kuantitas'] = '';
                $data_program_kegiatan[$x]['biaya_nilai_satuan'] = '';
                $data_program_kegiatan[$x]['jumlah'] = '';
                $data_program_kegiatan[$x]['tipe'] =$_POST['pk']['tipe'][$i];
            }elseif($_POST['pk']['tipe'][$i] ==  1){
                $data_program_kegiatan[$x]['id'] =$_POST['pk']['id'][$i];
                $data_program_kegiatan[$x]['kode'] = $_POST['pk']['kode'][$i];
                $data_program_kegiatan[$x]['nama'] = $_POST['pk']['nama'][$i];
                $data_program_kegiatan[$x]['unit_nama']='';                                
                $data_program_kegiatan[$x]['blt'] = $_POST['total_komp_blt_'.$_POST['pk']['id'][$i]];
                $data_program_kegiatan[$x]['bltt'] = $_POST['total_komp_bltt_'.$_POST['pk']['id'][$i]];
                $data_program_kegiatan[$x]['btlt'] = $_POST['total_komp_btlt_'.$_POST['pk']['id'][$i]];
                $data_program_kegiatan[$x]['btltt'] = $_POST['total_komp_btltt_'.$_POST['pk']['id'][$i]];
                $data_program_kegiatan[$x]['kuantitas'] = '';
                $data_program_kegiatan[$x]['biaya_nilai_satuan'] = '';
                $data_program_kegiatan[$x]['jumlah'] = '';
                $data_program_kegiatan[$x]['tipe'] =$_POST['pk']['tipe'][$i];
             }else{
                 continue;
             }
             /**
              * untuk mengisi index x
              */
             $x++;      
        }
        
        /**
         * nilai total biaya
         */    
        $tb_lt = $_POST['tb_lt'];
        $tb_ltt = $_POST['tb_ltt'];
        $tb_tlt = $_POST['tb_tlt'];
        $tb_tltt = $_POST['tb_tltt'];

        /**
         * proses generate ke excell
         */
         
         
  		if (empty($data_program_kegiatan)) {
			$this->mWorksheets['Data']->write(0, 0, $this->L('data_kosong'));
		} else {
		    /**
             * format text
             */
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
            $formatKegiatan->set_italic();
    
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
    

            $formatCurrency = $this->mrWorkbook->add_format();
            $formatCurrency->set_border(1);
            $formatCurrency->set_align('right');
            $formatCurrency->set_align('vcenter');
            /**
             * end format
             */

		   $this->mWorksheets['Data']->write(0, 0, 'Laporan Program Kegiatan', $fTitle);
		   $this->mWorksheets['Data']->write(2, 0, 'Tahun Periode '.$periode_nama);
           $this->mWorksheets['Data']->write(3, 0, 'Unit / Sub Unit '.$unit_label);
		   
		   $num=6;
		   $this->mWorksheets['Data']->merge_cells(6,0,7,0);
		   $this->mWorksheets['Data']->merge_cells(6,1,7,1);
		   $this->mWorksheets['Data']->merge_cells(6,2,7,2);
		   $this->mWorksheets['Data']->merge_cells(6,3,7,3);
           $this->mWorksheets['Data']->merge_cells(6,4,6,7);
		   $this->mWorksheets['Data']->merge_cells(6,8,7,8);
		   $this->mWorksheets['Data']->merge_cells(6,9,7,9);
		   $this->mWorksheets['Data']->merge_cells(6,10,7,10);
		   $this->mWorksheets['Data']->set_column(0,0,8);
           $this->mWorksheets['Data']->write($num, 0, 'No', $formatHeader);
           $this->mWorksheets['Data']->set_column(1,1,15);
           $this->mWorksheets['Data']->write($num, 1, 'Kode', $formatHeader);
           $this->mWorksheets['Data']->set_column(2,2,50);
           $this->mWorksheets['Data']->write($num, 2, 'Uraian', $formatHeader);
           $this->mWorksheets['Data']->set_column(3,3,40);
           $this->mWorksheets['Data']->write($num, 3, 'Unit / Sub Unit', $formatHeader);
		   $this->mWorksheets['Data']->write($num, 4, 'Biaya', $formatHeader);
		   $this->mWorksheets['Data']->write($num, 5, '', $formatHeader);
		   $this->mWorksheets['Data']->write($num, 6, '', $formatHeader);
		   $this->mWorksheets['Data']->write($num, 7, '', $formatHeader);;
           $this->mWorksheets['Data']->set_column(8,8,10);
		   $this->mWorksheets['Data']->write($num, 8, 'Kuantitas', $formatHeader);
           $this->mWorksheets['Data']->set_column(9,9,20);
           $this->mWorksheets['Data']->write($num, 9, 'Nilai Satuan', $formatHeader);
           $this->mWorksheets['Data']->set_column(10,10,20);
           $this->mWorksheets['Data']->write($num, 10, 'Jumlah', $formatHeader);
		   $num=7;
		   $this->mWorksheets['Data']->merge_cells(6,0,7,0);
		   $this->mWorksheets['Data']->merge_cells(6,1,7,1);
		   $this->mWorksheets['Data']->merge_cells(6,2,7,2);
		   $this->mWorksheets['Data']->merge_cells(6,3,7,3);
		   $this->mWorksheets['Data']->merge_cells(6,8,7,8);
		   $this->mWorksheets['Data']->merge_cells(6,9,7,9);           
           $this->mWorksheets['Data']->merge_cells(6,10,7,10);
		   $this->mWorksheets['Data']->write($num, 0, '', $formatHeader);
           $this->mWorksheets['Data']->write($num, 1, '', $formatHeader);
           $this->mWorksheets['Data']->write($num, 2, '', $formatHeader);
           $this->mWorksheets['Data']->write($num, 3, '', $formatHeader);
           //$this->mWorksheets['Data']->set_column(3,3,14);
           //$this->mWorksheets['Data']->write($num, 3, 'Volume', $fColKegiatan);
           $this->mWorksheets['Data']->set_column(4,4,20);
           $this->mWorksheets['Data']->merge_cells(6,4,6,7);
           $this->mWorksheets['Data']->write($num, 4, 'Langsung Tetap', $formatHeader);
           $this->mWorksheets['Data']->set_column(5,5,20);
           $this->mWorksheets['Data']->merge_cells(6,4,6,7);
		   $this->mWorksheets['Data']->write($num, 5, 'Langsung Tak Tetap', $formatHeader);
           $this->mWorksheets['Data']->set_column(6,6,20);
           $this->mWorksheets['Data']->merge_cells(6,4,6,7);
           $this->mWorksheets['Data']->write($num, 6, 'Tak Langsung Tetap', $formatHeader);
           $this->mWorksheets['Data']->set_column(7,7,20);
           $this->mWorksheets['Data']->merge_cells(6,4,6,7);
           $this->mWorksheets['Data']->write($num, 7, 'Tak Langsung Tak Tetap',$formatHeader);
           //$this->mWorksheets['Data']->set_column(8,8,30);
           $this->mWorksheets['Data']->write($num, 8, '', $formatHeader);
           $this->mWorksheets['Data']->write($num, 9, '', $formatHeader);
           $this->mWorksheets['Data']->write($num, 10, '', $formatHeader);
           
           $num = 8;
           $no = 1;
           for ($i=0; $i<sizeof($data_program_kegiatan);$i++) {
             if($data_program_kegiatan[$i]['tipe'] == 1){
                $f = $formatProgram;
                $fc = $formatCurrencyProgram;
                $data_program_kegiatan[$i]['nomor'] =$no;
                $no++;    
             }
             if($data_program_kegiatan[$i]['tipe'] == 2){
                $f = $formatKegiatan;
                $fc = $formatCurrencyKegiatan;
             }
             if($data_program_kegiatan[$i]['tipe'] == 3){
                $f = $formatSubKegiatan;
                $fc = $formatCurrencySubKegiatan;
             }
             if($data_program_kegiatan[$i]['tipe'] == 4){
                $f = $format;
                $fc = $formatCurrency;
             }
             $this->mWorksheets['Data']->write($num + $i, 0, $data_program_kegiatan[$i]['nomor'], $f);
             $this->mWorksheets['Data']->write($num + $i, 1, $data_program_kegiatan[$i]['kode'], $f);
             $this->mWorksheets['Data']->write($num + $i, 2, $data_program_kegiatan[$i]['nama'], $f);
             $this->mWorksheets['Data']->write($num + $i, 3, $data_program_kegiatan[$i]['unit_nama'], $f);
             $this->mWorksheets['Data']->write($num + $i, 4, $data_program_kegiatan[$i]['blt'], $fc);
             $this->mWorksheets['Data']->write($num + $i, 5, $data_program_kegiatan[$i]['bltt'], $fc);
             $this->mWorksheets['Data']->write($num + $i, 6, $data_program_kegiatan[$i]['btlt'], $fc);
             $this->mWorksheets['Data']->write($num + $i, 7, $data_program_kegiatan[$i]['btltt'], $fc);
             $this->mWorksheets['Data']->write($num + $i, 8, $data_program_kegiatan[$i]['kuantitas'], $f);
             $this->mWorksheets['Data']->write($num + $i, 9, $data_program_kegiatan[$i]['biaya_nilai_satuan'], $fc);
             $this->mWorksheets['Data']->write($num + $i, 10, $data_program_kegiatan[$i]['jumlah'], $fc);
           }
            
            /**
             * total biaya
             */ 
             $num = $num + $i;
             $this->mWorksheets['Data']->merge_cells($num,0,$num,3);
             $this->mWorksheets['Data']->write($num, 0, $this->L('total'), $formatHeader);
             $this->mWorksheets['Data']->merge_cells($num,0,$num,3);
             $this->mWorksheets['Data']->write($num, 1, '', $formatHeader);
             $this->mWorksheets['Data']->merge_cells($num,0,$num,3);
             $this->mWorksheets['Data']->write($num, 2, '', $formatHeader);
             $this->mWorksheets['Data']->merge_cells($num,0,$num,3);
             $this->mWorksheets['Data']->write($num, 3, '', $formatHeader);
             $this->mWorksheets['Data']->write($num, 4, $tb_lt, $formatCurrencyProgram);
             $this->mWorksheets['Data']->write($num, 5, $tb_ltt, $formatCurrencyProgram);
             $this->mWorksheets['Data']->write($num, 6, $tb_tlt, $formatCurrencyProgram);
             $this->mWorksheets['Data']->write($num, 7, $tb_tltt, $formatCurrencyProgram);
             $this->mWorksheets['Data']->merge_cells($num,8,$num,10);
             $this->mWorksheets['Data']->write($num, 8, '', $formatHeader);
             $this->mWorksheets['Data']->merge_cells($num,8,$num,10);
             $this->mWorksheets['Data']->write($num, 9, '', $formatHeader);
             $this->mWorksheets['Data']->merge_cells($num,8,$num,10);
             $this->mWorksheets['Data']->write($num, 10,'', $formatHeader);             
             
		}
        
        /**
         * end generate excell
         */        
   }
}