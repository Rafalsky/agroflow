<template>
  <div v-if="isOpen" class="fixed inset-0 z-[100] overflow-hidden">
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" @click="$emit('close')"></div>
    
    <div class="absolute inset-y-0 right-0 max-w-full flex">
      <div class="w-screen max-w-xl bg-white shadow-2xl flex flex-col transform transition-transform duration-300">
        <!-- Drawer Header -->
        <div class="px-8 py-6 border-b border-slate-200 flex justify-between items-center bg-slate-50">
          <h2 class="text-xl font-black italic text-slate-900 uppercase tracking-tight">{{ title }}</h2>
          <button @click="$emit('close')" class="p-2 border border-slate-200 rounded-lg hover:bg-white text-slate-400 hover:text-slate-900 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Drawer Body -->
        <div class="flex-1 overflow-y-auto p-8">
          <div v-if="entity && entity.type === 'task'" class="space-y-8">
            <!-- Task Details -->
            <div>
              <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4 italic">Szczegóły Zadania</p>
              <h3 class="text-3xl font-black italic text-slate-900 mb-6">{{ entity.data.name }}</h3>
              
              <div class="grid grid-cols-2 gap-4">
                <div class="bg-slate-50 p-4 rounded-xl border border-slate-100">
                  <span class="block text-[10px] font-black text-slate-400 uppercase mb-1">Status</span>
                  <span :class="['font-black italic underline decoration-2 underline-offset-4', entity.data.status === 'DONE' ? 'text-emerald-600 decoration-emerald-600' : 'text-orange-600 decoration-orange-600']">
                    {{ entity.data.status === 'DONE' ? 'Wykonano' : 'Oczekuje' }}
                  </span>
                </div>
                <div class="bg-slate-50 p-4 rounded-xl border border-slate-100">
                  <span class="block text-[10px] font-black text-slate-400 uppercase mb-1">Punkty</span>
                  <span class="text-xl font-black italic text-slate-900">{{ entity.data.points }} pkt</span>
                </div>
              </div>
            </div>

            <!-- Task Audit -->
            <div>
              <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4 italic">Historia Operacji (Audit)</p>
              <div class="space-y-3">
                <!-- Mock audit for now or we could pass it if we hit an endpoint -->
                <div v-if="entity.data.doneAt" class="flex gap-4 p-4 bg-slate-50/50 rounded-lg border border-slate-100 italic">
                  <span class="text-emerald-600 font-bold">✓</span>
                  <div class="text-sm">
                    <span class="font-bold text-slate-900">Zadanie ukończone</span>
                    <p class="text-slate-400 text-xs mt-1">{{ entity.data.doneAt }}</p>
                  </div>
                </div>
                <div class="flex gap-4 p-4 bg-slate-50/50 rounded-lg border border-slate-100 italic">
                  <span class="text-slate-400 font-bold">●</span>
                  <div class="text-sm">
                    <span class="font-bold text-slate-900">System wygenerował zadanie</span>
                    <p class="text-slate-400 text-xs mt-1">Przygotowano na Tydzień {{ entity.data.weekNumber }}</p>
                  </div>
                </div>
              </div>
            </div>

            <div class="pt-8 border-t border-slate-100 flex gap-4">
              <button @click="$emit('toggle-task', entity.data)" v-if="entity.data.status === 'DONE'" class="flex-1 bg-white border border-slate-200 text-slate-400 py-4 rounded-xl font-black uppercase tracking-widest text-[10px] hover:text-slate-900 transition-colors">Cofnij Wykonanie</button>
              <button @click="$emit('toggle-task', entity.data)" v-else class="flex-1 bg-orange-600 text-white py-4 rounded-xl font-black uppercase tracking-widest text-[10px] shadow-sm hover:bg-orange-700 transition-colors">Oznacz jako Wykonane</button>
              <button @click="$emit('delete', entity.data)" class="px-6 py-4 border border-rose-100 text-rose-500 rounded-xl hover:bg-rose-50 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
              </button>
            </div>
          </div>

          <div v-else-if="entity && entity.type === 'template'" class="space-y-8">
            <h3 class="text-2xl font-black italic text-slate-900">{{ entity.data ? 'Edytuj Szablon' : 'Nowy Szablon' }}</h3>
            <!-- Form Mockup -->
            <div class="space-y-6">
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 italic">Nazwa Zadania</label>
                    <input type="text" :value="entity.data?.name" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-4 focus:outline-none focus:border-orange-600 font-bold italic">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 italic">Punkty</label>
                        <input type="number" :value="entity.data?.points" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-4 focus:outline-none focus:border-orange-600 font-bold italic">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 italic">Dzień Tygodnia</label>
                        <select :value="entity.data?.weekday" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-4 focus:outline-none focus:border-orange-600 font-bold italic">
                            <option v-for="n in 7" :key="n" :value="n">Dzień {{ n }}</option>
                        </select>
                    </div>
                </div>
                <!-- Other fields... -->
                <button class="w-full bg-slate-900 text-white py-5 rounded-xl font-black uppercase tracking-widest text-xs shadow-lg shadow-slate-900/10">Zapisz Szablon</button>
            </div>
          </div>

          <div v-else-if="entity && entity.type === 'worker'" class="space-y-8">
            <h3 class="text-2xl font-black italic text-slate-900">{{ entity.data ? 'Edytuj Pracownika' : 'Nowy Pracownik' }}</h3>
            <div class="space-y-6">
                <div>
                   <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 italic">Nickname</label>
                   <input type="text" :value="entity.data?.name" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-4 focus:outline-none focus:border-orange-600 font-bold italic">
                </div>
                <div v-if="entity.data" class="bg-orange-50 p-6 rounded-2xl border border-orange-100">
                    <p class="text-[10px] font-black text-orange-600 uppercase tracking-widest mb-2 italic">Magic Token (Login)</p>
                    <code class="text-sm font-mono text-orange-900 break-all select-all block bg-white/50 p-3 rounded-lg">{{ entity.data.accessToken }}</code>
                    <p class="text-[9px] font-black text-orange-400 uppercase tracking-widest mt-4">Nigdy nie udostępniaj tokenu osobom trzecim.</p>
                </div>
                <button class="w-full bg-slate-900 text-white py-5 rounded-xl font-black uppercase tracking-widest text-xs shadow-lg shadow-slate-900/10">Zapisz Zmiany</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  entity: { type: Object, default: null },
  isOpen: { type: Boolean, default: false },
  workers: { type: Array, default: () => [] }
});

const emit = defineEmits(['close', 'toggle-task', 'assign-worker', 'delete']);

const title = computed(() => {
  if (!props.entity) return '';
  switch (props.entity.type) {
    case 'task': return 'Detale Zadania';
    case 'template': return props.entity.data ? 'Zarządzanie Szablonem' : 'Nowy Szablon';
    case 'worker': return props.entity.data ? 'Zarządzanie Pracownikiem' : 'Nowy Pracownik';
    default: return 'Szczegóły';
  }
});
</script>
