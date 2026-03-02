{extends file="page.tpl"}

{block name="page_content"}
    <div class="container mt-5 text-center">
        <h2>Votre panier est vide</h2>
        <p>Vous devez ajouter des produits dans votre panier pour créer un devis.</p>

        <div class="mt-4 flex justify-center gap-3">
            <a href="{$cart_url}" class="btn btn-primary">Retour au panier</a>
        </div>
    </div>
{/block}