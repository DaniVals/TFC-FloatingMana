document.addEventListener('DOMContentLoaded', () => {
	const popUp = document.getElementById('addCardPopUp');
	if (popUp) {
		popUp.addEventListener('click', (event) => {
			if (event.target === popUp) {
				popUp.setAttribute('data-show-status', '0');
			}
		});
	}
	const collectionCheckBox = document.getElementById('addCardPopUp-window-collection-isFoil');
	if (collectionCheckBox) {
		collectionCheckBox.addEventListener('change', updatePrices);
	}
});

let prices;

function addCardPopUp(cardName, cardId, cardPrices) {
	console.log("opening pop up of " + cardName + " --- " + cardId);

	const popUp = document.getElementById('addCardPopUp');
	popUp.setAttribute('data-show-status', '1');

	const cardNamecollection = document.getElementById('addCardPopUp-window-collection-name');
	cardNamecollection.innerText = cardName;

	const cardNameDeck = document.getElementById('addCardPopUp-window-deck-name');
	cardNameDeck.innerText = cardName;

	const cardNameInput = document.getElementById('addCardPopUp-window-card-name');
	cardNameInput.value = cardName;
	const cardIdInput = document.getElementById('addCardPopUp-window-card-id');
	cardIdInput.value = cardId;

	prices = cardPrices;
	console.log(prices);

	const cardFoilcollection = document.getElementById('addCardPopUp-window-collection-isFoil');
	if (!prices["eur"] && prices["eur_foil"]) {
		cardFoilcollection.checked = true;
	}
	
	updatePrices();
}

function updatePrices() {
	const cardPricecollection = document.getElementById('addCardPopUp-window-collection-purchacePrice');
	const cardFoilcollection = document.getElementById('addCardPopUp-window-collection-isFoil');

	// console.log(cardFoilcollection.checked);
	
	if (cardPricecollection.value == prices["eur"] || cardPricecollection.value == prices["eur_foil"] || cardPricecollection.value == "") {
		if (prices["eur"] && !cardFoilcollection.checked) {
			cardPricecollection.value = prices["eur"];
		}
		else if (prices["eur_foil"] && cardFoilcollection.checked) {
			cardPricecollection.value = prices["eur_foil"];
		}
		else {
			cardPricecollection.value = "";	
		}
	}
}



function addCardToDeck() {
	const AJAXroute = document.getElementById('addCardPopUp-window-deck').getAttribute("data-AJAX-route");
	const message = document.getElementById('addCardPopUp-window-message');
	const messageText = document.getElementById('addCardPopUp-window-message-text');

	console.log("adding card to deck");
	showMessaje("Añadiendo carta...", 1);
	
	const params = {
		cardId: document.getElementById('addCardPopUp-window-card-id').value,
		cardName: document.getElementById('addCardPopUp-window-deck-name').innerText,
		deckId: document.getElementById('addCardPopUp-window-deck-selected').value,
		quantity: document.getElementById('addCardPopUp-window-deck-quantity').value
	};

	if (params["deckId"] == "none") {
		console.log("missing deck");
		showMessaje("Seleccione un mazo", 404);
		return;
	}
	if (params["quantity"] <= 0) {
		console.log("missing quantity");
		showMessaje("Introduzca una cantidad valida", 404);
		return;
	}

	const xhr = new XMLHttpRequest();
	xhr.open('POST', AJAXroute);
	xhr.setRequestHeader('Content-Type', 'application/json');
	xhr.send(JSON.stringify(params));

	xhr.onload = function () {
		// >> DELETE WHEN AJAX WORKS
		console.log(xhr);
		messageText.innerHTML = xhr.responseText;
		// <<

		const data = JSON.parse(xhr.responseText);
		console.log("Card request", data);

		messageText.innerText = data["message"];
		if (data["success"] == true) {
			message.setAttribute("data-status", 200);
		} else if (data["success"] == false) {
			message.setAttribute("data-status", 404);
		} else {
			message.setAttribute("data-status", xhr.status);
		}
	};
	
	xhr.onerror = function () {
		// console.log(xhr);
		console.error('No se ha podido añadir la carta', xhr.statusText);
		showMessaje("No se ha podido añadir la carta: " + xhr.status, 404);

	};
	xhr.send();
}
function addCardToCollection() {
	const AJAXroute = document.getElementById('addCardPopUp-window-collection').getAttribute("data-AJAX-route");
	const message = document.getElementById('addCardPopUp-window-message');
	const messageText = document.getElementById('addCardPopUp-window-message-text');

	console.log("adding card to collection");
	showMessaje("Añadiendo carta...", 1);	
	
	const params = {
		card_id: document.getElementById('addCardPopUp-window-card-id').value,
		cardName: document.getElementById('addCardPopUp-window-collection-name').innerText,
		isFoil: document.getElementById('addCardPopUp-window-collection-isFoil').checked,
		purchasePrice: document.getElementById('addCardPopUp-window-collection-purchacePrice').value,
		state: document.getElementById('addCardPopUp-window-collection-state').value,
		quantity: document.getElementById('addCardPopUp-window-collection-quantity').value
	};
	
	if (params["quantity"] <= 0) {
		console.log("missing quantity");
		showMessaje("Introduzca una cantidad valida", 404);
		return;
	}

	const xhr = new XMLHttpRequest();
	xhr.open('POST', AJAXroute);
	xhr.setRequestHeader('Content-Type', 'application/json');
	xhr.send(JSON.stringify(params));

	xhr.onload = function () {
		// >> DELETE WHEN AJAX WORKS
		console.log(xhr);
		messageText.innerHTML = xhr.responseText;
		// <<

		const data = JSON.parse(xhr.responseText);
		console.log("Card request", data);

		messageText.innerText = data["message"];
		if (data["success"] == true) {
			message.setAttribute("data-status", 200);
		} else if (data["success"] == false) {
			message.setAttribute("data-status", 404);
		} else {
			message.setAttribute("data-status", xhr.status);
		}
	};
	
	xhr.onerror = function () {
		// console.log(xhr);
		console.error('No se ha podido añadir la carta', xhr.statusText);
		showMessaje("No se ha podido añadir la carta: " + xhr.status, 404);

	};
	xhr.send();
}

function showMessaje(messaje, code) {
	const message = document.getElementById('addCardPopUp-window-message');
	const messageText = document.getElementById('addCardPopUp-window-message-text');
	
	message.setAttribute("data-status", code);
	messageText.innerText = messaje;
}