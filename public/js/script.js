document.addEventListener('DOMContentLoaded', function() {
    const joinEventBtn = document.getElementById('join-event');
    const requestSongBtn = document.getElementById('request-a-song');
    const songSearchInput = document.getElementById('song-search');
    const suggestionsList = document.getElementById('suggestions');
    const loadingIndicator = document.getElementById('loading');
    const selectedSongsList = document.getElementById('selected-songs');
    const confirmBtn = document.getElementById('confirm');
    const sendRequestBtn = document.getElementById('send-request');
    const selectAnotherSongBtn = document.getElementById('select-another-song');

    // Array to hold selected songs
    let selectedSongs = [];

    // Load previously selected songs from localStorage
    const storedSongs = localStorage.getItem('selectedSongs');
    if (storedSongs) {
        selectedSongs = JSON.parse(storedSongs);
        updateSelectedSongsDisplay();
    }

    if (joinEventBtn) {
        joinEventBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            const codeInput = document.getElementById('code');
            
            if (codeInput && codeInput.value.trim() !== '') {
                const eventCode = codeInput.value.trim();
                
                // Store the event code in localStorage
                localStorage.setItem('eventCode', eventCode);
                
                // Redirect to the event page with the code as a query parameter
                window.location.href = `/event?code=${encodeURIComponent(eventCode)}`;
            } else {
                alert('Please enter a valid event code.');
            }
        });
    }

    if (requestSongBtn) {
        requestSongBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Retrieve the event code from localStorage
            const eventCode = localStorage.getItem('eventCode');
            
            if (eventCode) {
                // Redirect to the songs page with the code as a query parameter
                window.location.href = `/songs?code=${encodeURIComponent(eventCode)}`;
            } else {
                alert('No event code found. Please join an event first.');
            }
        });
    }

    if (songSearchInput) {
        songSearchInput.addEventListener('input', function() {
            const query = this.value;
            if (query.length < 2) {
                suggestionsList.style.display = 'none';
                loadingIndicator.style.display = 'none';
                return;
            }

            loadingIndicator.style.display = 'block';

            fetch(`songs/search?search=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    suggestionsList.innerHTML = '';
                    loadingIndicator.style.display = 'none';

                    if (data.length > 0) {
                        data.forEach(song => {
                            const highlightedName = highlightMatch(song.name, query);
                            const highlightedArtist = highlightMatch(song.artist, query);
                            
                            const li = document.createElement('li');
                            li.className = 'list-group-item list-group-item-action d-flex align-items-center';
                            li.innerHTML = `
                                <img src="${song.image}" alt="${song.name} album art" class="img-thumbnail mr-3" style="width: 50px; height: 50px;">
                                <div>
                                    ${highlightedName} - ${highlightedArtist}
                                </div>
                            `;
                            li.onclick = () => selectSong(song);
                            suggestionsList.appendChild(li);
                        });
                        suggestionsList.style.display = 'block';
                    } else {
                        suggestionsList.style.display = 'none';
                    }
                })
                .catch(error => {
                    console.error('Error fetching songs:', error);
                    loadingIndicator.style.display = 'none';
                });
        });
    }

    if (confirmBtn) {
        confirmBtn.addEventListener('click', function(event) {
            event.preventDefault(); // Prevent form submission if it's within a form

            if (selectedSongs.length > 0) {
                // Store selected songs in localStorage
                localStorage.setItem('selectedSongs', JSON.stringify(selectedSongs));

                // Get the event code from localStorage
                const eventCode = localStorage.getItem('eventCode');

                if (eventCode) {
                    // Redirect to the confirm page with the event code
                    window.location.href = `/confirm?code=${encodeURIComponent(eventCode)}`;
                } else {
                    alert('No event code found. Please join an event first.');
                }
            } else {
                alert('Please select at least one song before confirming.');
            }
        });
    }

    if (sendRequestBtn) {
        sendRequestBtn.addEventListener('click', function(event) {
            event.preventDefault();

            const selectedSongs = JSON.parse(localStorage.getItem('selectedSongs') || '[]');
            const eventCode = localStorage.getItem('eventCode');

            if (selectedSongs.length > 0 && eventCode) {
                // Send selected songs to the server
                fetch(`/confirm/save?code=${encodeURIComponent(eventCode)}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(selectedSongs),
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Songs successfully saved!');
                        // Clear selected songs from localStorage
                        localStorage.removeItem('selectedSongs');
                        // Redirect to a thank you page or back to the event page
                        window.location.href = `/finish?code=${encodeURIComponent(eventCode)}`;
                    } else {
                        alert('Error saving songs. Please try again.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                });
            } else {
                alert('No songs selected or event code missing. Please try again.');
            }
        });
    }

    if (selectAnotherSongBtn) {
        selectAnotherSongBtn.addEventListener('click', function(event) {
            event.preventDefault();

            const eventCode = localStorage.getItem('eventCode');
            if (eventCode) {
                window.location.href = `/songs?code=${encodeURIComponent(eventCode)}`;
            } else {
                alert('No event code found. Please join an event first.');
            }
        });
    }

    // Helper functions
    function highlightMatch(text, query) {
        const regex = new RegExp(`(${query})`, 'gi');
        return text.replace(regex, '<span class="highlight">$1</span>');
    }

    function selectSong(song) {
        if (!selectedSongs.some(s => s.name === song.name && s.artist === song.artist)) {
            selectedSongs.push(song);

            const listItem = document.createElement('li');
            listItem.className = 'list-group-item';
            listItem.innerHTML = `
                <div class="d-flex align-items-center">
                    <img src="${song.image}" alt="${song.name} album art" class="img-thumbnail mr-3" style="width: 60px; height: 60px;">
                    <div class="song-details">
                        <strong class="song-name">${song.name}</strong>
                        <span class="song-artist">${song.artist}</span>
                        <span class="song-album">Album: ${song.album}</span>
                        <span class="song-release">Released: ${song.release_date}</span>
                    </div>
                </div>
            `;
            selectedSongsList.appendChild(listItem);
            updateSelectedSongsDisplay();
        }

        songSearchInput.value = '';
        suggestionsList.style.display = 'none';
    }

    function updateSelectedSongsDisplay() {
        selectedSongsList.innerHTML = '';

        selectedSongs.forEach(song => {
            const li = document.createElement('li');
            li.className = 'list-group-item';
            li.innerHTML = `
                <div class="d-flex align-items-center">
                    <img src="${song.image}" alt="${song.name} album art" class="img-thumbnail mr-3" style="width: 60px; height: 60px;">
                    <div class="song-details">
                        <strong class="song-name">${song.name}</strong>
                        <span class="song-artist">${song.artist}</span>
                        <span class="song-album">Album: ${song.album}</span>
                        <span class="song-release">Released: ${song.release_date}</span>
                    </div>
                </div>
            `;
            selectedSongsList.appendChild(li);
        });

        selectedSongsList.style.display = selectedSongs.length > 0 ? 'block' : 'none';
    }
});
