/* --- PLAYER AUDIO LOGIC --- */
let audioPlayer = new Audio();
let isPlaying = false;
let currentPlaylist = []; 
let currentIndex = 0;

function initPlayer() {
    const btnPlay = document.getElementById('player-play-btn');
    const btnNext = document.getElementById('player-next-btn');
    const btnPrev = document.getElementById('player-prev-btn');

    if (btnPlay) btnPlay.onclick = togglePlayPause;
    if (btnNext) btnNext.onclick = playNext;
    if (btnPrev) btnPrev.onclick = playPrev;

    audioPlayer.addEventListener('timeupdate', updateProgress);
    audioPlayer.addEventListener('ended', playNext);
}

function togglePlayPause() {
    if (currentPlaylist.length === 0) return;
    if (audioPlayer.paused) { audioPlayer.play(); isPlaying = true; } 
    else { audioPlayer.pause(); isPlaying = false; }
    updateInterface();
}

function playNext() {
    if (currentIndex < currentPlaylist.length - 1) playTrack(currentIndex + 1);
    else playTrack(0);
}

function playPrev() {
    if (audioPlayer.currentTime > 3) audioPlayer.currentTime = 0;
    else if (currentIndex > 0) playTrack(currentIndex - 1);
}

function playTrack(index) {
    if (index < 0 || index >= currentPlaylist.length) return;
    currentIndex = index;
    const track = currentPlaylist[currentIndex];
    
    if (audioPlayer.src !== track.src) {
        audioPlayer.src = track.src;
    }
    audioPlayer.play();
    isPlaying = true;
    
    document.getElementById('player-track-title').innerText = track.title;
    document.getElementById('player-track-artist').innerText = track.artist;
    if(track.cover) document.getElementById('player-track-img').src = track.cover;
    
    updateInterface();
    document.querySelectorAll('.track-item').forEach((row, idx) => {
        if(idx === index) row.classList.add('playing');
        else row.classList.remove('playing');
    });
}

function updateInterface() {
    const icon = document.getElementById('player-play-icon');
    if (icon) icon.className = isPlaying ? 'bi bi-pause-fill fs-5' : 'bi bi-play-fill fs-5 ms-1';
}

function updateProgress() {
    const progressBar = document.getElementById('player-progress-bar');
    if (audioPlayer.duration) {
        const percent = (audioPlayer.currentTime / audioPlayer.duration) * 100;
        if (progressBar) progressBar.style.width = percent + '%';
        document.getElementById('player-current-time').innerText = formatTime(audioPlayer.currentTime);
        document.getElementById('player-total-time').innerText = formatTime(audioPlayer.duration);
    }
}

function formatTime(s) {
    let m = Math.floor(s / 60);
    let sec = Math.floor(s % 60);
    return m + ':' + (sec < 10 ? '0' : '') + sec;
}

// Fonctions globales appelées depuis le HTML ou app.js
window.launchAlbum = function(tracks, index) {
    currentPlaylist = tracks;
    playTrack(index);
};

window.playRandomAlbum = function(tracks) {
     let mixed = [...tracks].sort(() => Math.random() - 0.5);
     launchAlbum(mixed, 0);
};