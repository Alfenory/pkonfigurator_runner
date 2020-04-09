<?php

error_reporting(E_ALL);
ini_set('error_reporting', E_ALL);

require "vendor/autoload.php";
require "config.php";

class ReplacementController {
    var $url;
    var $produkt_id;
    var $produkt;
    var $seiten_list;
    var $konfiguration_list;
    var $konfigurationen;
    var $konfigurationenoption_list;
    var $replace_keys = [];

    function __construct($url, $produkt_id, $directus_controller, $order) {
        $this->url = $url;
        $this->produkt_id = $produkt_id;
        $this->seiten_list = [];
        $this->konfiguration_list = [];
        $this->konfigurationen = [];
        $this->directus_controller = $directus_controller;
        $this->init_data($order);
    }

    public function init_data($order) {
        global $config;
        $produkt_list = $this->directus_controller->load_custom_object_list($config["api"]["url"]."/items/produkt/".$this->produkt_id);
        if (count($produkt_list) > 0) {
            $this->produkt = $produkt_list[0];
            $this->seiten_list = $this->directus_controller->load_custom_object("seiten?filter[status][eq]=published&sort=reihenfolge&filter[produkt][eq]=".$this->produkt_id);

            for ($j=0; $j<count($seiten_list); $j++) {
                $bereich_list = $this->directus_controller->load_custom_object("produktbereich?filter[status]=published&sort=reihenfolge&filter[seite][eq]=".$this->seiten_list[$j]->id);
                for ($k=0; $k<count($bereich_list); $k++) {
                    $konfiguration_list1 = $this->directus_controller->load_custom_object("produktkonfiguration?filter[status]=published&filter[produktbereich][eq]=".$bereich_list[$k]->id);
                    for ($l=0; $l<count($konfiguration_list1); $l++) {
                        $this->konfiguration_list[] = $konfiguration_list1[$l];
                        $this->konfigurationen[] = $konfiguration_list1[$l]->id;
                    }
                }
            }

            $this->konfigurationenoption_list = $directus_controller->load_custom_object("produktkonfigurationsoption?filter[status]=published&filter[produktkonfiguration][in]=".implode(",", $this->konfigurationen));
            $this->replace_keys = \PKonfigurator\Runner\TemplateController::get_replace_keys($order, $this->produkt, $this->konfiguration_list, $this->konfigurationenoption_list);
        }
    }
    
    public function replace_content($inhalt) {
        return strtr($inhalt, $this->replace_keys);
    }
}