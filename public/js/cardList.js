
var cards = []
const CARD_LIST_ID = 'card-list'

var group = "none";
var groups = {};
groups["noni"] = 0;

document.addEventListener('DOMContentLoaded', () => {
	const cardList = document.getElementById(CARD_LIST_ID);
	groups["none"] = cardList;
	
	if (cardList){
		const AJAXroute = cardList.getAttribute('data-ajax-route');
		// console.log(AJAXroute);
		// console.log(cardList);
		
		Array.from(document.getElementsByClassName("card-list-show")).forEach(cardSlide => {
			// console.log(cardSlide);
			let card = new CardCollection(
				cardSlide.getAttribute("data-card-order"),
				cardSlide.getAttribute("data-card-id"), cardSlide.getAttribute("data-card-name"),
				cardSlide.getAttribute("data-card-quantity"),
				cardSlide,
				"", 0.0, "",
				cardSlide.getAttribute("data-card-purchase-price"), 
				cardSlide.getAttribute("data-card-is-foil"), 
				cardSlide.getAttribute("data-card-state")
			);
			cards.push(card);
			// console.log(card);
			

			const xhr = new XMLHttpRequest();
			xhr.open('GET', AJAXroute + cardSlide.getAttribute("data-card-id"));
			xhr.setRequestHeader('Content-Type', 'application/json');

			xhr.onload = function () {
				if (xhr.status === 200) {
					const data = JSON.parse(xhr.responseText);
					// console.log('Success:', data);
					// console.log(cardSlide);
					card.updateElement(data);
					// console.log(cardSlide);
				}
			};
			
			xhr.onerror = function () { console.error('No se ha podido cargar la carta ', cardSlide);};
			xhr.send();
		});
	}
});






function addToBody() {
	for (let i = 0; i < cards.length; i++) {
		groups[group].appendChild(cards[i].element);
	}
}


function sortBy(selectId) {
	const sortingMode = document.getElementById(selectId).value
	console.log("sorting by: ", sortingMode);
	
	switch (sortingMode) {
		case "id":
			cards.sort((a, b) => a.order.localeCompare(b.order));
			break;

		case "name":
			cards.sort((a, b) => a.name.localeCompare(b.name));
			break;
			
		case "price":
			cards.sort((a, b) => b.price - a.price);
			break;
			
		case "purchace-price":
			cards.sort((a, b) => b.purchasePrice - a.purchasePrice);
			break;
	
		default:
			break;
	}

	addToBody();
}


function groupBy(selectId) {
	grupo = document.getElementById(selectId).value;
	console.log("grouping by: ", grupo);
	console.log("NOT IMPLEMENTED");
	

	const groups = document.getElementsByClassName("groupMode");
	for (let i = 0; i < groups.length; i++) {
		groups[i].setAttribute("data-show", 0);
	}
	
	switch (grupo) {
		case "type":
			// log secreto para ver la lista
			console.log(cards);
			break;
			
		case "set":
			break;
	
		default:
			break;
	}
	
	addToBody();
}


function createGroupDiv(group) {
	let div = document.createElement("div");
	div.setAttribute("data-group-id", group);
	div.setAttribute("data-show", 0);
	return div;
}

function modifyCardQuantity(cardId, quantityMod) {
	for (let i = 0; i < cards.length; i++) {
		if (cards[i].order === cardId) {
			cards[i].modifyQuantity(quantityMod);
			break;
		}
	}
	let changedCardsCont = 0;
	let deletedCardsCont = 0;
	for (let i = 0; i < cards.length; i++) {
		if (cards[i].newQuantity != cards[i].quantity) {
			if (cards[i].newQuantity == 0) {
				deletedCardsCont++;
			} else {
				changedCardsCont++;
			}
		}
	}
	const SAVE_CHANGES_POPUP = document.getElementById('save-changes-popup');

	const TEXT_SAVE_CHANGES_POPUP = SAVE_CHANGES_POPUP.querySelector('p')
	TEXT_SAVE_CHANGES_POPUP.innerText = "";
	
	if (changedCardsCont != 0) {
		TEXT_SAVE_CHANGES_POPUP.innerText += "Cartas modificadas " + changedCardsCont + "\n";
	}
	if (deletedCardsCont != 0) {
		TEXT_SAVE_CHANGES_POPUP.innerText += "  Cartas borradas " + deletedCardsCont;
	}
	
	if (changedCardsCont == 0 && deletedCardsCont == 0) {
		SAVE_CHANGES_POPUP.setAttribute('data-has-changes', 0)
	} else {
		SAVE_CHANGES_POPUP.setAttribute('data-has-changes', 1)
	}
}

function saveChanges() {
	const SAVE_CHANGES_POPUP = document.getElementById('save-changes-popup');
	let changed_cards = {"changed_card": []};
	for (let i = 0; i < cards.length; i++) {
		if (cards[i].newQuantity != cards[i].quantity) {
			changed_cards["changed_card"].push({"card_id":cards[i].order, "quantity":cards[i].newQuantity});
		}
	}

	const TEXT_SAVE_CHANGES_POPUP = SAVE_CHANGES_POPUP.querySelector('p')
	const AJAXroute = SAVE_CHANGES_POPUP.getAttribute('data-save-route');

	TEXT_SAVE_CHANGES_POPUP.innerText = "Guardando cambios...";
	console.log(changed_cards);
	
	const xhr = new XMLHttpRequest();
	xhr.open('POST', AJAXroute);
	xhr.setRequestHeader('Content-Type', 'application/json');
	xhr.send(JSON.stringify(changed_cards));

	xhr.onload = function () {
		if (xhr.status === 200) {
			for (let i = 0; i < cards.length; i++) {
				if (cards[i].newQuantity != cards[i].quantity) {
					cards[i].quantity = cards[i].newQuantity
					cards[i].updateElementQuantity()
				}
			}
			TEXT_SAVE_CHANGES_POPUP.innerText = "Cartas guardadas";
			SAVE_CHANGES_POPUP.setAttribute('data-has-changes', 2);

			const data = JSON.parse(xhr.responseText);
			TEXT_SAVE_CHANGES_POPUP.innerText = data["message"];
		}
		else if (xhr.status === 400) {
			TEXT_SAVE_CHANGES_POPUP.innerText = "Error al guardar cambios";
			console.error('Error al guardar cambios en el mazo', xhr);
		}
	};
	xhr.onerror = function () {
		TEXT_SAVE_CHANGES_POPUP.innerText = "Error al guardar cambios";
		console.error('Error al guardar cambios en el mazo');
	};
}

function closePopup() {
	const SAVE_CHANGES_POPUP = document.getElementById('save-changes-popup');
	SAVE_CHANGES_POPUP.setAttribute('data-has-changes', 0);
	console.log("Cerrando popup de guardar cambios");
}