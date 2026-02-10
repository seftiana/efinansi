<?php
/**
* ================= doc ====================
* FILENAME     : ViewLaporanRekapPengeluaran.html.class.php
* @package     : ViewLaporanRekapPengeluaran
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-03-06
* @Modified    : 2015-03-06
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/laporan_rekap_pengeluaran_kas_bulanan/business/LaporanRekapPengeluaranKas.class.php';
require_once GTFWConfiguration::GetValue('application','docroot').
'module/user_unit_kerja/business/UserUnitKerja.class.php';

class ViewLaporanRekapPengeluaran extends HtmlResponse
{
   function TemplateModule(){
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
      'module/laporan_rekap_pengeluaran_kas_bulanan/template/');
      $this->SetTemplateFile('view_laporan_rekap_pengeluaran.html');
   }

   function ProcessRequest(){
      $mObj             = new LaporanRekapPengeluaranKas();
      $mUnitObj         = new UserUnitKerja();
      $userId           = $mObj->getUserId();
      $unitKerja        = $mUnitObj->GetUnitKerjaRefUser($userId);
      $arrPeriodeTahun  = $mObj->getPeriodeTahun();
      $periodeTahun     = $mObj->getPeriodeTahun(array('active' => true));
      $minYear          = date('Y', strtotime($periodeTahun[0]['min_date']));
      $maxYear          = date('Y', strtotime($periodeTahun[0]['max_date']));
      $getdate          = getdate();
      $currDay          = (int)$getdate['mday'];
      $currMon          = (int)$getdate['mon'];
      $currYear         = (int)$getdate['year'];
      $requestData      = array();
      if(isset($mObj->_POST['btnSearch'])){
         $periodeDay    = (int)$mObj->_POST['periode_day'];
         $periodeMon    = (int)$mObj->_POST['periode_mon'];
         $periodeYear   = (int)$mObj->_POST['periode_year'];
         $requestData['unit_id']    = $mObj->_POST['unit_id'];
         $requestData['unit_nama']  = $mObj->_POST['unit_nama'];
         $requestData['ta_id']      = $mObj->_POST['tahun_anggaran'];
         $requestData['periode']    = date('Y-m-d', mktime(0,0,0, $periodeMon, 1, $periodeYear));
      }elseif (isset($mObj->_GET['search'])) {
         $requestData['unit_id']    = Dispatcher::Instance()->Decrypt($mObj->_GET['unit_id']);
         $requestData['unit_nama']  = Dispatcher::Instance()->Decrypt($mObj->_GET['unit_nama']);
         $requestData['ta_id']      = Dispatcher::Instance()->Decrypt($mObj->_GET['ta_id']);
         $requestData['periode']    = date('Y-m-d', strtotime(Dispatcher::Instance()->Decrypt($mObj->_GET['periode'])));
      }else{
         $requestData['unit_id']    = $unitKerja['id'];
         $requestData['unit_nama']  = $unitKerja['nama'];
         $requestData['ta_id']      = $periodeTahun[0]['id'];
         $requestData['periode']    = date('Y-m-d', mktime(0,0,0, $currMon, 1, $currYear));
      }

      foreach ($arrPeriodeTahun as $ta) {
         if((int)$ta['id'] === (int)$requestData['ta_id']){
            $requestData['ta_nama'] = $ta['name'];
         }
      }

      if(method_exists(Dispatcher::Instance(), 'getQueryString')){
         # @param array
         $queryString   = Dispatcher::instance()->getQueryString($requestData);
      }else{
         $query         = array();
         foreach ($requestData as $key => $value) {
            $query[$key]   = Dispatcher::Instance()->Encrypt($value);
         }
         $queryString   = urldecode(http_build_query($query));
      }

      $offset     = 0;
      $limit      = 20;
      $page       = 0;
      if(isset($_GET['page'])){
         $page    = (string) $_GET['page']->StripHtmlTags()->SqlString()->Raw();
         $offset  = ($page - 1) * $limit;
      }
      #paging url
      $url        = Dispatcher::Instance()->GetUrl(
        Dispatcher::Instance()->mModule,
        Dispatcher::Instance()->mSubModule,
        Dispatcher::Instance()->mAction,
        Dispatcher::Instance()->mType
      ).'&search='.Dispatcher::Instance()->Encrypt(1).'&'.$queryString;

      $destination_id   = "subcontent-element";
      $dataList         = $mObj->getDataLaporan($offset, $limit, (array)$requestData);
      $total_data       = $mObj->Count((array)$requestData);
      #send data to pagging component
      Messenger::Instance()->SendToComponent(
         'paging',
         'Paging',
         'view',
         'html',
         'paging_top',
         array(
            $limit,
            $total_data,
            $url,
            $page,
            $destination_id
         ),
         Messenger::CurrentRequest
      );


      # GTFW Tanggal
      Messenger::Instance()->SendToComponent(
         'tanggal',
         'Tanggal',
         'view',
         'html',
         'periode',
         array(
            $requestData['periode'],
            $minYear,
            $maxYear,
            false,
            false,
            true
         ),
         Messenger::CurrentRequest
      );

      # Combobox
      Messenger::Instance()->SendToComponent(
         'combobox',
         'Combobox',
         'view',
         'html',
         'tahun_anggaran',
         array(
            'tahun_anggaran',
            $arrPeriodeTahun,
            $requestData['ta_id'],
            false,
            'id="cmb_periode_tahun"'
         ),
         Messenger::CurrentRequest
      );

      $return['request_data']    = $requestData;
      $return['unit_kerja']      = $unitKerja;
      $return['query_string']    = $queryString;
      $return['data_list']       = $mObj->ChangeKeyName($dataList);
      $return['start']           = $offset+1;
      return $return;
   }

   function ParseTemplate($data = null){
      $unitKerja     = $data['unit_kerja'];
      $requestData   = $data['request_data'];
      $queryString   = $data['query_string'];
      $start         = $data['start'];
      $dataList      = $data['data_list'];
      $urlSearch     = Dispatcher::Instance()->GetUrl(
         'laporan_rekap_pengeluaran_kas_bulanan',
         'LaporanRekapPengeluaran',
         'view',
         'html'
      );

      $urlPopupUnit  = Dispatcher::Instance()->GetUrl(
         'laporan_rekap_pengeluaran_kas_bulanan',
         'PopupUnitKerja',
         'view',
         'html'
      );

      $urlExportExcel   = Dispatcher::Instance()->GetUrl(
         'laporan_rekap_pengeluaran_kas_bulanan',
         'ExportLaporanPengeluaran',
         'view',
         'xlsx'
      ).'&'.$queryString;

      $this->mrTemplate->AddVar('content', 'URL_SEARCH', $urlSearch);
      $this->mrTemplate->AddVar('content', 'URL_EXPORT', $urlExportExcel);
      $this->mrTemplate->AddVar('data_unit', 'LEVEL', strtoupper($unitKerja['status']));
      $this->mrTemplate->AddVar('data_unit', 'UNIT_ID', $requestData['unit_id']);
      $this->mrTemplate->AddVar('data_unit', 'UNIT_NAMA', $requestData['unit_nama']);
      $this->mrTemplate->AddVar('data_unit', 'URL_POPUP_UNIT', $urlPopupUnit);

      if (empty($dataList)){
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
      }else{
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');

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
                  $dataGrid[$index]['nominal_pengadaan']       = number_format($dataList[$i]['nominal_pengadaan'], 0, ',','.');
                  $dataGrid[$index]['nominal_non_pengadaan']   = number_format($dataList[$i]['nominal_non_pengadaan'], 0, ',','.');
                  $dataGrid[$index]['nominal']                 = number_format($dataList[$i]['nominal'], 0, ',', '.');
                  $dataGrid[$index]['nominal_approve_pengadaan']        = number_format($dataList[$i]['nominal_approve_pengadaan'], 0, ',','.');
                  $dataGrid[$index]['nominal_approve_non_pengadaan']    = number_format($dataList[$i]['nominal_approve_non_pengadaan'], 0, ',','.');
                  $dataGrid[$index]['nominal_approve']                  = number_format($dataList[$i]['nominal_approve'], 0, ',','.');

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
               $dataGrid[$index]['class_name']  = 'table-common-even rkat';
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
               $dataGrid[$index]['class_name']  = 'table-common-even2 kegiatan';
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
               $dataGrid[$index]['class_name']  = 'table-common-even1 program';
               $dataGrid[$index]['level']       = 'program';
            }
            $index++;
         }

         foreach ($dataGrid as $grid) {
            $this->mrTemplate->clearTemplate('data_type');
            // deskripsi
            $this->mrTemplate->clearTemplate("LEVEL");
            $this->mrTemplate->AddVar('LEVEL','TYPE', strtoupper($grid['level']));
            $this->mrTemplate->AddVar('LEVEL', 'deskripsi', $grid['deskripsi']);

            // end deskripsi
            // nominal pengadaan
            if(strtoupper($grid['level']) != 'KOMPONEN'){
               $grid['nominal_pengadaan']                = number_format($dataPengadaan[$grid['kode_sistem']]['nominal_pengadaan'], 0, ',','.');
               $grid['nominal_non_pengadaan']            = number_format($dataPengadaan[$grid['kode_sistem']]['nominal_non_pengadaan'], 0, ',','.');
               $grid['nominal']                          = number_format($dataPengadaan[$grid['kode_sistem']]['nominal'], 0, ',','.');
               $grid['nominal_approve_pengadaan']        = number_format($dataPengadaan[$grid['kode_sistem']]['nominal_approve_pengadaan'], 0, ',','.');
               $grid['nominal_approve_non_pengadaan']    = number_format($dataPengadaan[$grid['kode_sistem']]['nominal_approve_non_pengadaan'], 0, ',','.');
               $grid['nominal_approve']                  = number_format($dataPengadaan[$grid['kode_sistem']]['nominal_approve'], 0, ',','.');
            }
            // end nominal pengadaan
            $this->mrTemplate->AddVars('data_item', $grid);
            $this->mrTemplate->parseTemplate('data_item', 'a');
         }
      }

   }
}
?>