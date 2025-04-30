document.addEventListener('DOMContentLoaded', () => {
	const popUp = document.getElementById('addCardPopUp');
	if (popUp) {
		popUp.addEventListener('click', (event) => {
			if (event.target === popUp) {
				popUp.setAttribute('data-show-status', '0');
			}
		});
	}
});


function addCardPopUp(cardName, cardId) {
	console.log("creando pop up de " + cardName + " --- " + cardId);

	const popUp = document.getElementById('addCardPopUp');
	popUp.setAttribute('data-show-status', '1');

	const cardNameColecction = document.getElementById('addCardPopUp-window-colecction-name');
	cardNameColecction.innerText = cardName;

	const cardNameDeck = document.getElementById('addCardPopUp-window-deck-name');
	cardNameDeck.innerText = cardName;
}