<script setup lang="ts">
import { usePage } from '@inertiajs/vue3'

// A product alert sign-up link (Provider\PriceAlert, Provider\StockAlert)
withDefaults(defineProps<Lynx.ProductPriceAlert | Lynx.ProductStockAlert>(), {
    url: '',
    label: '',
})

// The editor's preview shows the link wherever it is placed, so the admin can find it
const preview = !!usePage().props.preview
</script>

<template>
    <!-- A plain anchor: the endpoint sends a guest to sign in first -->
    <a v-if="url || preview" :href="url || '#'" class="text-base text-link underline underline-offset-4">{{ label }}</a>
</template>
