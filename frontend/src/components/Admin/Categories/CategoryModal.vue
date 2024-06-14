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
  return props.categoryToEdit.id ? 'Edit Category' : 'New Category'
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
          label="Name"
          placeholder="Name"
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
          label="Slug"
          placeholder="Slug"
          type="text"
          :value="slug"
          @change="(value) => (slug = value)"
          :errors="errors.slug"
          :reset="resetInput"
          @reset="() => (resetInput = false)"
        />

        <div class="relative w-full">
          <label for="department" class="block text-sm font-medium text-gray-700">Department</label>
          <div class="mt-1">
            <input
              placeholder="Select Department"
              type="text"
              id="department"
              class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              :value="selectedNode?.name || selectedParentNode?.name || ''"
              readonly
              @click="showDropdown = !showDropdown"
            />
          </div>
          <div
            v-if="showDropdown"
            class="absolute z-10 mt-1 max-h-60 w-full overflow-auto rounded-md border bg-white shadow-lg"
          >
            <ul>
              <li v-for="node in treeData" :key="node.id">
                <div
                  @click="selectParentNode(node)"
                  :class="{ 'font-bold': selectedParentNode?.id === node.id }"
                  class="cursor-pointer px-4 py-2 hover:bg-gray-100"
                >
                  {{ node.name }}
                </div>
                <ul
                  v-if="selectedParentNode?.id === node.id && node.children && node.children.length"
                  class="pl-4"
                >
                  <li v-for="child in node.children" :key="child.id">
                    <div
                      @click="selectNode(child)"
                      :class="{ 'font-bold': selectedNode?.id === child.id }"
                      class="cursor-pointer px-4 py-2 hover:bg-gray-100"
                    >
                      {{ child.name }}
                    </div>
                    <ul v-if="child.children && child.children.length" class="pl-4">
                      <li v-for="grandchild in child.children" :key="grandchild.id">
                        <div
                          @click="selectNode(grandchild)"
                          :class="{ 'font-bold': selectedNode?.id === grandchild.id }"
                          class="cursor-pointer px-4 py-2 hover:bg-gray-100"
                        >
                          {{ grandchild.name }}
                        </div>
                        <ul v-if="grandchild.children && grandchild.children.length" class="pl-4">
                          <li
                            v-for="greatgrandchild in grandchild.children"
                            :key="greatgrandchild.id"
                          >
                            <div
                              @click="selectNode(greatgrandchild)"
                              :class="{ 'font-bold': selectedNode?.id === greatgrandchild.id }"
                              class="cursor-pointer px-4 py-2 hover:bg-gray-100"
                            >
                              {{ greatgrandchild.name }}
                            </div>
                          </li>
                        </ul>
                      </li>
                    </ul>
                  </li>
                </ul>
              </li>
            </ul>
          </div>
        </div>
      </div>

      <div class="mt-3 flex justify-end border-t bg-gray-50 px-6 py-3">
        <PrimaryButton type="submit" :text="buttonText" :loading="isLoading" />
      </div>
    </form>
  </Modal>
</template>
