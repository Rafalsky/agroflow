<template>
  <div class="admin-incident-manager bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden text-slate-900">
    <!-- Header -->
    <div class="px-8 pt-8 pb-4 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
      <div>
        <h2 class="text-xl font-black text-slate-900 uppercase tracking-tighter italic underline decoration-orange-600 decoration-4 underline-offset-4 mb-2">
          Zarządzanie Awariami
        </h2>
        <p class="text-slate-500 font-medium text-xs italic">Panel kontrolny i historia zgłoszeń</p>
      </div>
      <div class="flex flex-wrap gap-3">
        <a :href="exportUrl" class="bg-slate-900 hover:bg-slate-800 text-white px-6 py-3 rounded-xl font-black text-[10px] uppercase tracking-widest transition-colors shadow-sm">
          Eksport CSV
        </a>
        <button @click="toggleMode" class="bg-white hover:bg-slate-50 text-slate-600 px-6 py-3 rounded-xl font-black text-[10px] uppercase tracking-widest transition-colors border border-slate-200">
          {{ showAll ? 'Pokaż aktywne' : 'Pokaż wszystkie' }}
        </button>
      </div>
    </div>

    <div class="px-8 pb-8 pt-4">
      <div v-if="loading && incidents.length === 0" class="text-center py-20">
        <p class="text-[10px] font-black text-slate-300 uppercase italic tracking-widest animate-pulse">Ładowanie zgłoszeń...</p>
      </div>

      <div v-else-if="incidents.length === 0" class="text-center py-20 bg-slate-50 rounded-2xl border border-dashed border-slate-200">
        <p class="text-[10px] font-black text-slate-300 uppercase italic tracking-widest">Brak zgłoszeń do wyświetlenia</p>
      </div>

      <div v-else class="space-y-6">
        <div v-for="incident in incidents" :key="incident.id"
             class="group relative bg-white rounded-xl p-6 border border-slate-200 transition-colors hover:border-orange-200 shadow-sm">
          
          <div class="flex flex-col md:flex-row justify-between items-start mb-6 gap-6">
            <div class="flex items-center gap-6">
              <div :class="[
                'w-14 h-14 rounded-xl flex items-center justify-center text-2xl font-black italic shadow-sm',
                priorityBg[incident.priority]
              ]">
                {{ incident.priority === 'URGENT' ? '!' : '#' }}
              </div>
              <div>
                <div class="flex items-center gap-2 mb-2">
                   <span :class="['text-[10px] font-black px-2 py-0.5 rounded uppercase tracking-tighter', priorityClasses[incident.priority]]">
                    PRIORYTET {{ priorityNames[incident.priority] }}
                  </span>
                  <span :class="['text-[10px] font-black px-2 py-0.5 rounded uppercase tracking-widest border', statusBorderClasses[incident.status]]">
                    STATUS: {{ statusNames[incident.status] }}
                  </span>
                </div>
                <h4 class="text-xl font-black text-slate-900 italic tracking-tight">{{ incident.title }}</h4>
              </div>
            </div>

            <div class="text-right md:min-w-[150px]">
              <div class="text-[10px] font-black text-slate-400 uppercase italic mb-1 tracking-widest">Przez: {{ incident.reportedBy }}</div>
              <div class="text-[10px] font-black text-slate-400 italic">{{ incident.reportedAt }}</div>
            </div>
          </div>

          <div class="bg-slate-50 rounded-xl p-4 mb-6 text-slate-600 font-bold italic border border-slate-100 text-sm">
            &ldquo;{{ incident.description }}&rdquo;
          </div>

          <!-- Admin Actions -->
          <div v-if="incident.status === 'NEW'" class="flex flex-col md:flex-row gap-3">
            <div class="flex-1">
              <input v-model="incident.newComment" type="text" placeholder="Dodaj komentarz (opcjonalnie)..."
                     class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-orange-500 focus:ring-0 text-xs font-bold uppercase tracking-widest">
            </div>
            <button @click="handleAction(incident, 'approve')" class="bg-orange-600 text-white px-8 py-3 rounded-xl font-black text-[10px] uppercase tracking-[0.2em] hover:bg-orange-700 transition-colors shadow-sm">
              Zatwierdź
            </button>
            <button @click="handleAction(incident, 'reject')" class="bg-white text-red-600 border border-red-100 px-8 py-3 rounded-xl font-black text-[10px] uppercase tracking-[0.2em] hover:bg-red-50 transition-colors">
              Odrzuć
            </button>
          </div>

          <!-- Resolution Note when APPROVED -->
          <div v-if="incident.status === 'APPROVED'" class="flex flex-col md:flex-row gap-3">
            <div class="flex-1">
              <input v-model="incident.newComment" type="text" placeholder="Notatka z naprawy (opcjonalnie)..."
                     class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-orange-500 focus:ring-0 text-xs font-bold uppercase tracking-widest">
            </div>
            <button @click="handleAction(incident, 'resolve')" class="bg-slate-900 text-white px-10 py-3 rounded-xl font-black text-[10px] uppercase tracking-[0.2em] hover:bg-slate-950 transition-colors shadow-sm">
              Zakończ naprawę
            </button>
          </div>

          <!-- Closed Info -->
          <div v-if="['RESOLVED', 'REJECTED'].includes(incident.status)" class="text-[10px] font-black text-slate-400 uppercase italic pt-6 mt-6 border-t border-slate-100">
            Obsłużone przez: <span class="text-slate-900">{{ incident.handledBy }}</span> &mdash; {{ incident.handledAt }}
            <div v-if="incident.adminComment" class="mt-2 text-slate-500 font-normal">
              Notatka: <span class="font-bold underline">{{ incident.adminComment }}</span>
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
  listUrl: { type: String, required: true },
  exportUrl: { type: String, required: true },
  actionUrlPattern: { type: String, required: true } // format: /api/incidents/ID/ACTION
});

