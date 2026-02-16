<template>
  <div class="worker-tasks-view">
    <!-- Pending Tasks from Previous Weeks -->
    <div v-if="pendingTasks.length > 0" class="mb-12">
      <div class="bg-slate-50 border border-slate-200 rounded-2xl p-8">
        <h2 class="text-xl font-black text-slate-900 mb-6 uppercase tracking-tighter italic underline decoration-orange-600 decoration-4 underline-offset-4"> Zaległe Zadania</h2>
        <p class="text-[10px] font-black text-slate-400 mb-8 uppercase tracking-widest italic">Zadania z poprzednich tygodni do ukończenia</p>
        
        <div class="space-y-3">
          <div v-for="task in pendingTasks" :key="task.id"
               class="bg-white rounded-xl p-6 border border-slate-200 shadow-sm transition-colors hover:border-orange-200">
            <div class="flex justify-between items-start mb-4 gap-6">
              <div class="flex-1">
                <h3 class="font-black text-lg text-slate-900 italic tracking-tight mb-2">{{ task.name }}</h3>
                <div class="flex flex-wrap gap-2 text-[10px] font-black uppercase tracking-tighter text-slate-400">
                  <span>TYDZIEŃ {{ task.weekNumber }}/{{ task.year }}</span>
                  <span>•</span>
                  <span>DZIEŃ {{ task.weekday }}</span>
                </div>
              </div>
              <div class="flex-shrink-0">
                <div class="h-12 w-12 bg-slate-900 rounded flex items-center justify-center shadow-sm">
                  <span class="text-lg font-black text-white italic">{{ task.points }}</span>
                </div>
              </div>
            </div>
            
            <button @click="markDone(task.id)"
                    class="w-full px-6 py-3 bg-orange-600 text-white font-black rounded-xl hover:bg-orange-700 transition-colors uppercase tracking-widest text-[10px] shadow-sm">
              Zakończ zadanie
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Points Summary Bar -->
    <div class="grid grid-cols-2 gap-6 mb-12">
      <div class="bg-white rounded-2xl p-8 border border-slate-200 shadow-sm flex flex-col items-center justify-center text-center">
        <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 italic">Punkty w tym tygodniu</div>
        <div class="text-4xl font-black text-slate-900 italic underline decoration-orange-600 decoration-4 underline-offset-4">{{ weekPoints }}</div>
      </div>
      <div class="bg-slate-900 rounded-2xl p-8 shadow-sm flex flex-col items-center justify-center text-center text-white">
        <div class="text-[10px] font-black opacity-50 uppercase tracking-widest mb-2 italic">Suma wszystkich punktów</div>
        <div class="text-4xl font-black italic">{{ totalPoints }}</div>
      </div>
    </div>

    <!-- Current Tasks -->
    <div v-if="tasks.length > 0">
      <h2 class="text-xl font-black text-slate-900 mb-8 uppercase tracking-tighter italic underline decoration-orange-600 decoration-4 underline-offset-4 w-fit">Twoje Zadania</h2>
      
      <div class="space-y-4">
        <div v-for="task in tasks" :key="task.id"
             :class="[
               'rounded-xl p-6 border transition-colors shadow-sm',
               task.status === 'DONE' 
                 ? 'bg-slate-50 border-slate-100 opacity-60' 
                 : 'bg-white border-slate-200 hover:border-orange-200'
             ]">
          <div class="flex justify-between items-start mb-6 gap-6">
            <div class="flex-1">
              <h3 class="font-black text-xl italic tracking-tight" :class="task.status === 'DONE' ? 'text-slate-400 line-through' : 'text-slate-900'">
                {{ task.name }}
              </h3>
              <div class="flex flex-wrap gap-2 text-[10px] font-black uppercase tracking-tighter text-slate-400 mt-2">
                <span>TYDZIEŃ {{ task.weekNumber }}/{{ task.year }}</span>
                <span>•</span>
                <span>DZIEŃ {{ task.weekday }}</span>
                <span v-if="task.priority === 'URGENT'" class="ml-2 font-black text-red-600 decoration-red-600 underline">
                  PILNE
                </span>
              </div>
            </div>
            <div class="flex-shrink-0">
              <div :class="[
                'h-14 w-14 rounded flex items-center justify-center shadow-sm',
                task.status === 'DONE' 
                  ? 'bg-slate-100' 
                  : 'bg-slate-900 border border-slate-800 shadow-orange-900/10 shadow-lg'
              ]">
                <span class="text-2xl font-black italic" :class="task.status === 'DONE' ? 'text-slate-300' : 'text-white'">{{ task.points }}</span>
              </div>
            </div>
          </div>

          <!-- Action Button -->
          <button v-if="task.status === 'DONE'"
                  @click="markPending(task.id)"
                  class="w-full px-6 py-3 bg-white text-slate-400 font-black rounded-xl hover:bg-slate-50 transition-colors border border-slate-200 uppercase tracking-widest text-[10px]">
                  Cofnij oznaczenie
          </button>
          <button v-else
                  @click="markDone(task.id)"
                  class="w-full px-6 py-3 bg-orange-600 text-white font-black rounded-xl hover:bg-orange-700 transition-colors uppercase tracking-widest text-[10px] shadow-sm">
                  Oznacz jako wykonane
          </button>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-if="tasks.length === 0 && pendingTasks.length === 0" 
         class="text-center py-24">
      <h3 class="text-xl font-black text-slate-900 mb-2 italic underline decoration-orange-600 decoration-4 underline-offset-4 uppercase tracking-tighter">Brak zadań</h3>
      <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-8 italic">Obecnie nie masz przypisanych żadnych zadań</p>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
  initialTasks: {
    type: Array,
    default: () => []
  },
  initialPendingTasks: {
    type: Array,
    default: () => []
  },
  workerName: {
    type: String,
    required: true
  },
  initialTotalPoints: {
    type: Number,
    default: 0
  },
  weekPoints: {
    type: Number,
    default: 0
  },
  doneUrlPattern: {
    type: String,
    required: true
  },
  undoUrlPattern: {
    type: String,
    required: true
  }
});

