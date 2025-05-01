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
    await pinia_simple.fetchSimpleCustomers(auth.user.shop.id)
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
    await pinia_simple.fetchSimpleMasters(auth.user.shop.id)
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
    shop_id: auth.user.shop.id,
    customer: null,
    master: null,
    order_items: [
      {
        id:0,
        item:null,
        long: null,
        frac_long: null,
        cheast: null,
        frac_cheast: null,
        belly: null,
        frac_belly: null,
        hip: null,
        frac_hip: null,
        shoulder: null,
        frac_shoulder: null,
        hand_long_full: null,
        frac_hand_long_full: null,
        kop: null,
        frac_kop: null,
        throat: null,
        frac_throat: null,
        total_loose: null,
        frac_total_loose: null,
        enclosure: null,
        frac_enclosure: null,
        front_cheast: null,
        frac_front_cheast: null,
        front_belly: null,
        frac_front_belly: null,
        front_hip: null,
        frac_front_hip: null,
        wrap: null,
        frac_wrap: null,
        arm: null,
        frac_arm: null,
        kop_arm: null,
        frac_kop_arm: null,

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
    <!-- Customer Modal -->
    <LocalModal :visible="createCustomerStatus" @close="closeModal('customer')">
      <template #title>
          Create New Customer
      </template>
      <form @submit.prevent="storeCustomer">
        <div class="modal-body">
          <div class="form-group">
              <label for="customer_name">Name</label>
              <input type="text" class="form-control" id="customer_name" v-model="modalForm.name" placeholder="Customer Name">
              <span class="text-danger" v-if="pinia_user.errors?.name">{{ pinia_user.errors?.name[0] }}</span>
          </div>
          <div class="form-group">
              <label for="customer_phone">Phone</label>
              <input type="text" class="form-control" id="customer_phone" v-model="modalForm.phone" placeholder="Customer Phone No">
              <span class="text-danger" v-if="pinia_user.errors?.phone">{{ pinia_user.errors?.phone[0] }}</span>
          </div>
          <div class="form-group">
              <label for="customer_email">Email</label>
              <input type="email" class="form-control" id="customer_email" v-model="modalForm.email" placeholder="Customer Email Address">
          </div>
          <div class="form-group">
              <label for="customer_address">Address</label>
              <input type="text" class="form-control" id="customer_address" v-model="modalForm.address" placeholder="Customer Address">
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-success" type="submit">Save</button>
        </div>
      </form>
    </LocalModal>

    <!-- Master Modal -->
    <LocalModal :visible="createMasterStatus" @close="closeModal('master')">
      <template #title>
          Create New Master
      </template>
      <form @submit.prevent="storeMaster">
        <div class="modal-body">
          <div class="form-group">
              <label for="master_name">Name</label>
              <input type="text" class="form-control" id="master_name" v-model="modalForm.name" placeholder="Master Name">
              <span class="text-danger" v-if="pinia_user.errors?.name">{{ pinia_user.errors?.name[0] }}</span>
          </div>
          <div class="form-group">
              <label for="master_phone">Phone</label>
              <input type="text" class="form-control" id="master_phone" v-model="modalForm.phone" placeholder="Master Phone No">
              <span class="text-danger" v-if="pinia_user.errors?.phone">{{ pinia_user.errors?.phone[0] }}</span>
          </div>
          <div class="form-group">
              <label for="master_email">Email</label>
              <input type="email" class="form-control" id="master_email" v-model="modalForm.email" placeholder="Master Email Address">
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-success" type="submit">Save</button>
        </div>
      </form>
    </LocalModal>

    <section class="content pt-3">
      <div class="container-fluid">
        <div class="row">
          <div class="col-md-12">
            <h4 class="text-center">Add Order</h4 >
          </div>
        </div>
        
        <div class="row">
  
          <div class="col-md-5">
            <div class="card card-primary">
                <div class="card-header">
                  <h3 class="card-title">Select Customer</h3>
                </div>
                <div class="card-body">
                  <div class="form-group">
                    <label for="category">Select Your Customer</label>
                    <div class="d-flex">
                      <v-select style="width: 100%;" :options="simple_customers" label="name" v-model="form.customer"></v-select>
                      <span class="input-group-append">
                        <button type="button" class="btn btn-primary btn-flat ml-2 mb-0 py-0" @click="openModal('customer')" ><b>+</b></button>
                      </span>
                    </div>
                    <span class="text-danger" v-if="errors?.customer">{{ errors.customer[0] }}</span>
                  </div>

                  <table class="table table-bordered">
                      <tr>
                          <th>Name: </th>
                          <td>{{ form.customer?.name }}</td>
                      </tr>                    
                      <tr>
                          <th>Phone: </th>
                          <td>{{ form.customer?.phone }}</td>
                      </tr>
                      <tr>
                          <th>Email: </th>
                          <td>{{ form.customer?.email }}</td>
                      </tr>
                      <tr>
                          <th>Address: </th>
                          <td>{{ form.customer?.address }}</td>
                      </tr>
                  </table>
                </div>
            </div>
          </div>

          <div class="col-md-7">
            <div class="card card-primary">
                <div class="card-header">
                  <h3 class="card-title">Add Client's Note</h3>
                </div>

                <div class="card-body">
                  <table class="table border">
                      <tr v-for="(item, index) in form.client_notes" :key="item.id">
                          <th width="30px">{{index+1}}.</th>
                          <td style="width:65px !important;" class="px-0"><input type="number" v-model="form.client_notes[index].num" class="form-control"></td>
                          <td style="width:70px !important;" class="px-0">
                              <select name="" id="" class="form-control" v-model="form.client_notes[index].frac" style="font-size: 18px; font-weight: bold;">
                                  <option v-for="(fraction, i) in simple_fractions" style="font-size: 18px; font-weight: bold;" :value="fraction.code"><span v-html="fraction.code"></span></option>
                              </select>
                          </td>
                          <td class="mx-0 px-0"><input type="text" v-model="form.client_notes[index].note" class="form-control"></td>
                          <td width="95px" class="d-flex">
                            <a href="#" class="btn btn-primary mr-2" @click.prevent="addClientNote"><b>+</b></a>
                            <a href="#" class="btn btn-danger" @click.prevent="removeClientNote(item.id)"><b>-</b></a>
                          </td>
                      </tr>
                  </table>
                </div>
            </div>
          </div>
        </div>

        
        <div class="row">
          <div class="col-md-6">           
            <div class="card card-info">
                <div class="card-header">
                  <h3 class="card-title">Select Master</h3>
                </div>
                <div class="card-body">
                  <div class="form-group">
                    <label for="category">Select Your Master</label>
                    <div class="d-flex">
                      <v-select style="width: 100%;" :options="simple_masters" label="name" v-model="form.master"></v-select>
                      <span class="input-group-append">
                        <button type="button" class="btn btn-info btn-flat ml-2 mb-0 py-0" @click="openModal('master')" ><b>+</b></button>
                      </span>
                    </div>
                    <span class="text-danger" v-if="errors?.master">{{ errors.master[0] }}</span>
                  </div>

                  <table class="table table-bordered">
                      <tr>
                          <th>Name: </th>
                          <td>{{ form.master?.name }}</td>
                      </tr>                    
                      <tr>
                          <th>Phone: </th>
                          <td>{{ form.master?.phone }}</td>
                      </tr>
                      <tr>
                          <th>Email: </th>
                          <td>{{ form.master?.email }}</td>
                      </tr>                    
                  </table>
                </div>
            </div>
          </div>

          <div class="col-md-6">
            <div class="card card-info">
                <div class="card-header">
                  <h3 class="card-title">Add Master's Note</h3>
                </div>

                <div class="card-body">
                  <table class="table table-bordered">
                      <tr v-for="(item, index) in form.master_notes" :key="item.id">
                          <th>{{index+1}}.</th>
                          <td><input type="text" v-model="form.master_notes[index].note" class="form-control"></td>
                          <td class="d-flex">
                            <a href="#" class="btn btn-info mr-2" @click.prevent="addMasterNote"><b>+</b></a>
                            <a href="#" class="btn btn-danger" @click.prevent="removeMasterNote(item.id)"><b>-</b></a>
                          </td>
                      </tr>
                  </table>
                </div>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-md-12">
            <div class="card card-success">
              <div class="card-header">
                <h3 class="card-title">Add Order Item</h3>    
              </div>
 
              <form>
                <div class="card-body">
                    <table class="table table-bordered table-hover add_order" style="width:100%">
                        <tr v-for="(item, index) in form.order_items" :key="item.id">
                            <table class="table table-bordered table-hover">
                               
                                <tr>
                                    <th>{{index+1}}) Select Item</th>
                                    <td style="width:222px"><v-select :options="simple_items" label="name" v-model="form.order_items[index].item" style="font-weight: bold"></v-select></td>
                                    <th>লম্বা</th>
                                    <td class="d-flex" style="width:222px">
                                      <input type="number" min="0" class="form-control mr-1" v-model="form.order_items[index].long">
                                      <select name="" id="" class="form-control" v-model="form.order_items[index].frac_long" style="font-size: 18px; font-weight: bold;">
                                        <option v-for="(fraction, i) in simple_fractions" style="font-size: 18px; font-weight: bold;" :value="fraction.value"><span v-html="fraction.code"></span></option>
                                      </select>
                                    </td>
                                    <th >চেস্ট</th>
                                    <td class="d-flex" style="width:222px">
                                      <input type="number" min="0" class="form-control mr-1" v-model="form.order_items[index].cheast">
                                      <select name="" id="" class="form-control" v-model="form.order_items[index].frac_cheast" style="font-size: 18px; font-weight: bold;">
                                        <option v-for="(fraction, i) in simple_fractions" style="font-size: 18px; font-weight: bold;" :value="fraction.value"><span v-html="fraction.code"></span></option>
                                      </select>                                    
                                    </td>
                                </tr>
                                <tr>
                                    <th>বেলী</th>
                                    <td class="d-flex" style="width:222px">
                                      <input type="number" min="0" class="form-control mr-1" v-model="form.order_items[index].belly">
                                      <select name="" id="" class="form-control" v-model="form.order_items[index].frac_belly" style="font-size: 18px; font-weight: bold;">
                                        <option v-for="(fraction, i) in simple_fractions" style="font-size: 18px; font-weight: bold;" :value="fraction.value"><span v-html="fraction.code"></span></option>
                                      </select>                                    
                                    </td>
                                    <th>হিপ</th>
                                    <td class="d-flex" style="width:222px">
                                      <input type="number" min="0" class="form-control mr-1" v-model="form.order_items[index].hip">
                                      <select name="" id="" class="form-control" v-model="form.order_items[index].frac_hip" style="font-size: 18px; font-weight: bold;">
                                        <option v-for="(fraction, i) in simple_fractions" style="font-size: 18px; font-weight: bold;" :value="fraction.value"><span v-html="fraction.code"></span></option>
                                      </select>                                    
                                    </td>
                                    <th>শোল্ডার</th>
                                    <td class="d-flex" style="width:222px">
                                      <input type="number" min="0" class="form-control mr-1" v-model="form.order_items[index].shoulder">
                                      <select name="" id="" class="form-control" v-model="form.order_items[index].frac_shoulder" style="font-size: 18px; font-weight: bold;">
                                        <option v-for="(fraction, i) in simple_fractions" style="font-size: 18px; font-weight: bold;" :value="fraction.value"><span v-html="fraction.code"></span></option>
                                      </select>                                    
                                    </td>                                    
                                </tr>
                                <tr>
                                    <th>হাতার লম্বা ফুল</th>
                                    <td class="d-flex" style="width:222px">
                                      <input type="number" min="0" class="form-control mr-1" v-model="form.order_items[index].hand_long_full">
                                      <select name="" id="" class="form-control" v-model="form.order_items[index].frac_hand_long_full" style="font-size: 18px; font-weight: bold;">
                                        <option v-for="(fraction, i) in simple_fractions" style="font-size: 18px; font-weight: bold;" :value="fraction.value"><span v-html="fraction.code"></span></option>
                                      </select>                                    
                                    </td>
                                    <th>কপ</th>
                                    <td class="d-flex" style="width:222px">
                                      <input type="number" min="0" class="form-control mr-1" v-model="form.order_items[index].kop">
                                      <select name="" id="" class="form-control" v-model="form.order_items[index].frac_kop" style="font-size: 18px; font-weight: bold;">
                                        <option v-for="(fraction, i) in simple_fractions" style="font-size: 18px; font-weight: bold;" :value="fraction.value"><span v-html="fraction.code"></span></option>
                                      </select>                                    
                                    </td>
                                      <th>গলা</th>
                                      <td class="d-flex" style="width:222px">
                                        <input type="number" min="0" class="form-control mr-1" v-model="form.order_items[index].throat">
                                        <select class="form-control" v-model="form.order_items[index].frac_throat" style="font-size: 18px; font-weight: bold;">
                                          <option v-for="(fraction, i) in simple_fractions" style="font-size: 18px; font-weight: bold;" :value="fraction.value"><span v-html="fraction.code"></span></option>
                                        </select>                                    
                                      </td>                                      
                                  </tr>
                                  <tr>
                                      <th>মোট লুজ</th>
                                      <td class="d-flex" style="width:222px">
                                        <input type="number" min="0" class="form-control mr-1" v-model="form.order_items[index].total_loose">
                                        <select name="" id="" class="form-control" v-model="form.order_items[index].frac_total_loose" style="font-size: 18px; font-weight: bold;">
                                          <option v-for="(fraction, i) in simple_fractions" style="font-size: 18px; font-weight: bold;" :value="fraction.value"><span v-html="fraction.code"></span></option>
                                        </select>                                    
                                      </td>
                                      <th>ঘের</th>
                                      <td class="d-flex" style="width:222px">
                                        <input type="number" min="0" class="form-control mr-1" v-model="form.order_items[index].enclosure">
                                        <select name="" id="" class="form-control" v-model="form.order_items[index].frac_enclosure" style="font-size: 18px; font-weight: bold;">
                                          <option v-for="(fraction, i) in simple_fractions" style="font-size: 18px; font-weight: bold;" :value="fraction.value"><span v-html="fraction.code"></span></option>
                                        </select>                                    
                                      </td>
                                      <th>সামনা চেস্ট</th>
                                      <td class="d-flex" style="width:222px">
                                        <input type="number" min="0" class="form-control mr-1" v-model="form.order_items[index].front_cheast">
                                        <select name="" id="" class="form-control" v-model="form.order_items[index].frac_front_cheast" style="font-size: 18px; font-weight: bold;">
                                          <option v-for="(fraction, i) in simple_fractions" style="font-size: 18px; font-weight: bold;" :value="fraction.value"><span v-html="fraction.code"></span></option>
                                        </select>                                    
                                      </td>
                                  </tr>
                                  <tr>                                      
                                      <th>সামনা বেলী</th>
                                      <td class="d-flex" style="width:222px">
                                        <input type="number" min="0" class="form-control mr-1" v-model="form.order_items[index].front_belly">
                                        <select name="" id="" class="form-control" v-model="form.order_items[index].frac_front_belly" style="font-size: 18px; font-weight: bold;">
                                          <option v-for="(fraction, i) in simple_fractions" style="font-size: 18px; font-weight: bold;" :value="fraction.value"><span v-html="fraction.code"></span></option>
                                        </select>                                    
                                      </td>
                                      <th>সামনা হিপ</th>
                                      <td class="d-flex" style="width:222px">
                                        <input type="number" min="0" class="form-control mr-1" v-model="form.order_items[index].front_hip">
                                        <select name="" id="" class="form-control" v-model="form.order_items[index].frac_front_hip" style="font-size: 18px; font-weight: bold;">
                                          <option v-for="(fraction, i) in simple_fractions" style="font-size: 18px; font-weight: bold;" :value="fraction.value"><span v-html="fraction.code"></span></option>
                                        </select>                                    
                                      </td>
                                      <th>মোড়া</th>
                                      <td class="d-flex" style="width:222px">
                                        <input type="number" min="0" class="form-control mr-1" v-model="form.order_items[index].wrap">
                                        <select name="" id="" class="form-control" v-model="form.order_items[index].frac_wrap" style="font-size: 18px; font-weight: bold;">
                                          <option v-for="(fraction, i) in simple_fractions" style="font-size: 18px; font-weight: bold;" :value="fraction.value"><span v-html="fraction.code"></span></option>
                                        </select>                                    
                                      </td>                                      
                                  </tr>
                                <tr>
                                    <th>বাহু</th>                                     
                                    <td class="d-flex" style="width:222px">
                                      <input type="number" min="0" class="form-control mr-1" v-model="form.order_items[index].arm">
                                      <select name="" id="" class="form-control" v-model="form.order_items[index].frac_arm" style="font-size: 18px; font-weight: bold;">
                                        <option v-for="(fraction, i) in simple_fractions" style="font-size: 18px; font-weight: bold;" :value="fraction.value"><span v-html="fraction.code"></span></option>
                                      </select>                                    
                                    </td>
                                    <th>কপ বাহু</th>
                                    <td class="d-flex" style="width:222px">
                                      <input type="number" min="0" class="form-control mr-1" v-model="form.order_items[index].kop_arm">
                                      <select name="" id="" class="form-control" v-model="form.order_items[index].frac_kop_arm" style="font-size: 18px; font-weight: bold;">
                                        <option v-for="(fraction, i) in simple_fractions" style="font-size: 18px; font-weight: bold;" :value="fraction.value"><span v-html="fraction.code"></span></option>
                                      </select>                                    
                                    </td>
                                    <th>Action</th>
                                      <td class="d-flex justify-content-center" style="width:222px">
                                      <a href="#" class="btn btn-success mr-2" @click.prevent="addItem"><b>+</b></a>
                                      <a href="#" class="btn btn-danger" @click.prevent="removeItem(item.id)"><b>-</b></a>
                                    </td>
                                </tr>

                                <tr>
                                  <th>Unit Price</th>
                                    <td style="width:222px"><input type="number" class="form-control" v-model="form.order_items[index].unit_price" @keyup="calculateUnitPrice(item.id)"></td>
                                    <th>Quantity</th>
                                    <td style="width:222px"><input type="number" class="form-control" v-model="form.order_items[index].qty" @keyup="calculateUnitPrice(item.id)" :min=0></td>
                                    <th>Net Price</th>
                                    <td style="width:222px"><input type="number" class="form-control" v-model="form.order_items[index].net_price" disabled></td>
                                    
                                </tr>
                                
                            </table>    
                        </tr>
                    </table>          
                </div>
              </form>
            </div>
            <!-- /.card -->
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