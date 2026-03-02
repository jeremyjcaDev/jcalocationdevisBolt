// alert('Durée de location sélectionnée : ' + selectedDuration + ' mois');

document.addEventListener('DOMContentLoaded', () => {


    const rentalModeToggles = document.querySelectorAll('.js-rental-mode-toggle');
    const rentalDurationOptions = document.querySelector('.rental-duration-options');
    const rentalDurationRadios = document.querySelectorAll('.js-rental-duration');
    const rentalConfig = JSON.parse(document.getElementById('rental-config').dataset.rentalConfig);
    console.log('Configuration de location chargée :', rentalConfig);
    const toggleRentalOptions = (isRentalModeEnabled) => {
        rentalDurationOptions.style.display = isRentalModeEnabled ? 'block' : 'none';
    };

    const updatePrices = (durationType) => {
        const priceHTElement = document.querySelector('.js-price-ht');
        const priceTTCElement = document.querySelector('.js-price-ttc');

        if (!priceHTElement || !priceTTCElement) {
            console.error('Price elements not found');
            return;
        }

        const basePriceHT = parseFloat(priceHTElement.getAttribute('data-price-ht'));
        const basePriceTTC = parseFloat(priceTTCElement.getAttribute('data-price-ttc'));

        if (!durationType) {
            // Réinitialiser les prix au prix classique
            console.log('Réinitialisation des prix : HT =', basePriceHT, ', TTC =', basePriceTTC);
            priceHTElement.textContent = `${basePriceHT.toFixed(2).replace('.', ',')} €`;
            priceTTCElement.textContent = `${basePriceTTC.toFixed(2).replace('.', ',')} €`;
            return;
        }

        const taxRate = 0.2; // Taux de TVA fixe à 20%
        let rentalRate = 0;
        let durationMonths = 1;
        if (durationType === '36') {
            rentalRate = rentalConfig.duration36Months / 100; // Convertir en pourcentage
            durationMonths = 36;
        } else if (durationType === '60') {
            rentalRate = rentalConfig.duration60Months / 100; // Convertir en pourcentage
            durationMonths = 60;
        }

        // Calculer le prix mensuel HT
        const monthlyBasePriceHT = basePriceHT / durationMonths;
        const monthlyPriceHT = (monthlyBasePriceHT * (1 + rentalRate)).toFixed(2);
        const monthlyPriceTTC = (monthlyPriceHT * (1 + taxRate)).toFixed(2);

        priceHTElement.textContent = `${monthlyPriceHT.replace('.', ',')} € / mois`;
        priceTTCElement.textContent = `${monthlyPriceTTC.replace('.', ',')} € / mois`;
    };

    // Sauvegarder les prix originaux HT et TTC dans des attributs data
    const priceHTElement = document.querySelector('.js-price-ht');
    const priceTTCElement = document.querySelector('.js-price-ttc');
    if (priceHTElement && priceTTCElement) {
        priceHTElement.setAttribute('data-original-price-ht', priceHTElement.getAttribute('content').replace(',', '.'));
        priceTTCElement.setAttribute('data-original-price-ttc', priceTTCElement.textContent.replace(',', '.').replace(' €', ''));
    }

    const replaceAddToCartButton = () => {
        const addToCartButton = document.querySelector('[data-button-action="add-to-cart"]');

        if (!addToCartButton) {
            console.error('Add to Cart button not found');
            return;
        }

        const addToQuoteButton = document.createElement('button');
        addToQuoteButton.className = 'text-sm bg-blue-500 hover:bg-dark-gray add-to-quote text-white rounded flex items-center justify-center gap-2 px-4 py-1 border-0 cursor-pointer';
        addToQuoteButton.type = 'button';
        addToQuoteButton.innerHTML = `
            <span>Ajouter au devis</span>
            <svg xmlns="http://www.w3.org/2000/svg" width="21" height="21" viewBox="0 0 21 21" fill="none">
                <g clip-path="url(#clip0_1181_3828)">
                    <path d="M8.00008 18.798C8.46032 18.798 8.83341 18.4249 8.83341 17.9647C8.83341 17.5044 8.46032 17.1313 8.00008 17.1313C7.53984 17.1313 7.16675 17.5044 7.16675 17.9647C7.16675 18.4249 7.53984 18.798 8.00008 18.798Z" stroke="white" stroke-linecap="round" stroke-linejoin="round"></path>
                    <path d="M17.1666 18.798C17.6268 18.798 17.9999 18.4249 17.9999 17.9647C17.9999 17.5044 17.6268 17.1313 17.1666 17.1313C16.7063 17.1313 16.3333 17.5044 16.3333 17.9647C16.3333 18.4249 16.7063 18.798 17.1666 18.798Z" stroke="white" stroke-linecap="round" stroke-linejoin="round"></path>
                    <path d="M1.33325 1.29834H4.66658L6.89992 12.4567C6.97612 12.8403 7.18484 13.185 7.48954 13.4303C7.79424 13.6755 8.1755 13.8058 8.56658 13.7983H16.6666C17.0577 13.8058 17.4389 13.6755 17.7436 13.4303C18.0483 13.185 18.257 12.8403 18.3333 12.4567L19.6666 5.46501H5.49992" stroke="white" stroke-linecap="round" stroke-linejoin="round"></path>
                </g>
                <defs>
                    <clipPath id="clip0_1181_3828">
                        <rect width="20" height="20" fill="white" transform="translate(0.5 0.464844)"></rect>
                    </clipPath>
                </defs>
            </svg>
        `;

        addToCartButton.replaceWith(addToQuoteButton);

        addToQuoteButton.addEventListener('click', () => {
            console.log('Ajouter au devis button clicked');
            // Appel AJAX vers le contrôleur front à implémenter
        });
    };

    rentalModeToggles.forEach(toggle => {
        toggle.addEventListener('change', (event) => {
            const isRentalModeEnabled = event.target.value === 'true';
            toggleRentalOptions(isRentalModeEnabled);

            if (isRentalModeEnabled) {
                replaceAddToCartButton();
            } else {
                // Optionally, restore the original Add to Cart button here if needed
            }
        });
    });

    rentalDurationRadios.forEach(radio => {
        radio.addEventListener('change', (event) => {
            const selectedDuration = event.target.value;
            console.log(`Durée de location sélectionnée : ${selectedDuration} mois`);
            updatePrices(selectedDuration);
        });
    });

    
});