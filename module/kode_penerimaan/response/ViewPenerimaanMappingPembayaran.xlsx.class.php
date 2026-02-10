<?php
/**
* ================= doc ====================
* scope        : PUBLIC
* @Author      : cecep
* @Created     : 2025-12-19
* @Analysts    : Cecep seftiana
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/

require_once GTFWConfiguration::GetValue('application', 'docroot') .
'module/kode_penerimaan/business/KodePenerimaanMappingPembayaran.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot'). 	'module/kode_penerimaan/business/KodePenerimaan.class.php';

class ViewPenerimaanMappingPembayaran extends XlsxResponse
{
   # Internal Variables
   public $Excel;

   function ProcessRequest()
   {
      $mObj       = new KodePenerimaanMappingPembayaran();
	  $mEfinansi  = new KodePenerimaan();
	  
      $requestData['id']    = '';
      $dataList               = $mObj->ChangeKeyName($mObj->GetDataExcel());
	  // echo'<pre>';print_r($dataList);die;
      # set writer for XlsxResponse
      # default Excel5 for .xls extension
      # option Excel2007 for .xlsx extension
      # $this->SetWriter('Excel2007');
      $this->SetFileName('daftar_mapping_kode_penerimaan_pembayaran.xls');

      # Write your code here
      # Get active sheet
      # Document Setting
      $sheet   = $this->Excel->getActiveSheet(0);
      $sheet->setShowGridlines(false); # false: Hide the gridlines; true: show the gridlines
      # set worksheet name
      $sheet->setTitle('mapping_penerimaan_pembayaran');
      # set font default
      $sheet->getDefaultStyle()->getFont()->setName('Courier')->setSize('10');
      # setting paging
      # options ORIENTATION_DEFAULT : default; ORIENTATION_LANDSCAPE: lanscape; ORIENTATION_PORTRAIT: Potrait
      $sheet->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
      $sheet->getPageSetup()->setFitToWidth(1); # Set 0 or 1
      $sheet->getPageSetup()->setFitToHeight(0); # Set 0 or 1
      $sheet->getPageSetup()->setHorizontalCentered(true); # true or false
      $sheet->getPageSetup()->setVerticalCentered(false); # true or false
      # Set The papersize
      $sheet->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
      $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(3,4);
      # /Document Setting

      // pengelolaan label
      $nomor         = GTFWConfiguration::GetValue('language', 'no');
      $komponen      = GTFWConfiguration::GetValue('language', 'komponen');
      $mak           = GTFWConfiguration::GetValue('language', 'coa');
      $kode          = GTFWConfiguration::GetValue('language', 'kode');
      $nama          = GTFWConfiguration::GetValue('language', 'nama');
      $satuan        = GTFWConfiguration::GetValue('language', 'satuan');
      $keterangan    = GTFWConfiguration::GetValue('language', 'keterangan');
      $formula       = GTFWConfiguration::GetValue('language', 'formula');
      $hargaSatuan   = GTFWConfiguration::GetValue('language', 'harga_satuan');
      $sumberDana    = GTFWConfiguration::GetValue('language', 'sumber_dana');

      // width & height
      $sheet->getRowDimension(1)->setRowheight(20);
      $sheet->getRowDimension(3)->setRowheight(18);
      $sheet->getRowDimension(4)->setRowheight(18);

      $sheet->getColumnDimension('A')->setWidth(6);
      $sheet->getColumnDimension('B')->setWidth(15);
      $sheet->getColumnDimension('C')->setWidth(25);
      $sheet->getColumnDimension('D')->setWidth(15);
      $sheet->getColumnDimension('E')->setWidth(25);
      $sheet->getColumnDimension('F')->setWidth(30);
      $sheet->getColumnDimension('G')->setWidth(8);
      $sheet->getColumnDimension('H')->setWidth(12);
      $sheet->getColumnDimension('I')->setWidth(18);
      $sheet->getColumnDimension('J')->setWidth(15);
      $sheet->setCellValue('A1', strtoupper($komponen));
      $sheet->getStyle('A1:E1')->applyFromArray(array(
         'font' => array(
            'bold' => true,
            'size' => 11
         ), 'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
         )
      ));

      $sheet->setCellValue('A3', $nomor);
      $sheet->setCellValue('B3', $komponen);
      $sheet->setCellValue('B4', $kode);
      $sheet->setCellValue('C4', $nama);
      $sheet->setCellValue('D3', $mak);
      $sheet->setCellValue('D4', $kode);
      $sheet->setCellValue('E4', $nama);
      $sheet->setCellValue('F3', $keterangan);
      $sheet->setCellValue('G3', $satuan);
      $sheet->setCellValue('H3', $formula);
      $sheet->setCellValue('I3', $hargaSatuan);
      $sheet->setCellValue('J3', $sumberDana);

      $sheet->getStyle('A3:J4')->applyFromArray(array(
         'font' => array(
            'bold' => true,
            'size' => 10
         ), 'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            'shrinkToFit' => true,
            'wrap' => true
         )
      ));

      $sheet->getStyle('A3:J3')->applyFromArray(array(
         'borders' => array(
            'bottom' => array(
               'style' => PHPExcel_Style_Border::BORDER_HAIR,
               'color' => array('argb' => 'ff000000')
            ), 'top' => array(
               'style' => PHPExcel_Style_Border::BORDER_THIN,
               'color' => array('argb' => 'ff006666')
            )
         )
      ));

      $sheet->getStyle('A4:J4')->applyFromArray(array(
         'borders' => array(
            'bottom' => array(
               'style' => PHPExcel_Style_Border::BORDER_DOUBLE,
               'color' => array('argb' => 'ff006666')
            )
         )
      ));
      $sheet->freezePane('A5');
      if(empty($dataList)){
         $this->setCellValue('A5', GTFWConfiguration::GetValue('language', 'data_kosong'));
         $sheet->mergeCells('A5:J5');
      }else{
         $row     = 5;
         $nomor   = 1;

         foreach ($dataList as $list) {
			$getCoa = $mObj->GetCoaMapByIdPembayaran($list['id']); 
            $height  = max(array(
               ceil(strlen($list['nama'])/28),
               ceil(strlen($list['nama'])/30),
               ceil(strlen($list['nama'])/20)
            ));
            $rowHeight  = $height*14;
            $sheet->getRowDimension($row)->setRowheight($rowHeight);
            $sheet->setCellValue('A'.$row, $nomor);
            $sheet->setCellValueExplicit('B'.$row, $list['kode'], PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('C'.$row, $list['nama']);
            $sheet->setCellValue('D'.$row, $getCoa[0]['ket_coa']);
            $sheet->setCellValue('E'.$row, $getCoa[0]['ket_penerimaan']);
            $sheet->setCellValue('F'.$row, $list['deskripsi']);
            $sheet->setCellValueExplicit('G'.$row, $list['satuan'], PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('H'.$row, $list['formula'], PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('I'.$row, $list['harga_satuan'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValue('J'.$row, $list['sumber_dana']);
            $sheet->getStyle('A'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('B'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('G'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('H'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('I'.$row)->getNumberFormat()->setFormatCode('#,##0.00_);[RED](#,##0.00)');
            $sheet->getStyle('A'.$row.':J'.$row)->applyFromArray(array(
               'borders' => array(
                  'bottom' => array(
                     'style' => PHPExcel_Style_Border::BORDER_THIN,
                     'color' => array('argb' => 'ff444444')
                  )
               )
            ));
            $nomor++;
            $row++;
         }
         $sheet->getStyle('A5:J'.$row)->applyFromArray(array(
            'alignment' => array(
               'wrap' => true,
               'shrinkToFit' => true,
               'vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP
            )
         ));
      }
      # Save Excel document to local hard disk
      $this->Save();
   }
}
?>