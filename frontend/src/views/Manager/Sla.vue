<script setup lang="ts">
import axios from 'axios'

import TableCard from '@/components/Common/Table/TableCard.vue'
import TableTd from '@/components/Common/Table/TableTd.vue'
import Pagination from '@/components/Common/Pagination.vue'
import TableSkeleton from '@/components/Common/Table/TableSkeleton.vue'
import ActionButton from '@/components/Common/ActionButton.vue'
import NotFoundResults from '@/components/Common/NotFoundResults.vue'
import ConfirmationModal from '@/components/Admin/ConfirmationModal.vue'
import SlaModal from '@/components/Admin/Sla/SlaModal.vue'
import FilterCategories from '@/components/Admin/Categories/FilterCategories.vue'
import TableActions from '@/components/Common/Table/TableActions.vue'

import { ref, onMounted, computed, watch } from 'vue'
import { useRouter, useRoute, onBeforeRouteUpdate } from 'vue-router'
import { useToast } from 'vue-toastification'

import { useHead } from 'unhead'
import { appTitle } from '@/global'
import { useDashboard } from '@/stores/dashboard'
import getCategories from '@/composables/categories/getCategories'
import getDepartments from '@/composables/departments/getDepartments'
import useCategories from '@/composables/categories/useCategories'

import debounce from 'lodash/debounce'

import MagnifyingGlassIcon from '@heroicons/vue/24/outline/MagnifyingGlassIcon'
import AdjustmentsHorizontalIcon from '@heroicons/vue/24/outline/AdjustmentsHorizontalIcon'
import PlusIcon from '@heroicons/vue/24/outline/PlusIcon'
import ExclamationTriangleIcon from '@heroicons/vue/24/outline/ExclamationTriangleIcon'
import InformationCircleIcon from '@heroicons/vue/24/outline/InformationCircleIcon'
import PencilIcon from '@heroicons/vue/24/outline/PencilIcon'
import TrashIcon from '@heroicons/vue/24/outline/TrashIcon'

import type Category from '@/types/Category'
import type CategoriesFilters from '@/types/CategoriesFilters'
import type Option from '@/types/Option'
import type Department from '@/types/Department'

useHead({ title: `Sla Management | ${appTitle}` })

const { setTitle } = useDashboard()
setTitle('Sla Management')

const route = useRoute()
const router = useRouter()
const toast = useToast()

const filters = ref({
  page: isNaN(Number(route.query.page)) ? null : Number(route.query.page),
  query: route.query.query ?? null,
  department: isNaN(Number(route.query.department)) ? null : Number(route.query.department),
  trash: route.query.trash,
  paginate: true,
} as CategoriesFilters)

const filtersAreApplied = computed(() => {
  return filters.value.query || filters.value.department || filters.value.trash
})

const categories = ref([] as any[])
const isLoading = ref(true)

const loadCategories = async () => {
  isLoading.value = true
  try {
    const response = await axios.get('http://127.0.0.1:8000/api/priority', { params: filters.value })
    categories.value = response.data.data
  } catch (error) {
    toast.error('Failed to load categories')
  } finally {
    isLoading.value = false
  }
}

const handleAfterSave = async (keepQuery: boolean) => {
  if (!Object.keys(route.query).length) await loadCategories()
  else if (!keepQuery) router.push({ query: {} })
  else await loadCategories()
}

const departments = ref([] as Option[])
const { load: loadDepartments, departments: tempDepartments } = getDepartments()
onMounted(async () => {
  await loadCategories()
  await loadDepartments()
  departments.value = tempDepartments.value.map((department: Department) => ({
    name: department.name,
    value: department.id.toString(),
  }))
})

const categoryModalIsOpen = ref(false)
const confirmationModalIsOpen = ref(false)
const confirmationModalTitle = ref('')
const confirmationModalText = ref('')
const confirmationModalButtonText = ref('')
const confirmationModalBackgroundColor = ref('')
const confirmationModalButtonBackgroundColor = ref('')
const confirmationModalColor = ref('')
const confirmationModalIcon = ref({} as any)
const confirmationModalFunction = ref({} as Function)

