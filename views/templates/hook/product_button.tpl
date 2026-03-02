<!-- Bouton radio pour activer/désactiver le mode location -->
{block name='jca_locationdevis_product_button'}
    <div class="rental-mode-toggle">
        <label>
            <input type="radio" name="rentalMode" value="true" class="js-rental-mode-toggle" /> Activer le mode location
        </label>
        <label>
            <input type="radio" name="rentalMode" value="false" class="js-rental-mode-toggle" checked /> Désactiver le mode
            location
        </label>
    </div>

    <!-- Boutons radio pour choisir la durée de location -->
    <div class="rental-duration-options" style="display: none;">
        {if $duration36Enabled && $duration60Enabled}
            <p>Choisissez une durée de location :</p>
            <label>
                <input type="radio" name="rentalDuration" value="36" class="js-rental-duration" /> Location 36 mois
            </label>
            <label>
                <input type="radio" name="rentalDuration" value="60" class="js-rental-duration" /> Location 60 mois
            </label>
        {elseif $duration36Enabled}
            <p>Mode location activé : 36 mois</p>
            <input type="hidden" name="rentalDuration" value="36" class="js-rental-duration" />
        {elseif $duration60Enabled}
            <p>Mode location activé : 60 mois</p>
            <input type="hidden" name="rentalDuration" value="60" class="js-rental-duration" />
        {else}
            <p>La location n'est pas disponible pour ce produit.</p>
        {/if}
    </div>

    <div id="rental-config" data-rental-config="{$rentalConfiguration|json_encode}"></div>
{/block}
<script type="text/javascript" src="{$module_dir}views/js/product_button.js"></script>