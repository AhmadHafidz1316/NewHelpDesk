<script setup lang="ts">
import Modal from '@/components/Common/Modal.vue'
import FormInput from '@/components/Forms/FormInput.vue'
import PrimaryButton from '@/components/Forms/PrimaryButton.vue'

import type Option from '@/types/Option'
import type Category from '@/types/Category'

import { ref, computed, watch, onMounted } from 'vue'
import axios from 'axios'
import { useToast } from 'vue-toastification'

const props = defineProps<{
  open: boolean
  departments: Option[]
  categoryToEdit: Category
}>()

const emit = defineEmits<{
  (e: 'close'): void
  (e: 'success', keepQuery: boolean): void
}>()

const priority_name = ref('')
const resolution_time = ref('')
const response_time = ref('')

const method = computed(() => (props.categoryToEdit.id ? 'put' : 'post'))

const resetInput = ref(false)

const title = computed(() => {
  return props.categoryToEdit.id ? 'Edit Priority' : 'New Priority'
})

const buttonText = computed(() => {
  return props.categoryToEdit.id ? 'Update' : 'Create'
})

const toast = useToast()

const errors = ref({})

const reset = () => {
  resetInput.value = true
  priority_name.value = ''
  resolution_time.value = ''
  response_time.value = ''
}

const onSubmit = async () => {
  const payload = {
    priority_name: priority_name.value,
    resolution_time: resolution_time.value,
    response_time: response_time.value,
  }

  try {
    const response = await axios({
      method: method.value,
      url: `http://127.0.0.1:8000/api/priority${method.value === 'put' ? `/${props.categoryToEdit.id}` : ''}`,
      data: payload,
    })

    toast.success(response.data.message || 'Priority saved successfully')
    emit('success', true)
    reset()
  } catch (error) {
    if (error.response && error.response.data) {
      errors.value = error.response.data.errors || {}
      toast.error(error.response.data.message || 'Failed to save priority')
    } else {
      toast.error('An unexpected error occurred')
    }
  }
}
</script>

<template>
  <Modal :open="open" @close="$emit('close')" :title="title">
    <form @submit.prevent="onSubmit">
      <div class="space-y-4 p-6">
        <FormInput
          class="w-full"
          id="name"
          label="Priority Name"
          placeholder="Priority Name"
          type="text"
          :value="priority_name"
          @change="(value) => (priority_name = value)"
          :errors="errors.priority_name"
          :reset="resetInput"
          @reset="() => (resetInput = false)"
        />

        <FormInput
          class="w-full"
          id="resolution-time"
          label="Resolution Time"
          placeholder="Resolution Time"
          type="text"
          :value="resolution_time"
          @change="(value) => (resolution_time = value)"
          :errors="errors.resolution_time"
          :reset="resetInput"
          @reset="() => (resetInput = false)"
        />

        <FormInput
          class="w-full"
          id="response-time"
          label="Response Time"
          placeholder="Response Time"
          type="text"
          :value="response_time"
          @change="(value) => (response_time = value)"
          :errors="errors.response_time"
          :reset="resetInput"
          @reset="() => (resetInput = false)"
        />
      </div>

      <div class="mt-3 flex justify-end border-t bg-gray-50 px-6 py-3">
        <PrimaryButton type="submit" :text="buttonText" :loading="isLoading" />
      </div>
    </form>
  </Modal>
</template>
