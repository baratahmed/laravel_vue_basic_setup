import { defineStore } from "pinia";

export const useBulkDelete = defineStore("bulkDelete", {
  state: () => ({
    selectedData: [],
    selectAll: false,
  }),
  getters: {
    getSelectedData: (state)=>{
        return state.selectedData;
      },
  },

  actions: {
    selectAllData(data){
        if(this.selectAll){
            this.selectedData = data.map(item => item.id);
        }else{
            this.selectedData = [];
        }
    },
    toggleSelection(item){
        const index = this.selectedData.indexOf(item?.id)
        if(index==-1){
            this.selectedData.push(item?.id)
        }else{
            this.selectedData.splice(index,1)
        }
    },
    

  },
});
