<script setup>
import {reactive,onMounted, ref} from "vue"
import {useRouter, useRoute} from "vue-router"
import {storeToRefs} from "pinia"
import Datepicker from 'vuejs3-datepicker';
import LocalModal from "@/common/components/LocalModal.vue";
import _ from "lodash"
import { useOrder,useNotification, useAuth, useSimple, useUser} from "@/stores";
const auth = useAuth();
const pinia_simple = useSimple();
const pinia_user = useUser();
const router = useRouter();
const route = useRoute();
const pinia_order = useOrder();
const notify = useNotification();
const {simple_customers,simple_masters, simple_items, simple_fractions} = storeToRefs(pinia_simple);
const {errors, order, loading} = storeToRefs(pinia_order);

onMounted(()=>{
  pinia_order.show(route.params?.id)
  pinia_simple.fetchSimpleCustomers(auth.user?.shop?.id)
  pinia_simple.fetchSimpleMasters(auth.user?.shop?.id)
  pinia_simple.fetchSimpleItems()
  pinia_simple.fetchSimpleFractions()
})

</script>

<template>

    <section class="content pt-3">

      <div v-if="loading" class="text-center m-5">
          <span
              class="spinner-border spinner-border-sm mr-1 text-dark"
              style="padding: 12px; font-size: 20px;"
          ></span>
          <div>Loading....</div>
      </div>
      <div class="container-fluid" v-else>
        <div class="row">
          <div class="col-md-12">
            <h4 class="text-center">View Order (ID #{{route.params?.id}})</h4 >
          </div>
        </div>
        
        <div class="row">
  
          <div class="col-md-6">       
            <div class="card card-primary">
                <div class="card-header">
                  <h3 class="card-title">Customer's Info</h3>
                </div>
                <div class="card-body">

                  <table class="table table-bordered">
                      <tr>
                          <th>Name: </th>
                          <td>{{ order.customer?.name }}</td>
                      </tr>                    
                      <tr>
                          <th>Phone: </th>
                          <td>{{ order.customer?.phone }}</td>
                      </tr>
                      <tr>
                          <th>Email: </th>
                          <td>{{ order.customer?.email }}</td>
                      </tr>
                      <tr>
                          <th>Address: </th>
                          <td>{{ order.customer?.address }}</td>
                      </tr>
                  </table>
                </div>
            </div>
          </div>

          <div class="col-md-6">
            <div class="card card-primary">
                <div class="card-header">
                  <h3 class="card-title">Client's Note</h3>
                </div>

                <div class="card-body">
                  <table class="table table-bordered">
                      <tr v-for="(item, index) in order.client_notes" :key="item.id">
                          <th>{{index+1}}.</th>
                          <td>
                            <b v-html="item.note"></b>
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
                  <h3 class="card-title">Master's Info</h3>
                </div>
                <div class="card-body">

                  <table class="table table-bordered">
                      <tr>
                          <th>Name: </th>
                          <td>{{ order.master?.name }}</td>
                      </tr>                    
                      <tr>
                          <th>Phone: </th>
                          <td>{{ order.master?.phone }}</td>
                      </tr>
                      <tr>
                          <th>Email: </th>
                          <td>{{ order.master?.email }}</td>
                      </tr>                    
                  </table>
                </div>
            </div>
          </div>

          <div class="col-md-6">
            <div class="card card-info">
                <div class="card-header">
                  <h3 class="card-title">Master's Note</h3>
                </div>

                <div class="card-body">
                  <table class="table table-bordered">
                      <tr v-for="(item, index) in order.master_notes" :key="item.id">
                          <th>{{index+1}}.</th>
                          <td><input type="text" :value="item.note" class="form-control" :disabled="true"></td>
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
                <h3 class="card-title">Order Item(s)</h3>    
              </div>
 
              <form>
                <div class="card-body">
                    <table class="table table-bordered table-hover view_order" style="width:100%">
                        <tr v-for="(item, index) in order.order_items" :key="item.id">

                            <table class="table table-bordered table-hover">
                               
                                <tr>
                                    <th>{{index+1}}) Item Name</th>
                                    <td><input type="text" class="form-control" :value="order.order_items[index].item['name']" disabled ></td>

                                    <th>লম্বা</th>
                                    <td class="d-flex" style="width:200px">
                                      <input type="number" min="0" class="form-control mr-1" :value="Math.floor(order.order_items[index].long)" disabled>
                                      <select disabled class="form-control" style="font-size: 18px; font-weight: bold;" >
                                        <option></option>
                                        <option v-for="(fraction, i) in simple_fractions" style="font-size: 18px; font-weight: bold;" :value="fraction.value" :selected="fraction.value == (order.order_items[index].long - Math.floor(order.order_items[index].long)).toFixed(3)"><span v-html="fraction.code"></span></option>
                                      </select>
                                    </td>

                                    <th>চেস্ট</th>
                                    <td class="d-flex" style="width:200px">
                                      <input type="number" min="0" class="form-control mr-1" :value="Math.floor(order.order_items[index].cheast)" disabled>
                                      <select disabled class="form-control" style="font-size: 18px; font-weight: bold;" >
                                        <option></option>
                                        <option v-for="(fraction, i) in simple_fractions" style="font-size: 18px; font-weight: bold;" :value="fraction.value" :selected="fraction.value == (order.order_items[index].cheast - Math.floor(order.order_items[index].cheast)).toFixed(3)"><span v-html="fraction.code"></span></option>
                                      </select>
                                    </td>

                                    <th>বেলী</th>
                                    <td class="d-flex" style="width:200px">
                                      <input type="number" min="0" class="form-control mr-1" :value="Math.floor(order.order_items[index].belly)" disabled>
                                      <select disabled class="form-control" style="font-size: 18px; font-weight: bold;" >
                                        <option></option>
                                        <option v-for="(fraction, i) in simple_fractions" style="font-size: 18px; font-weight: bold;" :value="fraction.value" :selected="fraction.value == (order.order_items[index].belly - Math.floor(order.order_items[index].belly)).toFixed(3)"><span v-html="fraction.code"></span></option>
                                      </select>
                                    </td>
                                </tr>
                                 <tr>
                                    <th>হিপ</th>
                                    <td class="d-flex" style="width:200px">
                                      <input type="number" min="0" class="form-control mr-1" :value="Math.floor(order.order_items[index].hip)" disabled>
                                      <select disabled class="form-control" style="font-size: 18px; font-weight: bold;" >
                                        <option></option>
                                        <option v-for="(fraction, i) in simple_fractions" style="font-size: 18px; font-weight: bold;" :value="fraction.value" :selected="fraction.value == (order.order_items[index].hip - Math.floor(order.order_items[index].hip)).toFixed(3)"><span v-html="fraction.code"></span></option>
                                      </select>
                                    </td>
                                    
                                    <th>শোল্ডার</th>
                                    <td class="d-flex">
                                      <input type="number" min="0" class="form-control mr-1" :value="Math.floor(order.order_items[index].shoulder)" disabled>
                                      <select disabled class="form-control" style="font-size: 18px; font-weight: bold;" >
                                        <option></option>
                                        <option v-for="(fraction, i) in simple_fractions" style="font-size: 18px; font-weight: bold;" :value="fraction.value" :selected="fraction.value == (order.order_items[index].shoulder - Math.floor(order.order_items[index].shoulder)).toFixed(3)"><span v-html="fraction.code"></span></option>
                                      </select>
                                    </td>
                                    <th>হাতার লম্বা ফুল</th>
                                    <td class="d-flex">
                                      <input type="number" min="0" class="form-control mr-1" :value="Math.floor(order.order_items[index].hand_long_full)" disabled>
                                      <select disabled class="form-control" style="font-size: 18px; font-weight: bold;" >
                                        <option></option>
                                        <option v-for="(fraction, i) in simple_fractions" style="font-size: 18px; font-weight: bold;" :value="fraction.value" :selected="fraction.value == (order.order_items[index].hand_long_full - Math.floor(order.order_items[index].hand_long_full)).toFixed(3)"><span v-html="fraction.code"></span></option>
                                      </select>
                                    </td>
                                    <th>কপ</th>
                                    <td class="d-flex">
                                      <input type="number" min="0" class="form-control mr-1" :value="Math.floor(order.order_items[index].kop)" disabled>
                                      <select disabled class="form-control" style="font-size: 18px; font-weight: bold;" >
                                        <option></option>
                                        <option v-for="(fraction, i) in simple_fractions" style="font-size: 18px; font-weight: bold;" :value="fraction.value" :selected="fraction.value == (order.order_items[index].kop - Math.floor(order.order_items[index].kop)).toFixed(3)"><span v-html="fraction.code"></span></option>
                                      </select>
                                    </td>                            
                                </tr>

    
                                <tr>                         
                                      <th>গলা</th>
                                      <td class="d-flex">
                                        <input type="number" min="0" class="form-control mr-1" :value="Math.floor(order.order_items[index].throat)" disabled>
                                        <select disabled class="form-control" style="font-size: 18px; font-weight: bold;" >
                                          <option></option>
                                          <option v-for="(fraction, i) in simple_fractions" style="font-size: 18px; font-weight: bold;" :value="fraction.value" :selected="fraction.value == (order.order_items[index].throat - Math.floor(order.order_items[index].throat)).toFixed(3)"><span v-html="fraction.code"></span></option>
                                        </select>
                                      </td>
                                  
                                      <th>মোট লুজ</th>
                                      <td class="d-flex">
                                        <input type="number" min="0" class="form-control mr-1" :value="Math.floor(order.order_items[index].total_loose)" disabled>
                                        <select disabled class="form-control" style="font-size: 18px; font-weight: bold;" >
                                          <option></option>
                                          <option v-for="(fraction, i) in simple_fractions" style="font-size: 18px; font-weight: bold;" :value="fraction.value" :selected="fraction.value == (order.order_items[index].total_loose - Math.floor(order.order_items[index].total_loose)).toFixed(3)"><span v-html="fraction.code"></span></option>
                                        </select>
                                      </td>  
                                      <th>ঘের</th>
                                      <td class="d-flex">
                                        <input type="number" min="0" class="form-control mr-1" :value="Math.floor(order.order_items[index].enclosure)" disabled>
                                        <select disabled class="form-control" style="font-size: 18px; font-weight: bold;" >
                                          <option></option>
                                          <option v-for="(fraction, i) in simple_fractions" style="font-size: 18px; font-weight: bold;" :value="fraction.value" :selected="fraction.value == (order.order_items[index].enclosure - Math.floor(order.order_items[index].enclosure)).toFixed(3)"><span v-html="fraction.code"></span></option>
                                        </select>
                                      </td>  
                                      <th>সামনা চেস্ট</th>
                                      <td class="d-flex">
                                        <input type="number" min="0" class="form-control mr-1" :value="Math.floor(order.order_items[index].front_cheast)" disabled>
                                        <select disabled class="form-control" style="font-size: 18px; font-weight: bold;" >
                                          <option></option>
                                          <option v-for="(fraction, i) in simple_fractions" style="font-size: 18px; font-weight: bold;" :value="fraction.value" :selected="fraction.value == (order.order_items[index].front_cheast - Math.floor(order.order_items[index].front_cheast)).toFixed(3)"><span v-html="fraction.code"></span></option>
                                        </select>
                                      </td>  
                                  </tr>
                                  
                                  <tr>                                      
                                      <th>সামনা বেলী</th>
                                      <td class="d-flex">
                                        <input type="number" min="0" class="form-control mr-1" :value="Math.floor(order.order_items[index].front_belly)" disabled>
                                        <select disabled class="form-control" style="font-size: 18px; font-weight: bold;" >
                                          <option></option>
                                          <option v-for="(fraction, i) in simple_fractions" style="font-size: 18px; font-weight: bold;" :value="fraction.value" :selected="fraction.value == (order.order_items[index].front_belly - Math.floor(order.order_items[index].front_belly)).toFixed(3)"><span v-html="fraction.code"></span></option>
                                        </select>
                                      </td> 
                                      <th>সামনা হিপ</th>
                                      <td class="d-flex">
                                        <input type="number" min="0" class="form-control mr-1" :value="Math.floor(order.order_items[index].front_hip)" disabled>
                                        <select disabled class="form-control" style="font-size: 18px; font-weight: bold;" >
                                          <option></option>
                                          <option v-for="(fraction, i) in simple_fractions" style="font-size: 18px; font-weight: bold;" :value="fraction.value" :selected="fraction.value == (order.order_items[index].front_hip - Math.floor(order.order_items[index].front_hip)).toFixed(3)"><span v-html="fraction.code"></span></option>
                                        </select>
                                      </td> 
                                      <th>মোড়া</th>
                                      <td class="d-flex">
                                        <input type="number" min="0" class="form-control mr-1" :value="Math.floor(order.order_items[index].wrap)" disabled>
                                        <select disabled class="form-control" style="font-size: 18px; font-weight: bold;" >
                                          <option></option>
                                          <option v-for="(fraction, i) in simple_fractions" style="font-size: 18px; font-weight: bold;" :value="fraction.value" :selected="fraction.value == (order.order_items[index].wrap - Math.floor(order.order_items[index].wrap)).toFixed(3)"><span v-html="fraction.code"></span></option>
                                        </select>
                                      </td>     
                                      <th>বাহু</th>                                     
                                      <td class="d-flex">
                                        <input type="number" min="0" class="form-control mr-1" :value="Math.floor(order.order_items[index].arm)" disabled>
                                        <select disabled class="form-control" style="font-size: 18px; font-weight: bold;" >
                                          <option></option>
                                          <option v-for="(fraction, i) in simple_fractions" style="font-size: 18px; font-weight: bold;" :value="fraction.value" :selected="fraction.value == (order.order_items[index].arm - Math.floor(order.order_items[index].arm)).toFixed(3)"><span v-html="fraction.code"></span></option>
                                        </select>
                                      </td>                                 
                                  </tr>
                                <tr>                                   
                                    <th>কপ বাহু</th>
                                    <td class="d-flex">
                                      <input type="number" min="0" class="form-control mr-1" :value="Math.floor(order.order_items[index].kop_arm)" disabled>
                                      <select disabled class="form-control" style="font-size: 18px; font-weight: bold;" >
                                        <option></option>
                                        <option v-for="(fraction, i) in simple_fractions" style="font-size: 18px; font-weight: bold;" :value="fraction.value" :selected="fraction.value == (order.order_items[index].kop_arm - Math.floor(order.order_items[index].kop_arm)).toFixed(3)"><span v-html="fraction.code"></span></option>
                                      </select>
                                    </td> 
                                    <th>Unit Price</th>
                                    <td><input type="number" class="form-control" v-model="order.order_items[index].unit_price" disabled></td>
                                    <th>Quantity</th>
                                    <td><input type="number" class="form-control" v-model="order.order_items[index].qty" disabled></td>
                                    <th>Net Price</th>
                                    <td><input type="number" class="form-control" v-model="order.order_items[index].net_price" disabled></td>
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
                                v-model="order.date"
                                :disabled="true"
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
                                v-model="order.trial_date"
                                :disabled="true"
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
                                v-model="order.delivery_date"
                                :disabled="true"
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
                          <td><input type="number" class="form-control" v-model="order.sub_total" disabled></td>
                      </tr>
                      <tr>
                          <th class="text-success">Discount: </th>
                          <td><input type="number" class="form-control" v-model="order.discount" disabled></td>
                      </tr>
                      <tr>
                          <th class="text-primary">Grand Total: </th>
                          <td><input type="number" class="form-control" v-model="order.grand_total" disabled></td>
                      </tr>
                      <tr>
                          <th class="text-success">Paid:</th>
                          <td><input type="number" class="form-control" v-model="order.paid" disabled></td>
                      </tr>
                      <tr>
                          <th class="text-danger">Due: </th>
                          <td><input type="number" class="form-control" v-model="order.due" disabled></td>
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

.view_order {
    display: block;
    overflow-x: auto;
    white-space: nowrap;
}

</style>