const filterPanelIsOpen = ref(false)
const categoryToEdit = ref({} as Category)
const categoryToDelete = ref({} as Category)
const categoryToRestore = ref({} as Category)

const handleAdd = () => {
  categoryToEdit.value = {} as Category
  categoryModalIsOpen.value = true
}

const handleEdit = (category: Category) => {
  categoryToEdit.value = category
  categoryModalIsOpen.value = true
}

const handleDelete = (category: Category) => {
  categoryToDelete.value = category
  confirmationModalIsOpen.value = true
  confirmationModalTitle.value = 'Delete Category'
  confirmationModalText.value = 'Are you sure you want to delete this category?'
  confirmationModalButtonText.value = 'Delete'
  confirmationModalBackgroundColor.value = 'bg-red-100'
  confirmationModalButtonBackgroundColor.value = 'bg-red-600 hover:bg-red-700 focus:ring-red-500'
  confirmationModalColor.value = 'text-red-600'
  confirmationModalIcon.value = ExclamationTriangleIcon
  confirmationModalFunction.value = deleteCategory
}

const handlePermanentDelete = (category: Category) => {
  categoryToDelete.value = category
  confirmationModalIsOpen.value = true
  confirmationModalTitle.value = 'Permanent Delete Category'
  confirmationModalText.value = 'Are you sure you want to permanently delete this category? This action cannot be undone.'
  confirmationModalButtonText.value = 'Delete'
  confirmationModalBackgroundColor.value = 'bg-red-100'
  confirmationModalButtonBackgroundColor.value = 'bg-red-600 hover:bg-red-700 focus:ring-red-500'
  confirmationModalColor.value = 'text-red-600'
  confirmationModalIcon.value = ExclamationTriangleIcon
  confirmationModalFunction.value = forceDelete
}

const handleRestore = (category: Category) => {
  categoryToRestore.value = category
  confirmationModalIsOpen.value = true
  confirmationModalTitle.value = 'Restore Category'
  confirmationModalText.value = 'Are you sure you want to restore this category?'
  confirmationModalButtonText.value = 'Restore'
  confirmationModalBackgroundColor.value = 'bg-blue-100'
  confirmationModalButtonBackgroundColor.value = 'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500'
  confirmationModalColor.value = 'text-blue-600'
  confirmationModalIcon.value = InformationCircleIcon
  confirmationModalFunction.value = restoreCategory
}

const { destroy, restore, isLoading: operationIsLoading, message, isSuccess, destroy1 } = useCategories()

const deleteCategory = async () => {
  await destroy1(categoryToDelete.value.id)
  confirmationModalIsOpen.value = false
  if (isSuccess.value) {
    toast.success(message.value)
    await loadCategories()
  } else {
    toast.error(message.value)
  }
}

const forceDelete = async () => {
  await destroy(categoryToDelete.value.id, true)
  confirmationModalIsOpen.value = false
  if (isSuccess.value) {
    toast.success(message.value)
    await loadCategories()
  } else {
    toast.error(message.value)
  }
}

const restoreCategory = async () => {
  await restore(categoryToRestore.value.id)
  confirmationModalIsOpen.value = false
  if (isSuccess.value) {
    toast.success(message.value)
    await loadCategories()
  } else {
    toast.error(message.value)
  }
}

const headers = ['Priority Name', 'Resolution Time', 'Response Time', 'Actions']

const query = ref(filters.value.query ?? '')

watch(query, debounce(async (value: string) => {
  filters.value.query = value
  const query: any = {}
  if (value) query.query = value
  if (filters.value.trash) query.trash = filters.value.trash
  if (filters.value.department) query.department = filters.value.department
  router.push({ query })
}, 300))

const filter = async (newFilters: CategoriesFilters) => {
  filterPanelIsOpen.value = false
  filters.value = { ...newFilters, query: filters.value.query, paginate: true }
  const query: any = {}
  query.trash = newFilters.trash
  if (filters.value.query) query.query = filters.value.query
  if (newFilters.department) query.department = newFilters.department
  router.push({ query })
  await loadCategories()
}

