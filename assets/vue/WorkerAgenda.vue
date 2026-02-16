<template>
  <div class="worker-agenda-view max-w-lg mx-auto pb-24">
    <!-- Points Summary (Mobile optimized) -->
    <div class="grid grid-cols-2 gap-4 mb-8 px-1">
      <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 flex flex-col items-center justify-center text-center">
        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 italic">Ten tydzień</span>
        <span class="text-3xl font-black text-slate-900 italic underline decoration-orange-600 decoration-2 underline-offset-4">{{ weekPoints }} <small class="text-xs font-black text-slate-400 uppercase tracking-tighter not-italic">pkt</small></span>
      </div>
      <div class="bg-slate-900 rounded-2xl p-6 shadow-sm flex flex-col items-center justify-center text-center text-white">
        <span class="text-[10px] font-black opacity-50 uppercase tracking-widest mb-2 italic">Twój wynik</span>
        <span class="text-3xl font-black italic">{{ totalPoints }} <small class="text-xs font-black opacity-50 uppercase tracking-tighter not-italic">pkt</small></span>
      </div>
    </div>

    <!-- Agenda Sections -->
    <div v-if="groupedTasks.length > 0" class="space-y-6">
      <div v-for="section in groupedTasks" :key="section.title" class="agenda-section">
        <!-- Section Header -->
        <div class="flex justify-between items-center px-4 mb-4">
          <h2 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] italic">{{ section.title }}</h2>
          <span class="bg-slate-100 text-slate-500 px-2.5 py-0.5 rounded text-[10px] font-black">{{ section.tasks.length }}</span>
        </div>

        <!-- Section List (Card) -->
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
          <div v-for="(task, index) in section.tasks" :key="task.id" 
               class="task-item border-b border-slate-50 last:border-0">
            <!-- Task Row -->
            <button @click="toggleExpand(task.id)"
                    class="w-full flex items-center justify-between p-5 text-left active:bg-slate-50 transition-colors">
              <div class="flex items-center gap-4 flex-1 min-w-0">
                <!-- Priority Indicator -->
                <div v-if="task.priority === 'URGENT'" class="w-1.5 h-6 bg-rose-500 rounded-full flex-shrink-0 animate-pulse"></div>
                <div v-else class="w-1.5 h-6 bg-slate-100 rounded-full flex-shrink-0"></div>
                
                <span class="font-bold text-slate-700 truncate" :class="{ 'opacity-50 line-through': task.loading }">{{ task.name }}</span>
              </div>

              <!-- Points Badge -->
              <div class="flex items-center gap-2">
                <span :class="[
                  'text-[10px] font-black px-2.5 py-1 rounded shadow-sm uppercase tracking-widest min-w-[3.5rem] text-center',
                  task.priority === 'URGENT' ? 'bg-red-50 text-red-600 border border-red-100' : 'bg-slate-50 text-slate-500 border border-slate-100'
                ]">
                  {{ task.points }} pkt
                </span>
                <span class="text-slate-300 transition-transform duration-300" :class="{ 'rotate-180': expandedId === task.id }">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7" />
                  </svg>
                </span>
              </div>
            </button>

            <!-- Expanded Details (Accordion) -->
            <div v-if="expandedId === task.id" class="details-pane bg-slate-50/50 p-6 pt-2 border-t border-slate-50 animate-in slide-in-from-top-4 duration-300">
              <div class="space-y-4">
                <div class="flex gap-4 text-[10px] font-bold uppercase tracking-widest text-slate-400">
                  <span :class="task.priority === 'URGENT' ? 'text-rose-500' : 'text-slate-500'">PRIORYTET: {{ task.priority }}</span>
                  <span>TYDZIEŃ: {{ task.weekNumber }}/{{ task.year }}</span>
                </div>
                
                <p class="text-sm text-slate-500 leading-relaxed font-medium">Brak dodatkowego opisu dla tego zadania. Zapytaj zootechnika o szczegóły jeśli masz wątpliwości.</p>

                <div class="flex gap-3">
                  <button @click="markAsDone(task)" :disabled="task.loading"
                          class="flex-1 bg-orange-600 active:bg-orange-700 text-white py-4 rounded-xl font-black text-xs uppercase tracking-widest shadow-sm transition-all disabled:opacity-50">
                    {{ task.loading ? 'Przetwarzanie...' : 'Wykonano ✓' }}
                  </button>
                  <button @click="expandedId = null" 
                          class="px-5 bg-white text-slate-400 border border-slate-200 rounded-xl font-black text-xs active:bg-slate-50 transition-colors uppercase">
                    Zamknij
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="text-center py-24">
      <h3 class="text-lg font-black text-slate-900 mb-2 italic underline decoration-orange-600 decoration-4 underline-offset-4 uppercase tracking-tighter">Wszystko gotowe</h3>
      <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-8 px-8">Obecnie nie masz żadnych zadań</p>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  initialTasks: { type: Array, default: () => [] },
  initialTotalPoints: { type: Number, default: 0 },
  initialWeekPoints: { type: Number, default: 0 },
  doneUrlPattern: { type: String, required: true },
  workerName: { type: String, required: true }
});

