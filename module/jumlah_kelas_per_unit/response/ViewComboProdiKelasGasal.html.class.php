<?php

require_once GTFWConfiguration::GetValue('application', 'docroot') .
        'module/jumlah_kelas_per_unit/business/GetDataAkademik.class.php';

class ViewComboProdiKelasGasal extends HtmlResponse {

    public function TemplateModule() {
        $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') .
                'module/jumlah_kelas_per_unit/template');
        $this->SetTemplateFile('combo_prodi_kelas_gasal.html');
    }

    public function ProcessRequest() {

        $Obj = new GetDataAkademik;

        $prodiKelasGasal = $Obj->GetJumlahKelasPerProdi($_REQUEST['dataId']);

        Messenger::Instance()->SendToComponent(
                'combobox', 'Combobox', 'view', 'html', 'prodi_kelas_gasal', array(
            'prodi_kelas_gasal',
            $prodiKelasGasal,
            $prodiSelected,
            'false',
            ''
                ), Messenger::CurrentRequest);

        return $return;
    }

    public function ParseTemplate($data = NULL) {
        
    }

}

?>