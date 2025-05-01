import { defineStore } from "pinia";
import axiosInstance from "@/services/AxiosService";

export const useRole = defineStore("role", {
  state: () => ({
    items: [],
    permissions_by_group_name: [],
    role: [],
    total_permissions: 0,
    errors: {},
    loading: false,
    storeLoading: false,
    updateLoading: false,
  }),
  getters: {
    getItems: (state)=>{
      return state.items;
    },
  },

  actions: {
    async index() {
      try {
        this.loading = true
        const res = await axiosInstance.get("/roles");
        if (res.status === 200) {
          this.items = res.data;
          return res.data;
        }
      } catch (error) {
        if (error.response.data) {
          throw error.response.data.errors;
        }
      }finally{
        this.loading = false
      }
    },
    async fetchPermissionsByGroupName(form) {
      try {
        this.loading = true
        const res = await axiosInstance.get(`/fetch/permissions/by/group/name?id=${form.get('id')}`);
        if (res.status === 200) {
          this.permissions_by_group_name = res.data.permissions_by_groups;
          this.role = res.data.role;
          return res.data;
        }
      } catch (error) {
        if (error.response.data) {
          this.errors = error.response.data.errors;
        }
      }finally{
        this.loading = false
      }
    },
    async store(form) {
      try {
        this.storeLoading = true
        const res = await axiosInstance.post("/roles",form);
        if (res.status === 200) {
          return res.data;
        }
      } catch (error) {
        if (error.response.data) {
          this.errors = error.response.data.errors;
        }
      }finally{
        this.storeLoading = false
      }
    },

    async update(form) {
      try {
        this.updateLoading = true;
        const res = await axiosInstance.put(`/roles/${form.id}`,form);
        return new Promise((resolve) => {
          resolve(res.data);
        });
      } catch (error) {
        if (error.response.data) {
          this.errors = error.response.data.errors
        }
      }finally{
        this.updateLoading = false;
      }
    },


    async destroy(id) {
      try {
        const res = await axiosInstance.delete(`/roles/${id}`);
        if (res.status === 200) {
            let index = this.items.findIndex(
              (item) => item?.id == id
            );
            this.items.splice(index,1)
            return new Promise((resolve) => {
              resolve(res.data);
            });
        }
      } catch (error) {
        if (error.response.data) {
          throw error.response.data.errors;
        }
      }
    },


  },
});
