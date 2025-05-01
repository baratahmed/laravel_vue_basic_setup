<script setup>
import {reactive, onMounted, watch} from "vue"
import {useRouter, useRoute} from "vue-router"
import {storeToRefs} from "pinia"
import { useUser,useNotification, useAddress, useAuth } from "@/stores";
const auth = useAuth();
const router = useRouter();
const route = useRoute();
const pinia_user = useUser();
const pinia_address = useAddress();
const notify = useNotification();
const {errors,loading, roles, user} = storeToRefs(pinia_user);
const {divisions,zilas,upazilas,unions} = storeToRefs(pinia_address);

const formData = new FormData();
const form = reactive({
    id: route.params?.id,
    shop_id: auth.user?.shop?.id,
    address_id: null,
    name: '',
    role_name: null,
    email: null,
    phone: null,
    password: null,
    image: null,
    status: 1,

    division: null,
    zila: null,
    upazila: null,
    union: null,
    address: '',
});

onMounted(async()=>{
  pinia_user.loading = true
  await pinia_address.fetchDivisions()
  const resData = await pinia_user.fetchInitialData(route.params?.id, auth.user?.shop?.id)
  form.id = resData.user.id
  form.shop_id = resData.user.shop_id
  form.role_name = resData.user.role
  form.name = resData.user?.name
  form.email = resData.user?.email
  form.phone = resData.user?.phone
  form.password = resData.user?.real_password
  form.status = resData.user?.is_verified
  form.image = import.meta.env.VITE_APP_URL+"/"+resData.user.image

  form.address_id = resData.user?.address?.id
  form.division = resData.user?.address?.divisions[0]
  form.zila = resData.user?.address?.zilas[0]
  form.upazila = resData.user?.address?.upazilas[0]
  form.union = resData.user?.address?.unions[0]
  form.address = resData.user?.address?.address
})


watch(
  ()=>[form.division],
  async() => {
    await pinia_address.fetchZilas(form.division?.id)
  }
)
watch(
  ()=>[form.zila],
  async() => {
    await pinia_address.fetchUpazilas(form.zila?.id)
  }
)
watch(
  ()=>[form.upazila],
  async() => {
    await pinia_address.fetchUnions(form.upazila?.id)
  }
)

const loadUserImage = (e) => {
    const reader = new FileReader();
    reader.onload = (event) => {
      form.image = event.target.result;
    };
    reader.readAsDataURL(e.target.files[0]);
    formData.append("image",e.target.files[0]);
}

const changeStatus = () => {
    form.status = !form.status;
}

const updateUser = async() => {
  formData.append("id",form.id);
  formData.append("address_id",form.address_id);
  formData.append("name",form.name);
  if(form.role_name != null){
    formData.append("role_name",form.role_name);
  }
  if(form.shop_id != null){
    formData.append("shop_id",form.shop_id);
  }
  formData.append("email",form.email);
  if(form.phone != null){
    formData.append("phone",form.phone);
  }
  formData.append("password",form.password);
  formData.append("status",form.status ? 1 : 0);

  if(form.division != null){
    formData.append("division_id",form.division?.id);
  }
  if(form.zila != null){
    formData.append("zila_id",form.zila?.id);
  }
  if(form.upazila != null){
    formData.append("upazila_id",form.upazila?.id);
  }
  if(form.union != null){
    formData.append("union_id",form.union?.id);
  }
  formData.append("address",form.address);

  const resData = await pinia_user.update(formData);

  if(resData?.status){
    notify.Success(resData.message);
    form.id = null
    form.address_id = null
    form.name = ''
    form.role_name = null
    form.email = null
    form.phone = null
    form.password = null
    form.status = 1

    form.division = null
    form.zila = null
    form.upazila = null
    form.union = null
    form.address = ''
  
    router.push({name: 'user.index'})
  }else{
    notify.Error("Something went wrong!");
  }
}
</script>

