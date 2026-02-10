<?php

/**
 * 
 * @class SessionFilter URI
 * @author noor hadi <noor.hadi@gmaatechno.com>
 * @description class ini digunakan untuk menyimpan variable filter agar tidak hilang
 * @copyright (c) 2016 Gamatechno Indonesia
 * 
 */

/**
 * cara menggunakan class ini.
 * sertakan file ini pada module yang bersangkutan.
 * 1. require_once GTFWConfiguration::GetValue('application', 'docroot') .
 * 'module/additional_lib/business/SessionFilterURI.class.php';
 * 2. Registerkan data data filter dengan method ini : 
 *    SessionFilterURI::RegisterParamsFilter($data);
 * 3. Penggil data data filter dengan method ini :
 *    $dataFilter =  SessionFilterURI::GetParamsFilter();
 */


class SessionFilterURI {

    /**
     * RegisterParamsFilter
     * untuk meregisterkan parameter value filter
     * @return String
     * @access Public
     */
    public static function RegisterParamsFilter($data = array()) {
        //$_SESSION['filter'] = $data;
        if(is_array($data)) {
            foreach ($data as $key => $value) {
                if(is_object($value)) {
                    $value = $value->mrVariable;
                }
                $params[$key] =  Dispatcher::Instance()->Decrypt($value);
            }
        }
        $_SESSION['paramsFilter']  = $params;
    }
            
    /**
     * GetParamsFilter
     * untuk mendapatkan parameter value filter
     * nilai kembali => &a=data1&b=data2&c=data3&d=data4
     * @return String
     * @access Public
     */
    public static function GetParamsFilter()
    {
        if(!empty($_SESSION['paramsFilter'])) {
            $getValue = $_SESSION['paramsFilter'];
        }
       
        if(!empty($getValue)) {
            foreach($getValue as $key => $value) {
                $params[$key] = Dispatcher::Instance()->Encrypt($getValue[$key]);
            }
        }
        
      
        if(is_array($params)) {
            $paramString = implode('&', $params);
        } else {
            $paramString = $params;
        }
        
        return '&' . urldecode(http_build_query($params));
         
    }
    /**
     * end
     */

}

?>