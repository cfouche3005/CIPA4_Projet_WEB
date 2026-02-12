/* --- DATA --- */
const musicDB = []; // Base de données vide
const API_BASE_URL = window.location.origin + '/php/request.php';

/* --- ROUTER & NAVIGATION --- */
function navigateTo(pageName, paramsStr = "") {
    document.querySelectorAll('.spa-view').forEach(el => el.classList.remove('active'));
    const target = document.getElementById('view-' + pageName);
    if (target) {
        target.classList.add('active');
        if (paramsStr) {
            const newUrl = window.location.pathname + paramsStr;
            window.history.pushState({ path: newUrl }, '', newUrl);
        }

        const footer = document.getElementById('global-player-footer');
        // Footer visible sur Profil, Album, Artiste, Accueil
        if (['index', 'connexion', 'inscription'].includes(pageName)) {
            footer.style.display = 'none';
        } else {
            footer.style.display = 'flex';
        }

        if (pageName === 'acceuil') initAcceuil();
        if (pageName === 'album') initAlbum();
        if (pageName === 'artiste') initArtiste();
        if (pageName === 'profil') initProfile(); 
    }
}

/* --- GESTION UTILISATEUR & MODIFICATION --- */

function saveUserRegistration() {
    const nom = document.getElementById('signup-nom').value;
    const prenom = document.getElementById('signup-prenom').value;
    const email = document.getElementById('signup-email').value;
    const birth = document.getElementById('signup-birth').value;
    const isPremium = document.getElementById('signup-premium').checked;
    const bank = document.getElementById('signup-bank').value;
    const password = document.getElementById('signup-password').value;
    const pseudo = document.getElementById('signup-pseudo').value;

    if(nom && prenom && email && password && pseudo && birth) {
        const formData = new FormData();
        formData.append('lastname', nom);
        formData.append('surname', prenom);
        formData.append('mail', email);
        formData.append('password', password);
        formData.append('pseudo', pseudo);
        formData.append('birthdate', birth);

        fetch(`${API_BASE_URL}/auth/register`, {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if(data === true) {
                // Registration successful, now login or just save local user data for display
                // For now, we simulate login by saving to localStorage as before but with real data structure if needed
                // But wait, the backend doesn't return the user object on register, just true/false.
                // We can try to login immediately or just redirect to login page.
                // Let's redirect to login page for security best practice, or auto-login.
                // Given the previous code, let's auto-login by saving local data (mocking the session)
                const user = { 
                    nom: nom, 
                    prenom: prenom, 
                    email: email,
                    birth: birth,
                    isPremium: isPremium,
                    bank: bank || "",
                    pseudo: pseudo
                };
                localStorage.setItem('streamify_user', JSON.stringify(user));
                navigateTo('acceuil');
            } else if (data === "mail-exist") {
                alert("Cet email est déjà utilisé.");
            } else {
                alert("Erreur lors de l'inscription.");
            }
        })
        .catch(err => {
            console.error(err);
            alert("Erreur de connexion au serveur.");
        });
    } else {
        alert("Veuillez remplir tous les champs obligatoires.");
    }
}

function loginUser() {
    const email = document.getElementById('login-email').value;
    const password = document.getElementById('login-password').value;

    if(email && password) {
        const formData = new FormData();
        formData.append('mail', email);
        formData.append('password', password);

        fetch(`${API_BASE_URL}/auth/login`, {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if(data && data.length > 0) {
                const userDB = data[0];
                // Map DB user to local storage format
                const user = {
                    id: userDB.user_id,
                    nom: userDB.user_name,
                    prenom: userDB.user_surname,
                    email: userDB.user_mail,
                    birth: userDB.user_birthdate,
                    pseudo: userDB.user_pseudo,
                    isPremium: false, // Not in DB yet
                    bank: "" // Not in DB yet
                };
                localStorage.setItem('streamify_user', JSON.stringify(user));
                navigateTo('acceuil');
            } else {
                alert("Email ou mot de passe incorrect.");
            }
        })
        .catch(err => {
            console.error(err);
            alert("Erreur de connexion au serveur.");
        });
    }
}

