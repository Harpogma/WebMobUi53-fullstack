<script setup>
import { ref, computed, watch } from 'vue';
import { useFetchApi } from '@/composables/useFetchApi';
import { pollToViewResults, showResultsDialog } from '@/store/polls';

const { fetchApi } = useFetchApi();

const poll    = ref(null);
const loading = ref(false);
const error   = ref(null);

watch(pollToViewResults, async (p) => {
  if (!p) return;
  poll.value  = null;
  error.value = null;
  loading.value = true;
  try {
    poll.value = await fetchApi({ url: `/polls/${p.secret_token}` });
  } catch {
    error.value = 'Impossible de charger les résultats.';
  } finally {
    loading.value = false;
  }
}, { immediate: true });

const totalVotes = computed(() => {
  if (!poll.value?.options) return 0;
  return poll.value.options.reduce((sum, o) => sum + (o.votes_count ?? 0), 0);
});

function votePercent(option) {
  if (!totalVotes.value) return 0;
  return Math.round(((option.votes_count ?? 0) / totalVotes.value) * 100);
}
</script>

<template>
  <q-card style="min-width: 480px; max-width: 600px">
    <q-card-section class="row items-center justify-between">
      <div class="text-h6">Résultats</div>
      <q-btn flat round icon="close" @click="showResultsDialog = false" />
    </q-card-section>

    <q-separator />

    <q-card-section v-if="loading" class="text-center q-py-xl">
      <q-spinner color="primary" size="2em" />
    </q-card-section>

    <q-card-section v-else-if="error">
      <p class="text-negative">{{ error }}</p>
    </q-card-section>

    <q-card-section v-else-if="poll">
      <div v-if="poll.title" class="text-caption text-grey q-mb-xs">{{ poll.title }}</div>
      <div class="text-subtitle1 q-mb-md">{{ poll.question }}</div>

      <div v-if="totalVotes === 0" class="text-grey q-mb-md">Aucun vote pour l'instant.</div>

      <div v-for="option in poll.options" :key="option.id" class="q-mb-md">
        <div class="row items-center justify-between q-mb-xs">
          <span>{{ option.label }}</span>
          <span class="text-caption text-grey">
            {{ option.votes_count ?? 0 }} vote{{ (option.votes_count ?? 0) !== 1 ? 's' : '' }}
            <span v-if="totalVotes > 0"> ({{ votePercent(option) }}%)</span>
          </span>
        </div>
        <q-linear-progress
          :value="votePercent(option) / 100"
          color="primary"
          track-color="grey-3"
          rounded
          style="height: 12px"
        />
      </div>

      <p class="text-caption text-grey q-mt-sm">
        {{ totalVotes }} vote{{ totalVotes !== 1 ? 's' : '' }} au total
      </p>
    </q-card-section>

    <q-card-actions align="right">
      <q-btn flat label="Fermer" @click="showResultsDialog = false" />
    </q-card-actions>
  </q-card>
</template>
