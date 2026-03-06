<template>
  <div class="checklist-widget mt-6 border-t border-slate-100 pt-6">
    <h4 class="text-xs font-black uppercase tracking-widest text-slate-400 mb-4 italic">Checklista Wykonania</h4>
    
    <div class="space-y-3">
      <label v-for="(item, index) in schema.items" :key="index" 
             class="flex justify-between items-center bg-slate-50 border border-slate-100 p-4 rounded-xl cursor-pointer hover:border-orange-200 transition-colors"
             :class="{'opacity-60 cursor-not-allowed': isReadOnly}">
        <span class="text-sm font-bold italic text-slate-900" :class="{'line-through text-slate-400': localPayload[index]}">{{ item }}</span>
        <div class="relative flex items-center">
            <input type="checkbox" 
                   v-model="localPayload[index]" 
                   :disabled="isReadOnly"
                   @change="emitUpdate"
                   class="w-6 h-6 text-orange-600 border-slate-300 rounded focus:ring-orange-600 focus:ring-2 disabled:bg-slate-200 cursor-pointer disabled:cursor-not-allowed">
        </div>
      </label>
    </div>

    <div class="mt-4 p-4 bg-orange-50 border border-orange-100 rounded-xl" v-if="!allChecked && !isReadOnly">
        <p class="text-[10px] font-black uppercase text-orange-600 tracking-widest text-center">Zaznacz wszystkie punkty, aby ukończyć zadanie.</p>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, computed } from 'vue';

const props = defineProps({
  schema: { type: Object, default: () => ({ items: [] }) },
  payload: { type: Object, default: null },
  isReadOnly: { type: Boolean, default: false }
});

const emit = defineEmits(['update:payload', 'validity']);

const localPayload = ref({});

// Initialize payload
const initPayload = () => {
    if (props.payload && Object.keys(props.payload).length > 0) {
        localPayload.value = { ...props.payload };
    } else {
        const initial = {};
        (props.schema.items || []).forEach((_, idx) => {
            initial[idx] = false;
        });
        localPayload.value = initial;
    }
    checkValidity();
};

watch(() => props.payload, initPayload, { immediate: true });

const allChecked = computed(() => {
    if (!props.schema.items || props.schema.items.length === 0) return true;
    return props.schema.items.every((_, idx) => localPayload.value[idx] === true);
});

const checkValidity = () => {
    emit('validity', allChecked.value);
};

const emitUpdate = () => {
    checkValidity();
    emit('update:payload', { ...localPayload.value });
};
</script>
