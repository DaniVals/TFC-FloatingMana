class CardCollection {
	constructor(
		order,
		id, name, quantity,
		element,
		image, price, type,
		purchasePrice, isFoil, state
	) {
		this.order = parseInt(order, 10);
		this.id = id;
		this.name = name;
		this.quantity = parseInt(quantity, 10);
		this.newQuantity = parseInt(quantity, 10);
		this.element = element;
		this.image = image;
		this.price = price;
		this.type = type;
		this.purchasePrice = purchasePrice;
		this.isFoil = isFoil;
		this.state = state;
	}

	updateElement(JSON_DATA) {
		// console.log(JSON_DATA);
		
		const cardImage = this.element.querySelector("#card-image");
		if (JSON_DATA["image_uris"] && JSON_DATA["image_uris"]["art_crop"]) {
			cardImage.src = JSON_DATA["image_uris"]["art_crop"];
		} else if (JSON_DATA["card_faces"] && JSON_DATA["card_faces"][0]["image_uris"] && JSON_DATA["card_faces"][0]["image_uris"]["art_crop"]) {
			cardImage.src = JSON_DATA["card_faces"][0]["image_uris"]["art_crop"];
		}

		const cardPrice = this.element.querySelector("#card-price");
		if (JSON_DATA["prices"] && JSON_DATA["prices"]["eur"]) {
			if (this.isFoil && this.isFoil == true) {
				this.price = JSON_DATA["prices"]["eur_foil"];
				cardPrice.innerText = JSON_DATA["prices"]["eur_foil"] + "€";
			}else{
				this.price = JSON_DATA["prices"]["eur"];
				cardPrice.innerText = JSON_DATA["prices"]["eur"] + "€";
			}
		}

		if (JSON_DATA["type_line"]) {
			let types = JSON_DATA["type_line"].split("—")[0].trim().split(/\s+/);
			// console.log(types);
			if (types[0] == "Legendary") {
				types.shift();
			}
			// console.log(types);
			this.type = types[0];
			
		} else {
			this.type = "other"
		}

		const cardPrices = this.element.querySelector("#card-prices");
		if (cardPrices) {
			let improvement = parseFloat((this.price - this.purchasePrice).toFixed(2));

			// show color only if there is a price to compare
			if (JSON_DATA["prices"] && JSON_DATA["prices"]["eur"]) {
				cardPrices.setAttribute("data-price-improvement", improvement);
				if (0 == improvement) {
					cardPrices.title = "= " + improvement + "€";
				}
				else if (0 < improvement) {
					cardPrices.title = "↑ " + improvement + "€";
				} 
				else {
					cardPrices.title = "↓ " + Math.abs(improvement) + "€";
				}
			}else{
				cardPrices.title = "sin precio";
			}
		}
	}

	modifyQuantity(quantityMod) {
		this.newQuantity += parseInt(quantityMod, 10);
		if (this.newQuantity < 0) {
			this.newQuantity = 0;
		}
		this.updateElementQuantity()
	}

	updateElementQuantity() {
		const P_QUANTITY = this.element.querySelector("#card-quantity");
		
		P_QUANTITY.innerText = this.newQuantity;
		if (this.newQuantity <= 0) {
			P_QUANTITY.setAttribute("data-quantity-status", "removed");
		}
		else if (this.quantity == this.newQuantity) {
			P_QUANTITY.setAttribute("data-quantity-status", "default");	
		}
		else {
			P_QUANTITY.setAttribute("data-quantity-status", "modified");
		}
	}
}