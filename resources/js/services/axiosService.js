import axios from "axios";
import { useAuth,useToken } from "@/stores";

const axiosInstance = axios.create({
  baseURL: "/api",
});

axiosInstance.interceptors.request.use(
  function (config) {
    const token = useToken();
    const auth = useAuth();
    config.headers.common["Authorization"] = `Bearer ${token.getToken}`;
    config.headers.common["shop_id"] = auth.user?.shop?.id;
    return config;
  },
  function (error) {
    return Promise.reject(error);
  }
);

axiosInstance.interceptors.response.use(
  (response) => {
    return response;
  },
  (error) => {
    if(error.response && error.response.status == 401){
      const auth = useAuth();
      auth.removeAuthInfo();
      window.location.href = "/";
    }
    return Promise.reject(error);
  }
);

export default axiosInstance;
