<template>
  <div id="app">
    <div class="editor-tabs">
      <button
        v-for="page in pages"
        :key="page.id"
        :class="['tab', { active: currentPage === page.id }]"
        @click="currentPage = page.id"
      >
        {{ page.label }}
      </button>
    </div>

    <AdminConfig v-if="currentPage === 'config'" />
    <QuotesList v-else-if="currentPage === 'quotes-list'" @navigate="navigateTo" />
    <CreateQuote v-else-if="currentPage === 'create-quote'" @navigate="navigateTo" />
    <QuoteDetail v-else-if="currentPage === 'quote-detail'" :quote-id="pageParams" @navigate="navigateTo" />
    <EditQuote v-else-if="currentPage === 'edit-quote'" :quote-id="pageParams" @navigate="navigateTo" />
  </div>
</template>

<script>
import AdminConfig from './views/AdminConfig.vue'
import QuotesList from './views/QuotesList.vue'
import CreateQuote from './views/CreateQuote.vue'
import QuoteDetail from './views/QuoteDetail.vue'
import EditQuote from './views/EditQuote.vue'

export default {
  name: 'App',
  components: {
    AdminConfig,
    QuotesList,
    CreateQuote,
    QuoteDetail,
    EditQuote
  },
  data() {
    return {
      currentPage: 'config',
      pageParams: null,
      pages: [
        { id: 'config', label: 'Configuration' },
        { id: 'quotes-list', label: 'Gestion des Devis' }
      ]
    }
  },
  methods: {
    navigateTo(pageId, params = null) {
      this.currentPage = pageId
      this.pageParams = params
    }
  }
}
</script>
<style>
.editor-tabs{
    background: white;
    border-bottom: 1px solid #e0e0e0;
    display: flex;
    padding: 0 24px;
}
.tab.active {
    color: #1976d2;
    border-bottom-color: #1976d2;
    background: #f8f9fa;
}
.tab {
    position: relative;
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 16px 24px;
    border: none;
    background: transparent;
    color: #666;
    cursor: pointer;
    transition: all 0.2s;
    border-bottom: 3px solid transparent;
    outline: none; /* Remove focus outline */
}
.tab:focus {
    outline: none; /* Ensure focus outline is disabled */
}
</style>

