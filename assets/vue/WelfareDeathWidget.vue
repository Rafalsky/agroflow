<template>
  <div class="welfare-death-widget mt-6 border-t border-slate-100 pt-6">
    <h4 class="text-xs font-black uppercase tracking-widest text-slate-400 mb-4 italic">Zgłoszenie Upadków (PIWET)</h4>
    
    <div class="bg-slate-50 border border-slate-100 p-4 rounded-xl transition-colors" :class="{'opacity-60': isReadOnly}">
        
        <!-- Kolumna wiekowa i sztuki -->
        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Kategoria Stada</label>
                <select 
                    v-model="localPayload.category" 
                    :disabled="isReadOnly"
                    @change="validateInput"
                    class="w-full px-3 py-2 text-sm font-bold text-slate-700 bg-white border border-slate-300 rounded focus:ring-orange-600 focus:ring-2 focus:outline-none disabled:bg-slate-200">
                    <option value="sows">Lochy (Sows)</option>
                    <option value="piglets">Prosięta (Piglets)</option>
                    <option value="fatteners">Tuczniki / Warchlaki (Fatteners)</option>
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Liczba upadków (Sztuki)</label>
                <div class="relative flex items-center">
                    <input type="number" 
                        min="0"
                        step="1"
                        v-model.number="localPayload.amount" 
                        :disabled="isReadOnly"
                        @input="validateInput"
                        class="w-full px-3 py-2 text-right font-mono font-bold text-rose-600 bg-white border border-slate-300 rounded focus:ring-rose-600 focus:ring-2 focus:outline-none disabled:bg-slate-200"
                        placeholder="0">
                </div>
            </div>
        </div>

        <!-- Uwagi w przypadku upadków -->
        <div v-if="localPayload.amount > 0" class="p-3 bg-rose-50 border border-rose-100 rounded-lg">
            <p class="text-[10px] font-black uppercase text-rose-600 tracking-widest mb-2 flex items-center gap-1">
                Wymagany powód upadku
            </p>
            <textarea 
                v-model="localPayload.notes"
                :disabled="isReadOnly"
                @input="validateInput"
                class="w-full text-sm p-2 bg-white border border-rose-200 rounded focus:ring-rose-500 focus:outline-none focus:border-rose-500 placeholder:text-rose-300 disabled:bg-slate-100 disabled:text-slate-500"
                placeholder="Opisz przyczynę, np. nagły zgon, sektor 3..."></textarea>
        </div>
    </div>

    <!-- Status -->
    <div class="mt-4 p-4 rounded-xl text-center" 
         :class="isValid ? 'bg-emerald-50 border border-emerald-100' : 'bg-orange-50 border border-orange-100'"
         v-if="!isReadOnly">
        <p v-if="isValid && localPayload.amount > 0" class="text-[10px] font-black uppercase text-emerald-600 tracking-widest">
            Poprawnie uzupełniono raport upadku. Zapisz zadanie.
        </p>
        <p v-else-if="isValid && (!localPayload.amount || localPayload.amount === 0)" class="text-[10px] font-black uppercase text-slate-500 tracking-widest">
            Brak upadków w trakcie tego obchodu. Kliknij Zrób.
        </p>
        <p v-else class="text-[10px] font-black uppercase text-orange-600 tracking-widest">
            Dodaj powód upadku przed wysłaniem!
        </p>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, computed } from 'vue';

const props = defineProps({
  schema: { type: Object, default: () => ({}) },
  payload: { type: Object, default: null },
  isReadOnly: { type: Boolean, default: false }
});

const emit = defineEmits(['update:payload', 'validity']);

const localPayload = ref({
    category: 'fatteners',
    amount: 0,
    notes: ''
});

const initPayload = () => {
    if (props.payload && Object.keys(props.payload).length > 0) {
        localPayload.value = { ...props.payload };
    } else {
        localPayload.value = {
            category: 'fatteners',
            amount: 0,
            notes: ''
        };
    }
    checkValidity();
};

watch(() => props.payload, initPayload, { immediate: true });

const isValid = computed(() => {
    if (localPayload.value.amount > 0) {
        return localPayload.value.notes && localPayload.value.notes.trim() !== '';
    }
    return true; // if 0 or null, it's valid
});

const validateInput = () => {
    emitUpdate();
};

const emitUpdate = () => {
    emit('validity', isValid.value);
    emit('update:payload', { ...localPayload.value });
};

// Initial validation tick
watch(() => isValid.value, (val) => {
    emit('validity', val);
}, { immediate: true });

</script>
