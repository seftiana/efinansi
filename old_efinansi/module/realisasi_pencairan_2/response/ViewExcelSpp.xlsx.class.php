<?php
# Doc
# @category    XlsxResponse
# @package     ViewExcelSpp
# @copyright   Copyright (c) 2011 Gamatechno
# @author      By Ucil
# @Created     2012-08-14
# @modified    2012-08-14
# @Modified    By Ucil
# @contact     eko[dot]susilo[at]gmail[dot]com
# /Doc
require_once GTFWConfiguration::GetValue( 'application', 'docroot').
'module/realisasi_pencairan_2/business/spp.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot').
'main/function/terbilang.php';
class ViewExcelSpp extends XlsxResponse
{
   # Internal Variables
   public $Excel;

   function ProcessRequest()
   {
      # set writer for XlsxResponse
      # default Excel5 for .xls extension
      # option Excel2007 for .xlsx extension
      # $this->SetWriter('Excel2007');
      $this->SetFileName('surat_permintaan_pembayaran.xls');

      # Write your code here
      # Get active sheet
      # Document Setting
      $sheet   = $this->Excel->getActiveSheet(0);
      $sheet->setShowGridlines(false); # true: Hide the gridlines; false: show the gridlines
      # set worksheet name
      $sheet->setTitle('Surat Permintaan Pembayaran');
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
      $sheet->getDefaultColumnDimension()->setWidth(12);

      #data organization
      $company_name     = GTFWConfiguration::GetValue('organization','company_name');
      $nama_lembaga     = GTFWConfiguration::GetValue('organization','kementerian_lembaga_nama');
      $nomor_lembaga    = GTFWConfiguration::GetValue('organization','kementerian_lembaga_no');
      $unit_organisasi  = GTFWConfiguration::GetValue('organization','unit_org_eselon_nama');
      $no_unit_org      = GTFWConfiguration::GetValue('organization','unit_org_eselon_no');
      $kota             = GTFWConfiguration::GetValue('organization','city');
      $kode_kota        = GTFWConfiguration::GetValue('organization','city_number');
      $pelaksana        = GTFWConfiguration::GetValue('organization','kewenangan_pelaksanaan');
      $company_address  = GTFWConfiguration::GetValue('organization','company_address');
      $provinsi         = GTFWConfiguration::GetValue('organization', 'provinsi');
      $provinsi_no      = GTFWConfiguration::GetValue('organization', 'provinsi_no');
      $nama_ppk               = GTFWConfiguration::GetValue('organization', 'pejabat_pembuat_komitmen');
      $nip_ppk                = GTFWConfiguration::GetValue('organization', 'pejabat_pembuat_komitmen_nip');

      $mObj                = new Spp();
      $mNumber             = new Number();
      $queryString         = $mObj->_getQueryString();
      $realisasiId         = Dispatcher::Instance()->Decrypt($mObj->_GET['id']);
      $sppId               = Dispatcher::Instance()->Decrypt($mObj->_GET['spp_id']);
      $dataRealisasi       = $mObj->ChangeKeyName($mObj->GetDataPengajuanRealisasi($realisasiId));
      $dataSpp             = $mObj->ChangeKeyName($mObj->GetDataSpp($sppId));
      $dataRealisasiDet    = $mObj->ChangeKeyName($mObj->GetPengajuanRealisaiDetail($realisasiId));
      $dataSpp             = array_merge((array)$dataRealisasi, (array)$dataSpp);
      $dataSpp['tanggal_string'] = $mObj->indonesianDate(date('Y-m-d', strtotime($dataSpp['tanggal'])));
      $dataSpp['tanggal_cetak']  = $mObj->indonesianDate(date('Y-m-d', time()));
      $dataSpp['dipa_tahun']     = date('Y', strtotime($dataSpp['dipa_tanggal']));
      $dataSpp['spk']            = ($dataSpp['spk_nomor'] == '') ? '-' : $dataSpp['spk_nomor'].'/'.date('d/m/Y', strtotime($dataSpp['spk_tanggal']));
      $dataSpp['spk_nominal']    = ($dataSpp['spk_nomor'] == '') ? '-' : number_format($dataSpp['spk_nominal'], 0, ',','.');
      $dataSpp['terbilang']      = $mNumber->Terbilang($dataSpp['nominal'], 3);

      $headerStyle         = array(
            'font' => array(
            'size' => 14,
            'bold' => true
         ), 'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
         ));

