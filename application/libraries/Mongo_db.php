<?php

use MongoDB\Client;

class Mongo_db {
    protected $CI;
    protected $client;
    protected $db;

    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->config->load('mongodb');
        
        $mongo_uri = $this->CI->config->item('mongo_uri');
        $dbname = $this->CI->config->item('mongo_db');

        $this->client = new Client($mongo_uri);
        $this->db = $this->client->{$dbname};
    }

    public function get_collection($collection) {
        return $this->db->{$collection};
    }
}
