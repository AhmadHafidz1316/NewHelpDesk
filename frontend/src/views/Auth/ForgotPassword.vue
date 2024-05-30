<script setup lang="ts">
import FormInput from '@/components/Forms/FormInput.vue'
import PrimaryButton from '@/components/Forms/PrimaryButton.vue'
import Logo from '@/assets/fps-icon.svg'

import '@/assets/style.css'

import EnvelopeIcon from '@heroicons/vue/24/outline/EnvelopeIcon'
import ArrowLeftIcon from '@heroicons/vue/24/outline/ArrowLeftIcon'

import { useAuthStore } from '@/stores/auth'
import { storeToRefs } from 'pinia'

import { ref } from 'vue'

import { useHead } from 'unhead'

import { appTitle } from '@/global'

import { useToast } from 'vue-toastification'

useHead({ title: `Forgot Password | ${appTitle}` })

const email = ref('')

const store = useAuthStore()

const { errors, isLoading, message, isSuccess } = storeToRefs(store)

const { forgotPassword } = store

const toast = useToast()

const onSubmit = async () => {
  if (isLoading.value) return

  await forgotPassword(email.value)

  if (isSuccess.value) toast.success(message.value)
  else toast.error(message.value)
}
</script>

<template>
  <section class="background-image flex h-screen items-center justify-center">
    <div class="rounded-md border bg-opacity-80 bg-white px-6 pt-6 pb-8 shadow">
      <header class="mb-6">
        <img :src="Logo" alt="" class="w-full p-10" />

        <h2 class="login text-center text-lg">Forgot your password?</h2>
      </header>

      <form @submit.prevent="onSubmit">
        <FormInput
          id="email"
          type="email"
          label="Email"
          placeholder="email@example.com"
          @change="(value) => (email = value)"
          :errors="errors?.email"
          :Icon="EnvelopeIcon"
        />

        <PrimaryButton type="submit" text="Send" class="mt-8 w-full" :loading="isLoading" />
      </form>

      <div class="mt-8">
        <router-link :to="{ name: 'Login' }" class="text-blue-600 hover:text-blue-800">
          <p class="flex items-center justify-center gap-1">
            <ArrowLeftIcon class="h-5 w-5" />
            Back to Login
          </p>
        </router-link>
      </div>
    </div>
  </section>
</template>
<style scoped>
.background-image {
  background-image: url('@/assets/bg.jpg');
  background-size: cover;
  background-position: center;
  background-repeat: no-repeat;
  margin: 0; /* Add this line to remove the top margin */

}

.bg-opacity-80 {
  background-color: rgba(255, 255, 255, 0.8);
}
</style>
