import Vue from 'vue';
import Router from 'vue-router';
import DashboardView from '../views/Dashboard.vue';
import QuotesListView from '../views/QuotesList.vue';
import QuoteDetailView from '../views/QuoteDetail.vue';
import ConfigurationView from '../views/Configuration.vue';

Vue.use(Router);

const routes = [
  {
    path: '/',
    name: 'Dashboard',
    component: DashboardView
  },
  {
    path: '/quotes',
    name: 'QuotesList',
    component: QuotesListView
  },
  {
    path: '/quotes/:quoteNumber',
    name: 'QuoteDetail',
    component: QuoteDetailView
  },
  {
    path: '/config',
    name: 'Configuration',
    component: ConfigurationView
  }
];

export default new Router({
  mode: 'history',
  routes
});
