<script setup>
import { ref } from 'vue';

const username = ref("Vue test name");
</script>

<template>
  <h1>Hello, {{ username }}!</h1>
</template>

<style scoped>

</style>
