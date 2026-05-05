<script setup>
import { deletePoll, polls} from '@/store/polls';
import { useQuasar } from "quasar";

    const q = useQuasar();


  async function handleDelete(poll) {
    if (!confirm(`Êtes-vous sûr de vouloir supprimer le sondage "${poll.title}" ?`)) return;

    try {
        q.loading.show({
            message: "Suppression du sondage...",
        });
        await deletePoll(poll.id);
    } catch (error) {
        console.error(`Error deleting poll with id ${poll.id}:`, error);
    } finally {
        console.log("Suppression terminée.");
        q.loading.hide();
    }
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
        <th class="border px-3 py-2">Brouillon</th>
        <th class="border px-3 py-2">Debut</th>
        <th class="border px-3 py-2">Fin</th>
        <th class="border px-3 py-2">Actions</th>
      </tr>
    </thead>
    <tbody>
      <tr v-for="poll in polls" :key="poll.id">
        <td class="border px-3 py-2">{{ poll.id }}</td>
        <td class="border px-3 py-2">{{ poll.title || '-' }}</td>
        <td class="border px-3 py-2">{{ poll.question }}</td>
        <td class="border px-3 py-2">{{ poll.is_draft ? 'Oui' : 'Non' }}</td>
        <td class="border px-3 py-2">{{ poll.started_at || '-' }}</td>
        <td class="border px-3 py-2">{{ poll.ends_at || '-' }}</td>
        <td class="border px-3 py-2">
        <q-btn round color="primary" icon="edit" />
        <q-btn round color="negative" icon="delete" @click="handleDelete(poll)" />
        </td>
      </tr>
    </tbody>
  </table>
</template>
