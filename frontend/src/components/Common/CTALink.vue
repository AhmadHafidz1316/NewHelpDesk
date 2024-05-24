<script setup lang="ts">
defineProps<{
  to: string
  text: string
}>()
</script>

<template>
  <router-link
    :to="{ name: to }"
    class="rounded-md border border-x-0 border-transparent bg-blue-400 bg-gradient-to-r py-3 px-4 font-medium text-white shadow hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
  >
    {{ text }}
  </router-link>
</template>
