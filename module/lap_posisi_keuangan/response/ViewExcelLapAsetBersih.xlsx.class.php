<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
    'module/lap_posisi_keuangan/business/AppLapPosisiKeuangan.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') .
    'main/function/date.php';

class ViewExcelLapAsetBersih extends XlsxResponse
{
   # Internal Variables
    public $Excel;
    protected $mObj;

    function ProcessRequest() {
        $this->mObj = new AppLapPosisiKeuangan();
        #Id Aset Bersih
        $this->mObj->Setup(224);
        $tgl_awal   = Dispatcher::Instance()->Decrypt($_GET['tanggal_awal']);
        $tgl_akhir  = Dispatcher::Instance()->Decrypt($_GET['tanggal_akhir']);
        $subAccount = Dispatcher::Instance()->Decrypt($_GET['sub_account']);
        
        if($subAccount == '01-00-00-00-00-00-00'){
            $header = strtoupper(GTFWConfiguration::GetValue('organization', 'header_lap_yayasan'));
        }elseif($subAccount == '00-00-00-00-00-00-00'){
            $header = strtoupper(GTFWConfiguration::GetValue('organization', 'header_lap_perbanas'));
        }else{
            $header = strtoupper(GTFWConfiguration::GetValue('organization', 'company_name'));
        }

        $this->mObj->LaporanBuilder()->PrepareData($tgl_awal, $tgl_akhir,$subAccount);
        $asetBersih = $this->mObj->LaporanBuilder()->laporanView();

        # set writer for XlsxResponse
        # default Excel5 for .xls extension
        # option Excel2007 for .xlsx extension
        # $this->SetWriter('Excel2007');
        $this->SetFileName('laporan_aset_bersih_' . date("d-m-Y") . '.xls');

        # Write your code here
        # Get active sheet
        # Document Setting
        $sheet   = $this->Excel->getActiveSheet(0);
        $sheet->setShowGridlines(false); # false: Hide the gridlines; true: show the gridlines
        # set worksheet name
        $sheet->setTitle('BS');
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

        if (empty($asetBersih)) {
            $sheet->setCellValue('A1', GTFWConfiguration::GetValue('language', 'data_kosong'));
        } else {
            $sheet->getColumnDimension('A')->setWidth(80);
            $sheet->getColumnDimension('B')->setWidth(20);
            $sheet->getColumnDimension('C')->setWidth(20);

            $sheet->getRowDimension(1)->setRowHeight(18);
            $sheet->getRowDimension(2)->setRowHeight(16);
            $sheet->getRowDimension(3)->setRowHeight(16);
            $sheet->getRowDimension(4)->setRowHeight(16);

            $sheet->mergeCells('A1:B1');
            $sheet->mergeCells('A2:B2');
            $sheet->mergeCells('A3:B3');
            $sheet->mergeCells('A4:B4');
            $sheet->setCellValueByColumnAndRow(0, 1, $header);
            $sheet->setCellValueByColumnAndRow(0, 2, 'Laporan Aset Bersih');
            $sheet->setCellValueByColumnAndRow(0, 3,
                'Periode ' .
                IndonesianDate($tgl_awal, 'yyyy-mm-dd').' s/d '.IndonesianDate($tgl_akhir, 'yyyy-mm-dd')
            );
            $sheet->setCellValueByColumnAndRow(0, 4, '(Dinyatakan dalam Rupiah kecuali dinyatakan lain)');

            $sheet->getStyle('A1:B4')->applyFromArray(array(
                'font' => array(
                'bold' => true
                ), 'alignment' => array(
                'wrap' => true,
                'shrinkToFit' => true
                )
            ));

            $bulanIni = date("n", strtotime($tgl_akhir));
            $date = $tgl_akhir;
            $bulanLalu = date("n", strtotime("first day of $date -1 month"));

            $sheet->setCellValueByColumnAndRow(0, 6, 'URAIAN');
            $sheet->setCellValueByColumnAndRow(1, 6, $this->mObj->indonesianMonth[$bulanIni] );
            $sheet->setCellValueByColumnAndRow(2, 6, $this->mObj->indonesianMonth[$bulanLalu]);

            $sheet->getStyle('A6:C6')->applyFromArray(array(
                'font' => array(
                    'bold' => true
                ),
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'wrap' => true
                ),
                'borders' => array(
                    'top' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THICK
                    ),'bottom' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THICK
                    )
                )
            ));

            $formatRupiah = '_("Rp"* #,##0_);_("Rp"* \(#,##0\);_("Rp"* "-"_);_(@_)';
            $row  = 7;
            $space = " ";

            foreach ($asetBersih as $itemLaporan) {
                $isShowDet = false;

                $itemLaporan['nama'] = str_repeat($space,$itemLaporan['level']-2).$itemLaporan['nama'];
                if ($itemLaporan['is_summary'] == 'Y') {
                    $pengali = 1;
                    $jumlahSaldoKlp = $itemLaporan['saldo_summary'] * $pengali;
                    $jumlahSaldoKlpBl = $itemLaporan['saldo_summary_lalu'] * $pengali;
                    $sheet->getStyle('A'.$row.':C'.$row)->applyFromArray(array(
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
                    $sheet->getStyle('B'.$row.':C'.$row)->getNumberFormat()->setFormatCode($formatRupiah);
                } else {
                    $pengali = ($itemLaporan['is_tambah'] == 'T') ? -1 : 1;
                    $jumlahSaldoKlp = $itemLaporan['saldo'] * $pengali;
                    $jumlahSaldoKlpBl = $itemLaporan['saldo_lalu'] * $pengali;
        
                    if ($itemLaporan['is_child'] == '0') {
                        $sheet->mergeCells('A'.$row.':C'.$row);
                        if($itemLaporan['level'] == '2') {
                            $title = strtoupper($itemLaporan['nama']);
                            $sheet->getStyle('A'.$row.':C'.$row)->applyFromArray(
                                array(
                                    'font' => array(
                                        'bold' => true
                                    ),
                                    'borders' => array(
                                        'bottom' => array(
                                            'style' => PHPExcel_Style_Border::BORDER_DOUBLE
                                        ), 'top' => array(
                                            'style' => PHPExcel_Style_Border::BORDER_THICK
                                        )
                                    )
                                )
                            );
                        }else{
                            $title = str_repeat($space,$itemLaporan['level']-2).$itemLaporan['nama'];
                            $sheet->getStyle('A'.$row.':C'.$row)->applyFromArray(array(
                                'font' => array(
                                    'bold' => true
                                ),
                                'borders' => array(
                                    'bottom' => array(
                                    'style' => PHPExcel_Style_Border::BORDER_THICK
                                    )
                                )
                            ));
                        }

                        $itemLaporan['nama'] = $title;
                    } else {
                        $itemLaporan['nama'] = str_repeat($space,$itemLaporan['level']-1).$itemLaporan['nama'];
                        $sheet->getStyle('B'.$row.':C'.$row)->getNumberFormat()->setFormatCode($formatRupiah);
                        $isShowDet = true;

                        $dataDetail = $this->mObj->LaporanBuilder()->getLaporanDetail(
                            $tgl_awal,
                            $tgl_akhir,
                            $itemLaporan['id'],
                            $subAccount,
                            $status
                        );
                    }
                }
                $sheet->setCellValueByColumnAndRow(0, $row, $itemLaporan['nama']);
                $sheet->setCellValueByColumnAndRow(1, $row, $jumlahSaldoKlp);
                $sheet->setCellValueByColumnAndRow(2, $row, $jumlahSaldoKlpBl);
                $row++;
                if(!empty($dataDetail) && $isShowDet){
                    foreach($dataDetail as $valueDet){

                        $nominal = $valueDet['kellap_coa_saldo']*$pengali;
                        $nominalBl = $valueDet['kellap_coa_saldo_lalu']*$pengali;

                        $sheet->setCellValueByColumnAndRow(0, $row,
                            str_repeat($space,$itemLaporan['level']+4).
                            $valueDet['kellap_coa_kode'].
                            ' ['.$valueDet['kellap_sub_acc'].'] - '.
                            $valueDet['kellap_coa_nama']
                        );
                        $sheet->setCellValueByColumnAndRow(1, $row, $nominal);
                        $sheet->setCellValueByColumnAndRow(2, $row, $nominalBl);
                        $sheet->getStyle('A'.$row.':C'.$row)->applyFromArray(
                            array(
                                'font' => array(
                                    'italic' => true
                                )
                            )
                        );
                        $sheet->getStyle('B'.$row.':C'.$row)
                            ->getNumberFormat()
                            ->setFormatCode($formatRupiah);
                        $row++;
                    }
                }
            }
        }

        # Save Excel document to local hard disk
        $this->Save();
    }
}
