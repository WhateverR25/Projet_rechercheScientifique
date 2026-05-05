<template>
  <router-view v-slot="{ Component }">
    <transition name="route-dissolve" mode="out-in">
      <component :is="Component" />
    </transition>
  </router-view>
</template>

<style>
/* ─── Top-level route transition (auth ↔ dashboard) ─── */
/* Inspired by Linear / Stripe — subtle opacity + scale dissolve */
.route-dissolve-enter-active {
  transition: opacity 0.4s cubic-bezier(0.16, 1, 0.3, 1),
              transform 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}
.route-dissolve-leave-active {
  transition: opacity 0.25s cubic-bezier(0.4, 0, 1, 1),
              transform 0.25s cubic-bezier(0.4, 0, 1, 1);
}
.route-dissolve-enter-from {
  opacity: 0;
  transform: scale(0.98) translateY(6px);
}
.route-dissolve-leave-to {
  opacity: 0;
  transform: scale(0.99) translateY(-4px);
}
</style>
