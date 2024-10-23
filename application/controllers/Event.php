<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Event extends CI_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->library('mongo_db');
        $this->load->helper('url');
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
                $data['content'] = $this->load->view('event_view', ['event' => $event], TRUE);
            } else {
                // If no event found, prepare an error message
                $data['content'] = $this->load->view('event_view', ['error' => 'Error: No event found with the provided code.'], TRUE);
            }
        }
    
        // Set the title for the layout view
        $data['title'] = 'Event Page';
        
        // Define footer buttons
        $data['footerBtn'] = ["request-a-song" => 'Request a Song'];
        
        // Load the layout view with the content data
        $this->load->view('layout', $data);
    }    
}
