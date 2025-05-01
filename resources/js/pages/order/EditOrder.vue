<script setup>
import {reactive,onMounted} from "vue"
import {useRouter, useRoute} from "vue-router"
import {storeToRefs} from "pinia"
import Datepicker from 'vuejs3-datepicker';
import _ from "lodash"
import { useNotification, useAuth, useSimple } from "@/stores";
const auth = useAuth();
const pinia_simple = useSimple();
const router = useRouter();
const route = useRoute();
const notify = useNotification();
const {simple_customers,simple_products} = storeToRefs(pinia_simple);
let counter = reactive(0);
let payment_counter = reactive(0);

const form = reactive({
    sale_id: route.params?.id,
    user_id: auth.user.id,
    shop_id: auth.user.shop.id,
    customer: null,
    date: null,
    sub_total: 0,
    discount: 0,
    grand_total: 0,
    paid: 0,
    due: 0,
    is_return: 0,
    sale_items: [],
    sale_payments: [],
    
});

onMounted(async()=>{
  await pinia_sale.show(route.params?.id);
  await pinia_simple.fetchSimpleCustomers(auth.user?.shop?.id)
  await pinia_simple.fetchSimpleProducts(auth.user?.shop?.id)

  form.customer = sale.value['customer']
  form.date = sale.value.date
  form.sub_total = sale.value.sub_total
  form.discount = sale.value.discount
  form.grand_total = sale.value.grand_total
  form.paid = sale.value.paid
  form.due = sale.value.due
  form.is_return = sale.value.is_return

  counter = sale.value['sale_details'].length - 1
  sale.value['sale_details'].forEach((el,index)=>{
    form.sale_items.push(
        {
            id: index,
            sale_details_id: el['id'],
            product: el['product'],
            unit_price: el['unit_price'],
            qty: el['qty'],
            net_price: el['net_price'],
        }
    )
  })

  payment_counter = sale.value['sale_payments'].length - 1
  sale.value['sale_payments'].forEach((el,index)=>{
    form.sale_payments.push(
        {
            id: index,
            sale_payments_id: el['id'],
            amount: el['amount'],
            date: el['date'],
            payment_method: el['payment_method'],
        }
    )
  })

})

const changeStatus = () => {
    form.is_return = !form.is_return;
}
const addItem = () => {
      form.sale_items.push(
          {
              id:`${++counter}`,
              sale_details_id: null,
              product:null,
              unit_price:0,
              qty:0,
              net_price:0,
          }
      )
}
const removeItem = (item_id) => {
    if( form.sale_items.length > 1){
      form.sale_items.splice(_.findIndex(form.sale_items, o=>{
            return o.id == item_id;
        }), 1);

      form.sub_total = 0;
      form.sale_items.forEach(element => {
          form.sub_total += Number(element.net_price)
      });
      form.grand_total = form.sub_total - form.discount
      form.due = form.grand_total - form.paid
    }
}

const addPayment = () => {
      form.sale_payments.push(
          {
              id:`${++payment_counter}`,
              sale_payments_id: null,
              amount: 0,
              date: new Date(),
              payment_method: 'CASH',
          }
      )
}
const removePayment = async (item_id) => {
    if( form.sale_payments.length > 1){

      form.sale_payments.splice(_.findIndex(form.sale_payments, o=>{
          return o.id == item_id;
      }), 1);

      form.sub_total = 0;
      form.sale_payments.forEach(element => {
          form.sub_total += element.net_price
      });
      form.discount = 0
      form.grand_total = 0
      form.paid = 0
      form.due = 0
    }
}


const updateSale = async() => {
  const resData = await pinia_sale.update(form.sale_id,form);
  if(resData?.status){
    notify.Success(resData.message);
    form.customer = null
    form.sub_total= 0
    form.discount= 0
    form.grand_total= 0
    form.paid= 0
    form.due= 0
    form.is_return = 0
    form.sale_items = []
    form.sale_payments = []
    router.push({name: 'sale.index'})
  }else{
    notify.Error(resData?.message);
  }
}

