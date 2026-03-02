{extends file="page.tpl"}

{block name="page_content"}
    <div class="container mt-5 text-center">
        <h2>Vous devez être connecté pour demander un devis</h2>
        <p>Veuillez vous connecter ou créer un compte pour continuer.</p>

        <div class="mt-4 flex justify-center gap-3">
            <a href="{$login_url}" class="btn btn-primary">Se connecter / S'inscrire</a>
            <a href="{$cart_url}" class="btn btn-secondary">Retour au panier</a>
        </div>
    </div>
{/block}