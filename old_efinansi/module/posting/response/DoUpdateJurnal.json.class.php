<?php
/**
* ================= doc ====================
* FILENAME     : DoUpdateJurnal.json.class.php
* @package     : DoUpdateJurnal
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-02-24
* @Modified    : 2015-02-24
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/posting/business/AppPosting.class.php';

class DoUpdateJurnal extends JsonResponse
{
   public function ProcessRequest()
   {
      $mObj             = new AppPosting();
      $urlRedirect      = Dispatcher::Instance()->GetUrl(
         'posting',
         'Posting',
         'view',
         'html'
      );

      $urlReturn        = Dispatcher::Instance()->GetUrl(
         'posting',
         'UpdateJurnal',
         'view',
         'html'
      );
      if($mObj->method == 'post'){
         $requestData   = array();
         $tanggalDay    = $mObj->_POST['tanggal_day'];
         $tanggalMon    = $mObj->_POST['tanggal_mon'];
         $tanggalYear   = $mObj->_POST['tanggal_year'];

         $requestData['transaksi_id']  = $mObj->_POST['data_id'];
         $requestData['pembukuan_id']  = $mObj->_POST['pembukuan_id'];
         $requestData['tanggal']       = date('Y-m-d', mktime(0,0,0, $tanggalMon, $tanggalDay, $tanggalYear));
         $process       = $mObj->doUpdateJurnal($requestData);
         if($process === true){
            Messenger::Instance()->Send(
               'posting',
               'Posting',
               'view',
               'html',
               array(
                  $mObj->_POST,
                  'Update jurnal berhasil',
                  'notebox-done'
               ),
               Messenger::NextRequest
            );

            return array(
               // 'exec' => ''
               'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlRedirect.'&ascomponent=1"); popupClose();'
            );
         }else{
            Messenger::Instance()->Send(
               'posting',
               'doUpdateJurnal',
               'view',
               'html',
               array(
                  $mObj->_POST,
                  'Update jurnal gagal',
                  'notebox-warning'
               ),
               Messenger::NextRequest
            );

            return array(
               // 'exec' => ''
               'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlReturn.'&ascomponent=1");'
            );
         }
      }else{
         return array(
            // 'exec' => ''
            'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlRedirect.'&ascomponent=1"); popupClose();'
         );
      }


   }
}
?>