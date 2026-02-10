<?php
/**
* ================= doc ====================
* FILENAME     : ViewExcelLapProgramKegiatan.xlsx.class.php
* @package     : ViewExcelLapProgramKegiatan
* scope        : PUBLIC
* @Author      : noor hadi
* @Created     : 2015-06-15
* @Modified    : 2015-06-15
* @Analysts    : Dyah Fajar N
* @contact     : noor.hadi@gamatechno.com
* @copyright   : Copyright (c) 2015 Gamatechno
* ================= doc ====================
*/


require_once GTFWConfiguration::GetValue('application', 'docroot') . 
    'module/lap_program_kegiatan/business/LapProgramKegiatan.class.php';

class ViewExcelLapProgramKegiatan extends XlsxResponse
{
   # Internal Variables
   public $Excel;

   function ProcessRequest()
   {
      # set writer for XlsxResponse
      # default Excel5 for .xls extension
      # option Excel2007 for .xlsx extension
      # $this->SetWriter('Excel2007');
      $this->SetFileName('laporan_program_kegiatan_'.date('Ymd', time()).'.xls');

      # Write your code here
      # Get active sheet
      # Document Setting
      $sheet   = $this->Excel->getActiveSheet(0);
      $sheet->setShowGridlines(false); # false: Hide the gridlines; true: show the gridlines
      # set worksheet name
      $sheet->setTitle('Worksheet Name');
      # set font default
      $sheet->getDefaultStyle()->getFont()->setName('Arial')->setSize('10');
      # setting paging
      # options ORIENTATION_DEFAULT : default; ORIENTATION_LANDSCAPE: lanscape; ORIENTATION_PORTRAIT: Potrait
      $sheet->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
      $sheet->getPageSetup()->setFitToWidth(1); # Set 0 or 1
      $sheet->getPageSetup()->setFitToHeight(0); # Set 0 or 1
      $sheet->getPageSetup()->setHorizontalCentered(true); # true or false
      $sheet->getPageSetup()->setVerticalCentered(false); # true or false
      # Set The papersize
      $sheet->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
      # /Document Setting
      
      /**
       * generate data
       * 
       */
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
         
      $dataList = $data_program_kegiatan;
         
      if(empty($dataList)){
         $this->setCellValue('A1', 'DATA KOSONG');
      }else{
         // inisialisasi data
         $program          = '';
         $kegiatan         = '';
         $dataGrid         = array();
         $dataMonitoring   = array();
         $index            = 0;

         $headerStyle         = array(
            'font' => array(
               'size' => 14,
               'bold' => true
            ), 'alignment' => array(
              // 'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
               'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            )
         );

         $borderTableStyledArray = array(
            'borders' => array(
               'allborders' => array(
                  'style' => PHPExcel_Style_Border::BORDER_THIN,
                  'color' => array('argb' => '00000000')
               )
            )
         );
         $styledTableHeaderArray = array(
            'borders' => array(
               'allborders' => array(
                  'style' => PHPExcel_Style_Border::BORDER_HAIR,
                  'color' => array('argb' => 'ff000000')
               )
            ),
            'fill' => array(
               'type' => PHPExcel_Style_Fill::FILL_SOLID,
               'startcolor' => array(
                  'argb' => 'ffE6E6E6'
               )
            ),
            'font' => array(
               'bold' => true,
               'color' => array(
                  'rgb' => '000000'
               )
            ),
            'alignment' => array(
               'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
               'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            )
         );

         $styledBold = array(
            'font' => array(
               'bold' => true,
               'color' => array(
                  'rgb' => '000000'
               )
            )
         );

         $styledItalicBold = array(
            'font' => array(
               'italic' => true,
               'bold' => true,
               'color' => array(
                  'rgb' => '000000'
               )
            )
         );
                  

         $styledItalic = array(
            'font' => array(
               'italic' => true,
               'color' => array(
                  'rgb' => '000000'
               )
            )
         );
                  
         //$sheet->mergeCells('A1:I1');
         $sheet->mergeCells('A3:B3');
         $sheet->mergeCells('A4:B4');
         
         $sheet->mergeCells('A7:A8');
         $sheet->mergeCells('B7:B8');
         $sheet->mergeCells('C7:C8');
         $sheet->mergeCells('D7:D8');
         $sheet->mergeCells('E7:H7');
         $sheet->mergeCells('I7:I8');
         $sheet->mergeCells('J7:J8');
         $sheet->mergeCells('K7:K8');

         $sheet->getRowDimension(1)->setRowHeight(20);
         $sheet->getColumnDimension('A')->setWidth(8);
         $sheet->getColumnDimension('B')->setWidth(15);
         $sheet->getColumnDimension('C')->setWidth(50);
         $sheet->getColumnDimension('D')->setWidth(40);
         $sheet->getColumnDimension('E')->setWidth(20);
         $sheet->getColumnDimension('F')->setWidth(20);
         $sheet->getColumnDimension('G')->setWidth(20);
         $sheet->getColumnDimension('H')->setWidth(20);
         $sheet->getColumnDimension('I')->setWidth(12);
         $sheet->getColumnDimension('J')->setWidth(20);
         $sheet->getColumnDimension('K')->setWidth(20);

         $sheet->setCellValue('A1', 'Laporan Program Kegiatan');
         $sheet->getStyle('A1:I1')->applyFromArray($headerStyle);
         $sheet->setCellValue('A3', 'Tahun Periode');
         $sheet->setCellValueExplicit('C3', ': '.$periode_nama , PHPExcel_Cell_DataType::TYPE_STRING);
         $sheet->setCellValue('A4', GTFWConfiguration::GetValue('language', 'unit_kerja'));
         $sheet->setCellValueExplicit('C4', ': '.$unit_label, PHPExcel_Cell_DataType::TYPE_STRING);
         $sheet->getStyle('A3:C4')->getFont()->setBold(true);

         $sheet->setCellValue('A7', 'No');
         $sheet->setCellValue('B7', 'Kode');
         $sheet->setCellValue('C7', 'Uraian');
         $sheet->setCellValue('D7', 'Unit / Sub Unit');
         $sheet->setCellValue('E7', 'Biaya');
         $sheet->setCellValue('E8', 'Langsung Tetap');
         $sheet->setCellValue('F8', 'Langsung Tak Tetap');
         $sheet->setCellValue('G8', 'Tak Langsung Tetap');
         $sheet->setCellValue('H8', 'Tak Langsung Tak Tetap');
         $sheet->setCellValue('I7', 'Kuantitas');
         $sheet->setCellValue('J7', 'Nilai Satuan');
         $sheet->setCellValue('K7', 'Jumlah');
         $sheet->getStyle('A7:K8')->applyFromArray($styledTableHeaderArray);
         
         $rows = 9;
         $no = 1 ;
         foreach ($dataList as $list) {
             
             if($list['tipe'] == 1){
                $list['nomor'] =$no;
                $no++;    
             }
             
            $sheet->setCellValue('A'.$rows, $list['nomor']);
            $sheet->setCellValue('B'.$rows, $list['kode']);
            $sheet->setCellValue('C'.$rows, $list['nama']);
            $sheet->setCellValue('D'.$rows, $list['unit_nama']);
            $sheet->setCellValueExplicit('E'.$rows, $list['blt'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('F'.$rows, $list['bltt'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('G'.$rows, $list['btlt'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('H'.$rows, $list['btltt'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            

            if($list['tipe'] == 1){
                $sheet->getStyle('A'.$rows.':K'.$rows)->applyFromArray($styledBold);
            }
             
            if($list['tipe'] == 2){
                $sheet->getStyle('A'.$rows.':K'.$rows)->applyFromArray($styledItalicBold); 
            }
             
            if($list['tipe'] == 3){
                $sheet->getStyle('A'.$rows.':K'.$rows)->applyFromArray($styledItalic);
            }
            
            if($list['tipe'] == 4){
                $sheet->setCellValueExplicit('I'.$rows, $list['kuantitas'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                $sheet->setCellValueExplicit('J'.$rows, $list['biaya_nilai_satuan'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                $sheet->setCellValueExplicit('K'.$rows, $list['jumlah'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            } else {
                $sheet->setCellValueExplicit('I'.$rows, $list['kuantitas']);
                $sheet->setCellValueExplicit('J'.$rows, $list['biaya_nilai_satuan']);
                $sheet->setCellValueExplicit('K'.$rows, $list['jumlah']);
            }
            $rows++;             
         }
         
         $sheet->getStyle('A9:K'.($rows-1))->getAlignment()->setWrapText(true)->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
         $sheet->getStyle('A9:K'.($rows-1))->applyFromArray($borderTableStyledArray);
         $sheet->getStyle('E8:K'.($rows-1))->getNumberFormat()->setFormatCode('#,##0_);[RED](#,##0)');
         $sheet->getStyle('A8:A'.($rows-1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

      }

      # Save Excel document to local hard disk
      $this->Save();
   }
}
?>