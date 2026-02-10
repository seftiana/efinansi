<?php
/**
* ================= doc ====================
* FILENAME     : ViewExportExcelSppu.xlsx.class.php
* @package     : ViewExportExcelSppu
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
'module/finansi_sppu/business/Sppu.class.php';
require_once GTFWConfiguration::GetValue('application','docroot').
'main/function/terbilang.php';

class ViewExportExcelSppu extends XlsxResponse
{
   # Internal Variables
   public $Excel;

   function ProcessRequest()
   {
      $mObj          = new Sppu();
      $mNumber       = new Number();
      $dataId        = Dispatcher::Instance()->Decrypt($mObj->_GET['data_id']);
      $dataSppu      = $mObj->getDataDetailSppu($dataId);
      $dataSppu['terbilang']  = $mNumber->Terbilang($dataSppu['nominal'], 3).' Rupiah';
      $sppuTanggal   = date('Y-m-d', strtotime($dataSppu['tanggal']));
      $sppuTglDay    = (int)date('d', strtotime($dataSppu['tanggal']));
      $sppuTglMon    = (int)date('m', strtotime($dataSppu['tanggal']));
      $sppuTglYear   = (int)date('Y', strtotime($dataSppu['tanggal']));
      $time          = gmmktime(0,0,0, $sppuTglMon, $sppuTglDay, $sppuTglYear);
      $dataList      = $mObj->getDataSppuItems($dataId);
      $namaPejabatKabiroKeuanganUmum   = $mObj->getSettingValue('kabiro_keuangan_dan_umum');
      $namaPejabatWarekSdm             = $mObj->getSettingValue('warek_sdm');
      # set writer for XlsxResponse
      # default Excel5 for .xls extension
      # option Excel2007 for .xlsx extension
      # $this->SetWriter('Excel2007');
      $this->SetFileName('sppu_'.date('YmdHis', time()).'.xls');

      # Write your code here
      # Get active sheet
      # Document Setting
      $sheet   = $this->Excel->getActiveSheet(0);
      $sheet->setShowGridlines(true); # false: Hide the gridlines; true: show the gridlines
      # set worksheet name
      $sheet->setTitle('Worksheet Name');
      # set font default
      $sheet->getDefaultStyle()->getFont()->setName('Arial')->setSize('13');
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
	  
	  $sheet->getRowDimension(1)->setRowHeight(25); #
      $sheet->getRowDimension(2)->setRowHeight(25);
      $sheet->getRowDimension(3)->setRowHeight(25);
      $sheet->getRowDimension(5)->setRowHeight(25);
      $sheet->getRowDimension(6)->setRowHeight(25);
      $sheet->getRowDimension(7)->setRowHeight(20);
      $sheet->getRowDimension(8)->setRowHeight(20);
      $sheet->getRowDimension(10)->setRowHeight(20);
      $sheet->getRowDimension(11)->setRowHeight(20);
      $sheet->getRowDimension(12)->setRowHeight(30);
      $sheet->getRowDimension(14)->setRowHeight(16);
      $sheet->getRowDimension(15)->setRowHeight(18);

      $sheet->getColumnDimension('A')->setWidth(5);
      $sheet->getColumnDimension('B')->setWidth(25);
      $sheet->getColumnDimension('J')->setWidth(16);

      $sheet->mergeCells('A6:F8');
      $sheet->mergeCells('I1:K1'); #
      $sheet->mergeCells('I2:J2');
      $sheet->mergeCells('I3:J3');
      $sheet->mergeCells('A10:K10');
      $sheet->mergeCells('A11:K11');
      $sheet->mergeCells('G5:H5');
      $sheet->mergeCells('G6:H6');
      $sheet->mergeCells('I5:K5');
      $sheet->mergeCells('I6:K6');
      $sheet->mergeCells('A12:K12');
      $sheet->mergeCells('A14:K14');
      $sheet->mergeCells('C15:I15');
      $sheet->mergeCells('J15:K15');

      $objectDrawing    = new PHPExcel_Worksheet_Drawing();
      $objectDrawing2   = new PHPExcel_Worksheet_Drawing();
      $objectDrawing3   = new PHPExcel_Worksheet_Drawing();

      $checkboxChecked  = GTFWConfiguration::GetValue('application', 'docroot').'/images/icons/24/checkbox-checked.png';
      $checkboxUncheck  = GTFWConfiguration::GetValue('application', 'docroot').'/images/icons/24/Unchecked-Checkbox.png';
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
            'size' => 10,
            'bold' => true
         )
      ));
		
	  $sheet->setCellValue('I1', 'KODE DOKUMEN : F-KUA-001.02 ');	#
      $sheet->setCellValue('I2', 'Bank Payment : ');
      $objectDrawing2->setName('bank payment');
      $objectDrawing2->setDescription('Bank Payment');
      if(strtoupper($dataSppu['bank_payment']) == 'Y'){
         $objectDrawing2->setPath($checkboxChecked);
      }else{
         $objectDrawing2->setPath($checkboxUncheck);
      }
      $objectDrawing2->setHeight(16);
      $objectDrawing2->setCoordinates('K2');
      $objectDrawing2->setWorksheet($sheet);
      $objectDrawing2->setOffsetX(20);
      $objectDrawing2->setOffsetY(10);

      $sheet->setCellValue('I3', 'Cash Payment : ');
      $objectDrawing3->setName('cash payment');
      $objectDrawing3->setDescription('Cash Payment');
      if(strtoupper($dataSppu['cash_receipt']) == 'Y'){
         $objectDrawing3->setPath($checkboxChecked);
      }else{
         $objectDrawing3->setPath($checkboxUncheck);
      }
      $objectDrawing3->setHeight(16);
      $objectDrawing3->setCoordinates('K3');
      $objectDrawing3->setWorksheet($sheet);
      $objectDrawing3->setOffsetX(20);
      $objectDrawing3->setOffsetY(10);
		
	  #
      $sheet->getStyle('I1:K3')->applyFromArray(array(
         'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
         ), 'font' => array(
            'bold' => true,
            'size' => 10
         )
      ));

      $sheet->getStyle('K2:K3')->applyFromArray(array(
         'borders' => array(
            'allborders' => array(
               'style' => PHPExcel_Style_Border::BORDER_DOUBLE,
               'color' => array('argb' => 'ff000000')
            )
         )
      ));
	  
	  #
		$sheet->getStyle('I1:K1')->applyFromArray(array(
         'borders' => array(
            'allborders' => array(
               'style' => PHPExcel_Style_Border::BORDER_DOUBLE,
               'color' => array('argb' => 'ff000000')
            )
         )
      ));
	  
      $sheet->setCellValue('G5', 'No. SPPU');
      $sheet->setCellValueExplicit('I5', $dataSppu['nomor'], PHPExcel_Cell_DataType::TYPE_STRING);
      $sheet->setCellValue('G6', GTFWConfiguration::GetValue('language', 'tanggal'));
      $sheet->setCellValue('I6', PHPExcel_Shared_Date::PHPToExcel($time));

      $sheet->getStyle('G5:H6')->applyFromArray(array(
         'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
         ), 'font' => array(
            'bold' => true
         )
      ));
	  
      $sheet->getStyle('I5:K6')->applyFromArray(array(
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
      $sheet->getStyle('I6')->getNumberFormat()->setFormatCode('dd mmmm yyyy;@');
	  
	
      $sheet->setCellValue('A10', 'Surat Pencairan Pengeluaran Uang (SPPU)');
      $sheet->setCellValueExplicit('A11', '('.$dataSppu['nomor_bukti'].')', PHPExcel_Cell_DataType::TYPE_STRING);
      $sheet->getStyle('A10:K11')->applyFromArray(array(
         'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
         ), 'font' => array(
            'bold' => true
         )
      ));

      $sheet->setCellValue('A12', "Kepada Yth\nRektor ".GTFWConfiguration::GetValue('organization', 'company_name'));

      $sheet->setCellValue('A14', 'Dengan ini mohon dapat dikeluarkan uang sebagai berikut.');

      $sheet->getStyle('A12:K14')->applyFromArray(array(
         'alignment' => array(
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP,
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
            'wrap' => true,
            'shrinkToFit' => true
         )
      ));

      $sheet->setCellValue('A15', GTFWConfiguration::GetValue('language', 'no'));
      $sheet->setCellValue('B15', GTFWConfiguration::GetValue('language', 'no_pengajuan'));
      $sheet->setCellValue('C15', GTFWConfiguration::GetValue('language', 'keterangan'));
      $sheet->setCellValue('J15', GTFWConfiguration::GetValue('language', 'jumlah_rp'));
      $sheet->getStyle('A15:K15')->applyFromArray(array(
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
      if(!empty($dataList)){
         $nomor      = 1;
         foreach ($dataList as $list) {
            $sheet->setCellValue('A'.$row, $nomor);           
            $sheet->setCellValueExplicit('B'.$row, $list['no_fpa'], PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->mergeCells('C'.$row.':I'.$row);
            // $getHeightRow = ceil(strlen($list['lingkup_komponen'])/70.5) * 14;            
            $sheet->setCellValue('C'.$row,$list['lingkup_komponen']);

            $sheet->getStyle('C'.$row.':I'.$row)->getAlignment()->setWrapText(TRUE);
            $getCountContent = ceil(strlen($list['lingkup_komponen'])/40); 
            if($getCountContent > 5){
               $getHeightRow = ceil(strlen($list['lingkup_komponen'])/40) * 15;     
               $sheet->getRowDimension($row)->setRowHeight($getHeightRow);
            }elseif($getCountContent > 10){
               $getHeightRow = ceil(strlen($list['lingkup_komponen'])/40) * 18;     
               $sheet->getRowDimension($row)->setRowHeight($getHeightRow);
            }else{
               $getHeightRow = ceil(strlen($list['lingkup_komponen'])/40) * 13;     
               $sheet->getRowDimension($row)->setRowHeight($getHeightRow);
            }

            $sheet->mergeCells('J'.$row.':K'.$row);
            $sheet->setCellValue('J'.$row, $list['nominal']);  
            
            $sheet->getRowDimension($row)->setRowHeight($getHeightRow);
            $nomor+=1;
            $row++;
        }
        
        $sheet->getStyle('J'.$rowAwal.':K'.$row)->getNumberFormat()->setFormatCode('_("Rp"* #,##0_);_("Rp"* \(#,##0\);_("Rp"* "-"_);_(@_)');
        
        $sheet->getStyle('A'.$rowAwal.':A'.$row)->applyFromArray(array(
               'alignment' => array(
                  'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
               )
        ));         
        $sheet->getStyle('A'.$rowAwal.':K'.$row)->applyFromArray(array(
            'alignment' => array(
               'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
               'wrap' => true
                 
            )
         ));
         $sheet->mergeCells('A'.$row.':I'.$row);
         $sheet->mergeCells('J'.$row.':K'.$row);
         $sheet->setCellValue('A'.$row, 'TOTAL');
         $sheet->setCellValue('J'.$row, '=SUM(J16:K'.($row-1).')');
         $sheet->getStyle('J'.$row.':K'.$row)->getNumberFormat()->setFormatCode('_("Rp"* #,##0_);_("Rp"* \(#,##0\);_("Rp"* "-"_);_(@_)');
         $sheet->getStyle('A'.$row.':K'.$row)->applyFromArray(array(
            'font' => array(
               'bold' => true
            ), 'alignment' => array(
               'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            )
         ));
         $sheet->getStyle('A'.$row.':I'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
         $sheet->getRowDimension($row)->setRowHeight(20);
         $sheet->getStyle('A16:K'.$row)->applyFromArray(array(
            'borders' => array(
               'allborders' => array(
                  'style' => PHPExcel_Style_Border::BORDER_THIN,
                  'color' => array('argb' => 'ff000000')
               )
            )
         ));
      }

      $sheet->getRowDimension(($row+2))->setRowHeight(30);
      $sheet->mergeCells('A'.($row+2).':B'.($row+2));
      $sheet->setCellValue('A'.($row+2), 'Terbilang');
      $sheet->mergeCells('C'.($row+2).':K'.($row+2));
      $sheet->setCellValue('C'.($row+2), $dataSppu['terbilang']);
	  $sheet->getStyle('A'.($row+2).':K'.($row+2))->getAlignment()->setWrapText(TRUE);
      $sheet->getStyle('A'.($row+2).':K'.($row+2))->applyFromArray(array(
         'font' => array(
            'bold' => true
         ), 'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
         )
      ));
      $sheet->getStyle('C'.($row+2).':K'.($row+2))->applyFromArray(array(
         'borders' => array(
            'allborders' => array(
               'style' => PHPExcel_Style_Border::BORDER_THICK,
               'color' => array('argb' => 'ff000000')
            )
         )
      ));

      $sheet->getRowDimension(($row+4))->setRowHeight(18);
      $sheet->setCellValue('B'.($row+4), 'No.Cek/Giro');
      $sheet->mergeCells('C'.($row+4).':E'.($row+4));
      $sheet->mergeCells('F'.($row+4).':K'.($row+4));
      $sheet->setCellValue('C'.($row+4), $dataSppu['nomor_rekening']);
      $sheet->setCellValue('F'.($row+4), $dataSppu['bank']);
      $sheet->getStyle('A'.($row+4).':K'.($row+4))->applyFromArray(array(
         'alignment' => array(
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
         ), 'font' => array(
            'bold' => true
         )
      ));

      $sheet->getStyle('A'.($row+4).':E'.($row+4))->applyFromArray(array(
         'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
         )
      ));

      $sheet->getStyle('C'.($row+4).':E'.($row+4))->applyFromArray(array(
         'borders' => array(
            'allborders' => array(
               'style' => PHPExcel_Style_Border::BORDER_THICK,
               'color' => array('argb' => 'ff000000')
            )
         )
      ));

      $sheet->mergeCells('B'.($row+6).':D'.($row+6));
      $sheet->mergeCells('B'.($row+7).':D'.($row+7));
      $sheet->setCellValue('B'.($row+6), 'Kabiro');
      $sheet->setCellValue('B'.($row+7), 'Keuangan & Umum');

      $sheet->mergeCells('I'.($row+6).':K'.($row+6));
      $sheet->mergeCells('I'.($row+7).':K'.($row+7));
      $sheet->setCellValue('I'.($row+6), 'Warek');
      $sheet->setCellValue('I'.($row+7), 'Pengelolaan Sumber Daya');

      $sheet->getStyle('A'.($row+6).':K'.($row+7))->applyFromArray(array(
         'font' => array(
            'bold' => true
         ), 'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP
         )
      ));

      $sheet->mergeCells('B'.($row+11).':D'.($row+11));
      $sheet->mergeCells('I'.($row+11).':K'.($row+11));
      $sheet->setCellValue('B'.($row+11), $namaPejabatKabiroKeuanganUmum);
      $sheet->setCellValue('I'.($row+11), $namaPejabatWarekSdm);
      $sheet->getStyle('A'.($row+11).':K'.($row+11))->applyFromArray(array(
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
