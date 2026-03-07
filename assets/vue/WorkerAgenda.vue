<template>
  <div class="worker-agenda-view max-w-lg mx-auto pb-24">
    <!-- Points Summary (Mobile optimized) -->
    <div class="grid grid-cols-2 gap-4 mb-8 px-4">
      <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100 flex flex-col items-center justify-center text-center">
        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1 italic">Ten tydzień</span>
        <span class="text-3xl font-black text-slate-900 italic">{{ weekPoints }} <small class="text-xs font-black text-slate-400 uppercase tracking-tighter not-italic">pkt</small></span>
      </div>
      <div class="bg-orange-600 rounded-3xl p-6 shadow-sm flex flex-col items-center justify-center text-center text-white">
        <span class="text-[10px] font-black text-orange-200 uppercase tracking-widest mb-1 italic">Twój wynik</span>
        <span class="text-3xl font-black italic">{{ totalPoints }} <small class="text-xs font-black text-orange-200 uppercase tracking-tighter not-italic">pkt</small></span>
      </div>
    </div>

    <!-- Agenda Sections -->
    <div v-if="groupedTasks.length > 0" class="space-y-8 px-4">
      <div v-for="section in groupedTasks" :key="section.title" class="agenda-section">
        <!-- Section Header -->
        <div class="flex items-center gap-3 mb-4">
          <h2 class="text-xs font-black uppercase tracking-[0.2em] italic" :class="section.isDoneList ? 'text-emerald-600' : 'text-slate-400'">{{ section.title }}</h2>
          <div class="flex-1 h-px bg-slate-200" v-if="!section.isDoneList"></div>
          <div class="flex-1 h-px bg-emerald-200" v-else></div>
          <span :class="['px-2 py-0.5 rounded text-[10px] font-black', section.isDoneList ? 'bg-emerald-100 text-emerald-600' : 'bg-slate-100 text-slate-500']">{{ section.tasks.length }}</span>
        </div>

        <!-- Section List (Cards inside section) -->
        <div class="space-y-3">
          <div v-for="task in section.tasks" :key="task.id">
            <!-- Task Row (Large Touch Target) -->
            <button @click="handleTaskClick(task)"
                    :disabled="task.loading"
                    class="w-full text-left bg-white rounded-2xl shadow-sm border border-slate-100 p-4 min-h-[5rem] flex items-center justify-between active:scale-[0.98] transition-all disabled:opacity-50 touch-manipulation">
              
              <div class="flex items-center gap-4 flex-1 min-w-0 pr-2">
                <!-- Checkbox or Priority Indicator -->
                <div v-if="task.status === 'DONE'" class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center flex-shrink-0">
                  <span class="text-emerald-600 font-bold text-lg">✓</span>
                </div>
                <div v-else-if="task.priority === 'URGENT'" class="w-2 h-10 bg-rose-500 rounded-full flex-shrink-0 animate-pulse"></div>
                <div v-else class="w-2 h-10 bg-slate-200 rounded-full flex-shrink-0"></div>
                
                <div class="flex flex-col">
                  <span class="font-bold text-base leading-tight" :class="task.status === 'DONE' ? 'text-slate-400 line-through' : 'text-slate-700'">{{ task.name }}</span>
                  <span v-if="task.instruction && task.status !== 'DONE'" class="text-[10px] font-medium text-slate-400 truncate mt-1">{{ task.instruction }}</span>
                </div>
              </div>

              <!-- Points Badge or Fast Action -->
              <div class="flex items-center gap-3 flex-shrink-0 pl-2 border-l border-slate-100">
                <span v-if="task.status !== 'DONE' && !task.widget_type" class="text-[10px] font-black px-2.5 py-1.5 rounded-lg bg-orange-50 text-orange-600 border border-orange-100">
                  {{ task.points }} pkt
                </span>
                <span v-else-if="task.status !== 'DONE' && task.widget_type" class="text-slate-400 bg-slate-50 p-2 rounded-lg">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                  </svg>
                </span>
                <span v-else-if="task.status === 'DONE' && task.widget_type" class="text-emerald-400">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                </span>
              </div>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="text-center py-24 px-4">
      <div class="w-24 h-24 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-6">
        <span class="text-4xl">🎉</span>
      </div>
      <h3 class="text-xl font-black text-slate-900 mb-2 italic tracking-tight">Wszystko zrobione!</h3>
      <p class="text-xs font-black text-slate-400 uppercase tracking-widest mt-2">Dobra robota na dzisiaj.</p>
    </div>

    <!-- Context Drawer -->
    <ContextDrawer 
      :is-open="drawerOpen" 
      :entity="selectedEntity" 
      @close="closeDrawer"
      @toggle-task="handleToggleTask"
    />
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import ContextDrawer from './ContextDrawer.vue';

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

