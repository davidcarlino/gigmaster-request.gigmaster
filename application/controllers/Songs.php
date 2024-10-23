<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Songs extends CI_Controller {

    private $clientId = 'b72ad25967b148f6849d6c48be955495';
    private $clientSecret = 'f42ec53cb9054527830b6d5d28547f89';
    private $baseUrl = 'https://api.spotify.com/v1/';

    public function index() {
        $this->load->library('mongo_db');
        
        $collection = $this->mongo_db->get_collection('songRequests');
        $songs = $collection->find()->toArray();
        
        // Load content view
        $data['content'] = $this->load->view('songs_list', ['songs' => $songs], TRUE);
        
        // Load layout view
        $data['title'] = 'Songs List'; 
        $data['footerBtn'] = ['confirm' => 'Confirm'];
        $this->load->view('layout', $data);
    }

    // Function to fetch Spotify access token
    private function getSpotifyAccessToken() {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://accounts.spotify.com/api/token");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Basic ' . base64_encode($this->clientId . ':' . $this->clientSecret),
            'Content-Type: application/x-www-form-urlencoded'
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);
        return $data['access_token'];
    }

    // Function to search songs from Spotify API
    // Search songs using Spotify API
    public function searchSongs($query) {
        $accessToken = $this->getSpotifyAccessToken();

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . "search?q=" . urlencode($query) . "&type=track&limit=5");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $songs = json_decode($response, true)['tracks']['items'];
        return $songs;
    }

    // Main function to handle the search request
    public function search() {
        $query = $this->input->get('search');
        
        if ($query) {
            $songs = $this->searchSongs($query);

            $songData = [];
            foreach ($songs as $song) {
                $name = $song['name'];
                $artist = implode(', ', array_map(fn($artist) => $artist['name'], $song['artists']));
                $album = $song['album']['name'];
                $releaseDate = $song['album']['release_date'];
                $images = $song['album']['images']; // Get album images

                // Get the first image if available
                $imageUrl = !empty($images) ? $images[0]['url'] : null;

                $songData[] = [
                    'name' => $name,
                    'artist' => $artist,
                    'album' => $album,
                    'release_date' => $releaseDate,
                    'image' => $imageUrl, // Add image URL to the response
                ];
            }

            // Return the response as JSON
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($songData));
        } else {
            show_404(); // Show a 404 page if no search query is provided
        }
    }
}

// class Songs extends CI_Controller {

//     public function index() {
//         $this->load->library('mongo_db');
        
//         $collection = $this->mongo_db->get_collection('songRequests');
//         $songs = $collection->find()->toArray();
        
//         // Load content view
//         $data['content'] = $this->load->view('songs_list', ['songs' => $songs], TRUE);
        
//         // Load layout view
//         $data['title'] = 'Songs List'; 
//         $data['footerBtn'] = ['Confirm'];
//         $this->load->view('layout', $data);
//     }
// }