<?php
/**
* ================= doc ====================
* FILENAME     : ViewEditSppu.html.class.php
* @package     : ViewEditSppu
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-04-08
* @Modified    : 2015-04-08
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'main/function/date.php';
require_once GTFWConfiguration::GetValue('application','docroot').
'module/finansi_sppu/business/Sppu.class.php';
require_once GTFWConfiguration::GetValue('application','docroot').
'main/function/terbilang.php';

class ViewEditSppu extends HtmlResponse
{
   function TemplateModule(){
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
      'module/finansi_sppu/template/');
      $this->SetTemplateFile('view_edit_sppu.html');
   }

   function ProcessRequest(){
      $messenger     = Messenger::Instance()->Receive(__FILE__);
      $mObj          = new Sppu();
      $queryString   = $mObj->_getQueryString();
      //$queryString   = preg_replace('/(search=[\d]+)/', '', $queryString);
      ///$queryString   = preg_replace('/(data_id=[\d]+)/', '', $queryString);
      //$queryString   = preg_replace('/\&[\&]+/', '&', $queryString);
      //$queryString   = preg_replace('/[\&]$/', '', $queryString);
      $queryReturn   = ($queryString == '') ? '' : '&search=1&'.$queryString;
      $dataId        = Dispatcher::Instance()->Decrypt($mObj->_GET['data_id']);
      $message       = $style = $messengerData = NULL;
      $dataSppu      = $mObj->getDataDetailSppu($dataId);
      $dataList      = $mObj->getDataSppuItems($dataId);
      //print_r($queryString);
      
      $requestData['id']            = $dataSppu['id'];
      $requestData['nomor']         = $dataSppu['nomor'];
      $requestData['tanggal']       = date('Y-m-d', strtotime($dataSppu['tanggal']));
      $requestData['nomor_bukti']   = $dataSppu['nomor_bukti'];
      $requestData['bank']          = $dataSppu['bank'];
      $requestData['no_rekening']   = $dataSppu['nomor_rekening'];
      $requestData['no_cek_giro']   = $dataSppu['nomor_cek_giro'];
      $requestData['bp']            = $dataSppu['bank_payment'];
      $requestData['cr']            = $dataSppu['cash_receipt'];
      $requestData['keterangan']    = $dataSppu['keterangan'];
      
      if($messenger){
         $messengerData    = $messenger[0][0];
         $message          = $messenger[0][1];
         $style            = $messenger[0][2];
         $tanggalDay       = (int)$messengerData['tanggal_day'];
         $tanggalMon       = (int)$messengerData['tanggal_mon'];
         $tanggalYear      = (int)$messengerData['tanggal_year'];
         $tanggal          = date('Y-m-d', mktime(0,0,0, $tanggalMon, $tanggalDay, $tanggalYear));
         $requestData['tanggal']       = $tanggal;
         $requestData['nomor_bukti']   = $messengerData['nomor_bukti'];
         $requestData['bank']          = $messengerData['bank'];
         $requestData['no_rekening']   = $messengerData['no_rekening'];
         $requestData['no_cek_giro']   = $messengerData['no_cek_giro'];
         $requestData['bp']            = $messengerData['bp'];
         $requestData['cr']            = $messengerData['cr'];
         $requestData['keterangan']    = $messengerData['keterangan'];
         $dataList    = $messengerData['data'];
            if(!empty($dataList)) {
                $rIndex   = 0;
                foreach ($dataList as $r) { 
                  $realisasiData[$rIndex]['realisasi_id']  = $r['realisasi_id'];
                  $realisasiData[$rIndex]['no_fpa']        = $r['no_fpa'];//pengjuan
                  $realisasiData[$rIndex]['nominal']       = $r['nominal'];
                  $realisasiData[$rIndex]['prog_nama']     = $r['prog_nama'];
                  $realisasiData[$rIndex]['keg_nama']      = $r['keg_nama'];
                  $realisasiData[$rIndex]['sub_keg_nama']  = $r['sub_keg_nama'];
                  $realisasiData[$rIndex]['lingkup']       = $r['lingkup'];
                  $realisasiData[$rIndex]['unit_nama']     = $r['unit_nama'];
                  $realisasiData[$rIndex]['tanggal']       = $r['tanggal'];
                  $rIndex+=1;
                }
            }   else {
                $realisasiData = array();
            }
      } else {
            if(!empty($dataList)) {
                $rIndex   = 0;
                foreach ($dataList as $r) { 
                      $realisasiData[$rIndex]['realisasi_id']  = $r['realisasi_id'];
                      $realisasiData[$rIndex]['no_fpa']        = $r['no_fpa'];//pengjuan
                      $realisasiData[$rIndex]['nominal']       = $r['nominal'];
                      $realisasiData[$rIndex]['prog_nama']     = $r['program_nama'];
                      $realisasiData[$rIndex]['keg_nama']      = $r['kegiatan_nama'];
                      $realisasiData[$rIndex]['sub_keg_nama']  = $r['sub_kegiatan_nama'];
                      $realisasiData[$rIndex]['lingkup']       = $r['lingkup_komponen'];
                      $realisasiData[$rIndex]['unit_nama']     = $r['unit_nama'];
                      $realisasiData[$rIndex]['tanggal']       = IndonesianDate($r['tanggal'], 'YYYY-MM-DD');
                      $rIndex+=1;
                }
            } else {
                $realisasiData = array();
            }    
      }

      # GTFW Tanggal
      $tahun_awal       = date('Y',time())-5;
      $tahun_akhir      = date('Y', time())+5;
      Messenger::Instance()->SendToComponent(
         'tanggal',
         'Tanggal',
         'view',
         'html',
         'tanggal',
         array(
            $requestData['tanggal'],
            $tahun_awal,
            $tahun_akhir,
            false,
            false,
            false
         ),
         Messenger::CurrentRequest
      );

      $return['realisasi']['data']    = json_encode($realisasiData);
      $return['data_sppu']    = $dataSppu;
      $return['data_list']    = $dataList;
      $return['message']      = $message;
      $return['style']        = $style;
      $return['query_return'] = $queryReturn;
      $return['query_string'] = $queryString;
      $return['request_data'] = $requestData;
      return $return;
   }

   function ParseTemplate($data = null){
      $mNumber          = new Number();
      $queryString      = $data['query_string'];
      $requestQuery     = $data['query_return'];
      $dataList         = $data['data_list'];
      $message          = $data['message'];
      $style            = $data['style'];
      $requestData      = $data['request_data'];
      $nominal          = 0;

      $urlReturn        = Dispatcher::Instance()->GetUrl(
         'finansi_sppu',
         'ListSppu',
         'view',
         'html'
      ).'&search=1&'.$requestQuery;

      $urlAction        = Dispatcher::Instance()->GetUrl(
         'finansi_sppu',
         'UpdateSppu',
         'do',
         'json'
      ).'&'.$queryString;

      $urlPopupFpa        = Dispatcher::Instance()->GetUrl(
         'finansi_sppu',
         'PopupFpa',
         'view',
         'html'
      );
            
      $this->mrTemplate->AddVar('content', 'URL_POPUP_FPA', $urlPopupFpa);
      $this->mrTemplate->AddVar('content', 'URL_RETURN', $urlReturn);
      $this->mrTemplate->AddVar('content', 'URL_ACTION', $urlAction);

      $checked = 'checked="checked"';
     
      if(strtoupper($requestData['bp']) == 'Y'){
         $this->mrTemplate->AddVar('content','CHECKED_BP', $checked);
      }else{
         $this->mrTemplate->AddVar('content','CHECKED_BP', '');
      }

      if(strtoupper($requestData['cr']) == 'Y'){
         $this->mrTemplate->AddVar('content','CHECKED_CR', $checked);
      }else{
         $this->mrTemplate->AddVar('content','CHECKED_CR', '');
      }
      
      if(empty($dataList)){
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
      }else{
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');
         $nomor      = 1;
         
         foreach ($dataList as $realisasi) {
            $nominal    += $realisasi['nominal'];
            $realisasi['nomor']           = $nomor;
            $realisasi['nominal_label']   = number_format($realisasi['nominal'], 2, ',','.');
            //$this->mrTemplate->AddVars('data_list', $realisasi);
            //$this->mrTemplate->parseTemplate('data_list', 'a');
            $nomor+=1;
         }
      }
      $this->mrTemplate->AddVars('content', $data['realisasi'], 'REALISASI_');
      $this->mrTemplate->AddVar('content', 'NOMINAL', number_format($nominal, 2, ',','.'));
      $this->mrTemplate->AddVar('content', 'TERBILANG', $mNumber->Terbilang($nominal, 3));
      $this->mrTemplate->AddVars('content', $requestData);

      if($message){
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $message);
         $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $style);
      }
   }
}
?>