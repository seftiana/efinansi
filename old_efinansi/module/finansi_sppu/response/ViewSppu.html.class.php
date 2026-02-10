<?php
/**
* ================= doc ====================
* FILENAME     : ViewSppu.html.class.php
* @package     : ViewSppu
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-03-24
* @Modified    : 2015-03-24
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/finansi_sppu/business/Sppu.class.php';
require_once GTFWConfiguration::GetValue('application','docroot').
'module/user_unit_kerja/business/UserUnitKerja.class.php';

class ViewSppu extends HtmlResponse
{
   function TemplateModule(){
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
      'module/finansi_sppu/template/');
      $this->SetTemplateFile('view_sppu.html');
   }

   function ProcessRequest(){      
      
      $messenger  = Messenger::Instance()->Receive(__FILE__);
      $mObj       = new Sppu();
      $mUnitObj   = new UserUnitKerja();
      $userId     = $mObj->getUserId();
      $unitKerja  = $mUnitObj->GetUnitKerjaRefUser($userId);
      $arrPeriodeTahun     = $mObj->getPeriodeTahun();
      $periodeTahun        = $mObj->getPeriodeTahun(array('active' => true));
      $arrProgram          = (array)$mObj->GetDataProgram();
      $requestData         = array();
      $queryString         = '';
      $message             = $style = $messengerData = NULL;
      
      $months              = $mObj->indonesianMonth;

      if(isset($mObj->_POST['btnSearch'])){
         $requestData['ta_id']      = $mObj->_POST['tahun_anggaran'];
         $requestData['unit_id']    = $mObj->_POST['unit_id'];
         $requestData['unit_nama']  = $mObj->_POST['unit_nama'];
         $requestData['program_id'] = $mObj->_POST['program'];
         $requestData['kode']       = $mObj->_POST['kode'];
         $requestData['nama']       = $mObj->_POST['nama'];
         $requestData['bulan']      = $mObj->_POST['bulan'];
      }elseif(isset($mObj->_GET['search'])){
         $requestData['ta_id']      = Dispatcher::Instance()->Decrypt($mObj->_GET['ta_id']);
         $requestData['unit_id']    = Dispatcher::Instance()->Decrypt($mObj->_GET['unit_id']);
         $requestData['unit_nama']  = Dispatcher::Instance()->Decrypt($mObj->_GET['unit_nama']);
         $requestData['program_id'] = Dispatcher::Instance()->Decrypt($mObj->_GET['program_id']);
         $requestData['kode']       = Dispatcher::Instance()->Decrypt($mObj->_GET['kode']);
         $requestData['nama']       = Dispatcher::Instance()->Decrypt($mObj->_GET['nama']);
         $requestData['bulan']      = Dispatcher::Instance()->Decrypt($mObj->_GET['bulan']);
      }else{
         $requestData['ta_id']      = $periodeTahun[0]['id'];
         $requestData['unit_id']    = $unitKerja['id'];
         $requestData['unit_nama']  = $unitKerja['nama'];
         $requestData['program_id'] = '';
         $requestData['kode']       = '';
         $requestData['nama']       = '';
         $requestData['bulan']   = '';
      }

      // foreach ($arrPeriodeTahun as $ta) {
      //    if((int)$ta['id'] === (int)$requestData['ta_id']){
      //       $requestData['ta_nama'] = $ta['name'];
      //    }
      // }
      foreach ($arrPeriodeTahun as $ta) {
         if((int)$ta['id'] === (int)$requestData['ta_id']){
            $requestData['ta_nama']    = $ta['name'];
            $requestData['active']     = $ta['active'];
            $requestData['open']       = $ta['open'];
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
      ).'&search='.Dispatcher::Instance()->Encrypt(1).'&'.$queryString;

      $destination_id   = "subcontent-element";
      $dataList         = $mObj->getDataRealisasi($offset, $limit, (array)$requestData);
      $total_data       = $mObj->CountDataRealisasi((array)$requestData);

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
         'tahun_anggaran',
         array(
            'tahun_anggaran',
            $arrPeriodeTahun,
            $requestData['ta_id'],
            false,
            'id="cmb_tahun_anggaran" style="width: 135px;"'
         ),
         Messenger::CurrentRequest
      );

      # Combobox
      Messenger::Instance()->SendToComponent(
         'combobox',
         'Combobox',
         'view',
         'html',
         'program',
         array(
            'program',
            array(),
            NULL,
            true,
            'id="cmb_program"'
         ),
         Messenger::CurrentRequest
      );
      # Combobox
      Messenger::Instance()->SendToComponent(
         'combobox',
         'Combobox',
         'view',
         'html',
         'bulan',
         array(
            'bulan',
            $months,
            $requestData['bulan'],
            true,
            'id="cmb_bulan"'
         ),
         Messenger::CurrentRequest
      );
      if($messenger){
         $messengerData    = $messenger[0][0];
         $style            = $messenger[0][2];
         $message          = $messenger[0][1];
      }

      $return['post_data']       = $messengerData;
      $return['message']         = $message;
      $return['style']           = $style;
      $return['periode_tahun']   = $periodeTahun;
      $return['unit_kerja']      = $unitKerja;
      $return['request_data']    = $requestData;
      $return['program']['data'] = json_encode($arrProgram);
      $return['data_list']       = $dataList;
      $return['start']           = $offset+1;
      $return['query_string']    = $queryString;
      $return['limit']           = $limit;
      return $return;
   }

   function ParseTemplate($data = null){
      $queryString   = $data['query_string'];
      $unitKerja     = $data['unit_kerja'];
      $requestData   = $data['request_data'];
      $periodeTahun  = $data['periode_tahun'];
      $program       = $data['program'];
      $dataList      = $data['data_list'];
      $start         = $data['start'];
      $postData      = $data['post_data'];
      $message       = $data['message'];
      $style         = $data['style'];
      $limit         = $data['limit'];
      $urlSearch     = Dispatcher::Instance()->GetUrl(
         'finansi_sppu',
         'Sppu',
         'view',
         'html'
      );

      $urlPopupUnit  = Dispatcher::Instance()->GetUrl(
         'finansi_sppu',
         'popupUnitKerja',
         'view',
         'html'
      );

      $urlInputSppu  = Dispatcher::Instance()->GetUrl(
         'finansi_sppu',
         'inputSppu',
         'view',
         'html'
      ).'&'.$queryString;

      $urlListSppu   = Dispatcher::Instance()->GetUrl(
         'finansi_sppu',
         'ListSppu',
         'view',
         'html'
      );

      $this->mrTemplate->AddVar('data_unit', 'LEVEL', strtoupper($unitKerja['status']));
      $this->mrTemplate->AddVar('data_unit', 'UNIT_ID', $requestData['unit_id']);
      $this->mrTemplate->AddVar('data_unit', 'UNIT_NAMA', $requestData['unit_nama']);
      $this->mrTemplate->AddVar('data_unit', 'URL_POPUP_UNIT', $urlPopupUnit);
      $this->mrTemplate->AddVar('content', 'URL_SEARCH', $urlSearch);
      $this->mrTemplate->AddVars('content', $program, 'PROGRAM_');
      $this->mrTemplate->AddVars('content', $requestData);
      $this->mrTemplate->AddVar('content', 'URL_ADD', $urlInputSppu);
      $this->mrTemplate->AddVar('content', 'URL_LIST_SPPU', $urlListSppu);

      if($message AND !is_null($message)){
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $message);
         $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $style);
      }

      if (empty($dataList)) {
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
      } else {
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');

         $program          = '';
         $kegiatan         = '';
         $subKegiatan      = '';
         $index            = 0;
         $dataRealisasi    = array();
         $dataGrid         = array();

         for ($i=0; $i < count($dataList);) {
            if((int)$dataList[$i]['program_id'] === (int)$program && 
                (int)$dataList[$i]['kegiatan_id'] === (int)$kegiatan && 
                (int)$dataList[$i]['sub_kegiatan_id'] === (int)$subKegiatan){
               $kodeSistemProgram            = $program;
               $kodeSistemKegiatan           = $program.'.'.$kegiatan;
               $dataRealisasi[$kodeSistemProgram]['nominal_usulan']  += $dataList[$i]['nominal_detail_belanja_usulan'];
               $dataRealisasi[$kodeSistemProgram]['nominal_setuju']  += $dataList[$i]['nominal_detail_belanja_setuju'];

               $dataRealisasi[$kodeSistemKegiatan]['nominal_usulan'] += $dataList[$i]['nominal_detail_belanja_usulan'];
               $dataRealisasi[$kodeSistemKegiatan]['nominal_setuju'] += $dataList[$i]['nominal_detail_belanja_setuju'];

               //$dataGrid[$index]['nomor']    = $start;
               $dataGrid[$index]['id']       = $dataList[$i]['id'];
               //$dataGrid[$index]['tanggal']  = date('Y-m-d', strtotime($dataList[$i]['tanggal']));
               $dataGrid[$index]['kode']           = $dataList[$i]['kode_akun'];
               $dataGrid[$index]['nama']           = $dataList[$i]['detail_belanja_nama'];
               $dataGrid[$index]['nominal_usulan'] = $dataList[$i]['nominal_detail_belanja_usulan'];
               $dataGrid[$index]['nominal_setuju'] = $dataList[$i]['nominal_detail_belanja_setuju'];
               //$dataGrid[$index]['status']         = strtoupper($dataList[$i]['status']);
               //$dataGrid[$index]['spp']            = $dataList[$i]['spp'];
               //$dataGrid[$index]['spp_id']         = $dataList[$i]['spp_id'];
               //$dataGrid[$index]['spm']            = $dataList[$i]['spm'];
               //$dataGrid[$index]['spm_id']         = $dataList[$i]['spm_id'];
               $dataGrid[$index]['tipe']           = 'detail';
               $dataGrid[$index]['class_name']     = ($start - 1 ) % 2 <> 0 ? 'table-common-even' : '';
               $dataGrid[$index]['row_style']      = '';
               $i++;
            }elseif((int)$dataList[$i]['program_id'] === (int)$program && 
                (int)$dataList[$i]['kegiatan_id'] === (int)$kegiatan  &&
                (int)$dataList[$i]['sub_kegiatan_id'] !== (int)$subKegiatan ){
               $subKegiatan         = (int)$dataList[$i]['sub_kegiatan_id'];
               $kodeSistemProgram            = $program;
               $kodeSistemKegiatan           = $program.'.'.$kegiatan;
               $kodeSistemSubKegiatan           = $program.'.'.$kegiatan.'.'.$subKegiatan;
               $dataRealisasi[$kodeSistemProgram]['nominal_usulan']  += $dataList[$i]['nominal_usulan'];
               $dataRealisasi[$kodeSistemProgram]['nominal_setuju']  += $dataList[$i]['nominal_setuju'];

               $dataRealisasi[$kodeSistemKegiatan]['nominal_usulan'] += $dataList[$i]['nominal_usulan'];
               $dataRealisasi[$kodeSistemKegiatan]['nominal_setuju'] += $dataList[$i]['nominal_setuju'];

               $dataGrid[$index]['nomor']    = $start;
               $dataGrid[$index]['id']       = $dataList[$i]['id'];
               $dataGrid[$index]['tanggal']  = date('Y-m-d', strtotime($dataList[$i]['tanggal']));
               $dataGrid[$index]['kode']           = $dataList[$i]['sub_kegiatan_kode'];
               $dataGrid[$index]['nama']           = $dataList[$i]['sub_kegiatan_nama'];
               $dataGrid[$index]['lk']             = $dataList[$i]['lingkup_komponen'];
               $dataGrid[$index]['unit_nama']     = $dataList[$i]['unit_nama'];
               $dataGrid[$index]['nominal_usulan'] = $dataList[$i]['nominal_usulan'];
               $dataGrid[$index]['nominal_setuju'] = $dataList[$i]['nominal_setuju'];
               $dataGrid[$index]['status']         = strtoupper($dataList[$i]['status']);
               $dataGrid[$index]['spp']            = $dataList[$i]['spp'];
               $dataGrid[$index]['spp_id']         = $dataList[$i]['spp_id'];
               $dataGrid[$index]['spm']            = $dataList[$i]['spm'];
               $dataGrid[$index]['spm_id']         = $dataList[$i]['spm_id'];
               $dataGrid[$index]['tipe']           = 'sub_kegiatan';
               $dataGrid[$index]['class_name']     = $start % 2 <> 0 ? 'table-common-even' : '';
               //$dataGrid[$index]['class_name']     = 'table-common-even';
               $dataGrid[$index]['row_style']      = '';
               $start++;
            }elseif((int)$dataList[$i]['program_id'] === (int)$program && (int)$dataList[$i]['kegiatan_id'] !== (int)$kegiatan){
               $kegiatan         = (int)$dataList[$i]['kegiatan_id'];
               $kodeSistem       = $program.'.'.$kegiatan;
               $dataRealisasi[$kodeSistem]['nominal_usulan']   = 0;
               $dataRealisasi[$kodeSistem]['nominal_setuju']   = 0;

               $dataGrid[$index]['id']       = $dataList[$i]['kegiatan_id'];
               $dataGrid[$index]['kode']     = $dataList[$i]['kegiatan_kode'];
               $dataGrid[$index]['nama']     = $dataList[$i]['kegiatan_nama'];     
               $dataGrid[$index]['kode_sistem'] = $kodeSistem;
               $dataGrid[$index]['tipe']        = 'kegiatan';
               $dataGrid[$index]['class_name']  = 'table-common-even2';
               $dataGrid[$index]['row_style']   = '';
            }else{
               $program          = (int)$dataList[$i]['program_id'];
               $kodeSistem       = $program;
               $dataRealisasi[$kodeSistem]['nominal_usulan']   = 0;
               $dataRealisasi[$kodeSistem]['nominal_setuju']   = 0;

               $dataGrid[$index]['id']       = $dataList[$i]['program_id'];
               $dataGrid[$index]['kode']     = $dataList[$i]['program_kode'];
               $dataGrid[$index]['nama']     = $dataList[$i]['program_nama'];
               $dataGrid[$index]['kode_sistem'] = $kodeSistem;
               $dataGrid[$index]['tipe']        = 'program';
               $dataGrid[$index]['class_name']  = 'table-common-even1';
               $dataGrid[$index]['row_style']   = 'font-weight: bold;';
            }
            $index++;
         }

         foreach ($dataGrid as $list) {
            $this->mrTemplate->clearTemplate('data_checkbox');
            $this->mrTemplate->SetAttribute('data_checkbox', 'visibility', 'hidden');

            // cek tahun anggaran aktif
            if($requestData['active'] == 'T'){
               $this->mrTemplate->clearTemplate('data_checkbox');
               $message    = 'Periode Tahun berada pada status tidak aktif, Periode Tahun aktif saat ini <b>'.$periodeTahun[0]['name'].'</b>. <br />Data tidak bisa digunakan untuk membuat SPPU';
               $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
               $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $message);
               $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', 'notebox-warning');
               $this->mrTemplate->AddVar('data_checkbox', 'DISABLED', 'disabled');
               $this->mrTemplate->AddVar('content', 'DISPLAY_BUTTON', 'display: none;');
            }

            switch (strtoupper($list['tipe'])) {
               case 'PROGRAM':
                   $this->mrTemplate->SetAttribute('content_deskripsi', 'visibility', 'hidden');
                  $list['nominal_usulan']    = number_format($dataRealisasi[$list['kode_sistem']]['nominal_usulan'], 0, ',','.');
                  $list['nominal_setuju']    = number_format($dataRealisasi[$list['kode_sistem']]['nominal_setuju'], 0, ',','.');
                  break;
               case 'KEGIATAN':
                   $this->mrTemplate->SetAttribute('content_deskripsi', 'visibility', 'hidden');
                  $list['nominal_usulan']    = number_format($dataRealisasi[$list['kode_sistem']]['nominal_usulan'], 0, ',','.');
                  $list['nominal_setuju']    = number_format($dataRealisasi[$list['kode_sistem']]['nominal_setuju'], 0, ',','.');
                  break;
               case 'DETAIL':
                   $this->mrTemplate->SetAttribute('content_deskripsi', 'visibility', 'hidden');
                  $list['nominal_usulan']    = number_format($list['nominal_usulan'], 0, ',','.');
                  $list['nominal_setuju']    = number_format($list['nominal_setuju'], 0, ',','.');
                  break;   
               case 'SUB_KEGIATAN':
                  $this->mrTemplate->SetAttribute('content_deskripsi', 'visibility', 'visible');
                  $list['nominal_usulan']    = number_format($list['nominal_usulan'], 0, ',','.');
                  $list['nominal_setuju']    = number_format($list['nominal_setuju'], 0, ',','.');
                  $this->mrTemplate->AddVar('content_deskripsi', 'LK',$list['lk']);
                  $this->mrTemplate->SetAttribute('data_checkbox', 'visibility', 'show');
                  $this->mrTemplate->AddVar('data_checkbox', 'ID', $list['id']);
                  break;
            }
            $this->mrTemplate->AddVars('data_list', $list);
            $this->mrTemplate->parseTemplate('data_list', 'a');
         }
      }
   }
}
?>