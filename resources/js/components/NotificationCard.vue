<template>
  <div class="bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700 p-6">
    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
      {{ title }}
    </h3>
    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
      {{ subtitle }}
    </p>
    <div v-if="actionUrl" class="mt-4">
      <a
        :href="actionUrl"
        :target="actionTarget"
        class="inline-flex items-center px-4 py-2 text-sm font-medium text-white rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2"
        :class="buttonClass"
      >
        {{ actionLabel }}
      </a>
    </div>
  </div>
</template>

<script>
export default {
    props: {
        title: {
            type: String,
            required: true,
        },
        subtitle: {
            type: String,
            default: '',
        },
        actionLabel: {
            type: String,
            default: 'View',
        },
        actionUrl: {
            type: String,
            default: null,
        },
        actionTarget: {
            type: String,
            default: '_self',
        },
        variant: {
            type: String,
            default: 'primary',
            validator: (value) => ['primary', 'success', 'warning', 'danger'].includes(value),
        },
    },
    computed: {
        buttonClass() {
            const classes = {
                primary: 'bg-primary-500 hover:bg-primary-600 focus:ring-primary-500',
                success: 'bg-green-500 hover:bg-green-600 focus:ring-green-500',
                warning: 'bg-yellow-500 hover:bg-yellow-600 focus:ring-yellow-500',
                danger: 'bg-red-500 hover:bg-red-600 focus:ring-red-500',
            }
            return classes[this.variant] || classes.primary
        },
    },
}
</script>
