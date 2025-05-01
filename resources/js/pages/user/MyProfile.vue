<script setup>
import { onMounted, ref, reactive, watch, computed} from "vue";
import {storeToRefs} from "pinia"
import { debounce } from "lodash";
import { useRouter } from "vue-router";
import { useAuth, useNotification } from "@/stores";

const notify = useNotification();
const auth = useAuth();
const router = useRouter();
const {errors} = storeToRefs(auth) 


const profile = reactive({
  'user_id': auth.user.id,
  'name': auth.user.name,
  'phone': auth.user.phone,
  'email': auth.user.email,
});

const profilePictureUpdate = (event) => {
  const imageFile = event.target.files[0];
  if(imageFile){
    const formData = new FormData();
    formData.append("user_id",auth.user.id);
    formData.append("image",imageFile);
    auth.updateProfileImage(formData);
  }
}

const updateProfile = async () => {
  const resData = await auth.updateProfile(profile);
  if(resData?.status == true){
    notify.Success(resData.message);
  }else{
    notify.Error(resData.message);
  }
}

const password = reactive({
  'user_id': auth.user.id,
  'current_password': '',
  'password': '',
  'password_confirmation': '',
});

const changePassword = async () => {
  const resData = await auth.changePassword(password);
  if(resData?.status == true){
    notify.Success(resData.message);
  }else{
    notify.Error(resData.message);
  }
}

</script>

<template>
   <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12">
            <h4 class="text-center m-3">My Profile</h4>
          </div>
    
          <!-- /.col -->
        </div>
        <div class="row">
          <div class="col-md-4">

            <!-- Profile Image -->
            <div class="card card-primary card-outline">
              <div class="card-body box-profile">
                <div class="text-center">
                  <div class="upload">
                        <img class="profile-user-img img-fluid img-circle" :src="$filters.makeImagePath(auth.user.image)" alt="User profile picture">
                        <div class="round"> 
                          <input type="file" @change="profilePictureUpdate" class="profile-img">
                          <i class="fa fa-camera" style="color: #fff;"></i>
                        </div>
                  </div>
                </div>

                <h3 class="profile-username text-center">{{ auth.user?.name }}</h3>

                <p class="text-muted text-center"><b>Role:</b> <span class="text-primary">{{ auth.role }}</span></p>

                <ul class="list-group list-group-unbordered mb-3" v-show="auth.role!='Super Admin' && auth.role!='Admin'">
                  <li class="list-group-item">
                    <b>Shop Name:</b> <a class="float-right">{{ auth.user?.shop?.name }}</a>
                  </li>
                  <li class="list-group-item">
                    <b>Mall Name:</b> <a class="float-right">{{auth.user?.shop?.mall?.name}}</a>
                  </li>
                  <li class="list-group-item">
                    <b>Location:</b> <a class="float-right">{{auth.user?.shop?.mall?.location}}</a>
                  </li>
                </ul>
              </div>
              <!-- /.card-body -->
            </div>
            <!-- /.card -->

          </div>
          <!-- /.col -->
          <div class="col-md-8">
            <div class="card">
              <div class="card-header p-2">
                <ul class="nav nav-pills">
                  <li class="nav-item"><a class="nav-link active" href="#edit_profile" data-toggle="tab">Edit Profile</a></li>
                  <li class="nav-item"><a class="nav-link " href="#change_password" data-toggle="tab">Change Password</a></li>
                </ul>
              </div>
              <div class="card-body">
                  <div class="tab-content">
                    <div class="active tab-pane" id="edit_profile">
                      <form class="form-horizontal" @submit.prevent="updateProfile">
                        <div class="form-group row">
                          <label for="inputName" class="col-sm-2 col-form-label">Name</label>
                          <div class="col-sm-10">
                            <input type="text" class="form-control" v-model="profile.name" id="inputName" placeholder="Name">
                            <span class="text-danger" v-if="errors?.name">{{ errors.name[0] }}</span>
                          </div>
                        </div>
                        <div class="form-group row">
                          <label for="inputPhone" class="col-sm-2 col-form-label">Phone No</label>
                          <div class="col-sm-10">
                            <input type="text" v-model="profile.phone" class="form-control" id="inputPhone" placeholder="Mobile No">
                            <span class="text-danger" v-if="errors?.phone">{{ errors.phone[0] }}</span>
                          </div>
                        </div>
                        <div class="form-group row">
                          <label for="inputEmail" class="col-sm-2 col-form-label">Email</label>
                          <div class="col-sm-10">
                            <input type="email" v-model="profile.email" class="form-control" id="inputEmail" placeholder="Email">
                            <span class="text-danger" v-if="errors?.email">{{ errors.email[0] }}</span>
                          </div>
                        </div>
                      
                        <div class="form-group row">
                          <div class="offset-sm-2 col-sm-10">
                            <button type="submit" class="btn btn-success">Update</button>
                          </div>
                        </div>
                      </form>
                    </div>

                    <div class="tab-pane" id="change_password">
                      <form class="form-horizontal" @submit.prevent="changePassword">
                        <div class="form-group row">
                          <label for="current_password" class="col-sm-2 col-form-label">Current Password</label>
                          <div class="col-sm-10">
                            <input type="text" class="form-control" v-model="password.current_password" id="current_password" placeholder="Current Password">
                          </div>
                        </div>
                        <div class="form-group row">
                          <label for="password" class="col-sm-2 col-form-label">New Password</label>
                          <div class="col-sm-10">
                            <input type="password" v-model="password.password" class="form-control" id="password" placeholder="New Password">
                          </div>
                        </div>
                        <div class="form-group row">
                          <label for="password_confirmation" class="col-sm-2 col-form-label">Confirm Password</label>
                          <div class="col-sm-10">
                            <input type="password" v-model="password.password_confirmation" class="form-control" id="password_confirmation" placeholder="Confirm Password">
                          </div>
                        </div>
                      
                        <div class="form-group row">
                          <div class="offset-sm-2 col-sm-10">
                            <button type="submit" class="btn btn-success">Update</button>
                          </div>
                        </div>
                      </form>
                    </div>
                  </div>

              </div><!-- /.card-body -->
            </div>
            <!-- /.card -->
          </div>
          <!-- /.col -->
        </div>
      </div>
      <!-- /.container-fluid -->
    </section>
</template>

<style scoped>
.upload{
  width: 100px;
  position: relative;
  margin: auto;
}
.upload img{
  border-radius: 50%;
  border: 6px solid #eaeaea;
}
.upload .round{
  position: absolute;
  bottom: 0;
  right: 0;
  background: #2c4add;
  width: 32px;
  height: 32px;
  line-height: 33px;
  text-align: center;
  border-radius: 50%;
  overflow: hidden;
}

.profile-img{
  position: absolute;
  transform: scale(2);
  opacity: 0;
}
.profile-img::-webkit-file-upload-button{
 cursor: pointer;
}
</style>
