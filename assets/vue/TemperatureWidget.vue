<template>
  <div class="temperature-widget mt-6 border-t border-slate-100 pt-6">
    <h4 class="text-xs font-black uppercase tracking-widest text-slate-400 mb-4 italic">Raport Klimatyczny (Temperatura)</h4>
    
    <div class="space-y-4">
      <div v-for="zone in schema.zones" :key="zone.id" 
             class="bg-slate-50 border border-slate-100 p-4 rounded-xl transition-colors"
             :class="{'opacity-60': isReadOnly}">
        
        <div class="flex justify-between items-center mb-3">
            <div>
                <span class="text-sm font-bold italic text-slate-900 block">{{ zone.name }}</span>
                <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Norma: {{ zone.min }}°C - {{ zone.max }}°C</span>
            </div>
            <div class="relative flex items-center gap-2">
                <input type="number" 
                       step="0.1"
                       v-model="localPayload[`${zone.id}_temp`]" 
                       :disabled="isReadOnly"
                       @input="validateZone(zone)"
                       class="w-24 px-3 py-2 text-right font-mono font-bold text-orange-600 bg-white border border-slate-300 rounded focus:ring-orange-600 focus:ring-2 focus:outline-none disabled:bg-slate-200"
                       placeholder="np. 21.5">
                <span class="text-slate-500 font-bold">°C</span>
            </div>
        </div>

        <div v-if="hasDeviation(zone)" class="mt-3 p-3 bg-rose-50 border border-rose-100 rounded-lg">
            <p class="text-[10px] font-black uppercase text-rose-600 tracking-widest mb-2 flex items-center gap-1">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                Odchylenie temperatury!
            </p>
            <textarea 
                v-model="localPayload[`${zone.id}_notes`]"
                :disabled="isReadOnly"
                @input="validateZone(zone)"
                class="w-full text-sm p-2 bg-white border border-rose-200 rounded focus:ring-rose-500 focus:outline-none focus:border-rose-500 placeholder:text-rose-300 disabled:bg-slate-100 disabled:text-slate-500"
                placeholder="Wymagane: Opisz przyczynę odstępstwa i podjęte działania..."></textarea>
        </div>
      </div>
    </div>

    <div class="mt-4 p-4 rounded-xl text-center" 
         :class="isValid ? 'bg-emerald-50 border border-emerald-100' : 'bg-orange-50 border border-orange-100'"
         v-if="!isReadOnly">
        <p v-if="isValid" class="text-[10px] font-black uppercase text-emerald-600 tracking-widest">
            Wszystkie dane poprawne. Można zakończyć.
        </p>
        <p v-else class="text-[10px] font-black uppercase text-orange-600 tracking-widest">
            Uzupełnij wszystkie odczyty oraz uwagi do odchyleń.
        </p>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, computed } from 'vue';

const props = defineProps({
  schema: { type: Object, default: () => ({ zones: [] }) },
  payload: { type: Object, default: null },
  isReadOnly: { type: Boolean, default: false }
});

const emit = defineEmits(['update:payload', 'validity']);

const localPayload = ref({});

const initPayload = () => {
    if (props.payload && Object.keys(props.payload).length > 0) {
        localPayload.value = { ...props.payload };
    } else {
        const initial = {};
        (props.schema.zones || []).forEach((zone) => {
            initial[`${zone.id}_temp`] = null;
            initial[`${zone.id}_notes`] = '';
        });
        localPayload.value = initial;
    }
    checkValidity();
};

watch(() => props.payload, initPayload, { immediate: true });

const hasDeviation = (zone) => {
    const val = localPayload.value[`${zone.id}_temp`];
    if (val === null || val === undefined || val === '') return false;
    const temp = parseFloat(val);
    if (isNaN(temp)) return false;
    return temp < zone.min || temp > zone.max;
};

const isValid = computed(() => {
    if (!props.schema.zones || props.schema.zones.length === 0) return true;
    
    return props.schema.zones.every(zone => {
        const tempVal = localPayload.value[`${zone.id}_temp`];
        
        // Temperature must be provided
        if (tempVal === null || tempVal === undefined || tempVal === '') return false;
        
        // If there is a deviation, notes must be provided
        if (hasDeviation(zone)) {
            const notes = localPayload.value[`${zone.id}_notes`];
            if (!notes || notes.trim() === '') return false;
        }
        
        return true;
    });
});

const validateZone = () => {
    emitUpdate();
};

const emitUpdate = () => {
    emit('validity', isValid.value);
    // clone to avoid proxy tracking leaks over time if needed, although simple decomposition is fine
    emit('update:payload', { ...localPayload.value });
};

// Initial validation tick just in case
watch(() => isValid.value, (val) => {
    emit('validity', val);
}, { immediate: true });

</script>
