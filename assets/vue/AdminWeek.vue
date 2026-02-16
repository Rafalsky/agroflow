<template>
  <div class="week-view bg-white text-slate-900">
    <!-- Week Control Bar -->
    <div class="bg-slate-50 border border-slate-200 rounded-2xl p-8 mb-8 flex flex-col md:flex-row justify-between items-center gap-8">
      <div class="flex items-center gap-8">
        <div>
          <h2 class="text-3xl font-black italic text-slate-900 leading-none mb-2">Tydzień {{ week.weekNumber }} / {{ week.year }}</h2>
          <span :class="['inline-flex items-center px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-widest italic', week.status === 'OPEN' ? 'bg-orange-100 text-orange-700' : 'bg-slate-900 text-white']">
            {{ week.status === 'OPEN' ? 'TRYB OPERACYJNY (OPEN)' : 'TYDZIEŃ ZAMKNIĘTY' }}
          </span>
        </div>
        <div class="flex flex-col gap-1 border-l border-slate-200 pl-8">
          <div class="flex gap-4 text-[10px] font-black uppercase tracking-widest text-slate-400">
            <span>Zadania: <span class="text-slate-900 italic">{{ tasks.length }}</span></span>
            <span>Wykonane: <span class="text-emerald-600 italic">{{ tasks.filter(t => t.status === 'DONE').length }}</span></span>
            <span>Zaległe: <span class="text-red-500 italic">{{ pendingTasks.length }}</span></span>
          </div>
          <div class="text-[10px] font-black uppercase tracking-[0.2em] text-orange-600">Punkty Tygodnia: {{ teamTotal }} pkt</div>
        </div>
      </div>
      <div class="flex gap-3">
        <form v-if="week.status === 'OPEN'" method="post" :action="urls.generate">
          <button type="submit" class="bg-orange-600 text-white px-8 py-4 rounded-xl font-black uppercase tracking-widest text-[10px] shadow-sm hover:bg-orange-700 transition-colors">Generuj Tydzień</button>
        </form>
        <form v-if="week.status === 'OPEN'" method="post" :action="urls.close" onsubmit="return confirm('Czy na pewno chcesz zamknąć tydzień? Tej operacji nie można cofnąć.');">
          <button type="submit" class="bg-slate-900 text-white px-8 py-4 rounded-xl font-black uppercase tracking-widest text-[10px] hover:bg-slate-950 transition-colors">Zamknij Tydzień</button>
        </form>
      </div>
    </div>
    <!-- Pending Tasks Section -->
    <div v-if="pendingTasks.length > 0" class="mb-12 border border-slate-200 rounded-2xl p-8 bg-slate-50">
      <div class="flex items-center mb-6">
        <h2 class="text-xl font-black text-slate-900 uppercase tracking-tighter italic underline decoration-orange-600 decoration-4 underline-offset-4">Zaległe zadania</h2>
      </div>
      <div class="space-y-3">
        <div v-for="task in pendingTasks" :key="task.id" 
             class="flex justify-between items-center p-4 bg-white rounded-xl border border-slate-200 shadow-sm transition-all hover:border-orange-200">
          <div class="flex-1">
            <div class="flex items-center gap-2 mb-2">
              <span class="text-[10px] font-black text-slate-400 bg-slate-100 px-2 py-0.5 rounded uppercase tracking-widest">
                Tydz {{ task.weekNumber }}/{{ task.year }}
              </span>
              <span v-if="task.priority === 'URGENT'" class="text-[10px] font-black bg-red-600 text-white px-2 py-0.5 rounded uppercase tracking-widest">
                PILNE
              </span>
            </div>
            <p class="text-slate-900 font-bold italic">{{ task.name }}</p>
            <div v-if="task.worker_name" class="mt-2 inline-flex items-center gap-1 px-2 py-0.5 text-[10px] font-bold bg-blue-50 text-blue-700 rounded border border-blue-100 uppercase">
              👤 {{ task.worker_name }}
            </div>
          </div>
          <div class="flex items-center gap-4 ml-4">
            <select 
              v-model="task.worker_id" 
              @change="assignWorker(task)" 
              class="text-xs border border-slate-200 rounded-lg px-3 py-2 bg-white focus:border-orange-500 focus:ring-0 transition-all font-bold uppercase"
            >
              <option :value="null">Nie przypisano</option>
              <option v-for="worker in workers" :key="worker.id" :value="worker.id">{{ worker.shortName }}</option>
            </select>
            <button 
              @click="toggleTask(task)" 
              :disabled="task.processing"
              class="px-5 py-2 text-xs font-black rounded-lg transition-all uppercase tracking-widest"
              :class="task.status === 'DONE' ? 'bg-orange-600 text-white hover:bg-orange-700' : 'bg-white border border-slate-200 text-slate-600 hover:border-orange-600 hover:text-orange-600'"
            >
              {{ task.processing ? '...' : (task.status === 'DONE' ? 'Gotowe ✓' : 'Wykonaj') }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Scoring Summary -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-12">
      <div class="bg-orange-600 rounded-2xl p-8 shadow-sm text-white">
        <div class="text-[10px] font-black uppercase tracking-[0.2em] opacity-70 mb-2 italic">Punkty Zespołu</div>
        <div class="text-5xl font-black italic">{{ teamTotal || 0 }}</div>
      </div>
      <div class="md:col-span-3 bg-white border border-slate-200 rounded-2xl p-8 shadow-sm">
        <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-6 italic">
          Najlepsi w tym tygodniu
        </h3>
        <div class="flex flex-wrap gap-4">
          <div v-for="(entry, idx) in weekLeaderboard.slice(0, 5)" :key="entry.worker_id" 
               class="flex items-center gap-4 bg-slate-50 px-5 py-3 rounded-xl border border-slate-100 group hover:border-orange-200 transition-colors">
            <span class="text-xl font-black italic text-slate-300 group-hover:text-orange-500 transition-colors">#{{ idx + 1 }}</span>
            <div>
              <div class="font-black text-slate-900 text-sm italic">{{ entry.name }}</div>
              <div class="text-[10px] font-black text-orange-600 uppercase">{{ entry.total_amount }} pkt</div>
            </div>
          </div>
          <div v-if="weekLeaderboard.length === 0" class="text-slate-400 text-sm font-bold italic">
            Brak punktów w tym tygodniu
          </div>
        </div>
      </div>
    </div>

    <!-- Week Grid -->
    <div class="grid grid-cols-1 md:grid-cols-7 gap-4">
      <div v-for="day in 7" :key="day" 
           class="bg-white border border-slate-200 rounded-2xl p-4 min-h-[350px] shadow-sm">
        <div class="flex justify-between items-center pb-3 mb-6 border-b border-slate-100 italic">
          <h3 class="font-black text-xs uppercase tracking-widest text-slate-400">
            {{ weekDays[day - 1] }}
          </h3>
          <button @click="$emit('add-task', day)" class="text-slate-300 hover:text-orange-600 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd" />
            </svg>
          </button>
        </div>
        <div class="space-y-3">
          <div 
            v-for="task in getTasksForDay(day)" 
            :key="task.id" 
            @click="$emit('open-task', task)"
            class="p-4 border rounded-xl text-sm flex flex-col gap-3 transition-colors shadow-sm mb-4 cursor-pointer group hover:border-orange-300"
            :class="getTaskCardClass(task)"
          >
            <!-- Task Header -->
            <div class="flex justify-between items-start gap-3">
              <span class="font-bold text-slate-900 leading-tight italic" :class="{'line-through text-slate-300': task.status === 'DONE'}">
                {{ task.name }}
              </span>
              <span class="text-[10px] font-black bg-slate-100 text-slate-600 px-2 py-0.5 rounded shadow-sm whitespace-nowrap uppercase">
                {{ task.points }} pkt
              </span>
            </div>

            <!-- Priority Badge -->
            <div v-if="task.priority === 'URGENT'" class="text-[10px] font-black text-red-600 bg-red-50 px-2 py-0.5 rounded border border-red-100 w-fit uppercase tracking-tighter">
              Zadanie PILNE
            </div>
            
            <!-- Worker Badge -->
            <div v-if="task.worker_name" class="inline-flex items-center gap-1 text-[10px] font-black bg-blue-50 text-blue-700 px-2 py-1 rounded border border-blue-100 w-fit uppercase">
              👤 {{ task.worker_name }}
            </div>

            <!-- Worker Assignment Dropdown -->
            <select 
              :value="task.worker_id" 
              @change.stop="$emit('assign-worker', task, $event.target.value)" 
              class="text-[10px] font-black border border-slate-200 rounded-lg px-2 py-2 bg-white focus:border-orange-500 focus:ring-0 transition-all uppercase"
            >
              <option :value="null">Nie przypisano</option>
              <option v-for="worker in workers" :key="worker.id" :value="worker.id">{{ worker.shortName }}</option>
            </select>

            <!-- Action Button -->
            <button 
              @click.stop="$emit('toggle-task', task)" 
              :disabled="task.processing"
              class="w-full text-center py-2 rounded-lg text-xs font-black transition-all uppercase tracking-widest mt-1"
              :class="getTaskButtonClass(task)"
            >
              {{ task.processing ? '...' : (task.status === 'DONE' ? 'Cofnij ✓' : 'Zrób') }}
            </button>
          </div>
          
          <!-- Empty State -->
          <div v-if="getTasksForDay(day).length === 0" class="text-center py-12">
            <p class="text-[10px] font-black text-slate-300 uppercase italic tracking-widest">Brak zadań</p>
          </div>
        </div>
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
  teamTotal: Number
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
