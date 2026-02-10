<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/rencana_penerimaan/business/AppRencanaPenerimaan.class.php';

class ViewPopupDetailRencanaPenerimaan extends HtmlResponse
{
   function TemplateModule()
   {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot') .
         'module/rencana_penerimaan/template');
      $this->SetTemplateFile('view_popup_detail_rencana_penerimaan.html');
   }

   function TemplateBase() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
      $this->SetTemplateFile('document-common-popup.html');
      $this->SetTemplateFile('layout-common-popup.html');
   }

   function ProcessRequest()
   {
      $mObj          = new AppRencanaPenerimaan();
      $queryString   = $mObj->_getQueryString();
      $dataId        = Dispatcher::Instance()->Decrypt($mObj->_GET['data_id']);
      $months           = range(1, 12);
      $indonesianMonths = $mObj->indonesianMonth;
      $index            = 0;
      $dataRincianPenerimaan     = array();
      $dataRencanaPenerimaan     = $mObj->ChangeKeyName($mObj->getDataDetail($dataId));
      $dataDetailRincian         = $mObj->getDataDetailRincian($dataId);

      $requestData['id']                  = $dataRencanaPenerimaan['id'];
      $requestData['ta_id']               = $dataRencanaPenerimaan['ta_id'];
      $requestData['ta_nama']             = $dataRencanaPenerimaan['ta_nama'];
      $requestData['unit_id']             = $dataRencanaPenerimaan['unit_id'];
      $requestData['unit_nama']           = $dataRencanaPenerimaan['unit_nama'];
      $requestData['alokasi_pusat_id']    = $dataRencanaPenerimaan['alokasi_pusat_id'];
      $requestData['alokasi_pusat']       = $dataRencanaPenerimaan['alokasi_pusat'];
      $requestData['alokasi_unit_id']     = $dataRencanaPenerimaan['alokasi_id'];
      $requestData['alokasi_unit']        = $dataRencanaPenerimaan['alokasi_unit'];
      $requestData['kodepenerimaan_id']   = $dataRencanaPenerimaan['kode_penerimaan_id'];
      $requestData['kodepenerimaan_kode'] = $dataRencanaPenerimaan['kode_penerimaan_kode'];
      $requestData['kodepenerimaan_nama'] = $dataRencanaPenerimaan['kode_penerimaan_nama'];
      $requestData['volume']              = number_format($dataRencanaPenerimaan['volume'], 0, ',','.');
      $requestData['satuan']              = $dataRencanaPenerimaan['satuan'];
      $requestData['tarif']               = number_format($dataRencanaPenerimaan['tarif'], 0, ',','.');
      $requestData['nominal']             = number_format($dataRencanaPenerimaan['nominal'], 0, ',','.');
      $requestData['pagu']                = number_format($dataRencanaPenerimaan['realisasi_pagu'], 0, ',','.');
      $requestData['nominal_pagu']        = number_format($dataRencanaPenerimaan['nominal_pagu'], 0, ',','.');
      $requestData['keterangan']          = $dataRencanaPenerimaan['keterangan'];
      $requestData['sumber_dana_id']      = $dataRencanaPenerimaan['sumber_dana_id'];
      $requestData['sumber_dana_nama']    = $dataRencanaPenerimaan['sumber_dana_nama'];
      $requestData['nominal_total']       = number_format($dataRencanaPenerimaan['total_penerimaan'], 0, ',','.');
      $requestData['status']              = strtoupper($dataRencanaPenerimaan['status']);

      foreach ($months as $mon) {
         $nameIndex        = strtolower($indonesianMonths[$mon]['name']);
         $idIndex          = $indonesianMonths[$mon]['id'];
         $dataRincianPenerimaan[$index]['id']      = $indonesianMonths[$mon]['id'];
         $dataRincianPenerimaan[$index]['name']    = $indonesianMonths[$mon]['name'];
         $dataRincianPenerimaan[$index]['persen']  = 0;
         $dataRincianPenerimaan[$index]['nominal'] = 0;
         // ambil data rincian penerimaan dari data yang sudah di input
         if(!empty($dataDetailRincian)){
            $dataRincianPenerimaan[$index]['persen']  = $dataDetailRincian[$nameIndex]['persen'];
            $dataRincianPenerimaan[$index]['nominal'] = $dataDetailRincian[$nameIndex]['nominal'];
         }
         $index++;
      }

      $return['request_data']    = $requestData;
      $return['rincian_penerimaan']['data']     = json_encode($dataRincianPenerimaan);
      return $return;
   }

   function ParseTemplate($data = NULL)
   {
      $requestData            = $data['request_data'];
      $dataRincianPenerimaan  = $data['rincian_penerimaan'];

      $this->mrTemplate->AddVars('content', $requestData);
      $this->mrTemplate->AddVars('content', $dataRincianPenerimaan, 'RINCIAN_PENERIMAAN_');
      $this->mrTemplate->AddVar('content_status', 'STATUS', strtoupper($requestData['status']));
   }
}
?>