const drawerOpen = ref(false);
const selectedEntity = ref(null);

const weekdays = ['Poniedziałek', 'Wtorek', 'Środa', 'Czwartek', 'Piątek', 'Sobota', 'Niedziela'];

const groupedTasks = computed(() => {
  const groups = [];
  const now = new Date();
  const todayWeekday = now.getDay() === 0 ? 7 : now.getDay(); // 1-7

  // 1. Overdue
  const overdue = tasks.value.filter(t => t.isOverdue && t.status !== 'DONE');
  if (overdue.length > 0) {
    groups.push({ title: 'Zaległe', tasks: sortTasks(overdue), isDoneList: false });
  }

  // 2. Today
  const todayTasks = tasks.value.filter(t => !t.isOverdue && t.weekday === todayWeekday && t.status !== 'DONE');
  if (todayTasks.length > 0) {
    groups.push({ title: 'Dzisiaj', tasks: sortTasks(todayTasks), isDoneList: false });
  }

  // 3. Tomorrow
  const tomorrowWeekday = todayWeekday === 7 ? 1 : todayWeekday + 1;
  const tomorrowTasks = tasks.value.filter(t => !t.isOverdue && t.weekday === tomorrowWeekday && t.status !== 'DONE');
  if (tomorrowTasks.length > 0) {
    groups.push({ title: 'Jutro', tasks: sortTasks(tomorrowTasks), isDoneList: false });
  }

  // 4. Other days
  for (let i = 1; i <= 7; i++) {
    if (i === todayWeekday || i === tomorrowWeekday) continue;
    const dayTasks = tasks.value.filter(t => !t.isOverdue && t.weekday === i && t.status !== 'DONE');
    if (dayTasks.length > 0) {
      groups.push({ title: weekdays[i-1], tasks: sortTasks(dayTasks), isDoneList: false });
    }
  }

  // 5. Done Today
  const doneToday = tasks.value.filter(t => t.status === 'DONE');
  if (doneToday.length > 0) {
    groups.push({ title: 'Ukończone Dzisiaj', tasks: doneToday.sort((a,b) => b.id - a.id), isDoneList: true });
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

function handleTaskClick(task) {
    if (task.widget_type || task.status === 'DONE') {
        openDrawer(task);
    } else {
        // Simple binary fast action
        executeTaskToggle(task, null);
    }
}

function openDrawer(task) {
    selectedEntity.value = { type: 'task', data: task };
    drawerOpen.value = true;
}

function closeDrawer() {
    drawerOpen.value = false;
    setTimeout(() => { selectedEntity.value = null; }, 300);
}

function handleToggleTask(taskData, payload) {
    // ContextDrawer emitted toggle-task
    const task = tasks.value.find(t => t.id === taskData.id);
    if (task) {
        closeDrawer();
        if (task.status !== 'DONE') {
            executeTaskToggle(task, payload);
        }
    }
}

async function executeTaskToggle(task, payload) {
  if (task.status === 'DONE') return; // Handled by backend undo normally, but worker doesn't undo for now, drawer is read-only
  
  task.loading = true;
  try {
    const url = props.doneUrlPattern.replace('TASK_ID', task.id);
    const response = await fetch(url, { 
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: payload ? JSON.stringify(payload) : null
    });
    
    if (response.ok) {
      const data = await response.json();
      if (data.success) {
        totalPoints.value += task.points;
        weekPoints.value += task.points;
        task.status = 'DONE';
        task.execution_payload = payload;
      }
    } else if (response.status === 409) {
      // Already done by someone else
      tasks.value = tasks.value.filter(t => t.id !== task.id);
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
  animation: fadeIn 0.4s ease-out;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
