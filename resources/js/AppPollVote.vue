<script setup>
import { ref, computed, onMounted } from 'vue';
import { useFetchApi } from '@/composables/useFetchApi';
import { useQuasar } from 'quasar';

const props = defineProps({
  token:      { type: String,  required: true },
  loginUrl:   { type: String,  default: null },
  authUserId: { type: Number,  default: null },
});

const $q = useQuasar();
const { fetchApi } = useFetchApi();

const poll        = ref(null);
const loading     = ref(true);
const fetchError  = ref(null);
const selected    = ref([]);
const singleSelected = ref(null);
const voted       = ref(false);
const submitting  = ref(false);

onMounted(async () => {
  try {
    poll.value = await fetchApi({ url: `/polls/${props.token}` });
  } catch (err) {
    fetchError.value = err;
  } finally {
    loading.value = false;
  }
});

const isActive    = computed(() => poll.value && !poll.value.is_draft);
const isEnded     = computed(() => poll.value?.ends_at && new Date(poll.value.ends_at) < new Date());
const canVote     = computed(() => isActive.value && !isEnded.value && !!props.authUserId && !voted.value);
const showResults = computed(() => poll.value?.results_public && (voted.value || isEnded.value));

const totalVotes = computed(() => {
  if (!poll.value?.options) return 0;
  return poll.value.options.reduce((sum, o) => sum + (o.votes_count ?? 0), 0);
});

function votePercent(option) {
  if (!totalVotes.value) return 0;
  return Math.round(((option.votes_count ?? 0) / totalVotes.value) * 100);
}

const effectiveSelected = computed(() =>
  poll.value?.allow_multiple_choices
    ? selected.value
    : singleSelected.value != null ? [singleSelected.value] : []
);

async function submitVote() {
  if (!effectiveSelected.value.length) return;
  submitting.value = true;
  try {
    await fetchApi({
      url: `/polls/${props.token}/vote`,
      method: 'POST',
      data: { option_ids: effectiveSelected.value },
    });
    voted.value = true;
    poll.value = await fetchApi({ url: `/polls/${props.token}` });
    $q.notify({ message: 'Vote enregistré !', color: 'positive', position: 'top' });
  } catch (err) {
    const msg = err?.data?.message ?? 'Erreur lors du vote.';
    $q.notify({ message: msg, color: 'negative', position: 'top' });
  } finally {
    submitting.value = false;
  }
}
</script>

<template>
  <div class="min-h-screen flex items-center justify-center p-6 bg-slate-50">
    <q-card style="width: 100%; max-width: 560px">

      <template v-if="loading">
        <q-card-section class="text-center q-py-xl">
          <q-spinner color="primary" size="3em" />
        </q-card-section>
      </template>

      <template v-else-if="fetchError">
        <q-card-section>
          <div class="text-h6 text-negative">Sondage introuvable</div>
          <p class="text-grey q-mt-sm">Ce lien de sondage est invalide ou a été supprimé.</p>
        </q-card-section>
      </template>

      <template v-else-if="poll">
        <q-card-section>
          <div v-if="poll.title" class="text-caption text-grey q-mb-xs">{{ poll.title }}</div>
          <div class="text-h6">{{ poll.question }}</div>

          <q-chip
            v-if="poll.is_draft"
            color="warning" text-color="white" icon="edit" dense class="q-mt-sm"
          >Brouillon</q-chip>
          <q-chip
            v-else-if="isEnded"
            color="grey" text-color="white" icon="lock" dense class="q-mt-sm"
          >Terminé</q-chip>
          <q-chip
            v-else
            color="positive" text-color="white" icon="play_arrow" dense class="q-mt-sm"
          >En cours</q-chip>
        </q-card-section>

        <q-separator />

        <!-- Draft: not started yet -->
        <q-card-section v-if="poll.is_draft">
          <p class="text-grey">Ce sondage n'est pas encore ouvert aux votes.</p>
        </q-card-section>

        <!-- Active: show vote form or results -->
        <q-card-section v-else class="q-gutter-sm">

          <!-- Results view -->
          <template v-if="showResults">
            <div v-for="option in poll.options" :key="option.id">
              <div class="row items-center justify-between q-mb-xs">
                <span>{{ option.label }}</span>
                <span class="text-grey text-caption">{{ option.votes_count ?? 0 }} vote{{ (option.votes_count ?? 0) !== 1 ? 's' : '' }}</span>
              </div>
              <q-linear-progress
                :value="votePercent(option) / 100"
                color="primary"
                track-color="grey-3"
                rounded
                class="q-mb-md"
                style="height: 12px"
              />
            </div>
            <p class="text-caption text-grey">{{ totalVotes }} vote{{ totalVotes !== 1 ? 's' : '' }} au total</p>
          </template>

          <!-- Vote form -->
          <template v-else-if="canVote">
            <p class="text-caption text-grey q-mb-sm">
              {{ poll.allow_multiple_choices ? 'Plusieurs choix possibles' : 'Un seul choix possible' }}
            </p>

            <template v-if="poll.allow_multiple_choices">
              <q-checkbox
                v-for="option in poll.options"
                :key="option.id"
                v-model="selected"
                :val="option.id"
                :label="option.label"
                class="q-mb-xs"
              />
            </template>
            <template v-else>
              <q-radio
                v-for="option in poll.options"
                :key="option.id"
                v-model="singleSelected"
                :val="option.id"
                :label="option.label"
                class="q-mb-xs"
              />
            </template>

            <div class="q-mt-md">
              <q-btn
                color="primary"
                label="Voter"
                :disable="!effectiveSelected.length"
                :loading="submitting"
                @click="submitVote"
              />
            </div>
          </template>

          <!-- Not logged in -->
          <template v-else-if="!authUserId">
            <p class="text-grey">Vous devez être connecté pour voter.</p>
            <q-btn flat color="primary" label="Se connecter" :href="loginUrl" />
          </template>

          <!-- Already voted, results private -->
          <template v-else-if="voted">
            <p class="text-positive">Vote enregistré. Les résultats sont privés.</p>
          </template>

          <!-- Poll ended, results private -->
          <template v-else>
            <p class="text-grey">Ce sondage est terminé.</p>
          </template>
        </q-card-section>
      </template>

    </q-card>
  </div>
</template>
