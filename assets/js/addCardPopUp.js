document.addEventListener('DOMContentLoaded', () => {
	const popUp = document.getElementById('addCardPopUp');
	if (popUp) {
		popUp.addEventListener('click', (event) => {
			if (event.target === popUp) {
				popUp.setAttribute('data-show-status', '0');
			}
		});
	}
	const colecctionCheckBox = document.getElementById('addCardPopUp-window-colecction-isFoil');
	if (colecctionCheckBox) {
		colecctionCheckBox.addEventListener('change', updatePrices);
	}
});

let prices;

function addCardPopUp(cardName, cardId, cardPrices) {
	console.log("creando pop up de " + cardName + " --- " + cardId);

	const popUp = document.getElementById('addCardPopUp');
	popUp.setAttribute('data-show-status', '1');

	const cardNameColecction = document.getElementById('addCardPopUp-window-colecction-name');
	cardNameColecction.innerText = cardName;

	const cardNameDeck = document.getElementById('addCardPopUp-window-deck-name');
	cardNameDeck.innerText = cardName;

	const cardNameInput = document.getElementById('addCardPopUp-window-card-name');
	cardNameInput.value = cardName;
	const cardIdInput = document.getElementById('addCardPopUp-window-card-id');
	cardIdInput.value = cardId;

	prices = cardPrices;
	console.log(prices);

	const cardFoilColecction = document.getElementById('addCardPopUp-window-colecction-isFoil');
	if (!prices["eur"] && prices["eur_foil"]) {
		cardFoilColecction.checked = true;
	}
	
	updatePrices();
}

function updatePrices() {
	const cardPriceColecction = document.getElementById('addCardPopUp-window-colecction-purchacePrice');
	const cardFoilColecction = document.getElementById('addCardPopUp-window-colecction-isFoil');

	// console.log(cardFoilColecction.checked);
	
	if (cardPriceColecction.value == prices["eur"] || cardPriceColecction.value == prices["eur_foil"] || cardPriceColecction.value == "") {
		if (prices["eur"] && !cardFoilColecction.checked) {
			cardPriceColecction.value = prices["eur"];
		}
		else if (prices["eur_foil"] && cardFoilColecction.checked) {
			cardPriceColecction.value = prices["eur_foil"];
		}
		else {
			cardPriceColecction.value = "";	
		}
	}
}



function addCardToDeck() {
	const AJAXroute = document.getElementById('addCardPopUp-window-deck').getAttribute("data-AJAX-route");
	const message = document.getElementById('addCardPopUp-window-message');
	const messageText = document.getElementById('addCardPopUp-window-message-text');

	console.log("añadiendo carta a mazo");
	

	const xhr = new XMLHttpRequest();
	xhr.open('POST', AJAXroute);
	xhr.setRequestHeader('Content-Type', 'application/json');
	const params = {
		cardId: document.getElementById('addCardPopUp-window-card-id').value,
		deckId: document.getElementById('addCardPopUp-window-deck').value,
		quantity: document.getElementById('addCardPopUp-window-deck-quantity').value
	};
	xhr.send(JSON.stringify({ data: params }));

	xhr.onload = function () {
		// console.log(xhr);
		const data = JSON.parse(xhr.responseText);
		console.log("Carta añadida", data);

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
		message.setAttribute("data-status", 404);
		messageText.innerText = "No se ha podido añadir la carta: " + xhr.status;
	};
	xhr.send();
}