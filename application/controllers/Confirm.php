<?php
defined('BASEPATH') OR exit('No direct script access allowed');
use MongoDB\BSON\UTCDateTime;

class Confirm extends CI_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->library('mongo_db');
    }

    public function index() {
        $data['content'] = $this->load->view('confirm_view', [], TRUE);
        $data['title'] = 'Confirm Page';
        $data['footerBtn'] = ['send-request' => 'Send Request', 'select-another-song' => 'Select Another Song'];
        $this->load->view('layout', $data);
    }

    public function save() {
        // Define the events collection
        $eventsCollection = $this->mongo_db->get_collection('events');

        // Get raw input data
        $data = json_decode($this->input->raw_input_stream, true);
        $eventCode = $this->input->get('code');
        log_message('info', 'Raw input data: ' . print_r($data, true));
        log_message('info', 'Event code: ' . $eventCode);

        if (is_array($data) && count($data) > 0) {
            // Fetch the event ID based on the event code
            $eventId = null;
            if ($eventCode) {
                $event = $eventsCollection->findOne(['eventCode' => $eventCode]);
                $eventId = $event ? $event->_id : null;
            }
            $songsCollection = $this->mongo_db->get_collection('songrequests');
            log_message('info', 'Event ID: ' . $eventId);

            foreach ($data as $song) {
                // Insert each song into the MongoDB collection
                $updateResult = $songsCollection->updateOne(
                    ['name' => $song['name'], 'artist' => $song['artist']],
                    [
                        '$setOnInsert' => [
                            'name' => $song['name'],
                            'artist' => $song['artist'],
                            'album' => $song['album'],
                            'releaseDate' => $song['release_date'],
                            'image' => $song['image'],
                            'event' => $eventId,
                            'createdAt' => new UTCDateTime(),
                        ],
                        '$set' => [
                            'updatedAt' => new UTCDateTime(),
                        ],
                    ],
                    ['upsert' => true]
                );

                // If the song was newly inserted, update the Event's songRequests
                if ($updateResult->getUpsertedCount() > 0) {
                    $songInsertedId = $updateResult->getUpsertedId();
                    if ($eventId) {
                        $eventsCollection->updateOne(
                            ['_id' => $eventId],
                            ['$addToSet' => ['songRequests' => $songInsertedId]]
                        );
                    }
                }
            }

            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => true]));
        } else {
            $this->output
                ->set_content_type('application/json')
                ->set_status_header(400)
                ->set_output(json_encode(['success' => false, 'message' => 'Invalid input data.']));
        }
    }
}
