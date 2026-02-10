<?php

/**
 * ================= doc ====================
 * FILENAME     : ViewExcelLaporanPosisiKeuangan.xlsx.class.php
 * @package     : ViewExcelLaporanPosisiKeuangan
 * scope        : PUBLIC
 * @Author      : Eko Susilo
 * @Created     : 2015-02-27
 * @Modified    : 2015-02-27
 * @Analysts    : Dyah Fajar N
 * @contact     : eko.susilo@gamatechno.com
 * @copyright   : Copyright (c) 2012 Gamatechno
 * ================= doc ====================
 */
require_once GTFWConfiguration::GetValue('application', 'docroot') .
        'module/lap_posisi_keuangan_sementara/business/LaporanPosisiKeuangan.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') .
'main/function/date.php';

class ViewExcelLaporanPosisiKeuangan extends XlsxResponse {
    # Internal Variables

    public $Excel;

    function ProcessRequest() {
        $mObj = new LaporanPosisiKeuangan();
        $requestData['start_date'] = date('Y-m-d', strtotime(Dispatcher::Instance()->Decrypt($mObj->_GET['start_date'])));
        $requestData['end_date'] = date('Y-m-d', strtotime(Dispatcher::Instance()->Decrypt($mObj->_GET['end_date'])));

        $gridList = $mObj->getDataLaporan($requestData);
        # set writer for XlsxResponse
        # default Excel5 for .xls extension
        # option Excel2007 for .xlsx extension
        # $this->SetWriter('Excel2007');
        $this->SetFileName('laporan_posisi_keuangan_sementara.xls');

        # Write your code here
        # Get active sheet
        # Document Setting
        $sheet = $this->Excel->getActiveSheet(0);
        $sheet->setShowGridlines(false); # false: Hide the gridlines; true: show the gridlines
        # set worksheet name
        $sheet->setTitle('balance_sheet');
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

        $bsId = '';
        $dataList = array();
        $index = '';
        $index = 0;
        $templates = array(
            15 => 'aktiva_lancar',
            16 => 'aktiva_tidak_lancar',
            17 => 'kewajiban_jangka_pendek',
            18 => 'kewajiban_jangka_panjang',
            19 => 'aktiva_bersih',
            23 => 'aktiva_lain'
        );

        // untuk menyimpan nominal balance sheet
        $balance_sheet = array();
        for ($i = 0; $i < count($gridList);) {
            if ($gridList[$i]['kellap_jns_id'] == $bsId) {
                $balance_sheet[$bsId]['current'] += $gridList[$i]['nominal'];
                $balance_sheet[$bsId]['debet'] += $gridList[$i]['nominal_debet'];
                $balance_sheet[$bsId]['kredit'] += $gridList[$i]['nominal_kredit'];

                $dataList[$bsId]['data'][$index]['id'] = $gridList[$i]['kellap_id'];
                $dataList[$bsId]['data'][$index]['nama'] = $gridList[$i]['kellap_nama'];
                $dataList[$bsId]['data'][$index]['nominal'] = $gridList[$i]['nominal'];
                $dataList[$bsId]['data'][$index]['debet'] = $gridList[$i]['nominal_debet'];
                $dataList[$bsId]['data'][$index]['kredit'] = $gridList[$i]['nominal_kredit'];
                $i++;
                $index+=1;
            } else {
                unset($index);
                $index = 0;
                $bsId = $gridList[$i]['kellap_jns_id'];
                unset($balance_sheet[$bsId]['current']);
                unset($balance_sheet[$bsId]['debet']);
                unset($balance_sheet[$bsId]['kredit']);
                $balance_sheet[$bsId]['current'] = 0;
                $balance_sheet[$bsId]['debet'] = 0;
                $balance_sheet[$bsId]['kredit'] = 0;

                $dataList[$bsId]['template'] = $templates[$bsId];
                $dataList[$bsId]['label'] = $gridList[$i]['kellap_jns_nama'];
                $dataList[$bsId]['id'] = $bsId;
            }
        }

        $companyName = GTFWConfiguration::GetValue('organization', 'company_name');
        $header = GTFWConfiguration::GetValue('language', 'laporan_posisi_keuangan_sementara');
        $sheet->setCellValueByColumnAndRow(1, 3, 'Untuk Interval Waktu Mulai ' . IndonesianDate($requestData['start_date'], 'yyyy-mm-dd').' s/d '.IndonesianDate($requestData['end_date'], 'yyyy-mm-dd'));
        $sheet->setCellValueByColumnAndRow(1, 4, '(Dinyatakan dalam Rupiah kecuali dinyatakan lain)');
        
        $headerTableStyledArray = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            ), 'font' => array(
                'size' => 10,
                'bold' => true
            )
        );

        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(4);
        $sheet->getColumnDimension('C')->setWidth(40);
        $sheet->getColumnDimension('D')->setWidth(5);
        $sheet->getColumnDimension('E')->setWidth(28);
        $sheet->getColumnDimension('F')->setWidth(28);

        $sheet->mergeCells('B2:F2');
        $sheet->mergeCells('B3:F3');
        $sheet->mergeCells('B5:D5');


        $sheet->setCellValue('B1', strtoupper($companyName));
        $sheet->getStyle('B1:F1')->getFont()->setSize(11)->setBold(true);
        $sheet->setCellValue('B2', strtoupper($header));
        $sheet->getStyle('B2:F2')->getFont()->setSize(10)->setBold(true);

       // $sheet->setCellValue('E5', strtoupper(GTFWConfiguration::GetValue('language', 'nominal_rp')));
        $sheet->getStyle('B3:E4')->applyFromArray($headerTableStyledArray);
        $sheet->getStyle('B6:E6')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THICK);
        $sheet->getStyle('B6:E6')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_DOUBLE);
        $sheet->getStyle('E5')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);


        $sheet->setCellValue('B6', GTFWConfiguration::GetValue('language', 'aset'));
        $sheet->mergeCells('B6:E6');
        $sheet->getStyle('B6:E6')->getFont()->setBold(true);
        $sheet->getStyle('B6:E6')->getFont()->setUnderLine(true);
        $sheet->getRowDimension(6)->setRowHeight(18);
        $sheet->getStyle('B6:E6')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

        $row = 7;
        $aktiva_lancar = $dataList[15];
        $aktiva_tidak_lancar = $dataList[16];
        $kewajiban_jangka_pendek = $dataList[17];
        $kewajiban_jangka_panjang = $dataList[18];
        $aktiva_bersih = $dataList[19];
        $aset_lain = $dataList[23];

        $currentAktifa = $balance_sheet[15]['current'] + $balance_sheet[16]['current'] + $balance_sheet[19]['current'];
        $currentLiabilities = $balance_sheet[17]['current'] + $balance_sheet[18]['current'];

        $aktivaStartRow = $aktivaEndRow = $row;
        $isAktivaLancarEmpty = TRUE;
        $isAktivaTidakLancarEmpty = TRUE;
        $isAktivaBersih = TRUE;
        $isAsetLain = TRUE;
        $isKewajibanJangkaPendekEmpty = TRUE;
        $isKewajibanJangkaPanjangEmpty = TRUE;