// Ouvrir la Modal et pré-remplir les champs
function openEditProfile() {
    const savedUser = localStorage.getItem('streamify_user');
    if (savedUser) {
        const user = JSON.parse(savedUser);
        document.getElementById('edit-nom').value = user.nom;
        document.getElementById('edit-prenom').value = user.prenom;
        document.getElementById('edit-birth').value = user.birth !== "Non renseigné" ? user.birth : "";
        document.getElementById('edit-email').value = user.email;
        
        // Afficher la modal via Bootstrap (si disponible)
        const modalEl = document.getElementById('editProfileModal');
        if(window.bootstrap) {
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        } else {
            alert("Erreur: Bootstrap JS non chargé");
        }
    }
}

// Sauvegarder les modifications depuis la Modal
function saveProfileChanges() {
    const savedUser = localStorage.getItem('streamify_user');
    if (savedUser) {
        let user = JSON.parse(savedUser);
        
        // Mise à jour des valeurs
        const newNom = document.getElementById('edit-nom').value;
        const newPrenom = document.getElementById('edit-prenom').value;
        const newBirth = document.getElementById('edit-birth').value;
        const newEmail = document.getElementById('edit-email').value;

        // TODO: Call API to update user profile
        // For now, update local storage
        user.nom = newNom;
        user.prenom = newPrenom;
        user.birth = newBirth ? newBirth : "Non renseigné";
        user.email = newEmail;

        // Sauvegarde
        localStorage.setItem('streamify_user', JSON.stringify(user));
        
        // Fermer la modal
        const modalEl = document.getElementById('editProfileModal');
        if(window.bootstrap) {
            const modal = bootstrap.Modal.getInstance(modalEl);
            modal.hide();
        }

        // Rafraîchir l'affichage
        initProfile();
    }
}

function initProfile() {
    const savedUser = localStorage.getItem('streamify_user');
    if (savedUser) {
        const user = JSON.parse(savedUser);
        
        // Fetch fresh data from API if we have an ID
        if(user.id) {
            fetch(`${API_BASE_URL}/user/profile?id_user=${encodeURIComponent(user.id)}`)
            .then(res => res.json())
            .then(data => {
                if(data && data.length > 0) {
                    const userDB = data[0];
                    // Update local storage with fresh data
                    user.nom = userDB.user_name;
                    user.prenom = userDB.user_surname;
                    user.email = userDB.user_mail;
                    user.birth = userDB.user_birthdate;
                    user.pseudo = userDB.user_pseudo;
                    localStorage.setItem('streamify_user', JSON.stringify(user));
                    updateProfileUI(user);
                }
            })
            .catch(err => {
                console.error("Failed to fetch profile", err);
                updateProfileUI(user); // Fallback to local data
            });
        } else {
            updateProfileUI(user);
        }
    }
}

function updateProfileUI(user) {
    document.getElementById('profile-display-name').textContent = `${user.prenom} ${user.nom}`;
    document.getElementById('profile-info-name').textContent = `${user.nom} ${user.prenom}`;
    document.getElementById('profile-info-email').textContent = user.email;
    document.getElementById('profile-info-birth').textContent = user.birth;
    
    const badge = document.getElementById('profile-badge');
    const bankInfo = document.getElementById('profile-info-bank');

    if (user.isPremium) {
        badge.textContent = "Membre Premium";
        badge.className = "badge bg-warning text-dark fs-6 rounded-pill px-3";
        const cardNum = user.bank;
        const masked = cardNum && cardNum.length > 4 ? "**** **** **** " + cardNum.slice(-4) : (cardNum || "Carte enregistrée");
        bankInfo.textContent = masked;
    } else {
        badge.textContent = "Membre Gratuit";
        badge.className = "badge bg-secondary text-white fs-6 rounded-pill px-3";
        bankInfo.textContent = "Aucun moyen de paiement";
    }
}

/* --- LOGIQUE PAGES --- */

