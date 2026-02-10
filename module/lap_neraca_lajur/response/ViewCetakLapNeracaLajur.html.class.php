<?php

/**
 * 
 * class ViewCetakLapNeracaLajur
 * @package lap_neraca_lajur
 * @todo Untuk menampilkan data
 * @subpackage response
 * @since 11 April 2013
 * @SystemAnalyst Nanang Ruswianto <nanang@gamatechno.com>
 * @author noor hadi <noor.hadi@gamatechno.com>
 * @copyright 2013 Gamatechno Indonesia
 * 
 */
require_once GTFWConfiguration::GetValue('application', 'docroot') .
        'module/lap_neraca_lajur/business/LapNeracaLajur.class.php';

require_once GTFWConfiguration::GetValue('application', 'docroot') .
        'module/user_unit_kerja/business/UserUnitKerja.class.php';

class ViewCetakLapNeracaLajur extends HtmlResponse {

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
        $this->SetTemplateFile('view_cetak_lap_neraca_lajur.html');
    }

    public function TemplateBase() {
        $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') .
                'main/template/');
        $this->SetTemplateFile('document-print.html');
        $this->SetTemplateFile('layout-common-print.html');
    }

    public function ProcessRequest() {

        $tgl_awal = Dispatcher::Instance()->Decrypt($_GET['tgl_awal']);
        $tgl_akhir = Dispatcher::Instance()->Decrypt($_GET['tgl_akhir']);


        $return['get_data'] = $this->mDBObj->GetDataLaporan($tgl_awal, $tgl_akhir);
        
        $return['tgl_awal'] = $tgl_awal;
        $return['tgl_akhir'] = $tgl_akhir;
        return $return;
    }

    public function ParseTemplate($data = NULL) {

        $this->mrTemplate->AddVar('content', 'TANGGAL_AWAL', ($data['tgl_awal']));
        $this->mrTemplate->AddVar('content', 'TANGGAL_AKHIR', ($data['tgl_akhir']));


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