/**/
        if (!empty($aktiva_lancar['data'])) {
            $isAktivaLancarEmpty = FALSE;
            $sheet->setCellValue('B' . $row, $aktiva_lancar['label']);
            $sheet->setCellValue('E' . $row,  GTFWConfiguration::GetValue('language','jumlah_rp'));
            $sheet->getStyle('E' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('B' . $row . ':E' . $row)->getFont()->setBold(true);
            $sheet->getStyle('B' . $row . ':E' . $row)->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THICK);

            
            $row += 1;
            $aktivaStartRow = $row;
            if(!empty($aktiva_lancar['data'])) {
                foreach ($aktiva_lancar['data'] as $aktiva_list) {
                    $sheet->setCellValue('B' . $row, $aktiva_list['nama']);
                    $sheet->setCellValue('E' . $row, $aktiva_list['nominal']);
                    $row++;
                }
                $jumlahAktivaLancar = '=SUM(E' . $aktivaStartRow . ':E' .($row - 1). ')';
            } else {
                $jumlahAktivaLancar = 0;
            }    
            $aktivaEndRow = $row;
            $sheet->setCellValue('B' . $aktivaEndRow, GTFWConfiguration::GetValue('language', 'jumlah') . ' ' . $aktiva_lancar['label']);
            $sheet->mergeCells('B' . $aktivaEndRow . ':C' . $aktivaEndRow);
            $sheet->setCellValue('E' . $aktivaEndRow,$jumlahAktivaLancar);
            // $sheet->setCellValue('F'. $aktivaEndRow, '=SUM(F'.$aktivaStartRow.':F'.($aktivaEndRow-1).')');
            $sheet->getStyle('E' . $aktivaStartRow . ':E' . $aktivaEndRow)->getNumberFormat()->setFormatCode('_("Rp"* #,##0_);_("Rp"* \(#,##0\);_("Rp"* "-"_);_(@_)');

            $sheet->getStyle('B' . $aktivaEndRow . ':E' . $aktivaEndRow)->getFont()->setBold(true);
            $sheet->getStyle('B' . $aktivaEndRow . ':E' . $aktivaEndRow)->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
        }

        $aktivaTidakLancarStartRow = $aktivaTidakLancarEndRow = $row;
        if (!empty($aktiva_tidak_lancar['data'])) {
            $isAktivaTidakLancarEmpty = FALSE;
            $row = $aktivaEndRow + 2;
            $sheet->setCellValue('B' . $row, $aktiva_tidak_lancar['label']);
            $sheet->setCellValue('E' . $row,  GTFWConfiguration::GetValue('language','jumlah_rp'));
            $sheet->getStyle('E' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('B' . $row . ':E' . $row)->getFont()->setBold(true);
            $sheet->getStyle('B' . $row . ':E' . $row)->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THICK);

            
            $row += 1;
            $aktivaTidakLancarStartRow = $row;
            unset($list);
            if(!empty($aktiva_tidak_lancar['data'])){
                foreach ($aktiva_tidak_lancar['data'] as $list) {
                    $sheet->setCellValue('B' . $row, $list['nama']);
                    $sheet->setCellValue('E' . $row, $list['nominal']);
                    $row++;
                }
                
                $jumlahAktivaTidakLancar = '=SUM(E' . $aktivaTidakLancarStartRow . ':E' . ($row - 1) . ')';
            } else {                
                $jumlahAktivaTidakLancar = 0;
            }
            
            $aktivaTidakLancarEndRow = $row;
            $sheet->setCellValue('B' . $aktivaTidakLancarEndRow, GTFWConfiguration::GetValue('language', 'jumlah') . ' ' . $aktiva_tidak_lancar['label']);
            $sheet->mergeCells('B' . $aktivaTidakLancarEndRow . ':C' . $aktivaTidakLancarEndRow);
            $sheet->setCellValue('E' . $aktivaTidakLancarEndRow, $jumlahAktivaTidakLancar);
            // $sheet->setCellValue('F'. $aktivaTidakLancarEndRow, '=SUM(F'.$aktivaTidakLancarStartRow.':F'.($aktivaTidakLancarEndRow-1).')');
            $sheet->getStyle('E' . $aktivaTidakLancarStartRow . ':E' . $aktivaTidakLancarEndRow)->getNumberFormat()->setFormatCode('_("Rp"* #,##0_);_("Rp"* \(#,##0\);_("Rp"* "-"_);_(@_)');

            $sheet->getStyle('B' . $aktivaTidakLancarEndRow . ':E' . $aktivaTidakLancarEndRow)->getFont()->setBold(true);
            $sheet->getStyle('B' . $aktivaTidakLancarEndRow . ':E' . $aktivaTidakLancarEndRow)->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
        } else {
            $aktivaTidakLancarEndRow = $row ;
        }

        $asetLainStartRow = $asetLainEndRow = $row;
        if (!empty($aset_lain['data'])) {
            $isAsetLain = FALSE;
            $row = $aktivaTidakLancarEndRow + 2;
            $sheet->setCellValue('B' . $row, $aset_lain['label']);
            $sheet->setCellValue('E' . $row,  GTFWConfiguration::GetValue('language','jumlah_rp'));
            $sheet->getStyle('E' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            
            $sheet->getStyle('B' . $row . ':E' . $row)->getFont()->setBold(true);
            $sheet->getStyle('B' . $row . ':E' . $row)->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THICK);

            $row += 1;
            $asetLainStartRow = $row;
            unset($list);
            if(!empty($aset_lain['data'] )) {
                foreach ($aset_lain['data'] as $list) {
                    $sheet->setCellValue('B' . $row, $list['nama']);
                    $sheet->setCellValue('E' . $row, $list['nominal']);
                    $row++;
                }
                
                $jumlahAsetLain = '=SUM(E' . $asetLainStartRow . ':E' . ($row - 1) . ')';
            } else {
                
                $jumlahAsetLain = 0;
            }
            
            $asetLainEndRow = $row;
            $sheet->setCellValue('B' . $asetLainEndRow, GTFWConfiguration::GetValue('language', 'jumlah') . ' ' . $aset_lain['label']);
            $sheet->mergeCells('B' . $asetLainEndRow . ':C' . $asetLainEndRow);
            $sheet->setCellValue('E' . $asetLainEndRow,$jumlahAsetLain);
            // // $sheet->setCellValue('F'. $asetLainEndRow, '=SUM(F'.$asetLainStartRow.':F'.($asetLainEndRow-1).')');
            $sheet->getStyle('E' . $asetLainStartRow . ':E' . $asetLainEndRow)->getNumberFormat()->setFormatCode('_("Rp"* #,##0_);_("Rp"* \(#,##0\);_("Rp"* "-"_);_(@_)');

            $sheet->getStyle('B' . $asetLainEndRow . ':E' . $asetLainEndRow)->getFont()->setBold(true);
            $sheet->getStyle('B' . $asetLainEndRow . ':E' . $asetLainEndRow)->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
        } else {
            $asetLainEndRow = $row;
        }

        $row = $asetLainEndRow + 2;
        $sheet->setCellValue('B' . ($row ), GTFWConfiguration::GetValue('language', 'jumlah_aktiva'));
        $sheet->mergeCells('B' . ($row ) . ':C' . ($row ));
        //hitung total
        $formulaTotalAset = array();
        if ($isAktivaLancarEmpty === FALSE) {
            $formulaTotalAset[] = 'E' . $aktivaEndRow;
        }
        if ($isAktivaTidakLancarEmpty === FALSE) {
            $formulaTotalAset[] = 'E' . $aktivaTidakLancarEndRow;
        }
        if ($isAsetLain === FALSE) {
            $formulaTotalAset[] = 'E' . $asetLainEndRow;
        }
        $formulaTotalAsetStmt = '=(' . implode('+', $formulaTotalAset) . ')';
        //end
        $sheet->setCellValue('E' . ($row), $formulaTotalAsetStmt);
        $sheet->getStyle('E' . ($row) . ':E' . ($row) . '')->getNumberFormat()->setFormatCode('_("Rp"* #,##0_);_("Rp"* \(#,##0\);_("Rp"* "-"_);_(@_)');

        $sheet->getStyle('B' . ($row) . ':E' . ($row))->getFont()->setBold(true)->setSize(10);
        $sheet->getStyle('B' . ($row) . ':E' . ($row))->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THICK);
        $sheet->getStyle('B' . ($row) . ':E' . ($row))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THICK);
        $sheet->getRowDimension(($row))->setRowHeight(16);
        $sheet->getStyle('B' . ($row) . ':E' . ($row))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        /* */
        
        /**
         * kelompok kewajiban
         */
        $row += 2;
        $sheet->setCellValue('B' . $row, GTFWConfiguration::GetValue('language', 'kewajiban_dan_aktiva_bersih'));
        $sheet->mergeCells('B' . $row . ':E' . $row);
        $sheet->getStyle('B' . $row . ':E' . $row)->getFont()->setBold(true);
        $sheet->getStyle('B' . $row . ':E' . $row)->getFont()->setUnderLine(true);
        $sheet->getRowDimension($row)->setRowHeight(18);
        $sheet->getStyle('B' . $row . ':E' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $sheet->getStyle('B' . $row . ':E' . $row)->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THICK);
        $sheet->getStyle('B' . $row . ':E' . $row)->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_DOUBLE);
        $row += 1;
        $sortLiabilitiesStartRow = $sortLiabilitiesEndRow = $row;
        if (!empty($kewajiban_jangka_pendek['data'])) {
            $isKewajibanJangkaPendekEmpty = FALSE;
            $sheet->setCellValue('B' . $row, $kewajiban_jangka_pendek['label']);
            $sheet->setCellValue('E' . $row,  GTFWConfiguration::GetValue('language','jumlah_rp'));
            $sheet->getStyle('E' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('B' . $row . ':E' . $row)->getFont()->setBold(true);
            $sheet->getStyle('B' . $row . ':E' . $row)->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THICK);

            $row += 1;
            $sortLiabilitiesStartRow = $row;
            unset($list);
            foreach ($kewajiban_jangka_pendek['data'] as $list) {
                $sheet->setCellValue('B' . $row, $list['nama']);
                $sheet->setCellValue('E' . $row, $list['nominal']);
                // $sheet->setCellValue('F'. $row, $list['p_nominal']);
                $row++;
            }
            $sortLiabilitiesEndRow = $row;
            $sheet->setCellValue('B' . $sortLiabilitiesEndRow, GTFWConfiguration::GetValue('language', 'jumlah') . ' ' . $kewajiban_jangka_pendek['label']);
            $sheet->mergeCells('B' . $sortLiabilitiesEndRow . ':C' . $sortLiabilitiesEndRow);
            $sheet->setCellValue('E' . $sortLiabilitiesEndRow, '=SUM(E' . $sortLiabilitiesStartRow . ':E' . ($sortLiabilitiesEndRow - 1) . ')');
            // $sheet->setCellValue('F'. $sortLiabilitiesEndRow, '=SUM(F'.$sortLiabilitiesStartRow.':F'.($sortLiabilitiesEndRow-1).')');
            $sheet->getStyle('E' . $sortLiabilitiesStartRow . ':E' . $sortLiabilitiesEndRow)->getNumberFormat()->setFormatCode('_("Rp"* #,##0_);_("Rp"* \(#,##0\);_("Rp"* "-"_);_(@_)');

            $sheet->getStyle('B' . $sortLiabilitiesEndRow . ':E' . $sortLiabilitiesEndRow)->getFont()->setBold(true);
            $sheet->getStyle('B' . $sortLiabilitiesEndRow . ':E' . $sortLiabilitiesEndRow)->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
        } else {
            $sortLiabilitiesEndRow = $row;
        }

        $row = $sortLiabilitiesEndRow + 2;
        $longLiabilitiesStartRow = $longLiabilitiesEndRow = $row;
        if (!empty($kewajiban_jangka_panjang['data'])) {
            $isKewajibanJangkaPanjangEmpty = FALSE;
            $sheet->setCellValue('B' . $row, $kewajiban_jangka_panjang['label']);
            $sheet->setCellValue('E' . $row,  GTFWConfiguration::GetValue('language','jumlah_rp'));
            $sheet->getStyle('E' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('B' . $row . ':E' . $row)->getFont()->setBold(true);
            $sheet->getStyle('B' . $row . ':E' . $row)->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THICK);

            $row += 1;
            $longLiabilitiesStartRow = $row;
            unset($list);
            foreach ($kewajiban_jangka_panjang['data'] as $list) {
                $sheet->setCellValue('B' . $row, $list['nama']);
                $sheet->setCellValue('E' . $row, $list['nominal']);
                // $sheet->setCellValue('F'. $row, $list['p_nominal']);
                $row++;
            }
            $longLiabilitiesEndRow = $row;
            $sheet->setCellValue('B' . $longLiabilitiesEndRow, GTFWConfiguration::GetValue('language', 'jumlah') . ' ' . $kewajiban_jangka_panjang['label']);
            $sheet->mergeCells('B' . $longLiabilitiesEndRow . ':C' . $longLiabilitiesEndRow);
            $sheet->setCellValue('E' . $longLiabilitiesEndRow, '=SUM(E' . $longLiabilitiesStartRow . ':E' . ($longLiabilitiesEndRow - 1) . ')');
            // $sheet->setCellValue('F'. $longLiabilitiesEndRow, '=SUM(F'.$longLiabilitiesStartRow.':F'.($longLiabilitiesEndRow-1).')');
            $sheet->getStyle('E' . $longLiabilitiesStartRow . ':E' . $longLiabilitiesEndRow)->getNumberFormat()->setFormatCode('_("Rp"* #,##0_);_("Rp"* \(#,##0\);_("Rp"* "-"_);_(@_)');

            $sheet->getStyle('B' . $longLiabilitiesEndRow . ':E' . $longLiabilitiesEndRow)->getFont()->setBold(true);
            $sheet->getStyle('B' . $longLiabilitiesEndRow . ':E' . $longLiabilitiesEndRow)->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
        } else {
            $longLiabilitiesEndRow = $row;
        }

        $row = $longLiabilitiesEndRow + 2;
        $aktivaBersihStartRow = $aktivaBersihEndRow = $row;
        if (!empty($aktiva_bersih['data'])) {
            $isAktivaBersih = FALSE;
            // $row        = $aktivaEndRow+2;
            $sheet->setCellValue('B' . $row, $aktiva_bersih['label']);
            $sheet->setCellValue('E' . $row,  GTFWConfiguration::GetValue('language','jumlah_rp'));
            $sheet->getStyle('E' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('B' . $row . ':E' . $row)->getFont()->setBold(true);
            $sheet->getStyle('B' . $row . ':E' . $row)->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THICK);

            $row += 1;
            $aktivaBersihStartRow = $row;
            unset($list);
            foreach ($aktiva_bersih['data'] as $list) {
                $sheet->setCellValue('B' . $row, $list['nama']);
                $sheet->setCellValue('E' . $row, $list['nominal']);
                // $sheet->setCellValue('F'. $row, $list['p_nominal']);
                $row++;
            }
            $aktivaBersihEndRow = $row;
            $sheet->setCellValue('B' . $aktivaBersihEndRow, GTFWConfiguration::GetValue('language', 'jumlah') . ' ' . $aktiva_bersih['label']);
            $sheet->mergeCells('B' . $aktivaBersihEndRow . ':C' . $aktivaBersihEndRow);
            $sheet->setCellValue('E' . $aktivaBersihEndRow, '=SUM(E' . $aktivaBersihStartRow . ':E' . ($aktivaBersihEndRow - 1) . ')');
            // $sheet->setCellValue('F'. $aktivaBersihEndRow, '=SUM(F'.$aktivaBersihStartRow.':F'.($aktivaBersihEndRow-1).')');
            $sheet->getStyle('E' . $aktivaBersihStartRow . ':E' . $aktivaBersihEndRow)->getNumberFormat()->setFormatCode('_("Rp"* #,##0_);_("Rp"* \(#,##0\);_("Rp"* "-"_);_(@_)');

            $sheet->getStyle('B' . $aktivaBersihEndRow . ':E' . $aktivaBersihEndRow)->getFont()->setBold(true);
            $sheet->getStyle('B' . $aktivaBersihEndRow . ':E' . $aktivaBersihEndRow)->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
        } else {
            $aktivaBersihEndRow = $row;
        }
        
        $row = $aktivaBersihEndRow + 2;
        $sheet->setCellValue('B' . ($row), GTFWConfiguration::GetValue('language', 'jumlah_kewajiban_dan_aktiva_bersih'));
        $sheet->mergeCells('B' . ($row) . ':C' . ($row));
        $sheet->setCellValue('E' . ($row), '=SUM(E' . $sortLiabilitiesEndRow . '+E' . $longLiabilitiesEndRow . '+E' . $aktivaBersihEndRow . ')');
        $sheet->getStyle('E' . ($row) . ':E' . ($row) . '')->getNumberFormat()->setFormatCode('_("Rp"* #,##0_);_("Rp"* \(#,##0\);_("Rp"* "-"_);_(@_)');

        $sheet->getStyle('B' . ($row) . ':E' . ($row))->getFont()->setBold(true)->setSize(10);
        $sheet->getStyle('B' . ($row) . ':E' . ($row))->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THICK);
        $sheet->getStyle('B' . ($row) . ':E' . ($row))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THICK);
        $sheet->getRowDimension(($row))->setRowHeight(16);
        $sheet->getStyle('B' . ($row) . ':E' . ($row))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

        /*

          $row        = $aktivaBersihEndRow+4;





          $row  = $longLiabilitiesEndRow+1;

         */

        # Save Excel document to local hard disk
        $this->Save();
    }

}

?>