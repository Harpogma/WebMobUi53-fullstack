<script setup>
import { ref, computed } from 'vue';
import { createPoll, startPoll, showCreateDialog } from '@/store/polls';
import { useQuasar } from 'quasar';

const $q = useQuasar();

const title = ref('');
const question = ref('');
const options = ref(['', '']);
const allowMultipleChoices = ref(false);
const resultsPublic = ref(false);
const durationMinutes = ref(null);
const startNow = ref(false);

const createdPoll = ref(null);
const shareUrl = computed(() =>
  createdPoll.value ? `${window.location.origin}/polls/${createdPoll.value.secret_token}` : ''
);

const canSubmit = computed(() =>
  question.value.trim() && options.value.filter(o => o.trim()).length >= 2
);

function addOption() {
  options.value.push('');
}

function removeOption(index) {
  if (options.value.length > 2) options.value.splice(index, 1);
}

async function handleSubmit() {
  if (!canSubmit.value) return;
  try {
    const poll = await createPoll({
      question: question.value.trim(),
      title: title.value.trim() || null,
      options: options.value.map(o => o.trim()).filter(Boolean),
      allow_multiple_choices: allowMultipleChoices.value,
      results_public: resultsPublic.value,
      duration: durationMinutes.value || null,
      start_now: startNow.value,
    });
    createdPoll.value = poll;
  } catch (error) {
    console.error('Error creating poll:', error);
    $q.notify({ message: 'Erreur lors de la création du sondage.', color: 'negative', position: 'top' });
  }
}

async function handleStart() {
  try {
    const updated = await startPoll(createdPoll.value.id);
    createdPoll.value = updated;
    $q.notify({ message: 'Sondage démarré !', color: 'positive', position: 'top' });
  } catch (error) {
    console.error('Error starting poll:', error);
    $q.notify({ message: 'Erreur lors du démarrage du sondage.', color: 'negative', position: 'top' });
  }
}

async function copyShareLink() {
  await navigator.clipboard.writeText(shareUrl.value);
  $q.notify({ message: 'Lien copié !', color: 'positive', position: 'top' });
}

function handleClose() {
  title.value = '';
  question.value = '';
  options.value = ['', ''];
  allowMultipleChoices.value = false;
  resultsPublic.value = false;
  durationMinutes.value = null;
  startNow.value = false;
  createdPoll.value = null;
  showCreateDialog.value = false;
}
</script>

<template>
  <q-card style="min-width: 480px; max-width: 600px">

    <!-- Success: show share link -->
    <template v-if="createdPoll">
      <q-card-section>
        <div class="text-h6">Sondage créé !</div>
        <div class="text-caption text-grey q-mt-xs">
          {{ createdPoll.is_draft ? 'Le sondage est en mode brouillon.' : 'Le sondage est démarré.' }}
        </div>
      </q-card-section>

      <q-card-section class="q-pt-none">
        <div class="text-subtitle2 q-mb-sm">Lien de partage</div>
        <q-input :model-value="shareUrl" readonly outlined dense>
          <template #append>
            <q-btn flat dense icon="content_copy" @click="copyShareLink" />
          </template>
        </q-input>
      </q-card-section>

      <q-card-actions align="right">
        <q-btn flat label="Fermer" @click="handleClose" />
        <q-btn
          v-if="createdPoll.is_draft"
          color="primary"
          label="Démarrer maintenant"
          @click="handleStart"
        />
      </q-card-actions>
    </template>

    <!-- Form -->
    <template v-else>
      <q-card-section>
        <div class="text-h6">Nouveau sondage</div>
      </q-card-section>

      <q-card-section class="q-pt-none q-gutter-md">
        <q-input v-model="title" label="Titre (optionnel)" outlined />

        <q-input
          v-model="question"
          label="Question *"
          outlined
          :rules="[val => !!val.trim() || 'La question est obligatoire']"
        />

        <div>
          <div class="text-subtitle2 q-mb-sm">Options de réponse * (minimum 2)</div>
          <div
            v-for="(_, i) in options"
            :key="i"
            class="row q-gutter-sm q-mb-sm items-center"
          >
            <q-input
              v-model="options[i]"
              :label="`Option ${i + 1}`"
              outlined
              dense
              class="col"
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
        />

        <q-toggle v-model="startNow" label="Démarrer immédiatement" />
      </q-card-section>

      <q-card-actions align="right">
        <q-btn flat label="Annuler" @click="handleClose" />
        <q-btn
          color="primary"
          :label="startNow ? 'Créer et démarrer' : 'Créer en brouillon'"
          :disable="!canSubmit"
          @click="handleSubmit"
        />
      </q-card-actions>
    </template>

  </q-card>
</template>
