<?php

namespace PKonfigurator\Runner;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class TemplateController {

    public static function get_anrede($order) {
        if ($order->anrede*1 === 1) {
            return "Firma";
        }
        if ($order->anrede*1 === 2) {
            return "Herr";
        }
        if ($order->anrede*1 === 3) {
            return "Frau";
        }
        return "";
    }

    public static function get_anrede_text($order) {
        if ($order->anrede*1 === 1) {
            return "Sehr geehrter Firma ".$order->firma;
        }
        if ($order->anrede*1 === 2) {
            return "Sehr geehrter Herr ".$order->name;
        }
        if ($order->anrede*1 === 3) {
            return "Sehr geehrter Frau ".$order->name;
        }
        return "";
    }

    public static function get_option($id, $produktkonfigurationoption_list) {
        for ($i=0; $i<count($produktkonfigurationoption_list); $i++) {
            if ($produktkonfigurationoption_list[$i]->id*1 === $id*1) {
                return $produktkonfigurationoption_list[$i];
            }
        }
        return null;
    }

    public static function get_table_price($preis1, $preis2 = null, $count1 = null, $count2 = null, $bold = false) {
        $text = "";
        if ($count1 === null) {
            $count1 = 1;
        }
        if ($count2 === null) {
            $count2 = 1;
        }
        $btag = "";
        $btagend = "";
        if ($bold) {
            $btag = "<b>";
            $btagend = "</b>";
        }
        $text .= "<td style='text-align:right; padding: 10px'>".$btag.number_format($preis1*$count1, 2, ",", ".")." &euro;".$btagend."</td>\n";
        if ($preis2 !== null) {
            $text .= "<td style='text-align:right; padding: 10px'>".$btag.number_format($preis2*$count2, 2, ",", ".")." &euro;".$btagend."</td>\n";
        }

        /*echo "preis: $preis1*$count1, ".$preis1*$count1."\n";
        echo "preis: $preis2*$count2, ".$preis2*$count2."\n\n";*/

        return $text;
    }

    public static function get_orderdetails_value($orderdetails, $pkonf_id) {
        for ($i=0; $i<count($orderdetails); $i++) {
            if ($orderdetails[$i]->konf*1 == $pkonf_id*1) {
                return $orderdetails[$i]->value;
            }
        }
        return -1;
    }

    public static function get_orderdetails_additional_value($orderdetails_additional, $pkonf_id) {
        for ($i=0; $i<count($orderdetails_additional); $i++) {
            if ($orderdetails_additional[$i]->konf*1 == $pkonf_id*1) {
                return $orderdetails_additional[$i]->value;
            }
        }
        return 0;
    }

