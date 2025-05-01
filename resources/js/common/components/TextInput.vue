<script setup>
defineProps(['modelValue'])
defineEmits(['update:modelValue'])
</script>

<template>
    <input
        :value="modelValue"
        class="form-control"
        @input="$emit('update:modelValue', $event.target.value)"
    />
</template>
