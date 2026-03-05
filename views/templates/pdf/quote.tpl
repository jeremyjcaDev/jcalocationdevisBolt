<!-- Contenu principal du devis -->
<style>
    table.products {
        width: 100%;
        border-collapse: collapse;
        font-size: 9pt;
    }

    .table-spacing {
        margin-top: 55px;
        margin-bottom: 15px;
    }

    table.products th {
        background-color: #f0f0f0;
        border: 1px solid #000;
        padding: 4px;
        text-align: left;
    }

    table.products td {
        border: 1px solid #000;
        padding: 4px;
    }

    .right {
        text-align: right;
    }

    .center {
        text-align: center;
    }

    .left {
        text-align: left;
    }

    .product_header {
        font-weight: bold;
    }

    .totals_table {
        width: 100%;
        margin-top: 10px;
        font-size: 9pt;
    }

    .totals_table td {
        padding: 4px;
    }

    .totals_table .label {
        text-align: right;
        font-weight: bold;
    }

    .totals_table .value {
        text-align: right;
    }

    p {
        font-size: 9pt;
        margin-top: 0;
        margin-bottom: 0;
        padding-top: 0;
        padding-bottom: 0;
    }
</style>
<!-- ===================== -->
<!-- EN-TÊTE -->
<!-- ===================== -->
{if $logo_path}
    <table class="no-border">
        <tr>
            <td width="60%">
                <img src="{$logo_path}" alt="{$shop_name}" style="max-height: 100px; display: block; margin-bottom: 10px;">
            </td>
        </tr>
    </table>
    <div style="height:45px;"></div> <!-- espace après le logo -->
    <div style="height:45px;"></div> <!-- espace après le logo -->
    <div style="height:45px;"></div> <!-- espace après le logo -->
{/if}
<!-- ===================== -->
<table class="no-border table-spacing " style="margin-top:20px;" width="100%">
    <tr>
        <td width="60%">
            <strong>{$shop_name}</strong><br>
            {$shop_address1}<br>
            {$shop_postcode} {$shop_city}<br>
            Tél : {$shop_phone}<br>
            {$shop_email}
        </td>
        <td width="40%" class="right">
            <strong>{$quote.customer_name}</strong><br>
            {$quote.customer_email}<br>
            {$quote.customer_phone|default:'non renseigné'}
        </td>
    </tr>
</table>

<!-- ===================== -->
<!-- ADRESSES -->
<!-- ===================== -->
{if $invoice.name || $invoice.address1 || $invoice.postcode || $invoice.city || $delivery.name || $delivery.address1 || $delivery.postcode || $delivery.city}
    <table style="margin-top:10px;" width="100%">
        <tr>
            <!-- Facturation -->
            <td width="50%" valign="top">
                {if $invoice.name || $invoice.address1 || $invoice.postcode || $invoice.city}
                    <h2>Adresse de facturation</h2>
                    {if $invoice.name}{$invoice.name}<br>{/if}
                    {if $invoice.company}{$invoice.company}<br>{/if}
                    {if $invoice.address1}{$invoice.address1}<br>{/if}
                    {if $invoice.postcode || $invoice.city}{$invoice.postcode} {$invoice.city}<br>{/if}
                    {if $invoice.country}{$invoice.country}<br>{/if}
                    {if $invoice.phone}{$invoice.phone}{/if}
                {/if}
            </td>

            <!-- Livraison -->
            <td width="50%" valign="top">
                {if $delivery.name || $delivery.address1 || $delivery.postcode || $delivery.city}
                    <h2>Adresse de livraison</h2>
                    {if $delivery.name}{$delivery.name}<br>{/if}
                    {if $delivery.company}{$delivery.company}<br>{/if}
                    {if $delivery.address1}{$delivery.address1}<br>{/if}
                    {if $delivery.postcode || $delivery.city}{$delivery.postcode} {$delivery.city}<br>{/if}
                    {if $delivery.country}{$delivery.country}<br>{/if}
                    {if $delivery.phone}{$delivery.phone}{/if}
                {/if}
            </td>
        </tr>
    </table>
{/if}
<div style="height:45px;"></div> <!-- espace après le logo -->
<div style="height:45px;"></div> <!-- espace après le logo -->

<h2>Produits</h2>
<table class="products" width="100%" cellpadding="5" cellspacing="0"
    style="border-collapse: collapse; font-family: DejaVu Sans, sans-serif; font-size: 9pt; margin-top: 10px;">
    <thead>
        <tr style="background-color: #f5f5f5; border-bottom: 2px solid #ccc;">
            <th class="product_header left" style="text-align: left; font-weight: bold; border: 1px solid #ddd;">Produit
            </th>
            <th class="product_header center" style="text-align: center; font-weight: bold; border: 1px solid #ddd;">
                Quantité</th>
            <th class="product_header right" style="text-align: right; font-weight: bold; border: 1px solid #ddd;">Prix
                unitaire</th>
            <th class="product_header right" style="text-align: right; font-weight: bold; border: 1px solid #ddd;">Total
            </th>
        </tr>
    </thead>
    <tbody>
        {foreach from=$products item=product}
            {cycle values=["#ffffff", "#f9f9f9"] assign=bgcolor}
            <tr style="background-color: {$bgcolor}; border: 1px solid #ddd;">
                <td class="left" style="border: 1px solid #ddd; padding: 5px;">{$product.name}</td>
                <td class="center" style="text-align: center; border: 1px solid #ddd; padding: 5px;">{$product.quantity}
                </td>
                <td class="right" style="text-align: right; border: 1px solid #ddd; padding: 5px;">{$product.price}</td>
                <td class="right" style="text-align: right; border: 1px solid #ddd; padding: 5px;">{$product.total}</td>
            </tr>
        {/foreach}
    </tbody>
