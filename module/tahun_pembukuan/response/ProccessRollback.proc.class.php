<?php
require_once GTFWConfiguration::GetValue('application', 'docroot').
    'module/tahun_pembukuan/business/RollbackPembukuan.class.php';

class ProccessRollback{
    protected $mObj;
    protected $_POST;
	protected $pageView;

    public function __construct(){
        $this->_POST = is_object($_POST) ? $_POST->AsArray() : $_POST;
        
        $this->pageView = Dispatcher::Instance()->GetUrl(
            'tahun_pembukuan',
            'TahunPembukuan',
            'view',
            'html'
        );
        $this->mObj = new RollbackPembukuan();
    }

    public function Rollback(){
        $result = $this->mObj->DoRollback($this->_POST);

        if($result){
            $this->_POST['done'] = 'ok';

            Messenger::Instance()->Send(
                'tahun_pembukuan',
                'TahunPembukuan',
                'view',
                'html',
                array(
                    $this->_POST,
                    'Rollback Tahun Pembukuan Berhasil Dilakukan'
                ),
                Messenger::NextRequest
            );
        }else{
            Messenger::Instance()->Send(
                'tahun_pembukuan',
                'TahunPembukuan',
                'view',
                'html',
                array(
                    $this->_POST,
                    'Rollback Tahun Pembukuan Gagal Dilakukan'
                ),
                Messenger::NextRequest
            );
        }

        return $this->pageView;
    }
}
