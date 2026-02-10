<?php



/**
* ================= doc ====================
* FILENAME     : ViewExcelLaporanLppa.xlsx.class.php
* @package     : ViewExcelLaporanLppa
* scope        : PUBLIC
* @Author      : noor hadi
* @Created     : 2014-07-14
* @Modified    : 2014-07-14
* @Analysts    : Dyah Fajar N
* @contact     : noor.hadi@gamatechno.com
* @copyright   : Copyright (c) 2015 Gamatechno
* ================= doc ====================
*/

require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/lppa/business/Lppa.class.php';

require_once GTFWConfiguration::GetValue('application', 'docroot') .
'main/function/date.php';

class ViewExcelLaporanLppa extends XlsxResponse
{
   # Internal Variables
   public $Excel;

   function ProcessRequest()
   {
      $mObj       = new Lppa();
      $userId     = $mObj->getUserId();
      
      $mObj                = new Lppa;
      $requestData         = array();
      $requestData['data_id'] = Dispatcher::Instance()->Decrypt($mObj->_GET['dataId']);
   
      $dataList            = $mObj->GetLaporanLppa($requestData['data_id']);
      # set writer for XlsxResponse
      # default Excel5 for .xls extension
      # option Excel2007 for .xlsx extension
      # $this->SetWriter('Excel2007');
      $this->SetFileName('laporan_lppa.xls');

      # Write your code here
      # Get active sheet
      # Document Setting
      $sheet   = $this->Excel->getActiveSheet(0);
      $sheet->setShowGridlines(false); # false: Hide the gridlines; true: show the gridlines
      # set worksheet name
      $sheet->setTitle('laporan');
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
      $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(7,8);
      //$sheet->freezePane('A9');
      # /Document Setting

      $headerStyle         = array(
         'font' => array(
            'size' => 13,
            'bold' => true
         ), 'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
         )
      );

