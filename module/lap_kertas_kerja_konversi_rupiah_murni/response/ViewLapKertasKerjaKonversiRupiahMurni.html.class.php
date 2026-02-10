<?php

/**
 * Class ViewLapKertasKerjaKonversiRupiahMurni
 * @package lap_kertas_kerja_konversi_rupiah_murni
 * @copyright 2011 Gamatechno
 */

require_once GTFWConfiguration::GetValue('application', 'docroot') .
'module/lap_kertas_kerja_konversi_rupiah_murni/business/LapKertasKerjaKonversiRupiahMurni.class.php';

/**
 * Class ViewLapKertasKerjaKonversiRupiahMurni
 * @todo untuk menampilkan Lap Kertas Kerja Konversi Rupiah Murni
 */
class ViewLapKertasKerjaKonversiRupiahMurni extends HtmlResponse
{
   public function TemplateModule(){
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') .
      'module/lap_kertas_kerja_konversi_rupiah_murni/template');
      $this->SetTemplateFile('view_lap_kertas_kerja_konversi_rupiah_murni.html');
   }

   public function ProcessRequest(){
      $mObj             = new LapKertasKerjaKonversiRupiahMurni();
      $arrPeriodeTahun  = $mObj->GetPeriodeTahun();
      $periodeTahun     = $mObj->GetPeriodeTahun(array('active' => true));
      $startYear        = date('Y', strtotime($periodeTahun[0]['start']));
      $endYear          = date('Y', strtotime($periodeTahun[0]['end']));

      $LapKertarKerjaKonversiRupiahMurni = new LapKertasKerjaKonversiRupiahMurni();
      $tahunAnggaran = $LapKertarKerjaKonversiRupiahMurni->GetComboTahunAnggaran();

      if ($_POST['tahun_anggaran'] == ""){
         for ($i = 0;$i < count($tahunAnggaran);$i++){
            if ($tahunAnggaran[$i]['thanggarIsAktif'] = 'Y'){
                  $idTahunAnggaran = $tahunAnggaran[$i]['id'];
                  break;
            }
         }
      } else {
         $idTahunAnggaran=$_POST['tahun_anggaran'];
      }

      /**
       * membuat combobox untuk tahun anggaran
       */
      Messenger::Instance()->SendToComponent(
                     'combobox',
                     'Combobox',
                     'view',
                     'html',
                     'tahun_anggaran',
                     array(
                          'tahun_anggaran',
                          $tahunAnggaran,
                          $idTahunAnggaran
                        ) ,
                     Messenger::CurrentRequest);

         /**
          * untuk menentukan nilai awal tanggal pada dropdown tanggal
          */
         if((isset($_POST['trans_tanggal_day']))
         && (isset($_POST['trans_tanggal_mon'])) &&
           (isset($_POST['trans_tanggal_year']))){
               $trans_tanggal_day = $_POST['trans_tanggal_day'];
            $trans_tanggal_mon = $_POST['trans_tanggal_mon'];
            $trans_tanggal_year = $_POST['trans_tanggal_year'];
            $trans_tanggal_selected = $trans_tanggal_year.'-'.$trans_tanggal_mon.
                              '-'.$trans_tanggal_day;
      } else {
         $trans_tanggal_selected= date('Y').'-'.date('m').'-'.date('d');
      }
      $periode_awal = date('Y')-20;
      $periode_akhir= date('Y');

      /**
       * untuk membuat tanggal dalam dropdown
       */
         Messenger::Instance()->SendToComponent(
                     'tanggal',
                     'Tanggal',
                     'view',
                     'html',
                     'trans_tanggal',
                     array(
                        $trans_tanggal_selected,
                        $periode_awal,
                        $periode_akhir
                        ),
                     Messenger::CurrentRequest);
      $itemViewed = 20;
      $currPage = 1;
      $startRec = 0 ;

      if(isset($_GET['page'])) {
         $currPage = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
         $startRec =($currPage-1) * $itemViewed;
      }

     $return['idTa']=$idTahunAnggaran;
      $return['transTanggal'] = $trans_tanggal_selected;
      $totalData =
      $LapKertarKerjaKonversiRupiahMurni->GetCountDataLapKertasKerjaKonversiRupiahMurni($idTahunAnggaran,
      $trans_tanggal_selected);
      $return['data'] =
      $LapKertarKerjaKonversiRupiahMurni->GetDataLapKertasKerjaKonversiRupiahMurni($idTahunAnggaran,
        $trans_tanggal_selected,$startRec,$itemViewed);

      $url  = Dispatcher::Instance()->GetUrl(
              Dispatcher::Instance()->mModule,
              Dispatcher::Instance()->mSubModule,
              Dispatcher::Instance()->mAction,
              Dispatcher::Instance()->mType .
              '&thanggar=' . Dispatcher::Instance()->Encrypt($idTahunAnggaran) .
              '&trans_tanggal=' . Dispatcher::Instance()->Encrypt($trans_tanggal_selected) .
              '&cari=' . Dispatcher::Instance()->Encrypt(1));

      Messenger::Instance()->SendToComponent
            ('paging', 'Paging', 'view', 'html', 'paging_top',
            array($itemViewed, $totalData, $url, $currPage),
            Messenger::CurrentRequest);

      $return['start_rec'] = $startRec;
      $return['item_viewed'] = $itemViewed;
      return $return;
   }

   public function ParseTemplate($data = NULL){
      /**
       * untuk action proses pencarian
       */
      $urlAction = Dispatcher::Instance()->GetUrl(
            'lap_kertas_kerja_konversi_rupiah_murni',
            'LapKertasKerjaKonversiRupiahMurni',
            'view',
            'html');
      $this->mrTemplate->AddVar('content', 'URL_SEARCH', $urlAction);

   /**
    * untuk proses action cetak
    */
      $urlPrint = Dispatcher::Instance()->GetUrl(
            'lap_kertas_kerja_konversi_rupiah_murni',
            'CetakLapKertasKerjaKonversiRupiahMurni',
            'view',
            'html').
            '&idTa='.Dispatcher::Instance()->Encrypt($data['idTa']).
               '&trans_tanggal='.Dispatcher::Instance()->Encrypt($data['transTanggal'].
                 '&start_rec='.Dispatcher::Instance()->Encrypt($data['start_rec']) .
                 '&item_viewed='.Dispatcher::Instance()->Encrypt($data['item_viewed'])
                 );

      /**
       * untuk proses konversi ke excel
       */
      $urlExcel = Dispatcher::Instance()->GetUrl(
            'lap_kertas_kerja_konversi_rupiah_murni',
            'ExcelLapKertasKerjaKonversiRupiahMurni',
            'view',
            'xls').
            '&idTa='.Dispatcher::Instance()->Encrypt($data['idTa']).
               '&trans_tanggal='.Dispatcher::Instance()->Encrypt($data['transTanggal'].
                 '&start_rec='.Dispatcher::Instance()->Encrypt($data['start_rec']) .
                 '&item_viewed='.Dispatcher::Instance()->Encrypt($data['item_viewed'])
                 );

      $this->mrTemplate->AddVar('content', 'URL_PRINT', $urlPrint);
      $this->mrTemplate->AddVar('content', 'URL_EXCEL', $urlExcel);


      if (empty($data['data'])) {
            $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
         } else {
            $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');

            $dataList = $data['data'];

         for ($i=0; $i<sizeof($dataList); $i++) {
            $no = $i+$data['start'];

            if ($no % 2 != 0)
               $dataList[$i]['class_name'] = 'table-common-even';
            else
               $dataList[$i]['class_name'] = '';

               if(!is_null($dataList[$i]['sap_jumlah'])){
               $dataList[$i]['sap_jumlah']=number_format($dataList[$i]['sap_jumlah'], 0, ',', '.');
            }
            if(!is_null($dataList[$i]['sak_jumlah'])){
               $dataList[$i]['sak_jumlah']=number_format($dataList[$i]['sak_jumlah'], 0, ',', '.');
            }

            $this->mrTemplate->AddVars('data_item', $dataList[$i], '');
            $this->mrTemplate->parseTemplate('data_item', 'a');
         }
      }

   }
}
?>