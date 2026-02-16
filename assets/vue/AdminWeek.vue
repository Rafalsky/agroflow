<template>
  <div class="week-view bg-white text-slate-900">
    <!-- Compact Operational Control Bar -->
    <div class="bg-white border border-slate-200 rounded-xl p-4 mb-6 flex flex-col md:flex-row justify-between items-center gap-4 shadow-sm">
      <div class="flex items-center gap-6 divide-x divide-slate-100">
        <div class="flex items-center gap-3">
          <h2 class="text-lg font-black italic text-slate-900 leading-none">TYDZIEŃ {{ week.weekNumber }} / {{ week.year }}</h2>
          <span :class="['inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-black uppercase tracking-widest', week.status === 'OPEN' ? 'bg-orange-600 text-white' : 'bg-slate-900 text-white']">
            {{ week.status }}
          </span>
        </div>
        
        <div class="flex gap-4 pl-6 text-[10px] font-black uppercase tracking-widest text-slate-400">
          <span>Zadania: <span class="text-slate-900">{{ tasks.length }}</span></span>
          <span>Wykonane: <span class="text-emerald-600">{{ tasks.filter(t => t.status === 'DONE').length }}</span></span>
          <span>Zaległe: <span class="text-red-500">{{ pendingTasks.length }}</span></span>
          <span class="text-orange-600 ml-2">Punkty: {{ teamTotal }}</span>
        </div>
      </div>

      <div class="flex gap-2">
        <form v-if="week.status === 'OPEN'" method="post" :action="urls.generate">
          <button type="submit" class="bg-orange-600 text-white px-4 py-2.5 rounded-lg font-black uppercase tracking-widest text-[9px] hover:bg-orange-700 transition-colors shadow-sm">Generuj</button>
        </form>
        <form v-if="week.status === 'OPEN'" method="post" :action="urls.close" onsubmit="return confirm('Czy na pewno chcesz zamknąć tydzień?');">
          <button type="submit" class="bg-slate-900 text-white px-4 py-2.5 rounded-lg font-black uppercase tracking-widest text-[9px] hover:bg-slate-950 transition-colors">Zamknij</button>
        </form>
      </div>
    </div>
    <!-- Compact Pending Tasks List -->
    <div v-if="pendingTasks.length > 0" class="mb-8 bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
      <div class="bg-slate-50 px-4 py-2 border-b border-slate-200 flex justify-between items-center">
        <h2 class="text-[10px] font-black text-slate-900 uppercase tracking-widest italic">Zaległe Zadania ({{ pendingTasks.length }})</h2>
      </div>
      <div class="divide-y divide-slate-100">
        <div v-for="task in pendingTasks" :key="task.id" 
             class="flex items-center gap-4 px-4 py-2.5 hover:bg-slate-50/50 transition-colors">
          <div class="flex flex-col min-w-[120px]">
            <span class="text-[9px] font-black text-slate-400 uppercase tracking-tighter">TYDZ {{ task.weekNumber }}/{{ task.year }}</span>
            <span v-if="task.priority === 'URGENT'" class="text-[8px] font-black text-red-600 uppercase">PR_PILNY</span>
          </div>
          
          <div class="flex-1 font-bold text-slate-900 text-xs italic">{{ task.name }}</div>
          
          <div class="flex items-center gap-6">
            <div v-if="task.worker_name" class="text-[10px] font-black text-blue-600 uppercase">👤 {{ task.worker_name }}</div>
            <select 
              :value="task.worker_id" 
              @change.stop="$emit('assign-worker', task, $event.target.value)" 
              class="text-[9px] font-black border border-slate-200 rounded-md px-2 py-1 bg-white focus:border-orange-500 focus:ring-0 uppercase"
            >
              <option :value="null">Brak</option>
              <option v-for="worker in workers" :key="worker.id" :value="worker.id">{{ worker.shortName }}</option>
            </select>
            <button 
                @click.stop="$emit('toggle-task', task)" 
                :disabled="task.processing"
                class="px-4 py-1.5 text-[9px] font-black rounded-md transition-all uppercase tracking-widest"
                :class="task.status === 'DONE' ? 'bg-orange-600 text-white' : 'bg-slate-50 text-slate-400 border border-slate-200'"
            >
              {{ task.processing ? '...' : (task.status === 'DONE' ? 'Cofnij ✓' : 'Zrób') }}
            </button>
          </div>
        </div>
      </div>
    </div>


    <!-- High-Density Week Grid -->
    <div class="grid grid-cols-1 md:grid-cols-7 gap-3 mb-12">
      <div v-for="day in 7" :key="day" 
           class="bg-white border border-slate-200 rounded-xl flex flex-col min-h-[400px] shadow-sm overflow-hidden">
        
        <!-- Day Header -->
        <div class="px-3 py-2 bg-slate-50 border-b border-slate-100 flex justify-between items-center bg-white">
          <h3 class="font-black text-[9px] uppercase tracking-widest text-slate-400">
            {{ weekDays[day - 1] }} ({{ getTasksForDay(day).length }})
          </h3>
          <button @click="$emit('add-task', day)" class="text-slate-300 hover:text-orange-600 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd" />
            </svg>
          </button>
        </div>

        <div class="p-2 space-y-2 flex-1">
          <div 
            v-for="task in getTasksForDay(day)" 
            :key="task.id" 
            @click="$emit('open-task', task)"
            class="p-2 border rounded-lg text-xs flex flex-col gap-2 transition-colors shadow-sm mb-1 cursor-pointer group hover:border-orange-200 relative"
            :class="getTaskCardClass(task)"
          >
            <!-- Compact Task Header -->
            <div class="flex justify-between items-start gap-2">
              <span class="font-bold text-slate-900 leading-tight italic line-clamp-2" :class="{'line-through text-slate-300': task.status === 'DONE'}">
                {{ task.name }}
              </span>
              <span class="text-[8px] font-black bg-slate-50 text-slate-600 px-1 py-0.5 rounded shadow-sm whitespace-nowrap uppercase">
                {{ task.points }}
              </span>
            </div>

            <div class="flex items-center justify-between mt-1">
               <div class="flex gap-1 items-center">
                  <div v-if="task.priority === 'URGENT'" class="w-1.5 h-1.5 rounded-full bg-red-600 animate-pulse"></div>
                  <div v-if="task.worker_name" class="text-[8px] font-black text-blue-600 uppercase">👤 {{ task.worker_name }}</div>
               </div>
               
               <button 
                @click.stop="$emit('toggle-task', task)" 
                :disabled="task.processing"
                class="px-2 py-1 rounded text-[8px] font-black transition-all uppercase tracking-widest"
                :class="task.status === 'DONE' ? 'bg-orange-600 text-white' : 'bg-slate-50 text-slate-400 border border-slate-100'"
              >
                {{ task.processing ? '...' : (task.status === 'DONE' ? '✓' : 'Zrób') }}
              </button>
            </div>
          </div>
          
          <!-- Subtle Empty State -->
          <div v-if="getTasksForDay(day).length === 0" class="h-full flex items-center justify-center opacity-20">
            <span class="text-xs font-black">—</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Analytics Strip -->
    <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm flex flex-col md:flex-row items-center justify-around gap-8">
      <div class="text-center">
        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 italic">Punkty Tygodnia</p>
        <div class="text-3xl font-black italic text-slate-900 leading-none">{{ teamTotal || 0 }}</div>
      </div>

      <div class="w-px h-12 bg-slate-100 hidden md:block"></div>

      <div class="text-center w-full max-w-[200px]">
        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 italic">Realizacja Planu</p>
        <div class="flex items-center gap-3">
          <div class="flex-1 bg-slate-100 h-1.5 rounded-full overflow-hidden">
            <div class="bg-orange-600 h-full transition-all duration-1000" :style="{ width: completionRate + '%' }"></div>
          </div>
          <span class="text-lg font-black italic text-slate-900">{{ completionRate }}%</span>
        </div>
      </div>

      <div class="w-px h-12 bg-slate-100 hidden md:block"></div>

      <div class="text-center">
        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 italic">Lider Tygodnia</p>
        <div v-if="topWorker" class="flex items-center gap-3">
          <div class="w-8 h-8 bg-slate-900 rounded-full flex items-center justify-center text-xs font-black text-white italic">{{ topWorker.shortName }}</div>
          <div class="text-left">
            <div class="text-sm font-black italic text-slate-900 leading-none">{{ topWorker.name }}</div>
            <div class="text-[9px] font-black text-orange-600 uppercase tracking-tighter">{{ topWorker.total_amount }} pkt</div>
          </div>
        </div>
        <div v-else class="text-xs font-bold italic text-slate-300">0 pkt</div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, defineProps, defineEmits } from 'vue';