</table>
<div style="height:45px;"></div> <!-- espace après le logo -->

{if $delivery_item}
<h2>Livraison</h2>
<table class="products" width="100%" cellpadding="5" cellspacing="0"
    style="border-collapse: collapse; font-family: DejaVu Sans, sans-serif; font-size: 9pt; margin-top: 10px;">
    <thead>
        <tr style="background-color: #f5f5f5; border-bottom: 2px solid #ccc;">
            <th class="product_header left" style="text-align: left; font-weight: bold; border: 1px solid #ddd;">Mode de livraison</th>
            <th class="product_header right" style="text-align: right; font-weight: bold; border: 1px solid #ddd;">Prix HT</th>
        </tr>
    </thead>
    <tbody>
        <tr style="background-color: #ffffff; border: 1px solid #ddd;">
            <td class="left" style="border: 1px solid #ddd; padding: 5px;">{$delivery_item.name}</td>
            <td class="right" style="text-align: right; border: 1px solid #ddd; padding: 5px;">{$delivery_item.price}</td>
        </tr>
    </tbody>
</table>
<div style="height:45px;"></div> <!-- espace après le logo -->
{/if}

<h2>Total</h2>

{if $location.is_rental}
    <!-- Totaux pour location -->
    <table class="totals_table" width="100%" cellpadding="5" cellspacing="0"
        style="margin-top: 10px; border-collapse: collapse; font-size: 9pt; font-family: DejaVu Sans, sans-serif;">
        <tbody>
            <tr>
                <td class="label"
                    style="text-align: right; font-weight: bold; background-color: #f5f5f5; border: 1px solid #ddd;">
                    Durée (mois) :</td>
                <td class="value" style="text-align: right; border: 1px solid #ddd;">{$location.duration_months}</td>
            </tr>
            <tr>
                <td class="label"
                    style="text-align: right; font-weight: bold; background-color: #f5f5f5; border: 1px solid #ddd;">
                    Taux appliqué (%) :</td>
                <td class="value" style="text-align: right; border: 1px solid #ddd;">{$location.rate_percentage}</td>
            </tr>
            <tr>
                <td class="label"
                    style="text-align: right; font-weight: bold; background-color: #f5f5f5; border: 1px solid #ddd;">
                    Total mensuel :</td>
                <td class="value"
                    style="text-align: right; font-weight: bold; border: 1px solid #ddd; background-color: #eaeaea;">
                    {$location.monthly_total}
                </td>
            </tr>
        </tbody>
    </table>
{else}
    <!-- Totaux classiques -->
    <table class="totals_table" width="100%" cellpadding="5" cellspacing="0"
        style="margin-top: 10px; border-collapse: collapse; font-size: 9pt; font-family: DejaVu Sans, sans-serif;">
        <tbody>
            <tr>
                <td class="label"
                    style="text-align: right; font-weight: bold; background-color: #f5f5f5; border: 1px solid #ddd;">
                    Sous-total :</td>
                <td class="value" style="text-align: right; border: 1px solid #ddd;">{$totals.subtotal}</td>
            </tr>
            {if $delivery_item}
            <tr>
                <td class="label"
                    style="text-align: right; font-weight: bold; background-color: #f5f5f5; border: 1px solid #ddd;">
                    Livraison :</td>
                <td class="value" style="text-align: right; border: 1px solid #ddd;">{$delivery_item.price}</td>
            </tr>
            {/if}
            <tr>
                <td class="label"
                    style="text-align: right; font-weight: bold; background-color: #f5f5f5; border: 1px solid #ddd;">
                    TVA :</td>
                <td class="value" style="text-align: right; border: 1px solid #ddd;">{$totals.tax}</td>
            </tr>
            <tr>
                <td class="label"
                    style="text-align: right; font-weight: bold; background-color: #f5f5f5; border: 1px solid #ddd;">
                    Total TTC :</td>
                <td class="value"
                    style="text-align: right; font-weight: bold; border: 1px solid #ddd; background-color: #eaeaea;">
                    {$totals.total}
                </td>
            </tr>
        </tbody>
    </table>
{/if}
<div style="height:45px;"></div> <!-- espace après le logo -->
<table cellpadding="0" cellspacing="0" border="0"
    style="font-size:9pt; font-family:DejaVu Sans, sans-serif; line-height:1.2;">
    <tr>
        <td>Sans date de validité précisée sur le présent devis, celui-ci est valable une journée.</td>
    </tr>
    <tr>
        <td><strong>IMPORTANT</strong> : comme nous n'acceptons plus les chèques sur la boutique, le retour du devis
            signé accompagné d'un chèque n'est pas accepté.</td>
    </tr>
    <tr>
        <td>Vous devrez procéder au règlement par virement ou par carte bancaire après réception d'un lien de paiement.
        </td>
    </tr>
</table>