// Define global navigation helpers
window.openAlbum = function(id_album, titre, artiste, image) {
    const q = `?id=${encodeURIComponent(id_album)}&titre=${encodeURIComponent(titre)}&artiste=${encodeURIComponent(artiste)}&img=${encodeURIComponent(image)}`;
    navigateTo('album', q);
};
window.openPlaylist = function(id_playlist, titre, artiste, image) {
    const q = `?id=${encodeURIComponent(id_playlist)}&titre=${encodeURIComponent(titre)}&artiste=${encodeURIComponent(artiste)}&img=${encodeURIComponent(image)}`;
    navigateTo('album', q); 
};
window.openArtist = function(id_artist, name) {
    const q = `?id=${encodeURIComponent(id_artist)}&name=${encodeURIComponent(name)}`;
    navigateTo('artiste', q);
};

function initAcceuil() {
    // Fetch Albums
    fetch(`${API_BASE_URL}/content/albums`)
    .then(res => res.json())
    .then(albums => {
        const container = document.getElementById('albums-dynamic-container');
        if(!container) return;
        container.innerHTML = "";
        let slideIndex = 0;
        let rowContent = "";
        let itemsPerSlide = 3;
        albums.forEach((album, index) => {
            let imgHTML = album.album_image ? `<img src="${album.album_image}" class="card-img-custom">` : 
                `<div class="ratio ratio-1x1 bg-success d-flex justify-content-center align-items-center rounded mb-2"><i class="bi bi-disc display-4 text-white"></i></div>`;
            rowContent += `<div class="col-4" data-category="${album.album_type}"><div class="card border-0 bg-transparent clickable-card" onclick="openAlbum('${album.album_id}', '${album.album_name.replace(/'/g, "\\'")}', '${album.artist_pseudo.replace(/'/g, "\\'")}', '${album.album_image}')">${imgHTML}<div class="card-body text-center p-2"><h6 class="card-title text-truncate">${album.album_name}</h6><small class="text-secondary" onclick="event.stopPropagation(); openArtist('${album.artist_id}', '${album.artist_pseudo.replace(/'/g, "\\'")}')">${album.artist_pseudo}</small></div></div></div>`;
            if ((index + 1) % itemsPerSlide === 0 || index === albums.length - 1) {
                const activeClass = (slideIndex === 0) ? 'active' : '';
                container.innerHTML += `<div class="carousel-item ${activeClass}"><div class="row">${rowContent}</div></div>`;
                rowContent = "";
                slideIndex++;
            }
        });
    })
    .catch(err => {
        const container = document.getElementById('albums-dynamic-container');
        if(container) container.innerHTML = '<div class="text-center p-3 text-secondary">Impossible de charger les albums. Vérifiez que le serveur est lancé.</div>';
    });

    // Fetch Playlists
    fetch(`${API_BASE_URL}/content/playlists`)
    .then(res => res.json())
    .then(playlists => {
        const container = document.querySelector('#carouselPlaylists .carousel-inner');
        if(!container) return;
        container.innerHTML = "";
        let slideIndex = 0;
        let rowContent = "";
        let itemsPerSlide = 3;
        
        if(playlists.length === 0) {
             container.innerHTML = '<div class="text-center p-3 text-secondary">Aucune playlist disponible.</div>';
             return;
        }

        playlists.forEach((playlist, index) => {
            // Random gradient for playlist cover
            const gradients = ['bg-primary', 'bg-secondary', 'bg-success', 'bg-danger', 'bg-warning', 'bg-info', 'bg-dark'];
            const randomGradient = gradients[Math.floor(Math.random() * gradients.length)];
            
            let imgHTML = `<div class="ratio ratio-1x1 ${randomGradient} bg-gradient d-flex justify-content-center align-items-center rounded shadow-sm">
                                <div class="d-flex flex-column align-items-center justify-content-center h-100 text-white">
                                    <i class="bi bi-music-note-beamed display-4 mb-2"></i><span class="fw-bold text-uppercase fs-5">Playlist</span>
                                </div>
                           </div>`;
            
            rowContent += `<div class="col-4" data-category="playlist"><div class="card border-0 bg-transparent clickable-card" onclick="openPlaylist('${playlist.playlist_id}', '${playlist.playlist_name.replace(/'/g, "\\'")}', 'Playlist', '')">${imgHTML}<div class="card-body text-center p-2"><h6 class="card-title text-truncate">${playlist.playlist_name}</h6><small class="text-secondary">Playlist</small></div></div></div>`;
            
            if ((index + 1) % itemsPerSlide === 0 || index === playlists.length - 1) {
                const activeClass = (slideIndex === 0) ? 'active' : '';
                container.innerHTML += `<div class="carousel-item ${activeClass}"><div class="row">${rowContent}</div></div>`;
                rowContent = "";
                slideIndex++;
            }
        });
    })
    .catch(err => {
        const container = document.querySelector('#carouselPlaylists .carousel-inner');
        if(container) container.innerHTML = '<div class="text-center p-3 text-secondary">Impossible de charger les playlists.</div>';
    });

    // Fetch Random Albums (Top Hits replacement)
    fetch(`${API_BASE_URL}/content/album/random?numbers=3`)
    .then(res => res.json())
    .then(albums => {
        const container = document.querySelector('#carouselRecos .carousel-inner');
        if(!container) return;
        container.innerHTML = "";
        let slideIndex = 0;
        let rowContent = "";
        let itemsPerSlide = 3;
        
        if(albums.length === 0) {
             container.innerHTML = '<div class="text-center p-3 text-secondary">Aucun album recommandé.</div>';
             return;
        }

        albums.forEach((album, index) => {
            let imgHTML = album.album_image ? `<img src="${album.album_image}" class="card-img-custom">` : 
                `<div class="ratio ratio-1x1 bg-success d-flex justify-content-center align-items-center rounded mb-2"><i class="bi bi-disc display-4 text-white"></i></div>`;
            
            rowContent += `<div class="col-4" data-category="random"><div class="card border-0 bg-transparent clickable-card" onclick="openAlbum('${album.album_id}', '${album.album_name.replace(/'/g, "\\'")}', '${album.artist_pseudo.replace(/'/g, "\\'")}', '${album.album_image}')">${imgHTML}<div class="card-body text-center p-2"><h6 class="card-title text-truncate">${album.album_name}</h6><small class="text-secondary">${album.artist_pseudo}</small></div></div></div>`;
            
            if ((index + 1) % itemsPerSlide === 0 || index === albums.length - 1) {
                const activeClass = (slideIndex === 0) ? 'active' : '';
                container.innerHTML += `<div class="carousel-item ${activeClass}"><div class="row">${rowContent}</div></div>`;
                rowContent = "";
                slideIndex++;
            }
        });
    })
    .catch(err => {
        const container = document.querySelector('#carouselRecos .carousel-inner');
        if(container) container.innerHTML = '<div class="text-center p-3 text-secondary">Impossible de charger les recommandations.</div>';
    });
    
    // Recherche
    const search = document.getElementById('search-input');
    if(search) {
        search.onkeyup = (e) => {
            const term = e.target.value.toLowerCase();
            if(term.length < 2) {
                // Restore original view if search is cleared
                document.querySelectorAll('[data-category]').forEach(col => col.style.display = '');
                return;
            }

            fetch(`${API_BASE_URL}/content/search?q=${encodeURIComponent(term)}`)
            .then(res => res.json())
            .then(results => {
                // We need to display these results. 
                // The current UI structure is carousel-based which is hard to filter in-place with new data.
                // A simple approach is to hide everything and show a search result container, 
                // or replace the content of the main container.
                // Given the current structure, let's replace the "Parcourir les Albums" content temporarily.
                
                const container = document.getElementById('albums-dynamic-container');
                container.innerHTML = "";
                
                if(results.length === 0) {
                    container.innerHTML = '<div class="text-center p-3 text-secondary">Aucun résultat trouvé.</div>';
                    return;
                }

                let rowContent = "";
                // Display all results in one slide for simplicity or paginate
                results.forEach((album) => {
                    let imgHTML = album.album_image ? `<img src="${album.album_image}" class="card-img-custom">` : 
                        `<div class="ratio ratio-1x1 bg-success d-flex justify-content-center align-items-center rounded mb-2"><i class="bi bi-disc display-4 text-white"></i></div>`;
                    rowContent += `<div class="col-4"><div class="card border-0 bg-transparent clickable-card" onclick="openAlbum('${album.album_id}', '${album.album_name.replace(/'/g, "\\'")}', '${album.artist_pseudo.replace(/'/g, "\\'")}', '${album.album_image}')">${imgHTML}<div class="card-body text-center p-2"><h6 class="card-title text-truncate">${album.album_name}</h6><small class="text-secondary">${album.artist_pseudo}</small></div></div></div>`;
                });
                
                container.innerHTML = `<div class="carousel-item active"><div class="row">${rowContent}</div></div>`;
            });
        };
    }
}

