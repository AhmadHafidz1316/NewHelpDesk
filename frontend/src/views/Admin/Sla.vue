<script setup lang="ts">
import { ref } from 'vue'

const slaOptions = [
  { name: 'Low', value: 'low', responseTime: '4 hours' },
  { name: 'Medium', value: 'medium', responseTime: '2 hours' },
  { name: 'High', value: 'high', responseTime: '1 hour' }
]

const selectedSLA = ref('') // To store selected SLA option

const onSubmit = async () => {
  if (isLoading.value) return

  await create({
    priority: priority.value?.value,
    category_id: category.value?.value,
    subject: subject.value,
    description: description.value,
    attachments: files.value,
    sla_level: selectedSLA // Add SLA data to the ticket creation payload
  })

  if (isSuccess.value) {
    toast.success(message.value)
    router.push({ name: 'ClientSingleTicket', params: { reference: ticket.value.reference } })
  } else {
    toast.error(message.value)
  }
}


</script>

<template> 
 
<ListBox
  @update="(newPriority) => (priority = newPriority)"
  :selected="priority"
  label="Priority"
  :options="priorities"
  :errors="errors?.priority"
  null-text="Select a priority"
/>

<Autocomplete
  @update="(newCategory) => (category = newCategory)"
  :selected="category"
  label="Category"
  :options="categories"
  :errors="errors?.category_id"
  null-text="Select a category"
/>

<!-- SLA selection -->
<div class="mt-4">
  <label class="block text-sm font-medium text-gray-700">SLA Response Time</label>
  <select v-model="selectedSLA" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
    <option v-for="sla in slaOptions" :key="sla.value" :value="sla.value">{{ sla.name }} - {{ sla.responseTime }}</option>
  </select>
</div>

</template>