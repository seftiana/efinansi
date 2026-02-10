<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot').
    'module/tahun_pembukuan/business/RollbackPembukuan.class.php';


class ViewKonfirmasiRollback extends HtmlResponse{

    public function TemplateModule() {
        $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
            'module/tahun_pembukuan/template'
        );
        $this->SetTemplateFile('view_konfirmasi_rollback.html');
    }

    public function ProcessRequest(){
        $mObj = new RollbackPembukuan();
        $return['tahun_aktif'] = $mObj->GetTahunPembukuanAktif();
        $return['tahun_sebelumnya'] = $mObj->GetTahunPembukuanSebelumnya($return['tahun_aktif']['tppId']);

        return $return;
    }

    public function ParseTemplate($data = null){
        extract($data);

        $this->mrTemplate->AddVar('content', 'URL_KEMBALI',
            Dispatcher::Instance()->GetUrl(
                'tahun_pembukuan',
                'TahunPembukuan',
                'view',
                'html'
            )
        );

        $this->mrTemplate->AddVar('content', 'URL_ACTION',
            Dispatcher::Instance()->GetUrl(
                'tahun_pembukuan',
                'Rollback',
                'Do',
                'json'
            )
        );
        $yearAwal = date('Y',strtotime($tahun_aktif['tppTanggalAwal']));
        $yearAkhir = date('Y',strtotime($tahun_aktif['tppTanggalAkhir']));
        $yearAwalSebelum = date('Y',strtotime($tahun_sebelumnya['tppTanggalAwal']));
        $yearAkhirSebelum = date('Y',strtotime($tahun_sebelumnya['tppTanggalAkhir']));

        $tppAktifNama = ($yearAwal == $yearAkhir) ? $yearAwal : $yearAwal.'-'.$yearAkhir;
        $tppAktifSebelumNama = ($yearAwalSebelum == $yearAkhirSebelum) ?
            $yearAwalSebelum : $yearAwalSebelum.'-'.$yearAkhirSebelum;
        
        $this->mrTemplate->AddVar('content','TPP_AKTIF_NAMA',$tppAktifNama);
        $this->mrTemplate->AddVar('content','TPP_AKTIF_SEBELUM_NAMA',$tppAktifSebelumNama);

    }
}
