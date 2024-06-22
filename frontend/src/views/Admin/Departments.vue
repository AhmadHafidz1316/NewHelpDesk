<script setup lang="ts">
import TableCard from '@/components/Common/Table/TableCard.vue'
import ActionButton from '@/components/Common/ActionButton.vue'
import TableTd from '@/components/Common/Table/TableTd.vue'
import TableActions from '@/components/Common/Table/TableActions.vue'
import Pagination from '@/components/Common/Pagination.vue'
import TableSkeleton from '@/components/Common/Table/TableSkeleton.vue'
import NotFoundResults from '@/components/Common/NotFoundResults.vue'
import ConfirmationModal from '@/components/Admin/ConfirmationModal.vue'
import TrashSwitch from '@/components/Common/TrashSwitch.vue'
import DepartmentModal from '@/components/Admin/Departments/DepartmentModal.vue'
import SubDepartMentModal from '@/components/Admin/Departments/SubDepartmentModal.vue'

import { MenuItem } from '@headlessui/vue'

import PlusIcon from '@heroicons/vue/24/outline/PlusIcon'
import MagnifyingGlassIcon from '@heroicons/vue/24/outline/MagnifyingGlassIcon'
import ExclamationTriangleIcon from '@heroicons/vue/24/outline/ExclamationTriangleIcon'
import InformationCircleIcon from '@heroicons/vue/24/outline/InformationCircleIcon'

import { useHead } from 'unhead'

import { appTitle } from '@/global'

import { useDashboard } from '@/stores/dashboard'

import getDepartments from '@/composables/departments/getDepartments'
import useDepartments from '@/composables/departments/useDepartments'

import { ref, onMounted, watch, computed } from 'vue'

import type Department from '@/types/Department'
import type DepartmentsFilters from '@/types/DepartmentsFilters'

import { debounce } from 'lodash'

import { onBeforeRouteUpdate, useRoute, useRouter } from 'vue-router'

import { useToast } from 'vue-toastification'

useHead({ title: `Departments | ${appTitle}` })

const { setTitle } = useDashboard()

setTitle('Departments')

const headers = ['Department', 'Agents', 'Categories', 'Created At', '']

const route = useRoute()

const filters = ref({
  page: isNaN(Number(route.query.page)) ? null : Number(route.query.page),
  query: route.query.query ?? null,
  trash: route.query.trash === 'true' ? true : null,
  paginate: true
} as DepartmentsFilters)

const filtersAreApplied = computed(() => {
  return filters.value.query || filters.value.trash
})

const { load, isLoading, paginatedDepartments: departments } = getDepartments(filters)

const query = ref((route.query.query as string) ?? '')

const router = useRouter()

watch(
  query,
  debounce(async function (value: string) {
    filters.value.query = value

    const query: any = {}

    if (value) query.query = value
    if (filters.value.trash) query.trash = filters.value.trash

    router.push({ query })
  }, 300)
)

const trash = ref(Boolean(route.query.trash ?? false))

watch(trash, (value) => {
  filters.value.trash = value

  const query: any = {}

  if (value) query.trash = value

  router.push({ query })
})

onBeforeRouteUpdate(async (to, from) => {
  if (to.query.page !== from.query.page)
    filters.value = { ...filters.value, page: Number(to.query.page) }

  await load()
})

onMounted(async () => {
  await load()
})

const departmentModalIsOpen = ref(false)

const handleAfterSave = async (keepQuery: boolean) => {
  if (!Object.keys(route.query).length) await load()
  else if (!keepQuery) router.push({ query: {} })
  else await load()
}

const confirmationModalIsOpen = ref(false)
const confirmationModalTitle = ref('')
const confirmationModalText = ref('')
const confirmationModalButtonText = ref('')
const confirmationModalBackgroundColor = ref('')
const confirmationModalButtonBackgroundColor = ref('')
const confirmationModalColor = ref('')
const confirmationModalIcon = ref({} as any)
const confirmationModalFunction = ref({} as Function)

const { destroy, restore, message, isSuccess, isLoading: operationIsLoading } = useDepartments()

const handleAdd = () => {
  departmentToEdit.value = {} as Department

  departmentModalIsOpen.value = true
}

const departmentToEdit = ref({} as Department)

const handleEdit = (department: Department) => {
  departmentToEdit.value = department

  departmentModalIsOpen.value = true
}

const departmentToDelete = ref({} as Department)

const handleDelete = (department: Department) => {
  departmentToDelete.value = department

  confirmationModalIsOpen.value = true
  confirmationModalTitle.value = 'Delete Department'
  confirmationModalText.value = 'Are you sure you want to delete this department?'
  confirmationModalButtonText.value = 'Delete'
  confirmationModalBackgroundColor.value = 'bg-red-100'
  confirmationModalButtonBackgroundColor.value = 'bg-red-600 hover:bg-red-700 focus:ring-red-500'
  confirmationModalColor.value = 'text-red-600'
  confirmationModalIcon.value = ExclamationTriangleIcon
  confirmationModalFunction.value = deleteDepartment
}