function initAlbum() {
    const params = new URLSearchParams(window.location.search);
    const id_album = params.get('id');
    const titre = params.get('titre') || "Album";
    const artiste = params.get('artiste') || "Artiste";
    const img = params.get('img');
    document.getElementById('dynamic-album-title').innerText = titre;
    const artistEl = document.getElementById('dynamic-album-artist');
    artistEl.innerText = artiste;
    const imgEl = document.getElementById('dynamic-album-cover');
    const placeholder = document.getElementById('album-placeholder');
    if (img && img !== "undefined" && img !== "") { imgEl.src = img; imgEl.style.display = 'block'; placeholder.style.display = 'none'; } 
    else { imgEl.style.display = 'none'; placeholder.style.display = 'flex'; }

    if (!id_album) return;

    // Determine if it's an album or playlist based on context or try both?
    // Ideally we should have different views or a type param.
    // For now, let's try fetching as album first, if empty/error, try playlist.
    // Or better, check if the ID looks like a UUID (both are UUIDs though).
    // Let's assume the caller knows. But here we reused 'album' view.
    // Let's try to fetch album tracks first.
    
    fetch(`${API_BASE_URL}/content/album?id_album=${encodeURIComponent(id_album)}`)
    .then(res => res.json())
    .then(data => {
        if(data && data.musics && data.musics.length > 0) {
            renderTracks(data.musics, img);
            
            // Add artist link if album info is present
            if (data.album && data.album.length > 0) {
                const alb = data.album[0];
                if (artistEl && alb.artist_id) {
                    artistEl.innerText = alb.artist_pseudo;
                    artistEl.style.cursor = 'pointer';
                    artistEl.style.textDecoration = 'underline';
                    artistEl.onclick = () => openArtist(alb.artist_id, alb.artist_pseudo);
                }
            }
        } else {
            // Try fetching as playlist
             fetch(`${API_BASE_URL}/content/playlist?id_playlist=${encodeURIComponent(id_album)}`)
            .then(res => res.json())
            .then(data => {
                if(data && data.musics) {
                    renderTracks(data.musics, img);
                } else {
                     document.getElementById('track-container').innerHTML = '<div class="p-5 text-center text-secondary">Aucune musique trouvée.</div>';
                }
            });
        }
    })
    .catch(err => { document.getElementById('track-container').innerHTML = `<div class="p-5 text-center text-danger">Erreur de connexion au serveur.</div>`; });
}

