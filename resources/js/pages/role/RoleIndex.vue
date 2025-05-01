<script setup>
import {onMounted} from "vue"
import {storeToRefs} from "pinia"
import { useRole } from "@/stores";
const pinia_role = useRole();
const {items,loading} = storeToRefs(pinia_role);

onMounted(async()=>{
  await pinia_role.index();
})

const deleteRole = (id) => {
  Swal.fire({
      title: "Are you sure?",
      text: "You won't be able to revert this!",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#3085d6",
      cancelButtonColor: "#d33",
      confirmButtonText: "Yes, delete it!"
    }).then(async(result) => {
      if (result.isConfirmed) {
        const resData = await pinia_role.destroy(id);
        if(resData?.status){
            Swal.fire({
            title: "Deleted!",
            text: "Your file has been deleted.",
            icon: "success"
          });
        }else{
          Swal.fire({
            icon: "error",
            title: "Oops...",
            text: "Something went wrong!",
          });
        }
      }
  });
}
</script>

<template>
   <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12">
            <h4 class="text-center m-3">All Roles & Permissions</h4>
          </div>
          <div class="col-12">
            <div v-if="loading" class="text-center m-5">
                <span
                    class="spinner-border spinner-border-sm mr-1 text-dark"
                    style="padding: 12px; font-size: 20px;"
                ></span>
                <div>Loading....</div>
            </div>
            <div class="card" v-else>              
                <div class="mx-2 pb-0" v-if="$filters.hasPermission('role.create')"><router-link :to="{name: 'role.add'}" class="btn btn-success float-right mt-2 mr-3">Create New Role</router-link></div>              

                <div class="card-body">
                    <table id="role_list" class="table table-bordered table-striped">
                    <thead>
                    <tr>
                        <th width="5%">SL No.</th>
                        <th width="15%">Name</th>
                        <th width="55%">Permissions</th>
                        <th width="15%" v-if="$filters.hasPermission('role.update') || $filters.hasPermission('role.delete')">Action</th>
                    </tr>
                    </thead>

                    <tbody>
                      <tr v-for="(role, index) in items" :key="index">
                          <td>{{ index+1 }}</td> 
                          <td>{{ role?.name }}</td>
                          <td>
                              <span class="badge badge-info mr-2" v-for="(permission, i) in role?.permissions" :key="i">
                                    {{permission?.name}}
                              </span>
                          </td>
                          <td class="d-flex" v-if="$filters.hasPermission('role.update') || $filters.hasPermission('role.delete')">
                              <router-link :to="{name: 'role.edit', params: {id: role?.id}}" class="btn btn-primary px-3 mr-2" v-if="$filters.hasPermission('role.update')">Edit</router-link>
                              <button type="button" class="btn btn-danger" @click.prevent="deleteRole(role?.id)" v-if="$filters.hasPermission('role.delete')">Delete</button >

                          </td>
                      </tr>
                        <!-- <tr>
                            <td>{{$loop->index+1}}</td>
                            <td>{{$role->name}}</td>
                            <td>
                            @foreach ($role->permissions as $permission)
                                <span class="badge badge-info mr-2">
                                    {{$permission->name}}
                                </span>
                            @endforeach
                            </td>
                            <td class="d-flex">
                            @if (Auth::user()->can('role.update'))
                            <a href="{{route('roles.edit',$role->id)}}" class="btn btn-primary px-3 mr-2">Edit</a>
                            @endif

                            @if (Auth::user()->can('role.delete'))
                            <a href="{{route('roles.destroy',$role->id)}}" class="btn btn-danger" onclick="deleteRole(event)">Delete</a>
                            <form id="delete-form-{{$role->id}}" action="{{route('roles.destroy',$role->id)}}" style="display: none;" method="post">
                                @method('DELETE')
                                @csrf
                            </form>
                            @endif
                            </td>
                        </tr> -->
                    </tbody>
                    <tfoot>
                        <tr>
                        <th width="5%">SL No.</th>
                        <th width="15%">Name</th>
                        <th width="55%">Permissions</th>         
                        <th width="15%">Action</th>                        
                        </tr>
                    </tfoot>
                    </table>
                </div>
                    

            
              <!-- /.card-body -->
            </div>
            <!-- /.card -->
          </div>
          <!-- /.col -->
        </div>
        <!-- /.row -->
      </div>
      <!-- /.container-fluid -->
    </section>
</template>

<style scoped>

</style>
