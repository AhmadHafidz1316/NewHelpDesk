<script setup lang="ts">
import Modal from '@/components/Common/Modal.vue'
import FormInput from '@/components/Forms/FormInput.vue'
import ListBox from '@/components/Forms/ListBox.vue'
import PrimaryButton from '@/components/Forms/PrimaryButton.vue'
import FormLabel from '@/components/Forms/FormLabel.vue'
import Errors from '@/components/Forms/Errors.vue'
import useUsers from '@/composables/users/useUsers'

import type Option from '@/types/Option'
import type User from '@/types/User'

import { ref, computed, watch, onMounted } from 'vue'
import { useToast } from 'vue-toastification'
import axios from 'axios'

const props = defineProps<{
  open: boolean
  departments: Option[]
  userToEdit: User
}>()

const emit = defineEmits<{
  (e: 'close'): void
  (e: 'success', keepQuery: boolean): void
}>()

const name = ref('')
const email = ref('')
const phone = ref('')
const password = ref('')
const role = ref({} as Option)
const department = ref({} as Option)
const picture = ref({} as File | null)
const parentUser = ref({} as Option)

const roles = ref([
  { name: 'Agent', value: 'agent' },
  { name: 'Admin', value: 'admin' }
] as Option[])

const users = ref<Option[]>([])

const method = computed(() => (props.userToEdit.id ? 'put' : 'post'))

const isAgent = computed(() => role.value?.value === 'agent')

const API_URL = 'http://127.0.0.1:8000/api/users' // Ganti dengan URL API yang sesuai

const defaultImagePath = API_URL + 'storage/examples/user.jpg'

const previewImage = ref(defaultImagePath)

const onFileChange = (e: any) => {
  const file = e.target.files[0]
  previewImage.value = URL.createObjectURL(file)
  picture.value = file
}

const resetInput = ref(false)

const title = computed(() => {
  if (props.userToEdit.id) {
    return 'Edit User'
  } else {
    return 'New User'
  }
})

const buttonText = computed(() => {
  if (props.userToEdit.id) {
    return 'Update'
  } else {
    return 'Create'
  }
})

const toast = useToast()

const { save, isLoading, isSuccess, errors, message } = useUsers()

const reset = () => {
  resetInput.value = true

  name.value = ''
  email.value = ''
  phone.value = ''
  password.value = ''
  role.value = {} as Option
  department.value = {} as Option
  picture.value = {} as File | null
  previewImage.value = defaultImagePath
}

const onSubmit = async () => {
  const departmentId = selectedNode.value?.id || selectedParentNode.value?.id

  if (!departmentId) {
    toast.error('Please select a department')
    return
  }

  await save(
    {
      name: name.value,
      email: email.value,
      phone: phone.value,
      password: password.value,
      role: role.value?.value,
      department_id: isAgent.value ? departmentId : null,
      parent: parentUser.value?.value,
      picture: picture.value
    },
    method.value,
    props.userToEdit.id ?? null
  )

  if (isSuccess.value) {
    toast.success(message.value)

    reset()

    if (method.value === 'post') emit('success', false)
    else emit('success', true)

    emit('close')
  } else {
    toast.error(message.value)
  }

  // Log departmentId value to console
  console.log('Department ID:', departmentId)
  
  console.log('Parent User ID:', parentUser.value?.value)
}

const fetchUsers = async () => {
  try {
    const response = await axios.get(API_URL)
    users.value = response.data.data.map((user: User) => ({
      name: user.name,
      value: user.id.toString()
    }))
  } catch (error) {
    toast.error('Failed to load users')
  }
}

onMounted(fetchUsers)

watch(
  () => props.userToEdit,
  () => {
    if (props.userToEdit.id) {
      name.value = props.userToEdit.name
      email.value = props.userToEdit.email
      phone.value = props.userToEdit.phone
      role.value = roles.value.find((role) => role.value === props.userToEdit.role) ?? ({} as Option)
      previewImage.value = API_URL + props.userToEdit.picture
      parentUser.value = users.value.find(user => user.value === props.userToEdit.parent) ?? null
    } else {
      reset()
    }
  }
)

watch(
  () => props.departments && props.userToEdit,
  () => {
    if (props.userToEdit.id) {
      department.value =
        props.departments.find(
          (department) => department.value === props.userToEdit.department?.id.toString()
        ) ?? ({} as Option)
    }
  }
)

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
        <div class="flex flex-col gap-4 sm:flex-row">
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
            id="email"
            label="Email"
            placeholder="Email"
            type="email"
            :value="email"
            @change="(value) => (email = value)"
            :errors="errors.email"
            :reset="resetInput"
            @reset="() => (resetInput = false)"
          />
        </div>

        <div class="flex flex-col gap-4 sm:flex-row">
          <FormInput
            class="w-full"
            id="phone"
            label="Phone"
            placeholder="Phone"
            type="tel"
            :value="phone"
            @change="(value) => (phone = value)"
            :errors="errors.phone"
            :reset="resetInput"
            @reset="() => (resetInput = false)"
          />

          <FormInput
            class="w-full"
            id="password"
            label="Password"
            placeholder="Password"
            type="password"
            @change="(value) => (password = value)"
            :errors="errors.password"
            :reset="resetInput"
            @reset="() => (resetInput = false)"
          />
        </div>

        <ListBox
          null-text="Select a role"
          class="w-full"
          label="Role"
          :selected="role"
          :options="roles"
          @update="(value) => (role = value)"
          :errors="errors.role"
          :reset="resetInput"
          @reset="() => (resetInput = false)"
        />

        <div class="relative w-full">
          <label for="department" class="block text-sm font-medium text-gray-700">Department</label>
          <div class="mt-1">
            <input
              v-if="isAgent"
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


        <ListBox
          v-if="isAgent"
          null-text="Optional"
          class="w-full"
          label="Parent User"
          :selected="parentUser"
          :options="users"
          @update="(value) => (parentUser = value)"
          :errors="errors.parent"
          :reset="resetInput"
          @reset="() => (resetInput = false)"
        />

        <div>
          <FormLabel text="Picture" id="profile-picture" />

          <div class="flex flex-wrap items-center gap-3">
            <img
              class="relative h-20 w-20 rounded-full border object-cover"
              :src="previewImage"
              alt="Profile Picture"
            />

            <div>
              <label
                for="profile-picture"
                class="cursor-pointer rounded-md border border-gray-300 py-2 px-3 text-sm font-medium text-gray-700 hover:bg-gray-100"
              >
                Change
              </label>

              <input
                ref="temp"
                @change="onFileChange"
                type="file"
                id="profile-picture"
                class="hidden"
              />
            </div>
          </div>

          <Errors v-if="errors.picture" :errors="errors.picture" />
        </div>
      </div>

      <div class="mt-3 flex justify-end border-t bg-gray-50 px-6 py-3">
        <PrimaryButton type="submit" :text="buttonText" :loading="isLoading" />
      </div>
    </form>
  </Modal>
</template>
