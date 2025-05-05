document.addEventListener('DOMContentLoaded', () => {
	const cardList = document.getElementById('card-list');
	
	if (cardList){
		const AJAXroute = cardList.getAttribute('data-ajax-route');
		// console.log(AJAXroute);
		// console.log(cardList);
		
		Array.from(cardList.children).forEach(cardSlide => {
			console.log(cardSlide);

			const xhr = new XMLHttpRequest();
			xhr.open('GET', AJAXroute + cardSlide.getAttribute("data-card-id"));
			xhr.setRequestHeader('Content-Type', 'application/json');

			xhr.onload = function () {
				if (xhr.status === 200) {
					const data = JSON.parse(xhr.responseText);
					console.log('Success:', data);
					console.log(cardSlide);
					
					const cardImage = cardSlide.querySelector("#card-image");
					// console.log(cardImage);

					if (data.image_uris && data.image_uris.art_crop) {
						cardImage.src = data.image_uris.art_crop;
					} else if (data.card_faces && data.card_faces[0] && data.card_faces[0].image_uris && data.card_faces[0].image_uris.art_crop) {
						cardImage.src = data.card_faces[0].image_uris.art_crop;
					}
					

				}
			};
			
			xhr.onerror = function () {
				console.error('No se ha podido cargar la carta ', cardSlide);
			};

			xhr.send();
		});
	}
});