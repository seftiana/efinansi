<?php
require_once GTFWConfiguration::GetValue('application','docroot').
'module/rencana_penerimaan_adjust_history/business/RencanaPenerimaanAdjustHistory.class.php';

#doc
#    classname:    ViewDetilAdjustment
#    scope:        PUBLIC
#
#/doc

class ViewDetilAdjustment extends HtmlResponse
{
    #    internal variables
    public $obj;
    public $data;
    public $decId;
    #    Constructor
    function __construct ()
    {
        # code...
        $this->obj      = new RencanaPenerimaanAdjustHistory();
        $this->decId    = Dispatcher::Instance()->Decrypt($_GET['dataId']);
    }
    
    function TemplateModule()
    {
        $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
        'module/rencana_penerimaan_adjust_history/template/');
        $this->SetTemplateFile('view_detil_adjustment.html');
    }
    
    function ProcessRequest()
    {
        $this->data     = $this->obj->GetDataById($this->decId);
        $this->data['januariadjust']   = number_format($this->data['januari_b'],0,',','.');
        $this->data['februariadjust']  = number_format($this->data['februari_b'],0,',','.');
        $this->data['maretadjust']     = number_format($this->data['maret_b'],0,',','.');
        $this->data['apriladjust']     = number_format($this->data['april_b'],0,',','.');
        $this->data['meiadjust']       = number_format($this->data['mei_b'],0,',','.');
        $this->data['juniadjust']      = number_format($this->data['juni_b'],0,',','.');
        $this->data['juliadjust']      = number_format($this->data['juli_b'],0,',','.');
        $this->data['agustusadjust']   = number_format($this->data['agustus_b'],0,',','.');
        $this->data['septemberadjust'] = number_format($this->data['september_b'],0,',','.');
        $this->data['oktoberadjust']   = number_format($this->data['oktober_b'],0,',','.');
        $this->data['novemberadjust']  = number_format($this->data['november_b'],0,',','.');
        $this->data['desemberadjust']  = number_format($this->data['desember_b'],0,',','.');
        $this->data['tnominal']        = number_format($this->data['total'],0,',','.');
        
    }
    
    function ParseTemplate($data = null)
    {
        $dataList   = $this->data;
        $dataList['pagu']           = intval($dataList['pagu']);
        $dataList['totalpagu']      = number_format(floatval($dataList['totalpagu']), 0, ',','.');
        $dataList['totalterima']    = number_format(floatval($dataList['totalterima']), 0, ',', '.');
        $dataList['tarif']          = number_format(floatval($dataList['tarif']), 0, ',', '.');
        $dataList['adjustment']     = floatval(20000);
        $dataList['tgl_adjust']     = $this->_dateToIndo($dataList['tanggal_adjust']);
        $dataList['dataid']         = $this->idDec;
        foreach($dataList as $key => $val){
            $this->mrTemplate->AddVar('content',strtoupper($key),$val);
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