const handlePermanentDelete = (department: Department) => {
  departmentToDelete.value = department

  confirmationModalIsOpen.value = true
  confirmationModalTitle.value = 'Permanent Delete Department'
  confirmationModalText.value =
    'Are you sure you want to permanently delete this department? This action cannot be undone.'
  confirmationModalButtonText.value = 'Delete'
  confirmationModalBackgroundColor.value = 'bg-red-100'
  confirmationModalButtonBackgroundColor.value = 'bg-red-600 hover:bg-red-700 focus:ring-red-500'
  confirmationModalColor.value = 'text-red-600'
  confirmationModalIcon.value = ExclamationTriangleIcon
  confirmationModalFunction.value = forceDelete
}

const departmentToRestore = ref({} as Department)

const handleRestore = (department: Department) => {
  departmentToRestore.value = department

  confirmationModalIsOpen.value = true
  confirmationModalTitle.value = 'Restore Department'
  confirmationModalText.value = 'Are you sure you want to restore this department?'
  confirmationModalButtonText.value = 'Restore'
  confirmationModalBackgroundColor.value = 'bg-blue-100'
  confirmationModalButtonBackgroundColor.value = 'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500'
  confirmationModalColor.value = 'text-blue-600'
  confirmationModalIcon.value = InformationCircleIcon
  confirmationModalFunction.value = restoreDepartment
}

const toast = useToast()

const deleteDepartment = async () => {
  await destroy(departmentToDelete.value.id)

  confirmationModalIsOpen.value = false

  if (isSuccess.value) {
    toast.success(message.value)

    await load()
  } else {
    toast.error(message.value)
  }
}

const forceDelete = async () => {
  await destroy(departmentToDelete.value.id, true)

  confirmationModalIsOpen.value = false

  if (isSuccess.value) {
    toast.success(message.value)

    await load()
  } else {
    toast.error(message.value)
  }
}

const restoreDepartment = async () => {
  await restore(departmentToRestore.value.id)

  confirmationModalIsOpen.value = false

  if (isSuccess.value) {
    toast.success(message.value)

    await load()
  } else {
    toast.error(message.value)
  }
}

const subDepartmentModalIsOpen = ref(false)

const handleAddSubDepartment = () => {
  subDepartmentModalIsOpen.value = true
}

const handleClickDepartment = (department: Department) => {
  if (selectedDepartment.value === department) {
    selectedDepartment.value = null // Toggle selection
  } else {
    selectedDepartment.value = department
  }
}

const selectedDepartment = ref(null as Department | null)

// Filter the departments to separate parents and children
const parentDepartments = computed(() => {
  return departments.value?.data?.filter((dept: Department) => dept.parent === null) || []
})

const childDepartments = computed(() => {
  return departments.value?.data?.filter((dept: Department) => dept.parent !== null) || []
})

const getNestedChildren = (parent: Department, allDepartments: Department[]) => {
  const children = allDepartments.filter((dept) => dept.parent === parent.id)
  return children.map((child) => ({
    ...child,
    children: getNestedChildren(child, allDepartments)
  }))
}

const totalAgents = computed(() => {
  let total = 0

  function countAgents(departments) {
    departments.forEach((dept) => {
      total += dept.agents_count || 0 // Tambahkan jumlah agen dari departemen saat ini
      if (dept.children) {
        countAgents(dept.children) // Rekursif hitung agen dari anak-anak departemen
      }
    })
  }

  countAgents(departments.value.data || [])

  return total
})
</script>