    public static function get_produkttabelle($order, $produkt, $produktkonfiguration_list, $produktkonfigurationoption_list, $kurz = false) {

        
        $orderdetails = json_decode($order->orderdetails);
        $orderdetails_additional = json_decode($order->orderdetails_additional);

        $actionrunner = new ActionRunner($produktkonfiguration_list, $produktkonfigurationoption_list, $orderdetails, $orderdetails_additional);
        $actionrunner->run_actions();

        $produktkonfigurationoption_list = $actionrunner->option_list;

        $zahlungsweise = $order->zahlungsweise;

        $table = "<table>\n";
        $table .= "<thead>\n";
        $table .= "<tr>\n";
        $table .= "<th style='padding: 10px; padding-left: 0px; text-align: left'>Produkt / Option</th>\n";
        if ($zahlungsweise*1 === 1) {
            $table .= "<th style='padding: 10px'>Preis</th>\n";
        }
        if ($zahlungsweise*1 > 1) {
            $table .= "<th style='padding: 10px'>einmalig</th>\n";
            $table .= "<th style='padding: 10px'>";
            if ($zahlungsweise*1 === 2) {
                $table .= "monatlich";
            }
            if ($zahlungsweise*1 === 3) {
                $table .= "quartalsweise";
            }
            if ($zahlungsweise*1 === 4) {
                $table .= "jährlich";
            }
            $table .= "</th>\n";
        }
        $table .= "</tr>\n";
        $table .= "</thead>\n";
        $table .= "<tbody>\n";
        $table .= "<tr>\n";
        $table .= "<td style='padding: 10px; padding-left: 0px;'>".\htmlspecialchars($produkt->name)."</td>\n";

        $preis_einmalig = 0;
        $preis_laufend = 0;

        if ($zahlungsweise*1 === 1) {
            $table .= self::get_table_price($produkt->preis_einmalig_monatlich);
            $preis_einmalig += $produkt->preis_einmalig_monatlich;
        }
        if ($zahlungsweise*1 === 2) {
            $table .= self::get_table_price($produkt->preis_einmalig_monatlich, $produkt->preis_monatlich);
            $preis_einmalig += $produkt->preis_einmalig_monatlich;
            $preis_laufend += $produkt->preis_monatlich;
        }
        if ($zahlungsweise*1 === 3) {
            $table .= self::get_table_price($produkt->preis_einmalig_quartalsweise, $produkt->preis_quartalsweise);
            $preis_einmalig += $produkt->preis_einmalig_quartalsweise;
            $preis_laufend += $produkt->preis_quartalsweise;
        }
        if ($zahlungsweise*1 === 4) {
            $table .= self::get_table_price($produkt->preis_einmalig_jaehrlich, $produkt->preis_jaehrlich);
            $preis_einmalig += $produkt->preis_einmalig_jaehrlich;
            $preis_laufend += $produkt->preis_jaehrlich;
        }
        $table .= "</tr>";
        for ($i=0; $i<count($produktkonfiguration_list); $i++) {
            $orderdetails_value = self::get_orderdetails_value($orderdetails, $produktkonfiguration_list[$i]->id);
            if ($orderdetails_value !== -1) {
                $table .= "<tr>\n";
                $option = self::get_option($orderdetails_value, $produktkonfigurationoption_list);
                if ($produktkonfiguration_list[$i]->option*1 === 0) {
                    if ($produktkonfiguration_list[$i]->anzeige*1 === 2) {
                        $table .= "<td style='padding: 10px; padding-left: 0px;'>".\htmlspecialchars($option->name)."</td>\n";
                    } else {
                        $table .= "<td style='padding: 10px; padding-left: 0px;'>".\htmlspecialchars($produktkonfiguration_list[$i]->name)." : ".\htmlspecialchars($option->name)."</td>\n";
                    }
                    if ($zahlungsweise*1 === 1) {
                        $table .= self::get_table_price($option->preis_monatlich_einmalig);
                        $preis_einmalig += $option->preis_monatlich_einmalig;
                    }
                    if ($zahlungsweise*1 === 2) {
                        $table .= self::get_table_price($option->preis_monatlich_einmalig, $option->preis_monatlich);
                        $preis_einmalig += $option->preis_monatlich_einmalig;
                        $preis_laufend += $option->preis_monatlich;
                    }
                    if ($zahlungsweise*1 === 3) {
                        $table .= self::get_table_price($option->preis_quartalsweise_einmalig, $option->preis_quartalsweise);
                        $preis_einmalig += $option->preis_quartalsweise_einmalig;
                        $preis_laufend += $option->preis_quartalsweise;
                    }
                    if ($zahlungsweise*1 === 4) {
                        $table .= self::get_table_price($option->preis_jaehrlich_einmalig, $option->preis_jaehrlich);
                        $preis_einmalig += $option->preis_jaehrlich_einmalig;
                        $preis_laufend += $option->preis_jaehrlich;
                    }
                }
                if ($produktkonfiguration_list[$i]->option*1 === 1) {
                    $count = self::get_orderdetails_additional_value($orderdetails_additional, $produktkonfiguration_list[$i]->id)*1;
                    $table .= "<td style='padding: 10px; padding-left: 0px; '>$count x ".\htmlspecialchars($produktkonfiguration_list[$i]->name)."</td>\n";
                    if ($count > 0) {
                        if ($zahlungsweise*1 === 1) {
                            $table .= self::get_table_price($option->preis_monatlich_einmalig, null, $count);
                            $preis_einmalig += $option->preis_monatlich_einmalig*$count;
                        }
                        if ($zahlungsweise*1 === 2) {
                            $table .= self::get_table_price($option->preis_monatlich_einmalig, $option->preis_monatlich, $count, $count);
                            $preis_einmalig += $option->preis_monatlich_einmalig*$count;
                            $preis_laufend += $option->preis_monatlich*$count;
                        }
                        if ($zahlungsweise*1 === 3) {
                            $table .= self::get_table_price($option->preis_quartalsweise_einmalig, $option->preis_quartalsweise, $count, $count);
                            $preis_einmalig += $option->preis_quartalsweise_einmalig*$count;
                            $preis_laufend += $option->preis_quartalsweise*$count;
                        }
                        if ($zahlungsweise*1 === 4) {
                            $table .= self::get_table_price($option->preis_jaehrlich_einmalig, $option->preis_jaehrlich, $count, $count);
                            $preis_einmalig += $option->preis_jaehrlich_einmalig*$count;
                            $preis_laufend += $option->preis_jaehrlich*$count;
                        }
                    }
                }
                $table .= "</tr>\n";
            }
        }

        $table .= "</tbody>\n";
        $table .= "<tfoot>\n";

        $preistype = "";
        if ($produkt->steuer_inklusive) {
            $preistype = "Bruttogesamtbetrag";
        } else {
            $preistype = "Nettogesamtbetrag";
        }

        if ($zahlungsweise*1 === 1) {
            $table .= "<tr>\n";
            $table .= "<td style='padding: 10px; padding-left: 0px; '><b>$preistype</b></td>\n";
            $table .= self::get_table_price($preis_einmalig, null, null, null, true);
            $table .= "</tr>\n";
        }
        if ($zahlungsweise*1 > 1) {
            $table .= "<tr>\n";
            $table .= "<td style='padding: 10px; padding-left: 0px; '><b>$preistype</b></td>\n";
            $table .= self::get_table_price($preis_einmalig, $preis_laufend, null, null, true);
            $table .= "</tr>\n";
        }

        $ust_einmalig = 0;
        $ust_laufend = 0;

        $bruttogesamtbetrag_einmalig = 0;
        $bruttogesamtbetrag_laufend = 0;

        $nettogesamtbetrag_einmalig = 0;
        $nettogesamtbetrag_laufend = 0;

        $steuertext = "";
        if ($produkt->steuer_inklusive === true) {
            $bruttogesamtbetrag_einmalig = $preis_einmalig;
            $bruttogesamtbetrag_laufend = $preis_laufend;

            $ust_einmalig = $preis_einmalig/(1+$produkt->steuer_typ/100.0)*($produkt->steuer_typ/100.0);
            $ust_laufend =  $preis_einmalig/(1+$produkt->steuer_typ/100.0)*($produkt->steuer_typ/100.0);
            
            $steuertext = "inkl. Umsatzsteuer ".$produkt->steuer_typ." %";
            
            $nettogesamtbetrag_einmalig = $bruttogesamtbetrag_einmalig - $ust_einmalig;
            $nettogesamtbetrag_laufend = $bruttogesamtbetrag_laufend -  $ust_laufend;
        } else {
            $ust_einmalig = $preis_einmalig*($produkt->steuer_typ/100.0);
            $ust_laufend =  $preis_laufend*($produkt->steuer_typ/100.0);
            $steuertext = "zzgl. Umsatzsteuer ".$produkt->steuer_typ." %";
            
            $nettogesamtbetrag_einmalig = $preis_einmalig;
            $nettogesamtbetrag_laufend = $preis_laufend;

            $bruttogesamtbetrag_einmalig = $nettogesamtbetrag_einmalig + $ust_einmalig;
            $bruttogesamtbetrag_laufend = $nettogesamtbetrag_laufend + $ust_laufend;
        }
        
        if ($preis_einmalig === 1) {
            $table .= "<tr>\n";
            $table .= "<td style='padding: 10px; padding-left: 0px;'>$steuertext</td>\n";
            $table .= self::get_table_price($ust_einmalig, null);
            $table .= "</tr>\n";
        }
        if ($zahlungsweise*1 > 1) {
            $table .= "<tr>\n";
            $table .= "<td style='padding: 10px; padding-left: 0px;'>$steuertext</td>\n";
            $table .= self::get_table_price($ust_einmalig, $ust_laufend);
            $table .= "</tr>\n";
        }
        
        if ($produkt->steuer_inklusive === false) {
            if ($zahlungsweise*1 === 1) {
                $table .= "<tr>\n";
                $table .= "<td style='padding: 10px; padding-left: 0px;'><b>Bruttogesamtbetrag</b></td>\n";
                $table .= self::get_table_price($bruttogesamtbetrag_einmalig, null, null, null, true);
                $table .= "</tr>\n";
            }
            if ($zahlungsweise*1 > 1) {
                $table .= "<tr>\n";
                $table .= "<td style='padding: 10px; padding-left: 0px;'><b>Bruttogesamtbetrag</b></td>\n";
                $table .= self::get_table_price($bruttogesamtbetrag_einmalig, $bruttogesamtbetrag_laufend, null, null, true);
                $table .= "</tr>\n";
            }
        }
        $table .= "</tfooter>\n";
        $table .= "</table>";
        return array(
            "PRODUKTTABELLE" => $table,
            "NETTOGESAMTBETRAG_EINMALIG" => number_format($nettogesamtbetrag_einmalig,2,",",".")." &euro;",
            "NETTOGESAMTBETRAG_LAUFEND" => number_format($nettogesamtbetrag_laufend,2,",",".")." &euro;",
            "MWSTBETRAG_EINMALIG" => number_format($ust_einmalig,2,",",".")." &euro;",
            "MWSTBETRAG_LAUFEND" => number_format($ust_laufend,2,",",".")." &euro;",
            "BRUTTOGESAMTBETRAG_EINMALIG" => number_format($bruttogesamtbetrag_einmalig,2,",",".")." &euro;",
            "BRUTTOGESAMTBETRAG_LAUFEND" => number_format($bruttogesamtbetrag_laufend,2,",",".")." &euro;"
        );
    }

    public static function get_replace_keys($order, $produkt, $produktkonfiguration_list, $produktkonfigurationoption_list) {
        
        $bestellnrshort = explode("-", $order->bestellnr);
        
        $arr = [
            "{{ANREDE}}" => self::get_anrede($order),
            "{{ANREDE_TEXT}}" => self::get_anrede_text($order),
            "{{NAME}}" => $order->name,
            "{{PRODUKTNAME}}" => $produkt->name,
            "{{BESTELLNR}}" => $order->bestellnr,
            "{{BESTELLNRSHORT}}" => $bestellnrshort[0]
        ];

        $table = self::get_produkttabelle($order, $produkt, $produktkonfiguration_list, $produktkonfigurationoption_list);

        $keys = array_keys($table);

        for ($i=0; $i<count($keys); $i++) {
            $arr["{{".$keys[$i]."}}"] = $table[$keys[$i]];
        }

        return $arr;
    }
}