<?php
/**
* ================= doc ====================
* FILENAME     : ViewLaporanHarianBank.xlsx.class.php
* @package     : ViewLaporanHarianBank
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
'module/laporan_harian_bank/business/LaporanHarianBank.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') .
'main/function/date.php';

class ViewLaporanHarianBank extends XlsxResponse
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
      $mObj          = new LaporanHarianBank();
      $request_data['start_date']   = date('Y-m-d', strtotime(Dispatcher::Instance()->Decrypt($mObj->_GET['start_date'])));
      $request_data['end_date']     = date('Y-m-d', strtotime(Dispatcher::Instance()->Decrypt($mObj->_GET['end_date'])));
      $request_data['nama_bank']    = Dispatcher::Instance()->Decrypt($mObj->_GET['nama_bank']);
      $request_data['nomor_bukti']    = Dispatcher::Instance()->Decrypt($mObj->_GET['nomor_bukti']);
      $data_list     = $mObj->getDataExcel($request_data);
      # set writer for XlsxResponse
      # default Excel5 for .xls extension
      # option Excel2007 for .xlsx extension
      # $this->SetWriter('Excel2007');
      $this->SetFileName('laporan_harian_bank.xls');

      # Write your code here
      # Get active sheet
      # Document Setting
      $sheet   = $this->Excel->getActiveSheet(0);
      $sheet->setShowGridlines(false); # false: Hide the gridlines; true: show the gridlines
      # set worksheet name
      $sheet->setTitle('laporan');
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

      $sheet->getColumnDimension('D')->setWidth(18);
      $sheet->getColumnDimension('E')->setWidth(18);
      $sheet->getColumnDimension('A')->setWidth(18);
      $sheet->getColumnDimension('B')->setWidth(18);
      $sheet->getColumnDimension('C')->setWidth(50);

      $sheet->mergeCells('A1:E1');
      $sheet->mergeCells('A2:E2');
      $sheet->mergeCells('A3:E3');
      $sheet->mergeCells('A4:E4');
      $sheet->mergeCells('A6:E6');

      $sheet->setCellValue('A1', 'Tgl. '. $tanggalCetak);
      // $sheet->setCellValue('C1', 'SISKAP11');
      $sheet->setCellValue('A2',  GTFWConfiguration::GetValue('organization', 'company_name'));
      $sheet->setCellValue('A3', 'STATUS HARIAN BANK');
      $sheet->setCellValue('A4', 'LAPORAN PERINCIAN TRANSAKSI');

      $sheet->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
      $sheet->getStyle('A2:E6')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

      $sheet->setCellValue('A6', 'Tanggal : '.$mObj->_dateToIndo($request_data['start_date']).' s/d '.$mObj->_dateToIndo($request_data['end_date']));

      $sheet->setCellValue('A7', GTFWConfiguration::GetValue('language', 'tanggal'));
      $sheet->setCellValue('B7', GTFWConfiguration::GetValue('language', 'no_bukti'));
      $sheet->setCellValue('C7', GTFWConfiguration::GetValue('language', 'uraian'));
      $sheet->setCellValue('D7', GTFWConfiguration::GetValue('language', 'nominal_rp'));
      $sheet->setCellValue('D8', GTFWConfiguration::GetValue('language', 'debet'));
      $sheet->setCellValue('E8', GTFWConfiguration::GetValue('language', 'kredit'));
      $sheet->getStyle('A7:E8')->applyFromArray($styledTableHeaderArray);
      $sheet->mergeCells('A7:A8');
      $sheet->mergeCells('B7:B8');
      $sheet->mergeCells('C7:C8');
      $sheet->mergeCells('D7:E7');
      $row     = 9;
      if(empty($data_list)){
         $sheet->setCellValue('A'.$row, GTFWConfiguration::GetValue('language', 'data_kosong'));
         $sheet->mergeCells('A'.$row.':E'.$row);
      }else{
         foreach ($data_list as $list) {
            if($tanggal == $list['tanggal']){
               $list['tanggal']  = '';
            }else{
               $tanggal       = $list['tanggal'];
               $sheet->setCellValueExplicit('A'.$row, strtoupper($mObj->_dateToIndo($tanggal)), PHPExcel_Cell_DataType::TYPE_STRING);
            }

            $list['uraian']      = $list['uraian_penerimaan'] == '' ? $list['uraian_pengeluaran'] : $list['uraian_penerimaan'];

            $sheet->setCellValueExplicit('B'.$row, $list['bank_bpkb'], PHPExcel_Cell_DataType::TYPE_STRING);
            if($list['sppu_id'] == ''){
               $sheet->setCellValueExplicit('C'.$row, strtoupper($list['uraian']), PHPExcel_Cell_DataType::TYPE_STRING);
            }else{
               $sheet->setCellValueExplicit('C'.$row, strtoupper($list['uraian_fpa']), PHPExcel_Cell_DataType::TYPE_STRING);
            }
            $sheet->setCellValueExplicit('D'.$row, $list['nominal_debet'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('E'.$row, $list['nominal_kredit'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->getStyle('D'.$row.':E'.$row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
            $sheet->getStyle('A'.$row.':B'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $row++;
         }

         $sheet->setCellValue('A'.$row, 'MUTASI');
         $sheet->setCellValueExplicit('D'.$row, '=SUM(D7:D'.($row-1).')', PHPExcel_Cell_DataType::TYPE_FORMULA);
         $sheet->setCellValueExplicit('E'.$row, '=SUM(E7:E'.($row-1).')', PHPExcel_Cell_DataType::TYPE_FORMULA);
         $sheet->setCellValue('A'.($row+1), 'SALDO AKHIR');
         $sheet->setCellValue('D'.($row+1), '=SUM(D'.$row.'-E'.$row.')');
         $sheet->getStyle('D'.$row.':E'.($row+1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
         $sheet->getStyle('A8:E'.($row+1))->applyFromArray($borderTableStyledArray);
         $sheet->getStyle('A'.$row.':E'.($row+1))->getFont()->setBold(true);
         $sheet->mergeCells('A'.$row.':C'.$row);
         $sheet->mergeCells('A'.($row+1).':C'.($row+1));

         $sheet->setCellValue('A'.($row+4), strtoupper(GTFWConfiguration::GetValue('language', 'diketahui_oleh')). ',');
         $sheet->setCellValue('C'.($row+4), strtoupper(GTFWConfiguration::GetValue('language', 'disetujui_oleh')). ',');
         $sheet->setCellValue('D'.($row+3), GTFWConfiguration::GetValue('organization', 'city').', '.$tanggalCetak);
         $sheet->setCellValue('D'.($row+4), strtoupper(GTFWConfiguration::GetValue('language', 'dibuat_oleh')). ',');
         $sheet->setCellValue('A'.($row+8), '-----------------------------');
         $sheet->setCellValue('C'.($row+8), '-----------------------------');
         $sheet->setCellValue('D'.($row+8), '-----------------------------');
         $sheet->setCellValue('A'.($row+9), GTFWConfiguration::GetValue('organization', 'kabiro_keu'));
         $sheet->setCellValue('C'.($row+9), GTFWConfiguration::GetValue('organization', 'kabag_keu'));
         $sheet->setCellValue('D'.($row+9), GTFWConfiguration::GetValue('organization', 'kasir'));
         $sheet->mergeCells('A'.($row+4).':B'.($row+4));
         $sheet->mergeCells('D'.($row+3).':E'.($row+3));
         $sheet->mergeCells('D'.($row+4).':E'.($row+4));
         $sheet->mergeCells('A'.($row+8).':B'.($row+8));
         $sheet->mergeCells('D'.($row+8).':E'.($row+8));
         $sheet->mergeCells('A'.($row+9).':B'.($row+9));
         $sheet->mergeCells('D'.($row+9).':E'.($row+9));
         $sheet->getStyle('A'.($row+3).':E'.($row+9))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
      }
      # Save Excel document to local hard disk
      $this->Save();
   }
}
?>