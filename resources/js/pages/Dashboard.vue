<script setup>
import { onMounted, ref, computed} from "vue";
import Datepicker from 'vuejs3-datepicker';  // It's used
import {storeToRefs} from "pinia"
import { debounce } from "lodash";
import { useDashboard, useAuth } from "@/stores";

const auth = useAuth();
const pinia_dashboard = useDashboard();
const {dashboard} = storeToRefs(pinia_dashboard);

var d = new Date();
const from = ref( new Date(`${d.getFullYear()}-${d.getMonth()}-${d.getDate()}`));
const to = ref(new Date());

onMounted(async()=>{
    await pinia_dashboard.index(auth.user?.shop?.id, from.value, to.value)
})

const getResults = async () => {
    await pinia_dashboard.index(auth.user?.shop?.id, from.value, to.value)
}

const debounceSearch = computed(()=> debounce(getResults,500));

</script>
<template lang="">
    <div class="row">
        <div class="col-12 my-4">
            <div class="d-flex">
                <div class="mr-auto p-2">
                    <div class="tb_search d-flex">
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
        </div>
       
        <div v-if="dashboard == undefined || dashboard.length == 0" class="col-12">
            <div class="text-center m-5">
                <span
                    class="  spinner-border spinner-border-sm mr-1 text-dark"
                    style="padding: 12px; font-size: 20px;"
                ></span>
                <div>Loading....</div>
            </div>
        </div>

        <div class="row mx-2" v-else>

            <div class="col-lg-3 col-6">
                <!-- small box -->
                <div class="small-box bg-info">
                <div class="inner">
                    <h3>30</h3>
                    <h5>Total Teachers</h5>
                </div>
                <div class="icon">
                    <i class="ion ion-bag"></i>
                </div>
                <router-link :to="{name: 'user.dashboard'}" class="small-box-footer" :class="{ disabled: auth.user?.role == 'Employee' }">More info <i class="fas fa-arrow-circle-right"></i></router-link>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <!-- small box -->
                <div class="small-box bg-warning">
                <div class="inner">
                    <h3>100</h3>

                    <h5>Total Meals</h5>
                </div>
                <div class="icon">
                    <i class="ion ion-person-add"></i>
                </div>
                <router-link :to="{name: 'user.dashboard'}" class="small-box-footer" :class="{ disabled: auth.user?.role == 'Employee' }">More info <i class="fas fa-arrow-circle-right"></i></router-link>
                </div>
            </div>

            
            <div class="col-lg-3 col-6">
                <!-- small box -->
                <div class="small-box bg-success">
                <div class="inner">
                    <h3>50</h3>

                    <h5>Total Orders</h5>
                </div>
                <div class="icon">
                    <i class="ion ion-pie-graph"></i>
                </div>
                <router-link :to="{name: 'user.dashboard'}" class="small-box-footer" :class="{ disabled: auth.user?.role == 'Employee' }">More info <i class="fas fa-arrow-circle-right"></i></router-link>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <!-- small box -->
                <div class="small-box bg-danger">
                <div class="inner">
                    <h3>10</h3>

                    <h5>Total Pending</h5>
                </div>
                <div class="icon">
                    <i class="ion ion-pie-graph"></i>
                </div>
                <router-link :to="{name: 'user.dashboard'}" class="small-box-footer" :class="{ disabled: auth.user?.role == 'Employee' }">More info <i class="fas fa-arrow-circle-right"></i></router-link>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <!-- small box -->
                <div class="small-box bg-success">
                    <div class="inner">
                        <!-- <h3>53<sup style="font-size: 20px">%</sup></h3> -->
                        
                        <h3>5</h3>
                        <h5>Total Cancellations</h5>
                    </div>
                    <div class="icon">
                        <i class="ion ion-stats-bars"></i>
                    </div>
                    <a href="#" class="small-box-footer" :class="{ disabled: auth.user?.role == 'Employee' }">More info <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>   


            <div class="col-lg-3 col-6">
                <!-- small box -->
                <div class="small-box bg-warning">
                <div class="inner">
                    <h3>3</h3>

                    <h5>Total Admins</h5>
                </div>
                <div class="icon">
                    <i class="ion ion-person-add"></i>
                </div>
                <router-link :to="{name: 'user.dashboard'}" class="small-box-footer" :class="{ disabled: auth.user?.role == 'Employee' }">More info <i class="fas fa-arrow-circle-right"></i></router-link>
                </div>
            </div>

            
            <div class="col-lg-3 col-6">
                <!-- small box -->
                <div class="small-box bg-success">
                <div class="inner">
                    <h3>27</h3>

                    <h5>Active Users (Now)</h5>
                </div>
                <div class="icon">
                    <i class="ion ion-pie-graph"></i>
                </div>
                <router-link :to="{name: 'user.dashboard'}" class="small-box-footer" :class="{ disabled: auth.user?.role == 'Employee' }">More info <i class="fas fa-arrow-circle-right"></i></router-link>
                </div>
            </div>

            <div class="col-lg-3 col-6"> 
                <!-- small box -->
                <div class="small-box bg-danger">
                <div class="inner">
                    <h3>3</h3>

                    <h5>Inactive Users (Now)</h5>
                </div>
                <div class="icon">
                    <i class="ion ion-pie-graph"></i>
                </div>
                <router-link :to="{name: 'user.dashboard'}" class="small-box-footer" :class="{ disabled: auth.user?.role == 'Employee' }">More info <i class="fas fa-arrow-circle-right"></i></router-link>
                </div>
            </div>


            
        </div>
     


    </div>
</template>

<style scoped>
.disabled {
    opacity: 0.5;
    pointer-events: none;
}
</style>