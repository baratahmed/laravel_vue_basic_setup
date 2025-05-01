<script setup>
import { onMounted, ref, watch, computed} from "vue";
import {storeToRefs} from "pinia"
import { debounce } from "lodash";
import { useRouter } from "vue-router";
import { Bootstrap5Pagination } from 'laravel-vue-pagination';
import { useUser, useBulkDelete, useNotification, useAuth } from "@/stores";

const auth = useAuth();
const notify = useNotification();
const pinia_user = useUser();
const router = useRouter();
const pinia_bulk_delete = useBulkDelete();
const {loading} = storeToRefs(pinia_user);

import {Table, TableHead, TableRow, TableData} from "@/common/components/Table"
import {PrimaryButton, DeleteButton} from "@/common/components/Form"

onMounted(async()=>{
    pinia_bulk_delete.$reset();
    await pinia_user.index(null, null, null, auth.user?.shop?.id)
})

const perPage = ref(10);
const searchQuery = ref("");

const debounceSearch = computed(()=> debounce(getResults,500));

const getResults = async (page = 1) => {
    await pinia_user.index(page, perPage.value, searchQuery.value, auth.user?.shop?.id)
}

// Multiple Delete
const selectAllData = async () => {
    await pinia_bulk_delete.selectAllData(pinia_user.getItems?.data);
}

const toggleSelectSelection = async (item) => {
    await pinia_bulk_delete.toggleSelection(item);
}

const multipleDeleteSubmit = async () => {
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
        const resData = await pinia_user.multipleDelete(pinia_bulk_delete.getSelectedData);
        let inputs = document.querySelectorAll('.check_box');
        for (let i = 0; i < inputs.length; i++) {
            if(inputs[i].checked){
                inputs[i].checked = false;
            }
        }
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

const singleDelete = (id) => {
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
        const resData = await pinia_user.destroy(id);
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

const goToUserEditPage = (user) => {
    router.push({name: 'user.edit', params: {id: user?.id}});
}

</script>

<template>
   <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12">
            <h4 class="text-center m-3">All Users</h4>
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

              <div class="card-body">
                <div class="d-flex">
                    <div class="mr-auto">
                        <div class="tb_search">
                            <input type="text" @input="debounceSearch" v-model="searchQuery" placeholder="Search..." class="form-control">
                        </div>
                    </div>        

                    <div class="num_rows">
                        <div class="form-group">
                            <select class="form-control" v-model="perPage" @change="getResults">
                                <option value="10" :selected="true">10</option>
                                <option value="20">20</option>
                                <option value="30">30</option>
                                <option value="40">40</option>
                                <option value="50">50</option>
                            </select>
                        </div>
                    </div> 
                </div>
                <div class="d-flex justify-content-first mb-2">
                                      
                  <div v-if="pinia_bulk_delete.selectedData.length" class="pb-3 mr-2">
                        <button class="btn btn-sm btn-danger py-2" @click="multipleDeleteSubmit">Delete ({{ pinia_bulk_delete.selectedData.length }} {{pinia_bulk_delete.selectedData.length == 1 ? 'item' : 'items'}})</button>
                    </div>

                    <div class="pb-3" v-if="$filters.hasPermission('user.create')">
                        <router-link :to="{name: 'user.add'}" class="btn btn-sm btn-primary py-2">Add User (+)</router-link>
                    </div>
                </div>


                <Table v-if="pinia_user.getItems?.data?.length > 0">
                    <template #tableHead>
                        <TableHead v-if="auth.user?.role == 'Super Admin'">
                            <input type="checkbox" class="check_box" v-model="pinia_bulk_delete.selectAll" @change="selectAllData">
                        </TableHead>
                        <TableHead>Name</TableHead>
                        <TableHead>Phone</TableHead>
                        <TableHead>Email</TableHead>
                        <TableHead>Role</TableHead>
                        <TableHead>Image</TableHead>
                        <TableHead>Status</TableHead>
                        <TableHead v-if="$filters.hasPermission('user.update') || $filters.hasPermission('user.delete')">Action</TableHead>
                    </template>
                    <TableRow v-for="(user,index) in pinia_user.getItems?.data" :key="index">
                        <TableData v-if="auth.user?.role == 'Super Admin'">
                            <input type="checkbox" class="check_box" :checked="pinia_bulk_delete.selectAll" @change="toggleSelectSelection(user)">
                        </TableData>
                        <TableData>{{user.name}}</TableData>
                        <TableData>{{user.phone}}</TableData>
                        <TableData>{{user.email}}</TableData>
                        <TableData><b class="text-primary">{{user.role}}</b></TableData>
                        <TableData>
                            <img :src="$filters.makeImagePath(user.image)" alt="user Image" width="70">
                        </TableData>                        
                        <TableData v-if="user.is_verified==1" class="text-success"><b>Verified</b></TableData>
                        <TableData v-else class="text-danger"><b>Not Verified</b></TableData>

                        <TableData v-if="$filters.hasPermission('user.update') || $filters.hasPermission('user.delete')">
                            <div class="d-flex" style="gap:4px">
                                <PrimaryButton type="button" @click.prevent="goToUserEditPage(user)" v-if="$filters.hasPermission('user.update')">
                                    <i class="fa fa-edit"></i>
                                </PrimaryButton>
                                <DeleteButton type="button" @click="singleDelete(user?.id)" v-if="$filters.hasPermission('user.delete')">
                                    <i class="fa fa-trash"></i>
                                </DeleteButton>
                            </div>
                        </TableData>
                    </TableRow>
                </Table>
                <div class="col-12" v-else> 
                  <h5 class="text-center text-danger" >No Data Found</h5>
                </div>
                <Bootstrap5Pagination
                    :data="pinia_user.getItems"
                    @pagination-change-page="getResults"
                />

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