const tasks = ref(props.initialTasks.map(t => ({ ...t, loading: false })));
const totalPoints = ref(props.initialTotalPoints);
const weekPoints = ref(props.initialWeekPoints);
const expandedId = ref(null);

const weekdays = ['Poniedziałek', 'Wtorek', 'Środa', 'Czwartek', 'Piątek', 'Sobota', 'Niedziela'];

const groupedTasks = computed(() => {
  const groups = [];
  const now = new Date();
  const todayWeekday = now.getDay() === 0 ? 7 : now.getDay(); // 1-7

  // 1. Overdue
  const overdue = tasks.value.filter(t => t.isOverdue);
  if (overdue.length > 0) {
    groups.push({ title: 'Zaległe', tasks: sortTasks(overdue) });
  }

  // 2. Today
  const todayTasks = tasks.value.filter(t => !t.isOverdue && t.weekday === todayWeekday);
  if (todayTasks.length > 0) {
    groups.push({ title: 'Dzisiaj', tasks: sortTasks(todayTasks) });
  }

  // 3. Tomorrow
  const tomorrowWeekday = todayWeekday === 7 ? 1 : todayWeekday + 1;
  const tomorrowTasks = tasks.value.filter(t => !t.isOverdue && t.weekday === tomorrowWeekday);
  if (tomorrowTasks.length > 0) {
    groups.push({ title: 'Jutro', tasks: sortTasks(tomorrowTasks) });
  }

  // 4. Other days
  for (let i = 1; i <= 7; i++) {
    if (i === todayWeekday || i === tomorrowWeekday) continue;
    const dayTasks = tasks.value.filter(t => !t.isOverdue && t.weekday === i);
    if (dayTasks.length > 0) {
      groups.push({ title: weekdays[i-1], tasks: sortTasks(dayTasks) });
    }
  }

  return groups;
});

function sortTasks(taskList) {
  return [...taskList].sort((a, b) => {
    if (a.priority === 'URGENT' && b.priority !== 'URGENT') return -1;
    if (a.priority !== 'URGENT' && b.priority === 'URGENT') return 1;
    return a.id - b.id;
  });
}

function toggleExpand(id) {
  expandedId.value = expandedId.value === id ? null : id;
}

async function markAsDone(task) {
  task.loading = true;
  try {
    const url = props.doneUrlPattern.replace('TASK_ID', task.id);
    const response = await fetch(url, { method: 'POST' });
    
    if (response.ok) {
      const data = await response.json();
      if (data.success) {
        // Optimistic / Success update
        totalPoints.value += task.points;
        weekPoints.value += task.points;
        tasks.value = tasks.value.filter(t => t.id !== task.id);
        expandedId.value = null;
      }
    } else if (response.status === 409) {
      // Already done by someone else
      tasks.value = tasks.value.filter(t => t.id !== task.id);
      expandedId.value = null;
    } else {
      alert('Błąd: Nie udało się zakończyć zadania.');
    }
  } catch (e) {
    alert('Błąd połączenia. Spróbuj ponownie.');
  } finally {
    task.loading = false;
  }
}
</script>

<style scoped>
.agenda-section {
  animation: fadeIn 0.5s ease-out;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

.details-pane {
  background: linear-gradient(to bottom, rgba(248, 250, 252, 0.8), rgba(248, 250, 252, 1));
}
</style>
