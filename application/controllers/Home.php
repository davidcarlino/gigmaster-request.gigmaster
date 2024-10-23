<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends CI_Controller {

    public function index() {
        // Load content view
        $data['content'] = $this->load->view('home_view', [], TRUE);
        
        // Load layout view
        $data['title'] = 'Home Page';
        $data['footerBtn'] = ["join-event" =>'Join Event'];
        $this->load->view('layout', $data);
    }
}