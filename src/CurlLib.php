<?php

namespace PKonfigurator\Runner;

class CurlLib {
    public static function get_content_url_with_header($url, $values = null, $header = null, $type = 1, $return_mixed = false) {

        //echo "<br/><b>url:</b> ".$url;
        //echo "<br/><b>values:</b> ".var_export($values, true);
        //echo "<br/><b>header:</b> ".var_export($header, true);
        //echo "<br/><b>type:</b> ".$type;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        if($type <= 2) {
            curl_setopt($ch, CURLOPT_POST, $type === 2 ? 1 : 0);
            curl_setopt($ch, CURLOPT_HTTPGET, $type === 1 ? 1 : 0);
        }
        //curl_setopt($ch, CURLOPT_VERBOSE, 1);
        if($type === 3) {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE"); 
        }

        if($type === 4) {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH"); 
        }

        if($values !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $values);
        }
        if($header !== null) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }
        else {
            curl_setopt($ch, CURLOPT_HEADER, 0);
        }
        
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
        
        $sResult = curl_exec($ch);

        if (curl_errno($ch)) {
            // Fehlerausgabe
            error_log(curl_error($ch));
            return null;
        } else {
            // Kein Fehler, Ergebnis zurückliefern:
            $info = curl_getinfo($ch);
            curl_close($ch);
            if ($return_mixed === true) {
                return array($sResult, $info);
            } else {    
                return $sResult;
            }
        }    
    }

}