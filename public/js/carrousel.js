document.addEventListener('DOMContentLoaded', function () {
	
	const CARROUSEL = document.getElementById('carrousel');
	if (!CARROUSEL) {
		return;
	}
	
	const ELEMENTS = document.getElementById('elements');
	const VALUABLE_CARD_AJAX_ROUTE = CARROUSEL.getAttribute('data-AJAX-route');
	const MAX_CARDS = CARROUSEL.getAttribute('data-max-cards');

	const xhr = new XMLHttpRequest();
	xhr.open('POST', VALUABLE_CARD_AJAX_ROUTE);
	xhr.setRequestHeader('Content-Type', 'application/json');
	xhr.send(JSON.stringify({"limit": parseInt(MAX_CARDS)}));
	
	xhr.onload = function () {
		if (xhr.status === 200) {
			
			const data = JSON.parse(xhr.responseText);
			console.log("cargando cartas mas caras", data);
			for (let i = 0; i < data['data'].length; i++) {
				ELEMENTS.appendChild(createDivCard(data['data'][i]))
			}
			const loadingElem = document.getElementById('loading-element');
			if (loadingElem) {
				loadingElem.remove();
			}
			updateText();
		}
		else if (xhr.status === 400) {
			console.error("No se han encontrado cartas ", xhr.responseText);
		}
	};
	
	xhr.onerror = function () { console.error('No se ha podido cargar la carta ', cardSlide);};
});

// var intervalCarrouselMoving = null;
var carrouselPos = 0;

function changeIndex(change) {
	carrouselPos += parseInt(change, 10);
	
	if (carrouselPos < 0) {
		// carrouselPos = 0;
		carrouselPos = document.getElementById('elements').children.length-1;
	} 
	else if (document.getElementById('elements').children.length <= carrouselPos) {
		// carrouselPos = document.getElementById('elements').children.length-1;
		carrouselPos = 0;
	} 
	else {	
		console.log("cambiando index de carrusel a: ", carrouselPos);
		// intervalCarrouselMoving = setInterval(carrouselMoving, 10);
	}
	const elements = document.getElementById('elements');
	const targetScroll = carrouselPos * elements.children[0].offsetWidth;
	document.getElementById('elements').scrollLeft = targetScroll;
	updateText()
	
}

function updateText() {
	const carrouselText = document.getElementById('carrousel-text');
	carrouselText.innerText = (carrouselPos+1) + "/" + document.getElementById('elements').children.length;
}

function createDivCard(json) {
	const div = document.createElement('div');
	div.className = 'element p-2';
	
	const name = document.createElement('h1');
	name.textContent = json["card"]["cardName"];
	name.className = 'text-center'
	div.appendChild(name);
	
	const img = document.createElement('img');
	img.className = 'img-fluid w-100'
	div.appendChild(img);

	const infoDiv = document.createElement('div');
	infoDiv.className = 'd-flex justify-content-between text-center'
	div.appendChild(infoDiv);

	const spanPrice = document.createElement('span');
	infoDiv.appendChild(spanPrice);

	const spanPurchacePrice = document.createElement('span');
	spanPurchacePrice.innerText = "Precio de compra: " + json["purchasePrice"] + "€"
	infoDiv.appendChild(spanPurchacePrice);


	const SCRYFALL_AJAX_ROUTE = document.getElementById('carrousel')?.getAttribute('data-scryfall-route');
	const xhr = new XMLHttpRequest();
	xhr.open('GET', SCRYFALL_AJAX_ROUTE + json["card"]["idScryfall"]);
	xhr.setRequestHeader('Content-Type', 'application/json');

	xhr.onload = function () {
		if (xhr.status === 200) {
			const JSON_DATA = JSON.parse(xhr.responseText);

			if (JSON_DATA["image_uris"] && JSON_DATA["image_uris"]["art_crop"]) {
				img.src = JSON_DATA["image_uris"]["art_crop"];
			} else if (JSON_DATA["card_faces"] && JSON_DATA["card_faces"][0]["image_uris"] && JSON_DATA["card_faces"][0]["image_uris"]["art_crop"]) {
				img.src = JSON_DATA["card_faces"][0]["image_uris"]["art_crop"];
			}
		
			if (JSON_DATA["prices"] && JSON_DATA["prices"]["eur"]) {
				spanPrice.innerText = "Precio actual: " + JSON_DATA["prices"]["eur"] + "€";
			}
		}
	};
	xhr.onerror = function () { console.error('No se ha podido cargar la carta ', cardSlide);};
	xhr.send();
		
	return div;
}

// function carrouselMoving() {
// 	console.log("moving");
	
// 	const elements = document.getElementById('elements');
// 	const targetScroll = carrouselPos * elements.children[0].offsetWidth;

// 	const current = elements.scrollLeft;
// 	const distance = targetScroll - current;
// 	const step = distance * 0.15; // Ajusta el factor para suavidad

// 	console.log(current, distance, step, Math.abs(distance));
	
// 	if (Math.abs(distance) > 3) {
// 		document.getElementById('elements').scrollLeft = current + step;
// 		console.log("CP1");
		
// 	} else {
// 		document.getElementById('elements').scrollLeft = targetScroll;
// 		clearInterval(intervalCarrouselMoving);
// 	}

// }