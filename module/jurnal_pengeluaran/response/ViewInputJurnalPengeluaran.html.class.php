<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/jurnal_pengeluaran/response/ProcJurnalPengeluaran.proc.class.php';

class ViewInputJurnalPengeluaran extends HtmlResponse {
   protected $data;
   protected $proc;
   function ViewInputJurnalPengeluaran() {
      $this->proc    = new ProcJurnalPengeluaran;
      $this->data    = $this->proc->getPOST();
   }

   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
         'module/jurnal_pengeluaran/template');
      $this->SetTemplateFile('view_input_jurnal_pengeluaran.html');
   }


   function ProcessRequest() {
      $this->data['action']   = 'add';
      if(isset($_GET['grp'])) {
         if(is_object($_GET['grp'])){
            $grp  = $_GET['grp']->mrVariable;
         }else{
            $grp  = $_GET['grp'];
         }

         $jurnal['id']  = Dispatcher::Instance()->Decrypt($grp);
         $jurnal['grp'] = $grp;

         $datadetail    = $this->proc->db->GetDataById($jurnal['id']);
         //debug($datadetail);
         $return['disable']   = 'disabled="true"';

      } elseif(isset($_GET['cari'])) {
         //sebenernya ini adalah hasil parsingan dari componen delete kalo batal cuma bisa mengrimkan id lewat variable id
         if(is_object($_GET['cari'])){
            $grp     = $_GET['cari']->mrVariable;
         }else{
            $grp     = $_GET['cari'];
         }

         $jurnal['id']  = Dispatcher::Instance()->Decrypt($grp);
         $jurnal['grp'] = $grp;
         $datadetail    = $this->proc->db->GetDataById($jurnal['id']);
      }

      //parsing mana data kredit mana data debet untuk ditampilkan di view
      if(isset($datadetail)){
         $this->data['referensi_id']            = $datadetail[0]['referensi_id'];
         $this->data['referensi_nama']          = $datadetail[0]['referensi_nama'];
         $this->data['referensi_nilai']         = $datadetail[0]['referensi_nilai'];
         $this->data['referensi_tanggal']       = $datadetail[0]['referensi_tanggal'];
         $this->data['pembukuan_referensi_id']  = $datadetail[0]['pembukuan_referensi_id'];

         foreach($datadetail as $val){
           //ini adalah data kredit
           if($val['detail_status']=='K') {
               $this->data['kredit']['coa_id'] = $val['coa_id'];
               $this->data['kredit']['coa_nama'] = $val['coa_nama'];
               $this->data['kredit']['nilai'] = $val['detail_nilai'];
               $this->data['kredit']['detail_id'] = $val['detail_id'];
           } else {
               $akun['id'] = $val['coa_id'];
               $akun['detail_id'] = $val['detail_id'];
               $akun['kode'] = $val['coa_kode'];
               $akun['nama'] = $val['coa_nama'];
               $akun['keterangan'] = $val['detail_keterangan'];
               $akun['nilai'] = $val['detail_nilai'];
               $this->data['debet']['tambah_edit'][] = $akun;
               unset($akun);
           }
         }
         $this->data['action']='update';
      }

      $arr_coa    = $this->proc->db->GetComboCoa('kredit');
      $coaid      = isset($this->data['kredit']['coa_id']) ? $this->data['kredit']['coa_id'] : '';

      Messenger::Instance()->SendToComponent(
         'combobox',
         'Combobox',
         'view',
         'html',
         'data[kredit][coa_id]',
         array(
            'data[kredit][coa_id]',
            $arr_coa,
            $coaid,
            'kosong',
            ' style="width:200px;" '
         ), Messenger::CurrentRequest);

      $arr_bentuk_transaksi   = $this->proc->db->GetBentukTransaksi();
      Messenger::Instance()->SendToComponent(
         'combobox',
         'Combobox',
         'view',
         'html',
         'data[bentuk_transaksi]',
         array(
            'data[bentuk_transaksi]',
            $arr_bentuk_transaksi,
            '',
            '',
            ' style="width:150px;" '
         ), Messenger::CurrentRequest);

      $list_status      = array(array('id'=>'Y','name'=>'Ya'),array('id'=>'T','name'=>'Tidak'));
      Messenger::Instance()->SendToComponent(
         'combobox',
         'Combobox',
         'view',
         'html',
         'data[status_iskas]',
         array(
            'data[status_iskas]',
            $list_status,
            '',
            '',
            ''
         ), Messenger::CurrentRequest);

      //start menghandle pesan yang diparsing
      $tmp        = $this->proc->parsingUrl(__FILE__);
      if(isset($tmp['msg'])) {
         $this->data       = $tmp['data'];
         $return['msg']    = $tmp['msg'];
      }
      //end handle
      //echo "<pre>";
      // print_r($this->data);
      // echo "</pre>";


      if(isset($dataGrid)){
         $this->data['kredit']['datalist']   = $dataGrid['kredit'];
         $this->data['debet']['datalist']    = $dataGrid['debet'];
      }

      return $return;
   }

   function ParseTemplate($data = NULL) {
      if ($this->data['action'] == 'add'){
         $action     = $this->proc->moduleAdd;
         $this->mrTemplate->AddVar('content', 'MODE', 'Tambah');
      }else{
         $action     = $this->proc->moduleUpdate;
         $this->mrTemplate->AddVar('content', 'MODE', 'Ubah');
      }
      $urlAction     = Dispatcher::Instance()->GetUrl(
         $this->proc->moduleName,
         $action,
         'do',
         'html'
      );
      $urlListJurnal    =  Dispatcher::Instance()->GetUrl(
         $this->proc->moduleName,
         'jurnalPengeluaran',
         'view',
         'html'
      );

      $urlPopupCoa      = Dispatcher::Instance()->GetUrl(
         $this->proc->moduleName,
         'coa',
         'popup',
         'html'
      );

      $urlPopupReferensi = Dispatcher::Instance()->GetUrl(
         $this->proc->moduleName,
         'referensiTransaksi',
         'popup',
         'html'
      );

      $urlPopupCoaKredit   = Dispatcher::Instance()->GetUrl(
         $this->proc->moduleName,
         'PopupCoaKredit',
         'view',
         'html'
      );
      // mbuat json untuk rekening yang di delete (preserve data)
      $tmp     = array();
      if (isset($this->data['akun_list_delete'])) {
         foreach (array_keys($this->data['akun_list_delete']['id']) as $key){
            $tmp[$key][]   = $this->data['akun_list_delete']['id'][$key];
            $tmp[$key][]   = $this->data['akun_list_delete']['nama'][$key];
         }
      }
      $this->mrTemplate->AddVar('content', 'JSON_AKUN_LIST_DELETE',json_encode($tmp));
      // --------
      if (GTFWConfiguration::GetValue('application', 'auto_approve')){
         $this->mrTemplate->SetAttribute('approval', 'visibility', 'visible');
      }
      $this->mrTemplate->AddVar('content', 'URL_ACTION', $urlAction);
      $this->mrTemplate->AddVar('content', 'URL_LIST_JURNAL_PENERIMAAN', $urlListJurnal );
      $this->mrTemplate->AddVar('content', 'POPUP_COA', $urlPopupCoa);
      $this->mrTemplate->AddVar('content', 'POPUP_REFERENSI', $urlPopupReferensi);
      $this->mrTemplate->AddVar('content', 'POPUP_COA_KREDIT', $urlPopupCoaKredit);

      $this->mrTemplate->AddVar('content', 'REFERENSI_ID', $this->data['referensi_id'] );
      $this->mrTemplate->AddVar('content', 'REFERENSI_NAMA', $this->data['referensi_nama'] );
      $this->mrTemplate->AddVar('content', 'REFERENSI_KETERANGAN', ($this->data['referensi_keterangan']) ? $this->data['referensi_keterangan'] : $this->data['debet']['tambah_edit'][0]['keterangan']);
      $this->mrTemplate->AddVar('content', 'KREDIT_DETAIL_ID', $this->data['kredit']['detail_id'] );
      $this->mrTemplate->AddVar('content', 'COA_KREDIT_NAMA', $this->data['kredit']['coa_nama'] );
      $this->mrTemplate->AddVar('content', 'COA_KREDIT_ID', $this->data['kredit']['coa_id'] );
      $this->mrTemplate->AddVar('content', 'KREDIT_NILAI', $this->data['kredit']['nilai'] );
      $this->mrTemplate->AddVar('content', 'KREDIT_NILAI_VIEW', number_format($this->data['kredit']['nilai'], 2 , ',' ,'.') );
      $this->mrTemplate->AddVar('content', 'REFERENSI_TANGGAL', $this->data['referensi_tanggal'] );
      $this->mrTemplate->AddVar('content', 'PEMBUKUAN_REFERENSI_ID', $this->data['pembukuan_referensi_id'] );

      $this->mrTemplate->AddVar('content', 'DISABLE', $data['disable']);


      $this->mrTemplate->AddVar('content', 'DATA_ACTION', $this->data['action']);

      if (isset ($data['msg'])) {
            $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
            $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $data['msg']['message']);
         if($data['msg']['action']=='msg'){
            $class='notebox-done';
         }else{
            $class = 'notebox-warning';
         }

         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $data['msg']['message']);
         $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $class);
      }
      $viewsimpan = false;


      /* ===================start parsing data debet */
      #data sudah ada dalam database
      if(isset($this->data['debet']['tambah_edit']) && !empty($this->data['debet']['tambah_edit'])) {
         $this->mrTemplate->AddVar('bool-table-debet', 'HAVE_DATA', 'YES');
         $no=1;
         foreach($this->data['debet']['tambah_edit'] as $data){
            $data['nomor'] = $no;
            $no++;
            /*
            //=============start dipake componenet confirm delete ===============================
            $idDelete = Dispatcher::Instance()->Encrypt($data['skenariodetail_id'].'*'.$data['skenario_id']);
            $kirimVar = Dispatcher::Instance()->Encrypt($data['skenario_id']);
            $urlAccept = $this->proc->moduleName.'|deleteSkenarioDetail|do|html-cari-'.$kirimVar;
            $urlReturn = $this->proc->moduleName.'|inputSkenario|view|html-cari-'.$kirimVar;
            $label = 'Delete Jurnal Pengeluaran';
            $dataName = $data['nama'];
            $data['url_delete'] = Dispatcher::Instance()->GetUrl('confirm', 'confirmDelete', 'do', 'html').'&urlDelete='. $urlAccept.'&urlReturn='.$urlReturn.'&id='.$idDelete.'&label='.$label.'&dataName='.$dataName;
            //=============end  dipake componenet confirm delete ===============================
            */
            $this->mrTemplate->AddVars('table-debet', $data, 'DEBET_');
            $this->mrTemplate->parseTemplate('table-debet', 'a');
        }
        $viewsimpan = true;
      }

      #merupakan data yang belum dimasukan dalam data base, karena terjadi error pada sesuatu.
      if(isset($this->data['debet']['tambah']) && !empty($this->data['debet']['tambah'])) {
         $this->mrTemplate->AddVar('bool-table-debet-tambah', 'HAVE_DATA', 'YES');
         foreach($this->data['debet']['tambah'] as $data){
           $data['tr_id'] = 'tr-debet-'.$data['id'].rand(1, 20);
           $data['url_delete'] ='<a class="dest_subcontent-element" href="javascript:void(0)" onclick="javascript:hapusAkun(\''.$data['tr_id'].'\')" title="Membatalkan Menambah Akun debet">Batal</a>';
            $data['tr_id'] = 'id="'.$data['tr_id'].'"';
            $this->mrTemplate->AddVars('table-debet-tambah', $data, 'DEBET_');
            $this->mrTemplate->parseTemplate('table-debet-tambah', 'a');
        }
        $viewsimpan = true;
      }

      /* ====================end parsing data debet */
      if(!$viewsimpan) {
         $this->mrTemplate->AddVar('content', 'DIV_BTNSIMPAN_STYLE', 'style="display:none"');
         $this->mrTemplate->AddVar('content', 'TABLE_DEBET_STYLE', 'style="display:none"');
         $this->mrTemplate->AddVar('content', 'TABLE_KREDIT_STYLE', 'style="display:none"');
      }
   }
}
?>