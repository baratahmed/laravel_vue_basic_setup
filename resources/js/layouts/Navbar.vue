<script setup>
import {useRouter} from "vue-router";
import {useAuth} from "@/stores";
const auth = useAuth();
const router = useRouter();
const handleLogout = async () => {
  try {
    const res = await auth.logout();
    if(res.data.status){
      router.push({name: 'user.login'});
    }
  } catch (error) {

  }
}
const goToMyProfilePage = () => {
  router.push({'name':'user.profile', params: {id: auth.user?.id}});
}

</script>
<template>
  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button"
          ><i class="fas fa-bars"></i
        ></a>
      </li>

    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">

      <!-- Notifications Dropdown Menu -->
      <li class="nav-item dropdown" v-show="false">
        <a class="nav-link" data-toggle="dropdown" href="#">
          <i class="far fa-bell"></i>
          <span class="badge badge-warning navbar-badge">15</span>
        </a>
        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
          <span class="dropdown-item dropdown-header">15 Notifications</span>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item">
            <i class="fas fa-envelope mr-2"></i> 4 new messages
            <span class="float-right text-muted text-sm">3 mins</span>
          </a>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item">
            <i class="fas fa-users mr-2"></i> 8 friend requests
            <span class="float-right text-muted text-sm">12 hours</span>
          </a>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item">
            <i class="fas fa-file mr-2"></i> 3 new reports
            <span class="float-right text-muted text-sm">2 days</span>
          </a>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item dropdown-footer"
            >See All Notifications</a
          >
        </div>
      </li>
      <!-- User Dropdown Menu -->
      <li class="nav-item dropdown">
        <a class="nav-link" data-toggle="dropdown" href="#">
          <i class="far fa-user-circle fa-2x"></i>
        </a>
        <div class="dropdown-menu dropdown-menu-right">
          <button class="dropdown-item" @click.prevent="goToMyProfilePage()">
            <i class="fas fa-user mr-2"></i> My Profile
          </button>
          <div class="dropdown-divider" v-show="auth.user?.role != 'Super Admin'"></div>
          <div class="dropdown-divider"></div>
          <a href="javascript::void(0);" class="dropdown-item" @click="handleLogout">
            <i class="fas fa-power-off mr-2"></i> Logout
          </a>
          <div class="dropdown-divider"></div>
        </div>
      </li>

    </ul>
  </nav>
</template>
