<?php
require_once GTFWConfiguration::GetValue('application','docroot').
'module/rencana_penerimaan_adjust/business/RencanaPenerimaanAdjust.class.php';

#doc
#    classname:    ProcessAdjustment
#    scope:        PUBLIC
#
#/doc

class ProcessAdjustment 
{
    #    internal variables
    public $obj;
    public $post;
    public $urlReturn;
    public $urlHome;
    public $cssFail = "notebox-warning";
    public $cssDone = "notebox-done";
    public $data    = array();
    protected $userId;
    #    Constructor
    function __construct ()
    {
        # code...
        $this->userId   = Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
        $this->obj      = new RencanaPenerimaanAdjust();
        $this->urlReturn    = Dispatcher::Instance()->GetUrl(
            'rencana_penerimaan_adjust',
            'InputRencanaPenerimaanAdjust',
            'view',
            'html'
        );
        $this->urlHome      = Dispatcher::Instance()->GetUrl(
            'rencana_penerimaan_adjust',
            'RencanaPenerimaanAdjust',
            'view',
            'html'
        );
        $this->post = $_POST->AsArray();
    }
    
    function check()
    {
        if(isset($this->post['btnsimpan'])){
            if($this->obj->DoCheckAdjustmentRencanaPenerimaan($this->post['id_data']) <> 0){
                return 'checkApprovalAdjustment';
            }else{
                return true;
            }
        }
    }
    
    function Adjust()
    {
        $check  = $this->check();
        $this->data['user_id']  = $this->userId;
        $this->data['data_id']  = $this->post['id_data'];
        $this->data['total']    = $this->post['total_nominal'];
        $this->data['tpersen']  = $this->post['total_persen'];
        
        # persen inputan
        for($i=0; $i < count($this->post['persenpenerimaan']); $i++){
            if(empty($this->post['persenpenerimaan'][$i])){
                $this->post['persenpenerimaan'][$i] = 0;
            }
        }
        
        $this->data['pjanuariadjust']   = $this->post['persenpenerimaan'][0];
        $this->data['pfebruariadjust']  = $this->post['persenpenerimaan'][1];
        $this->data['pmaretadjust']     = $this->post['persenpenerimaan'][2];
        $this->data['papriladjust']     = $this->post['persenpenerimaan'][3];
        $this->data['pmeiadjust']       = $this->post['persenpenerimaan'][4];
        $this->data['pjuniadjust']      = $this->post['persenpenerimaan'][5];
        $this->data['pjuliadjust']      = $this->post['persenpenerimaan'][6];
        $this->data['pagustusadjust']   = $this->post['persenpenerimaan'][7];
        $this->data['pseptemberadjust'] = $this->post['persenpenerimaan'][8];
        $this->data['poktoberadjust']   = $this->post['persenpenerimaan'][9];
        $this->data['pnovemberadjust']  = $this->post['persenpenerimaan'][10];
        $this->data['pdesemberadjust']  = $this->post['persenpenerimaan'][11];
        
        for($i=0;$i<count($this->post['penerimaanbln']);$i++){
            if(empty($this->post['penerimaanbln'][$i])){
                $this->post['penerimaanbln'][$i]    = 0;
            }
        }
            
        $this->data['penerimaan_januari']   = $this->post['penerimaanbln'][0];
        $this->data['penerimaan_februari']  = $this->post['penerimaanbln'][1];
        $this->data['penerimaan_maret']     = $this->post['penerimaanbln'][2];
        $this->data['penerimaan_april']     = $this->post['penerimaanbln'][3];
        $this->data['penerimaan_mei']       = $this->post['penerimaanbln'][4];
        $this->data['penerimaan_juni']      = $this->post['penerimaanbln'][5];
        $this->data['penerimaan_juli']      = $this->post['penerimaanbln'][6];
        $this->data['penerimaan_agustus']   = $this->post['penerimaanbln'][7];
        $this->data['penerimaan_september'] = $this->post['penerimaanbln'][8];
        $this->data['penerimaan_oktober']   = $this->post['penerimaanbln'][9];
        $this->data['penerimaan_nopember']  = $this->post['penerimaanbln'][10];
        $this->data['penerimaan_desember']  = $this->post['penerimaanbln'][11];
            
        for ($i = 0; $i < count($this->post['jmlpenerimaan']); $i++)
        {
            # code...
            if(empty($this->post['jmlpenerimaan'][$i])){
                $this->post['jmlpenerimaan'][$i]    = 0;
            }
        }
        $this->data['adjust_januari']       = $this->post['jmlpenerimaan'][0];
        $this->data['adjust_februari']      = $this->post['jmlpenerimaan'][1];
        $this->data['adjust_maret']         = $this->post['jmlpenerimaan'][2];
        $this->data['adjust_april']         = $this->post['jmlpenerimaan'][3];
        $this->data['adjust_mei']           = $this->post['jmlpenerimaan'][4];
        $this->data['adjust_juni']          = $this->post['jmlpenerimaan'][5];
        $this->data['adjust_juli']          = $this->post['jmlpenerimaan'][6];
        $this->data['adjust_agustus']       = $this->post['jmlpenerimaan'][7];
        $this->data['adjust_september']     = $this->post['jmlpenerimaan'][8];
        $this->data['adjust_oktober']       = $this->post['jmlpenerimaan'][9];
        $this->data['adjust_nopember']      = $this->post['jmlpenerimaan'][10];
        $this->data['adjust_desember']      = $this->post['jmlpenerimaan'][11];
        
        if(isset($this->post['btnsimpan']) and $check === true){
            #echo $this->post['id'];
            # return $this->urlReturn.'&dataId='.$this->post['id_data'];
            
            $save   = $this->obj->DoInputAdjustmentRencanaPenerimaan($this->data,1);//'REQUEST');
            
            if($save){
                $pesan  = 'Berhasil melakukan adjustment rencana penerimaan';
                Messenger::Instance()->
				Send('rencana_penerimaan_adjust','RencanaPenerimaanAdjust','view','html',
				array($this->data,$pesan,$this->cssDone),Messenger::NextRequest);
				return $this->urlHome;
            }else{
                $pesan  = 'Gagal melakukan adjustment rencana penerimaan';
                Messenger::Instance()->
				Send('rencana_penerimaan_adjust','InputRencanaPenerimaanAdjust','view','html',
				array($this->data,$pesan,$this->cssFail),Messenger::NextRequest);
				return $this->urlReturn.'&dataId='.$this->post['id_data'];
            }
        }elseif($check == 'checkApprovalAdjustment'){
            $pesan  = 'Check history Adjustment Rencana Penerimaan, data ini tercatat mempunyai adjustmen yang belum di approve';
            Messenger::Instance()->
		    Send('rencana_penerimaan_adjust','InputRencanaPenerimaanAdjust','view','html',
		    array($this->data,$pesan,$this->cssFail),Messenger::NextRequest);
			return $this->urlReturn.'&dataId='.$this->post['id_data'];
        }else{
            return $this->urlHome;
        }
        
        return $this->urlHome;
    }

}
?>
