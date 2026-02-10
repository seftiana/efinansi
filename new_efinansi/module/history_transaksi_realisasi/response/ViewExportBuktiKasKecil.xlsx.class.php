<?php
/**
* ================= doc ====================
* FILENAME     : ViewExportLaporanTransaksi.xlsx.class.php
* @package     : ViewExportLaporanTransaksi
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-04-08
* @Modified    : 2015-04-08
* @Analysts    : Dyah Fajar N
* @contact     : eko.susilo@gamatechno.com
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/history_transaksi_realisasi/business/ExportLaporanTransaksi.class.php';

require_once GTFWConfiguration::GetValue('application','docroot').
'main/function/terbilang.php';

require_once GTFWConfiguration::GetValue('application','docroot').
'main/function/date.php';


class ViewExportBuktiKasKecil extends XlsxResponse
{
   # Internal Variables
   public $Excel;

   function ProcessRequest()
   {
      $mObj          = new ExportLaporanTransaksi();
      $mNumber       = new Number();
      
      $params['tanggal_awal'] = Dispatcher::Instance()->Decrypt($mObj->_GET['tawal']);
      $params['tanggal_akhir'] = Dispatcher::Instance()->Decrypt($mObj->_GET['takhir']);
      $params['fpa'] = Dispatcher::Instance()->Decrypt($mObj->_GET['fpa']);
      $params['status_jurnal'] = Dispatcher::Instance()->Decrypt($mObj->_GET['sts_jurnal']);
      $params['no_bpkb'] = Dispatcher::Instance()->Decrypt($mObj->_GET['no_bpkb']);
      
      $dataExportLaporanTransaksi      = $mObj->getDataExportLaporanTransaksiItems($params); 
      $time          =  IndonesianDate(date('Y-m-d',time()),'YYYY-MM-DD');
      //$time          =  date('Y-m-d',time());
      $namaPejabatKabiroKeuanganUmum   = $mObj->getSettingValue('kabiro_keuangan_dan_umum');
      $namaPejabatWarekSdm             = $mObj->getSettingValue('warek_sdm');
      # set writer for XlsxResponse
      # default Excel5 for .xls extension
      # option Excel2007 for .xlsx extension
      # $this->SetWriter('Excel2007');
      $this->SetFileName('Lap_bukti_kas_kecil_'.date('YmdHis', time()).'.xls');

      # Write your code here
      # Get active sheet
      # Document Setting
      $sheet   = $this->Excel->getActiveSheet(0);
      $sheet->setShowGridlines(true); # false: Hide the gridlines; true: show the gridlines
      # set worksheet name
      $sheet->setTitle('Worksheet Name');
      # set font default
      $sheet->getDefaultStyle()->getFont()->setName('Arial')->setSize('12');
      # setting paging
      # options ORIENTATION_DEFAULT : default; ORIENTATION_LANDSCAPE: lanscape; ORIENTATION_PORTRAIT: Potrait
      $sheet->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_PORTRAIT);
      $sheet->getPageSetup()->setFitToWidth(1); # Set 0 or 1
      $sheet->getPageSetup()->setFitToHeight(0); # Set 0 or 1
      $sheet->getPageSetup()->setHorizontalCentered(true); # true or false
      $sheet->getPageSetup()->setVerticalCentered(false); # true or false
      # Set The papersize
      $sheet->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
      # /Document Setting

      # Page Number
      $sheet->getHeaderFooter()->setOddHeader('&R Halaman &P dari &N');

      $sheet->getRowDimension(2)->setRowHeight(25);
      $sheet->getRowDimension(3)->setRowHeight(25);
      $sheet->getRowDimension(5)->setRowHeight(25);
      $sheet->getRowDimension(6)->setRowHeight(25);
      $sheet->getRowDimension(7)->setRowHeight(20);
      $sheet->getRowDimension(8)->setRowHeight(20);
      $sheet->getRowDimension(9)->setRowHeight(20);
      
      $sheet->getRowDimension(10)->setRowHeight(30);
      $sheet->getRowDimension(12)->setRowHeight(20);
      #$sheet->getRowDimension(13)->setRowHeight(18);

      $sheet->getColumnDimension('A')->setWidth(5);//no
      $sheet->getColumnDimension('B')->setWidth(16);//bpkb
      $sheet->getColumnDimension('C')->setWidth(20);//tanggal
      $sheet->getColumnDimension('D')->setWidth(60);//ket
      $sheet->getColumnDimension('E')->setWidth(30);//unit
      $sheet->getColumnDimension('F')->setWidth(20);//namapenerima
      $sheet->getColumnDimension('G')->setWidth(20);//jumlah

      $sheet->mergeCells('A6:D8');
      $sheet->mergeCells('F5:G5');
      $sheet->mergeCells('F6:G6');
      $sheet->mergeCells('A9:G9');
      $sheet->mergeCells('A10:G10');
      $sheet->mergeCells('A12:D12');
      $sheet->mergeCells('A14:G14');
      $sheet->mergeCells('F12:G12');

      $objectDrawing    = new PHPExcel_Worksheet_Drawing();

      $objectDrawing->setName('Logo');
      $objectDrawing->setDescription('Logo');
      $objectDrawing->setPath(GTFWConfiguration::GetValue('application', 'docroot').'/images/logo_bw_96.png');
      $objectDrawing->setHeight(96);
      $objectDrawing->setWorksheet($sheet);
      $objectDrawing->setCoordinates('A1');
      $objectDrawing->setOffsetX(30);
      $objectDrawing->setOffsetY(10);
      $sheet->setCellValue('A6', GTFWConfiguration::GetValue('organization', 'company_address').",\n".GTFWConfiguration::GetValue('organization', 'city').' '.GTFWConfiguration::GetValue('organization', 'city_number')."\nTelepon : ".GTFWConfiguration::GetValue('organization', 'company_telp'));
      $sheet->getStyle('A6:F8')->applyFromArray(array(
         'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP,
            'wrap' => true,
            'shrinkToFit' => true
         ), 'font' => array(
            'size' => 12,
            'bold' => true
         )
      ));



      $sheet->setCellValue('E5',GTFWConfiguration::GetValue('language', 'no_nota'));
      $sheet->setCellValueExplicit('G5', '');
      $sheet->setCellValue('E6', GTFWConfiguration::GetValue('language', 'tanggal'));
      $sheet->setCellValue('F6', $time);
      $sheet->getStyle('E5:E6')->applyFromArray(array(
         'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
         ), 'font' => array(
            'bold' => true
         )
      ));
      $sheet->getStyle('F5:G6')->applyFromArray(array(
         'font' => array(
            'bold' => true
         ), 'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
         ), 'borders' => array(
            'allborders' => array(
               'style' => PHPExcel_Style_Border::BORDER_DOUBLE,
               'color' => array('argb' => 'ff000000')
            )
         )
      ));
      

      $labelNotaDinas = strtoupper(GTFWConfiguration::GetValue('language', 'nota_dinas'));
      $sheet->setCellValue('A9', $labelNotaDinas);
      $sheet->getStyle('A9:K9')->applyFromArray(array(
         'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
         ), 'font' => array(
            'bold' => true
         )
      ));

      $sheet->setCellValue('A10', "Kepada Yth\nRektor ".GTFWConfiguration::GetValue('organization', 'company_name'));

      $sheet->setCellValue('A12', 'KAS KECIL');
      $sheet->getStyle('A12:D12')->applyFromArray(array(
         'font' => array(
            'bold' => true
         ), 'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
         ), 'borders' => array(
            'allborders' => array(
               'style' => PHPExcel_Style_Border::BORDER_THICK,
               'color' => array('argb' => 'ff000000')
            )
         )
      ));

      $sheet->setCellValue('A14', 'Dengan ini mohon dapat dikeluarkan uang sebagai berikut.');

      $sheet->getStyle('A10:G10')->applyFromArray(array(
         'alignment' => array(
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP,
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
            'wrap' => true,
            'shrinkToFit' => true
         )
      ));

      $sheet->getStyle('A14:G14')->applyFromArray(array(
         'alignment' => array(
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP,
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
            'wrap' => true,
            'shrinkToFit' => true
         )
      ));

      $sheet->setCellValue('A15', GTFWConfiguration::GetValue('language', 'no'));
      $sheet->setCellValue('B15', GTFWConfiguration::GetValue('language', 'no_bpkb'));
      $sheet->setCellValue('C15', GTFWConfiguration::GetValue('language', 'tanggal'));
      $sheet->setCellValue('D15', GTFWConfiguration::GetValue('language', 'keterangan'));
      $sheet->setCellValue('E15', GTFWConfiguration::GetValue('language', 'unit'));
      $sheet->setCellValue('F15', GTFWConfiguration::GetValue('language', 'nama_penerima'));
      $sheet->setCellValue('G15', GTFWConfiguration::GetValue('language', 'jumlah_rp'));
      $sheet->getStyle('A15:G15')->applyFromArray(array(
         'borders' => array(
            'allborders' => array(
               'style' => PHPExcel_Style_Border::BORDER_THIN,
               'color' => array('argb' => 'ff000000')
            )
         ),
         'fill' => array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'startcolor' => array(
               'argb' => 'FFFFFFFF'
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
      ));

      $row  = 16;
      $rowAwal = $row;
      if(!empty($dataExportLaporanTransaksi)){
         $nomor      = 1;
         $totalNominal = 0;
         foreach ($dataExportLaporanTransaksi as $list) {
            $sheet->setCellValue('A'.$row, $nomor);           
            $sheet->setCellValueExplicit('B'.$row, $list['no_bpkb'], PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('C'.$row,  IndonesianDate($list['tanggal'],'YYYY-MM-DD'), PHPExcel_Cell_DataType::TYPE_STRING); 
            $sheet->setCellValueExplicit('D'.$row, $list['keterangan'], PHPExcel_Cell_DataType::TYPE_STRING);            
            $sheet->setCellValueExplicit('E'.$row, $list['unit_nama'], PHPExcel_Cell_DataType::TYPE_STRING);            
            $sheet->setCellValueExplicit('F'.$row, $list['nama_penerima'], PHPExcel_Cell_DataType::TYPE_STRING);            
            $getHeightRow = ceil(strlen($list['keterangan'])/58.5) * 14;
            $getHeightRow2 = ceil(strlen($list['unit_nama'])/28.5) * 14;
            if($getHeightRow2 > $getHeightRow) {
                $getHeightRow = $getHeightRow2;
            }
            
            $sheet->setCellValue('G'.$row, $list['nominal']);  
            
            $sheet->getRowDimension($row)->setRowHeight($getHeightRow);
            $totalNominal += $list['nominal'];
            $nomor+=1;
            $row++;
        }
        
        $sheet->getStyle('G'.$rowAwal.':G'.$row)->getNumberFormat()->setFormatCode('_("Rp"* #,##0_);_("Rp"* \(#,##0\);_("Rp"* "-"_);_(@_)');
        
        $sheet->getStyle('A'.$rowAwal.':C'.$row)->applyFromArray(array(
               'alignment' => array(
                  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
               )
        ));         
        $sheet->getStyle('A'.$rowAwal.':G'.$row)->applyFromArray(array(
            'alignment' => array(
               'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
               'wrap' => true
                 
            )
         ));

      $sheet->getStyle('F12:G12')->getNumberFormat()->setFormatCode('_("Rp"* #,##0_);_("Rp"* \(#,##0\);_("Rp"* "-"_);_(@_)');
      $sheet->setCellValue('F12', '=SUM(G'.$rowAwal.':G'.($row-1).')');
      $sheet->getStyle('F12:G12')->applyFromArray(array(
         'font' => array(
            'bold' => true
         ), 'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
         ), 'borders' => array(
            'allborders' => array(
               'style' => PHPExcel_Style_Border::BORDER_THICK,
               'color' => array('argb' => 'ff000000')
            )
         )
      ));

         $labelTotal =  strtoupper(GTFWConfiguration::GetValue('language', 'total'));
         $sheet->mergeCells('A'.$row.':F'.$row);
         $sheet->setCellValue('A'.$row, $labelTotal);
         $sheet->setCellValue('G'.$row, '=SUM(G'.$rowAwal.':G'.($row-1).')');
         $sheet->getStyle('G'.$row.':G'.$row)->getNumberFormat()->setFormatCode('_("Rp"* #,##0_);_("Rp"* \(#,##0\);_("Rp"* "-"_);_(@_)');
         $sheet->getStyle('A'.$row.':G'.$row)->applyFromArray(array(
            'font' => array(
               'bold' => true
            ), 'alignment' => array(
               'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            )
         ));
         
         $sheet->getStyle('A'.$row.':F'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
         $sheet->getRowDimension($row)->setRowHeight(20);
         $sheet->getStyle('A'.$rowAwal.':G'.$row)->applyFromArray(array(
            'borders' => array(
               'allborders' => array(
                  'style' => PHPExcel_Style_Border::BORDER_THIN,
                  'color' => array('argb' => 'ff000000')
               )
            )
         ));
      }
      
      $labelTerbilang =  GTFWConfiguration::GetValue('language', 'terbilang');
      $sheet->getRowDimension(($row+2))->setRowHeight(30);
      $sheet->mergeCells('A'.($row+2).':B'.($row+2));
      $sheet->setCellValue('A'.($row+2), $labelTerbilang);
      $sheet->mergeCells('C'.($row+2).':G'.($row+2));
      $sheet->setCellValue('C'.($row+2),( $mNumber->Terbilang($totalNominal, 3).' Rupiah'));
      $sheet->getStyle('A'.($row+2).':G'.($row+2))->applyFromArray(array(
         'font' => array(
            'bold' => true
         ), 'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
         )
      ));
      $sheet->getStyle('C'.($row+2).':G'.($row+2))->applyFromArray(array(
         'borders' => array(
            'allborders' => array(
               'style' => PHPExcel_Style_Border::BORDER_THICK,
               'color' => array('argb' => 'ff000000')
            )
         )
      ));

      $labelKabiro =  GTFWConfiguration::GetValue('language', 'kabiro');
      $labelWarek =  GTFWConfiguration::GetValue('language', 'warek');
      $labelNamaUnit =  GTFWConfiguration::GetValue('language', 'keuangan_dan_umum');
      $labelNamaUnit2 =  GTFWConfiguration::GetValue('language', 'pengelolaan_sumber_daya');
      $sheet->setCellValue('D'.($row+6), $labelKabiro);
      $sheet->setCellValue('D'.($row+7), $labelNamaUnit);

      $sheet->mergeCells('F'.($row+6).':G'.($row+6));
      $sheet->mergeCells('F'.($row+7).':G'.($row+7));
      $sheet->setCellValue('F'.($row+6), $labelWarek);
      $sheet->setCellValue('F'.($row+7), $labelNamaUnit2);

      $sheet->getStyle('A'.($row+6).':K'.($row+7))->applyFromArray(array(
         'font' => array(
            'bold' => true
         ), 'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP
         )
      ));
      
      $sheet->mergeCells('F'.($row+11).':G'.($row+11));
      $sheet->setCellValue('D'.($row+11), $namaPejabatKabiroKeuanganUmum);
      $sheet->setCellValue('F'.($row+11), $namaPejabatWarekSdm);
      $sheet->getStyle('A'.($row+11).':G'.($row+11))->applyFromArray(array(
         'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
         )
      ));
      # Save Excel document to local hard disk
      $this->Save();
   }
}
?>