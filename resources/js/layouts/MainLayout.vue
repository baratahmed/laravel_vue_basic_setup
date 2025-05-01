<script setup>
import PageHeader from "./PageHeader.vue";
import Navbar from "./Navbar.vue";
import Sidebar from "./Sidebar.vue";
</script>
<template>
  <Navbar />
  <Sidebar />

  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <PageHeader />
    <!-- /.content-header -->
    <slot />
  </div>
</template>
