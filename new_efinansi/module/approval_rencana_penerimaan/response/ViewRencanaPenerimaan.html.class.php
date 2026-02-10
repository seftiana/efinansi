<?php

require_once GTFWConfiguration::GetValue('application', 'docroot') .
        'module/approval_rencana_penerimaan/business/AppRencanaPenerimaan.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') .
        'module/user_unit_kerja/business/UserUnitKerja.class.php';

class ViewRencanaPenerimaan extends HtmlResponse {

    protected  $Pesan;
    private $_paramsFilter;

    function TemplateModule() {
        $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') .
                'module/approval_rencana_penerimaan/template');
        $this->SetTemplateFile('view_rencana_penerimaan.html');
    }

    function ProcessRequest() {
        $_POST = $_POST->AsArray();
        $Obj = new AppRencanaPenerimaan();
        $userUnitKerjaObj = new UserUnitKerja();
        if (isset($_GET['dataId'])) {
            $rencana_penerimaanObj->DoAddRencanaPenerimaan(Dispatcher::Instance()->Decrypt($_REQUEST['dataId']));
        }

        $userId = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
        $unit = $userUnitKerjaObj->GetUnitKerjaUser($userId);
        //$role = $userUnitKerjaObj->GetRoleUser($userId);
        //print_r($role);
        //if($role['role_name'] == "Administrator") {
        if ($_POST['btncari']) {
            $this->Data['kodenama'] = $_POST['kodenama'];
            $this->Data['tahun_anggaran'] = $_POST['tahun_anggaran'];
            $this->Data['unitkerja'] = $_POST['unitkerja'];
            $this->Data['unitkerja_label'] = $_POST['unitkerja_label'];
            $this->Data['apparoval'] = $_POST['approval'];
            $unitkerja = $userUnitKerjaObj->GetUnitKerja($this->Data['unitkerja']);
            $this->Data['is_satker'] = $unitkerja['is_unit_kerja'];
        } elseif ($_GET['cari'] != "") {
            $get = $_GET->AsArray();
            $this->Data['kodenama'] = Dispatcher::Instance()->Decrypt($_GET['kodenama']);
            $this->Data['tahun_anggaran'] = Dispatcher::Instance()->Decrypt($_GET['tahun_anggaran']);
            $this->Data['unitkerja'] = Dispatcher::Instance()->Decrypt($_GET['unitkerja']);
            $this->Data['unitkerja_label'] = Dispatcher::Instance()->Decrypt($_GET['unitkerja_label']);
            $unitkerja = $userUnitKerjaObj->GetUnitKerja($this->Data['unitkerja']);
            $this->Data['is_satker'] = $unitkerja['is_unit_kerja'];
        } else {
            //print_r($this->Data);
            //$this->Data = $_POST;
            //untuk mengisi field unit kerja saat pertama kali field masih kosong
            $tahun_anggaran = $Obj->GetTahunAnggaranAktif();
            $this->Data['unitkerja'] = $unit['unit_kerja_id'];
            //$this->Data['unitkerja_label'] = $unit['satker_nama'] . "/ " . $unit['unit_kerja_nama'];
            $this->Data['unitkerja_label'] = $unit['unit_kerja_nama'];
            $this->Data['is_satker'] = $unit['is_unit_kerja'];
            $this->Data['tahun_anggaran'] = $tahun_anggaran['id'];
            //$this->Data['tahun_anggaran_label'] = $tahun_anggaran['name'];
        }

        $arr_tahun_anggaran = $Obj->GetComboTahunAnggaran();
        Messenger::Instance()->SendToComponent(
                'combobox', 'Combobox', 'view', 'html', 'tahun_anggaran', array(
            'tahun_anggaran',
            $arr_tahun_anggaran,
            $this->Data['tahun_anggaran'],
            '-',
            ' style="width:200px;" id="tahun_anggaran"'), Messenger::CurrentRequest);

        $arr_approval = $Obj->GetStatusApproval();
        Messenger::Instance()->SendToComponent(
                'combobox', 'Combobox', 'view', 'html', 'approval', array(
            'approval',
            $arr_approval,
            $this->Data['apparoval'],
            true,
            ' style="width:150px;" id="approval"'), Messenger::CurrentRequest);
        /**
          } elseif($role['role_name'] == "OperatorUnit") {
          if(isset($_POST['unitkerja_label'])) {
          //echo "asdf";
          $this->Data['kodenama'] = $_POST['kodenama'];
          $this->Data['unitkerja'] = $_POST['unitkerja'];
          $this->Data['unitkerja_label'] = $_POST['unitkerja_label'];
          } elseif(isset($_GET['unitkerja_label'])) {
          //echo "asdf";
          $get = $_GET->AsArray();
          $this->Data['kodenama'] = Dispatcher::Instance()->Decrypt($_GET['kodenama']);
          $this->Data['unitkerja'] = Dispatcher::Instance()->Decrypt($get['unitkerja']);
          $this->Data['unitkerja_label'] = Dispatcher::Instance()->Decrypt($get['unitkerja_label']);
          } else {
          $unit = $userUnitKerjaObj->GetSatkerUnitKerjaUser($userId);
          //print_r($unit);
          $this->Data['unitkerja'] = $unit['unit_kerja_id'];
          $this->Data['unitkerja_label'] = $unit['unit_kerja_nama'];
          $this->Data['is_satker'] = $unit['is_unit_kerja'];
          //$this->Data['unitkerja_label'] = Dispatcher::Instance()->Decrypt($unitkerja['satker_nama']);
          }
          $tahun_anggaran = $Obj->GetTahunAnggaranAktif();
          $this->Data['tahun_anggaran'] = $tahun_anggaran['id'];
          $this->Data['tahun_anggaran_label'] = $tahun_anggaran['name'];
          } else {
          if($_POST['btncari']) {
          $this->Data['kodenama'] = $_POST['kodenama'];
          }
          else if(isset($_GET)) {
          $this->Data['kodenama'] = Dispatcher::Instance()->Decrypt($get['kodenama']);
          }
          $unit = $userUnitKerjaObj->GetSatkerUnitKerjaUser($userId);
          $this->Data['unitkerja'] = $unit['unit_kerja_id'];
          //$this->Data['unitkerja_label'] = $unit['satker_nama'] . "/ " . $unit['unit_kerja_nama'];
          $this->Data['unitkerja_label'] =  $unit['unit_kerja_nama'];
          $this->Data['is_satker'] = $unit['is_unit_kerja'];
          $tahun_anggaran = $Obj->GetTahunAnggaranAktif();
          $this->Data['tahun_anggaran'] = $tahun_anggaran['id'];
          $this->Data['tahun_anggaran_label'] = $tahun_anggaran['name'];
          }
         */
        $totalData = $Obj->GetCountData(
                $this->Data['kodenama'], $this->Data['tahun_anggaran'], $this->Data['unitkerja'], $this->Data['apparoval']);
        $itemViewed = 20;
        $currPage = 1;
        $startRec = 0;
        if (isset($_GET['page'])) {
            $currPage = (string) $_GET['page']->StripHtmlTags()->SqlString()->Raw();
            $startRec = ($currPage - 1) * $itemViewed;
        }

        //view
        //if($this->Data['apparoval']=='all'){$this->Data['apparoval']='';}
        $data_unit = $Obj->GetDataUnitkerja(
                $this->Data['kodenama'], $this->Data['tahun_anggaran'], $this->Data['unitkerja'], $this->Data['apparoval'], $startRec, $itemViewed);
        $url = Dispatcher::Instance()->GetUrl(
                Dispatcher::Instance()->mModule, Dispatcher::Instance()->mSubModule, Dispatcher::Instance()->mAction, Dispatcher::Instance()->mType .
                '&kodenama=' . Dispatcher::Instance()->Encrypt($this->Data['kodenama']) .
                '&tahun_anggaran=' .
                Dispatcher::Instance()->Encrypt($this->Data['tahun_anggaran']) .
                '&unitkerja=' . Dispatcher::Instance()->Encrypt($this->Data['unitkerja']) .
                '&unitkerja_label=' .
                Dispatcher::Instance()->Encrypt($this->Data['unitkerja_label']) .
                '&cari=' . Dispatcher::Instance()->Encrypt(1));

        Messenger::Instance()->SendToComponent(
                'paging', 'Paging', 'view', 'html', 'paging_top', array(
            $itemViewed,
            $totalData,
            $url,
            $currPage), Messenger::CurrentRequest);


        $msg = Messenger::Instance()->Receive(__FILE__);
        $this->Pesan = $msg[0][1];
        $this->css = $msg[0][2];

        $return['role_name'] = $role['role_name'];
        $return['data'] = $data_unit;
        $return['start'] = $startRec + 1;
        $return['totalSubUnit'] = $Obj->GetTotalSubUnitKerja($unit['unit_kerja_id']);

        //registerkaan parameter filter ;
        $this->Data['page'] = $currPage;
        $this->Data['cari'] = 1;      
        SessionFilterURI::RegisterParamsFilter($this->Data);
        $this->_paramsFilter  = SessionFilterURI::GetParamsFilter();
        //end

        return $return;
    }

    function tambahNol($str = "0", $jml_char = 2) {
        while (strlen($str) < $jml_char) {
            $str = "0" . $str;
        }
        return $str;
    }

    function ParseTemplate($data = NULL) {
        //$search = $data['search'];
        $this->mrTemplate->AddVar('content', 'URL_SEARCH', Dispatcher::Instance()->GetUrl(
            'approval_rencana_penerimaan', 
            'RencanaPenerimaan', 
            'view', 
            'html'
        ));
        
        $this->mrTemplate->AddVar('content', 'URL_RESET', Dispatcher::Instance()->GetUrl(
            'approval_rencana_penerimaan', 
            'RencanaPenerimaan', 
            'view', 
            'html'
        ));

        //mulai bikin tombol delete
        $label = "Manajemen Approval Rencana Penerimaan";
        $urlDelete = Dispatcher::Instance()->GetUrl(
            'approval_rencana_penerimaan', 
            'deleteRencanaPenerimaan', 
            'do', 
            'html'
        );

        $urlReturn = Dispatcher::Instance()->GetUrl(
            'approval_rencana_penerimaan', 
            'RencanaPenerimaan', 
            'view', 
            'html') . $this->_paramsFilter;

        Messenger::Instance()->Send(
            'confirm', 
            'confirmDelete', 
            'do', 
            'html', array(
                $label,
                $urlDelete,
                $urlReturn
            ), 
            Messenger::NextRequest
        );

        $this->mrTemplate->AddVar(
            'content', 
            'URL_DELETE', 
            Dispatcher::Instance()->GetUrl('confirm', 'confirmDelete', 'do', 'html'));


        $this->mrTemplate->AddVar('content', 'KODENAMA', $this->Data['kodenama']);

        if ($data['totalSubUnit'] > 0) {
            $this->mrTemplate->AddVar('is_parent', 'IS_PARENT', 'YES');
        } else {
            $this->mrTemplate->AddVar('is_parent', 'IS_PARENT', 'NO');
        }
        /**
          if($data['role_name'] == "Administrator") {
          $this->mrTemplate->AddVar('role', 'WHOAMI', 'ADMINISTRATOR');
         */
        $this->mrTemplate->AddVar('is_parent', 'URL_POPUP_UNITKERJA', Dispatcher::Instance()->GetUrl(
                        'approval_rencana_penerimaan', 'PopupUnitkerja', 'view', 'html'));

        $this->mrTemplate->AddVar('content', 'UNITKERJA', $this->Data['unitkerja']);
        $this->mrTemplate->AddVar('is_parent', 'UNITKERJA_LABEL', $this->Data['unitkerja_label']);
        /**
          } elseif($data['role_name'] == "OperatorUnit") {
          $this->mrTemplate->AddVar('role', 'WHOAMI', 'OPERATORUNIT');
          $this->mrTemplate->AddVar('role', 'URL_POPUP_UNITKERJA',
          Dispatcher::Instance()->GetUrl(
          'approval_rencana_penerimaan',
          'PopupUnitkerja',
          'view',
          'html'));
          $this->mrTemplate->AddVar('role', 'TAHUN_ANGGARAN_LABEL', $this->Data['tahun_anggaran_label']);
          $this->mrTemplate->AddVar('role', 'UNITKERJA', $this->Data['unitkerja']);
          $this->mrTemplate->AddVar('role', 'UNITKERJA_LABEL', $this->Data['unitkerja_label']);
          } else {
          $this->mrTemplate->AddVar('role', 'WHOAMI', 'OPERATORSUBUNIT');
          $this->mrTemplate->AddVar('role', 'TAHUN_ANGGARAN_LABEL', $this->Data['tahun_anggaran_label']);
          $this->mrTemplate->AddVar('role', 'UNITKERJA_LABEL', $this->Data['unitkerja_label']);
          }
         */
        if ($this->Pesan) {
            $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
            $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->Pesan);
            $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $this->css);
        }

        if (empty($data['data'])) {
            $this->mrTemplate->AddVar('data_grid', 'IS_DATA_EMPTY', 'YES');
        } else {
            $this->mrTemplate->AddVar('data_grid', 'IS_DATA_EMPTY', 'NO');

            $total = '';
            $jumlah_total = '';
            $idrencana = '';
            $idkode = '';
            $kode = '';
            $nama = '';

            $data_list = $data['data'];
            $kode_satker = '';
            $kode_unit = '';
            $nama_satker = '';
            $nama_unit = '';
            for ($i = 0; $i < sizeof($data_list);) {

                if ($i == 0)
                    $this->mrTemplate->AddVar('content', 'FIRST_NUMBER', $number);
                if ($i == sizeof($data_list) - 1)
                    $this->mrTemplate->AddVar('content', 'LAST_NUMBER', $number);

                if (($data_list[$i]['kode_satker'] == $kode_satker) && ($data_list[$i]['kode_unit'] == $kode_unit)) {
                    if ($data_list[$i]['idrencana'] == "") {
                        $i++;
                        continue;
                    }
                    $send = $data_list[$i];
                    $send['total_penerimaan'] = number_format($data_list[$i]['total'], 0, ',', '.');
                    $send['class_name'] = "";
                    $send['nomor'] = $no;
                    $send['class_button'] = "links";
                    $url_add_edit = Dispatcher::Instance()->GetUrl(
                                    'approval_rencana_penerimaan', 'InputRencanaPenerimaan', 'view', 'html') .
                            "&dataId=" .
                            Dispatcher::Instance()->Encrypt($data_list[$i]['idrencana']);
                    $url_popup_detail = Dispatcher::Instance()->GetUrl(
                                    'approval_rencana_penerimaan', 'PopupDetailRencanaPenerimaan', 'view', 'html') .
                            "&dataId=" .
                            Dispatcher::Instance()->Encrypt($data_list[$i]['idrencana']);

                    if ($send['approval'] == 1) {
                        $send['status'] = 'Request';
                    } elseif ($send['approval'] == 2) {
                        $send['status'] = 'Approved';
                    } elseif ($send['approval'] == 3) {
                        $send['status'] = 'Cancel';
                    } else {
                        $send['status'] = 'Reject';
                    }
                    if ($data_list[$i]['approval'] != 1) {
                        $send['url_add_edit'] = '<!-- <a class="xhr dest_subcontent-element" href="' .
                                $url_add_edit .
                                '" title="Approve Rencana Penerimaan"><img src="images/icons/16/approval.gif" alt="Detail Rencana Penerimaan"/></a> -->';
                    } else {
                        $send['url_add_edit'] = '<a class="xhr dest_subcontent-element" href="' .
                                $url_add_edit .
                                '" title="Approve Rencana Penerimaan"><img src="images/icons/16/approval.gif" alt="Detail Rencana Penerimaan"/></a>';
                    }
                    $this->mrTemplate->AddVar('show_detail', 'IS_SHOW_DETAIL', 'YES');
                    $this->mrTemplate->AddVar('show_detail', 'URL_POPUP_DETAIL', $url_popup_detail);

                    $this->mrTemplate->AddVar('cekbox', 'data_number', $number);
                    $this->mrTemplate->AddVar('cekbox', 'data_idrencana', $data_list[$i]['idrencana']);
                    $this->mrTemplate->AddVar('cekbox', 'data_nama', $data_list[$i]['nama']);
                    $this->mrTemplate->AddVar('cekbox', 'IS_SHOW', 'YES');
                    $i++;
                    $no++;
                    $number++;
                } elseif ($data_list[$i]['kode_satker'] != $kode_satker && $data_list[$i]['nama_satker'] == $data_list[$i]['nama_unit']) {
                    $kode_satker = $data_list[$i]['kode_satker'];
                    $kode_unit = $data_list[$i]['kode_unit'];
                    $nama_satker = $data_list[$i]['nama_satker'];
                    $nama_unit = $data_list[$i]['nama_unit'];
                    $send['kode'] = "<b>" . $kode_unit . "</b>";
                    $send['nama'] = "<b>" . $data_list[$i]['nama_unit'] . "</b>";
                    $send['total_penerimaan'] = "<b>" . number_format(
                                    $data_list[$i]['jumlah_total'], 0, ',', '.') . "</b>";
                    if ($send['approval'] == 1) {
                        $send['status'] = '';
                    } elseif ($send['approval'] == 2) {
                        $send['status'] = '';
                    } elseif ($send['approval'] == 3) {
                        $send['status'] = '';
                    } else {
                        $send['status'] = '';
                    }
                    $send['class_name'] = "table-common-even1";
                    $send['nomor'] = "";
                    $send['class_button'] = "toolbar";
                    $url_add_edit = Dispatcher::Instance()->GetUrl(
                                    'approval_rencana_penerimaan', 'InputRencanaPenerimaan', 'view', 'html') .
                            '&tahun_anggaran=' .
                            Dispatcher::Instance()->Encrypt(
                                    $this->Data['tahun_anggaran']) .
                            '&unitkerja=' .
                            Dispatcher::Instance()->Encrypt(
                                    $data_list[$i]['idunit']) .
                            '&cari=' . Dispatcher::Instance()->Encrypt(1);

                    $send['url_add_edit'] = '<!--<a class="xhr dest_subcontent-element" href="' .
                            $url_add_edit .
                            '" title="Tambah Rencana Penerimaan"><img src="images/button-add.gif" alt="Tambah Rencana Penerimaan"/></a>-->';

                    $send['url_add_delete'] = "";
                    $send['url_popup_detail'] = '';
                    $no = 1;
                    // }
                } elseif ($data_list[$i]['kode_unit'] != $kode_unit) {
                    $kode_satker = $data_list[$i]['kode_satker'];
                    $kode_unit = $data_list[$i]['kode_unit'];
                    $nama_satker = $data_list[$i]['nama_satker'];
                    $nama_unit = $data_list[$i]['nama_unit'];
                    $send['kode'] = "<b>" . $kode_unit . "</b>";
                    $send['nama'] = "<b>" . $data_list[$i]['nama_unit'] . "</b>";
                    $send['total_penerimaan'] = "<b>" . number_format(
                                    $data_list[$i]['jumlah_total'], 0, ',', '.') . "</b>";
                    if ($send['approval'] == 1) {
                        $send['status'] = '';
                    } elseif ($send['approval'] == 2) {
                        $send['status'] = '';
                    } elseif ($send['approval'] == 3) {
                        $send['status'] = '';
                    } else {
                        $send['status'] = '';
                    }
                    $send['class_name'] = "table-common-even";
                    $send['nomor'] = "";
                    $send['class_button'] = "toolbar";
                    $url_add_edit = Dispatcher::Instance()->GetUrl(
                                    'approval_rencana_penerimaan', 'InputRencanaPenerimaan', 'view', 'html') .
                            '&tahun_anggaran=' .
                            Dispatcher::Instance()->Encrypt($this->Data['tahun_anggaran']) .
                            '&unitkerja=' .
                            Dispatcher::Instance()->Encrypt($data_list[$i]['idunit']) .
                            '&cari=' . Dispatcher::Instance()->Encrypt(1);

                    $send['url_add_edit'] = '<!--<a class="xhr dest_subcontent-element" href="' .
                            $url_add_edit .
                            '" title="Tambah Rencana Penerimaan"> <img src="images/button-add.gif" alt="Tambah Rencana Penerimaan"/></a>-->';

                    $send['url_add_delete'] = "";
                    $send['url_popup_detail'] = '';
                    $no = 1;
                }

                $this->mrTemplate->AddVars('data_item', $send, 'DATA_');
                $this->mrTemplate->parseTemplate('data_item', 'a');
            }
        }
    }

}

?>