const incidents = ref([]);
const loading = ref(false);
const showAll = ref(false);

const priorityNames = { LOW: 'Niski', NORMAL: 'Normalny', HIGH: 'Wysoki', URGENT: 'PILNY' };
const priorityClasses = {
  LOW: 'bg-slate-50 text-slate-400',
  NORMAL: 'bg-blue-50 text-blue-600',
  HIGH: 'bg-orange-50 text-orange-600',
  URGENT: 'bg-red-50 text-red-600'
};
const priorityBg = {
  LOW: 'bg-slate-50 text-slate-200',
  NORMAL: 'bg-blue-50 text-blue-300',
  HIGH: 'bg-orange-50 text-orange-300',
  URGENT: 'bg-red-600 text-white'
};

const statusNames = { NEW: 'Oczekuje', APPROVED: 'W realizacji', REJECTED: 'Odrzucona', RESOLVED: 'Naprawiona' };
const statusBorderClasses = {
  NEW: 'border-orange-200 text-orange-600',
  APPROVED: 'border-blue-200 text-blue-600',
  REJECTED: 'border-slate-200 text-slate-400',
  RESOLVED: 'border-slate-900 text-slate-900'
};

async function fetchIncidents() {
  loading.value = true;
  try {
    const response = await fetch(`${props.listUrl}?all=${showAll.value}`);
    const data = await response.json();
    incidents.value = data.map(i => ({ ...i, newComment: '' }));
  } catch (e) {
    console.error('Failed to fetch incidents');
  } finally {
    loading.value = false;
  }
}

function toggleMode() {
  showAll.value = !showAll.value;
  fetchIncidents();
}

async function handleAction(incident, action) {
  if (action === 'reject' && !incident.newComment) {
    alert('Podaj powód odrzucenia w polu komentarza.');
    return;
  }

  const url = props.actionUrlPattern.replace('ID', incident.id).replace('ACTION', action);
  try {
    const response = await fetch(url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ 
        comment: incident.newComment,
        reason: incident.newComment 
      })
    });
    
    if (response.ok) {
      await fetchIncidents();
    } else {
      const err = await response.json();
      alert(err.error || 'Błąd zapisu');
    }
  } catch (e) {
    alert('Błąd połączenia');
  }
}

onMounted(fetchIncidents);
</script>
