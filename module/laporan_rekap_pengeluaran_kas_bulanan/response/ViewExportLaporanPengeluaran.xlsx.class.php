<?php
/**
* ================= doc ====================
* FILENAME     : ViewExportLaporanPengeluaran.xlsx.class.php
* @package     : ViewExportLaporanPengeluaran
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-03-06
* @Modified    : 2015-03-06
* @Analysts    : Dyah Fajar N
* @contact     : eko.susilo@gamatechno.com
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/laporan_rekap_pengeluaran_kas_bulanan/business/LaporanRekapPengeluaranKas.class.php';
require_once GTFWConfiguration::GetValue('application','docroot').
'module/user_unit_kerja/business/UserUnitKerja.class.php';

class ViewExportLaporanPengeluaran extends XlsxResponse
{
   # Internal Variables
   public $Excel;
   public $indonesianMonth    = array(
      0 => array(
         'id' => 0,
         'name' => 'N/A'
      ), array(
         'id' => 1,
         'name' => 'Januari'
      ), array(
         'id' => 2,
         'name' => 'Februari'
      ), array(
         'id' => 3,
         'name' => 'Maret'
      ), array(
         'id' => 4,
         'name' => 'April'
      ), array(
         'id' => 5,
         'name' => 'Mei'
      ), array(
         'id' => 6,
         'name' => 'Juni'
      ), array(
         'id' => 7,
         'name' => 'Juli'
      ), array(
         'id' => 8,
         'name' => 'Agustus'
      ), array(
         'id' => 9,
         'name' => 'September'
      ), array(
         'id' => 10,
         'name' => 'Oktober'
      ), array(
         'id' => 11,
         'name' => 'November'
      ), array(
         'id' => 12,
         'name' => 'Desember'
      )
   );
   function ProcessRequest()
   {
      $mObj             = new LaporanRekapPengeluaranKas();
      $mUnitObj         = new UserUnitKerja();
      $userId           = $mObj->getUserId();

      $requestData['unit_id']    = Dispatcher::Instance()->Decrypt($mObj->_GET['unit_id']);
      $requestData['unit_nama']  = Dispatcher::Instance()->Decrypt($mObj->_GET['unit_nama']);
      $requestData['ta_id']      = Dispatcher::Instance()->Decrypt($mObj->_GET['ta_id']);
      $requestData['periode']    = date('Y-m-d', strtotime(Dispatcher::Instance()->Decrypt($mObj->_GET['periode'])));
      $requestData['ta_nama']    = Dispatcher::Instance()->Decrypt($mObj->_GET['ta_nama']);
      $periodeMon                = (int)date('m', strtotime($requestData['periode']));
      $periode    = $this->indonesianMonth[$periodeMon]['name'];

      $dataList   = $mObj->ChangeKeyName($mObj->getDataLaporan(0, 1000, (array)$requestData));
      # set writer for XlsxResponse
      # default Excel5 for .xls extension
      # option Excel2007 for .xlsx extension
      # $this->SetWriter('Excel2007');
      $this->SetFileName('laporan_rekap_pengeluaran_bulan.xls');

      # Write your code here
      # Get active sheet
      # Document Setting
      $sheet   = $this->Excel->getActiveSheet(0);
      $sheet->setShowGridlines(false); # false: Hide the gridlines; true: show the gridlines
      # set worksheet name
      $sheet->setTitle('laporan');
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

      $headerStyle         = array(
         'font' => array(
            'size' => 14,
            'bold' => true
         ), 'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
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
            'bottom' => array(
               'style' => PHPExcel_Style_Border::BORDER_THICK,
               'color' => array('argb' => 'ff269900')
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
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_BOTTOM,
            'wrap' => true,
            'shrinkToFit' => true
         )
      );


      $sheet->setCellValue('A1', 'Laporan Rekap Pengeluaran');
      $sheet->setCellValue('A3', GTFWConfiguration::GetValue('language', 'periode_tahun'));
      $sheet->setCellValue('C3', ':');
      $sheet->setCellValueExplicit('D3', $requestData['ta_nama'], PHPExcel_Cell_DataType::TYPE_STRING);
      $sheet->setCellValue('A4', GTFWConfiguration::GetValue('language', 'unit_kerja'));
      $sheet->setCellValue('C4', ':');
      $sheet->setCellValueExplicit('D4', $requestData['unit_nama']);
      $sheet->setCellValue('A5', GTFWConfiguration::GetValue('language', 'periode'));
      $sheet->setCellValue('C5', ':');
      $sheet->setCellValueExplicit('D5', $periode);

      $sheet->mergeCells('A1:K1');
      $sheet->getRowDimension('1')->setRowHeight(20);
      $sheet->mergeCells('A3:B3');
      $sheet->mergeCells('A4:B4');
      $sheet->mergeCells('A5:B5');
      $sheet->mergeCells('D3:K3');
      $sheet->mergeCells('D4:K4');
      $sheet->mergeCells('D5:K5');
      $sheet->getStyle('A1:K1')->applyFromArray($headerStyle);

      $sheet->getStyle('A3:J5')->getFont()->setBold(true);
      $sheet->getStyle('C3:C5')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

      $no         = GTFWConfiguration::GetValue('language', 'no');
      $kode       = GTFWConfiguration::GetValue('language', 'kode');
      $nama       = GTFWConfiguration::GetValue('language', 'nama');
      $unit       = GTFWConfiguration::GetValue('language', 'unit');
      $sub_unit   = GTFWConfiguration::GetValue('language', 'sub_unit');
      $nilai_rp   = GTFWConfiguration::GetValue('language', 'nilai_rp');
      $nilai_setujui_rp    = GTFWConfiguration::GetValue('language', 'nilai_setujui_rp');
      $total      = GTFWConfiguration::GetValue('language', 'total');
      $pengadaan           = GTFWConfiguration::GetValue('language', 'pengadaan');
      $non_pengadaan       = GTFWConfiguration::GetValue('language', 'non_pengadaan');
      $pengadaan           = GTFWConfiguration::GetValue('language', 'pengadaan');
      $non_pengadaan       = GTFWConfiguration::GetValue('language', 'non_pengadaan');

      $sheet->mergeCells('A7:A8');
      $sheet->mergeCells('B7:C8');
      $sheet->mergeCells('D7:D8');
      $sheet->mergeCells('E7:E8');
      $sheet->mergeCells('F7:G7');
      $sheet->mergeCells('H7:I7');
      $sheet->mergeCells('J7:J8');
      $sheet->mergeCells('K7:K8');

      $sheet->getColumnDimension('A')->setWidth('5');
      $sheet->getColumnDimension('B')->setWidth('12');
      $sheet->getColumnDimension('C')->setWidth('3');
      $sheet->getColumnDimension('D')->setWidth('30');
      $sheet->getColumnDimension('E')->setWidth('20');
      $sheet->getColumnDimension('F')->setWidth('16');
      $sheet->getColumnDimension('G')->setWidth('16');
      $sheet->getColumnDimension('H')->setWidth('16');
      $sheet->getColumnDimension('I')->setWidth('16');
      $sheet->getColumnDimension('J')->setWidth('16');
      $sheet->getColumnDimension('K')->setWidth('16');

      $sheet->setCellValue('A7', $no);
      $sheet->setCellValue('B7', $kode);
      $sheet->setCellValue('D7', $nama);
      $sheet->setCellValue('E7', $unit.'/'.$sub_unit);
      $sheet->setCellValue('F7', $nilai_rp);
      $sheet->setCellValue('H7', $nilai_setujui_rp);
      $sheet->setCellValue('J7', $total . "\n" . $nilai_rp);
      $sheet->setCellValue('K7', $total . "\n" . $nilai_setujui_rp);

      $sheet->setCellValue('F8', $pengadaan);
      $sheet->setCellValue('G8', $non_pengadaan);
      $sheet->setCellValue('H8', $pengadaan);
      $sheet->setCellValue('I8', $non_pengadaan);

      $sheet->getStyle('A7:K8')->applyFromArray($styledTableHeaderArray);

      $row     = 9;
      if (empty($dataList)){
         $sheet->mergeCells('A'.$row.':K'.$row);
         $sheet->setCellValue('A'.$row, 'Data Kosong');
         $sheet->getRowDimension($row)->setRowHeight(20);
         $sheet->getStyle('A'.$row.':K'.$row)->applyFromArray(array(
            'font' => array(
               'size' => 12,
               'bold' => true
            ), 'alignment' => array(
               'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
               'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            ), 'borders' => array(
               'bottom' => array(
                  'style' => PHPExcel_Style_Border::BORDER_THIN,
                  'color' => array('argb' => 'ff7D7D7D')
               )
            )
         ));
      }else{
         /**
          * Insisialisasi data
          */
         $programId        = '';
         $kegiatanId       = '';
         $subkegiatanId    = '';
         $komponenId       = '';
         $dataGrid         = array();
         $index            = 0;
         $dataPengadaan    = array();
         $dataKomponen     = array();
         $start            = 1;
         for ($i=0; $i < count($dataList);) {
            if((int)$dataList[$i]['program_id'] === (int)$programId && (int)$dataList[$i]['kegiatan_id'] === (int)$kegiatanId && (int)$dataList[$i]['id'] === (int)$subkegiatanId){
               $kegdetId                  = $dataList[$i]['id'];

               $programKodeSistem         = $programId.'.0.0';
               $kegiatanKodeSistem        = $programId.'.'.$kegiatanId.'.0';
               $subkegiatanKodeSistem     = $programId.'.'.$kegiatanId.'.'.$subkegiatanId;
               if(!is_null($dataList[$i]['komp_id'])){
                  // data pengadaan program
                  $dataPengadaan[$programKodeSistem]['nominal_pengadaan']        += $dataList[$i]['nominal_pengadaan'];
                  $dataPengadaan[$programKodeSistem]['nominal_non_pengadaan']    += $dataList[$i]['nominal_non_pengadaan'];
                  $dataPengadaan[$programKodeSistem]['nominal']                  += $dataList[$i]['nominal'];
                  $dataPengadaan[$programKodeSistem]['nominal_approve_pengadaan']      += $dataList[$i]['nominal_approve_pengadaan'];
                  $dataPengadaan[$programKodeSistem]['nominal_approve_non_pengadaan']  += $dataList[$i]['nominal_approve_non_pengadaan'];
                  $dataPengadaan[$programKodeSistem]['nominal_approve']                += $dataList[$i]['nominal_approve'];
                  // -- end data pengadaan program

                  // data pengadaan kegiatan
                  $dataPengadaan[$kegiatanKodeSistem]['nominal_pengadaan']        += $dataList[$i]['nominal_pengadaan'];
                  $dataPengadaan[$kegiatanKodeSistem]['nominal_non_pengadaan']    += $dataList[$i]['nominal_non_pengadaan'];
                  $dataPengadaan[$kegiatanKodeSistem]['nominal']                  += $dataList[$i]['nominal'];
                  $dataPengadaan[$kegiatanKodeSistem]['nominal_approve_pengadaan']      += $dataList[$i]['nominal_approve_pengadaan'];
                  $dataPengadaan[$kegiatanKodeSistem]['nominal_approve_non_pengadaan']  += $dataList[$i]['nominal_approve_non_pengadaan'];
                  $dataPengadaan[$kegiatanKodeSistem]['nominal_approve']                += $dataList[$i]['nominal_approve'];
                  // end data pengadaan kegiatan

                  // data pengadaan subkegiatan
                  $dataPengadaan[$subkegiatanKodeSistem]['nominal_pengadaan']        += $dataList[$i]['nominal_pengadaan'];
                  $dataPengadaan[$subkegiatanKodeSistem]['nominal_non_pengadaan']    += $dataList[$i]['nominal_non_pengadaan'];
                  $dataPengadaan[$subkegiatanKodeSistem]['nominal']                  += $dataList[$i]['nominal'];
                  $dataPengadaan[$subkegiatanKodeSistem]['nominal_approve_pengadaan']      += $dataList[$i]['nominal_approve_pengadaan'];
                  $dataPengadaan[$subkegiatanKodeSistem]['nominal_approve_non_pengadaan']  += $dataList[$i]['nominal_approve_non_pengadaan'];
                  $dataPengadaan[$subkegiatanKodeSistem]['nominal_approve']                += $dataList[$i]['nominal_approve'];
                  // -- end data pengadaan subkegiatan

                  $dataKomponen[$kodeSistem][]     = $dataList[$i]['status_komponen'];
                  /**
                   * Data Komponen (Detail Belanja)
                   */
                  $dataGrid[$index]['id']          = $dataList[$i]['komp_id'];
                  $dataGrid[$index]['kode']        = $dataList[$i]['komp_kode'];
                  $dataGrid[$index]['nama']        = $dataList[$i]['komp_nama'];
                  $dataGrid[$index]['deskripsi']   = $dataList[$i]['komp_deskripsi'];

                  $dataGrid[$index]['level']       = 'komponen';
               }else{
                  $index--;
               }
               $i++;
            }elseif((int)$dataList[$i]['program_id'] === (int)$programId && (int)$dataList[$i]['kegiatan_id'] === (int)$kegiatanId && (int)$dataList[$i]['id'] !== (int)$subkegiatanId){
               $subkegiatanId    = (int)$dataList[$i]['id'];
               $kodeSistem       = $dataList[$i]['program_id'].'.'.$dataList[$i]['kegiatan_id'].'.'.$dataList[$i]['id'];

               $dataPengadaan[$kodeSistem]['nominal_pengadaan']      = 0;
               $dataPengadaan[$kodeSistem]['nominal_non_pengadaan']  = 0;
               $dataPengadaan[$kodeSistem]['nominal']                = 0;
               $dataPengadaan[$kodeSistem]['nominal_approve_pengadaan']       = 0;
               $dataPengadaan[$kodeSistem]['nominal_approve_non_pengadaan']   = 0;
               $dataPengadaan[$kodeSistem]['nominal_approve']                 = 0;

               $dataKomponen[$kodeSistem]       = array();
               /**
                * Data RKAKT
                */
               $dataGrid[$index]['id']          = $dataList[$i]['id'];
               $dataGrid[$index]['kode']        = $dataList[$i]['subkegiatan_nomor'];
               $dataGrid[$index]['nama']        = $dataList[$i]['subkegiatan_nama'];
               $dataGrid[$index]['subkegiatan_id'] = $dataList[$i]['subkegiatan_id'];
               $dataGrid[$index]['deskripsi']   = $dataList[$i]['deskripsi'];
               $dataGrid[$index]['unit_kode']   = $dataList[$i]['unit_kode'];
               $dataGrid[$index]['unit_nama']   = $dataList[$i]['unit_nama'];
               $dataGrid[$index]['nomor']       = $start;
               $dataGrid[$index]['level']       = 'subkegiatan';
               $dataGrid[$index]['kode_sistem'] = $kodeSistem;
               $start++;
            }elseif((int)$dataList[$i]['program_id'] === (int)$programId && (int)$dataList[$i]['kegiatan_id'] !== (int)$kegiatanId){
               $kegiatanId                      = (int)$dataList[$i]['kegiatan_id'];
               $kodeSistem                      = $dataList[$i]['program_id'].'.'.$dataList[$i]['kegiatan_id'].'.0';

               $dataPengadaan[$kodeSistem]['nominal_pengadaan']      = 0;
               $dataPengadaan[$kodeSistem]['nominal_non_pengadaan']  = 0;
               $dataPengadaan[$kodeSistem]['nominal']                = 0;
               $dataPengadaan[$kodeSistem]['nominal_approve_pengadaan']       = 0;
               $dataPengadaan[$kodeSistem]['nominal_approve_non_pengadaan']   = 0;
               $dataPengadaan[$kodeSistem]['nominal_approve']                 = 0;
               /**
                * Data Kegiatan
                */
               $dataGrid[$index]['id']          = $dataList[$i]['kegiatan_id'];
               $dataGrid[$index]['kode']        = $dataList[$i]['kegiatan_nomor'];
               $dataGrid[$index]['nama']        = $dataList[$i]['kegiatan_nama'];
               $dataGrid[$index]['kode_sistem'] = $kodeSistem;
               $dataGrid[$index]['level']       = 'kegiatan';
            }else{
               $programId                       = (int)$dataList[$i]['program_id'];
               $kodeSistem                      = $dataList[$i]['program_id'].'.0.0';
               $dataPengadaan[$kodeSistem]['nominal_pengadaan']      = 0;
               $dataPengadaan[$kodeSistem]['nominal_non_pengadaan']  = 0;
               $dataPengadaan[$kodeSistem]['nominal']                = 0;
               $dataPengadaan[$kodeSistem]['nominal_approve_pengadaan']       = 0;
               $dataPengadaan[$kodeSistem]['nominal_approve_non_pengadaan']   = 0;
               $dataPengadaan[$kodeSistem]['nominal_approve']                 = 0;
               /**
                * Data Program
                */
               $dataGrid[$index]['id']          = $dataList[$i]['program_id'];
               $dataGrid[$index]['kode']        = $dataList[$i]['program_nomor'];
               $dataGrid[$index]['nama']        = $dataList[$i]['program_nama'];
               $dataGrid[$index]['kode_sistem'] = $kodeSistem;
               $dataGrid[$index]['level']       = 'program';
            }
            $index++;
         }

         foreach ($dataGrid as $grid) {
            $rowHegiht  = max(array(
               ceil(strlen(preg_replace('/\s/', '_', $list['nama']))/40)*16,
               ceil(strlen(preg_replace('/\s/', '_', $list['unit_nama']))/30)*16
            ));
            $ks      = $grid['kode_sistem'];
            $sheet->getRowDimension($row)->setRowHeight($rowheight);
            $sheet->setCellValue('A'.$row, $grid['nomor']);
            $sheet->setCellValueExplicit('B'.$row, $grid['kode'], PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('D'.$row, $grid['nama'], PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('E'.$row, $grid['unit_nama'], PHPExcel_Cell_DataType::TYPE_STRING);

            switch (strtoupper($grid['level'])) {
               case 'PROGRAM':
                  $rowStyle   = array(
                     'borders' => array(
                        'bottom' => array(
                           'style' => PHPExcel_Style_Border::BORDER_THIN,
                           'color' => array('argb' => 'ff404040')
                        )
                     ), 'font' => array(
                        'bold' => true
                     )
                  );
                  break;
               case 'KEGIATAN':
                  $rowStyle   = array(
                     'borders' => array(
                        'bottom' => array(
                           'style' => PHPExcel_Style_Border::BORDER_THIN,
                           'color' => array('argb' => 'ff404040')
                        )
                     ), 'font' => array(
                        'bold' => true
                     )
                  );
                  break;
               case 'SUBKEGIATAN':
                  $rowStyle   = array(
                     'borders' => array(
                        'bottom' => array(
                           'style' => PHPExcel_Style_Border::BORDER_THICK,
                           'color' => array('argb' => 'ff404040')
                        )
                     ), 'font' => array(
                        'bold' => true
                     )
                  );
                  break;
               case 'KOMPONEN':
                  $rowStyle   = array(
                     'borders' => array(
                        'bottom' => array(
                           'style' => PHPExcel_Style_Border::BORDER_THIN,
                           'color' => array('argb' => 'ffCCCCCC')
                        )
                     )
                  );
                  break;
               default:
                  $rowStyle   = array(
                     'borders' => array(
                        'bottom' => array(
                           'style' => PHPExcel_Style_Border::BORDER_THIN,
                           'color' => array('argb' => 'ffCCCCCC')
                        )
                     )
                  );
                  break;
            }
            $sheet->getStyle('A'.$row.':K'.$row)->applyFromArray($rowStyle);
            $row++;
         }

         $sheet->getStyle('A9:K'.($row-1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT)->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
         $sheet->getStyle('B9:B'.($row-1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
         $sheet->getStyle('A9:K'.($row-1))->getAlignment()->setWrapText(true);
         $sheet->getStyle('A9:K'.($row-1))->getAlignment()->setShrinkToFit(true);
      }

      # Save Excel document to local hard disk
      $this->Save();
   }
}
?>