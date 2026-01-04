<script setup>
import Header from '../components/Header.vue';
import Footer from '../components/Footer.vue';
</script>

<template>
  <div>
    <Header :hasSidebar="false" />
    <main>
      <router-view />
    </main>
    <Footer />
  </div>
</template>
