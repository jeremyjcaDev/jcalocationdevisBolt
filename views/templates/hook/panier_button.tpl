<div>
    <button
        class="mt-1 bg-brother-blue text-sm hover:bg-dark-gray add-to-cart text-white rounded-md flex items-center justify-center gap-2 px-3-t py-2-t border-0 cursor-pointer w-full"
        id="rental-quote-button">
        Demander un devis
    </button>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const btn = document.getElementById('rental-quote-button');
        btn.addEventListener('click', function() {
            window.location.href = prestashop.urls.base_url + 'module/jca_locationdevis/createdevis';
        });
    });
</script>