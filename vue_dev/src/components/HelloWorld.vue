<template>
  <div>
    <h1>{{ msg }}</h1>

    <div class="card">
      <button type="button" @click="count++">count is {{ count }}</button>
      <p>
        Edit
        <code>components/HelloWorld.vue</code> to test HMR
      </p>
    </div>

    <div class="api-section">
      <h2>Symfony API Connection</h2>
      <button @click="testApiConnection">Test API Connection</button>
      <p v-if="apiResponse">{{ apiResponse }}</p>
    </div>

    <p>
      Check out
      <a href="https://vuejs.org/guide/quick-start.html#local" target="_blank"
        >create-vue</a
      >, the official Vue + Vite starter
    </p>
    <p class="read-the-docs">Click on the Vite and Vue logos to learn more</p>
  </div>
</template>

<script>
import api from '../services/api.js'

export default {
  name: 'HelloWorld',
  props: {
    msg: String
  },
  data() {
    return {
      count: 0,
      apiResponse: null
    }
  },
  methods: {
    async testApiConnection() {
      try {
        const response = await api.get('/test');
        this.apiResponse = JSON.stringify(response.data);
      } catch (error) {
        this.apiResponse = `Error: ${error.message}`;
      }
    }
  }
}
</script>

<style scoped>
.read-the-docs {
  color: #888;
}

.api-section {
  margin: 2em 0;
  padding: 1em;
  border: 1px solid #ccc;
  border-radius: 8px;
}

.api-section button {
  margin: 1em 0;
}
</style>
