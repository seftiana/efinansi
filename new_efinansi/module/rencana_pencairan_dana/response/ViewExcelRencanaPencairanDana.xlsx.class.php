<?php
/**
* ================= doc ====================
* FILENAME     : ViewExcelRencanaPencairanDana.xlsx.class.php
* @package     : ViewExcelRencanaPencairanDana
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2014-09-25
* @Modified    : 2014-09-25
* @Analysts    : Dyah Fajar N
* @contact     : eko.susilo@gamatechno.com
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/rencana_pencairan_dana/business/AppRencanaPencairanDana.class.php';
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/user_unit_kerja/business/UserUnitKerja.class.php';
class ViewExcelRencanaPencairanDana extends XlsxResponse
{
   # Internal Variables
   public $Excel;

   function ProcessRequest()
   {
      # set writer for XlsxResponse
      # default Excel5 for .xls extension
      # option Excel2007 for .xlsx extension
      # $this->SetWriter('Excel2007');
      $this->SetFileName('lap_rencana_pencairan_dana_'.date('Ymd', time()).'.xls');

      # Write your code here
      # Get active sheet
      # Document Setting
      $sheet   = $this->Excel->getActiveSheet(0);
      $sheet->setShowGridlines(true); # false: Hide the gridlines; true: show the gridlines
      # set worksheet name
      $sheet->setTitle('laporan_pencairan_dana');
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

      $headerLabel            = GTFWConfiguration::GetValue('language', 'lap_rencana_pencairan_dana');
      $periodeTahunLabel      = GTFWConfiguration::GetValue('language', 'periode_tahun');
      $unitLabel              = GTFWConfiguration::GetValue('language', 'unit');
      $subUnitLabel           = GTFWConfiguration::GetValue('language', 'sub_unit');

      $mObj       = new AppRencanaPencairanDana();
      $mUnitObj   = new UserUnitKerja();
      $userId     = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
      $dataUnit   = $mUnitObj->GetUnitKerjaRefUser($userId);
      $arrPeriodeTahun  = $mObj->GetPeriodeTahun();
      $periodeTahun     = $mObj->GetPeriodeTahun(array(
         'active' => true
      ));
      $requestData      = array();

      $requestData['ta_id']      = Dispatcher::Instance()->Decrypt($mObj->_GET['ta_id']);
      $requestData['unit_id']    = Dispatcher::Instance()->Decrypt($mObj->_GET['unit_id']);
      $requestData['unit_nama']  = Dispatcher::Instance()->Decrypt($mObj->_GET['unit_nama']);

      foreach ($arrPeriodeTahun as $ta) {
         if((int)$ta['id'] === (int)$requestData['ta_id']){
            $requestData['ta_nama'] = $ta['name'];
         }
      }

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

      $dataList      = $mObj->ChangeKeyName($mObj->GetData(0, 10000, (array)$requestData));
      $totalData     = $mObj->Count();

      // MERGE CELLS
      $sheet->mergeCells('A1:R1');
      $sheet->mergeCells('A2:R2');
      $sheet->mergeCells('A4:B4');
      $sheet->mergeCells('C4:R4');
      $sheet->mergeCells('A5:A6');
      $sheet->mergeCells('B5:C5');
      $sheet->mergeCells('D5:D6');
      $sheet->mergeCells('E5:E6');
      $sheet->mergeCells('F5:F6');
      $sheet->mergeCells('G5:R5');

      $sheet->getColumnDimension('A')->setWidth(5);
      $sheet->getColumnDimension('B')->setWidth(15);
      $sheet->getColumnDimension('C')->setWidth(50);
      $sheet->getColumnDimension('D')->setWidth(35);
      $sheet->getColumnDimension('E')->setWidth(10);
      $sheet->getColumnDimension('F')->setWidth(20);
      $sheet->getColumnDimension('G')->setWidth(20);
      $sheet->getColumnDimension('H')->setWidth(20);
      $sheet->getColumnDimension('I')->setWidth(20);
      $sheet->getColumnDimension('J')->setWidth(20);
      $sheet->getColumnDimension('K')->setWidth(20);
      $sheet->getColumnDimension('L')->setWidth(20);
      $sheet->getColumnDimension('M')->setWidth(20);
      $sheet->getColumnDimension('N')->setWidth(20);
      $sheet->getColumnDimension('O')->setWidth(20);
      $sheet->getColumnDimension('P')->setWidth(20);
      $sheet->getColumnDimension('Q')->setWidth(20);
      $sheet->getColumnDimension('R')->setWidth(20);

      $sheet->getRowDimension(1)->setRowHeight(20);
      $sheet->getRowDimension(5)->setRowHeight(16);
      $sheet->getRowDimension(6)->setRowHeight(16);
      $sheet->setCellValue('A1', $headerLabel);
      $sheet->getStyle('A1:K1')->applyFromArray($headerStyle);

      $sheet->setCellValue('A2', $periodeTahunLabel.' : '.$requestData['ta_nama']);
      $sheet->getStyle('A2:K2')->applyFromArray($headerStyle);
      $sheet->getStyle('A2:K2')->getFont()->setSize(11);

      $sheet->setCellValue('A4', $unitLabel.'/'.$subUnitLabel);
      $sheet->setCellValue('C4', $requestData['unit_nama']);
      $sheet->getStyle('A4:J4')->getFont()->setBold(true);

      $sheet->setCellValue('A5', GTFWConfiguration::GetValue('language', 'no'));
      $sheet->setCellValue('B5', GTFWConfiguration::GetValue('language', 'label_rincian'));
      $sheet->setCellValue('B6', GTFWConfiguration::GetValue('language', 'kode'));
      $sheet->setCellValue('C6', GTFWConfiguration::GetValue('language', 'nama'));
      $sheet->setCellValue('D5', $unitLabel.'/'.$subUnitLabel);
      $sheet->setCellValue('E5', GTFWConfiguration::GetValue('language', 'volume'));
      $sheet->setCellValue('F5', GTFWConfiguration::GetValue('language', 'alokasi_pagu_rp'));
      $sheet->setCellValue('G5', GTFWConfiguration::GetValue('language', 'rencana_penarikan_bulan'));
      $sheet->setCellValue('G6', GTFWConfiguration::GetValue('language', 'januari'));
      $sheet->setCellValue('H6', GTFWConfiguration::GetValue('language', 'februari'));
      $sheet->setCellValue('I6', GTFWConfiguration::GetValue('language', 'maret'));
      $sheet->setCellValue('J6', GTFWConfiguration::GetValue('language', 'april'));
      $sheet->setCellValue('K6', GTFWConfiguration::GetValue('language', 'mei'));
      $sheet->setCellValue('L6', GTFWConfiguration::GetValue('language', 'juni'));
      $sheet->setCellValue('M6', GTFWConfiguration::GetValue('language', 'juli'));
      $sheet->setCellValue('N6', GTFWConfiguration::GetValue('language', 'agustus'));
      $sheet->setCellValue('O6', GTFWConfiguration::GetValue('language', 'september'));
      $sheet->setCellValue('P6', GTFWConfiguration::GetValue('language', 'oktober'));
      $sheet->setCellValue('Q6', GTFWConfiguration::GetValue('language', 'november'));
      $sheet->setCellValue('R6', GTFWConfiguration::GetValue('language', 'desember'));
      $sheet->getStyle('A5:R6')->applyFromArray($styledTableHeaderArray);

      if (empty($dataList))  {
         $sheet->setCellValue('A7', '-- Tidak Ada Data '.$moduleName.' --');
         $sheet->mergeCells('A7:R7');
         $sheet->getStyle('A7:R7')->applyFromArray($styledTableHeaderArray);
         $sheet->getStyle('A7:R7')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
      } else {
         // inisialisasi data
         $program       = '';
         $kegiatan      = '';
         $subkegiatan   = '';
         $kegdet        = '';
         $makId         = '';
         $index         = 0;
         $dataGrid      = array();
         $dataRincian   = array();
         $start         = 1;
         $row           = 7;
         for ($i=0; $i < count($dataList);) {
            if((int)$dataList[$i]['program_id'] === (int)$program
               && (int)$dataList[$i]['kegiatan_id'] === (int)$kegiatan
               && (int)$subkegiatan === (int)$dataList[$i]['sub_kegiatan_id']
               && (int)$kegdet === (int)$dataList[$i]['kegdet_id']
               && (int)$makId === (int)$dataList[$i]['mak_id']){
               // kode sistem
               $kodeSistemProgram         = $program;
               $kodeSistemKegiatan        = $program.'.'.$kegiatan;
               $kodeSistemSubKegiatan     = $program.'.'.$kegiatan.'.'.$subkegiatan;
               $kodeSistemKegdet          = $program.'.'.$kegiatan.'.'.$subkegiatan.'.'.$kegdet;
               $kodeSistemMak             = $program.'.'.$kegiatan.'.'.$subkegiatan.'.'.$kegdet.'.'.$makId;

               $dataRincian[$kodeSistemProgram]['nominal_approve']       += $dataList[$i]['nominal_approve'];
               $dataRincian[$kodeSistemKegiatan]['nominal_approve']      += $dataList[$i]['nominal_approve'];
               $dataRincian[$kodeSistemSubKegiatan]['nominal_approve']   += $dataList[$i]['nominal_approve'];
               $dataRincian[$kodeSistemKegdet]['nominal_approve']        += $dataList[$i]['nominal_approve'];
               $dataRincian[$kodeSistemMak]['nominal_approve']           += $dataList[$i]['nominal_approve'];

               $dataRincian[$kodeSistemProgram]['nominal_januari']      += $dataList[$i]['nominal_januari'];
               $dataRincian[$kodeSistemProgram]['nominal_februari']     += $dataList[$i]['nominal_februari'];
               $dataRincian[$kodeSistemProgram]['nominal_maret']        += $dataList[$i]['nominal_maret'];
               $dataRincian[$kodeSistemProgram]['nominal_april']        += $dataList[$i]['nominal_april'];
               $dataRincian[$kodeSistemProgram]['nominal_mei']          += $dataList[$i]['nominal_mei'];
               $dataRincian[$kodeSistemProgram]['nominal_juni']         += $dataList[$i]['nominal_juni'];
               $dataRincian[$kodeSistemProgram]['nominal_juli']         += $dataList[$i]['nominal_juli'];
               $dataRincian[$kodeSistemProgram]['nominal_agustus']      += $dataList[$i]['nominal_agustus'];
               $dataRincian[$kodeSistemProgram]['nominal_september']    += $dataList[$i]['nominal_september'];
               $dataRincian[$kodeSistemProgram]['nominal_oktober']      += $dataList[$i]['nominal_oktober'];
               $dataRincian[$kodeSistemProgram]['nominal_november']     += $dataList[$i]['nominal_november'];
               $dataRincian[$kodeSistemProgram]['nominal_desember']     += $dataList[$i]['nominal_desember'];

               // nominal bulan kegiatan
               $dataRincian[$kodeSistemKegiatan]['nominal_januari']     += $dataList[$i]['nominal_januari'];
               $dataRincian[$kodeSistemKegiatan]['nominal_februari']    += $dataList[$i]['nominal_februari'];
               $dataRincian[$kodeSistemKegiatan]['nominal_maret']       += $dataList[$i]['nominal_maret'];
               $dataRincian[$kodeSistemKegiatan]['nominal_april']       += $dataList[$i]['nominal_april'];
               $dataRincian[$kodeSistemKegiatan]['nominal_mei']         += $dataList[$i]['nominal_mei'];
               $dataRincian[$kodeSistemKegiatan]['nominal_juni']        += $dataList[$i]['nominal_juni'];
               $dataRincian[$kodeSistemKegiatan]['nominal_juli']        += $dataList[$i]['nominal_juli'];
               $dataRincian[$kodeSistemKegiatan]['nominal_agustus']     += $dataList[$i]['nominal_agustus'];
               $dataRincian[$kodeSistemKegiatan]['nominal_september']   += $dataList[$i]['nominal_september'];
               $dataRincian[$kodeSistemKegiatan]['nominal_oktober']     += $dataList[$i]['nominal_oktober'];
               $dataRincian[$kodeSistemKegiatan]['nominal_november']    += $dataList[$i]['nominal_november'];
               $dataRincian[$kodeSistemKegiatan]['nominal_desember']    += $dataList[$i]['nominal_desember'];

               // nominal bulan sub kegiatan
               $dataRincian[$kodeSistemSubKegiatan]['nominal_januari']   += $dataList[$i]['nominal_januari'];
               $dataRincian[$kodeSistemSubKegiatan]['nominal_februari']  += $dataList[$i]['nominal_februari'];
               $dataRincian[$kodeSistemSubKegiatan]['nominal_maret']     += $dataList[$i]['nominal_maret'];
               $dataRincian[$kodeSistemSubKegiatan]['nominal_april']     += $dataList[$i]['nominal_april'];
               $dataRincian[$kodeSistemSubKegiatan]['nominal_mei']       += $dataList[$i]['nominal_mei'];
               $dataRincian[$kodeSistemSubKegiatan]['nominal_juni']      += $dataList[$i]['nominal_juni'];
               $dataRincian[$kodeSistemSubKegiatan]['nominal_juli']      += $dataList[$i]['nominal_juli'];
               $dataRincian[$kodeSistemSubKegiatan]['nominal_agustus']   += $dataList[$i]['nominal_agustus'];
               $dataRincian[$kodeSistemSubKegiatan]['nominal_september'] += $dataList[$i]['nominal_september'];
               $dataRincian[$kodeSistemSubKegiatan]['nominal_oktober']   += $dataList[$i]['nominal_oktober'];
               $dataRincian[$kodeSistemSubKegiatan]['nominal_november']  += $dataList[$i]['nominal_november'];
               $dataRincian[$kodeSistemSubKegiatan]['nominal_desember']  += $dataList[$i]['nominal_desember'];

               // nominal bulan kegiatan detail
               $dataRincian[$kodeSistemKegdet]['nominal_januari']    += $dataList[$i]['nominal_januari'];
               $dataRincian[$kodeSistemKegdet]['nominal_februari']   += $dataList[$i]['nominal_februari'];
               $dataRincian[$kodeSistemKegdet]['nominal_maret']      += $dataList[$i]['nominal_maret'];
               $dataRincian[$kodeSistemKegdet]['nominal_april']      += $dataList[$i]['nominal_april'];
               $dataRincian[$kodeSistemKegdet]['nominal_mei']        += $dataList[$i]['nominal_mei'];
               $dataRincian[$kodeSistemKegdet]['nominal_juni']       += $dataList[$i]['nominal_juni'];
               $dataRincian[$kodeSistemKegdet]['nominal_juli']       += $dataList[$i]['nominal_juli'];
               $dataRincian[$kodeSistemKegdet]['nominal_agustus']    += $dataList[$i]['nominal_agustus'];
               $dataRincian[$kodeSistemKegdet]['nominal_september']  += $dataList[$i]['nominal_september'];
               $dataRincian[$kodeSistemKegdet]['nominal_oktober']    += $dataList[$i]['nominal_oktober'];
               $dataRincian[$kodeSistemKegdet]['nominal_november']   += $dataList[$i]['nominal_november'];
               $dataRincian[$kodeSistemKegdet]['nominal_desember']   += $dataList[$i]['nominal_desember'];

               // nominal bulan MAK
               $dataRincian[$kodeSistemMak]['nominal_januari']    += $dataList[$i]['nominal_januari'];
               $dataRincian[$kodeSistemMak]['nominal_februari']   += $dataList[$i]['nominal_februari'];
               $dataRincian[$kodeSistemMak]['nominal_maret']      += $dataList[$i]['nominal_maret'];
               $dataRincian[$kodeSistemMak]['nominal_april']      += $dataList[$i]['nominal_april'];
               $dataRincian[$kodeSistemMak]['nominal_mei']        += $dataList[$i]['nominal_mei'];
               $dataRincian[$kodeSistemMak]['nominal_juni']       += $dataList[$i]['nominal_juni'];
               $dataRincian[$kodeSistemMak]['nominal_juli']       += $dataList[$i]['nominal_juli'];
               $dataRincian[$kodeSistemMak]['nominal_agustus']    += $dataList[$i]['nominal_agustus'];
               $dataRincian[$kodeSistemMak]['nominal_september']  += $dataList[$i]['nominal_september'];
               $dataRincian[$kodeSistemMak]['nominal_oktober']    += $dataList[$i]['nominal_oktober'];
               $dataRincian[$kodeSistemMak]['nominal_november']   += $dataList[$i]['nominal_november'];
               $dataRincian[$kodeSistemMak]['nominal_desember']   += $dataList[$i]['nominal_desember'];

               $dataRincian[$kodeSistemProgram]['nominal_total']       += $dataList[$i]['total_approve'];
               $dataRincian[$kodeSistemKegiatan]['nominal_total']      += $dataList[$i]['total_approve'];
               $dataRincian[$kodeSistemSubKegiatan]['nominal_total']   += $dataList[$i]['total_approve'];
               $dataRincian[$kodeSistemKegdet]['nominal_total']        += $dataList[$i]['total_approve'];
               $dataRincian[$kodeSistemMak]['nominal_total']           += $dataList[$i]['total_approve'];

               $dataGrid[$index]['kode']        = $dataList[$i]['komp_kode'];
               $dataGrid[$index]['nama']        = $dataList[$i]['komp_nama'];
               $dataGrid[$index]['unit_nama']   = $dataList[$i]['unit_nama'];
               $dataGrid[$index]['nomor']       = $start;
               $dataGrid[$index]['tipe']        = 'komponen';
               $dataGrid[$index]['volume']      = $dataList[$i]['volume'];
               $dataGrid[$index]['nominal_approve']      = $dataList[$i]['nominal_approve'];
               $dataGrid[$index]['nominal_total']        = $dataList[$i]['total_approve'];
               $dataGrid[$index]['nominal_januari']      = $dataList[$i]['nominal_januari'];
               $dataGrid[$index]['nominal_februari']     = $dataList[$i]['nominal_februari'];
               $dataGrid[$index]['nominal_maret']        = $dataList[$i]['nominal_maret'];
               $dataGrid[$index]['nominal_april']        = $dataList[$i]['nominal_april'];
               $dataGrid[$index]['nominal_mei']          = $dataList[$i]['nominal_mei'];
               $dataGrid[$index]['nominal_juni']         = $dataList[$i]['nominal_juni'];
               $dataGrid[$index]['nominal_juli']         = $dataList[$i]['nominal_juli'];
               $dataGrid[$index]['nominal_agustus']      = $dataList[$i]['nominal_agustus'];
               $dataGrid[$index]['nominal_september']    = $dataList[$i]['nominal_september'];
               $dataGrid[$index]['nominal_oktober']      = $dataList[$i]['nominal_oktober'];
               $dataGrid[$index]['nominal_november']     = $dataList[$i]['nominal_november'];
               $dataGrid[$index]['nominal_desember']     = $dataList[$i]['nominal_desember'];
               $i++;
               $start++;
            }elseif((int)$dataList[$i]['program_id'] === (int)$program
               && (int)$dataList[$i]['kegiatan_id'] === (int)$kegiatan
               && (int)$subkegiatan === (int)$dataList[$i]['sub_kegiatan_id']
               && (int)$kegdet === (int)$dataList[$i]['kegdet_id']
               && (int)$makId !== (int)$dataList[$i]['mak_id']){
               $makId            = (int)$dataList[$i]['mak_id'];
               $kodeSistem       = $program.'.'.$kegiatan.'.'.$subkegiatan.'.'.$kegdet.'.'.$makId;
               $dataRincian[$kodeSistem]['nominal_approve']    = 0;
               $dataRincian[$kodeSistem]['nominal_total']      = 0;
               $dataRincian[$kodeSistem]['nominal_januari']    = 0;
               $dataRincian[$kodeSistem]['nominal_februari']   = 0;
               $dataRincian[$kodeSistem]['nominal_maret']      = 0;
               $dataRincian[$kodeSistem]['nominal_april']      = 0;
               $dataRincian[$kodeSistem]['nominal_mei']        = 0;
               $dataRincian[$kodeSistem]['nominal_juni']       = 0;
               $dataRincian[$kodeSistem]['nominal_juli']       = 0;
               $dataRincian[$kodeSistem]['nominal_agustus']    = 0;
               $dataRincian[$kodeSistem]['nominal_september']  = 0;
               $dataRincian[$kodeSistem]['nominal_oktober']    = 0;
               $dataRincian[$kodeSistem]['nominal_november']   = 0;
               $dataRincian[$kodeSistem]['nominal_desember']   = 0;

               $dataGrid[$index]['kode_sistem'] = $kodeSistem;
               $dataGrid[$index]['nomor']       = '';
               $dataGrid[$index]['kode']        = $dataList[$i]['mak_kode'];
               $dataGrid[$index]['nama']        = $dataList[$i]['mak_nama'];
               $dataGrid[$index]['class_name']  = 'rkat';
               $dataGrid[$index]['row_style']   = 'font-weight: bold;';
               $dataGrid[$index]['tipe']        = 'rkat';
            }elseif((int)$dataList[$i]['program_id'] === (int)$program
               && (int)$dataList[$i]['kegiatan_id'] === (int)$kegiatan
               && (int)$subkegiatan === (int)$dataList[$i]['sub_kegiatan_id']
               && (int)$kegdet !== (int)$dataList[$i]['kegdet_id']){
               $kegdet           = (int)$dataList[$i]['kegdet_id'];
               $kodeSistem       = $program.'.'.$kegiatan.'.'.$subkegiatan.'.'.$kegdet;
               $dataRincian[$kodeSistem]['nominal_approve']  = 0;
               $dataRincian[$kodeSistem]['nominal_total']   = 0;
               $dataRincian[$kodeSistem]['nominal_januari']    = 0;
               $dataRincian[$kodeSistem]['nominal_februari']   = 0;
               $dataRincian[$kodeSistem]['nominal_maret']      = 0;
               $dataRincian[$kodeSistem]['nominal_april']      = 0;
               $dataRincian[$kodeSistem]['nominal_mei']        = 0;
               $dataRincian[$kodeSistem]['nominal_juni']       = 0;
               $dataRincian[$kodeSistem]['nominal_juli']       = 0;
               $dataRincian[$kodeSistem]['nominal_agustus']    = 0;
               $dataRincian[$kodeSistem]['nominal_september']  = 0;
               $dataRincian[$kodeSistem]['nominal_oktober']    = 0;
               $dataRincian[$kodeSistem]['nominal_november']   = 0;
               $dataRincian[$kodeSistem]['nominal_desember']   = 0;

               $dataGrid[$index]['kode_sistem'] = $kodeSistem;
               $dataGrid[$index]['nomor']       = '';
               $dataGrid[$index]['kode']        = $dataList[$i]['sub_kegiatan_kode'].'.'.$kegdet;
               $dataGrid[$index]['nama']        = $dataList[$i]['deskripsi'];
               $dataGrid[$index]['class_name']  = 'table-common-even2';
               $dataGrid[$index]['row_style']   = 'font-style: italic;';
               $dataGrid[$index]['tipe']        = 'referensi';
            }elseif((int)$dataList[$i]['program_id'] === (int)$program
               && (int)$dataList[$i]['kegiatan_id'] === (int)$kegiatan
               && (int)$subkegiatan !== (int)$dataList[$i]['sub_kegiatan_id']){
               $subkegiatan      = (int)$dataList[$i]['sub_kegiatan_id'];
               $kodeSistem       = $program.'.'.$kegiatan.'.'.$subkegiatan;
               $dataRincian[$kodeSistem]['nominal_approve']  = 0;
               $dataRincian[$kodeSistem]['nominal_total']   = 0;
               $dataRincian[$kodeSistem]['nominal_januari']    = 0;
               $dataRincian[$kodeSistem]['nominal_februari']   = 0;
               $dataRincian[$kodeSistem]['nominal_maret']      = 0;
               $dataRincian[$kodeSistem]['nominal_april']      = 0;
               $dataRincian[$kodeSistem]['nominal_mei']        = 0;
               $dataRincian[$kodeSistem]['nominal_juni']       = 0;
               $dataRincian[$kodeSistem]['nominal_juli']       = 0;
               $dataRincian[$kodeSistem]['nominal_agustus']    = 0;
               $dataRincian[$kodeSistem]['nominal_september']  = 0;
               $dataRincian[$kodeSistem]['nominal_oktober']    = 0;
               $dataRincian[$kodeSistem]['nominal_november']   = 0;
               $dataRincian[$kodeSistem]['nominal_desember']   = 0;

               $dataGrid[$index]['kode_sistem'] = $kodeSistem;
               $dataGrid[$index]['nomor']       = '';
               $dataGrid[$index]['kode']        = $dataList[$i]['sub_kegiatan_kode'];
               $dataGrid[$index]['nama']        = $dataList[$i]['sub_kegiatan_nama'];
               $dataGrid[$index]['class_name']  = 'table-common-even2';
               $dataGrid[$index]['row_style']   = 'font-weight: bold;';
               $dataGrid[$index]['tipe']        = 'sub_kegiatan';
               $dataGrid[$index]['rkakl']       = 'sub_kegiatan';
               $dataGrid[$index]['rkakl_nama']  = $dataList[$i]['rkakl_sub_kegiatan_nama'];
            }elseif((int)$dataList[$i]['program_id'] === (int)$program
               && (int)$dataList[$i]['kegiatan_id'] !== (int)$kegiatan){
               $kegiatan         = (int)$dataList[$i]['kegiatan_id'];
               $kodeSistem       = $program.'.'.$kegiatan;
               $dataRincian[$kodeSistem]['nominal_approve']  = 0;
               $dataRincian[$kodeSistem]['nominal_total']   = 0;
               $dataRincian[$kodeSistem]['nominal_januari']    = 0;
               $dataRincian[$kodeSistem]['nominal_februari']   = 0;
               $dataRincian[$kodeSistem]['nominal_maret']      = 0;
               $dataRincian[$kodeSistem]['nominal_april']      = 0;
               $dataRincian[$kodeSistem]['nominal_mei']        = 0;
               $dataRincian[$kodeSistem]['nominal_juni']       = 0;
               $dataRincian[$kodeSistem]['nominal_juli']       = 0;
               $dataRincian[$kodeSistem]['nominal_agustus']    = 0;
               $dataRincian[$kodeSistem]['nominal_september']  = 0;
               $dataRincian[$kodeSistem]['nominal_oktober']    = 0;
               $dataRincian[$kodeSistem]['nominal_november']   = 0;
               $dataRincian[$kodeSistem]['nominal_desember']   = 0;

               $dataGrid[$index]['kode_sistem'] = $kodeSistem;
               $dataGrid[$index]['nomor']       = '';
               $dataGrid[$index]['kode']        = $dataList[$i]['kegiatan_kode'];
               $dataGrid[$index]['nama']        = $dataList[$i]['kegiatan_nama'];
               $dataGrid[$index]['class_name']  = 'table-common-even1';
               $dataGrid[$index]['row_style']   = 'font-weight: bold; font-style: italic;';
               $dataGrid[$index]['tipe']        = 'kegiatan';
               $dataGrid[$index]['rkakl']       = 'output';
               $dataGrid[$index]['rkakl_nama']  = $dataList[$i]['rkakl_output_nama'];
               $dataGrid[$index]['ikk_nama']    = $dataList[$i]['ikk_nama'];
               $dataGrid[$index]['iku_nama']    = $dataList[$i]['iku_nama'];
               $dataGrid[$index]['output']      = '-';
            }else{
               $program          = (int)$dataList[$i]['program_id'];
               $kodeSistem       = $program;
               $dataRincian[$kodeSistem]['nominal_approve']  = 0;
               $dataRincian[$kodeSistem]['nominal_total']   = 0;
               $dataRincian[$kodeSistem]['nominal_januari']    = 0;
               $dataRincian[$kodeSistem]['nominal_februari']   = 0;
               $dataRincian[$kodeSistem]['nominal_maret']      = 0;
               $dataRincian[$kodeSistem]['nominal_april']      = 0;
               $dataRincian[$kodeSistem]['nominal_mei']        = 0;
               $dataRincian[$kodeSistem]['nominal_juni']       = 0;
               $dataRincian[$kodeSistem]['nominal_juli']       = 0;
               $dataRincian[$kodeSistem]['nominal_agustus']    = 0;
               $dataRincian[$kodeSistem]['nominal_september']  = 0;
               $dataRincian[$kodeSistem]['nominal_oktober']    = 0;
               $dataRincian[$kodeSistem]['nominal_november']   = 0;
               $dataRincian[$kodeSistem]['nominal_desember']   = 0;

               $dataGrid[$index]['kode_sistem'] = $kodeSistem;
               $dataGrid[$index]['kode']        = $dataList[$i]['program_kode'];
               $dataGrid[$index]['nama']        = $dataList[$i]['program_nama'];
               $dataGrid[$index]['class_name']  = 'table-common-even1';
               $dataGrid[$index]['row_style']   = 'font-weight: bold;';
               $dataGrid[$index]['nomor']       = '';
               $dataGrid[$index]['tipe']        = 'program';
               $dataGrid[$index]['rkakl']       = 'kegiatan';
               $dataGrid[$index]['rkakl_nama']  = $dataList[$i]['rkakl_kegiatan_nama'];
            }
            $index++;
         }

         foreach ($dataGrid as $list) {
            switch (strtoupper($list['tipe'])) {
               case 'PROGRAM':
                  $sheet->getStyle('F'.$row.':R'.$row)->getNumberFormat()->setFormatCode('#,##0.00_);(#,##0.00)');
                  $list['nama']                 = $list['nama']."\n[".$list['rkakl_nama']."]";
                  $list['nominal_approve']      = $dataRincian[$list['kode_sistem']]['nominal_approve'];
                  $list['nominal_total']        = $dataRincian[$list['kode_sistem']]['nominal_total'];
                  $sheet->setCellValueExplicit('F'.$row, $list['nominal_total'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $sheet->setCellValueExplicit('G'.$row, $dataRincian[$list['kode_sistem']]['nominal_januari'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $sheet->setCellValueExplicit('H'.$row, $dataRincian[$list['kode_sistem']]['nominal_februari'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $sheet->setCellValueExplicit('I'.$row, $dataRincian[$list['kode_sistem']]['nominal_maret'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $sheet->setCellValueExplicit('J'.$row, $dataRincian[$list['kode_sistem']]['nominal_april'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $sheet->setCellValueExplicit('K'.$row, $dataRincian[$list['kode_sistem']]['nominal_mei'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $sheet->setCellValueExplicit('L'.$row, $dataRincian[$list['kode_sistem']]['nominal_juni'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $sheet->setCellValueExplicit('M'.$row, $dataRincian[$list['kode_sistem']]['nominal_juli'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $sheet->setCellValueExplicit('N'.$row, $dataRincian[$list['kode_sistem']]['nominal_agustus'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $sheet->setCellValueExplicit('O'.$row, $dataRincian[$list['kode_sistem']]['nominal_september'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $sheet->setCellValueExplicit('P'.$row, $dataRincian[$list['kode_sistem']]['nominal_oktober'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $sheet->setCellValueExplicit('Q'.$row, $dataRincian[$list['kode_sistem']]['nominal_november'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $sheet->setCellValueExplicit('R'.$row, $dataRincian[$list['kode_sistem']]['nominal_desember'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  break;
               case 'KEGIATAN':
                  $list['nama']                 = $list['nama']."\n[".$list['rkakl_nama']."]";
                  $list['nominal_approve']      = $dataRincian[$list['kode_sistem']]['nominal_approve'];
                  $list['nominal_total']        = $dataRincian[$list['kode_sistem']]['nominal_total'];
                  $sheet->getStyle('F'.$row.':R'.$row)->getNumberFormat()->setFormatCode('#,##0.00_);(#,##0.00)');
                  $sheet->setCellValueExplicit('F'.$row, $list['nominal_total'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $sheet->setCellValueExplicit('G'.$row, $dataRincian[$list['kode_sistem']]['nominal_januari'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $sheet->setCellValueExplicit('H'.$row, $dataRincian[$list['kode_sistem']]['nominal_februari'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $sheet->setCellValueExplicit('I'.$row, $dataRincian[$list['kode_sistem']]['nominal_maret'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $sheet->setCellValueExplicit('J'.$row, $dataRincian[$list['kode_sistem']]['nominal_april'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $sheet->setCellValueExplicit('K'.$row, $dataRincian[$list['kode_sistem']]['nominal_mei'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $sheet->setCellValueExplicit('L'.$row, $dataRincian[$list['kode_sistem']]['nominal_juni'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $sheet->setCellValueExplicit('M'.$row, $dataRincian[$list['kode_sistem']]['nominal_juli'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $sheet->setCellValueExplicit('N'.$row, $dataRincian[$list['kode_sistem']]['nominal_agustus'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $sheet->setCellValueExplicit('O'.$row, $dataRincian[$list['kode_sistem']]['nominal_september'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $sheet->setCellValueExplicit('P'.$row, $dataRincian[$list['kode_sistem']]['nominal_oktober'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $sheet->setCellValueExplicit('Q'.$row, $dataRincian[$list['kode_sistem']]['nominal_november'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $sheet->setCellValueExplicit('R'.$row, $dataRincian[$list['kode_sistem']]['nominal_desember'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  break;
               case 'SUB_KEGIATAN':
                  $list['nama']                 = $list['nama']."\n[".$list['rkakl_nama']."]";
                  $list['nominal_approve']      = $dataRincian[$list['kode_sistem']]['nominal_approve'];
                  $list['nominal_total']        = $dataRincian[$list['kode_sistem']]['nominal_total'];
                  $sheet->getStyle('F'.$row.':R'.$row)->getNumberFormat()->setFormatCode('#,##0.00_);(#,##0.00)');
                  $sheet->setCellValueExplicit('F'.$row, $list['nominal_total'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $sheet->setCellValueExplicit('G'.$row, $dataRincian[$list['kode_sistem']]['nominal_januari'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $sheet->setCellValueExplicit('H'.$row, $dataRincian[$list['kode_sistem']]['nominal_februari'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $sheet->setCellValueExplicit('I'.$row, $dataRincian[$list['kode_sistem']]['nominal_maret'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $sheet->setCellValueExplicit('J'.$row, $dataRincian[$list['kode_sistem']]['nominal_april'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $sheet->setCellValueExplicit('K'.$row, $dataRincian[$list['kode_sistem']]['nominal_mei'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $sheet->setCellValueExplicit('L'.$row, $dataRincian[$list['kode_sistem']]['nominal_juni'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $sheet->setCellValueExplicit('M'.$row, $dataRincian[$list['kode_sistem']]['nominal_juli'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $sheet->setCellValueExplicit('N'.$row, $dataRincian[$list['kode_sistem']]['nominal_agustus'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $sheet->setCellValueExplicit('O'.$row, $dataRincian[$list['kode_sistem']]['nominal_september'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $sheet->setCellValueExplicit('P'.$row, $dataRincian[$list['kode_sistem']]['nominal_oktober'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $sheet->setCellValueExplicit('Q'.$row, $dataRincian[$list['kode_sistem']]['nominal_november'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $sheet->setCellValueExplicit('R'.$row, $dataRincian[$list['kode_sistem']]['nominal_desember'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  break;
               case 'REFERENSI':
                  break;
               case 'RKAT':
                  $list['nominal_approve']      = $dataRincian[$list['kode_sistem']]['nominal_approve'];
                  $list['nominal_total']        = $dataRincian[$list['kode_sistem']]['nominal_total'];

                  $sheet->getStyle('F'.$row.':R'.$row)->getNumberFormat()->setFormatCode('#,##0.00_);(#,##0.00)');
                  $sheet->setCellValueExplicit('F'.$row, $list['nominal_total'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $sheet->setCellValueExplicit('G'.$row, $dataRincian[$list['kode_sistem']]['nominal_januari'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $sheet->setCellValueExplicit('H'.$row, $dataRincian[$list['kode_sistem']]['nominal_februari'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $sheet->setCellValueExplicit('I'.$row, $dataRincian[$list['kode_sistem']]['nominal_maret'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $sheet->setCellValueExplicit('J'.$row, $dataRincian[$list['kode_sistem']]['nominal_april'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $sheet->setCellValueExplicit('K'.$row, $dataRincian[$list['kode_sistem']]['nominal_mei'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $sheet->setCellValueExplicit('L'.$row, $dataRincian[$list['kode_sistem']]['nominal_juni'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $sheet->setCellValueExplicit('M'.$row, $dataRincian[$list['kode_sistem']]['nominal_juli'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $sheet->setCellValueExplicit('N'.$row, $dataRincian[$list['kode_sistem']]['nominal_agustus'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $sheet->setCellValueExplicit('O'.$row, $dataRincian[$list['kode_sistem']]['nominal_september'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $sheet->setCellValueExplicit('P'.$row, $dataRincian[$list['kode_sistem']]['nominal_oktober'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $sheet->setCellValueExplicit('Q'.$row, $dataRincian[$list['kode_sistem']]['nominal_november'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $sheet->setCellValueExplicit('R'.$row, $dataRincian[$list['kode_sistem']]['nominal_desember'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  break;
               case 'KOMPONEN':
                  $list['nominal_approve']      = $list['nominal_approve'];
                  $list['nominal_total']        = $list['nominal_total'];
                  $sheet->getStyle('F'.$row.':R'.$row)->getNumberFormat()->setFormatCode('#,##0.00_);(#,##0.00)');
                  $sheet->setCellValueExplicit('F'.$row, $list['nominal_total'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $sheet->setCellValueExplicit('A'.$row, $list['nomor'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $sheet->setCellValueExplicit('G'.$row, $list['nominal_januari'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $sheet->setCellValueExplicit('H'.$row, $list['nominal_februari'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $sheet->setCellValueExplicit('I'.$row, $list['nominal_maret'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $sheet->setCellValueExplicit('J'.$row, $list['nominal_april'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $sheet->setCellValueExplicit('K'.$row, $list['nominal_mei'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $sheet->setCellValueExplicit('L'.$row, $list['nominal_juni'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $sheet->setCellValueExplicit('M'.$row, $list['nominal_juli'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $sheet->setCellValueExplicit('N'.$row, $list['nominal_agustus'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $sheet->setCellValueExplicit('O'.$row, $list['nominal_september'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $sheet->setCellValueExplicit('P'.$row, $list['nominal_oktober'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $sheet->setCellValueExplicit('Q'.$row, $list['nominal_november'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $sheet->setCellValueExplicit('R'.$row, $list['nominal_desember'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  break;
               default:
                  $list['nominal_approve']      = $list['nominal_approve'];
                  $list['nominal_total']        = $list['nominal_total'];
                  $sheet->getStyle('F'.$row.':R'.$row)->getNumberFormat()->setFormatCode('#,##0.00_);(#,##0.00)');
                  $sheet->setCellValueExplicit('A'.$row, $list['nomor'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $sheet->setCellValueExplicit('G'.$row, $list['nominal_januari'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $sheet->setCellValueExplicit('H'.$row, $list['nominal_februari'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $sheet->setCellValueExplicit('I'.$row, $list['nominal_maret'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $sheet->setCellValueExplicit('J'.$row, $list['nominal_april'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $sheet->setCellValueExplicit('K'.$row, $list['nominal_mei'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $sheet->setCellValueExplicit('L'.$row, $list['nominal_juni'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $sheet->setCellValueExplicit('M'.$row, $list['nominal_juli'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $sheet->setCellValueExplicit('N'.$row, $list['nominal_agustus'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $sheet->setCellValueExplicit('O'.$row, $list['nominal_september'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $sheet->setCellValueExplicit('P'.$row, $list['nominal_oktober'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $sheet->setCellValueExplicit('Q'.$row, $list['nominal_november'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $sheet->setCellValueExplicit('R'.$row, $list['nominal_desember'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $sheet->setCellValueExplicit('F'.$row, $list['nominal_total'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  break;
            }

            $rowHeight        = max(array(
               ceil(strlen($list['nama'])/45)*14,
               ceil(strlen($list['unit_nama'])/30)*14
            ));
            $sheet->getRowDimension($row)->setRowHeight($rowHeight+14);
            $sheet->setCellValueExplicit('B'.$row, $list['kode'], PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('C'.$row, $list['nama'], PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('D'.$row, $list['unit_nama'], PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('E'.$row, $list['volume']);
            $row+=1;
         }

         $sheet->getStyle('A7:R'.$row)->getAlignment()->setWrapText(true);
         $sheet->getStyle('A7:R'.$row)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
         $sheet->getStyle('A7:R'.($row-1))->applyFromArray($borderTableStyledArray);
         $sheet->getStyle('A7:A'.($row-1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
         $sheet->getStyle('B7:B'.($row-1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
         $sheet->getStyle('E7:E'.($row-1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
      }

      # Save Excel document to local hard disk
      $this->Save();
   }
}
?>