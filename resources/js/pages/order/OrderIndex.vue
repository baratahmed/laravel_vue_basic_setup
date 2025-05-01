<script setup>
import { onMounted, ref, watch, computed} from "vue";
import Datepicker from 'vuejs3-datepicker';
import {storeToRefs} from "pinia"
import { debounce } from "lodash";
import { useRouter } from "vue-router";
import { Bootstrap5Pagination } from 'laravel-vue-pagination';
import { useOrder, useBulkDelete, useNotification, useAuth } from "@/stores";

const notify = useNotification();
const auth = useAuth();
const pinia_order = useOrder();
const router = useRouter();
const pinia_bulk_delete = useBulkDelete();
const {loading} = storeToRefs(pinia_order);

import {Table, TableHead, TableRow, TableData} from "@/common/components/Table"
import {SuccessButton, PrimaryButton, DeleteButton} from "@/common/components/Form"

onMounted(async()=>{
    pinia_bulk_delete.$reset();
    await pinia_order.index(1,10,null,to.value, auth.user?.shop?.id)
})

const perPage = ref(10);
var d = new Date();
const from = ref( new Date(`${d.getFullYear()}-${d.getMonth()}-${d.getDate()}`));
const to = ref(new Date());

const debounceSearch = computed(()=> debounce(getResults,500));

const getResults = async (page = 1) => {
    await pinia_order.index(page, perPage.value, from.value, to.value, auth.user?.shop?.id)
}

// Multiple Delete
const selectAllData = async () => {
    await pinia_bulk_delete.selectAllData(pinia_order.getItems?.data);
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
        const resData = await pinia_order.multipleDelete(pinia_bulk_delete.getSelectedData);
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
        const resData = await pinia_order.destroy(id);
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

const goToOrderDetails = (id) => {
    router.push({name: 'order.view', params: {id: id}});
}

const goToOrderPrint = (id) => {
   window.location = `/print/order/${id}`;
}


</script>

<template>
   <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12">
            <h4 class="text-center m-3">All Orders</h4>
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
                            <datepicker 
                              v-model="from"
                              @input="debounceSearch"
                              placeholder="From"
                              :disabled-dates="{
                                  from: new Date(),
                              }"
                              iconColor="red"
                              iconWidth="18"
                              iconHeight="18">
                            </datepicker>
                            <span class="mr-2"></span>
                            <datepicker 
                              v-model="to"
                              @input="debounceSearch"
                              placeholder="To"
                              :disabled-dates="{
                                  from: new Date(),
                              }"
                              iconColor="red"
                              iconWidth="18"
                              iconHeight="18">
                            </datepicker>
                        </div>
                    </div>        

                </div>

                <div class="d-flex justify-content-first mt-3">
                                      
                    <div v-if="pinia_bulk_delete.selectedData.length" class="pb-3 mr-2">
                        <button class="btn btn-sm btn-danger py-2 mr-2" @click="multipleDeleteSubmit">Delete ({{ pinia_bulk_delete.selectedData.length }} {{pinia_bulk_delete.selectedData.length == 1 ? 'item' : 'items'}})</button>
                    </div>

                    <div class="pb-3 mr-3" v-if="$filters.hasPermission('order.create')">
                        <router-link :to="{name: 'order.add'}" class="btn btn-sm btn-primary py-2">Add Order (+)</router-link>
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

                <Table v-if="pinia_order.getItems?.data?.length > 0">
                    <template #tableHead>
                        <TableHead v-if="auth.user?.role == 'Super Admin'">
                            <input type="checkbox" class="check_box" v-model="pinia_bulk_delete.selectAll" @change="selectAllData">
                        </TableHead>
                        <TableHead>Order ID</TableHead>
                        <TableHead>Customer Name</TableHead>
                        <TableHead>Grand Total</TableHead>
                        <TableHead>Paid</TableHead>
                        <TableHead>Due</TableHead>
                        <TableHead>Payment Status</TableHead>
                        <TableHead v-if="$filters.hasPermission('order.update') || $filters.hasPermission('order.delete')">Action</TableHead>
                    </template>
                    <TableRow v-for="(order,index) in pinia_order.getItems?.data" :key="index">
                        <TableData v-if="auth.user?.role == 'Super Admin'">
                            <input type="checkbox" class="check_box" :checked="pinia_bulk_delete.selectAll" @change="toggleSelectSelection(order)">
                        </TableData>
                        <TableData>#{{order.id}}</TableData>
                        <TableData>{{order.customer?.name}}</TableData>
                        <TableData>{{ order.grand_total }}</TableData>
                        <TableData>{{ order.paid }}</TableData>
                        <TableData>{{ order.due }}</TableData>

                        <TableData v-if="order.payment_status=='PAID'" class="text-success"><b>PAID</b></TableData>
                        <TableData v-else class="text-danger"><b>DUE</b></TableData>

      
                        <TableData v-if="$filters.hasPermission('order.update') || $filters.hasPermission('order.delete')">
                            <div class="d-flex" style="gap:4px">
                                <SuccessButton type="button" @click="goToOrderPrint(order?.id)" v-if="$filters.hasPermission('order.read')" :disabled="false">
                                    <i class="fa fa-print"></i>
                                </SuccessButton>
                                <PrimaryButton type="button" @click="goToOrderDetails(order?.id)" v-if="$filters.hasPermission('order.update')" :disabled="false">
                                    <i class="fa fa-eye"></i>
                                </PrimaryButton>
                                <DeleteButton type="button" @click="singleDelete(order?.id)" v-if="$filters.hasPermission('order.delete')" :disabled="true">
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
                    :data="pinia_order.getItems"
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