const emit = defineEmits(['open-task', 'add-task', 'toggle-task', 'assign-worker']);

const props = defineProps({
  week: Object,
  tasks: Array,
  pendingTasks: Array,
  workers: Array,
  urls: { type: Object, default: () => ({}) },
  weekLeaderboard: Array,
  teamTotal: Number,
  completionRate: Number,
  topWorker: Object
});

const weekDays = ['Poniedziałek', 'Wtorek', 'Środa', 'Czwartek', 'Piątek', 'Sobota', 'Niedziela'];

const getTasksForDay = (day) => {
  return props.tasks.filter(t => t.weekday === day);
};

const getTaskCardClass = (task) => {
  if (task.status === 'DONE') {
    return 'bg-slate-50 border-slate-100 opacity-60';
  } else if (task.priority === 'URGENT') {
    return 'bg-white border-red-200 shadow-sm';
  } else {
    return 'bg-white border-slate-200 shadow-sm';
  }
};

const getTaskButtonClass = (task) => {
  if (task.status === 'DONE') {
    return 'bg-slate-100 text-slate-400 hover:bg-slate-200';
  } else {
    return 'bg-orange-600 text-white hover:bg-orange-700';
  }
};
</script>

<style scoped>
.week-view {
  animation: slideUp 0.6s cubic-bezier(0.23, 1, 0.32, 1);
}
@keyframes slideUp {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
