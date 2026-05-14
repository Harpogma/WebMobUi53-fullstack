<script setup>
  import { watch } from 'vue';
  import PollTable from './components/PollTable.vue';
  import CreatePollDialog from './components/CreatePollDialog.vue';
  import { useFetchApi } from './composables/useFetchApi';
  import { usePolling } from './composables/usePolling';
  import { showCreateDialog } from './store/polls';

  const props = defineProps({
    loginUrl: { type: String, required: true },
  });

  const { fetchApiToRef } = useFetchApi();

  const { data: getResult, error: getError, fetchNow } = fetchApiToRef({ url: 'polls/' });
  const { data: postResult, error: postError } = fetchApiToRef({ url: '/foo', data: { id: 1 } });

  function handleError(err) {
    if (!err) return;
    if (err?.status === 401) {
      window.location.href = props.loginUrl;
    } else {
      console.error(err);
    }
  }

  watch(getError, err => handleError(err));
  watch(postError, handleError);

  usePolling(fetchNow);
</script>

<template>
  <main class="min-h-screen p-6">
    <h1 class="mb-4 text-xl font-semibold">Mes sondages</h1>

    <q-btn class="mb-4" color="primary" label="Créer un nouveau sondage" icon="add" @click="showCreateDialog = true"/>

    <PollTable />

    <q-dialog v-model="showCreateDialog">
      <CreatePollDialog />
    </q-dialog>
  </main>
</template>
