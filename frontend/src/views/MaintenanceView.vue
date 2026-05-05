<template>
  <div class="maintenance-page">
    <div class="content">
      <img src="/aurem-logo.png" alt="Aurem" class="logo" />
      <h1 class="title">Nous revenons bientôt.</h1>
      <p class="subtitle">Aurem est actuellement en cours de maintenance pour améliorer votre expérience. Merci de votre patience.</p>
    </div>
  </div>
</template>

<style scoped>
.maintenance-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f5f3ef;
  color: #1a2332;
  text-align: center;
  padding: 20px;
}

.content {
  max-width: 500px;
}

.logo {
  height: 60px;
  margin-bottom: 40px;
}

.title {
  font-family: 'Playfair Display', serif;
  font-size: 3rem;
  margin-bottom: 16px;
}

.subtitle {
  font-family: 'Inter', sans-serif;
  font-size: 1.1rem;
  color: #6b7280;
  line-height: 1.6;
}
</style>
