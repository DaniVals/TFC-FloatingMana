document.addEventListener('DOMContentLoaded', function() {
	document.getElementById('sideHeaderButtonClose').addEventListener('click', closeSideHeader);
	document.getElementById('sideHeaderButtonOpen').addEventListener('click', openSideHeader);

	// cerrar si hace click fuera
	const headerBg = document.getElementById('open-header-background');
	if (headerBg) {
		headerBg.addEventListener('click', (event) => {
			if (event.target === headerBg) {
				closeSideHeader();
			}
		});
	}
});


function openSideHeader() {
	document.querySelector('header').setAttribute('data-is-open', '1');
}


function closeSideHeader() {
	document.querySelector('header').setAttribute('data-is-open', '0');
}
