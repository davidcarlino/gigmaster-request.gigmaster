<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Finish extends CI_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->library('mongo_db');
    }

    public function index() {
        // Retrieve the 'code' query parameter with XSS filtering
        $code = $this->input->get('code', TRUE); 
    
        // Initialize an empty event array
        $event = null;
    
        // Check if the code is provided
        if (!$code) {
            // If no code is provided, prepare an error message
            $data['content'] = $this->load->view('event_view', ['error' => 'Error: Event code is required.'], TRUE);
        } else {
            // Fetch the events collection from MongoDB
            $collection = $this->mongo_db->get_collection('events');
            
            // Search for the event using the provided code
            $event = $collection->findOne(["eventCode" => htmlspecialchars($code)]); // Changed to findOne for a single result
    
            // Check if the event was found
            if ($event) {
                // Load the view with the event data
                $data['content'] = $this->load->view('finish_view', ['event' => $event], TRUE);
            } else {
                // If no event found, prepare an error message
                $data['content'] = $this->load->view('finish_view', ['error' => 'Error: No event found with the provided code.'], TRUE);
            }
        }

        // Load layout view
        $data['title'] = 'Finish';
        $data['footerBtn'] = [];
        $this->load->view('layout', $data);
    }
}