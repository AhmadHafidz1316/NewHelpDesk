<template>
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
  </template>
  
  <script setup lang="ts">
  import { ref, onMounted } from 'vue'
  import axios from 'axios'
  
  const props = defineProps<{
    departments: any[]
  }>()
  
  const emit = defineEmits<{
    (e: 'selectNode', node: any): void
  }>()
  
  const treeData = ref([] as any[])
  const selectedParentNode = ref(null as any | null)
  const selectedNode = ref(null as any | null)
  const showDropdown = ref(false)
  
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
  
  const selectParentNode = (node: any) => {
    selectedParentNode.value = node
    selectedNode.value = null
    emit('selectNode', node)
  }
  
  const selectNode = (node: any) => {
    selectedNode.value = node
    selectedParentNode.value = null
    emit('selectNode', node)
    showDropdown.value = false
  }
  </script>
  