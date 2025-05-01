<script setup>
import {reactive,onMounted, ref} from "vue"
import {useRouter} from "vue-router"
import {storeToRefs} from "pinia"
import Datepicker from 'vuejs3-datepicker';
import LocalModal from "@/common/components/LocalModal.vue";
import _ from "lodash"
import { useOrder,useNotification, useAuth, useSimple, useUser} from "@/stores";
const auth = useAuth();
const pinia_simple = useSimple();
const pinia_user = useUser();
const router = useRouter();
const pinia_order = useOrder();
const notify = useNotification();
const {simple_customers,simple_masters, simple_items, simple_fractions} = storeToRefs(pinia_simple);
const {errors} = storeToRefs(pinia_order);
let counter = reactive(0);
let cn_counter = reactive(0);
let mn_counter = reactive(0);

// Modal Things
const modalForm = reactive({
    shop_id: auth.user?.shop?.id,
    name: null,
    phone: null,
    email: null,
    address: null,
});
const createCustomerStatus = ref(false)
const createMasterStatus = ref(false)

const closeModal = (data) => {
  modalForm.name = null
  modalForm.phone = null
  modalForm.email = null
  modalForm.address = null

  if(data == 'customer'){
    createCustomerStatus.value = false
  }
  if(data == 'master'){
    createMasterStatus.value = false
  }
}
const openModal = (data) => {
  if(data == 'customer'){
    createCustomerStatus.value = true
  }
  if(data == 'master'){
    createMasterStatus.value = true
  }
}

const storeCustomer = async () => {
  const resData = await pinia_user.storeCustomer(modalForm)
  if(resData?.status){
    notify.Success(resData.message);
    modalForm.name = null
    modalForm.email = null
    modalForm.phone = null
    modalForm.address = null
    closeModal('customer');
  }else{
    notify.Error("Something went wrong!");
  }
}

const storeMaster = async () => {
  const resData = await pinia_user.storeMaster(modalForm)
  if(resData?.status){
    notify.Success(resData.message);
    modalForm.name = null
    modalForm.email = null
    modalForm.phone = null
    modalForm.address = null
    closeModal('master');
  }else{
    notify.Error("Something went wrong!");
  }
}
// Modal Things End

const form = reactive({
    user_id: auth.user.id,
    order_items: [
      {
        id:0,
        item:null,

        unit_price:0,
        qty:0,
        net_price:0,
      }
    ],
    date: new Date(),
    trial_date: new Date(),
    delivery_date: new Date(),
    sub_total: 0,
    discount: 0,
    grand_total: 0,
    paid: 0,
    due: 0,
    client_notes: [
      {
        id:0,
        num:'',
        frac:'',
        note:'',
      }
    ],
    master_notes: [
      {
        id:0,
        note:'',
      }
    ],
});

onMounted(()=>{
  pinia_simple.fetchSimpleCustomers(auth.user?.shop?.id)
  pinia_simple.fetchSimpleMasters(auth.user?.shop?.id)
  pinia_simple.fetchSimpleItems()
  pinia_simple.fetchSimpleFractions()
})

const addItem = () => {
      form.order_items.push(
          {
              id:`${++counter}`,
              product:null,
              unit_price:0,
              qty:0,
              net_price:0,
          }
      )
}
const removeItem = (index) => {
    if( form.order_items.length > 1){
      form.order_items.splice(_.findIndex(form.order_items, o=>{
            return o.id == index;
        }), 1);

      form.sub_total = 0;
      form.order_items.forEach(element => {
          form.sub_total += element.net_price
      });
      form.discount = 0
      form.grand_total = form.sub_total
      form.paid = 0
      form.due = form.sub_total
    }
}

const storeOrder = async() => {
  Swal.fire({
      title: "Are you sure?",
      text: "You won't be able to revert this!",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#3085d6",
      cancelButtonColor: "#d33",
      confirmButtonText: "Yes, Proceed!"
    }).then(async(result) => {
      if (result.isConfirmed) {
        const resData = await pinia_order.store(form);
        if(resData?.status){
            Swal.fire({
              title: "Success!",
              text: resData.message,
              icon: "success"
            });
            form.customer = null
            form.master = null
            form.order_items = [
              {
                id:0,
                product:null,
                unit_price:0,
                qty:0,
                net_price:0,
              }
            ],
            form.sub_total= 0
            form.discount= 0
            form.grand_total= 0
            form.paid= 0
            form.due= 0
            router.push({name: 'order.index'})
        }else{
          Swal.fire({
            icon: "error",
            title: "Oops...",
            text: resData?.message,
          });
        }
      }
  });
  
}

