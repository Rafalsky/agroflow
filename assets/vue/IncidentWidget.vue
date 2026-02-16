<template>
  <div class="incident-widget bg-white rounded-3xl shadow-xl overflow-hidden border-2 border-red-50">
    <!-- Header -->
    <div class="bg-gradient-to-r from-red-500 to-rose-600 p-6 text-white flex justify-between items-center">
      <div>
        <h2 class="text-2xl font-black flex items-center gap-3">
          <span>🚨</span> Awarie i Problemy
        </h2>
        <p class="opacity-80 text-sm font-medium">Zgłoś usterkę lub awarię sprzętu</p>
      </div>
      <button v-if="!showForm" @click="showForm = true" 
              class="bg-white text-red-600 px-4 py-2 rounded-xl font-bold hover:bg-red-50 transition-all shadow-md">
        Zgłoś Nową
      </button>
      <button v-else @click="showForm = false" 
              class="bg-red-700/30 text-white px-4 py-2 rounded-xl font-bold hover:bg-red-700/50 transition-all">
        Zamknij
      </button>
    </div>

    <div class="p-6">
      <!-- Report Form -->
      <div v-if="showForm" class="mb-8 animate-in slide-in-from-top duration-300">
        <div class="bg-red-50 rounded-2xl p-5 border-2 border-red-100">
          <div class="space-y-4">
            <div>
              <label class="block text-xs font-bold text-red-600 uppercase mb-1">Tytuł zgłoszenia</label>
              <input v-model="form.title" type="text" placeholder="np. Awaria paszociągu hala A"
                     class="w-full px-4 py-3 rounded-xl border-2 border-red-200 focus:border-red-400 focus:ring-0 font-medium">
            </div>
            
            <div>
              <label class="block text-xs font-bold text-red-600 uppercase mb-1">Opis usterki</label>
              <textarea v-model="form.description" rows="3" placeholder="Opisz co się stało..."
                        class="w-full px-4 py-3 rounded-xl border-2 border-red-200 focus:border-red-400 focus:ring-0 font-medium"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-bold text-red-600 uppercase mb-1">Priorytet</label>
                <select v-model="form.priority" 
                        class="w-full px-4 py-3 rounded-xl border-2 border-red-200 focus:border-red-400 focus:ring-0 font-bold bg-white">
                  <option value="LOW">Niski</option>
                  <option value="NORMAL">Normalny</option>
                  <option value="HIGH">Wysoki</option>
                  <option value="URGENT">PILNY</option>
                </select>
              </div>
              <div class="flex items-end">
                <button @click="submitReport" :disabled="loading"
                        class="w-full py-3 bg-red-600 text-white font-black rounded-xl shadow-lg hover:bg-red-700 transition-all active:scale-95 disabled:opacity-50">
                  WYŚLIJ 🚀
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Active Incidents List -->
      <div>
        <h3 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-4">Aktywne zgłoszenia</h3>
        <div v-if="incidents.length === 0" class="text-center py-8 text-gray-400 italic">
          Brak aktywnych awarii. System działa sprawnie.
        </div>
        <div class="space-y-4">
          <div v-for="incident in incidents" :key="incident.id"
               class="bg-white rounded-2xl p-5 border-2 border-gray-100 shadow-sm transition-all hover:border-red-200">
            <div class="flex justify-between items-start mb-2">
              <div>
                <span :class="[
                  'text-[10px] font-black px-2 py-1 rounded-md uppercase tracking-wider mb-2 inline-block',
                  priorityClasses[incident.priority]
                ]">
                  {{ priorityNames[incident.priority] }}
                </span>
                <h4 class="font-black text-gray-800 text-lg">{{ incident.title }}</h4>
              </div>
              <div :class="[
                'px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest',
                statusClasses[incident.status]
              ]">
                {{ statusNames[incident.status] }}
              </div>
            </div>
            <p class="text-sm text-gray-600 mb-4">{{ incident.description }}</p>
            
            <div class="flex items-center justify-between pt-4 border-t border-gray-50">
              <div class="text-[10px] text-gray-400">
                Zgłoszone przez: <span class="font-bold text-gray-600">{{ incident.reportedBy }}</span><br>
                {{ incident.reportedAt }}
              </div>
              
              <!-- Resolve button for workers if APPROVED -->
              <button v-if="incident.status === 'APPROVED'" 
                      @click="resolveIncident(incident.id)"
                      class="px-4 py-2 bg-green-500 text-white text-xs font-black rounded-lg hover:bg-green-600 shadow-md">
                ROZWIĄZANE ✓
              </button>
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
  reportUrl: { type: String, required: true },
  resolveUrlPattern: { type: String, required: true }
});

const incidents = ref([]);
const loading = ref(false);
const showForm = ref(false);

const form = ref({
  title: '',
  description: '',
  priority: 'NORMAL'
});

const priorityNames = { LOW: 'Niski', NORMAL: 'Normalny', HIGH: 'Wysoki', URGENT: 'PILNY' };
const priorityClasses = {
  LOW: 'bg-blue-100 text-blue-600',
  NORMAL: 'bg-gray-100 text-gray-600',
  HIGH: 'bg-orange-100 text-orange-600',
  URGENT: 'bg-red-100 text-red-600'
};

const statusNames = { NEW: 'Oczekuje', APPROVED: 'W realizacji', REJECTED: 'Odrzucona', RESOLVED: 'Naprawiona' };
const statusClasses = {
  NEW: 'bg-amber-100 text-amber-700',
  APPROVED: 'bg-blue-100 text-blue-700',
  REJECTED: 'bg-gray-100 text-gray-700',
  RESOLVED: 'bg-green-100 text-green-700'
};

async function fetchIncidents() {
  try {
    const response = await fetch(props.listUrl);
    incidents.value = await response.json();
  } catch (e) {
    console.error('Failed to fetch incidents');
  }
}

async function submitReport() {
  if (!form.value.title || !form.value.description) return;
  
  loading.value = true;
  try {
    const response = await fetch(props.reportUrl, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(form.value)
    });
    
    if (response.ok) {
      await fetchIncidents();
      form.value = { title: '', description: '', priority: 'NORMAL' };
      showForm.value = false;
    } else {
      alert('Błąd wysyłania zgłoszenia');
    }
  } catch (e) {
    alert('Błąd połączenia');
  } finally {
    loading.value = false;
  }
}

async function resolveIncident(id) {
  if (!confirm('Czy na pewno awaria została naprawiona?')) return;
  
  try {
    const url = props.resolveUrlPattern.replace('ID', id);
    const response = await fetch(url, { method: 'POST' });
    if (response.ok) {
      await fetchIncidents();
    }
  } catch (e) {
    alert('Błąd połączenia');
  }
}

onMounted(fetchIncidents);
</script>
