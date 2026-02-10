<?php
require_once GTFWConfiguration::GetValue('application','docroot').
'module/rencana_penerimaan_adjust_history/business/RencanaPenerimaanAdjustHistory.class.php';
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
'module/user_unit_kerja/business/UserUnitKerja.class.php';

#doc
#    classname:    ViewHistoryAdjustmentRencanaPenerimaan
#    scope:        PUBLIC
#
#/doc

class ViewHistoryAdjustmentRencanaPenerimaan extends HtmlResponse
{
    #    internal variables
    public $obj;
    public $pesan;
    public $style;
    public $dataList;
    public $data;
    public $uid;
    public $unitKerjaObj;
    public $post;
    #    Constructor
    function __construct()
    {
        $this->obj          = new RencanaPenerimaanAdjustHistory();
        $this->unitKerjaObj = new UserUnitKerja();
        $this->uid          = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
        $this->post         = $_POST->AsArray();
    }
    
    function TemplateModule()
    {
        $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
        'module/rencana_penerimaan_adjust_history/template/');
        $this->SetTemplateFile('view_history_adjustment_rencana_penerimaan.html');
    }
    
    function ProcessRequest()
    {
        $role   = $this->unitKerjaObj->GetRoleUser($this->uid);
        $unit   = $this->unitKerjaObj->GetUnitKerjaUser($this->uid);
        $tahun_anggaran     = $this->obj->GetTahunAnggaranAktif();
        $arr_tahun_anggaran = $this->obj->GetComboTahunAnggaran();
        $msg			= Messenger::Instance()->Receive(__FILE__);
        $return['msg']	= $msg[0][1];
		$return['data']	= $msg[0][0];
		$return['css']	= $msg[0][2];
		
        if(isset($this->post['btncari'])){
            $this->data['nama']             = trim($this->post['nama']);
            $this->data['tahun_anggaran']   = $this->post['tahun_anggaran'];
            $this->data['unit_id']          = $this->post['unit_id'];
            $this->data['unit']             = $this->post['unit'];
        }elseif(isset($_GET) AND $_GET['cari'] != ''){
            $this->data['nama']             = Dispatcher::Instance()->Decrypt($_GET['nama']);
            $this->data['tahun_anggaran']   = Dispatcher::Instance()->Decrypt($_GET['tahun_anggaran']);
            $this->data['unit_id']          = Dispatcher::Instance()->Decrypt($_GET['unit_id']);
            $this->data['unit']             = Dispatcher::Instance()->Decrypt($_GET['unit']);
        }else{
            $this->data['tahun_anggaran']   = $tahun_anggaran['id'];
            $this->data['unit_id']          = $unit['unit_kerja_id'];
            $this->data['unit']             = $unit['unit_kerja_nama'];
            $this->data['nama']             = '';
        }
        
        Messenger::Instance()->SendToComponent(
            'combobox', 
            'Combobox', 
            'view', 
            'html', 
            'tahun_anggaran', 
            array(
                'tahun_anggaran', 
                $arr_tahun_anggaran, 
                $this->data['tahun_anggaran'], '-', 
                ' style="width:200px;" id="tahun_anggaran"'
            ), 
        Messenger::CurrentRequest);
        
        // paging
        $totalData = $this->obj->CountData(
                    $this->data['nama'],
                    $this->data['tahun_anggaran'], 
                    $this->data['unit_id']);
        $itemViewed = 20;
        $currPage = 1;
        $startRec = 0 ;
        if(isset($_GET['page'])) {
            $currPage = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
            $startRec =($currPage-1) * $itemViewed;
        }
        
        $this->dataList = $this->obj->GetData(
                    $this->data['nama'],
                    $this->data['tahun_anggaran'],
                    $this->data['unit_id'],$startRec,$itemViewed);
        
        $url = Dispatcher::Instance()->GetUrl(
            Dispatcher::Instance()->mModule, 
            Dispatcher::Instance()->mSubModule, 
            Dispatcher::Instance()->mAction, 
            Dispatcher::Instance()->mType 
            . '&nama=' . Dispatcher::Instance()->Encrypt($this->data['nama']) 
            . '&tahun_anggaran=' . Dispatcher::Instance()->Encrypt(
            $this->data['tahun_anggaran']) 
            . '&unit_id=' . Dispatcher::Instance()->Encrypt($this->data['unit_id']) 
            . '&unit=' . Dispatcher::Instance()->Encrypt(
            $this->data['unit']) 
            . '&cari=' . Dispatcher::Instance()->Encrypt(1));

        Messenger::Instance()->SendToComponent(
            'paging', 
            'Paging', 
            'view', 
            'html', 
            'paging_top', 
            array(
                $itemViewed,
                $totalData, 
                $url, 
                $currPage), 
        Messenger::CurrentRequest);
        
        $return['role']     = $role;
        return $return;
    }
    
    function ParseTemplate($data = null)
    {
        $role           = $data['role'];
        $msg            = $data['msg'];
        $style          = $data['css'];
        if($msg):
		    $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
		    $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $msg);
		    $this->mrTemplate->AddVar('warning_box', 'STYLE', $style);
		    
	    endif;
        $url_search     = Dispatcher::Instance()->GetUrl(
            'rencana_penerimaan_adjust_history',
            'HistoryAdjustmentRencanaPenerimaan',
            'view',
            'html'
        );
        
        $this->mrTemplate->AddVar('content','URL_SEARCH',$url_search);
        $this->mrTemplate->AddVar('content','URL_RESET',$url_search);
        $this->mrTemplate->AddVar('role','WHOAMI',strtolower($role['role_name']));
        
        $popup_unit_kerja   = Dispatcher::Instance()->GetUrl(
            'rencana_penerimaan_adjust_history',
            'PopupUnitkerja',
            'view',
            'html'
        );
        
        $this->mrTemplate->AddVar('role','URL_POPUP_UNIT_KERJA',$popup_unit_kerja);
        
        // data search
        $this->mrTemplate->AddVar('content','SEARCH_NAMA',$this->data['nama']);
        $this->mrTemplate->AddVar('role','UNIT_ID',$this->data['unit_id']);
        $this->mrTemplate->AddVar('role','UNIT',$this->data['unit']);
        
        $data_list  = $this->dataList;
        if(empty($data_list)){
            # jika data rencana penerimaan tidak ada alias kosong
            $this->mrTemplate->AddVar('data_list','DATA_EMPTY','YES');
        }else{
            # jika data rencana penerimaan ada datanya
            $this->mrTemplate->AddVar('data_list','DATA_EMPTY','NO');
            
            $total='';
            $jumlah_total='';
            $idrencana='';
            $idkode='';
            $kode='';
            $nama='';

            $kode_satker = '';
            $kode_unit = '';
            $nama_satker='';
            $nama_unit='';
            
            for ($i=0; $i<sizeof($data_list);) {
                 if(($data_list[$i]['kode_satker'] == $kode_satker) && 
                        ($data_list[$i]['kode_unit'] == $kode_unit)) {
                        
                    if($data_list[$i]['idrencana'] == "") {
                        $i++; 
                        continue;
                    }
                    
                    $send                       = $data_list[$i];
                    $send['total_penerimaan']   = number_format($data_list[$i]['total'], 0, ',', '.');
                    $send['class_name']         = "";
                    $send['nomor']              = $no;
                    $send['class_button']       = "links";
                    
                    $send['total_adjust']       = number_format($data_list[$i]['total_adjust'], 0, ',', '.');
                    $send['nomor_adjust']       = $data_list[$i]['nomor_adjust'];
                    $send['tanggal_adjust']     = $this->_dateToIndo($data_list[$i]['tgl_adjust']);
                    $url_add_edit               = Dispatcher::Instance()->GetUrl(
                        'rencana_penerimaan', 
                        'InputRencanaPenerimaan', 
                        'view', 
                        'html&dataId='. Dispatcher::Instance()->Encrypt(
                            $data_list[$i]['idrencana']
                        )
                    );
                    
                        $url_adjust             = Dispatcher::Instance()->GetUrl(
                            'rencana_penerimaan_adjust',
                            'InputRencanaPenerimaanAdjust',
                            'view',
                            'html&dataId='. Dispatcher::Instance()->Encrypt(
                                $data_list[$i]['idrencana']
                            )
                        );
                        
                        $url_edit               = Dispatcher::Instance()->GetUrl(
                            'rencana_penerimaan_adjust_history',
                            'FormRencanaPenerimaanAdjust',
                            'view',
                            'html&dataId='.Dispatcher::Instance()->Encrypt(
                                $data_list[$i]['adjust_id']
                            )
                        );
                        
                        $url_detil              = Dispatcher::Instance()->GetUrl(
                            'rencana_penerimaan_adjust_history',
                            'DetilAdjustment',
                            'view',
                            'html&dataId='.Dispatcher::Instance()->Encrypt(
                                $data_list[$i]['adjust_id']
                            )
                        );
                        
                        $url_approve            = Dispatcher::Instance()->GetUrl(
                            'rencana_penerimaan_adjust_history',
                            'ApprovalAdjustment',
                            'do',
                            'html&data='.Dispatcher::Instance()->Encrypt(
                                $data_list[$i]['adjust_id']
                            ).'|'.Dispatcher::Instance()->Encrypt(
                                $data_list[$i]['idrencana']
                            ).'|'.$data_list[$i]['total_adjust']
                        );
                    
                    if ($send['approval']==1 and $data_list[$i]['status'] == 'Request')
                    {                        
                        $send['url_adjust']     = '<a class="xhr dest_subcontent-element" 
                                                href="'.$url_adjust.'" 
                                                Title="Adjustment rencana penerimaan">
                                                <img src="images/button-tindaklanjuti.gif" 
                                                alt="Adjust" /></a>';
                        $send['url_edit']       = '<a class="xhr dest_subcontent-element" href="'.$url_edit.'" 
                                                  title="edit">
                                                  <img src="images/button-edit.gif" alt="edit" /></a>';
                        $send['url_approve']    = '<a class="xhr dest_subcontent-element" 
                                                  href="'.$url_approve.'" title="approve">
                                                  <img src="images/button-simpan.gif" alt="approve" /></a>';
                        $send['url_detil']      = '<a href="javascript:void(0);" 
                                                  onclick="detail_adjustment(\''.$url_detil.'\')" 
                                                  title="detail adjustment">
                                                  <img src="images/button-detail.gif" alt="detil" /></a>';
                        
                    }else{
                        $send['url_detil']      = '<a href="javascript:void(0);" 
                                                  onclick="detail_adjustment(\''.$url_detil.'\')" 
                                                  title="detail adjustment">
                                                  <img src="images/button-detail.gif" alt="detil" /></a>';
                    }
                    
                    $this->mrTemplate->AddVar('cekbox', 'data_number', $number);
                    $this->mrTemplate->AddVar('cekbox', 'data_idrencana', $data_list[$i]['idrencana']);
                    $this->mrTemplate->AddVar('cekbox', 'data_nama', $data_list[$i]['nama']);
                    $this->mrTemplate->AddVar('cekbox', 'IS_SHOW', 'YES');
                    $i++;
                    $no++;
                    $number++;

                } elseif($data_list[$i]['kode_satker'] != $kode_satker && 
                        $data_list[$i]['nama_satker'] == $data_list[$i]['nama_unit']) {
                    $send['url_adjust'] = '';
                    $kode_satker        = $data_list[$i]['kode_satker'];
                    $kode_unit          = $data_list[$i]['kode_unit'];
                    $nama_satker        = $data_list[$i]['nama_satker'];
                    $nama_unit          = $data_list[$i]['nama_unit'];
                    $send['kode']       = "<b>".$kode_unit."</b>";
                    $send['nama']       = "<b>".$data_list[$i]['nama_unit']."</b>";
                    $send['total_penerimaan'] = "<b>".number_format(
                        $data_list[$i]['jumlah_total'], 
                        0, 
                        ',',
                        '.'
                    )."</b>";
                    $send['total_adjust']   = '';
                    $send['class_name']     = "table-common-even1";
                    $send['nomor']          = "";
                    $send['class_button']   = "toolbar";
 
                    $no=1;
                }elseif($data_list[$i]['kode_unit'] != $kode_unit) {
                    
                    $kode_satker    = $data_list[$i]['kode_satker'];
                    $kode_unit      = $data_list[$i]['kode_unit'];
                    $nama_satker    = $data_list[$i]['nama_satker'];
                    $nama_unit      = $data_list[$i]['nama_unit'];
                    $send['kode']   = "<b>".$kode_unit."</b>";
                    $send['nama']   = "<b>".$data_list[$i]['nama_unit']."</b>";
                    $send['total_penerimaan'] = "<b>".number_format(
                        $data_list[$i]['jumlah_total'], 
                        0, 
                        ',', 
                        '.'
                    )."</b>";
                    $send['class_name']     = "table-common-even";
                    $send['nomor']          = "";
                    $send['class_button']   = "toolbar";
                    $send['total_adjust']   = '';
                    $no=1;
                }
            
            $this->mrTemplate->AddVars('data_grid', $send, 'DATA_');
            $this->mrTemplate->parseTemplate('data_grid', 'a');
            
            }
        }
    }
    
    # buat convert ke tanggal indonesia support php 5.3.xx, php 5.2.xx
    function _dateToIndo($date)
    {
        $indonesian_months = array('N/A',
                                   'Januari',
                                   'Februari',
                                   'Maret',
                                   'April',
                                   'Mei',
                                   'Juni',
                                   'Juli',
                                   'Agustus',
                                   'September',
                                   'Oktober',
                                   'Nopember',
                                   'Desember');
        if(preg_match("/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})/", $date, $patch))
        {
            $year   = (int) $patch[1];
            $month  = (int) $patch[2];
            $month  = $indonesian_months[(int) $patch[2]];
            $day    = (int) $patch[3];
            $hour   = (int) $patch[4];
            $min    = (int) $patch[5];
            $sec    = (int) $patch[6];
            
            $return = $day.' '.$month.' '.$year;
        }
        elseif(preg_match("/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})/", $date, $patch))
        {
            $year   = (int) $patch[1];
            $month  = (int) $patch[2];
            $month  = $indonesian_months[$month];
            $day    = (int) $patch[3];
            
            $return = $day.' '.$month.' '.$year;
        }
        else
        {
            $return = (int) $date;
        }
        return $return;
    }
    
}
?>
