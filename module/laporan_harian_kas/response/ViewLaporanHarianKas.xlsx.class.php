<?php
/**
* ================= doc ====================
* FILENAME     : ViewLaporanHarianKas.xlsx.class.php
* @package     : ViewLaporanHarianKas
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-05-26
* @Modified    : 2015-05-26
* @Analysts    : Dyah Fajar N
* @contact     : eko.susilo@gamatechno.com
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/laporan_harian_kas/business/LaporanHarianKas.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') .
'main/function/date.php';


class ViewLaporanHarianKas extends XlsxResponse
{
   # Internal Variables
   public $Excel;

   function ProcessRequest()
   {
      $get_date      = getdate();
      $curr_mon      = (int)$get_date['mon'];
      $curr_day      = (int)$get_date['mday'];
      $curr_year     = (int)$get_date['year'];
      $tgl_proses    = gmmktime(0,0,0, $curr_mon, $curr_day, $curr_year);
      $tanggalCetak  =  IndonesianDate(date('Y-m-d', time()),'YYYY-MM-DD');
      $mObj          = new LaporanHarianKas();
      $request_data['start_date']   = date('Y-m-d', strtotime(Dispatcher::Instance()->Decrypt($mObj->_GET['start_date'])));
      $request_data['end_date']     = date('Y-m-d', strtotime(Dispatcher::Instance()->Decrypt($mObj->_GET['end_date'])));
      $data_list     = $mObj->getDataLaporanKasExport($request_data);
      $saldoAwal     = $mObj->getSaldoAwal($request_data['start_date'] );
      # set writer for XlsxResponse
      # default Excel5 for .xls extension
      # option Excel2007 for .xlsx extension
      # $this->SetWriter('Excel2007');
      $this->SetFileName('laporan_harian_kas.xls');

      # Write your code here
      # Get active sheet
      # Document Setting
      $sheet   = $this->Excel->getActiveSheet(0);
      $sheet->setShowGridlines(false); # false: Hide the gridlines; true: show the gridlines
      # set worksheet name
      $sheet->setTitle('laporan_harian_kas');
      # set font default
      $sheet->getDefaultStyle()->getFont()->setName('Calibri')->setSize('12');
      # setting paging
      # options ORIENTATION_DEFAULT : default; ORIENTATION_LANDSCAPE: lanscape; ORIENTATION_PORTRAIT: Potrait
      $sheet->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_PORTRAIT);
      $sheet->getPageSetup()->setFitToWidth(1); # Set 0 or 1
      $sheet->getPageSetup()->setFitToHeight(0); # Set 0 or 1
      $sheet->getPageSetup()->setHorizontalCentered(true); # true or false
      $sheet->getPageSetup()->setVerticalCentered(false); # true or false
      # Set The papersize
      $sheet->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
      $sheet->getPageMargins()->setBottom(1.95);
      # /Document Setting

      $sheet->getHeaderFooter()->setOddHeader('&R Hal. &P ');

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
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
         )
      );

      $borderTableStyledArray = array(
         'borders' => array(
            'allborders' => array(
               'style' => PHPExcel_Style_Border::BORDER_THIN,
               'color' => array('argb' => 'ff000000')
            )
         ), 'alignment' => array(
            'wrap' => true,
            'shrinkToFit' => true,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP
         )
      );

      $sheet->getColumnDimension('A')->setWidth(5);
      $sheet->getColumnDimension('B')->setWidth(18);
      $sheet->getColumnDimension('C')->setWidth(15);
      $sheet->getColumnDimension('D')->setWidth(60);
      $sheet->getColumnDimension('E')->setWidth(8);
      $sheet->getColumnDimension('F')->setWidth(18);
      $sheet->getColumnDimension('G')->setWidth(18);
      $sheet->getColumnDimension('H')->setWidth(20);

      $sheet->mergeCells('A1:H1');
      $sheet->mergeCells('A2:H2');
      $sheet->mergeCells('A3:H3');
      $sheet->mergeCells('A4:H4');
      $sheet->mergeCells('A5:H5');
      $sheet->mergeCells('A6:H6');

      $sheet->setCellValue('A1', 'Tgl. '. $tanggalCetak);
      // $sheet->setCellValue('A1', 'SISKAP11');
      $sheet->setCellValue('A2',  GTFWConfiguration::GetValue('organization', 'company_name'));
      $sheet->setCellValue('A3', 'STATUS HARIAN KAS');
      $sheet->setCellValue('A4', 'LAPORAN PERINCIAN TRANSAKSI');
      // $sheet->getStyle('A2')->applyFromArray(array(
      //    'font' => array(
      //       'size' => 12
      //    ), 'alignment' => array(
      //       'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
      //       'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
      //    )
      // ));
      $sheet->getStyle('A2:H6')->applyFromArray(array(
         'font' => array(
            'size' => 12
         ), 'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
         )
      ));

      $sheet->setCellValue('A6', 'Tanggal : '.$mObj->_dateToIndo($request_data['start_date']).' s/d '.$mObj->_dateToIndo($request_data['end_date']));

      $sheet->setCellValue('A7', 'NO');
      $sheet->setCellValue('B7', 'TANGGAL');
      $sheet->setCellValue('C7', 'NO. BPKK');
      $sheet->setCellValue('D7', 'PENJELASAN');
      $sheet->setCellValue('E7', 'NO_REK');
      $sheet->setCellValue('F7', 'DEBET');
      $sheet->setCellValue('G7', 'KREDIT');
      $sheet->setCellValue('H7', 'JUMLAH');
      $sheet->getStyle('A7:H7')->applyFromArray($styledTableHeaderArray);
      $sheet->mergeCells('B8:G8');

      $row     = 9;
      if(empty($data_list)){
         $sheet->setCellValue('A'.$row, GTFWConfiguration::GetValue('language', 'data_kosong'));
         $sheet->mergeCells('A'.$row.':H'.$row);
      }else{
            $sheet->setCellValueExplicit('B'.($row-1), 'SALDO AWAL', PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->getStyle('B'.($row-1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            
               if($saldoAwal < 0){
                  $saldo_awal  = '('.$saldoAwal.')';
               }else{
                  $saldo_awal  = $saldoAwal;
               }
            
            $sheet->setCellValueExplicit('H'.($row-1), $saldo_awal, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->getStyle('H'.($row-1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
            $sheet->getStyle('H'.($row-1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('B'.($row-1).':H'.($row-1))->getFont()->setBold(true);

            $start      = 1;
         foreach ($data_list as $list) {

            if($list['tt_id'] == '1' && $list['trans_jenis'] == '9'){
               $keterangan       = $list['uraian_fpa'];
            }else{
               $keterangan       = $list['trans_catatan'];
            }

            $list['nomor']  = $start;

            if($tanggal == $list['trans_tanggal']){
               $list['tanggal']  = '';
            }else{
               $tanggal       = $list['trans_tanggal'];
               $sheet->setCellValueExplicit('B'.$row, strtoupper($mObj->_dateToIndo($tanggal)), PHPExcel_Cell_DataType::TYPE_STRING);
            }

            $sheet->setCellValueExplicit('A'.$row, $list['nomor'], PHPExcel_Cell_DataType::TYPE_STRING);
            // $sheet->setCellValueExplicit('B'.$row, strtoupper($mObj->_dateToIndo($list['trans_tanggal'])), PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('C'.$row, $list['nomor_referensi'], PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('D'.$row, strtoupper($keterangan), PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('E'.$row, $list[''], PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('F'.$row, $list['nominal_debet'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('G'.$row, $list['nominal_kredit'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->getStyle('F'.$row.':H'.$row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
            $sheet->getStyle('A'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('B'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('C'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $row++;

            $start++;
         }

         $sheet->setCellValue('A'.$row, '');
         $sheet->setCellValueExplicit('F'.$row, '=SUM(F9:F'.($row-1).')', PHPExcel_Cell_DataType::TYPE_FORMULA);
         $sheet->setCellValueExplicit('G'.$row, '=SUM(G9:G'.($row-1).')', PHPExcel_Cell_DataType::TYPE_FORMULA);
         $sheet->setCellValueExplicit('H'.$row, '=SUM(F'.$row.'-G'.$row.')', PHPExcel_Cell_DataType::TYPE_FORMULA);
         $sheet->setCellValue('A'.($row+1), 'SALDO AKHIR');
            $sheet->getStyle('A'.$row.':A'.($row+1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
         $sheet->setCellValue('H'.($row+1), '=SUM(H8+H'.$row.')');
         $sheet->getStyle('F'.$row.':H'.($row+1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
         $sheet->getStyle('A8:H'.($row+1))->applyFromArray($borderTableStyledArray);
         $sheet->getStyle('A'.$row.':H'.($row+1))->getFont()->setBold(true);
         $sheet->mergeCells('A'.$row.':E'.$row);
         $sheet->mergeCells('A'.($row+1).':G'.($row+1));
         
         $sheet->setCellValue('B'.($row+4), strtoupper(GTFWConfiguration::GetValue('language', 'diketahui_oleh')). ',');
         $sheet->setCellValue('D'.($row+4), strtoupper(GTFWConfiguration::GetValue('language', 'disetujui_oleh')). ',');
         $sheet->setCellValue('F'.($row+3), GTFWConfiguration::GetValue('organization', 'city').', '.$tanggalCetak);
         $sheet->setCellValue('F'.($row+4), strtoupper(GTFWConfiguration::GetValue('language', 'dibuat_oleh')). ',');
         $sheet->setCellValue('B'.($row+8), '-----------------------------');
         $sheet->setCellValue('D'.($row+8), '-----------------------------');
         $sheet->setCellValue('F'.($row+8), '-----------------------------');
         $sheet->setCellValue('B'.($row+9), GTFWConfiguration::GetValue('organization', 'kabiro_keu'));
         $sheet->setCellValue('D'.($row+9), GTFWConfiguration::GetValue('organization', 'kabag_keu'));
         $sheet->setCellValue('F'.($row+9), GTFWConfiguration::GetValue('organization', 'kasir'));
         $sheet->mergeCells('B'.($row+4).':C'.($row+4));
         $sheet->mergeCells('D'.($row+4).':E'.($row+4));
         $sheet->mergeCells('F'.($row+3).':G'.($row+3));
         $sheet->mergeCells('F'.($row+4).':G'.($row+4));
         $sheet->mergeCells('B'.($row+8).':C'.($row+8));
         $sheet->mergeCells('D'.($row+8).':E'.($row+8));
         $sheet->mergeCells('F'.($row+8).':G'.($row+8));
         $sheet->mergeCells('B'.($row+9).':C'.($row+9));
         $sheet->mergeCells('D'.($row+9).':E'.($row+9));
         $sheet->mergeCells('F'.($row+9).':G'.($row+9));
            $sheet->getStyle('B'.($row+3).':G'.($row+9))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
      }
      # Save Excel document to local hard disk
      $this->Save();
   }
}
?>