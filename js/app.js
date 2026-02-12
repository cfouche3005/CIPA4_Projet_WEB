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
                    id: userDB.User_ID,
                    nom: userDB.User_Name,
                    prenom: userDB.User_Surname,
                    email: userDB.User_Mail,
                    birth: userDB.User_birthdate,
                    pseudo: userDB.User_Pseudo,
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
}

/* --- LOGIQUE PAGES --- */

function initAcceuil() {
    window.openAlbum = function(id_album, titre, artiste, image) {
        const q = `?id=${encodeURIComponent(id_album)}&titre=${encodeURIComponent(titre)}&artiste=${encodeURIComponent(artiste)}&img=${encodeURIComponent(image)}`;
        navigateTo('album', q);
    };
    window.openPlaylist = window.openAlbum;

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
            let imgHTML = album.Album_Image ? `<img src="${album.Album_Image}" class="card-img-custom">` : 
                `<div class="ratio ratio-1x1 bg-success d-flex justify-content-center align-items-center rounded mb-2"><i class="bi bi-disc display-4 text-white"></i></div>`;
            rowContent += `<div class="col-4" data-category="${album.Album_Type}"><div class="card border-0 bg-transparent clickable-card" onclick="openAlbum('${album.Album_ID}', '${album.Album_Name.replace(/'/g, "\\'")}', '${album.Artist_Pseudo.replace(/'/g, "\\'")}', '${album.Album_Image}')">${imgHTML}<div class="card-body text-center p-2"><h6 class="card-title text-truncate">${album.Album_Name}</h6><small class="text-secondary">${album.Artist_Pseudo}</small></div></div></div>`;
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
    
    // Recherche
    const search = document.getElementById('search-input');
    if(search) {
        search.onkeyup = (e) => {
            const term = e.target.value.toLowerCase();
            document.querySelectorAll('[data-category]').forEach(col => {
                const txt = col.innerText.toLowerCase();
                col.style.display = txt.includes(term) ? '' : 'none';
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
    document.getElementById('dynamic-album-artist').innerText = artiste;
    const imgEl = document.getElementById('dynamic-album-cover');
    const placeholder = document.getElementById('album-placeholder');
    if (img && img !== "undefined" && img !== "") { imgEl.src = img; imgEl.style.display = 'block'; placeholder.style.display = 'none'; } 
    else { imgEl.style.display = 'none'; placeholder.style.display = 'flex'; }

    if (!id_album) return;

    fetch(`${API_BASE_URL}/content/album?id_album=${encodeURIComponent(id_album)}`)
    .then(res => res.json())
    .then(data => {
        const tracks = data.musics;
        const container = document.getElementById('track-container');
        container.innerHTML = "";
        if(tracks.length === 0) container.innerHTML = '<div class="p-5 text-center text-secondary">Aucune musique trouvée.</div>';
        tracks.forEach((track, idx) => {
            const div = document.createElement('div');
            div.className = 'track-grid-row track-item';
            // Assuming track.artists is an array of artists, we join their names
            const artistNames = track.artists.map(a => a.Artist_Pseudo).join(', ');
            div.innerHTML = `<div class="col-index">${idx+1}</div><div class="col-title">${track.Music_Title}</div><div class="col-artist text-secondary">${artistNames}</div><div class="col-duration">${track.Music_Duration||"-:-"}</div>`;
            
            // Prepare track object for player (mapping API response to player expected format)
            const playerTrack = {
                title: track.Music_Title,
                artist: artistNames,
                src: track.lien_music,
                cover: img // Use album cover for now
            };
            
            // We need to pass the full list of tracks formatted for the player to launchAlbum
            // But launchAlbum expects the original tracks array and index. 
            // We should probably adapt launchAlbum or map the tracks here.
            // Let's map all tracks first.
            
            div.onclick = () => {
                 const playerTracks = tracks.map(t => ({
                    title: t.Music_Title,
                    artist: t.artists.map(a => a.Artist_Pseudo).join(', '),
                    src: t.lien_music,
                    cover: img
                }));
                launchAlbum(playerTracks, idx);
            };
            container.appendChild(div);
        });
        
        document.getElementById('btn-play-all').onclick = () => {
             const playerTracks = tracks.map(t => ({
                title: t.Music_Title,
                artist: t.artists.map(a => a.Artist_Pseudo).join(', '),
                src: t.lien_music,
                cover: img
            }));
            playRandomAlbum(playerTracks);
        };
    })
    .catch(err => { document.getElementById('track-container').innerHTML = `<div class="p-5 text-center text-danger">Erreur de connexion au serveur.</div>`; });
}

function initArtiste() {
    const params = new URLSearchParams(window.location.search);
    let name = params.get('name') || "Artiste Inconnu";
    document.getElementById('dynamic-artist-name').innerText = name;
    // Note: The API for artist info by name is not directly available in the request.php switch case for GET /user/artist (it's POST).
    // However, we have /content/albums which returns all albums with artist names.
    // We can filter client side as before, or implement a proper GET /content/artist endpoint.
    // For now, let's keep using the /content/albums endpoint and filter.
    
    fetch(`${API_BASE_URL}/content/albums`).then(res => res.json()).then(all => {
        const container = document.getElementById('artist-albums-container');
        container.innerHTML = "";
        const artistAlbums = all.filter(a => a.Artist_Pseudo.toLowerCase().includes(name.toLowerCase()));
        if (artistAlbums.length === 0) { container.innerHTML = `<div class="col-12 text-center text-secondary py-5">Aucun album trouvé.</div>`; return; }
        artistAlbums.forEach(alb => {
            const div = document.createElement('div');
            div.className = 'col-6 col-md-3';
            let img = alb.Album_Image ? `<img src="${alb.Album_Image}">` : '<div class="bg-success text-white p-5 mb-2 rounded">CD</div>';
            div.innerHTML = `<div class="card-album-custom h-100">${img}<h6 class="text-white mt-2">${alb.Album_Name}</h6></div>`;
            div.onclick = () => navigateTo('album', `?id=${encodeURIComponent(alb.Album_ID)}&titre=${encodeURIComponent(alb.Album_Name)}&artiste=${encodeURIComponent(alb.Artist_Pseudo)}&img=${encodeURIComponent(alb.Album_Image)}`);
            container.appendChild(div);
        });
    }).catch(e => { document.getElementById('artist-albums-container').innerHTML = `<div class="col-12 text-center text-danger">Erreur serveur.</div>`; });
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