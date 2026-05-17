<script setup>
import { deletePoll, startPoll, polls, showEditDialog, pollToEdit, showResultsDialog, pollToViewResults } from '@/store/polls';
import { useQuasar } from 'quasar';

const $q = useQuasar();

async function handleDelete(poll) {
  if (!confirm(`Êtes-vous sûr de vouloir supprimer le sondage "${poll.title || poll.question}" ?`)) return;

  try {
    $q.loading.show({ message: 'Suppression du sondage...' });
    await deletePoll(poll.id);
  } catch (error) {
    console.error(`Error deleting poll with id ${poll.id}:`, error);
  } finally {
    $q.loading.hide();
  }
}

async function handleStart(poll) {
  try {
    $q.loading.show({ message: 'Démarrage du sondage...' });
    await startPoll(poll.id);
    $q.notify({ message: 'Sondage démarré !', color: 'positive', position: 'top' });
  } catch (error) {
    console.error(`Error starting poll with id ${poll.id}:`, error);
    $q.notify({ message: 'Erreur lors du démarrage.', color: 'negative', position: 'top' });
  } finally {
    $q.loading.hide();
  }
}

function copyShareLink(poll) {
  const url = `${window.location.origin}/polls/${poll.secret_token}`;
  navigator.clipboard.writeText(url);
  $q.notify({ message: 'Lien copié !', color: 'positive', position: 'top' });
}
</script>

<template>
  <p v-if="polls.length === 0">Aucun sondage.</p>

  <div v-else class="q-gutter-md">
    <q-card v-for="poll in polls" :key="poll.id" flat bordered>
      <q-card-section class="row items-start justify-between no-wrap q-pb-xs">
        <div class="col">
          <div class="text-subtitle1 text-weight-medium">
            {{ poll.title || poll.question }}
          </div>
          <div v-if="poll.title" class="text-caption text-grey ellipsis">
            {{ poll.question }}
          </div>
        </div>
        <q-chip
          dense
          class="q-ml-sm q-mt-xs"
          :color="poll.is_draft ? 'warning' : (poll.ends_at && new Date(poll.ends_at) < new Date() ? 'grey' : 'positive')"
          text-color="white"
        >
          {{ poll.is_draft ? 'Brouillon' : (poll.ends_at && new Date(poll.ends_at) < new Date() ? 'Terminé' : 'Démarré') }}
        </q-chip>
      </q-card-section>

      <q-card-section v-if="poll.started_at || poll.ends_at" class="q-pt-none q-pb-xs row q-gutter-x-lg">
        <span v-if="poll.started_at" class="text-caption text-grey">
          Début : {{ poll.started_at }}
        </span>
        <span v-if="poll.ends_at" class="text-caption text-grey">
          Fin : {{ poll.ends_at }}
        </span>
      </q-card-section>

      <q-separator />

      <q-card-actions class="q-gutter-xs">
        <q-btn
          v-if="poll.is_draft"
          round flat color="positive" icon="play_arrow" title="Démarrer le sondage"
          @click="handleStart(poll)"
        />
        <q-btn
          v-if="!poll.is_draft"
          round flat color="secondary" icon="bar_chart" title="Voir les résultats"
          @click="pollToViewResults = poll; showResultsDialog = true"
        />
        <q-btn round flat color="primary" icon="share" title="Copier le lien de partage" @click="copyShareLink(poll)" />
        <q-btn round flat color="primary" icon="edit" title="Modifier" @click="pollToEdit = poll; showEditDialog = true" />
        <q-btn round flat color="negative" icon="delete" title="Supprimer" @click="handleDelete(poll)" />
      </q-card-actions>
    </q-card>
  </div>
</template>