<template>
  <SubDepartMentModal
    :open="subDepartmentModalIsOpen"
    :departmentToEdit="departmentToEdit"
    @close="subDepartmentModalIsOpen = false"
    @success="handleAfterSave"
  />

  <DepartmentModal
    :open="departmentModalIsOpen"
    :departmentToEdit="departmentToEdit"
    @close="departmentModalIsOpen = false"
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

  <header class="mb-3 flex flex-col justify-between gap-3 sm:flex-row-reverse">
    <div class="flex justify-end sm:justify-start">
      <ActionButton :Icon="PlusIcon" text="New Department" :action="handleAdd" />
    </div>
    <div class="flex justify-end sm:justify-start">
      <ActionButton :Icon="PlusIcon" text="Add Sub Department" :action="handleAddSubDepartment" />
    </div>

    <div class="flex flex-1 gap-3">
      <div class="w-full max-w-sm">
        <label for="search" class="sr-only">Search</label>
        <div class="relative rounded-md shadow-sm">
          <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
            <MagnifyingGlassIcon class="h-5 w-5 text-gray-400" />
          </div>
          <input
            v-model="query"
            type="search"
            name="search"
            id="search"
            class="block w-full rounded-md border-gray-300 pl-10 text-sm focus:border-blue-500 focus:ring-blue-500"
            placeholder="Search"
          />
        </div>
      </div>

      <TrashSwitch :value="filters.trash" @change="(value) => (trash = value)" />
    </div>
  </header>

  <TableSkeleton v-if="isLoading" />

  <NotFoundResults
    v-else-if="!isLoading && !departments.value?.data?.length && filtersAreApplied"
    text="No departments related to your search"
  />

  <TableCard :headers="headers" v-else>
    <template v-slot:default>
      <template v-if="parentDepartments.length">
        <tr v-for="(department, index) in parentDepartments" :key="department.id">
          <TableTd @click="handleClickDepartment(department)">
            <div class="flex items-center">
              <span>{{ department.name }}</span>
            </div>

            <div
              class="ml-4 mt-1"
              v-for="child in getNestedChildren(department, departments.data)"
              :key="child.id"
            >
              <div class="flex items-center">
                > <span class="ml-4">{{ child.name }}</span>
              </div>
              <div class="ml-4 mt-1" v-for="grandchild in child.children" :key="grandchild.id">
                <div class="flex items-center">
                  > <span class="ml-8">{{ grandchild.name }}</span>
                </div>
                <div
                  class="ml-4 mt-1"
                  v-for="greatGrandchild in grandchild.children"
                  :key="greatGrandchild.id"
                >
                  <div class="flex items-center">
                    > <span class="ml-12">{{ greatGrandchild.name }}</span>
                  </div>
                  <!-- Tambahkan lebih banyak tingkatan jika perlu -->
                </div>
              </div>
            </div>
          </TableTd>
          <TableTd>
            <template v-if="parentDepartments.length">
              <div v-if="department.agents_count" class="ml-1 mt-10">
                {{ department.agents_count }} Agent
              </div>
              <template v-for="child in getNestedChildren(department, departments.data)">
                <div v-if="child.agents_count" class="ml-6 mt-2">{{ child.agents_count }} Agent</div>
                <template v-for="grandchild in child.children">
                  <div v-if="grandchild.agents_count" class="ml-9 mt-2">
                    {{ grandchild.agents_count }} Agent
                  </div>
                  <template v-for="greatGrandchild in grandchild.children">
                    <div v-if="greatGrandchild.agents_count" class="ml-12 mt-1">
                      {{ greatGrandchild.agents_count }} Agent
                    </div>
                  </template>
                </template>
              </template>
            </template>
          </TableTd>

          <TableTd>
            <template v-if="parentDepartments.length">
              <div v-if="department.categories_count" class="ml-1 mt-10">
                {{ department.categories_count }} Category
              </div>
              <template v-for="child in getNestedChildren(department, departments.data)">
                <div v-if="child.categories_count" lass="ml-6 mt-2">
                  {{ child.categories_count }} Category
                </div>
                <template v-for="grandchild in child.children">
                  <div v-if="grandchild.categories_count" class="ml-9 mt-2">
                    {{ grandchild.categories_count }} Category
                  </div>
                  <template v-for="greatGrandchild in grandchild.children">
                    <div v-if="greatGrandchild.categories_count" class="ml-12 mt-1">
                      {{ greatGrandchild.categories_count }} Category
                    </div>
                  </template>
                </template>
              </template>
            </template>
          </TableTd>

          <TableTd>
            {{ department.created_at }}
          </TableTd>

          <TableTd>
            <TableActions>
              <template v-if="$route.query.trash != 'true'">
                <MenuItem class="cursor-pointer">
                  <div
                    @click="handleEdit(department)"
                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                  >
                    Edit
                  </div>
                </MenuItem>

                <MenuItem class="cursor-pointer">
                  <div
                    @click="handleDelete(department)"
                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                  >
                    Delete
                  </div>
                </MenuItem>
              </template>

              <template v-else>
                <MenuItem class="cursor-pointer">
                  <div
                    @click="handleRestore(department)"
                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                  >
                    Restore
                  </div>
                </MenuItem>

                <MenuItem class="cursor-pointer">
                  <div
                    @click="handlePermanentDelete(department)"
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
          <TableTd colspan="5">
            <p class="p-6 text-center text-xl">No departments yet</p>
          </TableTd>
        </tr>
      </template>
    </template>

    <template v-slot:pagination>
      <Pagination
        class="border-t bg-gray-50 py-2 px-3"
        :from="departments.from"
        :to="departments.to"
        :total="departments.total"
        :prev_page_url="departments.prev_page_url"
        :next_page_url="departments.next_page_url"
        :links="departments.links"
        route-name="DashboardDepartments"
        :query="{
          trash: $route.query.trash,
          query: $route.query.query
        }"
      />
    </template>
  </TableCard>
</template>
