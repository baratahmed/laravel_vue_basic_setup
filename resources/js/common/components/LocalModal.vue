<script setup>

  const props = defineProps({
      visible: {
          type: Boolean,
          default: false,
      },
  })

  defineEmits(["close"]);

</script>

<template>
  <div v-show="visible">
    <div class="modal fade show" style="display: block;">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header bg-success">
            <h5 class="modal-title" id="exampleModalLabel">
                <slot name="title"/>
            </h5>
            <button type="button" class="close" aria-label="Close" @click.prevent="$emit('close')">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <slot />

        </div>
      </div>
    </div>
  </div>
</template>