      $boldStyle         = array(
         'font' => array(
            'bold' => true
         ), 'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
         )
      );

      $borderTableStyledArray = array(
         'borders' => array(
            'allborders' => array(
               'style' => PHPExcel_Style_Border::BORDER_THIN,
               'color' => array('argb' => 'ff000000')
            )
         ), 
         'alignment' => array(
            
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            'wrap' => true,
            'shrinkToFit' => true
         )
      );

      $borderOutsideTableStyledArray = array(
         'borders' => array(
            'outline' => array(
               'style' => PHPExcel_Style_Border::BORDER_THIN,
               'color' => array('argb' => 'ff000000')
            )
         ), 
         'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            'wrap' => true,
            'shrinkToFit' => true
         )
      );
            
      $styledTableHeaderArray = array(
         'borders' => array(
            'allborders' => array(
               'style' => PHPExcel_Style_Border::BORDER_THIN,
               'color' => array('argb' => 'ff000000')
            )
         ),
         'fill' => array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'startcolor' => array(
               'argb' => 'ffcccccc'
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
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            'wrap' => true,
            'shrinkToFit' => true
         )
      );

      $kodeRekLabel       = GTFWConfiguration::GetValue('language', 'kode_rek');
      $namaRekeningLabel     = GTFWConfiguration::GetValue('language', 'nama_rekening');
      $rincianLabel     = GTFWConfiguration::GetValue('language', 'rincian');
      $jumlahRpLabel    = GTFWConfiguration::GetValue('language', 'jumlah_rp');
      
      $penerimaanLabel    = GTFWConfiguration::GetValue('language', 'penerimaan_fpa');
      $pengeluaranLabel    = GTFWConfiguration::GetValue('language', 'pengeluaran');

      
      
      //$sheet->getRowDimension(1)->setRowHeight(20);
      $sheet->setCellValue('A1',GTFWConfiguration::GetValue('organization', 'company_name'));
      $sheet->mergeCells('A1:H1');
      $sheet->getStyle('A1:H1')->applyFromArray($headerStyle);
      $sheet->setCellValue('A2', 'Laporan Pertanggung Jawaban Penggunaan Anggaran (LPPA)');
      $sheet->mergeCells('A2:H2');
      $sheet->getStyle('A2:H2')->applyFromArray($headerStyle);     
     
      $sheet->setCellValue('A5', 'Divisi :');
      $sheet->setCellValue('A6', 'No. Bukti CP/BP :');
      $sheet->mergeCells('A5:D5');
      $sheet->mergeCells('A6:D6');
       
      $sheet->setCellValue('E5', 'Tanggal : '. IndonesianDate($dataList[0]['tgl_lppa'], 'yyyy-mm-dd'));
      $sheet->mergeCells('E5:H5');
      
      $sheet->mergeCells('A7:D7');
      $sheet->mergeCells('E7:H7');

      $sheet->getColumnDimension('A')->setWidth(12);
      $sheet->getColumnDimension('B')->setWidth(20);
      $sheet->getColumnDimension('C')->setWidth(35);
      $sheet->getColumnDimension('D')->setWidth(20);
      
      $sheet->getColumnDimension('E')->setWidth(15);
      $sheet->getColumnDimension('F')->setWidth(20);
      $sheet->getColumnDimension('G')->setWidth(35);
      $sheet->getColumnDimension('H')->setWidth(20);
      
      $sheet->setCellValue('A7', $penerimaanLabel);
      $sheet->setCellValue('E7', $pengeluaranLabel);
      
      $sheet->setCellValue('A8', $kodeRekLabel);
      $sheet->setCellValue('B8', $namaRekeningLabel);
      $sheet->setCellValue('C8', $rincianLabel);
      $sheet->setCellValue('D8', $jumlahRpLabel);

      $sheet->setCellValue('E8', $kodeRekLabel);
      $sheet->setCellValue('F8', $namaRekeningLabel);
      $sheet->setCellValue('G8', $rincianLabel);
      $sheet->setCellValue('H8', $jumlahRpLabel);

      $sheet->getStyle('A1:H6')->applyFromArray($borderOutsideTableStyledArray);
      $sheet->getStyle('A7:H8')->applyFromArray($styledTableHeaderArray);
      
      $row     = 9;
      if (empty($dataList)) {
         $sheet->setCellValue('A'.$row, 'DATA KOSONG');
         $sheet->mergeCells('A'.$row.':H'.$row);
         $sheet->getStyle('A'.$row.':H'.$row)->applyFromArray($borderTableStyledArray);
         $sheet->getStyle('A'.$row.':H'.$row)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
         $sheet->getRowDimension($row)->setRowHeight(16);
      } else {

     //print_r($dataList);
         $total_fpa = 0;
         $total_fpa_lppa = 0;
         $rowAwal = $row;
         foreach ($dataList as $key => $list) {
            if($list['nominal_lppa'] != '0'){
            if($key > 0 ){                
                if($dataList[$key]['kode_akun'] == $dataList[$key - 1]['kode_akun']){
                    $list['kode_akun'] ='';
                    $list['nama_akun'] ='';
                }
            }
            $total_fpa += $list['nominal_approve'];
            $total_fpa_lppa += $list['nominal_lppa'];
           
            $list['rincian'] = $list['detail_pengeluaran'];
            $sheet->setCellValue('A'.$row, $list['kode_akun']);
            $sheet->setCellValue('B'.$row, $list['nama_akun']);
            $sheet->setCellValue('C'.$row, $list['rincian'].' '.$list['deskripsi']);
            $sheet->setCellValue('D'.$row, $list['nominal_approve']);
            $sheet->setCellValue('E'.$row, $list['kode_akun']);
            $sheet->setCellValue('F'.$row, $list['nama_akun']);
            $sheet->setCellValue('G'.$row, $list['rincian'].' '.$list['komponen_eskripsi']);
            $sheet->setCellValue('H'.$row, $list['nominal_lppa']);         
            
            $row++;
         }
         }
         
         $rowAkhir = $row - 1;
         $sheet->mergeCells('A'.$rowAwal.':A'.$rowAkhir);
         $sheet->mergeCells('B'.$rowAwal.':B'.$rowAkhir);
         $sheet->mergeCells('E'.$rowAwal.':E'.$rowAkhir);
         $sheet->mergeCells('F'.$rowAwal.':F'.$rowAkhir);
         
         $sheet->mergeCells('A'.$row.':C'.$row);
         $sheet->mergeCells('E'.$row.':G'.$row);
         $sheet->setCellValue('A'.$row, 'Total');
         $sheet->setCellValue('E'.$row, 'Total');
         $sheet->setCellValue('D'.$row, '=SUM(D'.$rowAwal.':'.'D'.$rowAkhir.')');
         $sheet->setCellValue('H'.$row, '=SUM(H'.$rowAwal.':'.'H'.$rowAkhir.')');
         
         $sheet->getStyle('A'.$row.':H'.$row)->applyFromArray($boldStyle);     
         $sheet->getStyle('D'.$rowAwal.':'.'D'.$row)->getNumberFormat()->setFormatCode('#,##0_);[RED](#,##0)');
         $sheet->getStyle('H'.$rowAwal.':'.'H'.$row)->getNumberFormat()->setFormatCode('#,##0_);[RED](#,##0)');
         
         $sheet->getStyle('D'.$rowAwal.':'.'D'.$row)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
         $sheet->getStyle('H'.$rowAwal.':'.'H'.$row)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
         
         $row += 1;
         $sheet->setCellValue('A'.$row, 'Saldo');
         
         $sheet->mergeCells('B'.$row.':H'.$row);
         $sheet->getStyle('A'.$row.':H'.$row)->applyFromArray($boldStyle);     
         $sheet->setCellValue('B'.$row, '=(D'.($row -1).' - H'.($row -1).')');
         $sheet->getStyle('B'.$row.':'.'B'.$row)->getNumberFormat()->setFormatCode('#,##0_);[RED](#,##0)');
         $row += 1;
         $textArea = 'Saldo disetor ke rekening ABFII Perbanas Bank Bukopin A/C No. 1020447016, terlampir bukti setor asli.';
         $sheet->setCellValue('A'.$row,$textArea);
         $sheet->mergeCells('A'.$row.':H'.$row);
         $row += 1;
         $sheet->setCellValue('A'.$row,' Jakarta,'.  IndonesianDate(date('Y-m-d', time()), 'yyyy-mm-dd'));    
         $sheet->mergeCells('A'.$row.':H'.$row);        
         $sheet->getStyle('A9:H'.($row))->applyFromArray($borderTableStyledArray);
         $rowA = $row;
         $row+= 2;         
         $sheet->setCellValue('A'.$row,'Penanggung Jawab Unit Kerja');
         $sheet->setCellValue('E'.$row,'Mengetahui');
         $sheet->mergeCells('A'.$row.':D'.$row);
         $sheet->mergeCells('E'.$row.':H'.$row);
         $row += 1;
         $sheet->setCellValue('E'.$row,'Ka. Biro');
         $sheet->mergeCells('E'.$row.':H'.$row);
         $row += 5;
         $sheet->setCellValue('A'.$row,$dataList[0]['penanggung_jawab']);
         $sheet->setCellValue('E'.$row,$dataList[0]['mengetahui']);
         $sheet->mergeCells('A'.$row.':D'.$row);
         $sheet->mergeCells('E'.$row.':H'.$row);
         $sheet->getStyle('A'. $rowA .':H'.($row))->applyFromArray($borderOutsideTableStyledArray);
         
         
      }
      # Save Excel document to local hard disk
      $this->Save();
   }
}
?>