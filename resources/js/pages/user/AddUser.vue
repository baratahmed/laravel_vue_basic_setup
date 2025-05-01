<script setup>
import {reactive, onMounted, watch} from "vue"
import {useRouter} from "vue-router"
import {storeToRefs} from "pinia"
import { useUser,useNotification, useAddress, useAuth } from "@/stores";
const router = useRouter();
const auth = useAuth();
const pinia_user = useUser();
const pinia_address = useAddress();
const notify = useNotification();
const {errors,loading, roles} = storeToRefs(pinia_user);
const {divisions,zilas,upazilas,unions} = storeToRefs(pinia_address);

onMounted(async()=>{
  await pinia_address.fetchDivisions()
  await pinia_user.fetchInitialData(auth.user?.id, auth.user?.shop?.id)
})

const formData = new FormData();
const form = reactive({
    name: '',
    shop_id: auth.user?.shop?.id,
    role_name: null,
    email: null,
    phone: null,
    image: null,
    status: 1,

    division: null,
    zila: null,
    upazila: null,
    union: null,
    address: '',
});

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

const storeUser = async() => {
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

  const resData = await pinia_user.store(formData);

  if(resData?.status){
    notify.Success(resData.message);
    form.name = ''
    form.role_name = null
    form.email = null
    form.phone = null
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
        <div class="row">
          <!-- left column -->
          <div class="col-md-6">
            <!-- general form elements -->
            <div class="card card-primary">
              <div class="card-header">
                <h3 class="card-title">Add New User</h3>
              </div>
              <!-- /.card-header -->
              <!-- form start -->
              <form @submit.prevent="storeUser">
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
                  <button type="submit" class="btn btn-primary" :disabled="loading">Submit</button>
                  <!-- <button type="submit" class="btn btn-primary" :disabled="true">Submit</button> -->
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