<template>
    <section class="content pt-3">
      <div class="container-fluid">
        <div v-if="loading" class="text-center m-5">
            <span
                class="spinner-border spinner-border-sm mr-1 text-dark"
                style="padding: 12px; font-size: 20px;"
            ></span>
            <div>Loading....</div>
        </div>
        <div class="row" v-else>
          <!-- left column -->
          <div class="col-md-6">
            <!-- general form elements -->
            <div class="card card-primary">
              <div class="card-header">
                <h3 class="card-title">Update User ({{ user.name }})</h3>
              </div>
              <!-- /.card-header -->
              <!-- form start -->
              <form @submit.prevent="updateUser">
                <div class="card-body">

                  <div class="form-group">
                    <label for="user_name">User Name</label>
                    <input type="text" class="form-control" v-model="form.name" id="user_name" placeholder="Enter User Name">
                    <span class="text-danger" v-if="errors?.name">{{ errors.name[0] }}</span>
                  </div>

                  <div class="form-group">
                    <label for="user_phone">Mobile No</label>
                    <input type="text" class="form-control" v-model="form.phone" id="user_phone" placeholder="Enter Mobile Number">
                    <span class="text-danger" v-if="errors?.phone">{{ errors.phone[0] }}</span>
                  </div>

                  <div class="form-group">
                    <label for="user_email">Email Address</label>
                    <input type="email" class="form-control" v-model="form.email" id="user_email" placeholder="Enter Email Address">
                    <span class="text-danger" v-if="errors?.email">{{ errors.email[0] }}</span>
                  </div>

                  <div class="form-group">
                    <label for="password">Password</label>
                    <input type="text" class="form-control" v-model="form.password" id="password" placeholder="Enter Password">
                    <span class="text-danger" v-if="errors?.password">{{ errors.password[0] }}</span>
                  </div>

                  <div class="form-group">
                    <label for="exampleInputFile">User Image </label>
                    <div class="input-group">
                      <div class="custom-file">
                        <input type="file" @change="loadUserImage" class="custom-file-input" id="exampleInputFile">
                        <label class="custom-file-label" for="exampleInputFile">Choose file</label>
                      </div>
                      <span class="text-danger" v-if="errors?.image">{{ errors.image[0] }}</span>
                    </div>
                  </div>

                  <div class="form-group">
                    <div class="preview">
                        <img :src="form.image" v-if="form.image" alt="" width="100px">
                      </div>
                  </div>

                  <div class="form-group">
                    <label for="exampleSelectRounded0">Select User Role</label>
                    <select class="custom-select rounded-0" id="exampleSelectRounded0" v-model="form.role_name">
                      <option v-for="(role,index) in roles" :key="index" :value="role.name">{{ role.name }}</option>
                    </select>
                    <span class="text-danger" v-if="errors?.role_name">{{ errors.role_name[0] }}</span>
                  </div>

                  <div class="form-group py-3">
                    <div class="custom-control custom-switch">
                      <input type="checkbox" class="custom-control-input" @change="changeStatus" :checked="form.status" id="status">
                      <label class="custom-control-label" for="status">Status ({{ form.status ? 'Active' : 'Inactive' }})</label>
                    </div>
                  </div>

                  <div class="form-group">
                    <label for="category">Select Division</label>
                    <v-select :options="divisions" label="name" v-model="form.division"></v-select>
                    <span class="text-danger" v-if="errors?.division_id">{{ errors.division_id[0] }}</span>
                  </div>

                  <div class="form-group">
                    <label for="category">Select Zila</label>
                    <v-select :options="zilas" label="name" v-model="form.zila"></v-select>
                    <span class="text-danger" v-if="errors?.zila_id">{{ errors.zila_id[0] }}</span>
                  </div>

                  <div class="form-group">
                    <label for="category">Select Upazila</label>
                    <v-select :options="upazilas" label="name" v-model="form.upazila"></v-select>
                    <span class="text-danger" v-if="errors?.upazila_id">{{ errors.upazila_id[0] }}</span>
                  </div>

                  <div class="form-group">
                    <label for="category">Select Union</label>
                    <v-select :options="unions" label="name" v-model="form.union"></v-select>
                    <span class="text-danger" v-if="errors?.union_id">{{ errors.union_id[0] }}</span>
                  </div>

                  <div class="form-group">
                    <label for="address">Address</label>
                    <input type="text" class="form-control" v-model="form.address" id="address" placeholder="Enter User Address">
                    <span class="text-danger" v-if="errors?.address">{{ errors.address[0] }}</span>
                  </div>
                </div>


                <div class="form-group mx-3">
                  <!-- <button type="submit" class="btn btn-primary" :disabled="true">Update</button> -->
                  <button type="submit" class="btn btn-primary" :disabled="loading">Update</button>
                </div>
              </form>
            </div>
            <!-- /.card -->
          </div>
        </div>
        <!-- /.row -->
      </div><!-- /.container-fluid -->
    </section>
</template>