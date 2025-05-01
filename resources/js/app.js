import './bootstrap';
import './style.css';
import "admin-lte/plugins/bootstrap/js/bootstrap.bundle.min.js";
import "admin-lte/dist/js/adminlte.min.js";
import DropZone from 'dropzone-vue';
import 'dropzone-vue/dist/dropzone-vue.common.css';
import ElementPlus from "element-plus";
import piniaPluginPersistedstate from "pinia-plugin-persistedstate";
import "skeleton-screen-css";
import "element-plus/dist/index.css";
import VueSelect from "vue-select";
import "vue-select/dist/vue-select.css"

import "lightgallery.js/dist/css/lightgallery.css"
import "lightgallery.js/dist/js/lightgallery.js"
import "icheck-bootstrap/icheck-bootstrap.min.css"
import Swal from 'sweetalert2'
window.Swal = Swal

import { createPinia } from "pinia";
const pinia = createPinia();
pinia.use(piniaPluginPersistedstate);

import router from "./router"
import { createApp } from 'vue'
import App from './App.vue'

const app = createApp(App).component("v-select", VueSelect);

app.use(router)
app.use(pinia);
app.use(ElementPlus);
app.use(DropZone);

import { useAuth } from "@/stores";
const auth = useAuth();

app.config.globalProperties.$filters = {
    currencySymbol(value) {
      return "৳" + value;
    },

    makeImagePath(img) {
      if(import.meta.env.VITE_APP_URL.includes('127.0.0.1:8000')){
        return import.meta.env.VITE_APP_URL + "/" + img;
      }else{
        return import.meta.env.VITE_APP_URL + "/public/" + img;
      }
    },

    textShort(text, size) {
      if (!text) return "";
      text = text.toString();

      if (text.length <= size) {
        return text;
      }
      return text.substr(0, size) + "...";
    },

    hasPermission(permission_name){
        if(auth.isLoggedIn && auth.permissions){
          return auth.permissions.some(el => el.name == permission_name)
        }
    }
 };

app.mount('#app')

