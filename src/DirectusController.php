<?php

namespace PKonfigurator\Runner;

if (!function_exists('is_countable')) {
    function is_countable($var) {
        return (is_array($var) || $var instanceof Countable);
    }
}

class DirectusController {

    private $token;
    private $obj_cache;

    function __construct() {
        $this->get_token();
        $this->obj_cache = array();
    }

    function print_token() {
        echo "token:".$this->token;
    }

    function send_mail($userlist, $subject, $content) {
        global $config;
        $url = $config["api"]["url"]."/mail";
        $body = array(
            "to" => implode(",", $userlist),
            "subject" => $subject,
            "body" => $content,
            "type" => "html"
        );
        CurlLib::get_content_url_with_header($url, $body, $this->get_auth(), 2);
        return true;
    }

    function get_token() {
        global $config;
        $url = $config["api"]["url"]."/auth/authenticate";
        $data = array("email" => $config["api"]["username"], "password" => $config["api"]["password"]);
        $response = json_decode(CurlLib::get_content_url_with_header($url, $data, null, 2));
        $this->token = $response->data->token;
    }

    function get_auth() {
        $auth = array();
        $auth[] = "Authorization: Bearer ".$this->token;
        return $auth;
    }

    public static function is_countable($list) {
        if (!function_exists('is_countable')) {
            return is_countable($list); 
        } else {
            return (is_array($list) || $list instanceof Countable);
        }
    }

    function load_custom_object_list($url) {
        global $config;
        $response = json_decode(CurlLib::get_content_url_with_header($url, null, $this->get_auth()));
        if ($response && property_exists($response, "data")) {
            if (self::is_countable($response->data) && count($response->data) > 0) {
                return $response->data;
            } else {
                return array($response->data);
            }
        } else {
            return array();
        }
    }

    function load_custom_object($obj) {
        global $config;
        $url = $config["api"]["url"]."/items/".$obj;
        $resp = CurlLib::get_content_url_with_header($url, null, $this->get_auth());
        
        //echo "\nurl:".$url."\n";
        //echo $resp."\n";

        $this->obj_cache[$obj] = json_decode($resp)->data;
        return $this->obj_cache[$obj];
    }

    function load_custom_object_with_meta($obj) {
        global $config;
        $url = $config["api"]["url"]."/items/".$obj;
        return json_decode(CurlLib::get_content_url_with_header($url, null, $this->get_auth()));
    }

    function get_custom_object($obj, $id) {
        global $config;
        if (!isset($this->obj_cache[$obj])) {
            $this->load_custom_object($obj);
        } 
        for ($i=0; $i<count($this->obj_cache[$obj]); $i++) {
            if ($this->obj_cache[$obj][$i]->id*1 === $id*1) {
                return $this->obj_cache[$obj][$i];
            }
        }
        return null;
    }

    function post_data($custom_url, $body) {
        global $config;
        $url = $config["api"]["url"].$custom_url;
        return json_decode(CurlLib::get_content_url_with_header($url, $body, $this->get_auth(), 2));
    }

    function patch_data($custom_url, $body) {
        global $config;
        $url = $config["api"]["url"].$custom_url;
        return json_decode(CurlLib::get_content_url_with_header($url, $body, $this->get_auth(), 4));
    }

    function get_images_for_documents($document_list) {
        global $config; 
        $url = "";
        if (is_array($document_list)) {
            $url = $config["api"]["url"]."/files/".implode(",", $document_list);
        } else {
            $url = $config["api"]["url"]."/files/".$document_list;
        }
        $response = json_decode(CurlLib::get_content_url_with_header($url, null, $this->get_auth()));
        if (property_exists($response, "error")) {
            return array();
        }
        if (is_array($response->data)) {
            return $response->data;
        } else {
            return array($response->data);
        }
    }

}