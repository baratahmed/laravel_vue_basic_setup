import { defineStore } from "pinia";
import axiosInstance from "@/services/AxiosService";
import { useBulkDelete } from "@/stores";

export const useAddress = defineStore("address", {
  state: () => ({
    divisions: [],
    zilas: [],
    upazilas: [],
    unions: [],
    errors: {},
    loading: false,
  }),
  getters: {
    getItems: (state)=>{
      return state.items;
    },
  },

  actions: {
    async fetchDivisions() {
        try {
          const res = await axiosInstance.get("/divisions");
  
          if (res.status === 200) {
            this.divisions = res.data;
          }
        } catch (error) {
          if (error.response.data) {

          }
        }
      },
      async fetchZilas(division_id) {
        try {
          const res = await axiosInstance.get(`/zilas/${division_id}`);
  
          if (res.status === 200) {
            this.zilas = res.data;
          }
        } catch (error) {
          if (error.response.data) {
    
          }
        }
      },
      async fetchUpazilas(zila_id) {
        try {
          const res = await axiosInstance.get(`/upazilas/${zila_id}`);
  
          if (res.status === 200) {
            this.upazilas = res.data;
          }
        } catch (error) {
          if (error.response.data) {
    
          }
        }
      },
      async fetchUnions(upazila_id) {
        try {
          const res = await axiosInstance.get(`/unions/${upazila_id}`);
  
          if (res.status === 200) {
            this.unions = res.data;
          }
        } catch (error) {
          if (error.response.data) {
    
          }
        }
      },

  },
});
