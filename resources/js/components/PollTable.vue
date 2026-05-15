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

  <table v-else class="w-full border-collapse text-left">
    <thead>
      <tr>
        <th class="border px-3 py-2">ID</th>
        <th class="border px-3 py-2">Titre</th>
        <th class="border px-3 py-2">Question</th>
        <th class="border px-3 py-2">Statut</th>
        <th class="border px-3 py-2">Début</th>
        <th class="border px-3 py-2">Fin</th>
        <th class="border px-3 py-2">Actions</th>
      </tr>
    </thead>
    <tbody>
      <tr v-for="poll in polls" :key="poll.id">
        <td class="border px-3 py-2">{{ poll.id }}</td>
        <td class="border px-3 py-2">{{ poll.title || '-' }}</td>
        <td class="border px-3 py-2">{{ poll.question }}</td>
        <td class="border px-3 py-2">{{ poll.is_draft ? 'Brouillon' : 'Démarré' }}</td>
        <td class="border px-3 py-2">{{ poll.started_at || '-' }}</td>
        <td class="border px-3 py-2">{{ poll.ends_at || '-' }}</td>
        <td class="border px-3 py-2 q-gutter-xs">
          <q-btn
            v-if="poll.is_draft"
            round flat
            color="positive"
            icon="play_arrow"
            title="Démarrer le sondage"
            @click="handleStart(poll)"
          />
          <q-btn
            v-if="!poll.is_draft"
            round flat
            color="secondary"
            icon="bar_chart"
            title="Voir les résultats"
            @click="pollToViewResults = poll; showResultsDialog = true"
          />
          <q-btn
            round flat
            color="primary"
            icon="share"
            title="Copier le lien de partage"
            @click="copyShareLink(poll)"
          />
          <q-btn
            round flat
            color="primary"
            icon="edit"
            title="Modifier"
            @click="pollToEdit = poll; showEditDialog = true"
          />
          <q-btn round flat color="negative" icon="delete" title="Supprimer" @click="handleDelete(poll)" />
        </td>
      </tr>
    </tbody>
  </table>
</template>
