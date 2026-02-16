<template>
  <div class="audit-history">
    <div class="flex justify-between items-center mb-12">
        <h2 class="text-3xl font-black italic text-slate-900 underline decoration-orange-600 decoration-8 underline-offset-4 uppercase tracking-tighter">Historia Systemu</h2>
    </div>

    <div class="bg-white border border-slate-200 rounded-3xl overflow-hidden shadow-sm">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="bg-slate-50 border-b border-slate-200">
            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest italic">Data</th>
            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest italic">Typ</th>
            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest italic">Encja</th>
            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest italic">Zdarzenie</th>
            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest italic">Aktor</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <tr v-for="log in logs" :key="log.id" class="hover:bg-slate-50/50 transition-colors">
            <td class="px-8 py-5 text-sm font-bold text-slate-400 tabular-nums">{{ log.createdAt }}</td>
            <td class="px-8 py-5">
              <span class="px-2 py-0.5 rounded bg-slate-100 text-[10px] font-black text-slate-500 uppercase tracking-tighter">{{ log.entityType }}</span>
            </td>
            <td class="px-8 py-5 text-sm font-black text-slate-900 italic">#{{ log.entityId }}</td>
            <td class="px-8 py-5 text-sm font-bold text-slate-700">{{ log.eventType }}</td>
            <td class="px-8 py-5">
              <span class="text-[10px] font-black text-orange-600 uppercase tracking-widest italic">{{ log.actorType }}</span>
            </td>
          </tr>
        </tbody>
      </table>
      <div v-if="logs.length === 0" class="p-12 text-center">
        <p class="text-[10px] font-black text-slate-300 uppercase tracking-[0.2em] italic">Brak zarejestrowanych zdarzeń</p>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  logs: { type: Array, required: true }
});
</script>
