<?php

namespace PKonfigurator\Runner;

class ActionRunner {

    var $konfig_list;
    var $option_list;
    var $option_list_old;
    var $order_list;
    var $order_additional_list;

    function __construct($konfig_list, $option_list, $order_list, $order_additional_list) {
        $this->konfig_list = $konfig_list;
        $this->option_list = $option_list;
        $this->option_list_old = json_decode(json_encode($option_list));
        $this->order_additional_list = $order_additional_list;
        $this->order_list = $order_list;
    }

    function run_actions() {
        
        for ($i=0; $i<count($this->konfig_list); $i++) {
            $option_id = $this->getKonfigurationOptionId($this->konfig_list[$i]->id);
            if ($option_id*1 !== -1) {
                $option_index = $this->get_index_of_produktkonfigurationoption($option_id);
                if ($option_index !== -1) {
                    $this->do_option($this->konfig_list[$i], $this->option_list[$option_index]);
                }
            }
        }
    }

    function do_option($konfig, $option) {
        if (
            $option->aktionen !== null && $option->aktionen !== "null" && 
            $option->aktionen !== "") {
                
            $aktion = $option->aktionen;

            $vars = [
                '$AKTUELLE_OPTION' => $option->id,
                '$AKTUELLE_KONFIGURATION' => $konfig->id,
                'this.' => "\$this->"
            ];

            $aktion = strtr($aktion, $vars);

            try {
                eval($aktion);
            }
            catch (ParseError $err) {
                echo "not parseable!";
                echo $err->getMessage();
            }
            catch (\Throwable $exc) {
                echo "not executeable!";
                echo $exc->getMessage();
            }
        }
    }

    function get_index_of_produktkonfigurationoption($option_id) {
        for ($i=0; $i< count($this->option_list); $i++) {
            if ($this->option_list[$i]->id*1 === $option_id*1) {
              return $i;
            }
        }
        return -1;
    }
    
    function setOptionpreis($option_id, $price, $type) {

        $index = $this->get_index_of_produktkonfigurationoption($option_id);
        if ($type*1 === 1) { //einmalig
            $this->option_list[$index]->preis_monatlich_einmalig = $price*1;
        }
        if ($type*1 === 2) { //monatlich
            $this->option_list[$index]->preis_monatlich = $price*1;
        }
        if ($type*1 === 3) { //quartalsweise einmalig
            $this->option_list[$index]->preis_quartalsweise_einmalig = $price*1;
        }
        if ($type*1 === 4) { //quartalsweise
            $this->option_list[$index]->preis_quartalsweise = $price*1;
        }
        if ($type*1 === 5) { //jaehrlich einmalig
            $this->option_list[$index]->preis_jaehrlich_einmalig = $price*1;
        }
        if ($type*1 === 6) { //jaehrlich
            $this->option_list[$index]->preis_jaehrlich = $price*1;
        }
    }
    
    function getAnzahl($konfiguration_id) {
        for ($i=0;$i<count($this->order_additional_list); $i++) {
            if ($this->order_additional_list[$i]->konf*1 === $konfiguration_id) {
                return $this->order_additional_list[$i]->value*1;
            }
        }
        return 0;
    }
    
    function getKonfigurationOptionId($konfiguration_id) {
        for ($i=0;$i<count($this->order_list);$i++) {
            if ($this->order_list[$i]->konf*1 === $konfiguration_id*1) {
                return $this->order_list[$i]->value*1;
            }
        }
        return -1;
    }
    
    function getKonfigurationPreis($konfiguration_id, $type) {
        $option_id = $this->getKonfigurationOptionId($konfiguration_id);
        if ($option_id !== null) {
            return $this->getAlterPreis($option_id, $type);
        } else {
            error_log("getKonfigurationPreis: Konfiguration mit ID ".$konfiguration_id." nicht gefunden");
        }
        return 0;
    }
    
    function getAlterPreis($option_id, $type) {
        $index = $this->get_index_of_produktkonfigurationoption($option_id);
        if (isset($this->option_list_old[$index]) === false) {
          return 0;
        }
        if ($type*1 === 1) { //einmalig
            return $this->option_list_old[$index]->preis_monatlich_einmalig*1;
        }
        if ($type*1 === 2) { //monatlich
            return $this->option_list_old[$index]->preis_monatlich*1;
        }
        if ($type*1 === 3) { //quartalsweise einmalig
            return $this->option_list_old[$index]->preis_quartalsweise_einmalig*1;
        }
        if ($type*1 === 4) { //quartalsweise
            return $this->option_list_old[$index]->preis_quartalsweise*1;
        }
        if ($type*1 === 5) { //jaehrlich einmalig
            return $this->option_list_old[$index]->preis_jaehrlich_einmalig*1;
        }
        if ($type*1 === 6) { //jaehrlich
            return $this->option_list_old[$index]->preis_jaehrlich*1;
        }
    }
    
    function getValue($val) {
        return $this->getKonfigurationOptionId($val);
    }

}