<script setup>
import { Field } from "vee-validate";
defineProps(['modelValue','name'])
defineEmits(['update:modelValue'])
</script>

<template>
    <Field :name="name" v-slot="{errors, field, meta}">
        <input
            class="form-control"
            :class="{
                    'is-valid':meta.valid && meta.touched,
                    'is-invalid':!meta.valid && meta.touched,
            }"
            :value="modelValue"
            @input="$emit('update:modelValue', $event.target.value)"
            v-bind="{...$attrs, ...field}"
        />
        <span class="text-danger" v-show="errors">{{ errors[0] }}</span>
    </Field>
</template>


<style>
.input-group>.custom-file, .input-group>.custom-select, .input-group>.form-control, .input-group>.form-control-plaintext {
    width: 70%;
}</style>
