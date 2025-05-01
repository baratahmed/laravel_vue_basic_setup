import { defineStore } from "pinia";
import { useToken } from "@/stores";
import axiosInstance from "@/services/AxiosService";

export const useAuth = defineStore("auth", {
  state: () => ({
    user: {},
    isLoggedIn: false,
    errors: {},
    loading: false,
    role: null,
    permissions: null,
  }),
  getters: {
    getUser: (state)=>{
      return state.user;
    },
    getAuthStatus: (state)=>{
      return state.isLoggedIn;
    },
  },
  persist: {
    paths: ["user","isLoggedIn"],
  },
  actions: {
    async login(formData) {
      try {
        const res = await axiosInstance.post("/login", formData);
        if (res.status === 200) {
          this.setAuthInfo(res.data);
          this.role = res.data.data.role;
          this.permissions = res.data.data.permissions;
          return res.data;
          // return new Promise((resolve) => {
          //   resolve(res.data);
          // });
        }
      } catch (error) {
        if (error.response.data) {
          // this.errors = error.response.data.errors;
          throw error.response.data;
          // return new Promise((reject) => {
          //   reject(error.response.data.errors);
          // });
        }
      }
    },

    async logout() {
      this.loading = true;
      try {
        const res = await axiosInstance.post("/logout");
        if (res.status === 200) {
          window.location.href = '/'
          this.removeAuthInfo();
          return res;
        }
      } catch (error) {
        if (error.response) {
            throw(error.response);
        }
      }finally{
        this.loading = false;
      }
    },

    setAuthInfo(data){
      const token = useToken();
      this.user = data?.data;
      token.setToken(data?.meta?.token);
      this.isLoggedIn = true;
    },

    removeAuthInfo(){
      const token = useToken();
      token.removeToken();
      this.$reset();
    },

    async getInstantRolePermissions() {
      try {
        this.loading = true;
        const res = await axiosInstance.get(`/instant/role/permissions/${this.user?.id}`,);
        if (res.status === 200) {
          this.role = res.data.role;
          this.permissions = res.data.permissions;
        }
      } catch (error) {
        if (error.response.data) {

        }
      }finally{
        this.loading = false;
      }
    },


    async updateProfileImage(data) {
      try {
        const res = await axiosInstance.post("/update/profile/image",data);
        if (res.status === 200) {
          this.user.image = res.data;
        }
      } catch (error) {
          console.log(error)
      } finally {
 
      }
    },
    
    async updateProfile(form) {
      try {
        const res = await axiosInstance.post('/update/profile',form);
        if (res.status === 200) {
          this.user.name = form.name
          this.user.email = form.email
          this.user.phone = form.phone
          return new Promise((resolve) => {
            resolve(res.data);
          });
        }
      } catch (error) {
        if (error.response.data) {
          this.errors = error.response.data.errors
        }
      }finally{
        
      }
    },

    async changePassword(form) {
      try {
        this.loading = true;
        const res = await axiosInstance.post('/change/password',form);
        if (res.status === 200) {
          this.user.real_password = ''
          return new Promise((resolve) => {
            resolve(res.data);
          });
        }
      } catch (error) {
        if (error.response.data) {

        }
      }finally{
        this.loading = false;
      }
    },


  },
});
