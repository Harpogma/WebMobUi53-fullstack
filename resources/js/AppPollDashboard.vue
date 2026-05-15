<script setup>
  import PollTable from './components/PollTable.vue';
  import CreatePollDialog from './components/CreatePollDialog.vue';
  import EditPollDialog from './components/EditPollDialog.vue';
  import { useFetchApi } from './composables/useFetchApi';
  import { usePolling } from './composables/usePolling';
  import { polls, showCreateDialog, showEditDialog } from './store/polls';

  const props = defineProps({
    loginUrl: { type: String, required: true },
  });

  const { fetchApi } = useFetchApi();

  async function refreshPolls() {
    try {
      polls.value = await fetchApi({ url: 'polls/' });
    } catch (err) {
      if (err?.status === 401) window.location.href = props.loginUrl;
    }
  }

  refreshPolls();
  usePolling(refreshPolls);
</script>

<template>
  <main class="min-h-screen p-6">
    <h1 class="mb-4 text-xl font-semibold">Mes sondages</h1>

    <q-btn class="mb-4" color="primary" label="Créer un nouveau sondage" icon="add" @click="showCreateDialog = true"/>

    <PollTable />

    <q-dialog v-model="showCreateDialog">
      <CreatePollDialog />
    </q-dialog>

    <q-dialog v-model="showEditDialog">
      <EditPollDialog />
    </q-dialog>
  </main>
</template>
