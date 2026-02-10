<?php

require_once GTFWConfiguration::GetValue('application', 'docroot') .
        'module/lap_neraca_lajur/business/LapNeracaLajur.class.php';


class ViewLapNeracaLajur extends HtmlResponse {

    /**
     * untuk menginstanskan class database object
     */
    protected $mDBObj;
    protected $mModulName;
    protected $mData;

    public function __construct() {
        parent::__construct();
        $this->mDBObj = new LapNeracaLajur();
        $this->mModulName = 'lap_neraca_lajur';
    }

    public function TemplateModule() {
        $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') .
                'module/' . $this->mModulName . '/template');
        $this->SetTemplateFile('view_lap_neraca_lajur.html');
    }

    public function ProcessRequest() {
        $_POST = (is_object($_POST) ? $_POST->AsArray() : $_POST );
        $_GET = (is_object($_GET) ? $_GET->AsArray() : $_GET);

        if (isset($_POST['btncari'])) {
             $tanggal_awal = $_POST['tanggal_awal_year'] . "-" . $_POST['tanggal_awal_mon'] . "-" . $_POST['tanggal_awal_day'];
             $tanggal_akhir = $_POST['tanggal_akhir_year'] . "-" . $_POST['tanggal_akhir_mon'] . "-" . $_POST['tanggal_akhir_day'];
        } elseif ($_GET['cari'] != "") {            
            $tanggal_awal = date('Y-m-d', strtotime(Dispatcher::Instance()->Decrypt($mObj->_GET['tgl_awal'])));
            $tanggal_akhir= date('Y-m-d', strtotime(Dispatcher::Instance()->Decrypt($mObj->_GET['tgl_akhir'])));            
            
        } else {
            $tanggal_awal = date("Y-01-01");
            $tanggal_akhir = date("Y-m-d");
        }
        $arr_tahun_anggaran = $this->mDBObj->GetComboTahunAnggaran();
        Messenger::Instance()->SendToComponent(
                'combobox', 'Combobox', 'view', 'html', 'tahun_anggaran', array(
            'tahun_anggaran',
            $arr_tahun_anggaran,
            $this->mData['tahun_anggaran'], '-',
            ' style="width:200px;" id="tahun_anggaran"'), Messenger::CurrentRequest);


        //tahun untuk combo
        $tahunTrans = $this->mDBObj->GetMinMaxThnTrans();
        Messenger::Instance()->SendToComponent(
            'tanggal', 
            'Tanggal', 
            'view', 
            'html', 
            'tanggal_awal', 
            array(
                $tanggal_awal, 
                $tahunTrans['minTahun'], 
                $tahunTrans['maxTahun'],
                false,
                false,
                false
            ), 
            Messenger::CurrentRequest
        );

        Messenger::Instance()->SendToComponent(
            'tanggal', 
            'Tanggal', 
            'view', 
            'html', 
            'tanggal_akhir', 
            array(
                $tanggal_akhir, 
                $tahunTrans['minTahun'], 
                $tahunTrans['maxTahun'],
                false,
                false,
                false
            ), 
            Messenger::CurrentRequest
        );


        //view
        $return['get_data'] = $this->mDBObj->GetDataLaporan($tanggal_awal, $tanggal_akhir);

        //tanggal
        $return['tgl_awal'] = $tanggal_awal;
        $return['tgl_akhir'] = $tanggal_akhir;
        return $return;
    }

    public function ParseTemplate($data = NULL) {

        $this->mrTemplate->AddVar('content', 'URL_SEARCH', Dispatcher::Instance()->GetUrl(
                        $this->mModulName, 'lapNeracaLajur', 'view', 'html'));

        $this->mrTemplate->AddVar('content', 'URL_RESET', Dispatcher::Instance()->GetUrl(
                        $this->mModulName, 'lapNeracaLajur', 'view', 'html'));

        $this->mrTemplate->AddVar('content', 'URL_CETAK', Dispatcher::Instance()->GetUrl($this->mModulName, 'CetakLapNeracaLajur', 'view', 'html') .
                '&tgl_awal=' . Dispatcher::Instance()->Encrypt($data['tgl_awal']) .
                '&tgl_akhir=' . Dispatcher::Instance()->Encrypt($data['tgl_akhir']));

        $this->mrTemplate->AddVar('content', 'URL_EXCEL', Dispatcher::Instance()->GetUrl($this->mModulName, 'ExcelLapNeracaLajur', 'view', 'xlsx') .
                '&tgl_awal=' . Dispatcher::Instance()->Encrypt($data['tgl_awal']) .
                '&tgl_akhir=' . Dispatcher::Instance()->Encrypt($data['tgl_akhir']));

        $dataLaporan = $data['get_data'];
        if (empty($dataLaporan)) {
            $this->mrTemplate->AddVar('list_data', 'IS_EMPTY', 'YES');
        } else {
            $this->mrTemplate->AddVar('list_data', 'IS_EMPTY', 'NO');
            
            $totalNeracaD = 0;
            $totalNeracaK = 0;
            for ($k = 0; $k < sizeof($dataLaporan); $k++) {

                //mari kita menghitung neraca
                $nominalSaldoAwalD = $dataLaporan[$k]['saldo_awal_debet'];
                $nominalSaldoAwalK = $dataLaporan[$k]['saldo_awal_kredit'];
                $nominalDebet =  $nominalSaldoAwalD + $dataLaporan[$k]['neraca_debet'];
                $nominalKredit =  $nominalSaldoAwalK + $dataLaporan[$k]['neraca_kredit'];
                if($dataLaporan[$k]['debet_positif'] == 0 ) {
                    $nominalKreditJ = $nominalKredit - $nominalDebet;
                    $nominalDebetJ = 0;
                } else {
                    $nominalDebetJ = $nominalDebet - $nominalKredit ;
                    $nominalKreditJ = 0;
                }
                
                $dataLaporan[$k]['fneraca_debet'] = $this->mDBObj->SetFormatAngka($nominalDebetJ, 2);
                $dataLaporan[$k]['fneraca_kredit'] = $this->mDBObj->SetFormatAngka($nominalKreditJ, 2);
                $totalNeracaD += $nominalDebetJ;
                $totalNeracaK += $nominalKreditJ;
                $this->mrTemplate->AddVars('list_data_item', $dataLaporan[$k], '');
                $this->mrTemplate->parseTemplate('list_data_item', 'a');
            }
            $fTotalNeracaD =$this->mDBObj->SetFormatAngka($totalNeracaD,2) ;
            $fTotalNeracaK =$this->mDBObj->SetFormatAngka($totalNeracaK,2) ;
            $this->mrTemplate->AddVar('list_data', 'TOTAL_NERACA_DEBET',$fTotalNeracaD);
            $this->mrTemplate->AddVar('list_data', 'TOTAL_NERACA_KREDIT',$fTotalNeracaK);
        }
    }

}

?>