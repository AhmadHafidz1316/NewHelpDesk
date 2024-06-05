<script setup lang="ts">
import Modal from '@/components/Common/Modal.vue'
import PrimaryButton from '@/components/Forms/PrimaryButton.vue'
import FormSelect from '@/components/Forms/FormSelect.vue'

import { useToast } from 'vue-toastification'
import { ref, computed, watch } from 'vue'
import axios from 'axios'

const props = defineProps<{
  open: boolean
  departmentToEdit: Department
}>()

const emit = defineEmits<{
  (e: 'close'): void
  (e: 'success', keepQuery: boolean): void
}>()

const name = ref('')
const departmentNames = ref<string[]>([]) // Variable to hold department names

const method = computed(() => (props.departmentToEdit.id ? 'put' : 'post'))

const resetInput = ref(false)

const title = computed(() => {
  if (props.departmentToEdit.id) {
    return 'Edit Department'
  } else {
    return 'Add Sub Department'
  }
})

const buttonText = computed(() => {
  if (props.departmentToEdit.id) {
    return 'Update'
  } else {
    return 'Create'
  }
})

const toast = useToast()

const isLoading = ref(false)

const reset = () => {
  resetInput.value = true
  name.value = ''
}

const onSubmit = async () => {
  await save({ name: name.value }, method.value, props.departmentToEdit.id ?? null)

  if (isSuccess.value) {
    toast.success(message.value)
    reset()

    if (method.value === 'post') emit('success', false)
    else emit('success', true)

    emit('close')
  } else {
    toast.error(message.value)
  }
}

watch(
  () => props.departmentToEdit,
  () => {
    if (props.departmentToEdit.id) {
      name.value = props.departmentToEdit.name
    } else {
      reset()
    }
  }
)

axios
  .get('http://127.0.0.1:8000/api/departments')
  .then((response) => {
    console.log('Response data:');
    const departmentNamesArray = []; 

    response.data.data.forEach(department => {
      departmentNamesArray.push(department.name); 
      console.log(department.name);
    });

    departmentNames.value = departmentNamesArray; 
  })
  .catch((error) => {
    console.error('Error fetching department names:', error)
  })
</script>

<template>
  <Modal :open="open" @close="$emit('close')" :title="title">
    <form @submit.prevent="onSubmit">
      <div class="p-6">
        <FormSelect class="w-full" id="department" label="Department" :options="departmentNames">
        </FormSelect>
      </div>

      <div class="mt-3 flex justify-end border-t bg-gray-50 px-6 py-3">
        <PrimaryButton type="submit" :text="buttonText" :loading="isLoading" />
      </div>
    </form>
  </Modal>
</template>
