<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/rencana_penerimaan/business/AppRencanaPenerimaan.class.php';
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/user_unit_kerja/business/UserUnitKerja.class.php';

class ViewRencanaPenerimaan extends HtmlResponse
{

   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
         'module/rencana_penerimaan/template');
      $this->SetTemplateFile('view_rencana_penerimaan.html');
   }

   function ProcessRequest() {
      $msg              = Messenger::Instance()->Receive(__FILE__);
      $mObj             = new AppRencanaPenerimaan();
      $userId           = $mObj->getUserId();
      $mUnitObj         = new UserUnitKerja();
      $unitKerja        = $mUnitObj->GetUnitKerjaRefUser($userId);
      $arrPeriodeTahun  = $mObj->getPeriodeTahun();
      $periodeTahun     = $mObj->getPeriodeTahun(array('active' => true));
      $requestData      = array();
      $queryString      = '';

      if(isset($mObj->_POST['btncari'])){
         $requestData['kode']       = $mObj->_POST['kodenama'];
         $requestData['ta_id']      = $mObj->_POST['tahun_anggaran'];
         $requestData['unit_id']    = $mObj->_POST['unit_id'];
         $requestData['unit_nama']  = $mObj->_POST['unit_nama'];
      }elseif(isset($mObj->_GET['search'])){
         $requestData['ta_id']      = Dispatcher::Instance()->Decrypt($mObj->_GET['ta_id']);
         $requestData['unit_id']    = Dispatcher::Instance()->Decrypt($mObj->_GET['unit_id']);
         $requestData['unit_nama']  = Dispatcher::Instance()->Decrypt($mObj->_GET['unit_nama']);
         $requestData['kode']       = Dispatcher::Instance()->Decrypt($mObj->_GET['kode']);
      }else{
         $requestData['ta_id']      = $periodeTahun[0]['id'];
         $requestData['unit_id']    = $unitKerja['id'];
         $requestData['unit_nama']  = $unitKerja['nama'];
         $requestData['kode']       = '';
      }

      foreach ($arrPeriodeTahun as $ta) {
         if((int)$ta['id'] === (int)$requestData['ta_id']){
            $requestData['ta_nama'] = $ta['name'];
         }
      }

      if(method_exists(Dispatcher::Instance(), 'getQueryString')){
         # @param array
         $queryString      = Dispatcher::instance()->getQueryString($requestData);
      }else{
         $query            = array();
         foreach ($requestData as $key => $value) {
            $query[$key]   = Dispatcher::Instance()->Encrypt($value);
         }

         $queryString      = urldecode(http_build_query($query));
      }

      $offset        = 0;
      $limit         = 20;
      $page          = 0;
      if(isset($_GET['page'])){
         $page       = (string) $_GET['page']->StripHtmlTags()->SqlString()->Raw();
         $offset     = ($page - 1) * $limit;
      }
      #paging url
      $url           = Dispatcher::Instance()->GetUrl(
        Dispatcher::Instance()->mModule,
        Dispatcher::Instance()->mSubModule,
        Dispatcher::Instance()->mAction,
        Dispatcher::Instance()->mType
      ).'&search='.Dispatcher::Instance()->Encrypt(1).'&'.$queryString;

      $destination_id   = "subcontent-element";
      $dataList         = $mObj->getData($offset, $limit, (array)$requestData);
      $totalPerUnit     = $mObj->getTotalPerUnit((array)$requestData);
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


      # Combobox
      Messenger::Instance()->SendToComponent(
         'combobox',
         'Combobox',
         'view',
         'html',
         'periode_tahun',
         array(
            'tahun_anggaran',
            $arrPeriodeTahun,
            $requestData['ta_id'],
            false,
            'id="cmb_tahun_anggaran" style="width: 95px;"'
         ),
         Messenger::CurrentRequest
      );

      if($msg){
         $message       = $msg[0][1];
         $style         = $msg[0][2];
      }

      $return['request_data']    = $requestData;
      $return['query_string']    = $queryString;
      $return['unit_kerja']      = $unitKerja;
      $return['message']         = $message;
      $return['style']           = $style;
      $return['total_perunit']   = $mObj->ChangeKeyName($totalPerUnit);
      $return['data_list']       = $mObj->ChangeKeyName($dataList);
      $return['start']           = $offset+1;
      return $return;
   }

   function ParseTemplate($data = NULL) {
      $requestData   = $data['request_data'];
      $queryString   = $data['query_string'];
      $unitKerja     = $data['unit_kerja'];
      $dataList      = $data['data_list'];
      $totalPerUnit  = $data['total_perunit'];
      $start         = $data['start'];
      $message       = $data['message'];
      $style         = $data['style'];
      $urlSearch     = Dispatcher::Instance()->GetUrl(
         'rencana_penerimaan',
         'RencanaPenerimaan',
         'view',
         'html'
      );

      $urlPopupUnit  = Dispatcher::Instance()->GetUrl(
         'rencana_penerimaan',
         'popupUnitKerja',
         'view',
         'html'
      );

      $urlAdd        = Dispatcher::Instance()->GetUrl(
         'rencana_penerimaan',
         'InputRencanaPenerimaan',
         'view',
         'html'
      ).'&'.$queryString;

      $urLEdit       = Dispatcher::Instance()->GetUrl(
         'rencana_penerimaan',
         'EditRencanaPenerimaan',
         'view',
         'html'
      ).'&'.$queryString;

      $urlDetail     = Dispatcher::Instance()->GetUrl(
         'rencana_penerimaan',
         'PopupDetailRencanaPenerimaan',
         'view',
         'html'
      );

      $this->mrTemplate->AddVar('content', 'URL_SEARCH', $urlSearch);
      $this->mrTemplate->AddVars('content', $requestData);
      $this->mrTemplate->AddVar('data_unit', 'LEVEL', strtoupper($unitKerja['status']));
      $this->mrTemplate->AddVar('data_unit', 'UNIT_ID', $requestData['unit_id']);
      $this->mrTemplate->AddVar('data_unit', 'UNIT_NAMA', $requestData['unit_nama']);
      $this->mrTemplate->AddVar('data_unit', 'URL_POPUP_UNIT', $urlPopupUnit);
      $this->mrTemplate->AddVar('content', 'URL_ADD', $urlAdd);

      //mulai bikin tombol delete
      $label = "Manajemen Rencana Penerimaan";
      $urlDelete = Dispatcher::Instance()->GetUrl(
         'rencana_penerimaan',
         'deleteRencanaPenerimaan',
         'do',
         'html'
      ).'&'.$queryString;

      $urlReturn = Dispatcher::Instance()->GetUrl(
         'rencana_penerimaan',
         'RencanaPenerimaan',
         'view',
         'html') . '&search=1&'.$queryString;

      Messenger::Instance()->Send(
         'confirm',
         'confirmDelete',
         'do',
         'html',
         array(
            $label,
            $urlDelete,
            $urlReturn
            ), Messenger::NextRequest);

      $this->mrTemplate->AddVar('content', 'URL_DELETE', Dispatcher::Instance()->GetUrl(
         'confirm',
         'confirmDelete',
         'do',
         'html'
      ));
      $this->mrTemplate->AddVar('content', 'CONTROL_LABEL', $label);
      $this->mrTemplate->AddVar('content', 'CONTROL_ACTION', $urlDelete);
      $this->mrTemplate->AddVar('content', 'CONTROL_RETURN', $urlReturn);
      if($message) {
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $message);
         $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $style);
      }

      if(empty($dataList)){
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
      }else{
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');
         $unit          = '';
         $dataGrid      = array();
         $index         = 0;
         $taId          = '';
         $dataRencana   = array();

         for ($i=0; $i < count($dataList);) {
            if((int)$unit === (int)$dataList[$i]['unit_id']){
               $parentKodeSistem          = $taId.'.'.$unit;
               $dataRencana[$parentKodeSistem]['nominal']   = 0;
               $dataGrid[$index]['nomor'] = $start;
               $dataGrid[$index]['id']    = $dataList[$i]['rencana_penerimaan_id'];
               $dataGrid[$index]['kode']  = $dataList[$i]['kode'];
               $dataGrid[$index]['nama']  = $dataList[$i]['nama'];
               $dataGrid[$index]['keterangan']  = $dataList[$i]['keterangan'];
               $dataGrid[$index]['type']        = 'CHILD';
               $dataGrid[$index]['class_name']  = ($start % 2 <> 0) ? 'table-common-even' : '';
               $dataGrid[$index]['row_style']   = '';
               $dataGrid[$index]['nominal']     = $dataList[$i]['nominal'];
               $dataGrid[$index]['status']      = $dataList[$i]['status'];
               $i++;
               $start++;
            }else{
               $unit          = (int)$dataList[$i]['unit_id'];
               $taId          = (int)$dataList[$i]['ta_id'];
               $kodeSistem    = $taId.'.'.$unit;
               $dataRencana[$kodeSistem]['nominal']   = 0;
               $dataGrid[$index]['id']    = $dataList[$i]['unit_id'];
               $dataGrid[$index]['kode']  = $dataList[$i]['unit_kode'];
               $dataGrid[$index]['nama']  = $dataList[$i]['unit_nama'];
               $dataGrid[$index]['type']  = 'PARENT';
               $dataGrid[$index]['class_name']  = 'table-common-even1';
               $dataGrid[$index]['row_style']   = 'font-weight: bold;';
               $dataGrid[$index]['kode_sistem'] = $kodeSistem;
            }
            $index++;
         }

         // untuk menghitung total penerimaan per unit
         for ($i=0; $i < count($totalPerUnit);) {
            if((int)$unit === (int)$totalPerUnit[$i]['unit_id']){
               $parentKodeSistem          = $taId.'.'.$unit;
               $dataRencana[$parentKodeSistem]['nominal_total']   = $totalPerUnit[$i]['nominal'];
               $i++;

            }else{
               $unit          = (int)$totalPerUnit[$i]['unit_id'];
               $taId          = (int)$totalPerUnit[$i]['ta_id'];
               $kodeSistem    = $taId.'.'.$unit;
               $dataRencana[$kodeSistem]['nominal_total']   = 0;
            }
         }
         // end - hitung total penerimaan

         foreach ($dataGrid as $grid) {
           // $this->mrTemplate->clearTemplate('cekbox');
            $dataId        = Dispatcher::Instance()->Encrypt($grid['id']);
            switch (strtoupper($grid['type'])) {
               case 'PARENT':
                  $nominal             = $dataRencana[$grid['kode_sistem']]['nominal_total'];
                  if($nominal < 0){
                     $grid['nominal']  = '('.number_format(abs($nominal), 0, ',','.').')';
                  }else{
                     $grid['nominal']  = number_format($nominal, 0, ',','.');
                  }
                  $this->mrTemplate->SetAttribute('content_description', 'visibility', 'hidden');
                  $this->mrTemplate->AddVar('content_checkbox', 'CONDITION', 'PARENT');
                  $this->mrTemplate->AddVar('content_links', 'CONDITION', 'PARENT');
                  $this->mrTemplate->AddVar('content_links', 'URL_ADD', $urlAdd.'&unit_penerima='.$dataId);
                  break;
               case 'CHILD':
                  $nominal             = $grid['nominal'];
                  if($nominal < 0){
                     $grid['nominal']  = '('.number_format(abs($nominal), 0, ',','.').')';
                  }else{
                     $grid['nominal']  = number_format($nominal, 0, ',','.');
                  }
                  $this->mrTemplate->SetAttribute('content_description', 'visibility', 'visible');
                  $this->mrTemplate->AddVar('content_description', 'DESKRIPSI', $grid['keterangan']);
                  $this->mrTemplate->AddVar('content_links', 'CONDITION', 'CHILD');
                  $this->mrTemplate->AddVar('content_links', 'URL_DETAIL', $urlDetail.'&data_id='.$dataId);

                  // checkbox
                  $this->mrTemplate->AddVar('content_checkbox', 'CONDITION', 'CHILD');
                  $this->mrTemplate->AddVar('checkbox_status', 'LEVEL', strtoupper($grid['status']));
                  $this->mrTemplate->AddVar('link_status', 'LEVEL', strtoupper($grid['status']));

                  $grid['class_status']      = 'level_'.strtolower($grid['status']);
                  $this->mrTemplate->AddVar('content_links', 'url_popup_detail', $urlDetail.'&data_id='.$dataId);
                  $this->mrTemplate->AddVar('link_status', 'url_edit', $urLEdit.'&data_id='.$dataId);
                  $this->mrTemplate->AddVar('checkbox_status', 'ID', $grid['id']);
                  $this->mrTemplate->AddVar('checkbox_status', 'NAME', $grid['nama'].' ('.$grid['keterangan'].')');
                  break;
               default:
                  $grid['nominal']  = number_format(0, 0, ',','.');
                  $this->mrTemplate->SetAttribute('content_description', 'visibility', 'hidden');
                 // $this->mrTemplate->AddVar('cekbox', 'IS_SHOW', 'NO');
                  $this->mrTemplate->clearTemplate('content_links');
                  $this->mrTemplate->clearTemplate('content_checkbox');
                  $this->mrTemplate->clearTemplate('link_status');
                  break;
            }

            $this->mrTemplate->AddVars('data_list', $grid);
            $this->mrTemplate->parseTemplate('data_list', 'a');
         }
      }
   }
}
?>