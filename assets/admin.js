import './styles/admin.css';
import { createApp } from 'vue';
import AdminWeek from './vue/AdminWeek.vue';
import WorkerTasks from './vue/WorkerTasks.vue';
import WorkerAgenda from './vue/WorkerAgenda.vue';
import WelfareWidget from './vue/WelfareWidget.vue';

import IncidentWidget from './vue/IncidentWidget.vue';
import AdminIncidentManager from './vue/AdminIncidentManager.vue';
import AdminPanel from './vue/AdminPanel.vue';

// Basic Vue setup for admin widgets
document.addEventListener('DOMContentLoaded', () => {
    const adminWeekEl = document.getElementById('vue-admin-week');
    if (adminWeekEl) {
        const week = JSON.parse(adminWeekEl.dataset.week || '{}');
        const tasks = JSON.parse(adminWeekEl.dataset.tasks || '[]');
        const pendingTasks = JSON.parse(adminWeekEl.dataset.pendingTasks || '[]');
        const workers = JSON.parse(adminWeekEl.dataset.workers || '[]');
        const doneUrlPattern = adminWeekEl.dataset.doneUrlPattern;
        const undoUrlPattern = adminWeekEl.dataset.undoUrlPattern;
        const assignUrlPattern = adminWeekEl.dataset.assignUrlPattern;
        const weekLeaderboard = JSON.parse(adminWeekEl.dataset.weekLeaderboard || '[]');
        const teamTotal = parseInt(adminWeekEl.dataset.teamTotal || '0');

        createApp(AdminWeek, {
            week,
            initialTasks: tasks,
            initialPendingTasks: pendingTasks,
            workers,
            doneUrlPattern,
            undoUrlPattern,
            assignUrlPattern,
            weekLeaderboard,
            teamTotal
        }).mount(adminWeekEl);
    }

    const workerTasksEl = document.getElementById('vue-worker-tasks');
    if (workerTasksEl) {
        const tasks = JSON.parse(workerTasksEl.dataset.tasks || '[]');
        const workerName = workerTasksEl.dataset.workerName;
        const totalPoints = parseInt(workerTasksEl.dataset.totalPoints || '0');
        const weekPoints = parseInt(workerTasksEl.dataset.weekPoints || '0');
        const doneUrlPattern = workerTasksEl.dataset.doneUrlPattern;

        createApp(WorkerAgenda, {
            initialTasks: tasks,
            workerName,
            initialTotalPoints: totalPoints,
            initialWeekPoints: weekPoints,
            doneUrlPattern
        }).mount(workerTasksEl);
    }


    const welfareWidgetEl = document.getElementById('vue-welfare-widget');
    if (welfareWidgetEl) {
        const dataUrl = welfareWidgetEl.dataset.dataUrl;
        const changeUrl = welfareWidgetEl.dataset.changeUrl;

        createApp(WelfareWidget, {
            dataUrl,
            changeUrl
        }).mount(welfareWidgetEl);
    }

    const incidentWidgetEl = document.getElementById('vue-incident-widget');
    if (incidentWidgetEl) {
        const listUrl = incidentWidgetEl.dataset.listUrl;
        const reportUrl = incidentWidgetEl.dataset.reportUrl;
        const resolveUrlPattern = incidentWidgetEl.dataset.resolveUrlPattern;

        createApp(IncidentWidget, {
            listUrl,
            reportUrl,
            resolveUrlPattern
        }).mount(incidentWidgetEl);
    }

    const adminPanelEl = document.getElementById('vue-admin-panel');
    if (adminPanelEl) {
        const initialData = JSON.parse(adminPanelEl.dataset.initial || '{}');
        const urls = {
            done: adminPanelEl.dataset.doneUrlPattern,
            undo: adminPanelEl.dataset.undoUrlPattern,
            assign: adminPanelEl.dataset.assignUrlPattern,
            generate: adminPanelEl.dataset.generateUrl,
            close: adminPanelEl.dataset.closeUrl,
            incidentList: adminPanelEl.dataset.incidentListUrl,
            incidentExport: adminPanelEl.dataset.incidentExportUrl,
            incidentAction: adminPanelEl.dataset.incidentActionUrlPattern,
            welfareData: adminPanelEl.dataset.welfareDataUrl,
            welfareChange: adminPanelEl.dataset.welfareChangeUrl
        };

        createApp(AdminPanel, {
            initialData,
            urls
        }).mount(adminPanelEl);
    }

    const adminIncidentManagerEl = document.getElementById('vue-admin-incident-manager');
    if (adminIncidentManagerEl) {
        const listUrl = adminIncidentManagerEl.dataset.listUrl;
        const exportUrl = adminIncidentManagerEl.dataset.exportUrl;
        const actionUrlPattern = adminIncidentManagerEl.dataset.actionUrlPattern;

        createApp(AdminIncidentManager, {
            listUrl,
            exportUrl,
            actionUrlPattern
        }).mount(adminIncidentManagerEl);
    }
});
