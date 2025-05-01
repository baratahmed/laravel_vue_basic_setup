<script setup>
import {useAuth} from "@/stores"
import MainLayout from './layouts/MainLayout.vue';
import GuestLayout from './layouts/GuestLayout.vue';
const auth = useAuth();
</script>
<template>
    <MainLayout v-if="auth.getAuthStatus">
            <section class="content">
                <router-view></router-view>
            </section>
    </MainLayout>

    <GuestLayout v-if="!auth.getAuthStatus">
        <router-view></router-view>
    </GuestLayout>
</template>

<style lang="">
</style>
