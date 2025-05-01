<script setup>
import {onMounted} from "vue"
import {useRoute} from "vue-router"
import {useAuth} from "@/stores"
const route = useRoute();
const auth = useAuth();
onMounted(async()=>{
  $('[data-widget="treeview"]').Treeview('init');
  await auth.getInstantRolePermissions();
})
</script>

<template>
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <router-link :to="{name: 'user.login'}" class="brand-link text-center">
      <!-- <img
         src="dist/img/AdminLTELogo.png"
         alt="AdminLTE Logo"
         class="brand-image img-circle elevation-3"
         style="opacity: 0.8"
       -->
      <span class="brand-text font-weight-light text-white font-weight-bold"><b style="color: #3cc913;">LunchBox</b> <span style="color:#ff8300;">GCCC</span></span>
    </router-link>

    <!-- Sidebar -->
    <div class="sidebar">
      <div v-if="auth.loading" class="text-center m-5">
          <span
              class="spinner-border spinner-border-sm mr-1 text-white"
              style="padding: 12px; font-size: 20px;"
          ></span>
          <div class="text-white">Loading....</div>
      </div>
      <!-- Sidebar Menu -->
      <nav class="mt-2" v-else>
        <ul
          class="nav nav-pills nav-sidebar flex-column"
          data-widget="treeview"
          role="menu"
          data-accordion="false"
        >

          <li class="nav-item" v-show="$filters.hasPermission('dashboard.read')">
            <router-link :to="{name: 'user.dashboard'}" class="nav-link">
              <i class="nav-icon fas fa-tachometer-alt"></i>
              <p>
                Dashboard
              </p>
            </router-link>
          </li>

          <li class="nav-item" v-show="$filters.hasPermission('role.read')">
            <a class="nav-link" :class="(route.name=='role.index' || route.name=='role.add' || route.name=='role.edit') ? 'active' : ''" style="cursor: pointer;">
              <i class="nav-icon fab fa-critical-role"></i>
              <p>
                Role & Permissions
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item ml-3" v-show="$filters.hasPermission('role.create')">
                <router-link :to="{name: 'role.add'}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Add Role (+)</p>
                </router-link>
              </li>
              <li class="nav-item ml-3">
                <router-link :to="{name: 'role.index'}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Roles</p>
                </router-link>
              </li>
            </ul>
          </li>

          <li class="nav-item" v-show="$filters.hasPermission('user.read')">
            <a class="nav-link" :class="(route.name=='user.index' || route.name=='user.add' || route.name=='user.edit') ? 'active' : ''" style="cursor: pointer;">
              <i class="nav-icon fas fa-users"></i>
              <p>
                User Management
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item ml-3" v-show="$filters.hasPermission('user.create')">
                <router-link :to="{name: 'user.add'}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Add User (+)</p>
                </router-link>
              </li>
              <li class="nav-item ml-3">
                <router-link :to="{name: 'user.index'}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Users</p>
                </router-link>
              </li>
            </ul>
          </li>

          <li class="nav-item" v-show="$filters.hasPermission('order.read')">
          <!-- <li class="nav-item ml-3"> -->
            <a class="nav-link" :class="(route.name=='order.index' || route.name=='order.add' || route.name=='order.edit' || route.name=='order.view') ? 'active' : ''" style="cursor: pointer;">
              <i class="nav-icon fas fa-suitcase-rolling"></i>
              <p>
                Order
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item ml-3" v-show="$filters.hasPermission('order.create')">
                <router-link :to="{name: 'order.add'}" class="nav-link" :class="{ disabled: true }">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Add Order (+)</p>
                </router-link>
              </li>
              <li class="nav-item ml-3">
                <router-link :to="{name: 'order.index'}" class="nav-link" :class="{ disabled: true }">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Orders</p>
                </router-link>
              </li>
            </ul>
          </li>


        </ul>
      </nav>
      <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
  </aside>
</template>


<style>
.disabled {
    opacity: 0.8;
    pointer-events: none;
}
</style>