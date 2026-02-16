<template>
  <div class="welfare-widget bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
    <!-- Header -->
    <div class="px-8 pt-8 pb-4">
      <h2 class="text-xl font-black text-slate-900 uppercase tracking-tighter italic underline decoration-orange-600 decoration-4 underline-offset-4 mb-2">
        Dobrostan i Stan Hodowli
      </h2>
      <p class="text-slate-500 font-medium text-xs italic">Zarządzanie stanem stada i historia zmian</p>
    </div>

    <div class="px-8 pb-8">
      <!-- Current Status Grid -->
      <div class="grid grid-cols-3 gap-6 mb-10">
        <div v-for="(amount, cat) in state" :key="cat" 
             class="bg-slate-50 rounded-xl p-6 text-center border border-slate-100 transition-colors hover:border-orange-200 group">
          <div class="text-[10px] font-black text-slate-400 uppercase mb-2 tracking-widest group-hover:text-orange-500 transition-colors">{{ categoryNames[cat] }}</div>
          <div class="text-4xl font-black text-slate-900 italic">{{ amount }}</div>
        </div>
      </div>

      <!-- Action Form -->
      <div class="bg-slate-50 rounded-2xl p-8 border border-slate-100 mb-10">
        <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-6 italic">Rejestruj nową zmianę</h3>
        
        <div class="space-y-4">
          <!-- Category Selector -->
          <div class="flex flex-wrap gap-2">
            <button v-for="cat in categories" :key="cat"
                    @click="form.category = cat"
                    :class="[
                      'px-4 py-2 rounded-lg text-[10px] font-black uppercase tracking-widest transition-all',
                      form.category === cat 
                        ? 'bg-slate-900 text-white shadow-sm' 
                        : 'bg-white text-slate-400 border border-slate-200'
                    ]">
              {{ categoryNames[cat] }}
            </button>
          </div>

          <!-- Reason Selector -->
          <div class="grid grid-cols-2 md:grid-cols-5 gap-2">
            <button v-for="reason in reasons" :key="reason"
                    @click="form.reason = reason"
                    :class="[
                      'py-2 px-2 rounded-lg text-[10px] font-black uppercase transition-all border',
                      form.reason === reason 
                        ? 'bg-orange-50 border-orange-200 text-orange-600' 
                        : 'bg-white border-slate-200 text-slate-400'
                    ]">
              {{ reasonNames[reason] }}
            </button>
          </div>

          <!-- Delta Selector -->
          <div class="flex items-center gap-6">
            <div class="flex items-center bg-white rounded-xl border border-slate-200 overflow-hidden w-fit">
              <button @click="form.delta--" class="px-6 py-3 bg-slate-50 hover:bg-slate-100 font-black text-slate-600 transition-colors">-</button>
              <input type="number" v-model.number="form.delta" 
                     class="w-20 text-center font-black text-2xl bg-transparent border-none focus:ring-0 text-slate-900">
              <button @click="form.delta++" class="px-6 py-3 bg-slate-50 hover:bg-slate-100 font-black text-slate-600 transition-colors">+</button>
            </div>
            <p class="text-[10px] font-black text-slate-400 uppercase italic">Sztuk do zmiany</p>
          </div>

          <button @click="submitChange" 
                  :disabled="loading || form.delta === 0"
                  class="w-full py-4 bg-orange-600 text-white font-black rounded-xl shadow-sm hover:bg-orange-700 transition-all uppercase tracking-widest text-xs disabled:opacity-50">
            Zapisz zmianę stanu
          </button>
        </div>
      </div>

      <!-- History -->
      <div>
        <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-6 italic">Ostatnie zmiany</h3>
        <div class="space-y-4">
          <div v-for="entry in history" :key="entry.id" 
               class="flex items-center justify-between p-4 bg-white rounded-xl border border-slate-100 shadow-sm group hover:border-orange-200 transition-colors">
            <div class="flex items-center gap-4">
              <div :class="[
                'w-12 h-12 rounded-lg flex items-center justify-center font-black italic text-xl',
                entry.delta > 0 ? 'bg-green-50 text-green-600' : 'bg-red-50 text-red-600'
              ]">
                {{ entry.delta > 0 ? '+' : '' }}{{ entry.delta }}
              </div>
              <div>
                <div class="font-black text-sm text-slate-900 italic tracking-tight">{{ categoryNames[entry.category] }}</div>
                <div class="text-[10px] font-black text-slate-400 uppercase tracking-tighter">{{ reasonNames[entry.reason] }} • <span class="italic font-normal lowercase tracking-normal">{{ entry.createdAt }}</span></div>
              </div>
            </div>
            <div class="text-right">
              <div class="text-[10px] font-black text-slate-400 uppercase italic">Przez: {{ entry.workerName }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const props = defineProps({
  dataUrl: { type: String, required: true },
  changeUrl: { type: String, required: true }
});

const state = ref({});
const history = ref([]);
const loading = ref(false);

const categories = ['SOWS', 'PIGLETS', 'FATTENERS'];
const categoryNames = {
  SOWS: 'Lochy',
  PIGLETS: 'Prosięta',
  FATTENERS: 'Tuczniki'
};

const reasons = ['BIRTH', 'DEATH', 'SALE', 'PURCHASE', 'ADJUSTMENT'];
const reasonNames = {
  BIRTH: 'Urodziny',
  DEATH: 'Upadek',
  SALE: 'Sprzedaż',
  PURCHASE: 'Zakup',
  ADJUSTMENT: 'Korekta'
};

const form = ref({
  category: 'PIGLETS',
  reason: 'DEATH',
  delta: 0,
  note: ''
});

async function fetchData() {
  try {
    const response = await fetch(props.dataUrl);
    const data = await response.json();
    state.value = data.state;
    history.value = data.history;
  } catch (e) {
    console.error('Failed to fetch welfare data');
  }
}

async function submitChange() {
  if (form.value.delta === 0) return;
  
  loading.value = true;
  try {
    const response = await fetch(props.changeUrl, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(form.value)
    });
    
    if (response.ok) {
      const data = await response.json();
      state.value = data.state;
      history.value = data.history;
      form.value.delta = 0;
      form.value.note = '';
    } else {
      const err = await response.json();
      alert(err.error || 'Błąd zapisu');
    }
  } catch (e) {
    alert('Błąd połączenia');
  } finally {
    loading.value = false;
  }
}

onMounted(fetchData);
</script>

<style scoped>
/* No extra styles needed, using tailwind */
</style>
