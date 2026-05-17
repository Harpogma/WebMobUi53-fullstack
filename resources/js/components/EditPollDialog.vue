<script setup>
import { ref, computed, watch } from 'vue';
import { updatePoll, pollToEdit, showEditDialog } from '@/store/polls';
import { useQuasar } from 'quasar';

const $q = useQuasar();

const title = ref('');
const question = ref('');
const options = ref([]);
const allowMultipleChoices = ref(false);
const resultsPublic = ref(false);
const durationMinutes = ref(null);
const validationErrors = ref({});

watch(showEditDialog, (open) => {
  if (!open) validationErrors.value = {};
});

watch(
  pollToEdit,
  (poll) => {
    if (!poll) return;
    title.value = poll.title ?? '';
    question.value = poll.question ?? '';
    options.value = (poll.options ?? []).map(o => ({ id: o.id, label: o.label }));
    allowMultipleChoices.value = !!poll.allow_multiple_choices;
    resultsPublic.value = !!poll.results_public;
    durationMinutes.value = poll.duration ? Math.round(poll.duration / 60) : null;
  },
  { immediate: true }
);

const canSubmit = computed(
  () => question.value.trim() && options.value.filter(o => o.label.trim()).length >= 2
);

function addOption() {
  options.value.push({ id: null, label: '' });
}

function removeOption(index) {
  if (options.value.length > 2) options.value.splice(index, 1);
}

async function handleSubmit() {
  if (!canSubmit.value) return;
  validationErrors.value = {};
  try {
    await updatePoll(pollToEdit.value.id, {
      question: question.value.trim(),
      title: title.value.trim() || null,
      options: options.value
        .map(o => ({ id: o.id || null, label: o.label.trim() }))
        .filter(o => o.label),
      allow_multiple_choices: allowMultipleChoices.value,
      results_public: resultsPublic.value,
      duration: durationMinutes.value || null,
    });
    $q.notify({ message: 'Sondage modifié !', color: 'positive', position: 'top' });
    showEditDialog.value = false;
  } catch (err) {
    if (err?.status === 422 && err?.data?.errors) {
      validationErrors.value = err.data.errors;
    } else {
      const msg = err?.data?.message ?? 'Erreur lors de la modification.';
      $q.notify({ message: msg, color: 'negative', position: 'top' });
    }
  }
}
</script>

<template>
  <q-card style="min-width: 480px; max-width: 600px">
    <q-card-section>
      <div class="text-h6">Modifier le sondage</div>
    </q-card-section>

    <q-card-section class="q-pt-none q-gutter-md">
      <q-input
        v-model="title"
        label="Titre (optionnel)"
        outlined
        :error="!!validationErrors.title"
        :error-message="validationErrors.title?.[0]"
      />

      <q-input
        v-model="question"
        label="Question *"
        outlined
        :rules="[val => !!val.trim() || 'La question est obligatoire']"
        :error="!!validationErrors.question"
        :error-message="validationErrors.question?.[0]"
      />

      <div>
        <div class="text-subtitle2 q-mb-sm">Options de réponse * (minimum 2)</div>
        <div
          v-for="(option, i) in options"
          :key="i"
          class="row q-gutter-sm q-mb-sm items-center"
        >
          <q-input
            v-model="options[i].label"
            :label="`Option ${i + 1}`"
            outlined
            dense
            class="col"
            :error="!!validationErrors[`options.${i}.label`]"
            :error-message="validationErrors[`options.${i}.label`]?.[0]"
          />
          <q-btn
            flat round dense
            icon="remove_circle_outline"
            color="negative"
            :disable="options.length <= 2"
            @click="removeOption(i)"
          />
        </div>
        <q-btn flat dense icon="add" label="Ajouter une option" @click="addOption" />
      </div>

      <q-separator />

      <div class="text-subtitle2">Paramètres</div>

      <q-toggle v-model="allowMultipleChoices" label="Autoriser plusieurs choix" />
      <q-toggle v-model="resultsPublic" label="Résultats publics" />

      <q-input
        v-model.number="durationMinutes"
        type="number"
        label="Durée (minutes, optionnel)"
        outlined
        min="1"
        clearable
        :error="!!validationErrors.duration"
        :error-message="validationErrors.duration?.[0]"
      />
    </q-card-section>

    <q-card-actions align="right">
      <q-btn flat label="Annuler" @click="showEditDialog = false" />
      <q-btn
        color="primary"
        label="Enregistrer"
        :disable="!canSubmit"
        @click="handleSubmit"
      />
    </q-card-actions>
  </q-card>
</template>
