{extends file="page.tpl"}

{block name="page_content"}
    <div class="container">
        <section class="page-content max-w-7xl mx-auto border p-6 bg-white rounded-lg shadow">
            <h1 class="text-center my-4">Mes Devis</h1>

            {if $quotes|@count > 0}
                <table class="table table-striped table-bordered">
                    <thead class="thead-default">
                        <tr>
                            <th>Numéro de devis</th>
                            <th>Type</th>
                            <th>Nom du client</th>
                            <th>Email</th>
                            <th>Statut</th>
                            <th>Valide jusqu'au</th>
                                <th>Date de création</th>
                                <th>Télécharger</th>
                            </tr>
                        </thead>
                        <tbody>
                            {foreach $quotes as $quote}
                                <tr>
                                    <td class="text-gray-400 font-semibold">{$quote.quote_number}</td>
                                    <td>















                                    {if $quote.quote_type == 'standard'}Achat















                                    {else}Location















                                    {/if}</td>
                                    <td>{$quote.customer_name}</td>
                                    <td>{$quote.customer_email}</td>
                                    <td>













                                    {if $quote.status == 'pending'}
                                            <span class="badge bg-brother-blue text-lowercase">En attente</span>















                                    {elseif $quote.status == 'validated'}
                                            <span class="badge bg-green-500 text-lowercase">Validé</span>















                                    {elseif $quote.status == 'refused'}
                                            <span class="badge bg-red-600 text-lowercase">Refusé</span>















                                    {else}
                                            <span class="badge badge-secondary">Inconnu</span>















                                    {/if}
                                    </td>
                                    <td>{$quote.valid_until|date_format:"%d/%m/%Y"}</td>
                                    <td>{$quote.date_add|date_format:"%d/%m/%Y"}</td>
                                    <td class="text-sm-center hidden-md-down">
                                        <div class="quote-download-link">
                                            <a href="{$link->getModuleLink('jca_locationdevis', 'downloadquote', ['id_quote' => $quote.id_quote])}"
                                                class="text-orange-500 hover:text-blue-700">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                    stroke-width="1.5" stroke="currentColor" class="w-5 h-5 inline">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M3 16.5v4.125c0 .621.504 1.125 1.125 1.125h15.75c.621 0 1.125-.504 1.125-1.125V16.5M7.5 10.5l4.5 4.5m0 0l4.5-4.5m-4.5 4.5V3">
                                                    </path>
                                                </svg>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
















                                {/foreach}
                        </tbody>
                    </table>
















                            {else}
                    <div class="alert alert-warning text-center">
                        Vous n'avez aucun devis pour le moment.
                    </div>
                {/if}
            </section>
        </div>
    {/block}