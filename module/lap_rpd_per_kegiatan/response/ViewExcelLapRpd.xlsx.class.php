<?php
/**
* ================= doc ====================
* FILENAME     : ViewExcelLapRpd.xlsx.class.php
* @package     : ViewExcelLapRpd
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-05-13
* @Modified    : 2015-05-13
* @Analysts    : Dyah Fajar N
* @contact     : eko.susilo@gamatechno.com
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/lap_rpd_per_kegiatan/business/AppLapRpdPerKegiatan.class.php';
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/user_unit_kerja/business/UserUnitKerja.class.php';

class ViewExcelLapRpd extends XlsxResponse
{
   # Internal Variables
   public $Excel;
   function L($indexLangName = '')
   {
      $lang       = GTFWConfiguration::GetValue('language',$indexLangName);
      if(!empty($lang)){
         return $lang;
      }

      return '';
   }

   function ProcessRequest()
   {
      if(isset($_GET)) {
         if(is_object($_GET)){
            $v    = $_GET->AsArray();
         }else{
            $v    = $_GET;
         }
      }
      $Obj              = new AppLapRpd();
      $tahun_anggaran   = Dispatcher::Instance()->Decrypt($v['tahun_anggaran']);
      $unitkerjaId      = Dispatcher::Instance()->Decrypt($v['unitkerja']);
      $data             = $Obj->GetDataRpdCetak($tahun_anggaran,$unitkerjaId);
      $dataMak          = $Obj->GetMak($tahun_anggaran,$unitkerjaId);
      $unitkerja        = $Obj->GetUnitKerja($unitkerjaId);
      $tahunanggaran    = $Obj->GetTahunAnggaranCetak($tahun_anggaran);

      $unitkerja_nama      = $unitkerja['unit_kerja_nama'];
      $tahunanggaran_nama  = $tahunanggaran['name'];

      # set writer for XlsxResponse
      # default Excel5 for .xls extension
      # option Excel2007 for .xlsx extension
      # $this->SetWriter('Excel2007');
      $this->SetFileName('laporan_rpd_kegiatan.xls');

      # Write your code here
      # Get active sheet
      # Document Setting
      $sheet   = $this->Excel->getActiveSheet(0);
      $sheet->setShowGridlines(true); # false: Hide the gridlines; true: show the gridlines
      # set worksheet name
      $sheet->setTitle('rpd');
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

      $sheet->setCellValueByColumnAndRow(0, 1, $this->L('lap_rincian_penggunaan_dana_per_kegiatan'));
      $sheet->setCellValueByColumnAndRow(0, 2, $this->L('tahun_periode'));
      $sheet->setCellValueByColumnAndRow(2, 2, $tahunanggaran_nama);
      $sheet->setCellValueByColumnAndRow(0, 3, $this->L('unit').' / '.$this->L('sub_unit'));
      $sheet->setCellValueByColumnAndRow(2, 3, $unitkerja_nama);

      $dataGrid   = $data;
      $i          = 0;
      $x          = 0;
      $program_nomor    = ''; //inisialisasi program
      $kegiatan_nomor   = ''; //inisialisasi kegiatan
      $sub_keg_nomor    = ''; //inisialisasi subkegiatan
      $mak     = '';
      $no      = 1;

      $num     = $row_header = 5;
      $sheet->setCellValueByColumnAndRow(0, $num, $this->L('no'));
      $sheet->setCellValueByColumnAndRow(1, $num,  $this->L('kode'));
      $sheet->setCellValueByColumnAndRow(2, $num, $this->L('label_rincian'));
      $sheet->setCellValueByColumnAndRow(3, $num, $this->L('unit').' / '.$this->L('sub_unit'));
      $sheet->setCellValueByColumnAndRow(4, $num, $this->L('volume'));
      $sheet->setCellValueByColumnAndRow(5, $num, $this->L('harga_satuan'));

      $col              = 6;
      $header           = $dataMak;
      $max_header       = sizeof($header);
      $end_header_col   = 'I';
      /**
      * membuat header
      */
      if($max_header > 0){
         // $this->mWorksheets['Data']->merge_cells(5,$col,5,($col + $max_header)-1);
         $sheet->setCellValueByColumnAndRow($col, $num,$this->L('perhitungan'));
         $start_col     = $sheet->getCellByColumnAndRow(($col),5)->getColumn();
         $column_head   = $col;
         for($n=0;$n < $max_header;$n++) {
            $sheet->getColumnDimension($sheet->getCellByColumnAndRow(($col+$n),$num)->getColumn())->setWidth(20);
            $sheet->getCellByColumnAndRow(($col+$n), 6)->setValueExplicit(($header[$n]['makNama'] == '') ? '-' : $header[$n]['makNama'], PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->getCellByColumnAndRow(($col+$n), 7)->setValueExplicit(($header[$n]['makKode'] == '') ? '-' : $header[$n]['makKode'], PHPExcel_Cell_DataType::TYPE_STRING);
            $column_head+=1;
         }
         $max_col          = $sheet->getCellByColumnAndRow(($column_head-1),$num)->getColumn();
         $end_header_col   = $sheet->getCellByColumnAndRow($column_head,$num)->getColumn();
         $row_header       = 7;
         $sheet->mergeCells($start_col.'5:'.$max_col.'5');

         $sheet->mergeCells('A5:A7');
         $sheet->mergeCells('B5:B7');
         $sheet->mergeCells('C5:C7');
         $sheet->mergeCells('D5:D7');
         $sheet->mergeCells('E5:E7');
         $sheet->mergeCells('F5:F7');
         $sheet->mergeCells($end_header_col.'5:'.$end_header_col.'7');
      } else {
         $end_header_col   = $sheet->getCellByColumnAndRow($col, $num)->getColumn();
      }
      /**
      * end
      */
      $sheet->setCellValueByColumnAndRow(($col+$max_header), $num,$this->L('jumlah_biaya'));
      $sheet->getStyle('A'.$num.':'.$end_header_col.$row_header)->applyFromArray(array(
         'font' => array(
            'bold' => true
         ), 'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            'wrap' => true,
            'shrinkToFit' => true
         ), 'borders' => array(
            'allborders' => array(
               'style' => PHPExcel_Style_Border::BORDER_THIN
            )
         )
      ));

      // set column Dimension
      $sheet->getColumnDimension('A')->setWidth(5);
      $sheet->getColumnDimension('B')->setWidth(16);
      $sheet->getColumnDimension('C')->setWidth(60);
      $sheet->getColumnDimension('D')->setWidth(20);
      $sheet->getColumnDimension('E')->setWidth(10);
      $sheet->getColumnDimension('F')->setWidth(20);
      $sheet->getColumnDimension($end_header_col)->setWidth(20);
      $sheet->mergeCells('A1:'.$end_header_col.'1');
      $sheet->mergeCells('A2:B2');
      $sheet->mergeCells('C2:'.$end_header_col.'2');
      $sheet->mergeCells('A3:B3');
      $sheet->mergeCells('C3:'.$end_header_col.'3');

      $sheet->getStyle('A1:'.$end_header_col.'1')->applyFromArray(array(
         'font' => array(
            'size' => 11
         )
      ));

      $sheet->getStyle('A1:'.$end_header_col.'3')->applyFromArray(array(
         'font' => array(
            'bold' => true
         ), 'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP
         )
      ));

      $rows       = 8;
      if(empty($dataGrid)){
         $sheet->mergeCells('A8:'.$end_header_col.'8');
         $sheet->setCellValue('A8', GTFWConfiguration::GetValue('language', 'data_kosong'));
         $sheet->getRowDimension(8)->setRowHeight(18);
         $sheet->getStyle('A8:'.$end_header_col.'8')->applyFromArray(array(
            'alignment' => array(
               'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
               'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            ), 'borders' => array(
               'allborders' => array(
                  'style' => PHPExcel_Style_Border::BORDER_THIN
               )
            )
         ));
      }else{
         $i       = 0;
         $x       = 0;
         $program_nomor    = ''; //inisialisasi program
         $kegiatan_nomor   = ''; //inisialisasi kegiatan
         $sub_keg_nomor    = ''; //inisialisasi subkegiatan
         $mak              = '';
         $no               = 1;

         for ($i=0; $i<sizeof($dataGrid);) {
            //=========strat setting tampilan=======================
            $view_program_nomor     = $dataGrid[$i]['program_nomor'];
            $view_kegiatan_nomor    = $dataGrid[$i]['kegiatan_nomor'];
            if(($program_nomor == $dataGrid[$i]['program_id']) &&
               ($kegiatan_nomor == $dataGrid[$i]['subprogram_id']) &&
               ($sub_keg_nomor == $dataGrid[$i]['subkegiatan_id']) &&
               ($mak == $dataGrid[$i]['mak_id'])) {
               //komponen
               $send[$x]['kode']    = '';
               $send[$x]['nama']    = " - ".$dataGrid[$i]['komponen_nama'];
               $send[$x]['satuan_setuju']    = $dataGrid[$i]['satuan_setuju'].' '.$dataGrid[$i]['nama_satuan'];
               $send[$x]['nominal_setuju']   = $dataGrid[$i]['nominal_setuju'];
               $send[$x]['jumlah_setuju']    = $dataGrid[$i]['jumlah_setuju'];
               $send[$x]['nomor']            = $dataGrid[$i]['nomor'];
               $send[$x]['jenis']            = "komponen";
               $send[$x]['mak_id']           = $dataGrid[$i]['mak_id'];
               $send[$x]['format']           = $format;
               $send[$x]['format_curr']      = $formatCurrency;
               $send[$x]['unit_subunit']     = $dataGrid[$i]['unit_subunit'];
               $i++;
            } elseif($program_nomor != $dataGrid[$i]['program_id']) {
               //program
               $program_nomor       = $dataGrid[$i]['program_id'];
               $send[$x]['kode']    =$view_program_nomor;
               $dataGrid[$i]['program_nama_rkakl']    = empty($dataGrid[$i]['program_nama_rkakl'])?'-':$dataGrid[$i]['program_nama_rkakl'];
               $send[$x]['nama']    = $dataGrid[$i]['program_nama']."\n".'[ '.$dataGrid[$i]['program_nama_rkakl'].' ]';
               $send[$x]['nomor']   = $no;
               $send[$x]['jenis']   = "program";
               $no++;
            } elseif($kegiatan_nomor != $dataGrid[$i]['subprogram_id']) {
               //kegiatan
               $kegiatan_nomor      = $dataGrid[$i]['subprogram_id'];

               $jenis_keg_id        = $dataGrid[$i]['jenis_keg_id'];
               $send[$x]['kode']    = $view_kegiatan_nomor;
               $dataGrid[$i]['kegiatan_nama_rkakl']   = empty($dataGrid[$i]['kegiatan_nama_rkakl'])?'-':$dataGrid[$i]['kegiatan_nama_rkakl'];
               $send[$x]['nama']    = $dataGrid[$i]['kegiatan_nama']."\n".'[ '.$dataGrid[$i]['kegiatan_nama_rkakl'].' ]';
               $send[$x]['jenis']   = "kegiatan";
            } elseif($sub_keg_nomor != $dataGrid[$i]['subkegiatan_id']) {
               //subkegiatan
               //===========start pengaturan tampilan kode;=======================
               $jenisKegId          = $dataGrid[$i]['jenis_keg_id'];
               $dataGrid[$i]['subkegiatan_nomor']  = $dataGrid[$i]['subkegiatan_nomor'];
               //===========end pengaturan tampilan kode;=======================
               $sub_keg_nomor       = $dataGrid[$i]['subkegiatan_id'];
               $jenis_keg_id        = $dataGrid[$i]['jenis_keg_id'];
               $send[$x]['kode']    = $dataGrid[$i]['subkegiatan_nomor'];
               $dataGrid[$i]['subkegiatan_nama_rkakl']   = empty($dataGrid[$i]['subkegiatan_nama_rkakl'])?'-':$dataGrid[$i]['subkegiatan_nama_rkakl'];
               $send[$x]['nama']    = $dataGrid[$i]['subkegiatan_nama'] ."\n". '[ '.$dataGrid[$i]['subkegiatan_nama_rkakl'].' ]';
               $send[$x]['jenis']   = "subkegiatan";
            }elseif ($mak != $dataGrid[$i]['mak_id']) {
               $mak                 = $dataGrid[$i]['mak_id'];
               $send[$x]['sts']     = 'mak';
            }
            $x++;
         }

         $i = sizeof($send)-1;
         $nominal_usulan=0;
         while($i >= 0) {
            if($send[$i]['jenis'] == 'komponen') {
               $jumlah_setuju    += $send[$i]['jumlah_setuju'];
               $nominal_setuju   += $send[$i]['nominal_setuju'];
            }
            if($send[$i]['jenis'] == 'subkegiatan') {
               $send[$i]['jumlah_setuju'] = $jumlah_setuju;
               $jumlah_setuju_sk    += $jumlah_setuju;
               $jumlah_setuju=0;
            }
            if($send[$i]['jenis'] == 'kegiatan') {
               $send[$i]['jumlah_setuju'] = $jumlah_setuju_sk;
               $jumlah_setuju_program     += $jumlah_setuju_sk;
               $jumlah_setuju=0;
            }
            if($send[$i]['jenis'] == 'program') {
               $send[$i]['jumlah_setuju'] = $jumlah_setuju_program;
               $jumlah_setuju_program     = 0;
            }
            $i--;
         }

         for($j=0;$j<sizeof($send);$j++) {
            if($send[$j]['sts'] == 'mak'){
               continue;
            }
            if($send[$j]['jumlah_setuju'] == "NULL") {
               $send[$j]['jumlah_setuju'] = '';
            } else {
                  $send[$j]['jumlah_setuju'] = $send[$j]['jumlah_setuju'];
            }

            if(strtoupper($send[$j]['jenis']) == 'PROGRAM'){
               $sheet->getCellByColumnAndRow(0, $rows)->setValueExplicit($send[$j]['nomor'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            }else{
               $sheet->getCellByColumnAndRow(0, $rows)->setValueExplicit($send[$j]['nomor'], PHPExcel_Cell_DataType::TYPE_NULL);
            }
            $sheet->getCellByColumnAndRow(1, $rows)->setValueExplicit($send[$j]['kode'], PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->getCellByColumnAndRow(2, $rows)->setValueExplicit($send[$j]['nama'], PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->getCellByColumnAndRow(3, $rows)->setValueExplicit($send[$j]['unit_subunit'], PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->getCellByColumnAndRow(4, $rows)->setValueExplicit($send[$j]['satuan_setuju'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->getCellByColumnAndRow(5, $rows)->setValueExplicit($send[$j]['nominal_setuju'], PHPExcel_Cell_DataType::TYPE_NUMERIC);

            if($max_header > 0){
               for($f=0;$f < $max_header;$f++) {
                  if( $send[$j]['jenis'] == 'komponen'){
                     if($send[$j]['mak_id'] == $header[$f]['mak_id']){
                        $sheet->getCellByColumnAndRow(($col+$f), $rows)->setValueExplicit($send[$j]['jumlah_setuju'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                     }else{
                        $sheet->getCellByColumnAndRow(($col+$f), $rows)->setValueExplicit(0, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                     }
                  }else{
                     $sheet->getCellByColumnAndRow(($col+$f), $rows)->setValueExplicit(0, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  }
               }
            }
            $sheet->getCellByColumnAndRow(($col+$max_header), $rows)->setValueExplicit($send[$j]['jumlah_setuju'], PHPExcel_Cell_DataType::TYPE_NUMERIC);

            switch (strtoupper($send[$j]['jenis'])) {
               case 'PROGRAM':
                  $sheet->getStyle('A'.$rows.':'.$end_header_col.$rows)->applyFromArray(array(
                     'font' => array(
                        'bold' => true
                     )
                  ));
                  break;
               case 'KEGIATAN':
                  $sheet->getStyle('A'.$rows.':'.$end_header_col.$rows)->applyFromArray(array(
                     'font' => array(
                        'bold' => true,
                        'italic' => true
                     )
                  ));
                  break;
               case 'SUBKEGIATAN':
                  $sheet->getStyle('A'.$rows.':'.$end_header_col.$rows)->applyFromArray(array(
                     'font' => array(
                        'italic' => true
                     )
                  ));
                  break;
               default:
                  # code...
                  break;
            }
            $rows+=1;
         }

         $sheet->mergeCells('A'.$rows.':F'.$rows);
         $sheet->setCellValue('A'.$rows, GTFWConfiguration::GetValue('language', 'total'));

         if($max_header > 0){
            for($x=0;$x < $max_header;$x++) {
               $cols       = $sheet->getCellByColumnAndRow(($col+$x), $rows)->getColumn();
               $sheet->setCellValueByColumnAndRow(($col+$x), $rows, '=SUM('.$cols.'8:'.$cols.($rows-1).')');
            }
         }
         $sheet->setCellValueByColumnAndRow(($col+$max_header), $rows, '=SUM('.$end_header_col.'8:'.$end_header_col.($rows-1).')');

         $sheet->getStyle('A8:'.$end_header_col.$rows)->applyFromArray(array(
            'borders' => array(
               'allborders' => array(
                  'style' => PHPExcel_Style_Border::BORDER_THIN
               )
            ), 'alignment' => array(
               'vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP,
               'wrap' => true,
               'shrinkToFit' => true
            )
         ));

         $sheet->getStyle('A'.$rows.':F'.$rows)->applyFromArray(array(
            'alignment' => array(
               'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
               'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            )
         ));
         $sheet->getStyle('F8:'.$end_header_col.$rows)->getNumberFormat()->setFormatCode('_("Rp"* #,##0_);_("Rp"* \(#,##0\);_("Rp"* "-"_);_(@_)');
         $sheet->getStyle('A'.$rows.':'.$end_header_col.$rows)->getFont()->setBold(true);
      }
      # Save Excel document to local hard disk
      $this->Save();
   }
}
?>