const reset = async () => {
  filterPanelIsOpen.value = false
  if (filters.value.trash || filters.value.department) {
    const query: any = {}
    if (filters.value.query) {
      filters.value = { query: filters.value.query, paginate: true }
      query.query = filters.value.query
    } else {
      filters.value = { paginate: true }
    }
    router.push({ query })
    await loadCategories()
  }
}

onBeforeRouteUpdate(async (to, from) => {
  if (to.query.page !== from.query.page) {
    filters.value = { ...filters.value, page: Number(to.query.page) }
    await loadCategories()
  }
})
</script>

<template>
  <SlaModal
    :open="categoryModalIsOpen"
    :departments="departments"
    :category-to-edit="categoryToEdit"
    @close="categoryModalIsOpen = false"
    @success="handleAfterSave"
  />

  <ConfirmationModal
    :title="confirmationModalTitle"
    :open="confirmationModalIsOpen"
    :text="confirmationModalText"
    :button-text="confirmationModalButtonText"
    :is-loading="operationIsLoading"
    :Icon="confirmationModalIcon"
    :color="confirmationModalColor"
    :background-color="confirmationModalBackgroundColor"
    @close="confirmationModalIsOpen = false"
    @confirm="confirmationModalFunction()"
    :button-background-color="confirmationModalButtonBackgroundColor"
  />

  <FilterCategories
    :departments="departments"
    :filters="filters"
    :open="filterPanelIsOpen"
    @close="filterPanelIsOpen = false"
    @filter="filter"
    @reset="reset"
  />

  <header class="mb-3 flex flex-col justify-between gap-3 sm:flex-row-reverse">
    <div class="flex justify-end sm:justify-start">
      <ActionButton :Icon="PlusIcon" text="New Priority" :action="handleAdd" />
    </div>
  </header>

  <TableSkeleton v-if="isLoading" />

  <NotFoundResults
    v-else-if="!isLoading && !categories.length && filtersAreApplied"
    text="No categories related to your search"
  />

  <TableCard :headers="headers" v-else>
    <template v-slot:default>
      <template v-if="categories.length">
        <tr v-for="category in categories" :key="category.id">
          <TableTd>{{ category.priority_name }}</TableTd>
          <TableTd>{{ category.resolution_time }}</TableTd>
          <TableTd>{{ category.response_time }}</TableTd>
          <TableTd>
           <TableActions>
            <template v-if="$route.query.trash != 'true'">
                <MenuItem class="cursor-pointer">
                  <div
                    @click="handleEdit(category)"
                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                  >
                    Edit
                  </div>
                </MenuItem>

                <MenuItem class="cursor-pointer">
                  <div
                    @click="handleDelete(category)"
                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                  >
                    Delete
                  </div>
                </MenuItem>
              </template>

              <template v-else>
                <MenuItem class="cursor-pointer">
                  <div
                    @click="handleRestore(category)"
                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                  >
                    Restore
                  </div>
                </MenuItem>

                <MenuItem class="cursor-pointer">
                  <div
                    @click="handlePermanentDelete(category)"
                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                  >
                    Delete permanently
                  </div>
                </MenuItem>
              </template>
           </TableActions>
          </TableTd>
        </tr>
      </template>
      <template v-else>
        <tr>
          <TableTd colspan="4">
            <p class="p-6 text-center text-xl">No priority yet</p>
          </TableTd>
        </tr>
      </template>
    </template>

    <template v-slot:pagination>
      <Pagination
        class="border-t bg-gray-50 py-2 px-3"
        :from="1"
        :to="categories.length"
        :total="categories.length"
        :prev_page_url="null"
        :next_page_url="null"
        :links="[]"
        route-name="DashboardCategories"
        :query="{
          query: $route.query.query,
          department: $route.query.department,
          trash: $route.query.trash
        }"
      />
    </template>
  </TableCard>
</template>
