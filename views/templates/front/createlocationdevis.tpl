{extends file="page.tpl"}

{block name="page_content"}

    <div class="container mx-auto mt-8 p-6 bg-white rounded-lg shadow-lg">
        <section class="page-content max-w-4xl mx-auto border p-6 bg-white rounded-lg shadow">
            <!-- Header du devis -->
            <div class="flex justify-between items-center mb-6 border-b pb-4 border-gray-200">
                <div class="flex items-center gap-4">
                    <span class="text-2xl font-bold">DEVIS</span>
                    <span class="type-badge bg-yellow-200 text-yellow-800">En
                        Attente</span>
                </div>
                <div>
                    <span class="flex items-center gap-1 status-badge bg-green-200 text-green-800">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                            <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                        </svg>
                        Location
                    </span>
                </div>
            </div>

            <!-- Informations client et dates -->
            <div class="grid md:grid-cols-2 gap-6 mb-6">
                <div class="bg-gray-50 p-4 rounded-lg shadow">
                    <h2 class="font-semibold text-lg mb-3-t">Informations Client</h2>
                    <div class="mb-2 flex items-center justify-between border-b pb-2-t border-gray-200"><span
                            class="font-medium text-gray-500 ">Nom:</span>
                        {$customer.firstname} {$customer.lastname}</div>
                    <div class="mb-2 flex items-center justify-between border-b pb-2-t border-gray-200"><span
                            class="font-medium text-gray-500 ">Email:</span>
                        {$customer.email}</div>
                    <div class="mb-2 flex items-center justify-between border-b pb-2-t border-gray-200"><span
                            class="font-medium text-gray-500 ">Téléphone:</span>
                        {$customer.phone|default:'non renseigné'}</div>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg shadow">
                    <h2 class="font-semibold text-lg mb-3-t">Dates</h2>
                    <div class="mb-2 flex items-center justify-between border-b pb-2-t border-gray-200"><span
                            class="font-medium text-gray-500 ">Créé le:</span>
                        {$date.created_at}
                    </div>
                    <div class="mb-2 flex items-center justify-between border-b pb-2-t border-gray-200"><span
                            class="font-medium text-gray-500 ">Valide jusqu'au:</span>
                            {$date.valid_until}
                        </div>
                    </div>
                </div>

                <!-- Produits -->
                <div class="mb-6">
                    <h2 class="text-xl font-semibold mb-3-t">Produits</h2>

                    {if $products|@count > 0}
                        <div class="space-y-4 ">
























                                    {foreach $products as $p}

                                <div class="flex justify-between items-center p-4 bg-gray-50 border border-gray-200 rounded-md shadow"
                                    data-product-id="{$p.id}">
                                    <div>
                                        <div class="font-medium text-lg">{$p.name}</div>
                                        <div class="text-gray-500 text-sm">Réf: {$p.reference}</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="mb-1">Qté: {$p.quantity}</div>
                                        <div class="font-semibold">{$p.price|number_format:2:',':' '} €</div>
                                    </div>
                                </div>
























                                    {/foreach}
                        </div>
























                                {else}
                        <div class="p-4 bg-red-100 text-red-800 rounded">Votre panier est vide. Ajoutez des produits pour créer
                            un
                            devis.</div>
























                                {/if}
                </div>

                <!-- Total et actions -->
























                                {if $products|@count > 0}
                    <div class="flex justify-between items-center mb-4 p-4 bg-gray-100 rounded-lg shadow">
                        <div data-v-5cfc61ba="" class="total-section">
                            <div data-v-5cfc61ba="" class="total-row rental-total">
                                <div data-v-5cfc61ba="" class="rental-total-info"><span data-v-5cfc61ba=""
                                        class="total-label font-semibold ">Mensualité totale: </span><span data-v-5cfc61ba=""
                                        class="total-value font-semibold  text-orange-price">{$monthlyPriceHT} € (HT) /mois</span>
                                </div>
                                <div data-v-5cfc61ba="" class="duration-display"><span data-v-5cfc61ba=""
                                        class="duration-label font-semibold ">Durée: </span><span data-v-5cfc61ba=""
                                        class="duration-value font-semibold text-orange-price">{$mode}
                                        mois</span></div>
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <button id="submitQuote"
                            class="flex items-center gap-2 px-3-t py-2-t bg-orange-500 text-white rounded hover:bg-gray-800 no-border cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                            Valider le devis
                        </button>
                        <a href="{$urls.pages.cart}"
                            class="flex items-center gap-2 px-3-t py-2-t bg-red-600 text-white rounded hover:bg-gray-800">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                            Refuser le devis
                        </a>
                    </div>
























                                {/if}
            </section>
        </div>

        <!-- JS AJAX -->
        <script type="application/json" id="quote-data">
        {
            "mode": {$mode|intval},
            "ratePercentage": {if isset($rentalConfiguration.selected_rate)}{$rentalConfiguration.selected_rate}{else}0{/if},
            "idRentalConfiguration": {if isset($rentalConfiguration.id_rental_configuration)}{$rentalConfiguration.id_rental_configuration|intval}{else}null{/if},
            "products": [
                {foreach $products as $p}
                {
                    "id_product": {$p.id|intval},
                    "name": {$p.name|json_encode nofilter},
                    "reference": {$p.reference|json_encode nofilter},
                    "price": {$p.price},
                    "quantity": {$p.quantity|intval}
                }{if !$p@last},{/if}
                {/foreach}
            ]
        }
        </script>
























            <script>
{literal}
                document.addEventListener('DOMContentLoaded', function() {
                    const btn = document.getElementById('submitQuote');

                    if (!btn) return;

                    // Récupérer les données JSON injectées
                    const quoteDataElement = document.getElementById('quote-data');
                    if (!quoteDataElement) {
                        console.error('Quote data not found');
                        return;
                    }

                    const quoteData = JSON.parse(quoteDataElement.textContent);
                    const mode = quoteData.mode;
                    const ratePercentage = quoteData.ratePercentage;
                    const idRentalConfiguration = quoteData.idRentalConfiguration;
                    const products = quoteData.products;

                    btn.addEventListener('click', function(e) {
                        e.preventDefault();

                        // On désactive le bouton pour éviter les doubles clics
                        btn.disabled = true;
                        btn.textContent = 'Création du devis...';

                        console.log('Products array:', products);
                        console.log('Mode:', mode);
                        console.log('Rate:', ratePercentage);
                        console.log('ID Rental Config:', idRentalConfiguration);

                        const requestData = {
                            quote_type: 'rental_only',
                            products: products,
                            duration_month: mode,
                            rate_percentage: ratePercentage,
                            id_rental_configuration: idRentalConfiguration
                        };
                        console.log('Sending request:', requestData);

                        fetch(prestashop.urls.base_url + 'module/jca_locationdevis/savedevis', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify(requestData)
                            })
                            .then(response => response.json())
                            .then(data => {
                                btn.disabled = false;
                                btn.textContent = 'Créer le devis';

                                if (data.success) {
                                    const messageDiv = document.createElement('div');
                                    messageDiv.textContent =
                                        'Votre devis a été créé avec succès !\nNuméro du devis : ' + data
                                        .quote_number +
                                        '\nVous allez être redirigé vers la page d\'accueil dans quelques instants.';
                                    messageDiv.style.position = 'fixed';
                                    messageDiv.style.top = '0';
                                    messageDiv.style.left = '0';
                                    messageDiv.style.width = '100%';
                                    messageDiv.style.backgroundColor = '#4caf50';
                                    messageDiv.style.color = 'white';
                                    messageDiv.style.padding = '10px';
                                    messageDiv.style.textAlign = 'center';
                                    messageDiv.style.zIndex = '1000';
                                    document.body.appendChild(messageDiv);

                                    setTimeout(() => {
                                        window.location.href = prestashop.urls.base_url;
                                    }, 5000); // Redirection après 5 secondes
                                } else {
                                    alert('Erreur : ' + data.message);
                                }
                            })
                            .catch(err => {
                                btn.disabled = false;
                                btn.textContent = 'Créer le devis';
                                console.error(err);
                                alert('Erreur inconnue lors de la création du devis');
                            });
                    });
                });
{/literal}
            </script>


    {/block}