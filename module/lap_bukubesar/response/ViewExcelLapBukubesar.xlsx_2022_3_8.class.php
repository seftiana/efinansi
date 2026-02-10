<?php
/**
* ================= doc ====================
* FILENAME     : ViewExcelLapBukubesar.xlsx.class.php
* @package     : ViewExcelLapBukubesar
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-05-25
* @Modified    : 2017-03-22
* @Analysts    : Dyah Fajar N
* @contact     : eko.susilo@gamatechno.com
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/lap_bukubesar/business/AppLapBukubesar.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') .
'main/function/date.php';

class ViewExcelLapBukubesar extends XlsxResponse
{
   # Internal Variables
   public $Excel;

   function ProcessRequest()
   {
      $Obj           = new AppLapBukubesar();
      $requestData['coa_id'] = Dispatcher::Instance()->Decrypt($_GET['rekening']);
      $requestData['coa_nama'] = Dispatcher::Instance()->Decrypt($_GET['coa_nama']);
      $requestData['start_date'] =Dispatcher::Instance()->Decrypt($_GET['tgl_awal']);
      $requestData['end_date'] = Dispatcher::Instance()->Decrypt($_GET['tgl_akhir']);
      $dataList      = $Obj->GetBukuBesarHis($requestData);
      $info_coa      = $Obj->GetInfoCoa($requestData['coa_id']);

      # set writer for XlsxResponse
      # default Excel5 for .xls extension
      # option Excel2007 for .xlsx extension
      # $this->SetWriter('Excel2007');
      $this->SetFileName('laporan_buku_besar.xls');

      # Write your code here
      # Get active sheet
      # Document Setting
      $sheet   = $this->Excel->getActiveSheet(0);
      $sheet->setShowGridlines(true); # false: Hide the gridlines; true: show the gridlines
      # set worksheet name
      $sheet->setTitle('buku besar');
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

      if (empty($dataList)) {
         $sheet->setCellValue('A1', GTFWConfiguration::GetValue('language', 'data_kosong'));
      } else {
         #set header
         $sheet->getColumnDimension('A')->setWidth(18);
         $sheet->getColumnDimension('B')->setWidth(25);
         $sheet->getColumnDimension('C')->setWidth(50);
         $sheet->getColumnDimension('D')->setWidth(18);
         $sheet->getColumnDimension('E')->setWidth(18);
         $sheet->getColumnDimension('F')->setWidth(18);

         $sheet->setCellValueByColumnAndRow(0, 1, 'Laporan Buku Besar');
         $sheet->mergeCells('A1:I1');
         $sheet->setCellValueByColumnAndRow(0, 3, 'Tanggal Transaksi : '. IndonesianDate($requestData['start_date'], 'yyyy-mm-dd') .' s/d '. IndonesianDate($requestData['end_date'], 'yyyy-mm-dd'));
         $sheet->mergeCells('A3:I3');
         $sheet->mergeCells('A4:I4');
         $sheet->mergeCells('A5:I5');
         $sheet->setCellValueByColumnAndRow(0, 4, 'Nama Rekening : '. $info_coa['rekening']);
         $sheet->setCellValueByColumnAndRow(0, 5, 'Nomor Rekening : '. $info_coa['no_rekening']);
         $sheet->getStyle('A1:I5')->applyFromArray(array(
            'font' => array(
               'bold' => true
            ), 'alignment' => array(
               'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
               'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
               'wrap' => true,
               'shrinkToFit' => true
            )
         ));

         $no=7;
         $sheet->setCellValueByColumnAndRow(0, $no, 'Tanggal');
         $sheet->setCellValueByColumnAndRow(1, $no, 'Nomor Bukti');
         $sheet->setCellValueByColumnAndRow(2, $no, 'Keterangan');
         $sheet->setCellValueByColumnAndRow(3, $no, 'Debet');
         $sheet->setCellValueByColumnAndRow(4, $no, 'Kredit');
         $sheet->setCellValueByColumnAndRow(5, $no, 'Saldo');
         $sheet->getStyle('A'.$no.':F'.$no)->applyFromArray(array(
            'font' => array(
               'bold' => true
            ), 'alignment' => array(
               'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
               'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
               'wrap' => true,
               'shrinkToFit' => true
            )
         ));

         $num = 8;
         /*
         for ($i=0;$i<sizeof($data);$i++) {
            $sheet->setCellValueByColumnAndRow(0, $num, IndonesianDate($data[$i]['tanggal_jurnal_entri'],'yyyy-mm-dd'), $fColNomor);
            $sheet->getCellByColumnAndRow(1, $num)->setValueExplicit($data[$i]['akun_kode'], PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->getCellByColumnAndRow(2, $num)->setValueExplicit($data[$i]['akun_nama'], PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueByColumnAndRow(3, $num,$data[$i]['keterangan']);
            $sheet->getCellByColumnAndRow(4, $num)->setValueExplicit($data[$i]['nomor_referensi'], PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->getCellByColumnAndRow(5, $num)->setValueExplicit($data[$i]['saldo_awal'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->getCellByColumnAndRow(6, $num)->setValueExplicit($data[$i]['debet'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->getCellByColumnAndRow(7, $num)->setValueExplicit($data[$i]['kredit'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->getCellByColumnAndRow(8, $num)->setValueExplicit($data[$i]['saldo_akhir'], PHPExcel_Cell_DataType::TYPE_NUMERIC);

            $sheet->getStyle('F'.$num.':I'.$num)->getNumberFormat()->setFormatCode('_("Rp"* #,##0_);_("Rp"* \(#,##0\);_("Rp"* "-"_);_(@_)');
            $num++;
         }
        */
    
         $kodeAkun = '';
         $items = array();
         $max = sizeof($dataList);
         $nk = 0;         
         $saldo = 0;
         $saldoAkhir = 0;
         for($k = 0; $k < $max;){

            ini_set('memory_limit', '1024M');
            ini_set('max_execution_time', '0');

            if($kodeAkun == $dataList[$k]['akun_kode']) {  
                //$sheet->setCellValueByColumnAndRow(1, $num,'cek');
                 if ((int) $dataList[$k]['id'] != 0) {                 
                    
                $num++;
                $items[$nk]['akun_kode'] ='';
                $items[$nk]['akun_nama'] ='';
                $items[$nk]['tanggal_jurnal_entri'] = $dataList[$k]['tanggal_jurnal_entri'];
                $items[$nk]['sub_account'] = $dataList[$k]['sub_account'];
                $items[$nk]['keterangan'] = $dataList[$k]['keterangan'];
                $items[$nk]['nomor_referensi'] = $dataList[$k]['nomor_referensi'];
                $items[$nk]['debet'] = $dataList[$k]['debet'];    
                $items[$nk]['kredit'] = $dataList[$k]['kredit'];
                
                $sheet->setCellValueByColumnAndRow(0, $num,IndonesianDate($items[$nk]['tanggal_jurnal_entri'], 'yyyy-mm-dd'));
                $sheet->setCellValueByColumnAndRow(1, $num,$items[$nk]['nomor_referensi']);
                $sheet->setCellValueByColumnAndRow(2, $num,$items[$nk]['keterangan']);
                $getHeightRow = ceil(strlen($items[$nk]['keterangan'])/49.1) * 14;    
                $sheet->getRowDimension($num)->setRowHeight($getHeightRow);
                $sheet->setCellValueByColumnAndRow(3, $num,$items[$nk]['debet']);
                $sheet->setCellValueByColumnAndRow(4, $num,$items[$nk]['kredit']);
                $sheet->setCellValueByColumnAndRow(5, $num,'=(F'.($num - 1 ).'+D'.$num.'-E'.$num.')');  
                $sheet->getStyle('D'.$num.':F'.$num)->getNumberFormat()->setFormatCode('_("Rp "* #,##0_);_("Rp "* \(#,##0\);_("Rp "* "-"_);_(@_)');
                
                
                }
                 
                if(isset($dataList[$k + 1]['akun_kode'])) {
                    $cek = $dataList[$k + 1]['akun_kode'];
                } else {
                    $cek = null;
                }
                
                if($kodeAkun != $cek){                                    
                    $num++;
                    $items[$nk]['akun_kode'] = '';
                    $items[$nk]['akun_nama'] = '';  
                    $items[$nk]['tanggal_jurnal_entri'] = '';
                    $items[$nk]['sub_account'] = '';
                    $items[$nk]['keterangan'] = 'JUMLAH';
                    $items[$nk]['nomor_referensi'] = '';
                    $items[$nk]['saldo_awal'] = '';
                    $items[$nk]['debet'] = '';
                    $items[$nk]['kredit'] = '';                    
                    $items[$nk]['saldo_akhir'] = '';    
                    $sheet->setCellValueByColumnAndRow(0, $num,$items[$nk]['tanggal_jurnal_entri']);
                    $sheet->setCellValueByColumnAndRow(1, $num,$items[$nk]['nomor_referensi']);
                    $sheet->setCellValueByColumnAndRow(2, $num,$items[$nk]['keterangan']);
                    $sheet->setCellValueByColumnAndRow(3, $num,$items[$nk]['debet']);
                    $sheet->setCellValueByColumnAndRow(4, $num,$items[$nk]['kredit']);
                    $sheet->setCellValueByColumnAndRow(5, $num,'=(F'.($num -1 ).')');    
                    $sheet->getStyle('D'.$num.':F'.$num)->getNumberFormat()->setFormatCode('_("Rp "* #,##0_);_("Rp "* \(#,##0\);_("Rp "* "-"_);_(@_)');
                    $num++;
                }
                $k++;
            } elseif($kodeAkun != $dataList[$k]['akun_kode']) {
                $kodeAkun =  $dataList[$k]['akun_kode'];
                $saldo = 0;
                $saldoAkhir =0;
                $items[$nk]['akun_kode'] = '';
                $items[$nk]['akun_nama'] = '';
                $items[$nk]['tanggal_jurnal_entri'] = '';
                $items[$nk]['sub_account'] = '';
                $items[$nk]['keterangan'] = $kodeAkun.' - '.$dataList[$k]['akun_nama'];
                $items[$nk]['nomor_referensi'] = '';
                $items[$nk]['saldo_awal'] = $dataList[$k]['saldo_awal'];
                $items[$nk]['debet'] = '';
                $items[$nk]['kredit'] = '';
                $items[$nk]['saldo_akhir'] = '';   
                
                //$this->mrTemplate->AddVar('data_item_grid', 'HEADER','YES');
                //$this->mrTemplate->AddVars('data_item_grid', $items[$nk]);
                
                $sheet->setCellValueByColumnAndRow(0, $num, $items[$nk]['keterangan']);                
                $sheet->mergeCells('A'.$num.':F'.$num);
                $sheet->getStyle('A'.$num.':F'.$num)->applyFromArray(array(
                    'font' => array(
                        'bold' => true
                     ), 
                     'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                        'wrap' => true,
                        'shrinkToFit' => true
                    )
                ));
                
                $num++;
                $sheet->setCellValueByColumnAndRow(0, $num,'');
                $sheet->setCellValueByColumnAndRow(1, $num,'');
                $sheet->setCellValueByColumnAndRow(2, $num,'SALDO AWAL');
                $sheet->setCellValueByColumnAndRow(3, $num,'');
                $sheet->setCellValueByColumnAndRow(4, $num,'');
                $sheet->setCellValueByColumnAndRow(5, $num,$items[$nk]['saldo_awal']); 
                $sheet->getStyle('D'.$num.':F'.$num)->getNumberFormat()->setFormatCode('_("Rp "* #,##0_);_("Rp "* \(#,##0\);_("Rp "* "-"_);_(@_)');
            }
            
            //$this->mrTemplate->AddVars('data_item', $items[$nk]);
            //$this->mrTemplate->parseTemplate('data_item', 'a');
            $nk++; 
         }
         
         $sheet->getStyle('A7:F'.($num - 1))->applyFromArray(array(
            'borders' => array(
               'allborders' => array(
                  'style' => PHPExcel_Style_Border::BORDER_THIN
               )
            ),
            'alignment' => array(
               'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
               'wrap' => true,
               'shrinkToFit' => true
            )
         ));
         
        $sheet->getStyle('B7:B'.($num - 1))->applyFromArray(array(
            'alignment' => array(
               'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
            )
         ));
      }

      # Save Excel document to local hard disk
      $this->Save();
   }
}
?>