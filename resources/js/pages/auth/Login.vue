<script setup>
import { useRouter } from "vue-router";
import { useAuth,useNotification } from "@/stores";
import VTextInput from "@/common/components/VTextInput.vue";
import { Form } from "vee-validate";
import * as yup from "yup";

const schema = yup.object({
  phone: yup.string().required(),
  password: yup.string().required().min(6),
});

const auth = useAuth();
const notify = useNotification();
const router = useRouter();

const onSubmit = async (values, { setErrors, resetForm }) => {
    try {
      const res = await auth.login(values);
      if(res){
        router.push({name: "user.dashboard"});
      }
    } catch (error) {
      if(error?.status == false){
        notify.Warning(error?.message)
      }
      setErrors(error);
    }
};

</script>

<template>
  <div class="card card-outline card-primary">
    <div class="card-header text-center">
      <router-link :to="{name: 'user.login'}" class="h1 text-success"><b>LunchBox</b> <span class="text-danger">GCCC</span></router-link>
    </div>
    <div class="card-body pt-2">
      <h3 class="login-box-msg text-primary">Login</h3>

      <Form
        @submit="onSubmit"
        :validation-schema="schema"
        v-slot="{ errors, isSubmitting, meta }"
      >
        <div class="input-group mb-3">
            <div class="input-group-text">
                <span class="fas fa-phone"></span>
            </div>
            <VTextInput name="phone" type="phone" placeholder="Phone No" value="01812521337"/>

        </div>
        <div class="input-group mb-3">
            <div class="input-group-text">
              <span class="fas fa-lock"></span>
            </div>
            <VTextInput name="password" type="password" placeholder="Password" value="password"/>
        </div>
        <div class="row">
          <div class="col-7">
            <div class="icheck-primary">
              <input type="checkbox" id="remember" />
              <label for="remember"> &nbsp;Remember Me </label>
            </div>
          </div>
          <!-- /.col -->
          <div class="col-5">
            <button type="submit" :disabled="isSubmitting" class="btn btn-primary btn-block">
              <div>
                <span>Sign In</span>
                <span v-show="isSubmitting" class="spinner-border spinner-border-sm ml-1 mr-1"></span>
              </div>
            </button>
          </div>
          <!-- /.col -->
        </div>
      </Form>
    </div>
    <!-- /.card-body -->
  </div>
</template>
