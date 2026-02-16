<template>
  <div class="admin-panel h-full min-h-screen bg-white">
    <!-- Tab Navigation -->
    <div class="border-b border-slate-200 sticky top-[56px] bg-white z-20">
      <div class="max-w-[1400px] mx-auto flex gap-6 px-6">
        <button v-for="tab in tabs" :key="tab.id"
                @click="currentTab = tab.id"
                :class="[
                  'py-3 px-1 text-[10px] font-black uppercase tracking-widest transition-all border-b-2',
                  currentTab === tab.id ? 'border-orange-600 text-slate-900' : 'border-transparent text-slate-400 hover:text-slate-600'
                ]">
          {{ tab.name }}
        </button>
      </div>
    </div>

    <!-- Main Content Area -->
    <main class="max-w-[1400px] mx-auto py-8 px-6">
        <div v-show="currentTab === 'week'">
            <AdminWeek 
                v-if="weekData"
                :week="weekData.week"
                :tasks="weekData.tasks"
                :pendingTasks="weekData.pendingTasks"
                :workers="workers"
                :urls="urls"
                :doneUrlPattern="urls.done"
                :undoUrlPattern="urls.undo"
                :assignUrlPattern="urls.assign"
                :weekLeaderboard="weekData.leaderboard"
                :teamTotal="weekData.teamTotal"
                :completionRate="completionRate"
                :topWorker="topWorker"
                @open-task="openTaskDrawer"
                @toggle-task="toggleTask"
                @assign-worker="assignWorker"
            />

        </div>

        <div v-show="currentTab === 'templates'">
            <div class="flex justify-between items-center mb-8">
                <h2 class="text-3xl font-black italic text-slate-900 underline decoration-orange-600 decoration-8 underline-offset-4 uppercase tracking-tighter">Szablony Zadań</h2>
                <button @click="openTemplateDrawer()" class="bg-orange-600 text-white px-8 py-4 rounded-xl font-black uppercase tracking-widest text-[10px] shadow-sm hover:bg-orange-700 transition-colors">Nowy Szablon</button>
            </div>
            <!-- Templates Grid/Table -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="template in templates" :key="template.id" 
                     class="bg-white border border-slate-200 rounded-2xl p-8 hover:border-orange-200 transition-colors group cursor-pointer"
                     @click="openTemplateDrawer(template)">
                    <div class="flex justify-between items-start mb-6">
                        <h3 class="text-xl font-black italic text-slate-900 group-hover:text-orange-600">{{ template.name }}</h3>
                        <span class="bg-slate-900 text-white px-3 py-1 rounded italic font-black text-sm">{{ template.points }} pkt</span>
                    </div>
                    <div class="flex gap-2 text-[10px] font-black uppercase tracking-widest text-slate-400">
                      <span>Dzień: {{ template.weekday }}</span>
                      <span v-if="template.recurring" class="text-orange-600">● Cykliczne</span>
                    </div>
                </div>
            </div>
        </div>

        <div v-show="currentTab === 'workers'">
             <div class="flex justify-between items-center mb-12">
                <h2 class="text-3xl font-black italic text-slate-900 underline decoration-orange-600 decoration-8 underline-offset-4 uppercase tracking-tighter">Zespół</h2>
                <button @click="openWorkerDrawer()" class="bg-orange-600 text-white px-8 py-4 rounded-xl font-black uppercase tracking-widest text-[10px] shadow-sm hover:bg-orange-700 transition-colors">Dodaj Pracownika</button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div v-for="worker in workers" :key="worker.id" 
                     class="bg-white border border-slate-200 rounded-2xl p-8 flex flex-col items-center text-center hover:border-orange-200 transition-colors cursor-pointer"
                     @click="openWorkerDrawer(worker)">
                    <div class="w-20 h-20 bg-slate-900 rounded-full flex items-center justify-center text-3xl font-black text-white italic mb-4">
                        {{ worker.shortName }}
                    </div>
                    <h3 class="text-xl font-black italic text-slate-900 mb-2">{{ worker.name }}</h3>
                    <span :class="['px-3 py-1 rounded text-[10px] font-black uppercase tracking-widest', worker.active ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600']">
                        {{ worker.active ? 'Aktywny' : 'Nieaktywny' }}
                    </span>
                    <div class="mt-6 pt-6 border-t border-slate-100 w-full text-left">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1 italic">Token Dostępny</p>
                        <code class="text-xs bg-slate-50 p-2 rounded block break-all font-mono text-slate-600 select-all">{{ worker.accessToken }}</code>
                    </div>
                </div>
            </div>
        </div>

        <div v-show="currentTab === 'audit'">
            <AuditHistory :logs="auditLogs" />
        </div>

        <div v-show="currentTab === 'welfare'">
            <WelfareWidget 
                :initialState="initialData.welfare" 
                :dataUrl="urls.welfareData"
                :changeUrl="urls.welfareChange"
            />
        </div>

        <div v-show="currentTab === 'incidents'">
            <AdminIncidentManager 
                :listUrl="urls.incidentList"
                :exportUrl="urls.incidentExport"
                :actionUrlPattern="urls.incidentAction"
            />
        </div>
    </main>

    <!-- Context Drawer -->
    <ContextDrawer 
        :entity="drawerEntity" 
        :isOpen="isDrawerOpen" 
        :workers="workers"
        @close="closeDrawer" 
        @toggle-task="toggleTask"
        @assign-worker="assignWorker"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import AdminWeek from './AdminWeek.vue';