const calculateUnitPrice = (itemId) => {
    let index = _.findIndex(form.sale_items, o =>{ return o.id == itemId});
    let net_price = parseFloat(form.sale_items[index].net_price).toFixed(2)
    let quantity = Number(form.sale_items[index].qty) 
    if(quantity > 0){
      form.sale_items[index].unit_price = (net_price / quantity).toFixed(2)
    }
    form.sub_total = 0;
    form.sale_items.forEach(element => {
        form.sub_total += parseFloat(element.net_price)
    });
    processPricing();
}

const processPaidAmount = () => {
  form.paid = 0
  form.sale_payments.forEach(element => {
      form.paid += parseFloat(element.amount)
  });
  form.due = (form.grand_total - form.paid).toFixed(2)
}

const processPricing = () => {
  form.grand_total = (form.sub_total - form.discount).toFixed(2)
  form.due = (form.grand_total - form.paid).toFixed(2)
}

</script>

<template>
    <section class="content pt-3">
      <h3 class="text-center text-success">View Sale</h3>
      <div v-if="loading" class="text-center m-5">
          <span
              class="spinner-border spinner-border-sm mr-1 text-dark"
              style="padding: 12px; font-size: 20px;"
          ></span>
          <div>Loading....</div>
      </div>
      <div class="container-fluid" v-else>
        
        <div class="row">
          <!-- left column -->
          <div class="col-md-6">
            <!-- general form elements -->
            <div class="card card-primary">
              <div class="card-header">
                <h3 class="card-title">Select Customer</h3>
              </div>
              <!-- /.card-header -->
              <!-- form start -->

                <div class="card-body">

                  <div class="form-group">
                    <label for="category">Select Your Customer</label>
                    <v-select :options="simple_customers" label="name" v-model="form.customer" disabled></v-select>
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
            <!-- /.card -->
          </div>

          <div class="col-md-6">
            <!-- general form elements -->
            <div class="card card-info">
              <div class="card-header">
                <h3 class="card-title">Additional Info</h3>
              </div>
              <!-- /.card-header -->
              <!-- form start -->

                <div class="card-body">

                  <table class="table table-hover table-bordered">
                      <tr>
                        <th>Is Return?</th>
                        <td>
                          <div class="form-group pt-2">
                            <div class="custom-control custom-switch">
                              <input type="checkbox" class="custom-control-input" disabled change="changeStatus" :checked="form.is_return" id="is_return">
                              <label class="custom-control-label" for="is_return"> ({{ form.is_return ? 'Yes' : 'No' }})</label>
                            </div>
                          </div>
                        </td>
                      </tr>
                      <tr>
                        <th class="text-info">Product Receiving Date:</th>
                        <td>
                          <datepicker 
                            v-model="form.date"
                            placeholder="Sale Date"
                            iconColor="red"
                            iconWidth="18"
                            disabled
                            iconHeight="18">
                          </datepicker>
                        </td>

                      </tr>
                  </table>

                </div>

   
            </div>
            <!-- /.card -->
          </div>
        </div>

        <div class="row">
          <!-- left column -->
          <div class="col-md-12">
            <!-- general form elements -->
            <div class="card card-success">
              <div class="card-header">
                <h3 class="card-title">Sold Items</h3>
              </div>
              <!-- /.card-header -->
              <!-- form start -->
              <form>
                <div class="card-body">
                    <table class="table table-bordered table-hover">
                        <tr>
                          <th>Select Product</th>
                          <th>Unit Price</th>
                          <th>Quantity</th>
                          <th>Net Price</th>
                          <!-- <th>Action</th> -->
                        </tr>
                        <tr v-for="(item, index) in form.sale_items" :key="item.id">
                            <td> <v-select disabled :options="simple_products" label="name" v-model="form.sale_items[index].product"></v-select></td>
                            <td><input disabled type="number" class="form-control" v-model="form.sale_items[index].unit_price" @keyup="calculateUnitPrice(item.id)"><span class="text-danger" v-if="form.sale_items[index].product && form.lcp">LCP: {{ form.sale_items[index].product?.latest_cost_price }}</span></td>
                            <td><input disabled type="number" class="form-control" v-model="form.sale_items[index].qty" @keyup="calculateUnitPrice(item.id)"> <span class="text-danger" v-if="form.sale_items[index].product" v-show="false">Stock: {{ form.sale_items[index].product?.stock }}</span></td>
                            <td><input disabled type="number" class="form-control" v-model="form.sale_items[index].net_price"></td>
                            <!-- <td class="d-flex">
                              <a href="#" class="btn btn-primary mr-2" @click.prevent="addItem"><b>+</b></a>
                              <a href="#" class="btn btn-danger" @click.prevent="removeItem(item.id)"><b>-</b></a>
                            </td> -->
                        </tr>
                    </table>          
                </div>
              </form>
            </div>
            <!-- /.card -->
          </div>
        </div>

        <div class="row">
          <!-- left column -->
          <div class="col-md-12">
            <!-- general form elements -->
            <div class="card card-primary">
              <div class="card-header">
                <h3 class="card-title">Sale Payments</h3>
              </div>
              <!-- /.card-header -->
              <!-- form start -->
              <form>
                <div class="card-body">
                    <table class="table table-bordered table-hover">
                        <tr>
                          <th>No</th>
                          <th>Amount</th>
                          <th>Date</th>
                          <th>Payment Method</th>
                          <!-- <th>Action</th> -->
                        </tr>
                        <tr v-for="(item, index) in form.sale_payments" :key="item.id">
                            <td><b>{{ index+1 }}</b></td>
                            <td><input disabled type="number" class="form-control" v-model="form.sale_payments[index].amount" @keyup="processPaidAmount"></td>
                            <td>
                                <datepicker 
                                  v-model="form.sale_payments[index].date"
                                  placeholder="Sale Date"
                                  iconColor="red"
                                  iconWidth="18"
                                  disabled
                                  iconHeight="18">
                                </datepicker>
                            </td>
                            <td>
                              <select disabled class="custom-select" v-model="form.sale_payments[index].payment_method">
                                <option value="CASH">CASH</option>
                                <option value="BKASH">BKASH</option>
                              </select>
                            </td>
                            <!-- <td class="d-flex">
                              <a href="#" class="btn btn-primary mr-2" @click.prevent="addPayment"><b>+</b></a>
                              <a href="#" class="btn btn-danger" @click.prevent="removePayment(item.id)" v-show="item.sale_payments_id==null"><b>-</b></a>
                            </td> -->
                        </tr>
                    </table>          
                </div>
              </form>
            </div>
            <!-- /.card -->
          </div>
        </div>

        <div class="row">
          <div class="col-md-6"></div>
          <div class="col-md-6">
            <!-- general form elements -->
            <div class="card ">

              <!-- /.card-header -->
              <!-- form start -->

                <div class="card-body">


                  <table class="table table-bordered">
                      <tr>
                          <th class="text-secondary">Sub Total: </th>
                          <td><input type="number" class="form-control" v-model="form.sub_total" disabled></td>
                      </tr>
                      <tr>
                          <th class="text-success">Discount: </th>
                          <td><input type="number" class="form-control" v-model="form.discount" @keyup="processPricing()" disabled></td>
                      </tr>
                      <tr>
                          <th class="text-primary">Grand Total: </th>
                          <td><input type="number" class="form-control" v-model="form.grand_total" disabled></td>
                      </tr>
                      <tr>
                          <th class="text-success">Paid:</th>
                          <td><input type="number" class="form-control" v-model="form.paid" disabled></td>
                      </tr>
                      <tr>
                          <th class="text-danger">Due: </th>
                          <td><input type="number" class="form-control" v-model="form.due" disabled></td>
                      </tr>
                      <!-- <tr>
                          <td colspan="2"><button class="btn btn-block btn-primary" @click.prevent="updateSale" type="buton" :disabled="updateLoading">Update</button></td>
                      </tr> -->
                  </table>

 
                </div>

   
            </div>
            <!-- /.card -->
          </div>
        </div>
        <!-- /.row -->
      </div><!-- /.container-fluid -->
    </section>
</template>


<style scoped>
input {font-weight:bold !important;}
select {font-weight:bold !important;}
</style>