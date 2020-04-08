<?php

namespace PKonfigurator\Runner;

class MailConfig {
    public $host;
    public $username;
    public $password;
    public $ssl;
    public $port;

    public function __construct($host, $username, $password, $ssl, $port) {
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->ssl = $ssl;
        $this->port = $port;
    }
}