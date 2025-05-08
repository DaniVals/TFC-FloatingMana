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