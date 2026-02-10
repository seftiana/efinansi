<?php

/**
 *
 * @class ViewPopupSekenarioJurnal
 * @package history_transaksi_keuangan_sp2d
 * @description untuk menjalankan query daftar transaksi keuagan sp2d
 * @analyst dyah fajar <dyah@gamatechno.com>
 * @author noor hadi <noor.hadi@gamatechno.com>
 * @since Januari 2014
 * @copyright 2014 Gamatechno Indonedia
 * @modified Eko Susilo <eko.susilo@gamatechno.com> 2014-10-20 10:22:20
 */


require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/history_transaksi_keuangan_sp2d/business/PopupSekenarioJurnal.class.php';

class ViewPopupSekenarioJurnal extends HtmlResponse
{
   public function TemplateModule()
   {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot') .
         'module/history_transaksi_keuangan_sp2d/template');
      $this->SetTemplateFile('view_popup_sekenario_jurnal.html');
   }

   public function TemplateBase()
   {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
      $this->SetTemplateFile('document-common-popup.html');
      $this->SetTemplateFile('layout-common-popup.html');
   }

   public function ProcessRequest(){
      $mObj          = new PopupSekenarioJurnal();
      $requestData   = array();

      if(isset($mObj->_POST['btncari'])){
         $requestData['nama']    = trim($mObj->_POST['nama']);
      }elseif(isset($mObj->_GET['search'])){
         $requestData['nama']    = Dispatcher::Instance()->_GET['nama'];
      }else{
         $requestData['nama']    = '';
      }

      $offset         = 0;
      $limit          = 20;
      $page           = 0;
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
      ).'&search='.Dispatcher::Instance()->Encrypt(1);

      $destination_id   = "popup-subcontent";
      $dataList         = $mObj->GetDataSkenario($offset, $limit, (array)$requestData);
      $total_data       = $mObj->GetCountSkenario((array)$requestData);
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

      // inisialisasi data
      $index         = 0;
      $_index        = 1;
      $skenario      = '';
      $dataGrid      = array();
      $dataCoa       = array();
      for ($i=0; $i < count($dataList);) {
         if((int)$skenario === (int)$dataList[$i]['id']){
            $dataCoa[$skenario][$_index]['nomor']  = $_index+1;
            $dataCoa[$skenario][$_index]['id']     = $dataList[$i]['akunId'];
            $dataCoa[$skenario][$_index]['kode']   = $dataList[$i]['akunKode'];
            $dataCoa[$skenario][$_index]['nama']   = $dataList[$i]['akunNama'];
            $dataCoa[$skenario][$_index]['status'] = $dataList[$i]['akun_dk'];

            $dataGrid[$index]['id']       = $dataList[$i]['akunId'];
            $dataGrid[$index]['kode']     = $dataList[$i]['akunKode'];
            $dataGrid[$index]['nama']     = $dataList[$i]['akunNama'];
            $dataGrid[$index]['status']   = $dataList[$i]['akun_dk'];
            $dataGrid[$index]['level']    = 'child';

            $i++;
            $_index+=1;
         }else{
            $skenario         = (int)$dataList[$i]['id'];
            unset($_index);
            $_index           = 0;
            $dataGrid[$index]['id']       = $dataList[$i]['id'];
            $dataGrid[$index]['kode']     = $dataList[$i]['kode'];
            $dataGrid[$index]['nama']     = $dataList[$i]['nama'];
            $dataGrid[$index]['level']    = 'parent';
         }
         $index++;
      }

      $return['json']['coa']     = json_encode($dataCoa);
      $return['data_list']       = $mObj->ChangeKeyName($dataGrid);
      $return['start']           = $offset+1;
      $return['request_data']    = $requestData;
      return $return;
   }

   public function ParseTemplate($data = NULL){
      $requestData      = $data['request_data'];
      $dataList         = $data['data_list'];
      $start            = $data['start'];
      $dataCoa          = $data['json'];

      $urlSearch        = Dispatcher::Instance()->GetUrl(
         'history_transaksi_keuangan_spj',
         'PopupSekenarioJurnal',
         'view',
         'html'
      );
      $this->mrTemplate->AddVar('content', 'NAMA', $requestData['nama']);
      $this->mrTemplate->AddVar('content', 'URL_SEARCH', $urlSearch);
      $this->mrTemplate->AddVars('content', $dataCoa, 'JSON_');

      if (empty($dataList)) {
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
      } else {
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');
         $index      = 0;
         foreach ($dataList as $list) {
            $this->mrTemplate->clearTemplate('debet');
            $this->mrTemplate->clearTemplate('kredit');
            $this->mrTemplate->clearTemplate('tipe_data');

            if(strtoupper($list['level']) == 'PARENT'){
               $this->mrTemplate->AddVar('tipe_data', 'TIPE', 'PARENT');
               $this->mrTemplate->AddVar('tipe_data', 'ID', $list['id']);
               $this->mrTemplate->AddVar('tipe_data', 'NAMA', $list['nama']);
               $list['nomor']    = $start;
               $list['class_name']  = 'table-common-even1';
               $list['row_style']   = 'font-weight: bold;';
               $start+=1;
            }else{
               $this->mrTemplate->AddVar('tipe_data', 'TIPE', 'child');
               $list['class_name']  = ($index % 2 <> 0) ? 'table-common-even' : '';
               switch (strtoupper($list['status'])) {
                  case 'D':
                     $this->mrTemplate->AddVar('debet', 'STATUS', 'YES');
                     break;
                  case 'K':
                     $this->mrTemplate->AddVar('kredit', 'STATUS', 'YES');
                     break;
                  default:
                     $this->mrTemplate->AddVar('debet', 'STATUS', 'NO');
                     $this->mrTemplate->AddVar('kredit', 'STATUS', 'NO');
                     break;
               }
               $index++;
            }
            $this->mrTemplate->AddVars('data_item', $list);
            $this->mrTemplate->parseTemplate('data_item', 'a');
         }
      }
   }
}

?>