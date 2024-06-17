<script setup lang="ts">
import Modal from '@/components/Common/Modal.vue'
import FormInput from '@/components/Forms/FormInput.vue'
import PrimaryButton from '@/components/Forms/PrimaryButton.vue'

import type Option from '@/types/Option'
import type Category from '@/types/Category'

import useCategories from '@/composables/categories/useCategories'

import { ref, computed, watch, onMounted } from 'vue'

import { useToast } from 'vue-toastification'
import axios from 'axios'

const props = defineProps<{
  open: boolean
  departments: Option[]
  categoryToEdit: Category
}>()

const emit = defineEmits<{
  (e: 'close'): void
  (e: 'success', keepQuery: boolean): void
}>()

const name = ref('')
const slug = ref('')
const department = ref({} as Option)

const method = computed(() => (props.categoryToEdit.id ? 'put' : 'post'))

const resetInput = ref(false)

const title = computed(() => {
  return props.categoryToEdit.id ? 'Edit Category' : 'New Priority'
})

const buttonText = computed(() => {
  return props.categoryToEdit.id ? 'Update' : 'Create'
})

const toast = useToast()

const { save, isLoading, isSuccess, errors, message } = useCategories()

const reset = () => {
  resetInput.value = true
  name.value = ''
  slug.value = ''
  department.value = {} as Option
}

const onSubmit = async () => {
  try {
    const departmentId = selectedNode.value?.id || selectedParentNode.value?.id
    
    if (!departmentId) {
      toast.error('Please select a department')
      return
    }

    const response = await axios.post('http://127.0.0.1:8000/api/categories', {
      name: name.value,
      slug: slug.value,
      department_id: departmentId
    })

    // Handle success response
    if (response.status === 200 && response.data.message === 'Category created successfully') {
      toast.success(response.data.message)
      reset()

      if (method.value === 'post') emit('success', false)
      else emit('success', true)

      emit('close')
    } else {
      toast.error('Failed to create category')
    }
  } catch (error) {
    console.error(error.response)
    toast.error('An error occurred while creating the category')
  }
}

watch(
  () => props.categoryToEdit,
  () => {
    if (props.categoryToEdit.id) {
      name.value = props.categoryToEdit.name
      slug.value = props.categoryToEdit.slug
    } else {
      reset()
    }
  }
)

watch(
  () => props.departments && props.categoryToEdit,
  () => {
    if (props.categoryToEdit.id) {
      department.value =
        props.departments.find(
          (department) => department.value === props.categoryToEdit.department?.id.toString()
        ) ?? ({} as Option)
    }
  }
)

// Fetch departments data from the API and create tree structure
const treeData = ref([] as any[])

const fetchDepartments = async () => {
  try {
    const response = await axios.get('http://127.0.0.1:8000/api/departments')
    const departments = response.data.data

    const buildTree = (list: any[], parent: number | null) => {
      return list
        .filter((item) => item.parent === parent)
        .map((item) => ({
          ...item,
          children: buildTree(list, item.id)
        }))
    }

    treeData.value = buildTree(departments, null)
  } catch (error) {
    console.error('Error fetching departments:', error)
  }
}

onMounted(fetchDepartments)

const selectedParentNode = ref(null as Option | null)
const selectedNode = ref(null as Option | null)
const showDropdown = ref(false)

const selectParentNode = (node: Option) => {
  selectedParentNode.value = node
  selectedNode.value = null
}

const selectNode = (node: Option) => {
  selectedNode.value = node
  department.value = node
  showDropdown.value = false
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
          :value="name"
          @change="(value) => (name = value)"
          :errors="errors.name"
          :reset="resetInput"
          @reset="() => (resetInput = false)"
        />

        <FormInput
          class="w-full"
          id="slug"
          label="Resolution Time"
          placeholder="Resolution Time"
          type="text"
          :value="slug"
          @change="(value) => (slug = value)"
          :errors="errors.slug"
          :reset="resetInput"
          @reset="() => (resetInput = false)"
        />

        <FormInput
          class="w-full"
          id="slug"
          label="Response Time"
          placeholder="Response Time"
          type="text"
          :value="slug"
          @change="(value) => (slug = value)"
          :errors="errors.slug"
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
