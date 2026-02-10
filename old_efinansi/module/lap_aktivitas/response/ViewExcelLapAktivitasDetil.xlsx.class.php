<?php

/**
 * ================= doc ====================
 * FILENAME     : ViewExcelLapAktivitas.xlsx.class.php
 * @package     : ViewExcelLapAktivitas
 * scope        : PUBLIC
 * @Author      : Eko Susilo
 * @Created     : 2015-05-25
 * @Modified    : 2015-05-25
 * @Analysts    : Dyah Fajar N
 * @contact     : eko.susilo@gamatechno.com
 * @copyright   : Copyright (c) 2012 Gamatechno
 * ================= doc ====================
 */
require_once GTFWConfiguration::GetValue('application', 'docroot') .
        'module/lap_aktivitas/business/AppLapAktifitas.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') .
        'main/function/date.php';

class ViewExcelLapAktivitasDetil extends XlsxResponse {
    # Internal Variables

    public $Excel;

    function ProcessRequest() {
        $Obj = new AppLapAktivitas();
        $get = $_GET->AsArray();
        $get_date = getdate();
        $curr_mon = (int) $get_date['mon'];
        $curr_year = (int) $get_date['year'];
        $curr_day = (int) $get_date['mday'];

        if (!empty($get['tgl_awal'])) {
            $tglAwal = $get['tgl_awal'];
        } else {
            $tglAwal = date("Y-m-d", mktime(0, 0, 0, 1, 1, $curr_year));
        }

        if (!empty($get['tgl_akhir'])) {
            $tgl = $get['tgl_akhir'];
        } else {
            $tgl = date("Y-m-d", time());
        }
        $gridList = $Obj->GetLaporanAllDetil($tglAwal, $tgl);
        $tgl_akhir = $tgl;
        # set writer for XlsxResponse
        # default Excel5 for .xls extension
        # option Excel2007 for .xlsx extension
        # $this->SetWriter('Excel2007');
        $this->SetFileName('laporan_aktivitas_detil.xls');

        # Write your code here
        # Get active sheet
        # Document Setting
        $sheet = $this->Excel->getActiveSheet(0);
        $sheet->setShowGridlines(false); # false: Hide the gridlines; true: show the gridlines
        # set worksheet name
        $sheet->setTitle('Laporan Aktivitas');
        # set font default
        $sheet->getDefaultStyle()->getFont()->setName('Courier New')->setSize('9');
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

        if (empty($gridList)) {
            $sheet->setCellValue('A1', GTFWConfiguration::GetValue('language', 'data_kosong'));
        } else {
            $sheet->GetColumnDimension('A')->setWidth(50);
            $sheet->GetColumnDimension('B')->setWidth(20);

            $sheet->GetRowDimension(1)->setRowHeight(20);
            #set header
            $sheet->setCellValueByColumnAndRow(0, 1, GTFWConfiguration::GetValue('organization', 'company_name'));
            $sheet->mergeCells('A1:B1');
            $sheet->setCellValueByColumnAndRow(0, 2, 'Laporan Aktivitas');
            $sheet->mergeCells('A2:B2');
            $sheet->setCellValueByColumnAndRow(0, 3, 'Interval waktu ' . IndonesianDate($tglAwal, 'yyyy-mm-dd') . ' s/d ' . IndonesianDate($tgl_akhir, 'yyyy-mm-dd'));
            $sheet->mergeCells('A3:B3');
            $sheet->setCellValueByColumnAndRow(0, 4, '(Dinyatakan dalam Rupiah kecuali dinyatakan lain)');
            $sheet->mergeCells('A4:B4');
            $sheet->getStyle('A1:B1')->getFont()->setSize(11);
            $sheet->getStyle('A1:B4')->applyFromArray(array(
                'font' => array(
                    'bold' => true
                ), 'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                    'wrap' => true,
                    'shrinkToFit' => true
                )
            ));

            //inisialisasi variable array
            $pendapatan = array();
            $beban = array();

            foreach ($gridList as $key => $value) {
                
                //if ($value['status'] == 'Ya') {
                if ($value['kelJnsNama'] == 'Pendapatan') {
                    if ($value['saldo_normal'] == 'D') {
                        $nilai = 0 - $value['nilai'];
                    } else {
                        $nilai = $value['nilai'];
                    }
                    $totalPerKelJns[$value['kellapId']] += $nilai;
                    $pendapatan[$value['kelJnsNama']][] = array(
                        "nama_kel_lap" => $value['nama_kel_lap'],
                        "kode_coa" => $value['kode_coa'],
                        "nama_coa" => $value['nama_coa'],
                        "nilai" => $nilai,
                        "kelJnsNama" => $value['kelJnsNama'],
                        "kellapId" => $value['kellapId']
                    );
                } else {
                    if ($value['saldo_normal'] == 'K') {
                        $nilai = 0 - $value['nilai'];
                    } else {
                        $nilai = $value['nilai'];
                    }
                    $totalPerKelJns[$value['kellapId']] += $nilai;
                    $beban[$value['kelJnsNama']][] = array(
                        "nama_kel_lap" => $value['nama_kel_lap'],
                        "kode_coa" => $value['kode_coa'],
                        "nama_coa" => $value['nama_coa'],
                        "nilai" => $nilai,
                        "kelJnsNama" => $value['kelJnsNama'],
                        "kellapId" => $value['kellapId']
                    );
                }
            }
            $totalPendapatan = 0;
            $totalBiaya = 0;

            $row = 6;
            $sheet->setCellValueByColumnAndRow(0, $row, GTFWConfiguration::GetValue('language', 'pendapatan'));
            $sheet->mergeCells('A' . $row . ':B' . $row);
            $sheet->GetRowDimension($row)->setRowHeight(18);
            $sheet->getStyle('A' . $row . ':B' . $row)->applyFromArray(array(
                'font' => array(
                    'bold' => true,
                    'size' => 10
                ), 'borders' => array(
                    'bottom' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THICK
                    )
                ), 'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                )
            ));
            $row+=1;
            if (!empty($pendapatan)) {
                foreach ($pendapatan as $key => $value) {
                    $jmlPendapatan = 0;
                    $sheet->setCellValueByColumnAndRow(0, $row, $key);
                    $sheet->setCellValueByColumnAndRow(1, $row, GTFWConfiguration::GetValue('language', 'jumlah_rp'));
                    $sheet->getStyle('A' . $row . ':B' . $row)->applyFromArray(array(
                        'font' => array(
                            'bold' => true,
                        ), 'borders' => array(
                            'bottom' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            )
                        ), 'alignment' => array(
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                        )
                    ));
                    //$row++;
                    /*
                    foreach ($value as $detilKey => $detilValue) {
                        $sheet->setCellValueByColumnAndRow(0, $row, $detilValue['nama_kel_lap']);
                        $sheet->getCellByColumnAndRow(1, $row)->setValueExplicit($detilValue['nilai'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                        $sheet->getStyle('B' . $row)->getNumberFormat()->setFormatCode('_("Rp"* #,##0_);_("Rp"* \(#,##0\);_("Rp"* "-"_);_(@_)');
                        $jmlPendapatan+= $detilValue['nilai'];
                        $row++;
                    }*/
                    $rAwal += 1;
                    $kelapId = null;
                    for ($k = 0; $k < sizeof($value); ) {           
                        if($value[$k]['kellapId'] ==  $kelapId) {                   
                            $row++;
                            $jmlPendapatan+= $value[$k]['nilai'];
                            $sheet->setCellValueByColumnAndRow(0, $row, $value[$k]['kode_coa']. ' - '.$value[$k]['nama_coa']);
                            $sheet->getCellByColumnAndRow(1, $row)->setValueExplicit($value[$k]['nilai'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                            $k++;
                        } elseif($kelapId != $value[$k]['kellapId'])  {
                            $kelapId =  $value[$k]['kellapId'];                    
                            $row++;
                            $sheet->setCellValueByColumnAndRow(0, $row, $value[$k]['nama_kel_lap']);
                            if(array_key_exists($kelapId, $totalPerKelJns)) {
                                $nilai =  $totalPerKelJns[$kelapId];
                            } else {
                                $nilai = 0;
                            }
                            $sheet->getCellByColumnAndRow(1, $row)->setValueExplicit($nilai, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                            $sheet->getStyle('A' . $row . ':B' . $row)->getFont()->setBold(true);
                        }                
                    }
                    
                    $sheet->setCellValueByColumnAndRow(0, $row, "Total " . $key);
                    $sheet->getCellByColumnAndRow(1, $row)->setValueExplicit($jmlPendapatan, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                    $sheet->getStyle('B'.$rAwal.':B'.$row)->getNumberFormat()->setFormatCode('_("Rp"* #,##0_);_("Rp"* \(#,##0\);_("Rp"* "-"_);_(@_)');
                    
                    $sheet->getStyle('A' . $row . ':B' . $row)->applyFromArray(array(
                        'font' => array(
                            'bold' => true,
                        ), 'borders' => array(
                            'bottom' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            ), 'top' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            )
                        )
                    ));
                    $row++;
                    $totalPendapatan+=$jmlPendapatan;
                }
            }
            $sheet->setCellValueByColumnAndRow(0, $row, GTFWConfiguration::GetValue('language', 'total_pendapatan'));
            $sheet->getCellByColumnAndRow(1, $row)->setValueExplicit($totalPendapatan, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->GetRowDimension($row)->setRowHeight(20);
            $sheet->getStyle('A' . $row . ':B' . $row)->applyFromArray(array(
                'borders' => array(
                    'top' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THICK
                    ), 'bottom' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THICK
                    )
                ), 'font' => array(
                    'bold' => true
                ), 'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                )
            ));

