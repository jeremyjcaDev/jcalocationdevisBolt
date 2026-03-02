import axios from "axios";

class ApiService {
  constructor() {
    this.mode = process.env.VUE_APP_API_MODE || 'prestashop';

    if (this.mode === 'supabase') {
      this.baseURL = `${process.env.VUE_APP_SUPABASE_URL}/functions/v1/quote-api`;
      this.token = process.env.VUE_APP_SUPABASE_ANON_KEY;
    } else {
      this.baseURL = window.JCA_LOCATIONDEVIS_CONFIG?.apiBaseUrl;
      this.token = window.JCA_LOCATIONDEVIS_CONFIG?.token || "";
    }

    this.axios = axios.create({
      baseURL: this.baseURL,
      timeout: 15000,
      headers: {
        "Content-Type": "application/json",
      },
    });

    this.axios.interceptors.request.use(
      (config) => {
        if (this.token) {
          if (this.mode === 'supabase') {
            config.headers.apikey = this.token;
            config.headers.Authorization = `Bearer ${this.token}`;
          } else {
            config.headers.Authorization = `Bearer ${this.token}`;
          }
        }
        return config;
      },
      (error) => Promise.reject(error)
    );

    this.axios.interceptors.response.use(
      (response) => response,
      (error) => {
        if (error.response) {
          const status = error.response.status;
          if (status === 401) {
            console.error("Token invalide ou expire");
          } else if (status === 403) {
            console.error("Acces refuse");
          } else if (status === 404) {
            console.error("Endpoint non trouve");
          } else if (status >= 500) {
            console.error("Erreur serveur");
          }
        }
        return Promise.reject(error);
      }
    );

    console.log(`API Mode: ${this.mode}`);
    console.log(`API URL: ${this.baseURL}`);
  }

  async request(action, entity, data = {}) {
    try {
      const payload = {
        action,
        entity,
        data
      };
      console.log('API Request:', { action, entity, data, url: this.baseURL });
      const response = await this.axios.post('', payload);
      console.log('API Response:', response.data);
      return response.data;
    } catch (error) {
      console.error('API Error Details:', {
        message: error.message,
        response: error.response?.data,
        status: error.response?.status,
        hasRequest: !!error.request,
        hasResponse: !!error.response
      });

      if (error.response) {
        const errorMsg = error.response.data?.message || error.response.statusText || 'Erreur serveur';
        throw new Error(`${errorMsg} (Code: ${error.response.status})`);
      } else if (error.request) {
        throw new Error(`Pas de réponse du serveur ${this.mode === 'supabase' ? 'Supabase' : 'PrestaShop'}. Vérifiez votre connexion.`);
      } else {
        throw new Error(`Erreur de configuration: ${error.message}`);
      }
    }
  }
}

export default new ApiService();
