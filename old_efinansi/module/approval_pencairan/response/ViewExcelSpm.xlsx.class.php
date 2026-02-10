<?php
# Doc
# @category    XlsxResponse
# @package     ViewExcelSpm
# @copyright   Copyright (c) 2011 Gamatechno
# @author      By ucil
# @Created     2012-08-15
# @modified    2012-08-15
# @Modified    By ucil
# @contact     ecko[dot]ucil[at]gmail[dot]com
# /Doc
require_once GTFWConfiguration::GetValue('application','docroot').
'module/approval_pencairan/business/Spm.class.php';
require_once GTFWConfiguration::GetValue('application','docroot').
'module/approval_pencairan/business/AppApprovalPencairan.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot').
'main/function/terbilang.php';
class ViewExcelSpm extends XlsxResponse
{
   # Internal Variables
   public $Excel;
   
   function ProcessRequest()
   {
      # set writer for XlsxResponse
      # default Excel5 for .xls extension
      # option Excel2007 for .xlsx extension
      # $this->SetWriter('Excel2007');
      $this->SetFileName('surat_perintah_membayar.xls');
      
      # Write your code here
      # Get active sheet
      # Document Setting
      $sheet   = $this->Excel->getActiveSheet(0);
      $sheet->setShowGridlines(false); # true: Hide the gridlines; false: show the gridlines
      # set worksheet name
      $sheet->setTitle('Surat Perintah Membayar');
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
      
      $spmObj         = new Spm();
      $numberObj      = new Number();
      $Obj            = new AppApprovalPencairan();
      
      $listCarabayar          = $spmObj->ListCaraBayar();
      $listJenisPembayaran    = $spmObj->ListJenisPembayaran();
      $listSifatPembayaran    = $spmObj->ListSifatPembayaran();
      $dataId                 = Dispatcher::Instance()->Decrypt($_GET['dataId']);
      $spmId                  = Dispatcher::Instance()->Decrypt($_GET['spmId']);
      
      if (isset($spmId) AND $spmId != '')
      {
         # code...
         $spm_data           = $spmObj->GetSpmBySpmId($spmId);
         $dipa               = $spmObj->GetDipa();
      }
      $dataApprovalPencairan  = $Obj->GetDataById($dataId);
      $data_detil             = $spmObj->ListKegiatanByApprovalId($dataId);
      $spm_pajak              = $spmObj->GetPajakSpm($spmId);
      
      # organization
      $kementerian_lembaga_nama     = GTFWConfiguration::GetValue('organization', 'kementerian_lembaga_nama');
      $kota                         = GTFWConfiguration::GetValue('organization', 'city'); 
      $kode_kota                    = GTFWConfiguration::GetValue('organization', 'city_number');
      $dipa                         = wordwrap($dipa['dipa_nama'].' Tanggal : '.$this->_dateToIndo($dipa['dipa_tanggal']));
      $satker_no                    = GTFWConfiguration::GetValue('organization', 'satker_no');
      $company_name                 = GTFWConfiguration::GetValue('organization', 'company_name');
      $fungsi_no                    = GTFWConfiguration::GetValue('organization', 'fungsi_no'); 
      $subfungsi_no                 = GTFWConfiguration::GetValue('organization', 'subfungsi_no'); 
      $unit_org_eselon_no           = GTFWConfiguration::GetValue('organization', 'unit_org_eselon_no');
      $program_no                   = GTFWConfiguration::GetValue('organization', 'program_no');
      $lembaga_nomor                = GTFWConfiguration::GetValue('organization','kementerian_lembaga_no');
      $nomor_lokasi                 = GTFWConfiguration::GetValue('organization','nomor_lokasi');
      $pejabat_penerbit_spm         = GTFWConfiguration::GetValue('organization', 'pejabat_penerbit_spm');
      $pejabat_penerbit_spm_nip     = GTFWConfiguration::GetValue('organization', 'pejabat_penerbit_spm_nip');
      
      $headerStyleArray    = array(
         'font' => array(
            'bold' => true, 
            'size' => 14
         ), 
         'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, 
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
         )
      );
      
