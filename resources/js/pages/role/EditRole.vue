<script setup>
import {onMounted,ref, reactive} from "vue"
import {useRouter,useRoute} from "vue-router"
import {storeToRefs} from "pinia"
import { useRole,useNotification } from "@/stores";
const pinia_role = useRole();
const router = useRouter();
const route = useRoute();
const notify = useNotification();
const {role,permissions_by_group_name,total_permissions,errors,loading,updateLoading} = storeToRefs(pinia_role);
const formData = new FormData();

const form = reactive({
    id: route.params?.id,
    name: '',
    permissions: []
});

onMounted(async()=>{
  pinia_role.total_permissions = 0;
  form.permissions = [];
  formData.append("id",route.params?.id);
  const res = await pinia_role.fetchPermissionsByGroupName(formData);

  form.name = role?.value.name
  Object.keys(role?.value?.permissions).forEach((key)=>{   
    form.permissions.push(role?.value?.permissions[key].name)                      
  })

  for (const property in res.permissions_by_groups) {
      for(const i in res.permissions_by_groups[property]){
        pinia_role.total_permissions++;
      }
  }

  let checkAllCount = 0;
  Object.keys(permissions_by_group_name.value).forEach((key)=>{
      let count=0;
      // key means GroupName
      Object.keys(permissions_by_group_name.value[key]).forEach((k)=>{
          const found = form.permissions.some(el => el == permissions_by_group_name.value[key][k].name);
          if(found == true){
            count++;
            checkAllCount++;
          }
          if(count == permissions_by_group_name.value[key].length){
             $("#"+key+'Management').prop('checked',true)
          }
      })            
    })
    
    if(checkAllCount == total_permissions.value){
      $("#checkPermissionAll").prop('checked',true)
    }


  $('#checkPermissionAll').click(function(){
      if($(this).is(':checked')){
        $('input[type=checkbox]').prop('checked',true)
        Object.keys(permissions_by_group_name.value).forEach((key)=>{          
          Object.keys(permissions_by_group_name.value[key]).forEach((k)=>{
            const found = form.permissions.some(el => el == permissions_by_group_name.value[key][k].name);
            if(!found) form.permissions.push(permissions_by_group_name.value[key][k].name)
          })            
        })
      }else{
        $('input[type=checkbox]').prop('checked',false)
        Object.keys(permissions_by_group_name.value).forEach((key)=>{
          Object.keys(permissions_by_group_name.value[key]).forEach((k)=>{
            var index = form.permissions.indexOf(permissions_by_group_name.value[key][k].name)
            if(index !== -1){
              form.permissions.splice(index,1)
            }
          })            
        })
      }
  });
})

function checkPermissionByGroup(className, checkThis, groupName){
    const groupIdName = $("#"+checkThis);  // col-4 e za ache  {{$i}}Management
    const classCheckBox = $("."+className+' input'); // col-8 e za ache role-{{$i}}-management-checkbox
    if(groupIdName.is(':checked')){
      classCheckBox.prop('checked',true)
        Object.keys(permissions_by_group_name.value).forEach((key)=>{
            if(key == groupName){
              Object.keys(permissions_by_group_name.value[key]).forEach((k)=>{
                const found = form.permissions.some(el => el == permissions_by_group_name.value[key][k].name);
                if(!found) form.permissions.push(permissions_by_group_name.value[key][k].name)
              })
            }
        })
    }else{
      classCheckBox.prop('checked',false)
      Object.keys(permissions_by_group_name.value).forEach((key)=>{
            if(key == groupName){
              Object.keys(permissions_by_group_name.value[key]).forEach((k)=>{
                var index = form.permissions.indexOf(permissions_by_group_name.value[key][k].name)
                if(index !== -1){
                  form.permissions.splice(index,1)
                }
              })
            }
        })
    }
    implementALlChecked();
}

function checkSinglePermission(groupClassName, groupID, countTotalPermission){
    // const classCheckBox = $("."+groupClassName+' input');
    const groupIDCheckBox = $("#"+groupID);
    if($('.'+groupClassName+' input:checked').length == countTotalPermission){
        groupIDCheckBox.prop('checked',true);
    }else{
        groupIDCheckBox.prop('checked',false);
    }
    implementALlChecked();
}

function implementALlChecked(){
  const countPermissions = total_permissions.value;
  const countPermissionGroups = Object.keys(permissions_by_group_name.value).length;
  if($('input[type="checkbox"]:checked').length >= (countPermissions+countPermissionGroups)){
    $("#checkPermissionAll").prop('checked',true);
  }else{
    $("#checkPermissionAll").prop('checked',false);
  }
}


const updateRole = async() => {
  const resData = await pinia_role.update(form);
  if(resData?.status){
    form.id = null
    form.name = ''
    form.permissions = []
    
    router.push({name: "role.index"})
    notify.Success(resData.message);
  }else{
    notify.Error('Something went wrong!');
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
        <div class="col-md-6">
          <div class="card card-primary">
            <div class="card-header">
              <h3 class="card-title">Edit Role ({{ role?.name }})</h3>
            </div>
            <form @submit.prevent="updateRole">
                <div class="card-body">
                  <div class="form-group">
                    <label for="name">Role Name</label>
                    <input type="text" class="form-control" v-model="form.name" id="name" placeholder="Enter Role Name">
                    <span class="text-danger" v-if="errors?.name">{{ errors.name[0] }}</span>
                  </div>

                  <div class="form-group">
                    <label for="permissions">Permissions</label>
                    <div class="icheck-success">
                      <input type="checkbox" class="form-check-input" id="checkPermissionAll" value="1">
                      <label class="form-check-label" for="checkPermissionAll"><b style="font-size: 20px;color:green;">Check All</b></label>
                    </div>
                    <hr>
                    <span class="text-danger" v-if="errors?.permissions">{{ errors.permissions[0] }}</span>

                        <div class="row" v-for="(group_permissions, group) in permissions_by_group_name" >
                          <div class="col-4">
                            <div class="icheck-primary">
                              <input type="checkbox" class="form-check-input" :id="group+'Management'" @click="checkPermissionByGroup(`role-${group}-management-checkbox`,`${group}Management`,group)" >
                              <label class="form-check-label" :for="group+'Management'"><b>{{group}}</b></label>
                            </div>
                          </div>
                          <div :class="'col-8 role-'+group+'-management-checkbox'">
                              <div class="icheck-primary" v-for="(permission, index) in group_permissions" >
                                  <input type="checkbox" v-model="form.permissions" class="form-check-input" @click="checkSinglePermission(`role-${group}-management-checkbox`,`${group}Management`,group_permissions.length)" :id="'checkPermission'+permission.id" :value="permission.name">
                                  <label class="form-check-label" :for="'checkPermission'+permission.id">{{permission.name}}</label>
                              </div>                                
                          </div>
                        </div>      
                  </div>          
                </div>
                
                <div class="card-footer">
                  <!-- <button type="submit" class="btn btn-primary" :disabled="true">Update</button> -->
                  <button type="submit" class="btn btn-primary" :disabled="updateLoading">Update</button>
                </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>


<style>

</style>