import WelfareWidget from './WelfareWidget.vue';
import AdminIncidentManager from './AdminIncidentManager.vue';
import ContextDrawer from './ContextDrawer.vue';
import AuditHistory from './AuditHistory.vue';

const props = defineProps({
    initialData: { type: Object, required: true },
    urls: { type: Object, required: true }
});

const currentTab = ref('week');
const tabs = [
    { id: 'week', name: 'Tydzień' },
    { id: 'templates', name: 'Szablony' },
    { id: 'workers', name: 'Pracownicy' },
    { id: 'welfare', name: 'Dobrostan' },
    { id: 'incidents', name: 'Awarie' },
    { id: 'audit', name: 'Historia' },
];

const weekData = ref(null);
const workers = ref([]);
const templates = ref([]);
const auditLogs = ref([]);

const drawerEntity = ref(null);
const isDrawerOpen = ref(false);

onMounted(() => {
    const data = props.initialData;
    const teamTotal = data.leaderboard.reduce((acc, curr) => acc + parseInt(curr.total_amount), 0);
    
    weekData.value = {
        week: data.week,
        tasks: data.tasks || [],
        pendingTasks: data.pendingTasks || [],
        leaderboard: data.leaderboard,
        teamTotal: teamTotal
    };
    workers.value = data.workers;
    templates.value = data.templates;
    auditLogs.value = data.auditLogs;
});

const completionRate = computed(() => {
  if (!weekData.value || weekData.value.tasks.length === 0) return 0;
  const done = weekData.value.tasks.filter(t => t.status === 'DONE').length;
  return Math.round((done / weekData.value.tasks.length) * 100);
});

const topWorker = computed(() => {
  if (!weekData.value || weekData.value.leaderboard.length === 0) return null;
  const leader = weekData.value.leaderboard[0];
  const workerInfo = workers.value.find(w => w.id === leader.worker_id);
  return { ...leader, ...workerInfo };
});

function openTaskDrawer(task) {
    drawerEntity.value = { type: 'task', data: task };
    isDrawerOpen.value = true;
}

function openTemplateDrawer(template = null) {
    drawerEntity.value = { type: 'template', data: template };
    isDrawerOpen.value = true;
}

function openWorkerDrawer(worker = null) {
    drawerEntity.value = { type: 'worker', data: worker };
    isDrawerOpen.value = true;
}

function closeDrawer() {
    isDrawerOpen.value = false;
    drawerEntity.value = null;
}

async function toggleTask(task) {
  if (task.processing) return;

  const isDone = task.status === 'DONE';
  const urlPattern = isDone ? props.urls.undo : props.urls.done;
  const url = urlPattern.replace('TASK_ID', task.id);

  task.processing = true;

  try {
    const response = await fetch(url, { method: 'POST' });
    if (!response.ok) throw new Error('API Error');
    
    // Update local state (optimistic)
    const oldStatus = task.status;
    task.status = isDone ? 'PENDING' : 'DONE';
    
    if (task.status === 'DONE') {
      task.doneAt = new Date().toISOString().replace('T', ' ').slice(0, 19);
      if (oldStatus !== 'DONE') weekData.value.teamTotal += task.points;
    } else {
      task.doneAt = null;
      if (oldStatus === 'DONE') weekData.value.teamTotal -= task.points;
    }
    
    // Refresh leaderboard could be done here if needed
  } catch (err) {
    alert('Błąd aktualizacji zadania: ' + err.message);
  } finally {
    task.processing = false;
  }
}

async function assignWorker(task, workerId) {
  const url = props.urls.assign.replace('TASK_ID', task.id);
  
  try {
    const response = await fetch(url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ worker_id: workerId })
    });
    
    if (!response.ok) throw new Error('API Error');
    
    task.worker_id = workerId;
    if (workerId) {
      const worker = workers.value.find(w => w.id === workerId);
      task.worker_name = worker ? worker.shortName : null;
    } else {
      task.worker_name = null;
    }
  } catch (err) {
    alert('Błąd przypisywania pracownika: ' + err.message);
  }
}
</script>
