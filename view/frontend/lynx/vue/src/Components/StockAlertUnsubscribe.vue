<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { useFormKeyStore } from 'OOCS_LynxTheme/stores/global/formKeyStore'

// The stock alert's unsubscribe link lands here; the form posts itself, as core's form-submitter does
const props = withDefaults(defineProps<Lynx.StockAlertUnsubscribe>(), {
    action: '#',
    product_id: 0,
    labels: () => ({}),
})

const formKey = useFormKeyStore()
const form = ref<HTMLFormElement>()
const page = usePage()

// The editor's preview stays on the page. The key is set on the input itself: a re-render would come after submit()
onMounted(() => {
    if (page.props.preview || !form.value) return
    ;(form.value.elements.namedItem('form_key') as HTMLInputElement).value = formKey.ensure()
    form.value.submit()
})
</script>

<template>
    <form ref="form" :action="props.action" method="post" class="w-full text-center" role="status" aria-live="polite">
        <input type="hidden" name="form_key" />
        <input type="hidden" name="product" :value="props.product_id" />
        <svg class="w-8 h-8 mx-auto animate-spin text-primary" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4l3-3-3-3v4a8 8 0 00-8 8h4z" />
        </svg>
        <span class="sr-only">{{ props.labels.loading }}</span>
    </form>
</template>
