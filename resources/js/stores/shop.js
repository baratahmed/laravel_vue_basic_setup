import { defineStore } from "pinia";
import axiosInstance from "@/services/AxiosService";
import { useBulkDelete } from "@/stores";

export const useShop = defineStore("shop", {
  state: () => ({
    items: [],
    errors: {},
    loading: false,
  }),
  getters: {
    getItems: (state)=>{
      return state.items;
    },
  },

  actions: {
    async index(page, perPage, searchQuery) {
      try {
        if(searchQuery == null){
          this.loading = true
        }
        const res = await axiosInstance.get("/shops",{
            params:{
              page,
              perPage,
              search: searchQuery
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

    async show(id) {
      try {
        this.loading = true;
        const res = await axiosInstance.get(`/shops/${id}`);
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
        const res = await axiosInstance.post('/shops',form);
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
        const res = await axiosInstance.post("/update/shop",form);
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
        const res = await axiosInstance.delete(`/shops/${id}`);
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
        const res = await axiosInstance.delete('/shop/multiple-delete',{
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

  },
});
