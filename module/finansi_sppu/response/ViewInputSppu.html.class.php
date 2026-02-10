<?php
/**
* ================= doc ====================
* FILENAME     : ViewInputSppu.html.class.php
* @package     : ViewInputSppu
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-04-07
* @Modified    : 2015-04-07
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/finansi_sppu/business/Sppu.class.php';
require_once GTFWConfiguration::GetValue('application','docroot').
'main/function/terbilang.php';



require_once GTFWConfiguration::GetValue('application','docroot').
'module/finansi_sppu/business/GetCookieSppu.class.php';

class ViewInputSppu extends HtmlResponse
{
   function TemplateModule(){
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
      'module/finansi_sppu/template/');
      $this->SetTemplateFile('view_input_sppu.html');
   }

   function ProcessRequest(){
      $messenger     = Messenger::Instance()->Receive(__FILE__);
      $mObj          = new Sppu();
      $queryString   = $mObj->_getQueryString();
      $requestQuery  = preg_replace('/(\search=[\d]+)/', '', $queryString);
      $requestQuery  = preg_replace('/(\data_id=[\d]+)/', '', $requestQuery);
      $requestQuery  = preg_replace('/\&[\&]+/', '&', $requestQuery);
      $dataId        = array();
      $dataGrid      = array();
      $getdate       = getdate();
      $currMon       = $getdate['mon'];
      $currDay       = $getdate['mday'];
      $currYear      = $getdate['year'];
      $tanggal       = date('Y-m-d', mktime(0,0,0, $currMon, $currDay, $currYear));
      $message       = $style = $messengerData = NULL;
      $requestData   = array();
      $requestData['tanggal']    = $tanggal;
      $requestData['bp']         = 'Y';

      //get id dari cookie
      $cookieSPPU= new GetCookieSppu(); 
      $getCookie = $cookieSPPU->get(); 
      
      if (!empty($getCookie)) {
            $cblist = $getCookie; 
            foreach ($cblist as $id) {
                $dataId[$id] = $id;
            }
        } else {
            if ($mObj->_POST['id']) {
                foreach ($mObj->_POST['id'] as $id) {
                    $dataId[$id] = $id;
                }
            }
        } 

        if($messenger){
         $messengerData    = $messenger[0][0];
         $message          = $messenger[0][1];
         $style            = $messenger[0][2];

         foreach ($messengerData['realisasi'] as $realisasi) {
            $dataId[$realisasi['id']]   = $realisasi['id'];
         }

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
      }
      $dataGrid      = $mObj->getDataDetailRealisasi($dataId);

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
            $tanggal,
            $tahun_awal,
            $tahun_akhir,
            false,
            false,
            false
         ),
         Messenger::CurrentRequest
      );
      $return['query_string']       = $queryString;
      $return['request_query']      = $requestQuery;
      $return['realisasi']          = $dataGrid;
      $return['data_id']            = $dataId;
      $return['message']            = $message;
      $return['style']              = $style;
      $return['request_data']       = $requestData;
      return $return;
   }

   function ParseTemplate($data = null){
      $mNumber          = new Number();
      $queryString      = $data['query_string'];
      $requestQuery     = $data['request_query'];
      $dataRealisasi    = $data['realisasi'];
      $dataId           = $data['data_id'];
      $message          = $data['message'];
      $style            = $data['style'];
      $requestData      = $data['request_data'];
      $nominal          = 0;
      $urlReturn        = Dispatcher::Instance()->GetUrl(
         'finansi_sppu',
         'Sppu',
         'view',
         'html'
      ).'&search=1&'.$requestQuery;

      $urlAction        = Dispatcher::Instance()->GetUrl(
         'finansi_sppu',
         'SaveSppu',
         'do',
         'json'
      ).'&'.$queryString;

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
      
      $this->mrTemplate->AddVar('content', 'URL_RETURN', $urlReturn);
      $this->mrTemplate->AddVar('content', 'URL_ACTION', $urlAction);

      foreach ($dataId as $id) {
         $this->mrTemplate->AddVars('data_id', array('id' => $id));
         $this->mrTemplate->parseTemplate('data_id', 'a');
      }
      if(empty($dataRealisasi)){
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
      }else{
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');
         $nomor      = 1;
         foreach ($dataRealisasi as $realisasi) {
            $nominal    += $realisasi['nominal'];
            $realisasi['nomor']           = $nomor;
            $realisasi['nominal_label']   = number_format($realisasi['nominal'], 2, ',','.');
            $this->mrTemplate->AddVars('data_list', $realisasi);
            $this->mrTemplate->parseTemplate('data_list', 'a');
            $nomor+=1;
         }
      }

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