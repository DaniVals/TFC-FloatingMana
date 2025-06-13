document.addEventListener('DOMContentLoaded', () => {
	
	const github_AJAX = document.getElementById('development-team').getAttribute('data-AJAX')
	const badges = document.getElementsByClassName('profile-badge');

	for (let i = 0; i < badges.length; i++) {
		const badge = badges[i];
		const username = badge.getAttribute('data-username');
		console.log(badge);
		
		
		const xhr = new XMLHttpRequest();
		xhr.open('GET', github_AJAX + username);
		xhr.setRequestHeader('Content-Type', 'application/json');
		xhr.onload = function () {
			if (xhr.status === 200) {
				const data = JSON.parse(xhr.responseText);

				const name = badge.querySelector('#profile-name');
				console.log(name);
				if (name) {
					name.innerHTML = data['name'];
				}
				
				const img = badge.querySelector('#profile-photo');
				console.log(img);
				if (name) {
					img.src = data['avatar_url'];
				}
			}
		};
			
		xhr.onerror = function () { console.error('No se ha podido cargar el perfil: ', username);};
		xhr.send();

	}
});