function renderTracks(tracks, img) {
    const container = document.getElementById('track-container');
    container.innerHTML = "";
    if(tracks.length === 0) {
        container.innerHTML = '<div class="p-5 text-center text-secondary">Aucune musique trouvée.</div>';
        return;
    }
    tracks.forEach((track, idx) => {
        const div = document.createElement('div');
        div.className = 'track-grid-row track-item';
        // Assuming track.artists is an array of artists, we join their names
        const artistNames = track.artists ? track.artists.map(a => a.artist_pseudo).join(', ') : "Artiste Inconnu";
        div.innerHTML = `<div class="col-index">${idx+1}</div><div class="col-title">${track.music_title}</div><div class="col-artist text-secondary">${artistNames}</div><div class="col-duration">${track.music_duration||"-:-"}</div>`;
        
        div.onclick = () => {
             const playerTracks = tracks.map(t => ({
                title: t.music_title,
                artist: t.artists ? t.artists.map(a => a.artist_pseudo).join(', ') : "Artiste Inconnu",
                src: t.lien_music,
                cover: img // Use album/playlist cover for now
            }));
            launchAlbum(playerTracks, idx);
        };
        container.appendChild(div);
    });
    
    document.getElementById('btn-play-all').onclick = () => {
         const playerTracks = tracks.map(t => ({
            title: t.music_title,
            artist: t.artists ? t.artists.map(a => a.artist_pseudo).join(', ') : "Artiste Inconnu",
            src: t.lien_music,
            cover: img
        }));
        playRandomAlbum(playerTracks);
    };
}

