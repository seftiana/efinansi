<?php
/**
* ================= doc ====================
* FILENAME     : ViewExcelLapRencanaPenerimaan.xlsx.class.php
* @package     : ViewExcelLapRencanaPenerimaan
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
'module/lap_rencana_penerimaan/business/AppLapRencanaPenerimaan.class.php';
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/user_unit_kerja/business/UserUnitKerja.class.php';

class ViewExcelLapRencanaPenerimaan extends XlsxResponse
{
   # Internal Variables
   public $Excel;

   function ProcessRequest()
   {
      # set writer for XlsxResponse
      # default Excel5 for .xls extension
      # option Excel2007 for .xlsx extension
      # $this->SetWriter('Excel2007');
      $this->SetFileName('laporan_rencana_penerimaan_bulanan_'.date('Ymd', time()).'.xls');

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

      $Obj                 = new AppLapRencanaPenerimaan();
      $UserUnitKerja       = new UserUnitKerja();
      $tahun_anggaran      = Dispatcher::Instance()->Decrypt($Obj->_GET['tgl']);
      $unitkerja_label     = Dispatcher::Instance()->Decrypt($Obj->_GET['unitkerja_label']);
      $unitkerja           = Dispatcher::Instance()->Decrypt($Obj->_GET['unitkerja']);
      $statusApprove       = Dispatcher::Instance()->Decrypt($Obj->_GET['status_approve']);
      $userId              = Dispatcher::Instance()->Decrypt($Obj->_GET['id']);

      $data                = $Obj->GetDataRencanaPenerimaan($tahun_anggaran, $unitkerja, $statusApprove);
      $unitkerja           = $UserUnitKerja->GetUnitKerja($unitkerja);
      $tahunanggaran       = $Obj->GetTahunAnggaran($tahun_anggaran);
      $unitkerja_nama      = $unitkerja['unit_kerja_nama'];
      $tahunanggaran_nama  = $tahunanggaran['name'];
      $data_kosong         = GTFWConfiguration::GetValue('language','data_kosong');

      if (empty($data)) {
         $sheet->setCellValue('A1', $data_kosong);
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

         /**
          * set label
          */
         $lap_penerimaan_label   = GTFWConfiguration::GetValue('language','lap_rencana_penerimaan');
         $unit_sub_unit_label    = GTFWConfiguration::GetValue('language','unit').'/'.GTFWConfiguration::GetValue('language','sub_unit');
         $tahun_periode_label    = GTFWConfiguration::GetValue('language','tahun_periode');
         $no_label               = GTFWConfiguration::GetValue('language','no');
         $kode_label             = GTFWConfiguration::GetValue('language','kode_penerimaan');
         $unit_kerja_label       = GTFWConfiguration::GetValue('language','unit_kerja');
         $jenis_penerimaan_label = GTFWConfiguration::GetValue('language','jenis_penerimaan');
         $keterangan_label       = GTFWConfiguration::GetValue('language','keterangan');
         $t_penerimaan_label     = GTFWConfiguration::GetValue('language','total_penerimaan');
         $dist_penerimaan_label  = GTFWConfiguration::GetValue('language','distribusi_penerimaan');
         $total_label            = GTFWConfiguration::GetValue('language','total');
         $januarai_label         = GTFWConfiguration::GetValue('language','januari');
         $februari_label         = GTFWConfiguration::GetValue('language','februari');
         $maret_label            = GTFWConfiguration::GetValue('language','maret');
         $april_label            = GTFWConfiguration::GetValue('language','april');
         $mei_label              = GTFWConfiguration::GetValue('language','mei');
         $juni_label             = GTFWConfiguration::GetValue('language','juni');
         $juli_label             = GTFWConfiguration::GetValue('language','juli');
         $agustus_label          = GTFWConfiguration::GetValue('language','agustus');
         $september_label        = GTFWConfiguration::GetValue('language','september');
         $oktober_label          = GTFWConfiguration::GetValue('language','oktober');
         $november_label         = GTFWConfiguration::GetValue('language','november');
         $desember_label         = GTFWConfiguration::GetValue('language','desember');
         $total_label            = GTFWConfiguration::GetValue('language','total');
         $persen_label           = '%';
         /**
          * end set label
          */

         $sheet->getRowDimension(1)->setRowHeight(20);
         $sheet->getColumnDimension('A')->setWidth(8);
         $sheet->getColumnDimension('B')->setWidth(18);
         $sheet->getColumnDimension('C')->setWidth(20);
         $sheet->getColumnDimension('D')->setWidth(40);
         $sheet->getColumnDimension('E')->setWidth(40);

         $sheet->getColumnDimension('F')->setWidth(23);
         $sheet->getColumnDimension('G')->setWidth(5);
         $sheet->getColumnDimension('H')->setWidth(23);
         $sheet->getColumnDimension('I')->setWidth(5);
         $sheet->getColumnDimension('J')->setWidth(23);
         $sheet->getColumnDimension('K')->setWidth(5);
         $sheet->getColumnDimension('L')->setWidth(23);
         $sheet->getColumnDimension('M')->setWidth(5);
         $sheet->getColumnDimension('N')->setWidth(23);
         $sheet->getColumnDimension('O')->setWidth(5);
         $sheet->getColumnDimension('P')->setWidth(23);
         $sheet->getColumnDimension('Q')->setWidth(5);
         $sheet->getColumnDimension('R')->setWidth(23);
         $sheet->getColumnDimension('S')->setWidth(5);
         $sheet->getColumnDimension('T')->setWidth(23);
         $sheet->getColumnDimension('U')->setWidth(5);
         $sheet->getColumnDimension('V')->setWidth(23);
         $sheet->getColumnDimension('W')->setWidth(5);
         $sheet->getColumnDimension('X')->setWidth(23);
         $sheet->getColumnDimension('Y')->setWidth(5);
         $sheet->getColumnDimension('Z')->setWidth(23);
         $sheet->getColumnDimension('AA')->setWidth(5);
         $sheet->getColumnDimension('AB')->setWidth(23);
         $sheet->getColumnDimension('AC')->setWidth(5);
         $sheet->getColumnDimension('AD')->setWidth(23);

         $sheet->mergeCells('A1:AD1');
         $sheet->mergeCells('A3:B3');
         $sheet->mergeCells('C3:AD3');
         $sheet->mergeCells('A4:B4');
         $sheet->mergeCells('C4:AD4');
         $sheet->mergeCells('G6:AD6');
         $sheet->mergeCells('A6:A7');
         $sheet->mergeCells('B6:B7');
         $sheet->mergeCells('C6:C7');
         $sheet->mergeCells('D6:D7');
         $sheet->mergeCells('E6:E7');
         $sheet->mergeCells('F6:F7');

         $sheet->setCellValue('A1', $lap_penerimaan_label);
         $sheet->getStyle('A1:AD1')->applyFromArray($headerStyle);

         $sheet->setCellValue('A3', $tahun_periode_label);
         $sheet->setCellValueExplicit('C3', ': '.$tahunanggaran_nama, PHPExcel_Cell_DataType::TYPE_STRING);
         $sheet->setCellValue('A4', $unit_sub_unit_label);
         $sheet->setCellValue('C4', ': '.$unitkerja_nama);
         $sheet->getStyle('A3:AD4')->getFont()->setBold(true);

         $sheet->setCellValue('A6', $no_label);
         $sheet->setCellValue('B6', $kode_label);
         $sheet->setCellValue('C6', $unit_kerja_label);
         $sheet->setCellValue('D6', $jenis_penerimaan_label);
         $sheet->setCellValue('E6', $keterangan_label);

         $sheet->setCellValue('F6', $t_penerimaan_label);
         $sheet->setCellValue('G6', $dist_penerimaan_label);
         $sheet->setCellValue('G7', $persen_label);
         $sheet->setCellValue('H7', $januarai_label);
         $sheet->setCellValue('I7', $persen_label);
         $sheet->setCellValue('J7', $februari_label);
         $sheet->setCellValue('K7', $persen_label);
         $sheet->setCellValue('L7', $maret_label);
         $sheet->setCellValue('M7', $persen_label);
         $sheet->setCellValue('N7', $april_label);
         $sheet->setCellValue('O7', $persen_label);
         $sheet->setCellValue('P7', $mei_label);
         $sheet->setCellValue('Q7', $persen_label);
         $sheet->setCellValue('R7', $juni_label);
         $sheet->setCellValue('S7', $persen_label);
         $sheet->setCellValue('T7', $juli_label);
         $sheet->setCellValue('U7', $persen_label);
         $sheet->setCellValue('V7', $agustus_label);
         $sheet->setCellValue('W7', $persen_label);
         $sheet->setCellValue('X7', $september_label);
         $sheet->setCellValue('Y7', $persen_label);
         $sheet->setCellValue('Z7', $oktober_label);
         $sheet->setCellValue('AA7', $persen_label);
         $sheet->setCellValue('AB7', $november_label);
         $sheet->setCellValue('AC7', $persen_label);
         $sheet->setCellValue('AD7', $desember_label);

         $sheet->getStyle('A6:AD7')->applyFromArray($styledTableHeaderArray);

         $row     = 8;
         $nomor   = 1;

         foreach ($data as $list) {
            $sheet->setCellValue('A'.$row, $nomor);
            $sheet->setCellValueExplicit('B'.$row, $list['kode_penerimaan'], PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('C'.$row, $list['unit_kerja_nama']);
            $sheet->setCellValue('D'.$row, $list['kode_penerimaan_nama']);
            $sheet->setCellValue('E'.$row, $list['keterangan']);

            $sheet->getStyle('C'.$row)->getAlignment()->setWrapText(TRUE);
            $sheet->getStyle('E'.$row)->getAlignment()->setWrapText(TRUE);

            $getHeightRow  = ceil(strlen( $list['unit_kerja_nama'])/20) * 15;
            $getHeightRow2 = ceil(strlen( $list['keterangan'])/40) * 15;   

            if($getHeightRow > $getHeightRow2) {
                $getHeightRow2 = $getHeightRow;
            }elseif($getHeightRow2 > $getHeightRow) {
                $getHeightRow = $getHeightRow2;
            }
            $sheet->getRowDimension($row)->setRowHeight($getHeightRow2);

            $sheet->setCellValueExplicit('F'.$row, $list['total_terima'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('G'.$row, $list['pjanuari'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('H'.$row, $list['januari'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('I'.$row, $list['pfebruari'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('J'.$row, $list['februari'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('K'.$row, $list['pmaret'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('L'.$row, $list['maret'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('M'.$row, $list['papril'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('N'.$row, $list['april'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('O'.$row, $list['pmei'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('P'.$row, $list['mei'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('Q'.$row, $list['pjuni'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('R'.$row, $list['juni'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('S'.$row, $list['pjuli'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('T'.$row, $list['juli'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('U'.$row, $list['pagustus'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('V'.$row, $list['agustus'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('W'.$row, $list['pseptember'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('X'.$row, $list['september'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('Y'.$row, $list['poktober'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('Z'.$row, $list['oktober'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('AA'.$row, $list['pnovember'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('AB'.$row, $list['november'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('AC'.$row, $list['pdesember'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('AD'.$row, $list['desember'], PHPExcel_Cell_DataType::TYPE_NUMERIC);

            $sheet->getStyle('A'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('G'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('H'.$row)->getNumberFormat()->setFormatCode('#,##0_);[RED](#,##0)');
            $sheet->getStyle('I'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('J'.$row)->getNumberFormat()->setFormatCode('#,##0_);[RED](#,##0)');
            $sheet->getStyle('K'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('L'.$row)->getNumberFormat()->setFormatCode('#,##0_);[RED](#,##0)');
            $sheet->getStyle('M'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('N'.$row)->getNumberFormat()->setFormatCode('#,##0_);[RED](#,##0)');
            $sheet->getStyle('O'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('P'.$row)->getNumberFormat()->setFormatCode('#,##0_);[RED](#,##0)');
            $sheet->getStyle('Q'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('R'.$row)->getNumberFormat()->setFormatCode('#,##0_);[RED](#,##0)');
            $sheet->getStyle('S'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('T'.$row)->getNumberFormat()->setFormatCode('#,##0_);[RED](#,##0)');
            $sheet->getStyle('U'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('V'.$row)->getNumberFormat()->setFormatCode('#,##0_);[RED](#,##0)');
            $sheet->getStyle('W'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('X'.$row)->getNumberFormat()->setFormatCode('#,##0_);[RED](#,##0)');
            $sheet->getStyle('Y'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('Z'.$row)->getNumberFormat()->setFormatCode('#,##0_);[RED](#,##0)');
            $sheet->getStyle('Y'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('AB'.$row)->getNumberFormat()->setFormatCode('#,##0_);[RED](#,##0)');
            $sheet->getStyle('AC'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('AD'.$row)->getNumberFormat()->setFormatCode('#,##0_);[RED](#,##0)');
            $sheet->getStyle('F'.$row)->getNumberFormat()->setFormatCode('#,##0_);[RED](#,##0)');

            $sheet->getStyle('A'.$row.':AD'.$row)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);

            $nomor++;
            $row++;
         }

         $sheet->getStyle('A8:AD'.$row)->applyFromArray($borderTableStyledArray);
         $sheet->mergeCells('A'.$row.':E'.$row);
         $sheet->setCellValue('A'.$row, 'TOTAL');
         $sheet->getStyle('A'.$row.':E'.$row)->getFont()->setBold(true);
         $sheet->getStyle('A'.$row.':E'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
         $sheet->setCellValueExplicit('F'.$row, '=SUM(F8:F'.($row-1).')', PHPExcel_Cell_DataType::TYPE_FORMULA);
         $sheet->setCellValueExplicit('H'.$row, '=SUM(H8:H'.($row-1).')', PHPExcel_Cell_DataType::TYPE_FORMULA);
         $sheet->setCellValueExplicit('J'.$row, '=SUM(J8:J'.($row-1).')', PHPExcel_Cell_DataType::TYPE_FORMULA);
         $sheet->setCellValueExplicit('L'.$row, '=SUM(L8:L'.($row-1).')', PHPExcel_Cell_DataType::TYPE_FORMULA);
         $sheet->setCellValueExplicit('N'.$row, '=SUM(N8:N'.($row-1).')', PHPExcel_Cell_DataType::TYPE_FORMULA);
         $sheet->setCellValueExplicit('P'.$row, '=SUM(P8:P'.($row-1).')', PHPExcel_Cell_DataType::TYPE_FORMULA);
         $sheet->setCellValueExplicit('R'.$row, '=SUM(R8:R'.($row-1).')', PHPExcel_Cell_DataType::TYPE_FORMULA);
         $sheet->setCellValueExplicit('T'.$row, '=SUM(T8:T'.($row-1).')', PHPExcel_Cell_DataType::TYPE_FORMULA);
         $sheet->setCellValueExplicit('V'.$row, '=SUM(V8:V'.($row-1).')', PHPExcel_Cell_DataType::TYPE_FORMULA);
         $sheet->setCellValueExplicit('X'.$row, '=SUM(X8:X'.($row-1).')', PHPExcel_Cell_DataType::TYPE_FORMULA);
         $sheet->setCellValueExplicit('Z'.$row, '=SUM(Z8:Z'.($row-1).')', PHPExcel_Cell_DataType::TYPE_FORMULA);
         $sheet->setCellValueExplicit('AB'.$row, '=SUM(AB8:AB'.($row-1).')', PHPExcel_Cell_DataType::TYPE_FORMULA);
         $sheet->setCellValueExplicit('AD'.$row, '=SUM(AD8:AD'.($row-1).')', PHPExcel_Cell_DataType::TYPE_FORMULA);
         $sheet->getStyle('E'.$row.':AD'.$row)->getNumberFormat()->setFormatCode('#,##0_);[RED](#,##0)');
      }

      # Save Excel document to local hard disk
      $this->Save();
   }
}
?>