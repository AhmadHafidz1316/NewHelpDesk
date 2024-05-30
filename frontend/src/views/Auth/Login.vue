<script setup lang="ts">
import FormInput from '@/components/Forms/FormInput.vue'
import PrimaryButton from '@/components/Forms/PrimaryButton.vue'
import Logo from '@/assets/fps-icon.svg'

import '@/assets/style.css'
import EnvelopeIcon from '@heroicons/vue/24/outline/EnvelopeIcon'
import LockClosedIcon from '@heroicons/vue/24/outline/LockClosedIcon'

import { useAuthStore } from '@/stores/auth'
import { storeToRefs } from 'pinia'

import { ref } from 'vue'

import { useHead } from 'unhead'

import { appTitle } from '@/global'

import { useToast } from 'vue-toastification'

import { useRouter } from 'vue-router'

useHead({ title: `Login | ${appTitle}` })

const email = ref<string>('')
const password = ref<string>('')

const store = useAuthStore()

const { errors, isLoading, message, isSuccess, isClient } = storeToRefs(store)

const { login } = store

const toast = useToast()

const router = useRouter()

const onSubmit = async () => {
  if (isLoading.value) return

  await login({
    email: email.value,
    password: password.value
  })

  if (isSuccess.value) {
    if (isClient.value) router.push({ name: 'ClientTickets' })
    else router.push({ name: 'Dashboard' })
  } else {
    toast.error(message.value)
  }
}
</script>

<template>
  <section class="background-image flex h-screen items-center justify-center">
    <div class="rounded-lg border bg-white bg-opacity-80 px-6 pt-6 pb-8 shadow">
      <header class="mb-6">
        <img :src="Logo" class="w-full p-12" alt="Logo" />
        <h2 class="login text-center text-lg">Sign in to your account</h2>
      </header>

      <form @submit.prevent="onSubmit">
        <div class="space-y-3">
          <FormInput
            id="email"
            type="email"
            label="Email"
            placeholder="email@example.com"
            @change="(value) => (email = value)"
            :errors="errors?.email"
            :Icon="EnvelopeIcon"
          />

          <FormInput
            id="password"
            type="password"
            label="Password"
            placeholder="********"
            @change="(value) => (password = value)"
            :errors="errors?.password"
            :Icon="LockClosedIcon"
          />
        </div>

        <router-link
          :to="{ name: 'ForgotPassword' }"
          class="mt-3 block text-right text-blue-600 hover:text-blue-800"
        >
          Forgot password?
        </router-link>

        <button class="mx-auto mt-3 w-full rounded-md bg-blue-500 p-3 text-white">Login</button>
      </form>

      <div class="mt-8">
        <p class="text-center text-gray-500">
          Don't have an account?
          <router-link :to="{ name: 'Register' }" class="text-blue-600 hover:text-blue-800">
            Register
          </router-link>
        </p>
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

