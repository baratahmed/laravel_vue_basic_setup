import { defineStore } from "pinia";
import axiosInstance from "@/services/AxiosService";
import { useBulkDelete } from "@/stores";

export const useOrder = defineStore("order", {
  state: () => ({
    order: {},
    items: [],
    errors: {},
    loading: false,
    updateLoading: false,
  }),
  getters: {
    getItems: (state)=>{
      return state.items;
    },
  },

  actions: {
    async index(page, perPage, from, to, shop_id) {
      this.loading = true
      try {
        const res = await axiosInstance.get("/orders",{
            params:{
              page,
              perPage,
              from,
              to,
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

    async show(id) {
      try {
        this.loading = true;
        const res = await axiosInstance.get(`/orders/${id}`);
        this.order = res.data
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
        const res = await axiosInstance.post('/orders',form);
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

    async update(id,form) {
      try {
        this.updateLoading = true;
        const res = await axiosInstance.put(`/orders/${id}`,form);
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
        const res = await axiosInstance.delete(`/orders/${id}`);
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
        const res = await axiosInstance.delete('/order/multiple-delete',{
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
