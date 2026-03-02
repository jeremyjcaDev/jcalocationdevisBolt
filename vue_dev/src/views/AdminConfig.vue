<template>
  <div class="admin-config">
    <div class="container">
      <h1 class="page-title">Configuration Administration</h1>

      <div class="tabs-container">
        <div class="tabs">
          <button
            v-for="tab in tabs"
            :key="tab.id"
            :class="['tab', { active: activeTab === tab.id }]"
            @click="activeTab = tab.id"
          >
            {{ tab.label }}
          </button>
        </div>

        <div class="tab-content">
          <component :is="currentTabComponent" />
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import ConfigGeneral from '../components/ConfigGeneral.vue'
import ConfigLocation from '../components/ConfigLocation.vue'
import ConfigDevis from '../components/ConfigDevis.vue'
import ProductRentalConfig from '../components/ProductRentalConfig.vue'

export default {
  name: 'AdminConfig',
  components: {
    ConfigGeneral,
    ConfigLocation,
    ConfigDevis,
    ProductRentalConfig
  },
  data() {
    return {
      activeTab: 'general',
      tabs: [
        { id: 'general', label: 'Configuration Générale' },
        { id: 'location', label: 'Location' },
        { id: 'devis', label: 'Devis' },
        { id: 'products', label: 'Produits en Location' }
      ]
    }
  },
  computed: {
    currentTabComponent() {
      const componentMap = {
        general: 'ConfigGeneral',
        location: 'ConfigLocation',
        devis: 'ConfigDevis',
        products: 'ProductRentalConfig'
      }
      return componentMap[this.activeTab]
    }
  }
}
</script>

<style scoped>
.admin-config {
  min-height: 100vh;
  background-color: #f5f5f7;
  padding: 2rem 0;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 1.5rem;
}

.page-title {
  font-size: 2rem;
  font-weight: 600;
  color: #1d1d1f;
  margin-bottom: 2rem;
  letter-spacing: -0.02em;
}

.tabs-container {
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
  overflow: hidden;
}

.tabs {
  display: flex;
  border-bottom: 1px solid #e5e5e7;
  background: #fafafa;
}

.tab {
  flex: 1;
  padding: 1rem 1.5rem;
  background: none;
  border: none;
  font-size: 0.9375rem;
  font-weight: 500;
  color: #6e6e73;
  cursor: pointer;
  transition: all 0.2s ease;
  position: relative;
  border-bottom: 2px solid transparent;
}

.tab:hover {
  color: #1d1d1f;
  background: #f5f5f7;
}

.tab.active {
  color: #0071e3;
  background: white;
  border-bottom-color: #0071e3;
}

.tab-content {
  padding: 2rem;
}

@media (max-width: 768px) {
  .tabs {
    flex-direction: column;
  }

  .tab {
    text-align: left;
    border-bottom: 1px solid #e5e5e7;
  }

  .tab.active {
    border-bottom-color: transparent;
    border-left: 3px solid #0071e3;
  }
}
</style>