function initArtiste() {
    const params = new URLSearchParams(window.location.search);
    let id_artist = params.get('id');
    let name = params.get('name') || "Artiste Inconnu";
    document.getElementById('dynamic-artist-name').innerText = name;
    
    if (!id_artist) {
        document.getElementById('artist-albums-container').innerHTML = `<div class="col-12 text-center text-secondary py-5">Artiste non trouvé.</div>`;
        return;
    }

    fetch(`${API_BASE_URL}/content/artist?id_artist=${encodeURIComponent(id_artist)}`)
    .then(res => res.json())
    .then(data => {
        const container = document.getElementById('artist-albums-container');
        container.innerHTML = "";
        
        if (!data || !data.albums || data.albums.length === 0) {
             container.innerHTML = `<div class="col-12 text-center text-secondary py-5">Aucun album trouvé.</div>`; 
             return; 
        }
        
        // Update artist info if available from API
        if(data.artist && data.artist.artist_pseudo) {
            document.getElementById('dynamic-artist-name').innerText = data.artist.artist_pseudo;
            if(data.artist.artist_image) {
                 const banner = document.getElementById('artist-banner-bg');
                 // Use artist image as banner background if available, or fallback
                 // Note: artist_image is a filename, need full path logic if not handled in PHP
                 // Assuming PHP returns full URL or we construct it. 
                 // Artist.php returns full URL in photo_art but info_artiste returns raw row.
                 // Let's assume we need to construct it or it's just a filename.
                 // For now, let's just use the name.
            }
        }

        data.albums.forEach(alb => {
            const div = document.createElement('div');
            div.className = 'col-6 col-md-3';
            // Construct image URL if it's just a filename, or use if it's a full URL
            // Album.php list_alb constructs it. info_artiste returns raw row.
            // We need to handle this. Let's assume we need to construct it.
            let imgUrl = alb.album_image;
            if(imgUrl && !imgUrl.startsWith('http')) {
                imgUrl = `https://music.cfouche.fr/${alb.album_id}/${alb.album_image}`;
            }
            
            let img = imgUrl ? `<img src="${imgUrl}">` : '<div class="bg-success text-white p-5 mb-2 rounded">CD</div>';
            div.innerHTML = `<div class="card-album-custom h-100">${img}<h6 class="text-white mt-2">${alb.album_name}</h6></div>`;
            div.onclick = () => navigateTo('album', `?id=${encodeURIComponent(alb.album_id)}&titre=${encodeURIComponent(alb.album_name)}&artiste=${encodeURIComponent(data.artist.artist_pseudo)}&img=${encodeURIComponent(imgUrl)}`);
            container.appendChild(div);
        });
    }).catch(e => { 
        console.error(e);
        document.getElementById('artist-albums-container').innerHTML = `<div class="col-12 text-center text-danger">Erreur serveur.</div>`; 
    });
}

/* STARTUP */
document.addEventListener('DOMContentLoaded', () => {
    // Appel à initPlayer défini dans player.js (disponible car inclus avant ou après)
    if(typeof initPlayer === 'function') initPlayer();
    
    const loginForm = document.getElementById('login-form');
    if(loginForm) loginForm.onsubmit = (e) => { 
        e.preventDefault(); 
        loginUser();
    };
    const signupForm = document.getElementById('signup-form');
    
    if(signupForm) {
        const premiumCheck = document.getElementById('signup-premium');
        const bankGroup = document.getElementById('bank-group');
        if(premiumCheck) {
            premiumCheck.addEventListener('change', (e) => {
                bankGroup.style.display = e.target.checked ? 'block' : 'none';
            });
        }
        signupForm.onsubmit = (e) => { 
            e.preventDefault(); 
            saveUserRegistration(); 
        };
    }
    
    navigateTo('index');
});