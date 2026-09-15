<script setup lang="ts">
// Transposition de backend/resources/views/layouts/main.blade.php.
useHead({ bodyAttrs: { class: 'bg-gray-50' } })
</script>

<template>
  <div>
    <SiteHeader />

    <main class="mt-20">
      <slot />
    </main>

    <SiteFooter />
  </div>
</template>
