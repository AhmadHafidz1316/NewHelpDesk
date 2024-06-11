<template>
    <div>
      <label :for="id" class="block text-sm font-medium text-gray-700">{{ label }}</label>
      <select
        :id="id"
        v-model="selectedValue"
        :class="{ 'border-red-500': errors && errors.length }"
        @change="handleChange"
        class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
      >
        <option v-if="placeholder" :value="null" disabled selected>{{ placeholder }}</option>
        <option v-for="option in options" :key="option" :value="option">{{ option }}</option>
      </select>
      <p  class="mt-2 text-sm text-red-600" v-for="(error, index) in errors" :key="index">{{ error }}</p>
    </div>
  </template>
  
  <script setup lang="ts">
  import { ref } from 'vue'
  
  const props = defineProps<{
    id: string
    label: string
    options: { value: any; label: string }[]
    value: any
    errors?: string[]
    placeholder?: string
  }>()
  
  const emit = defineEmits(['change']) 
  
  const selectedValue = ref(props.value)
  
  const handleChange = () => {
    console.log('Changes:', selectedValue.value)
    emit('change', selectedValue.value)
  }
  </script>
  