const calculateUnitPrice = (itemId) => {
    let index = _.findIndex(form.order_items, o =>{ return o.id == itemId});
    let unit_price = form.order_items[index].unit_price
    
    let quantity = form.order_items[index].qty 
  
    form.order_items[index].net_price = parseInt(unit_price * quantity)
  

    form.sub_total = 0;
    form.order_items.forEach(element => {
        form.sub_total += parseInt(element.net_price)
    });
    form.grand_total = form.sub_total
    form.discount = 0
    form.paid = 0
    form.due = form.sub_total

}

const processPricing = () => {
  form.grand_total = form.sub_total - form.discount
  form.due = form.grand_total - form.paid
}

const addClientNote = () => {
      form.client_notes.push(
          {
              id:`${++cn_counter}`,
              num:'',
              frac:'',
              note:'',
          }
      )
}
const removeClientNote = (index) => {
    if( form.client_notes.length > 1){
      form.client_notes.splice(_.findIndex(form.client_notes, o=>{
            return o.id == index;
        }), 1);
    }
}

const addMasterNote = () => {
      form.master_notes.push(
          {
              id:`${++mn_counter}`,
              note:'',
          }
      )
}
const removeMasterNote = (index) => {
    if( form.master_notes.length > 1){
      form.master_notes.splice(_.findIndex(form.master_notes, o=>{
            return o.id == index;
        }), 1);
    }
}

</script>

<template>

    <section class="content pt-3">
      <div class="container-fluid">
        <div class="row">
          <div class="col-md-12">
            <h4 class="text-center">Add Order</h4 >
          </div>
        </div>

        <div class="row">
            <div class="col-md-6">
              <div class="card ">
                  <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th class="text-primary">Order Date: </th>
                            <td class="text-center">
                              <datepicker 
                                v-model="form.date"
                                placeholder="Order Date"
                                :disabled-dates="{
                                    from: new Date(),
                                }"
                                iconColor="red"
                                iconWidth="18"
                                iconHeight="18">
                              </datepicker>
                            </td>
                        </tr>
                        <tr>
                            <th class="text-info">Trial Date: </th>
                            <td class="text-center">
                              <datepicker 
                                v-model="form.trial_date"
                                placeholder="Trial Date"
                                :disabled-dates="{
                                    to: new Date(),
                                }"
                                iconColor="red"
                                iconWidth="18"
                                iconHeight="18">
                              </datepicker>
                            </td>
                        </tr>
                        <tr>
                            <th class="text-success">Delivery Date: </th>
                            <td class="text-center">
                              <datepicker 
                                v-model="form.delivery_date"
                                placeholder="Delivery Date"
                                :disabled-dates="{
                                    to: new Date(),
                                }"
                                iconColor="red"
                                iconWidth="18"
                                iconHeight="18">
                              </datepicker>
                            </td>
                        </tr>    
                    </table> 
                  </div>   
              </div>
            </div>

            <div class="col-md-6">
              <div class="card ">
                <div class="card-body">
                  <table class="table table-bordered">
                      <tr>
                          <th class="text-secondary">Sub Total: </th>
                          <td><input type="number" class="form-control" v-model="form.sub_total" disabled></td>
                      </tr>
                      <tr>
                          <th class="text-success">Discount: </th>
                          <td><input type="number" class="form-control" v-model="form.discount" @keyup="processPricing()"></td>
                      </tr>
                      <tr>
                          <th class="text-primary">Grand Total: </th>
                          <td><input type="number" class="form-control" v-model="form.grand_total" disabled></td>
                      </tr>
                      <tr>
                          <th class="text-success">Paid:</th>
                          <td><input type="number" class="form-control" v-model="form.paid" @keyup="processPricing()"></td>
                      </tr>
                      <tr>
                          <th class="text-danger">Due: </th>
                          <td><input type="number" class="form-control" v-model="form.due" disabled></td>
                      </tr>
                      <tr>
                          <td colspan="2"><button class="btn btn-block btn-success" @click.prevent="storeOrder" type="buton">Submit</button></td>
                      </tr>
                  </table> 
                </div>   
            </div>
          </div>
        </div>
      </div>
    </section>
</template>


<style scoped>
input {font-weight:bold !important;}

.add_order {
    display: block;
    overflow-x: auto;
    white-space: nowrap;
}
</style>