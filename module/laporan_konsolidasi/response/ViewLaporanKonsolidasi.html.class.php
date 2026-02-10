<?php
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'module/laporan_konsolidasi/business/AppLaporanKonsolidasi.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'main/function/date.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'main/function/number_format.class.php';

class ViewLaporanKonsolidasi extends HtmlResponse {
    protected $mObj;

    function TemplateModule() {
        $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot')
            . 'module/laporan_konsolidasi/template');
        $this->SetTemplateFile('view_laporan_konsolidasi.html');
    }

    function ProcessRequest() {
    }

    function ParseTemplate($data = NULL) {

        $this->mrTemplate->AddVar('content', 'URL_POSISI_KEUANGAN',
            Dispatcher::Instance()->GetUrl(
                'laporan_konsolidasi',
                'LaporanKonsolidasiPosisiKeuangan',
                'view',
                'html'
            )
        );

        $this->mrTemplate->AddVar('content', 'URL_AKTIVITAS',
            Dispatcher::Instance()->GetUrl(
                'laporan_konsolidasi',
                'LaporanKonsolidasiAktivitas',
                'view',
                'html'
            )
        );

        $this->mrTemplate->AddVar('content', 'URL_ARUS_KAS',
            Dispatcher::Instance()->GetUrl(
                'laporan_konsolidasi',
                'LaporanKonsolidasiArusKas',
                'view',
                'html'
            )
        );
        
    }

}

?>
