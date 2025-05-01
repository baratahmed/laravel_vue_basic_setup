<script setup>
import { useFileManager, useNotification } from "@/stores";
const file_manager = useFileManager();
const notify = useNotification();

const props = defineProps({
    data: {
        type: Object,
    }
});

const fileRemove = async (fileId) => {
    // try {
    //     const res = await file_manager.removeFile(fileId);
    //     if(res.status == 200){
    //         notify.Success(res.data.message);
    //     }else{
    //         notify.Error(res.data.message);
    //     }
    // } catch (error) {

    // }
}

</script>
<template>
     <div class="dropzone__item dropzone--has-thumbnail dropzone--has-error dropzone__item--style">
        <div class="dropzone__item-thumbnail"><img :src="$filters.makeImagePath(data.file_name)"></div>
        <div class="dropzone__item-controls" @click.prevent="fileRemove(data?.id)">
            <div class="dropzone__item-control">
                <i class="fa fa-trash-alt text-danger"></i>
            </div>
        </div>

        <div class="dropzone__details dropzone__details--style">
            <div class="dropzone__file-size"><span><strong>{{(data.file_size/1024).toFixed(1)}}</strong> kb</span></div>
            <div class="dropzone__filename"><span>{{data.file_original_name}}</span></div>
        </div>
    </div>
</template>