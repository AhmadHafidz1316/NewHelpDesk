<script setup lang="ts">
import Modal from '@/components/Common/Modal.vue'
import PrimaryButton from '@/components/Forms/PrimaryButton.vue'
import FormSelect from '@/components/Forms/FormSelect.vue'
import FormInput from '@/components/Forms/FormInput.vue'

import type Department from '@/types/Department'
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
const departementSelected = ref('')
const departmentNames = ref<string[]>([]) 



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

const onSubmit = () => {
  console.log('Department:', departementSelected.value)
  console.log('Name:', name.value)

   axios.post('http://127.0.0.1:8000/api/departments', {
    name: name.value,
    parent: departementSelected.value
  })
  .then((response) => {
    toast.success('Subdepartment created successfully')
    emit('success', true)
    reset()
  })
  .catch((error) => {
    console.error('Error creating subdepartment:', error)
    toast.error('Failed to create subdepartment')
  })

  emit('success', true)
  reset()
}
// watch(departementSelected, async (newQuestion, oldQuestion) => {
//   console.log('sss',newQuestion)
//     departementSelected.value = newQuestion
// })

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
    console.log('Response data:', response.data.data[0].name);
    const departmentNamesArray: any[] = []; 

    response.data.data.forEach((department: { name: any }) => {
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
        <FormSelect class="w-full b" id="department" label="Department" v-model="departementSelected" :options="departmentNames" @change="(value) => (departementSelected = value)">
        </FormSelect>
      
        <br>
        <FormInput
          class="w-full"
          id="name"
          label="Name"
          placeholder="Name"
          type="text"
          :value="name"
          @change="(value) => (name = value)"
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
