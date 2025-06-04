document.addEventListener('DOMContentLoaded', function () {
	// Tu código aquí se ejecutará cuando el documento esté completamente cargado
	console.log('Documento cargado y listo.');

	// TODO: sacar cartas del carrusel desde AJAX
});

var intervalCarrouselMoving = null;
var carrouselPos = 0;

function changeIndex(change) {
	carrouselPos += parseInt(change, 10);
	
	if (carrouselPos < 0) {
		carrouselPos = 0;
	} 
	else if (document.getElementById('elements').children.length <= carrouselPos) {
		carrouselPos = document.getElementById('elements').children.length-1;
	} 
	else {	
		console.log("cambiando index de carrusel a: ", carrouselPos);
		intervalCarrouselMoving = setInterval(carrouselMoving, 10);
	}
}

function carrouselMoving() {
	console.log("moving");
	
	const elements = document.getElementById('elements');
	const targetScroll = carrouselPos * elements.children[0].offsetWidth;

	const current = elements.scrollLeft;
	const distance = targetScroll - current;
	const step = distance * 0.15; // Ajusta el factor para suavidad

	console.log(current, distance, step, Math.abs(distance));
	
	if (Math.abs(distance) > 3) {
		document.getElementById('elements').scrollLeft = current + step;
		console.log("CP1");
		
	} else {
		document.getElementById('elements').scrollLeft = targetScroll;
		clearInterval(intervalCarrouselMoving);
	}

}