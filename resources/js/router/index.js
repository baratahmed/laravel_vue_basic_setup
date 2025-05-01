import { createRouter, createWebHistory } from "vue-router";
import { useAuth } from "@/stores";
import NProgress from "nprogress";
import Dashboard from "@/pages/Dashboard.vue";
import Login from "@/pages/auth/Login.vue";

import {AddRole,EditRole,RoleIndex} from "@/pages/role";
import {AddUser,EditUser,UserIndex,MyProfile} from "@/pages/user";
import {AddOrder,EditOrder,ViewOrder,OrderIndex} from "@/pages/order";


const routes = [
    {
      path: "/",
      name: "user.login",
      component: Login,
      meta: { title: "Login", guest: true },
    },
    {
      path: "/dashboard",
      name: "user.dashboard",
      component: Dashboard,
      meta: { title: "Dashboard", requiresAuth: true },
    },


    // Role & Permission
    {
      path: "/roles",
      name: "role.index",
      component: RoleIndex,
      meta: { title: "All Roles", requiresAuth: true },
    },
    {
      path: "/add/role",
      name: "role.add",
      component: AddRole,
      meta: { title: "Add Role", requiresAuth: true },
    },
    {
      path: "/edit/role/:id",
      name: "role.edit",
      component: EditRole,
      meta: { title: "Edit Role", requiresAuth: true },
    },  

    // User Management
    {
      path: "/users",
      name: "user.index",
      component: UserIndex,
      meta: { title: "All Users", requiresAuth: true },
    },
    {
      path: "/add/user",
      name: "user.add",
      component: AddUser,
      meta: { title: "Add User", requiresAuth: true },
    },
    {
      path: "/edit/user/:id",
      name: "user.edit",
      component: EditUser,
      meta: { title: "Edit User", requiresAuth: true },
    },  
    {
      path: "/my/profile/:id",
      name: "user.profile",
      component: MyProfile,
      meta: { title: "My Profile", requiresAuth: true },
    }, 

    // Order
    {
      path: "/orders",
      name: "order.index",
      component: OrderIndex,
      meta: { title: "All Orders", requiresAuth: true },
    },
    {
      path: "/add/order",
      name: "order.add",
      component: AddOrder,
      meta: { title: "Add Order", requiresAuth: true },
    },
    {
      path: "/view/order/:id",
      name: "order.view",
      component: ViewOrder,
      meta: { title: "View Order", requiresAuth: true },
    },
    {
      path: "/edit/order/:id",
      name: "order.edit",
      component: EditOrder,
      meta: { title: "Edit Order", requiresAuth: true },
    },


];

const router = createRouter({
  history: createWebHistory(),
  routes,
  linkActiveClass: 'active',
  scrollBehavior() {
    // always scroll to top
    return { top: 0, behavior: "smooth" };
  },
});

const DEFAULT_TITLE = "404";

router.beforeEach((to, from, next) => {
  document.title = to.meta.title || DEFAULT_TITLE;
  NProgress.start();

  const auth = useAuth();

  if (to.matched.some((record) => record.meta.requiresAuth)) {
    if (!auth.getAuthStatus) {
      next({ name: "user.login" });
    } else {
      next();
    }
  } else if (to.matched.some((record) => record.meta.guest)) {
    if (auth.getAuthStatus) {
      next({ name: "user.dashboard" });
    } else {
      next();
    }
  } else {
    next();
  }
});

router.afterEach(() => {
  NProgress.done();
});
export default router;
