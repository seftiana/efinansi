<?php
/**
* Module : history_apbnp
* FileInclude : MovementAnggaran.class.php
* Class : ViewListKegiatanAnggaran
* Extends : HtmlResponse
*/
    require_once GTFWConfiguration::GetValue('application','docroot').
    'module/history_apbnp/business/AppKegiatan.class.php';

    class ViewListKegiatanAnggaranTujuan extends HtmlResponse{
        public $objKegiatan;
        public $POST;
        public $data;
        public $Pesan;
        public $css;
        
        public function __construct()
        {
            $this->objKegiatan  = new AppKegiatan();
            $this->POST         = $_POST->AsArray();
        }
        
        function TemplateModule(){
            $this->setTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
            'module/history_apbnp/template/');
            $this->setTemplateFile('view_list_kegiatan_anggaran_tujuan.html');
        }
        
        function TemplateBase() {
            $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 
	        'main/template/');
            $this->SetTemplateFile('document-common-popup.html');
            $this->SetTemplateFile('layout-common-popup.html');
        }
        
        function ProcessRequest(){
            //$mak_id             = Dispatcher::Instance()->Decrypt($_GET['mak_id']);
            $unit_id            = Dispatcher::Instance()->Decrypt($_GET['unit_id']);
            //var_dump($unit_id);
            //if(is_object($unit_id)) {
            //    $unit_id = $unit_id->mrVariable;    
            //}
            
            //echo 'MAK ID '.$mak_id;
            $tahunPeriodeArray      = $this->objKegiatan->GetTahunAnggaran(); // data tahun periode
            
            // data tahun periode yang aktif sekarang
            $tahunPeriodeSelected   = $this->objKegiatan->GetTahunAnggaranIsAktif();
            $months              = $this->objKegiatan->indonesianMonthCombo;

            Messenger::Instance()->SendToComponent('combobox', 'Combobox', 'view', 'html', 'tahun_periode',
            array('tahun_periode', $tahunPeriodeArray, $tahunPeriodeSelected, '', 
            'id="tahun_periode" style="width:250px;"'),
            Messenger::CurrentRequest);
            
            if(!empty($this->POST)) 
            {
			    $kegiatanref    = $this->POST['kegiatanref'];
			    $kode           = $this->POST['kode'];
                $bulan           = $this->POST['bulan'];
		    } 
		    elseif(isset($_GET['cari'])) 
		    {
			    $kegiatanref    = Dispatcher::Instance()->Decrypt($_GET['kegiatanref']);
			    $kode           = Dispatcher::Instance()->Decrypt($_GET['kode']);
                $bulan           = Dispatcher::Instance()->Decrypt($_GET['bulan']);
		    } 
		    else 
		    {
			    $kegiatanref    = "";
			    $kode           = "";
                $bulan          = "";
		    }
		    
		    $itemViewed = 20;
		    $currPage   = 1;
		    $startRec   = 0 ;
		    if(isset($_GET['page'])) {
			    $currPage = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
			    $startRec =($currPage-1) * $itemViewed;
		    }
		    
		    $dataKegiatanRef = $this->objKegiatan->getDataKegiatanRef($startRec, $itemViewed, $kegiatanref,$kode,$bulan, $unit_id);
		    $totalData  = $this->objKegiatan->GetCountDataKegiatanRef();
			
		    $url        = Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule, 
		                  Dispatcher::Instance()->mSubModule, Dispatcher::Instance()->mAction, 
		                  Dispatcher::Instance()->mType . '&subprogramId=' . $this->encSubProgramId . 
		                  '&kegiatanref=' . Dispatcher::Instance()->Encrypt($kegiatanref) .		                  
		                  '&unit_id='.Dispatcher::Instance()->Encrypt($unit_id).
		                  '&kode=' . Dispatcher::Instance()->Encrypt($kode) . 
                          '&bulan=' . Dispatcher::Instance()->Encrypt($bulan) . 
		                  '&cari=' . Dispatcher::Instance()->Encrypt(1));
		    $dest       = "popup-subcontent";
		    Messenger::Instance()->SendToComponent('paging', 'Paging', 'view', 'html', 'paging_top', 
		    array($itemViewed,$totalData, $url, $currPage, $dest), Messenger::CurrentRequest);
		    
		    $msg            = Messenger::Instance()->Receive(__FILE__);
		    $this->Pesan    = $msg[0][1];
		    $this->css      = $msg[0][2];
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
            $bulan,
            true,
            'id="cmb_bulan"'
         ),
         Messenger::CurrentRequest
      );
      		    
		    $komponen_anggaran          = $this->objKegiatan->GetKomponenAnggaranTujuan($dataKegiatanRef);
		    $return['json']['komponen_anggaran'] = json_encode($komponen_anggaran);
            $return['dataKegiatanRef']  = $dataKegiatanRef;
		    $return['start']            = $startRec+1;
		    $return['search']['kegiatanref']    = $kegiatanref;
		    $return['search']['kode']           = $kode;
		    $return['unit_id']        = $unit_id;              
            $return['nama_bulan']    = $this->objKegiatan->indonesianMonth;
		    return $return;
		    
        }
        
        function ParseTemplate($data = null){
            $search     = $data['search'];
            $namaBulan     = $data['nama_bulan'];
            $url_search = Dispatcher::Instance()->GetUrl('history_apbnp', 'ListKegiatanAnggaranTujuan', 'view', 'html').
                           '&unit_id=' . $data['unit_id'];
                          //.'&mak_id='.Dispatcher::Instance()->Decrypt($_GET['mak_id']);
                          
		    $this->mrTemplate->AddVar('content', 'KEGIATANREF', $search['kegiatanref']);
		    $this->mrTemplate->AddVar('content', 'KODE', $search['kode']);
		    $this->mrTemplate->AddVar('content', 'URL_SEARCH', $url_search);
		    
		    $this->mrTemplate->AddVars('content', $data['json'], 'JSON_');
            
            if($this->Pesan) {
			    $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
			    $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->Pesan);
			    $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $this->css);
		    }
		    
		    if (empty($data['dataKegiatanRef'])) 
		    {
			    $this->mrTemplate->AddVar('data_kegiatanref', 'KEGIATANREF_EMPTY', 'YES');
		    } 
		    else 
		    {
			    $this->mrTemplate->AddVar('data_kegiatanref', 'KEGIATANREF_EMPTY', 'NO');
			    $dataKegiatanRef = $data['dataKegiatanRef'];
			
                $oldProgram = null;
                $oldSubProgram = null;
                
                for ($i=0; $i<sizeof($dataKegiatanRef); $i++)
                {
				    $no                             = $i+$data['start'];
				    $dataKegiatanRef[$i]['number']  = $no;

                    if ($dataKegiatanRef[$i]['programId'] != $oldProgram)
                    {
                        $tmp = array();
                        $tmp['class_name']  = 'table-common-even1';
                        $tmp['kode']        = $dataKegiatanRef[$i]['programNomor'];
                        $tmp['nama']        = $dataKegiatanRef[$i]['programNama'];

                        $this->mrTemplate->AddVars('data_kegiatanref_item', $tmp, 'KEGIATANREF_');
                        $this->mrTemplate->SetAttribute('select_button', 'visibility', 'hidden');
                        $this->mrTemplate->parseTemplate('data_kegiatanref_item', 'a');
                        $this->mrTemplate->SetAttribute('select_button', 'visibility', 'visible');
                    }

                    if ($dataKegiatanRef[$i]['subprogId'] != $oldSubProgram)
                    {
                       $tmp = array();
                       $tmp['class_name']   = 'table-common-even';
                       $tmp['kode']         = $dataKegiatanRef[$i]['subprogNomor'];
                       $tmp['nama']         = $dataKegiatanRef[$i]['subprogNama'];

                       $this->mrTemplate->AddVars('data_kegiatanref_item', $tmp, 'KEGIATANREF_');
                       $this->mrTemplate->SetAttribute('select_button', 'visibility', 'hidden');
                       $this->mrTemplate->parseTemplate('data_kegiatanref_item', 'a');
                       $this->mrTemplate->SetAttribute('select_button', 'visibility', 'visible');
                    }

                    $dataKegiatanRef[$i]['total_realisasi']     = $dataKegiatanRef[$i]['realisasi_nominal'] + $dataKegiatanRef[$i]['realisasi_pencairan'];
                    //jika total anggaran sudah direalisasikan semua maka tidak dapat menyusun FPA kembali
                    //if($dataKegiatanRef[$i]['total_realisasi'] == $dataKegiatanRef[$i]['total_anggaran']){
                    //    $this->mrTemplate->SetAttribute('select_button', 'visibility', 'hidden');                        
                   // } else {
                        $this->mrTemplate->SetAttribute('select_button', 'visibility', 'visible');
                    ///}
                    
                    $dataKegiatanRef[$i]['total_sisa'] = number_format(($dataKegiatanRef[$i]['total_anggaran'] - $dataKegiatanRef[$i]['total_realisasi']), 0, ',', '.');
                    $dataKegiatanRef[$i]['realisasi_nominal']   = number_format($dataKegiatanRef[$i]['realisasi_nominal'], 0, ',', '.');
                    $dataKegiatanRef[$i]['total_realisasi']     = number_format($dataKegiatanRef[$i]['total_realisasi'], 0, ',', '.');
                    $dataKegiatanRef[$i]['total_anggaran']      = number_format($dataKegiatanRef[$i]['total_anggaran'], 0, ',', '.');
                    $dataKegiatanRef[$i]['realisasi_pencairan'] = number_format($dataKegiatanRef[$i]['realisasi_pencairan'], 0, ',', '.');
                    $dataKegiatanRef[$i]['deskripsi'] = '<br /><br /><i>('.(($dataKegiatanRef[$i]['deskripsi']!= '') ? $dataKegiatanRef[$i]['deskripsi'] :'-') .')</i>';
                    
                    $dataKegiatanRef[$i]['bulan_nama']          = $namaBulan[$dataKegiatanRef[$i]['bulan']];
                    
				    $this->mrTemplate->AddVars('data_kegiatanref_item', $dataKegiatanRef[$i], 'KEGIATANREF_');
				    $this->mrTemplate->AddVars('select_button', $dataKegiatanRef[$i], 'KEGIATANREF_');
				    $this->mrTemplate->parseTemplate('data_kegiatanref_item', 'a');
			    }
		    }
        }
    }
?>