      $tableBorders     = array(
         'borders' => array(
            'allborders' => array(
               'style' => PHPExcel_Style_Border::BORDER_THIN, 
               'color' => array ('rgb' => '000000')
            )
         ), 
         'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT, 
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP
         )
      );
      
      $tableHeaderStyle    = array(
         'font' => array(
            'bold' => true
         ), 
         'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, 
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
         )
      );
      
      # document setting
      $sheet->getRowDimension('1')->setRowHeight('28');
      $sheet->getRowDimension('2')->setRowHeight('27');
      $sheet->getColumnDimension('C')->setWidth('4');
      $sheet->getColumnDimension('H')->setWidth('4');
      
      $sheet->mergeCells('A1:M1');
      $sheet->mergeCells('A2:M2');
      $sheet->mergeCells('F3:G3');
      $sheet->mergeCells('H3:I3');
      $sheet->mergeCells('J3:K3');
      $sheet->mergeCells('A5:D5');
      $sheet->mergeCells('A6:D6');
      $sheet->mergeCells('E5:M5');
      $sheet->mergeCells('E6:M6');
      $sheet->mergeCells('A7:M7');
      $sheet->mergeCells('A8:B8');
      $sheet->mergeCells('D8:F8');
      $sheet->mergeCells('G8:H8');
      $sheet->mergeCells('A10:E10');
      $sheet->mergeCells('F10:G10');
      $sheet->mergeCells('H10:I10');
      $sheet->mergeCells('J10:M10');
      $sheet->mergeCells('A11:E11');
      $sheet->mergeCells('F11:G11');
      $sheet->mergeCells('H11:I11');
      $sheet->mergeCells('J11:M11');
      $sheet->mergeCells('F13:M13');
      $sheet->mergeCells('F14:M14');
      $sheet->mergeCells('F15:M15');
      $sheet->mergeCells('F16:M16');
      $sheet->mergeCells('F17:G17');
      $sheet->mergeCells('F18:G18');
      $sheet->mergeCells('I17:M17');
      $sheet->mergeCells('I18:M18');
      $sheet->mergeCells('A20:G20');
      $sheet->mergeCells('H20:M20');
      $sheet->mergeCells('A21:B21');
      $sheet->mergeCells('C21:E21');
      $sheet->mergeCells('F21:G21');
      $sheet->mergeCells('H21:K21');
      $sheet->mergeCells('L21:M21');
      
      $sheet->getStyle('A1:M1')->applyFromArray($headerStyleArray);
      $sheet->getStyle('A2:M2')->applyFromArray($headerStyleArray);
      $sheet->getStyle('A3:M3')->getFont()->setBold(true);
      $sheet->getStyle('A3:M3')->getAlignment()
      ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
      ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
      $sheet->getStyle('J3')->getAlignment()
      ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
      $sheet->getStyle('E6:M6')->getAlignment()
      ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
      $sheet->getStyle('C8')->getBorders()->getAllBorders()
      ->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
      $sheet->getStyle('C8')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
      ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
      $sheet->getStyle('I8')->getAlignment()
      ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
      $sheet->getStyle('A10:M11')->applyFromArray($tableBorders);
      $sheet->getStyle('A10:M10')->applyFromArray($tableHeaderStyle);
      $sheet->getStyle('A10:M11')->getAlignment()
      ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
      $sheet->getStyle('H17')->getBorders()->getAllBorders()
      ->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
      $sheet->getStyle('H17')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
      ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
      $sheet->getStyle('H18')->getBorders()->getAllBorders()
      ->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
      $sheet->getStyle('H18')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
      ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
      $sheet->getStyle('F13:M13')->getFont()->setBold(true);
      $sheet->getStyle('F15:M15')->getFont()->setBold(true);
      $sheet->getStyle('F17:G17')->getFont()->setBold(true);
      $sheet->getStyle('F18:G18')->getFont()->setBold(true);
      
      # header
      $sheet->setCellValue('A1', $kementerian_lembaga_nama);
      $sheet->setCellValue('A2', 'SURAT PERINTAH MEMBAYAR');
      $sheet->setCellValue('E3', 'Tanggal');
      $sheet->setCellValueExplicit(
         'F3', 
         date_format(date_create($spm_data['spm_tanggal']), 'd-m-Y'), 
         PHPExcel_Cell_DataType::TYPE_STRING
      );
      $sheet->setCellValue('H3', 'Nomor ');
      $sheet->setCellValueExplicit(
         'J3', 
         ': '.$spm_data['spm_nomor'], 
         PHPExcel_Cell_DataType::TYPE_STRING
      );
      
      $sheet->setCellValue('A5', 'Kuasa Bendahara Umum Negara, KPPN');
      $sheet->setCellValue('A6', 'Agar melakukan pembayaran sejumlah');
      $sheet->setCellValueExplicit(
         'E5', 
         $kota.' ('.$kode_kota.')', 
         PHPExcel_Cell_DataType::TYPE_STRING
      );
      $sheet->setCellValueExplicit(
         'E6', 
         $spm_data['spm_nominal'], 
         PHPExcel_Cell_DataType::TYPE_NUMERIC
      );
      $sheet->setCellValue('A7', '***'.$numberObj->terbilang($spm_data['spm_nominal'],2).'***');
      $sheet->setCellValue('A8', 'Cara Pembayaran');
      $sheet->setCellValueExplicit('C8', $spm_data['cara_bayar_kode'], PHPExcel_Cell_DataType::TYPE_STRING);
      $sheet->setCellValue('D8', $spm_data['cara_bayar_nama']);
      $sheet->setCellValue('G8', 'Tahun Anggaran');
      $sheet->setCellValue('I8', $dataApprovalPencairan['tahun_anggaran_label']);
      
      $sheet->setCellValue('A10', 'Dasar Pembayaran');
      $sheet->setCellValue('F10', ' Satker');
      $sheet->setCellValue('H10', 'KWN');
      $sheet->setCellValue('J10', 'Nama Satker');
      
      $sheet->setCellValueExplicit(
         'A11', 
         $dipa, 
         PHPExcel_Cell_DataType::TYPE_STRING
      );
      $sheet->setCellValueExplicit(
         'F11', 
         $satker_no, 
         PHPExcel_Cell_DataType::TYPE_STRING
      );
      $sheet->setCellValue(
         'H11', 
         'KD'
      );
      $sheet->setCellValue(
         'J11', 
         $company_name
      );
      
      $sheet->setCellValueExplicit(
         'F13', 
         'Fungsi, Sub Fungsi, Unit Es.I, Program', 
         PHPExcel_Cell_DataType::TYPE_STRING
      );
      $sheet->setCellValueExplicit(
         'F14', 
         $fungsi_no.'.'.$subfungsi_no.'.'.$unit_org_eselon_no.'.'.$program_no, 
         PHPExcel_Cell_DataType::TYPE_STRING
      ); 
      $sheet->setCellValueExplicit(
         'F15', 
         'Kegiatan, Output, Sub Kelompok Akun', 
         PHPExcel_Cell_DataType::TYPE_STRING
      );
      $sheet->setCellValueExplicit(
         'F16', 
         $data_detil[0]['keg_nomor'].'.'.$data_detil[0]['output_kode'].'.'.substr($data_detil[0]['mak_kode'],0,4), 
         PHPExcel_Cell_DataType::TYPE_STRING
      );
      $sheet->setCellValueExplicit(
         'F17', 
         'Jenis Pembayaran', 
         PHPExcel_Cell_DataType::TYPE_STRING
      );
      $sheet->setCellValueExplicit(
         'H17', 
         $spm_data['jenis_bayar_kode'], 
         PHPExcel_Cell_DataType::TYPE_STRING
      );
      $sheet->setCellValueExplicit(
         'I17', 
         $spm_data['jenis_bayar_nama'], 
         PHPExcel_Cell_DataType::TYPE_STRING
      );
      $sheet->setCellValue(
         'F18', 
         'Sifat Pembayaran'
      );
      $sheet->setCellValueExplicit(
         'H18', 
         $spm_data['sifat_bayar_kode'], 
         PHPExcel_Cell_DataType::TYPE_STRING
      );
      $sheet->setCellValueExplicit(
         'I18', 
         $spm_data['sifat_bayar_nama'], 
         PHPExcel_Cell_DataType::TYPE_STRING
      );
      
      # table main content
      $sheet->setCellValue('A20', 'PENGELUARAN');
      $sheet->setCellValue('H20', 'POTONGAN');
      $sheet->setCellValue('A21', 'AKUN');
      $sheet->setCellValue('C21', 'COA');
      $sheet->setCellValue('F21', 'Jumlah Uang (Rp.)');
      $sheet->setCellValue('H21', 'Lemb/Unit/Lok/MAP');
      $sheet->setCellValue('L21', 'Jumlah Uang (Rp.)');
      
      $rowstart      = 22;
      for($i = 0; $i < count($data_detil); $i++):
         $sheet->setCellValueExplicit(
            'A'.($rowstart+$i), 
            $data_detil[$i]['mak_kode'], 
            PHPExcel_Cell_DataType::TYPE_STRING
         );
         $sheet->setCellValueExplicit(
            'C'.($rowstart+$i), 
            $data_detil[$i]['coa_nama'], 
            PHPExcel_Cell_DataType::TYPE_STRING
         );
         $sheet->setCellValueExplicit(
            'F'.($rowstart+$i), 
            $data_detil[$i]['spp_ini'], 
            PHPExcel_Cell_DataType::TYPE_NUMERIC
         );
         if($i < 1):
            $nominal_pajak[$i]               = $spm_pajak['nominal_pajak'];
            $sheet->setCellValueExplicit(
               'H'.($rowstart+$i), 
               $lembaga_nomor.'/'.$unit_org_eselon_no.'/'.$nomor_lokasi.'/'.$spm_pajak['kode_pajak'], 
               PHPExcel_Cell_DataType::TYPE_STRING
            );
            $sheet->setCellValueExplicit(
               'L'.($rowstart+$i), 
               $spm_pajak['nominal_pajak'], 
               PHPExcel_Cell_DataType::TYPE_NUMERIC
            );
         else:
            $sheet->setCellValueExplicit(
               'H'.($rowstart+$i), 
               '', 
               PHPExcel_Cell_DataType::TYPE_STRING
            );
            $sheet->setCellValueExplicit(
               'L'.($rowstart+$i), 
               '', 
               PHPExcel_Cell_DataType::TYPE_NULL
            );
            $nominal_pajak[$i]               = 0;
         endif;
         $sheet->mergeCells('A'.($rowstart+$i).':B'.($rowstart+$i));
         $sheet->mergeCells('C'.($rowstart+$i).':E'.($rowstart+$i));
         $sheet->mergeCells('F'.($rowstart+$i).':G'.($rowstart+$i));
         $sheet->mergeCells('H'.($rowstart+$i).':K'.($rowstart+$i));
         $sheet->mergeCells('L'.($rowstart+$i).':M'.($rowstart+$i));
      endfor;
      $sheet->setCellvalue('A'.($rowstart+$i), 'Jumlah Pengeluaran');
      $sheet->mergeCells('A'.($rowstart+$i).':E'.($rowstart+$i));
      $sheet->setCellValue('F'.($rowstart+$i), '=SUM(F22:G'.(($rowstart+$i)-1).')');
      $sheet->mergeCells('F'.($rowstart+$i).':G'.($rowstart+$i));
      $sheet->setCellValue('H'.($rowstart+$i), 'Jumlah Potongan');
      $sheet->mergeCells('H'.($rowstart+$i).':K'.($rowstart+$i));
      $sheet->setCellValue('L'.($rowstart+$i), '=SUM(L22:M'.(($rowstart+$i)-1).')');
      $sheet->mergeCells('L'.($rowstart+$i).':M'.($rowstart+$i));
      $sheet->getStyle('A20:M'.($rowstart+$i))->applyFromArray($tableBorders);
      $sheet->getStyle('A20:M21')->applyFromArray($tableHeaderStyle);
      
      $sheet->getStyle('A22:M'.($rowstart+$i))->getAlignment()
      ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
      ->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
      $sheet->getStyle('A'.($rowstart+$i).':M'.($rowstart+$i))->getFont()->setBold(true);
      $sheet->getStyle('F22:G'.($rowstart+$i))->getAlignment()
      ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
      $sheet->getStyle('L22:M'.($rowstart+$i))->getAlignment()
      ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
      
      $new_row    = ($rowstart+$i)+1;
      $sheet->setCellValue('A'.$new_row, 'Kepada');
      $sheet->setCellValue('A'.($new_row+1), 'NPWP');
      $sheet->setCellValue('A'.($new_row+2), 'Nomor/Nama Rek');
      $sheet->setCellValue('A'.($new_row+3), 'Bank/Pos');
      $sheet->setCellValue('A'.($new_row+4), 'Uraian');
      $sheet->mergeCells('A'.($new_row).':B'.($new_row));
      $sheet->mergeCells('A'.($new_row+1).':B'.($new_row+1));
      $sheet->mergeCells('A'.($new_row+2).':B'.($new_row+2));
      $sheet->mergeCells('A'.($new_row+3).':B'.($new_row+3));
      $sheet->mergeCells('A'.($new_row+4).':B'.($new_row+4));
      
      $sheet->setCellValue('C'.$new_row, $spm_data['spm_nama']);
      $sheet->setCellValueExplicit(
         'C'.($new_row+1), 
         $spm_data['spm_npwp'], 
         PHPExcel_Cell_DataType::TYPE_STRING
      );
      $sheet->setCellValueExplicit(
         'C'.($new_row+2), 
         $spm_data['spm_rekening'], 
         PHPExcel_Cell_DataType::TYPE_STRING
      );
      $sheet->setCellValue('C'.($new_row+3), $spm_data['spm_bank']);
      $sheet->setCellValue('C'.($new_row+4), $spm_data['spm_keterangan']);
      $sheet->mergeCells('C'.($new_row).':M'.($new_row));
      $sheet->mergeCells('C'.($new_row+1).':M'.($new_row+1));
      $sheet->mergeCells('C'.($new_row+2).':M'.($new_row+2));
      $sheet->mergeCells('C'.($new_row+3).':M'.($new_row+3));
      $sheet->mergeCells('C'.($new_row+4).':M'.($new_row+4));
      
      $text          = $kota.", ".$this->_dateToIndo(date('Y-m-d', time()))."\nA.n. Kuasa Pengguna Anggaran\nPEJABAT PENERBIT SPM\n\n\n\n\n\n".$pejabat_penerbit_spm."\n".$pejabat_penerbit_spm_nip;
      
      $sheet->setCellValue('J'.($new_row+6), $text);
      $sheet->mergeCells('J'.($new_row+6).':M'.($new_row+15));
      
      $sheet->getStyle('A'.($new_row).':M'.($new_row+20))->getAlignment()
      ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT)
      ->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
      
      # Save Excel document to local hard disk
      $this->Save();
   }
   
   private function _dateToIndo($date)
   {
       $indonesian_months = array('N/A',
                                  'Januari',
                                  'Februari',
                                  'Maret',
                                  'April',
                                  'Mei',
                                  'Juni',
                                  'Juli',
                                  'Agustus',
                                  'September',
                                  'Oktober',
                                  'Nopember',
                                  'Desember');
       if(preg_match("/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})/", $date, $patch))
       {
           $year   = (int) $patch[1];
           $month  = (int) $patch[2];
           $month  = $indonesian_months[(int) $patch[2]];
           $day    = (int) $patch[3];
           $hour   = (int) $patch[4];
           $min    = (int) $patch[5];
           $sec    = (int) $patch[6];
           
           $return = $day.' '.$month.' '.$year.' '.$hour.':'.$min.':'.$sec;
       }
       elseif(preg_match("/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})/", $date, $patch))
       {
           $year   = (int) $patch[1];
           $month  = (int) $patch[2];
           $month  = $indonesian_months[$month];
           $day    = (int) $patch[3];
           
           $return = $day.' '.$month.' '.$year;
       }
       else
       {
           $return = (int) $date;
       }
       return $return;
   }
}
?>