            $sheet->getStyle('B' . $row)->applyFromArray(array(
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT
                ), 'numberformat' => array(
                    'code' => '_("Rp"* #,##0_);_("Rp"* \(#,##0\);_("Rp"* "-"_);_(@_)'
                )
            ));

            $row+= 2;
            $sheet->setCellValueByColumnAndRow(0, $row, GTFWConfiguration::GetValue('language', 'beban'));
            $sheet->getStyle('A' . $row . ':B' . $row)->applyFromArray(array(
                'font' => array(
                    'bold' => true,
                    'size' => 10
                ), 'borders' => array(
                    'bottom' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THICK
                    )
                ), 'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                )
            ));
            $row+=1;
            $totalBeban = 0;
            if (!(empty($beban))) {
                foreach ($beban as $key => $value) {
                    $jmlBeban = 0;
                    $sheet->setCellValueByColumnAndRow(0, $row, $key);
                    $sheet->setCellValueByColumnAndRow(1, $row, GTFWConfiguration::GetValue('language', 'jumlah_rp'));
                    $sheet->getStyle('A' . $row . ':B' . $row)->applyFromArray(array(
                        'font' => array(
                            'bold' => true,
                        ), 'borders' => array(
                            'bottom' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            )
                        ), 'alignment' => array(
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                        )
                    ));
                    /*
                    $row++;
                    foreach ($value as $detilKey => $detilValue) {
                        $sheet->setCellValueByColumnAndRow(0, $row, $detilValue['nama_kel_lap']);
                        $sheet->getCellByColumnAndRow(1, $row)->setValueExplicit($detilValue['nilai'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                        $sheet->getStyle('B' . $row)->getNumberFormat()->setFormatCode('_("Rp"* #,##0_);_("Rp"* \(#,##0\);_("Rp"* "-"_);_(@_)');
                        $jmlBeban+= $detilValue['nilai'];
                        $row++;
                    }*/
                    $rAwal += 1;
                    $kelapId = null;
                    for ($k = 0; $k < sizeof($value); ) {           
                        if($value[$k]['kellapId'] ==  $kelapId) {                   
                            $row++;
                            $jmlBeban+= $value[$k]['nilai'];
                            $sheet->setCellValueByColumnAndRow(0, $row, $value[$k]['kode_coa']. ' - '.$value[$k]['nama_coa']);
                            $sheet->getCellByColumnAndRow(1, $row)->setValueExplicit($value[$k]['nilai'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                            $k++;
                        } elseif($kelapId != $value[$k]['kellapId'])  {
                            $kelapId =  $value[$k]['kellapId'];                    
                            $row++;
                            $sheet->setCellValueByColumnAndRow(0, $row, $value[$k]['nama_kel_lap']);
                            if(array_key_exists($kelapId, $totalPerKelJns)) {
                                $nilai =  $totalPerKelJns[$kelapId];
                            } else {
                                $nilai = 0;
                            }
                            $sheet->getCellByColumnAndRow(1, $row)->setValueExplicit($nilai, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                            $sheet->getStyle('A' . $row . ':B' . $row)->getFont()->setBold(true);
                        }                
                    }
                    
                    $sheet->setCellValueByColumnAndRow(0, $row, "Total " . $key);
                    $sheet->getCellByColumnAndRow(1, $row)->setValueExplicit($jmlBeban, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                    $sheet->getStyle('B'. $rAwal.':B'.$row)->getNumberFormat()->setFormatCode('_("Rp"* #,##0_);_("Rp"* \(#,##0\);_("Rp"* "-"_);_(@_)');
                    $sheet->getStyle('A' . $row . ':B' . $row)->applyFromArray(array(
                        'font' => array(
                            'bold' => true,
                        ), 'borders' => array(
                            'bottom' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            ), 'top' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            )
                        )
                    ));
                    $row++;
                    $totalBeban+=$jmlBeban;
                }
            }
            $sheet->setCellValueByColumnAndRow(0, $row, GTFWConfiguration::GetValue('language', 'total_beban'));
            $sheet->getCellByColumnAndRow(1, $row)->setValueExplicit($totalBeban, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->GetRowDimension($row)->setRowHeight(20);
            $sheet->getStyle('A' . $row . ':B' . $row)->applyFromArray(array(
                'borders' => array(
                    'top' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THICK
                    ), 'bottom' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THICK
                    )
                ), 'font' => array(
                    'bold' => true
                ), 'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                )
            ));

            $sheet->getStyle('B' . $row)->applyFromArray(array(
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT
                ), 'numberformat' => array(
                    'code' => '_("Rp"* #,##0_);_("Rp"* \(#,##0\);_("Rp"* "-"_);_(@_)'
                )
            ));

            $row+=1;
            $sheet->setCellValueByColumnAndRow(0, $row, GTFWConfiguration::GetValue('language', 'kenaikan_aktiva_bersih'));
            $sheet->getCellByColumnAndRow(1, $row)->setValueExplicit(($totalPendapatan - $totalBeban), PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->GetRowDimension($row)->setRowHeight(20);
            $sheet->getStyle('A' . $row . ':B' . $row)->applyFromArray(array(
                'borders' => array(
                    'top' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THICK
                    ), 'bottom' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THICK
                    )
                ), 'font' => array(
                    'bold' => true
                ), 'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                )
            ));

            $sheet->getStyle('B' . $row)->applyFromArray(array(
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT
                ), 'numberformat' => array(
                    'code' => '_("Rp"* #,##0_);_("Rp"* \(#,##0\);_("Rp"* "-"_);_(@_)'
                )
            ));
        }
        # Save Excel document to local hard disk
        $this->Save();
    }

}

?>