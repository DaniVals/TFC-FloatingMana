
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
				cardSlide.getAttribute("data-card-id"), cardSlide.getAttribute("data-card-name"),
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
			cards.sort((a, b) => a.id.localeCompare(b.id));
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
