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

class ViewExcelLapAktivitas extends XlsxResponse {
    # Internal Variables
    protected $mObj;

    public $Excel;

    function ProcessRequest() {
        $this->mObj = new AppLapAktivitas();
        $this->mObj->Setup();

        $get =  is_object($_GET) ? $_GET->AsArray() : $_GET;
        $tglAwal =  Dispatcher::Instance()->Decrypt($get['tanggal_awal']);
        $tglAkhir =  Dispatcher::Instance()->Decrypt($get['tanggal_akhir']);
        $subAccount = Dispatcher::Instance()->Decrypt($get['sub_account']);

        if($subAccount == '01-00-00-00-00-00-00'){
            $header = strtoupper(GTFWConfiguration::GetValue('organization', 'header_lap_yayasan'));
        }elseif($subAccount == '00-00-00-00-00-00-00'){
            $header = strtoupper(GTFWConfiguration::GetValue('organization', 'header_lap_perbanas'));
        }else{
            $header = strtoupper(GTFWConfiguration::GetValue('organization', 'company_name'));
        }

        $this->mObj->LaporanBuilder()->PrepareData($tglAwal, $tglAkhir,$subAccount);
        $dataAktivitas = $this->mObj->LaporanBuilder()->laporanView();

        # set writer for XlsxResponse
        # default Excel5 for .xls extension
        # option Excel2007 for .xlsx extension
        # $this->SetWriter('Excel2007');
        $this->SetFileName('laporan_penghasilan_komprehensif_' . date("d-m-Y") . '.xls');

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

        if (empty($dataAktivitas)) {
            $sheet->setCellValue('A1', GTFWConfiguration::GetValue('language', 'data_kosong'));
        } else {
            $sheet->GetColumnDimension('A')->setWidth(50);
            $sheet->GetColumnDimension('B')->setWidth(20);

            $sheet->GetRowDimension(1)->setRowHeight(20);
            #set header
            $sheet->setCellValueByColumnAndRow(0, 1, $header);
            $sheet->mergeCells('A1:B1');
            $sheet->setCellValueByColumnAndRow(0, 2, 'Laporan Aktivitas');
            $sheet->mergeCells('A2:B2');
            $sheet->setCellValueByColumnAndRow(0, 3, 'Interval waktu ' . IndonesianDate($tglAwal, 'yyyy-mm-dd') . ' s/d ' . IndonesianDate($tglAkhir, 'yyyy-mm-dd'));
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

           
            $row = 6;
            $space = " ";

            foreach ($dataAktivitas as $itemLaporan) {
                $itemLaporan['nama'] = str_repeat($space,$itemLaporan['level']-1).$itemLaporan['nama'];
                if ($itemLaporan['is_summary'] == 'Y') {
                    $pengali = 1;
                    $jumlahSaldoKlp = $itemLaporan['saldo_summary'] * $pengali;
                    $sheet->getStyle('A'.$row.':B'.$row)->applyFromArray(array(
                        'font' => array(
                            'bold' => true
                        ), 'borders' => array(
                            'top' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            ),'bottom' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THICK
                            )
                        )
                    ));
                    $sheet->getStyle('B'.$row)->getNumberFormat()->setFormatCode('_("Rp"* #,##0_);_("Rp"* \(#,##0\);_("Rp"* "-"_);_(@_)');
                } else {
                    $pengali = ($itemLaporan['is_tambah'] == 'T') ? -1 : 1;
                    $jumlahSaldoKlp = $itemLaporan['saldo'] * $pengali;
        
                    if ($itemLaporan['is_child'] == '0') {
                        $sheet->mergeCells('A'.$row.':B'.$row);
                        switch ($itemLaporan['level']) {
                            case '2':
                                $title = strtoupper($itemLaporan['nama']);
                                $sheet->getStyle('A'.$row.':B'.$row)->applyFromArray(array(
                                'font' => array(
                                    'bold' => true
                                ), 'borders' => array(
                                    'bottom' => array(
                                        'style' => PHPExcel_Style_Border::BORDER_DOUBLE
                                    ), 'top' => array(
                                        'style' => PHPExcel_Style_Border::BORDER_THICK
                                    )
                                )
                                ));
                                
                                break;
                            default : 
                                $title = str_repeat($space,$itemLaporan['level']-2).$itemLaporan['nama'];
                                $sheet->getStyle('A'.$row.':B'.$row)->applyFromArray(array(
                                'font' => array(
                                    'bold' => true
                                ), 'borders' => array(
                                    'bottom' => array(
                                        'style' => PHPExcel_Style_Border::BORDER_THICK
                                    )
                                )
                                ));
                                break;
                        }
        
                        $itemLaporan['nama'] = $title;
                    } else {
                        $itemLaporan['nama'] = str_repeat($space,$itemLaporan['level']-1).$itemLaporan['nama'];
                        $sheet->getStyle('B'.$row)->getNumberFormat()->setFormatCode('_("Rp"* #,##0_);_("Rp"* \(#,##0\);_("Rp"* "-"_);_(@_)');
                    }
                }
                $sheet->setCellValueByColumnAndRow(0, $row, $itemLaporan['nama']);
                $sheet->setCellValueByColumnAndRow(1, $row, $jumlahSaldoKlp);
                $row++;
            }
        }
        # Save Excel document to local hard disk
        $this->Save();
    }

}

?>