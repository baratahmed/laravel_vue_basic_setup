import { defineStore } from "pinia";
import axiosInstance from "@/services/AxiosService";

export const useSimple = defineStore("simple", {
  state: () => ({
    simple_categories: [],
    simple_sub_categories: [],
    simple_brands: [],
    simple_units: [],
    simple_sizes: [],
    simple_colors: [],
    simple_malls: [],
    simple_shops: [],
    simple_shop_types: [],
    simple_suppliers: [],
    simple_customers: [],
    simple_masters: [],
    simple_items: [],
    simple_fractions: [],
    simple_products: [],
    simple_expense_types: [],
    errors: {},
    loading: false,
  }),
  getters: {
    getItems: (state)=>{
      return state.items;
    },
  },

  actions: {

    async fetchSimpleCategories(shop_id) {
      try {
        const res = await axiosInstance.get("/simple/categories",{
          params: {
            shop_id
          }
        });
        if (res.status === 200) {
          this.simple_categories = res.data;
          return res.data;
        }
      } catch (error) {
        if (error.response.data) {
          throw error.response.data.errors;
        }
      }
    },

    async fetchSimpleSubCategories(cat_id) {
        try {
          const res = await axiosInstance.get("/simple/sub_categories/"+cat_id);
          if (res.status === 200) {
            this.simple_sub_categories = res.data;
            return res.data;
          }
        } catch (error) {
          if (error.response.data) {
            throw error.response.data.errors;
          }
        }
      },


    async fetchSimpleBrands() {
        try {
          const res = await axiosInstance.get("/simple/brands");
          if (res.status === 200) {
            this.simple_brands = res.data;
            return res.data;
          }
        } catch (error) {
          if (error.response.data) {
            throw error.response.data.errors;
          }
        }
      },

      async fetchSimpleUnits() {
        try {
          const res = await axiosInstance.get("/simple/units");
          if (res.status === 200) {
            this.simple_units = res.data;
            return res.data;
          }
        } catch (error) {
          if (error.response.data) {
            throw error.response.data.errors;
          }
        }
      },

      async fetchSimpleSizes() {
        try {
          const res = await axiosInstance.get("/simple/sizes");
          if (res.status === 200) {
            this.simple_sizes = res.data;
            return res.data;
          }
        } catch (error) {
          if (error.response.data) {
            throw error.response.data.errors;
          }
        }
      },

      async fetchSimpleColors() {
        try {
          const res = await axiosInstance.get("/simple/colors");
          if (res.status === 200) {
            this.simple_colors = res.data;
            return res.data;
          }
        } catch (error) {
          if (error.response.data) {
            throw error.response.data.errors;
          }
        }
      },

      async fetchSimpleMalls() {
        try {
          const res = await axiosInstance.get("/simple/malls");
          if (res.status === 200) {
            this.simple_malls = res.data;
            return res.data;
          }
        } catch (error) {
          if (error.response.data) {
            throw error.response.data.errors;
          }
        }
      },

      async fetchSimpleShopTypes() {
        try {
          const res = await axiosInstance.get("/simple/shop_types");
          if (res.status === 200) {
            this.simple_shop_types = res.data;
            return res.data;
          }
        } catch (error) {
          if (error.response.data) {
            throw error.response.data.errors;
          }
        }
      },

      async fetchSimpleShops() {
        try {
          const res = await axiosInstance.get("/simple/shops");
          if (res.status === 200) {
            this.simple_shops = res.data;
            return res.data;
          }
        } catch (error) {
          if (error.response.data) {
            throw error.response.data.errors;
          }
        }
      },

      async fetchSimpleSuppliers(shop_id) {
  
        try {
          // const  res = await axiosInstance.get(`/simple/suppliers?shop_id=${shop_id}`);
          const  res = await axiosInstance.get(`/simple/suppliers`);
          if (res.status === 200) {
            this.simple_suppliers = res.data;
            return res.data;
          }
        } catch (error) {
          if (error.response.data) {
            throw error.response.data.errors;
          }
        }
      },

      async fetchSimpleCustomers(shop_id) {
  
        try {
          const  res = await axiosInstance.get(`/simple/customers?shop_id=${shop_id}`);
          if (res.status === 200) {
            this.simple_customers = res.data;
            return res.data;
          }
        } catch (error) {
          if (error.response.data) {
            throw error.response.data.errors;
          }
        }
      },

      async fetchSimpleMasters(shop_id) {
  
        try {
          const  res = await axiosInstance.get(`/simple/masters?shop_id=${shop_id}`);
          if (res.status === 200) {
            this.simple_masters = res.data;
            return res.data;
          }
        } catch (error) {
          if (error.response.data) {
            throw error.response.data.errors;
          }
        }
      },

      async fetchSimpleItems() {
        try {
          const  res = await axiosInstance.get('/simple/items');
          if (res.status === 200) {
            this.simple_items = res.data;
            return res.data;
          }
        } catch (error) {
          if (error.response.data) {
            throw error.response.data.errors;
          }
        }
      },

      async fetchSimpleFractions() {
        try {
          const  res = await axiosInstance.get('/simple/fractions');
          if (res.status === 200) {
            this.simple_fractions = res.data;
            return res.data;
          }
        } catch (error) {
          if (error.response.data) {
            throw error.response.data.errors;
          }
        }
      },

      async fetchSimpleProducts(shop_id) {
  
        try {
          const  res = await axiosInstance.get(`/simple/products?shop_id=${shop_id}`);
          if (res.status === 200) {
            this.simple_products = res.data;
            return res.data;
          }
        } catch (error) {
          if (error.response.data) {
            throw error.response.data.errors;
          }
        }
      },

      async fetchSimpleExpenseTypes() {
  
        try {
          const  res = await axiosInstance.get(`/simple/expense/types`);
          if (res.status === 200) {
            this.simple_expense_types = res.data;
            return res.data;
          }
        } catch (error) {
          if (error.response.data) {
            throw error.response.data.errors;
          }
        }
      },

  },
});