         $subHeaderStyle      = array(
            'font' => array(
            'size' => 12,
            'bold' => true
         ), 'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
         ));

         $tableHeaderStyleArray     = array(
            'borders' => array(
               'allborders' => array(
               'style' => PHPExcel_Style_Border::BORDER_HAIR,
               'color' => array('argb' => 'ff000000')
            )
         ),
            'font' => array(
               'bold' => true,
               'color' => array(
               'rgb' => '000000'
            )
         ),
            'alignment' => array(
               'wrap' => true,
               'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
               'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
         ),
            'fill' => array(
               'type' => PHPExcel_Style_Fill::FILL_SOLID,
               'startcolor' => array(
               'argb' => 'ffcccccc'
            )
         ));

         $borderTableStyledArray = array(
            'borders' => array(
               'allborders' => array(
               'style' => PHPExcel_Style_Border::BORDER_HAIR,
               'color' => array('argb' => 'ff000000')
            )
         ));

      $sheet->setCellValue('A1', 'SURAT PERMINTAAN PEMBAYARAN');
      $sheet->getStyle('A1:S1')->applyFromArray($headerStyle);
      $sheet->mergeCells('A1:S1');
      if(empty($dataRealisasiDet)){
         $sheet->setCellValue('A3', 'Data Kosong');
         $sheet->mergeCells('A3:S4');
         $sheet->getStyle('A3:S4')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
         $sheet->getStyle('A3:S4')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
         $sheet->getStyle('A3:S4')->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_HAIR);
         $sheet->getStyle('A3:S4')->getFont()->setBold(true)->setSize(14);
      }else{
         // set column width
         $sheet->getColumnDimension('A')->setWidth('5');
         $sheet->getColumnDimension('B')->setWidth('4');
         $sheet->getColumnDimension('E')->setWidth('3');
         $sheet->getColumnDimension('H')->setWidth('3');
         $sheet->getColumnDimension('L')->setWidth('4');
         $sheet->getColumnDimension('P')->setWidth('4');
         $sheet->getColumnDimension('Q')->setWidth('3');

         // row height
         $sheet->getRowDimension('1')->setRowHeight('25');
         $sheet->getRowDimension('2')->setRowHeight('20');
         $sheet->getRowDimension('19')->setRowHeight('30');
         $sheet->getRowDimension('23')->setRowHeight('30');
         $sheet->getRowDimension('31')->setRowHeight('35');

         # merge cells
         $sheet->mergeCells('A2:S2');
         $sheet->mergeCells('A3:S3');
         $sheet->mergeCells('E4:G4');
         $sheet->mergeCells('E5:G5');
         $sheet->mergeCells('E6:G6');
         $sheet->mergeCells('I4:L4');
         $sheet->mergeCells('I5:S5');
         $sheet->mergeCells('I6:S6');
         $sheet->mergeCells('N4:S4');
         $sheet->mergeCells('A7:S7');
         $sheet->mergeCells('C8:D8');
         $sheet->mergeCells('C9:D9');
         $sheet->mergeCells('C10:D10');
         $sheet->mergeCells('C11:D11');
         $sheet->mergeCells('C12:D12');
         $sheet->mergeCells('C13:D13');
         $sheet->mergeCells('F8:K8');
         $sheet->mergeCells('F9:K9');
         $sheet->mergeCells('F10:K10');
         $sheet->mergeCells('F11:K11');
         $sheet->mergeCells('F12:K12');
         $sheet->mergeCells('F13:K13');
         $sheet->mergeCells('M8:P8');
         $sheet->mergeCells('M9:P9');
         $sheet->mergeCells('M10:P10');
         $sheet->mergeCells('M11:P11');
         $sheet->mergeCells('M12:P12');
         $sheet->mergeCells('M13:P13');
         $sheet->mergeCells('R8:S8');
         $sheet->mergeCells('R9:S9');
         $sheet->mergeCells('R10:S10');
         $sheet->mergeCells('R11:S11');
         $sheet->mergeCells('R12:S12');
         $sheet->mergeCells('R13:S13');
         $sheet->mergeCells('C14:S14');
         $sheet->mergeCells('C15:S15');
         $sheet->mergeCells('C16:S16');
         $sheet->mergeCells('C17:S17');
         $sheet->mergeCells('A18:S18');
         $sheet->mergeCells('C19:S19');
         $sheet->mergeCells('A20:S20');
         $sheet->mergeCells('C21:G21');
         $sheet->mergeCells('C22:G22');
         $sheet->mergeCells('C23:G23');
         $sheet->mergeCells('C24:G24');
         $sheet->mergeCells('C25:G25');
         $sheet->mergeCells('C26:G26');
         $sheet->mergeCells('C27:G27');
         $sheet->mergeCells('C28:G28');
         $sheet->mergeCells('C29:G29');
         $sheet->mergeCells('C30:G30');
         $sheet->mergeCells('I21:S21');
         $sheet->mergeCells('I22:S22');
         $sheet->mergeCells('I23:S23');
         $sheet->mergeCells('I24:S24');
         $sheet->mergeCells('I25:S25');
         $sheet->mergeCells('I26:S26');
         $sheet->mergeCells('I27:S27');
         $sheet->mergeCells('I28:S28');
         $sheet->mergeCells('I29:S29');
         $sheet->mergeCells('I30:S30');
         $sheet->mergeCells('A31:B31');
         $sheet->mergeCells('C31:F31');
         $sheet->mergeCells('G31:I31');
         $sheet->mergeCells('J31:K31');
         $sheet->mergeCells('L31:N31');
         $sheet->mergeCells('O31:Q31');
         $sheet->mergeCells('R31:S31');
         $sheet->mergeCells('A32:B32');
         $sheet->mergeCells('C32:F32');
         $sheet->mergeCells('G32:I32');
         $sheet->mergeCells('J32:K32');
         $sheet->mergeCells('L32:N32');
         $sheet->mergeCells('O32:Q32');
         $sheet->mergeCells('R32:S32');

         $sheet->getStyle('H4:H6')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
         $sheet->getStyle('B8:B13')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP)->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
         $sheet->getStyle('A14:S20')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP)->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
         $sheet->getStyle('B21:B30')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP)->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
         $sheet->getStyle('C21:G30')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP)->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
         $sheet->getStyle('C22')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
         $sheet->getStyle('I21:S30')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP)->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
         $sheet->getStyle('E8:E13')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP)->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
         $sheet->getStyle('H21:H30')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP)->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
          $sheet->getStyle('L8:L13')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP)->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
         $sheet->getStyle('Q8:Q13')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP)->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
         $sheet->getStyle('A7:S7')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_HAIR);
         $sheet->getStyle('A14:S14')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_HAIR);
         $sheet->getStyle('A18:S18')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_HAIR);
         $sheet->getStyle('A1:S30')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_HAIR);
         $sheet->getStyle('A1:S30')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_HAIR);
         $sheet->getStyle('A1:S30')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_HAIR);
         $sheet->getStyle('A31:S32')->applyFromArray($tableHeaderStyleArray);

         $sheet->setCellValue('E4', GTFWConfiguration::GetValue('language', 'tanggal'));
         $sheet->setCellValue('E5', GTFWConfiguration::GetValue('language', 'sifat_pembayaran'));
         $sheet->setCellValue('E6', GTFWConfiguration::GetValue('language', 'jenis_pembayaran'));

         $sheet->setCellValue('H4', ':');
         $sheet->setCellValue('H5', ':');
         $sheet->setCellValue('H6', ':');

         $sheet->setCellValue('I4', $dataSpp['tanggal_string']);
         $sheet->setCellValue('I5', $dataSpp['sifat_pembayaran_nama']);
         $sheet->setCellValue('I6', $dataSpp['jenis_pembayaran_nama']);

         $sheet->setCellValue('M4', 'Nomor');
         $sheet->setCellValue('N4', $dataSpp['nomor']);

         # sub header
         $sheet->setCellValue('B8', '1');
         $sheet->setCellValue('C8', 'Kementrian');
         $sheet->setCellValue('E8', ':');
         $sheet->setCellValue('F8', $nama_lembaga.' ('.$nomor_lembaga.')');
         $sheet->setCellValue('L8', '7');
         $sheet->setCellValue('M8', 'Kegiatan');
         $sheet->setCellValue('Q8', ':');
         $sheet->setCellValue('R8', $dataSpp['kegiatan_nama']);

         $sheet->setCellValue('B9', '2');
         $sheet->setCellValue('C9', 'Unit Organisasi');
         $sheet->setCellValue('E9', ':');
         $sheet->setCellValue('F9', $unit_organisasi.' ('.$no_unit_org.')');
         $sheet->setCellValue('L9', '8');
         $sheet->setCellValue('M9', 'Kode Kegiatan');
         $sheet->setCellValue('Q9', ':');
         $sheet->setCellValueExplicit('R9', $dataSpp['kegiatan_kode'], PHPExcel_Cell_DataType::TYPE_STRING);

         $sheet->setCellValue('B10', '3');
         $sheet->setCellValue('C10', 'Provinsi');
         $sheet->setCellValue('E10', ':');
         $sheet->setCellValue('F10', $provinsi.' ('.$provinsi_no.')');
         $sheet->setCellValue('L10', '9');
         $sheet->setCellValue('M10', 'Kode Fungsi, Sub Fungsi, Program');
         $sheet->setCellValue('Q10', ':');
         $sheet->setCellValueExplicit('R10', GTFWConfiguration::GetValue('organization', 'fungsi_no').'.'.GTFWConfiguration::GetValue('organization', 'subfungsi_no').'.'.$dataSpp['program_kode'], PHPExcel_Cell_DataType::TYPE_STRING);

         $sheet->setCellValue('B11', '4');
         $sheet->setCellValue('C11', 'Satker / SKS');
         $sheet->setCellValue('E11', ':');
         $sheet->setCellValue('F11', $dataSpp['unit_nama'].' ('.$dataSpp['unit_kode'].')');
         $sheet->setCellValue('L11', '10');
         $sheet->setCellValue('M11', 'Kewenangan Pelaksanaan');
         $sheet->setCellValue('Q11', ':');
         $sheet->setCellValueExplicit('R11', 'KD', PHPExcel_Cell_DataType::TYPE_STRING);

         $sheet->setCellValue('B12', '5');
         $sheet->setCellValue('C12', 'Lokasi');
         $sheet->setCellValue('E12', ':');
         $sheet->setCellValue('F12', $kota.' ('.$kode_kota.')');

         $sheet->setCellValue('B13', '6');
         $sheet->setCellValue('C13', 'Alamat');
         $sheet->setCellValue('E13', ':');
         $sheet->setCellValue('F13', $company_address);

         # end sub header
         $sheet->setCellValue('C14', 'Kepada :');
         $sheet->setCellValue('C15', 'Yth. Pejabat Penerbit Surat Perintah Membayar');
         $sheet->setCellValue('C16', 'Satker Kantor Pusat Administrasi');
         $sheet->setCellValue('C17', 'di '.$kota);

         $sheet->setCellValue('C19', 'Berdasarkan DIPA NOMOR '.$dataSpp['dipa_nomor'].' bersama ini kami ajukan Permintaan Pembayaran sebagai berikut:');

         $sheet->setCellValue('B21', '1');
         $sheet->setCellValue('C21', 'Jumlah Pembayaran yang diminta');
         $sheet->setCellValueExplicit('I21', $dataSpp['nominal'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
         $sheet->getStyle('I21')->getNumberFormat()->setFormatCode('#,##0.00_);(#,##0.00)');
         $sheet->setCellValue('H21', ':');
         $sheet->setCellValue('C22', 'Terbilang');
         $sheet->setCellValue('I22', $dataSpp['terbilang'].' Rupiah');
         $sheet->setCellValue('H22', ':');
         $sheet->setCellValue('B23', '2');
         $sheet->setCellValue('C23', 'Untuk Keperluan');
         $sheet->setCellValue('H23', ':');
         $sheet->setCellValue('I23', $dataSpp['keperluan']);
         $sheet->setCellValue('B24', '3');
         $sheet->setCellValue('C24', 'Jenis Belanja');
         $sheet->setCellValue('H24', ':');
         $sheet->setCellValue('I24', $dataSpp['jenis_belanja']);
         $sheet->setCellValue('B25', '4');
         $sheet->setCellValue('C25', 'Atas Nama');
         $sheet->setCellValue('H25', ':');
         $sheet->setCellValue('I25', $dataSpp['nama']);
         $sheet->setCellValue('B26', '5');
         $sheet->setCellValue('C26', 'Alamat');
         $sheet->setCellValue('H26', ':');
         $sheet->setCellValue('I26', $dataSpp['alamat']);
         $sheet->setCellValue('B27', '6');
         $sheet->setCellValue('C27', 'Mempunyai Rekening');
         $sheet->setCellValue('H27', ':');
         $sheet->setCellValue('I27', $dataSpp['rekening']);
         $sheet->setCellValue('B28', '7');
         $sheet->setCellValue('C28', 'Nomor dan Tanggal SPK/Kontrak');
         $sheet->setCellValue('H28', ':');
         $sheet->setCellValue('I28', $dataSpp['spk']);
         $sheet->setCellValue('B29', '8');
         $sheet->setCellValue('C29', 'Nilai SPK/Kontrak');
         $sheet->setCellValue('H29', ':');
         $sheet->setCellValue('I29', $dataSpp['spk_nominal']);
         $sheet->setCellValue('B30', '9');
         $sheet->setCellValue('C30', 'Dengan Penjelasan');
         $sheet->setCellValue('H30', ':');

         // data detail SPP
         $sheet->setCellValue('A31', "NO \nURUT");
         $sheet->setCellValue('C31', "KEGIATAN/OUTPUT/LOKASI/ \nSUB KELOMPOK AKUN/AKUN");
         $sheet->setCellValue('G31', "PAGU DALAM \nDIPA (RP)");
         $sheet->setCellValue('J31', "SPP/SPM SD \nYANG LALU (RP)");
         $sheet->setCellValue('L31', "SPP INI (RP)");
         $sheet->setCellValue('O31', "JUMLAH SD. \nSPP INI (RP)");
         $sheet->setCellValue('R31', "SISA DANA \n(RP)");
         $sheet->setCellValue('A32', "1");
         $sheet->setCellValue('C32', "2");
         $sheet->setCellValue('G32', "3");
         $sheet->setCellValue('J32', "4");
         $sheet->setCellValue('L32', "5");
         $sheet->setCellValue('O32', "6 = 4 + 5");
         $sheet->setCellValue('R32', "7 = 3 - 6");

         $dataGrid      = array();
         $nominalPagu   = 0;
         $sppIni        = 0;
         $sppLalu       = 0;
         $nominalSpp    = 0;
         $idPagu        = '';
         $index         = 0;
         $nomor         = 1;

         $totalPagu     = 0;
         $totalSppLalu  = 0;
         $totalSppIni   = 0;
         $totalSisaDana = 0;
         $sppTotal      = 0;
         $dataList      = $dataRealisasiDet;
         for ($i=0; $i < count($dataList);) {
            if((int)$idPagu === (int)$dataList[$i]['pagu_id']){
               $nominalSpp       += $dataList[$i]['nominal'];
               $sppIni           += $dataList[$i]['nominal'];

               $totalPagu        += $nominalPagu;
               $totalSppLalu     += $dataList[$i]['spp_lalu']+($sppIni-$dataList[$i]['nominal']);
               $totalSppIni      += $dataList[$i]['nominal'];
               $sppTotal         += $dataList[$i]['spp_lalu']+$sppIni;
               $totalSisaDana    += $nominalPagu-($dataList[$i]['spp_lalu']+$sppIni);

               $dataGrid[$index]['realisasi_id']      = $dataList[$i]['id'];
               $dataGrid[$index]['nomor']             = $nomor;
               $dataGrid[$index]['mak_kode']          = $dataList[$i]['mak_kode'];
               $dataGrid[$index]['kode']              = $dataList[$i]['program_kode'].'/'.$dataList[$i]['kegiatan_kode'].'/'.$dataList[$i]['sub_kegiatan_kode'];
               $dataGrid[$index]['nominal_pagu']      = $nominalPagu;
               $dataGrid[$index]['spp_lalu']          = $dataList[$i]['spp_lalu']+($sppIni-$dataList[$i]['nominal']);
               $dataGrid[$index]['spp_ini']           = $dataList[$i]['nominal'];
               $dataGrid[$index]['nominal']           = $dataList[$i]['nominal'];
               $dataGrid[$index]['spp_total']         = $dataList[$i]['spp_lalu']+$sppIni;
               $dataGrid[$index]['sisa_dana']         = $nominalPagu-($dataList[$i]['spp_lalu']+$sppIni);
               $i++;
               $index++;
               $nomor++;
            }else{
               $idPagu        = (int)$dataList[$i]['pagu_id'];
               $nominalPagu   += $dataList[$i]['nominal_pagu'];
               unset($sppIni);
               unset($sppLalu);
               $sppIni        = 0;
               $sppLalu       = $dataList[$i]['spp_lalu'];
            }
         }
         $row     = 33;

         if(empty($dataGrid)){
            $sheet->setCellValue('A'.$row, 'Data Kosong');
            $sheet->mergeCells('A'.$row.':S'.$row);
            $sheet->getStyle('A'.$row.':S'.$row)->applyFromArray($borderTableStyledArray);
            $sheet->getStyle('A'.$row.':S'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
         }else{
            foreach ($dataGrid as $list) {
               // merge cells
               $sheet->mergeCells('A'.$row.':B'.$row);
               $sheet->mergeCells('C'.$row.':F'.$row);
               $sheet->mergeCells('G'.$row.':I'.$row);
               $sheet->mergeCells('J'.$row.':K'.$row);
               $sheet->mergeCells('L'.$row.':N'.$row);
               $sheet->mergeCells('O'.$row.':Q'.$row);
               $sheet->mergeCells('R'.$row.':S'.$row);
               // end merge cells

               // cell style
               $sheet->getStyle('A'.$row.':B'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
               $sheet->getStyle('C'.$row.':F'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
               $sheet->getStyle('A'.$row.':S'.$row)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
               // end cell style
               $sheet->setCellValue('A'.$row, $list['nomor']);
               $sheet->setCellValueExplicit('C'.$row, $list['kode'], PHPExcel_Cell_DataType::TYPE_STRING);
               $sheet->setCellValueExplicit('G'.$row, $list['nominal_pagu'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
               $sheet->setCellValueExplicit('J'.$row, $list['spp_lalu'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
               $sheet->setCellValueExplicit('L'.$row, $list['spp_ini'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
               $sheet->setCellValueExplicit('O'.$row, '=SUM(J'.$row.'+L'.$row.')', PHPExcel_Cell_DataType::TYPE_FORMULA);
               $sheet->setCellValueExplicit('R'.$row, '=SUM(G'.$row.'-O'.$row.')', PHPExcel_Cell_DataType::TYPE_FORMULA);
               $row++;
            }

            $sheet->mergeCells('A'.$row.':S'.$row);
            $sheet->getStyle('A32:S'.$row)->applyFromArray($borderTableStyledArray);
            $sheet->getStyle('G33:S'.($row-1))->getNumberFormat()->setFormatCode('#,##0.00_);(#,##0.00)');
            $rowBaru       = $row+1;

            $sheet->mergeCells('A'.$rowBaru.':B'.$rowBaru);
            $sheet->mergeCells('C'.$rowBaru.':F'.$rowBaru);
            $sheet->mergeCells('G'.$rowBaru.':I'.$rowBaru);
            $sheet->mergeCells('J'.$rowBaru.':K'.$rowBaru);
            $sheet->mergeCells('L'.$rowBaru.':N'.$rowBaru);
            $sheet->mergeCells('O'.$rowBaru.':Q'.$rowBaru);
            $sheet->mergeCells('R'.$rowBaru.':S'.$rowBaru);

            $sheet->setCellValue('C'.$rowBaru, 'Jumlah I');
            $sheet->setCellValueExplicit('G'.$rowBaru, $totalPagu, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('J'.$rowBaru, $totalSppLalu, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('L'.$rowBaru, $totalSppIni, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('O'.$rowBaru, $sppTotal, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('R'.$rowBaru, $totalSisaDana, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->getStyle('G'.$rowBaru.':S'.($rowBaru))->getNumberFormat()->setFormatCode('#,##0.00_);(#,##0.00)');

            $sheet->mergeCells('A'.($rowBaru+1).':B'.($rowBaru+1));
            $sheet->mergeCells('C'.($rowBaru+1).':F'.($rowBaru+1));
            $sheet->mergeCells('G'.($rowBaru+1).':I'.($rowBaru+1));
            $sheet->mergeCells('J'.($rowBaru+1).':K'.($rowBaru+1));
            $sheet->mergeCells('L'.($rowBaru+1).':N'.($rowBaru+1));
            $sheet->mergeCells('O'.($rowBaru+1).':Q'.($rowBaru+1));
            $sheet->mergeCells('R'.($rowBaru+1).':S'.($rowBaru+1));

            $sheet->setCellValue('A'.($rowBaru+1), 'II');
            $sheet->setCellValue('C'.($rowBaru+1), 'SEMUA KEGIATAN');

            $sheet->mergeCells('A'.($rowBaru+2).':B'.($rowBaru+2));
            $sheet->mergeCells('C'.($rowBaru+2).':F'.($rowBaru+2));
            $sheet->mergeCells('G'.($rowBaru+2).':I'.($rowBaru+2));
            $sheet->mergeCells('J'.($rowBaru+2).':K'.($rowBaru+2));
            $sheet->mergeCells('L'.($rowBaru+2).':N'.($rowBaru+2));
            $sheet->mergeCells('O'.($rowBaru+2).':Q'.($rowBaru+2));
            $sheet->mergeCells('R'.($rowBaru+2).':S'.($rowBaru+2));
            $sheet->setCellValue('C'.($rowBaru+2), GTFWConfiguration::GetValue('organization', 'fungsi_no').'.'.GTFWConfiguration::GetValue('organization', 'subfungsi_no').'.'.$dataSpp['program_kode'].'.'.$dataSpp['kegiatan_kode'], PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('G'.($rowBaru+2), $totalPagu, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('J'.($rowBaru+2), $totalSppLalu, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('L'.($rowBaru+2), $totalSppIni, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('O'.($rowBaru+2), $sppTotal, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('R'.($rowBaru+2), $totalSisaDana, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->getStyle('G'.($rowBaru+2).':S'.($rowBaru+2))->getNumberFormat()->setFormatCode('#,##0.00_);(#,##0.00)');

            $sheet->mergeCells('A'.($rowBaru+3).':B'.($rowBaru+3));
            $sheet->mergeCells('C'.($rowBaru+3).':F'.($rowBaru+3));
            $sheet->mergeCells('G'.($rowBaru+3).':I'.($rowBaru+3));
            $sheet->mergeCells('J'.($rowBaru+3).':K'.($rowBaru+3));
            $sheet->mergeCells('L'.($rowBaru+3).':N'.($rowBaru+3));
            $sheet->mergeCells('O'.($rowBaru+3).':Q'.($rowBaru+3));
            $sheet->mergeCells('R'.($rowBaru+3).':S'.($rowBaru+3));
            $sheet->setCellValue('A'.($rowBaru+3), 'II');
            $sheet->setCellValue('C'.($rowBaru+3), 'UANG PERSEDIAAN (UP)');

            $sheet->getStyle('A'.$rowBaru.':S'.($rowBaru+3))->applyFromArray($borderTableStyledArray);
            $sheet->getStyle('A'.$rowBaru.':S'.($rowBaru+3))->getFont()->setBold(true);
            $sheet->getStyle('A'.$rowBaru.':S'.($rowBaru+3))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
            $sheet->getStyle('A'.$rowBaru.':F'.($rowBaru+3))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

            $sheet->mergeCells('A'.($rowBaru+4).':C'.($rowBaru+4));
            $sheet->mergeCells('D'.($rowBaru+4).':I'.($rowBaru+4));
            $sheet->mergeCells('J'.($rowBaru+4).':S'.($rowBaru+4));
            $sheet->getRowDimension(($rowBaru+4))->setRowHeight('35');

            $sheet->setCellValue('A'.($rowBaru+4), 'Lampiran');
            $sheet->setCellValue('D'.($rowBaru+4), "- Dokumen \n- Pendukung");
            $sheet->setCellValue('J'.($rowBaru+4), "*) Surat Bukti Pengeluaran");
            $sheet->getStyle('A'.($rowBaru+4).':S'.($rowBaru+4))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_HAIR);
            $sheet->getStyle('A'.($rowBaru+4).':S'.($rowBaru+4))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_HAIR);
            $sheet->getStyle('A'.($rowBaru+4).':S'.($rowBaru+4))->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_HAIR);
            $sheet->getStyle('A'.($rowBaru+4).':S'.($rowBaru+4))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP)->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('A'.($rowBaru+4).':C'.($rowBaru+4))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP)->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

            $sheet->mergeCells('A'.($rowBaru+5).':K'.($rowBaru+5));
            $sheet->mergeCells('L'.($rowBaru+5).':S'.($rowBaru+5));
            $sheet->getRowDimension(($rowBaru+5))->setRowHeight('95');
            $sheet->getStyle('A'.($rowBaru+5).':S'.($rowBaru+5))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_HAIR);
            $sheet->getStyle('A'.($rowBaru+5).':S'.($rowBaru+5))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_HAIR);
            $sheet->getStyle('A'.($rowBaru+5).':S'.($rowBaru+5))->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_HAIR);
            $sheet->getStyle('A'.($rowBaru+5).':S'.($rowBaru+5))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);

            $sheet->setCellValue('L'.($rowBaru+5),$kota.','.$dataSpp['tanggal_string']."\nPejabat Pembuat Komitmen\n\n\n\n\n".$nama_ppk."\nNIP. ".$nip_ppk);
         }
      }

      # Save Excel document to local hard disk
      $this->Save();
   }
}
?>