const tasks = ref([...props.initialTasks]);
const pendingTasks = ref([...props.initialPendingTasks]);
const totalPoints = ref(props.initialTotalPoints);
const weekPoints = ref(props.weekPoints);

async function markDone(taskId) {
  try {
    const url = props.doneUrlPattern.replace('TASK_ID', taskId);
    const response = await fetch(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      }
    });

    if (response.ok) {
      // Update task status in current tasks
      const taskInCurrent = tasks.value.find(t => t.id === taskId);
      if (taskInCurrent) {
        taskInCurrent.status = 'DONE';
        totalPoints.value += taskInCurrent.points;
        weekPoints.value += taskInCurrent.points;
      }
      
      // Remove from pending tasks if it was there
      const pendingIndex = pendingTasks.value.findIndex(t => t.id === taskId);
      if (pendingIndex !== -1) {
        const pendingTask = pendingTasks.value[pendingIndex];
        pendingTask.status = 'DONE';
        totalPoints.value += pendingTask.points;
        weekPoints.value += pendingTask.points;
        pendingTasks.value.splice(pendingIndex, 1);
        tasks.value.push(pendingTask);
      }
    } else {
      alert('Nie udało się oznaczyć zadania jako zrobione');
    }
  } catch (error) {
    console.error('Error marking task as done:', error);
    alert('Wystąpił błąd podczas oznaczania zadania');
  }
}

async function markPending(taskId) {
  try {
    const url = props.undoUrlPattern.replace('TASK_ID', taskId);
    const response = await fetch(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      }
    });

    if (response.ok) {
      const task = tasks.value.find(t => t.id === taskId);
      if (task) {
        task.status = 'PENDING';
        totalPoints.value -= task.points;
        weekPoints.value -= task.points;
      }
    } else {
      alert('Nie udało się cofnąć zadania');
    }
  } catch (error) {
    console.error('Error marking task as pending:', error);
    alert('Wystąpił błąd podczas oznaczania zadania');
  }
}

</script>

<style scoped>
.worker-tasks-view {
  max-width: 800px;
  margin: 0 auto;
}
</style>
