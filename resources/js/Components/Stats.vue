<template>
  <div>
    <h2 class="text-xl font-semibold mb-4 dark:text-lime-400" v-if="title">{{ title }}</h2>
    <dl class="mx-auto grid grid-cols-1 gap-px bg-gray-800 sm:grid-cols-2 lg:grid-cols-4 opacity-75">
      <div v-for="stat in stats" :key="stat.name" class="flex flex-wrap opacity-70 items-baseline justify-between gap-x-4 gap-y-2 bg-white dark:bg-gray-800 px-4 py-10 sm:px-6 xl:px-8">
        <dt class="text-sm/6 font-medium text-gray-500 dark:text-lime-400 flex items-center">
          <!-- Add icon support -->
          <span v-if="stat.icon" class="mr-2" v-html="stat.icon"></span>
          {{ stat.name }}
        </dt>
        <dd :class="[stat.changeType === 'negative' ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400', 'text-xs font-medium']">{{ stat.change }}</dd>
        <dd class="w-full flex-none text-3xl/10 font-medium tracking-tight text-gray-900 dark:text-white">{{ stat.value }}</dd>
      </div>
    </dl>
  </div>
</template>
    
<script setup>
// Accept stats as a prop instead of hardcoding them
defineProps({
  title: {
    type: String,
    default: ''
  },
  stats: {
    type: Array,
    required: true,
    default: () => [
      { name: 'Revenue', value: '$0.00', change: '0%', changeType: 'neutral' },
      { name: 'Completed Work Orders', value: '0', change: '0%', changeType: 'neutral' },
      { name: 'Pending Work Orders', value: '0', change: '0%', changeType: 'neutral' },
      { name: 'Average Price', value: '$0.00', change: '0%', changeType: 'neutral' },
    ]
  }
});
</script>