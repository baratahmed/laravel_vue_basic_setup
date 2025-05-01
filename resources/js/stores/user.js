import { defineStore } from "pinia";
import axiosInstance from "@/services/AxiosService";
import { useBulkDelete } from "@/stores";

export const useUser = defineStore("user", {
  state: () => ({
    items: [],
    user: [],
    roles: [],
    errors: {},
    loading: false,
  }),
  getters: {
    getItems: (state)=>{
      return state.items;
    },
  },

  actions: {
    async index(page, perPage, searchQuery, shop_id) {
      try {
        if(searchQuery == null){
          this.loading = true
        }
        const res = await axiosInstance.get("/users",{
            params:{
              page,
              perPage,
              search: searchQuery,
              shop_id
            }
        });
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

    async fetchInitialData(user_id, shop_id) {
      try {
        this.loading = true;
        const res = await axiosInstance.get(`/user/fetch/initial/data?id=${user_id}&shop_id=${shop_id}`);
        this.roles = res.data.roles
        this.user = res.data.user
        return new Promise((resolve) => {
          resolve(res.data);
        });
      } catch (error) {
        if (error.response.data) {
          this.errors = error.response.data.errors
        }
      }finally{
        this.loading = false;
      }
    },

    async show(id) {
      try {
        this.loading = true;
        const res = await axiosInstance.get(`/users/${id}`);
        return new Promise((resolve) => {
          resolve(res.data);
        });
      } catch (error) {
        if (error.response.data) {
          this.errors = error.response.data.errors
        }
      }finally{
        this.loading = false;
      }
    },

    async store(form) {
      try {
        this.loading = true;
        const res = await axiosInstance.post('/users',form);
        return new Promise((resolve) => {
          resolve(res.data);
        });
      } catch (error) {
        if (error.response.data) {
          this.errors = error.response.data.errors
        }
      }finally{
        this.loading = false;
      }
    },

    async update(form) {
      try {
        this.loading = true;
        const res = await axiosInstance.post("/update/user",form);
        return new Promise((resolve) => {
          resolve(res.data);
        });
      } catch (error) {
        if (error.response.data) {
          this.errors = error.response.data.errors
        }
      }finally{
        this.loading = false;
      }
    },

    async destroy(id) {
      try {
        const res = await axiosInstance.delete(`/users/${id}`);
        if (res.status === 200) {
            let index = this.items.data.findIndex(
              (item)=> item?.id == id
            );
            this.items.data.splice(index,1)
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

    async multipleDelete(selectedIds) {
      try {
        const res = await axiosInstance.delete('/user/multiple-delete',{
            params:{
              ids: selectedIds
            }
        });
        if (res.status === 200) {
            this.items.data = this.items.data.filter(
              (item)=> !selectedIds.includes(item?.id)
            );
            const pinia_bulk_delete = useBulkDelete();
            pinia_bulk_delete.$reset();
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


    async storeSupplier(form) {
      try {
        this.loading = true;
        const res = await axiosInstance.post('/store/supplier',form);
        return new Promise((resolve) => {
          resolve(res.data);
        });
      } catch (error) {
        if (error.response.data) {
          this.errors = error.response.data.errors
        }
      }finally{
        this.loading = false;
      }
    },

    async storeCustomer(form) {
      try {
        this.loading = true;
        const res = await axiosInstance.post('/store/customer',form);
        return new Promise((resolve) => {
          resolve(res.data);
        });
      } catch (error) {
        if (error.response.data) {
          this.errors = error.response.data.errors
        }
      }finally{
        this.loading = false;
      }
    },
    async storeMaster(form) {
      try {
        this.loading = true;
        const res = await axiosInstance.post('/store/master',form);
        return new Promise((resolve) => {
          resolve(res.data);
        });
      } catch (error) {
        if (error.response.data) {
          this.errors = error.response.data.errors
        }
      }finally{
        this.loading = false;
      }
    },

  },
});
