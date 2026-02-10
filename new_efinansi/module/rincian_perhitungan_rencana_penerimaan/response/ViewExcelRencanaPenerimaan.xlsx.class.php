<?php
/**
* ================= doc ====================
* FILENAME     : ViewExcelRencanaPenerimaan.xlsx.class.php
* @package     : ViewExcelRencanaPenerimaan
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2014-07-08
* @Modified    : 2014-07-08
* @Analysts    : Dyah Fajar N
* @contact     : eko.susilo@gamatechno.com
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/rincian_perhitungan_rencana_penerimaan/business/AppRencanaPenerimaan.class.php';
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/user_unit_kerja/business/UserUnitKerja.class.php';

class ViewExcelRencanaPenerimaan extends XlsxResponse
{
   # Internal Variables
   public $Excel;

   function ProcessRequest()
   {
      # set writer for XlsxResponse
      # default Excel5 for .xls extension
      # option Excel2007 for .xlsx extension
      # $this->SetWriter('Excel2007');
      $this->SetFileName('laporan_rencana_penerimaan_'.date('Ymd').'.xls');

      # Write your code here
      # Get active sheet
      # Document Setting
      $sheet   = $this->Excel->getActiveSheet(0);
      $sheet->setShowGridlines(false); # false: Hide the gridlines; true: show the gridlines
      # set worksheet name
      $sheet->setTitle('rencana penerimaan');
      # set font default
      $sheet->getDefaultStyle()->getFont()->setName('Arial')->setSize('10');
      # setting paging
      # options ORIENTATION_DEFAULT : default; ORIENTATION_LANDSCAPE: lanscape; ORIENTATION_PORTRAIT: Potrait
      $sheet->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
      $sheet->getPageSetup()->setFitToWidth(1); # Set 0 or 1
      $sheet->getPageSetup()->setFitToHeight(0); # Set 0 or 1
      $sheet->getPageSetup()->setHorizontalCentered(false); # true or false
      $sheet->getPageSetup()->setVerticalCentered(false); # true or false
      # Set The papersize
      $sheet->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
      # /Document Setting

      $Obj              = new AppRencanaPenerimaan();
      $tahun_anggaran   = Dispatcher::Instance()->Decrypt($Obj->_GET['tgl']);
      $unit_id          = Dispatcher::Instance()->Decrypt($Obj->_GET['unitkerjaid']);
      $unit_label       = Dispatcher::Instance()->Decrypt($Obj->_GET['unitkerja_label']);
      $userId           = Dispatcher::Instance()->Decrypt($Obj->_GET['id']);
      $data             = $Obj->GetDataUnitkerja($tahun_anggaran, $unit_id);
      $data_jumlah      = $Obj->GetDataForTotal($tahun_anggaran, $userId, $unit_id);
      $jml              = count($data_jumlah);
      $tot_jumlah       = 0;
      $tot_terima       = 0;
      for($i=0;$i<=$jml;$i++){
         $tot_jumlah    += $data_jumlah[$i]['tot_jumlah'];
         $tot_terima    += $data_jumlah[$i]['tot_terima'];
      }
      $periode          = $Obj->GetTahunAnggaran($tahun_anggaran);

      if (empty($data)) {
         $sheet->setCellValue('A1', 'Data Kosong');
      } else {
         $headerStyle         = array(
            'font' => array(
               'size' => 14,
               'bold' => true
            ), 'alignment' => array(
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

         $sheet->mergeCells('A1:I1');
         $sheet->mergeCells('A3:B3');
         $sheet->mergeCells('C3:I3');
         $sheet->mergeCells('A4:B4');
         $sheet->mergeCells('C4:I4');
         $sheet->mergeCells('A6:A7');
         $sheet->mergeCells('B6:B7');
         $sheet->mergeCells('C6:C7');
         $sheet->mergeCells('D6:F6');
         $sheet->mergeCells('G6:G7');
         $sheet->mergeCells('H6:I6');

         $sheet->getRowDimension(1)->setRowHeight(20);
         $sheet->getRowDimension(6)->setRowHeight(28);

         $sheet->getColumnDimension('A')->setWidth(5);
         $sheet->getColumnDimension('B')->setWidth(18);
         $sheet->getColumnDimension('C')->setWidth(50);
         $sheet->getColumnDimension('D')->setWidth(10);
         $sheet->getColumnDimension('E')->setWidth(15);
         $sheet->getColumnDimension('F')->setWidth(18);
         $sheet->getColumnDimension('G')->setWidth(18);
         $sheet->getColumnDimension('H')->setWidth(10);
         $sheet->getColumnDimension('I')->setWidth(22);

         $sheet->setCellValue('A1', 'Laporan Rincian Perhitungan Rencana Penerimaan');
         $sheet->getStyle('A1:I1')->applyFromArray($headerStyle);
         $sheet->setCellValue('A3', 'Tahun Periode ');
         $sheet->setCellValueExplicit('C3', ': '.$periode['name'], PHPExcel_Cell_DataType::TYPE_STRING);
         $sheet->setCellValue('A4', 'Unit / Sub Unit');
         $sheet->setCellValue('C4', ': '.$unit_label);

         // data-table
         // table-header
         $sheet->setCellValue('A6', GTFWConfiguration::GetValue('language', 'no'));
         $sheet->setCellValue('B6', GTFWConfiguration::GetValue('language', 'kode'));
         $sheet->setCellValue('C6', "Akun Pendapatan/\nRincian Target");
         $sheet->setCellValue('D6', 'Perhitungan Target');
         $sheet->setCellValue('D7', 'Volume');
         $sheet->setCellValue('E7', 'Tarif (rp)');
         $sheet->setCellValue('F7', 'Jumlah (rp)');
         $sheet->setCellValue('G6', "PNBP Fungsional \n(Rp)");
         $sheet->setCellValue('H6', "Pagu PNBP sesuai\nijin menkeu");
         $sheet->setCellValue('H7', '%');
         $sheet->setCellValue('I7', GTFWConfiguration::GetValue('language', 'total_penerimaan_rp'));

         $sheet->getStyle('A6:I7')->applyFromArray($styledTableHeaderArray);
         // table-cell
         $startRow      = 8;
         $total         = '';
         $jumlah_total  = '';
         $idrencana     = '';
         $idkode        = '';
         $kode          = '';
         $nama          = '';

         $data_list     = $data;
         $kode_satker   = '';
         $kode_unit     = '';
         $nama_satker   = '';
         $nama_unit     = '';

         for ($i=0; $i<sizeof($data_list);) {
            if(($data_list[$i]['kode_satker'] == $kode_satker) && ($data_list[$i]['kode_unit'] == $kode_unit)) {
               if($data_list[$i]['idrencana'] == "") {
                  $i++;
                  continue;
               }
               $send                      = $data_list[$i];
               $send['total_penerimaan']  = $data_list[$i]['total'];
               $send['volume']            = $data_list[$i]['volume'];
               $send['pagu']              = $data_list[$i]['pagu'];
               $send['tarif']             = $data_list[$i]['tarif'];
               $send['totalterima']       = $data_list[$i]['total_kali'];
               $send['nomor']             = $no;

               $i++;$no++;$number++;
            } elseif($data_list[$i]['kode_satker'] != $kode_satker && $data_list[$i]['nama_satker'] == $data_list[$i]['nama_unit']) {
               $kode_satker               = $data_list[$i]['kode_satker'];
               $kode_unit                 = $data_list[$i]['kode_unit'];
               $nama_satker               = $data_list[$i]['nama_satker'];
               $nama_unit                 = $data_list[$i]['nama_unit'];
               $send['kode']              = $kode_unit;
               $send['nama']              = $data_list[$i]['nama_unit'];

               $send['total_penerimaan']  = $data_list[$i]['jumlah_total'];
               $send['volume']            = "";
               $send['pagu']              = "";
               $send['tarif']             = "";
               $send['totalterima']       = $data_list[$i]['totalterima'];
               $send['nomor'] = "";
               $no   = 1;
            } elseif($data_list[$i]['kode_unit'] != $kode_unit) {
               $kode_satker      = $data_list[$i]['kode_satker'];
               $kode_unit        = $data_list[$i]['kode_unit'];
               $nama_satker      = $data_list[$i]['nama_satker'];
               $nama_unit        = $data_list[$i]['nama_unit'];
               $send['kode']     = $kode_unit;
               $send['nama']     = $data_list[$i]['nama_unit'];
               $send['total_penerimaan']  = $data_list[$i]['jumlah_total'];
               $send['volume']            = "";
               $send['pagu']              = "";
               $send['totalterima']       = $data_list[$i]['totalterima'];
               $send['tarif']             = "";
               $send['nomor']             = "";
               $no=1;
            }

            $sheet->setCellValue('A'.$startRow, $send['nomor']);
            $sheet->setCellValueExplicit('B'.$startRow, $send['kode'], PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('C'.$startRow, $send['nama']);
            $sheet->setCellValueExplicit('D'.$startRow, $send['volume'], PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('E'.$startRow, $send['tarif']);
            $sheet->setCellValueExplicit('F'.$startRow, $send['totalterima'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('G'.$startRow, $send['totalterima'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValue('H'.$startRow, $send['pagu']);
            $sheet->setCellValueExplicit('I'.$startRow, $send['total_penerimaan'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->getStyle('E'.$startRow.':I'.$startRow)->getNumberFormat()->setFormatCode('#,##0_);[RED](#,##0)');
            $startRow++;
         }
         $sheet->getStyle('A8:A'.($startRow-1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
         $sheet->getStyle('B8:B'.($startRow-1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
         $sheet->getStyle('D8:D'.($startRow-1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
         $sheet->getStyle('H8:H'.($startRow-1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

         $sheet->getStyle('A8:I'.$startRow)->applyFromArray($borderTableStyledArray);
         $sheet->mergeCells('A'.$startRow.':C'.$startRow);
         $sheet->setCellValue('A'.$startRow, 'TOTAL JUMLAH');
         $sheet->setCellValueExplicit('D'.$startRow, '', PHPExcel_Cell_DataType::TYPE_NULL);
         $sheet->setCellValueExplicit('E'.$startRow, '', PHPExcel_Cell_DataType::TYPE_NULL);
         $sheet->setCellValueExplicit('F'.$startRow, $tot_jumlah, PHPExcel_Cell_DataType::TYPE_NUMERIC);
         $sheet->setCellValueExplicit('G'.$startRow, $tot_jumlah, PHPExcel_Cell_DataType::TYPE_NUMERIC);
         $sheet->setCellValueExplicit('H'.$startRow, '', PHPExcel_Cell_DataType::TYPE_NULL);
         $sheet->setCellValueExplicit('I'.$startRow, $tot_terima, PHPExcel_Cell_DataType::TYPE_NUMERIC);
         $sheet->getStyle('A'.$startRow.':I'.$startRow)->getFont()->setBold(true);
         $sheet->getStyle('A'.$startRow.':C'.$startRow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

         $sheet->getStyle('E'.$startRow.':I'.$startRow)->getNumberFormat()->setFormatCode('#,##0_);[RED](#,##0)');
      }


      # Save Excel document to local hard disk
      $this->Save();
   }
}
?>