<?php
#doc
#    classname:    Renstra
#    scope:        PUBLIC
#
#/doc

class Renstra extends Database
{
    #    internal variables
    protected $mSqlFile = 'module/visi/business/renstra.sql.php';
    #    Constructor
    function __construct ($connectionNumber = 0)
    {
        # code...
        parent::__construct($connectionNumber);
    }
    
    function GetComboRenstra()
    {
        # untuk mendapatkan combo renstra
        $result     = $this->Open(
            $this->mSqlQueries['get_combo_renstra'],
            array()
        );
        
        return $result;
    }
    
    function GetRenstraAktif(){
        # untuk mendapatkan renstra aktif
        $result     = $this->Open(
            $this->mSqlQueries['get_renstra_aktif'],
            array()
        );
        
        return $result[0];
    }

}
?>
