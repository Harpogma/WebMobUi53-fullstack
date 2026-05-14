<script setup>
import { ref } from 'vue';
import { createPoll, showCreateDialog } from '@/store/polls';

const question = ref('');
const title = ref('');

async function handleSubmit() {
  if (!question.value.trim()) return;
  try {
    await createPoll({ question: question.value.trim(), title: title.value.trim() || null });
    question.value = '';
    title.value = '';
    showCreateDialog.value = false;
  } catch (error) {
    console.error('Error creating poll:', error);
  }
}

function handleCancel() {
  question.value = '';
  title.value = '';
  showCreateDialog.value = false;
}
</script>

<template>
  <q-card style="min-width: 400px">
    <q-card-section>
      <div class="text-h6">Nouveau sondage</div>
    </q-card-section>

    <q-card-section class="q-pt-none q-gutter-md">
      <q-input
        v-model="title"
        label="Titre (optionnel)"
        outlined
        autofocus
      />
      <q-input
        v-model="question"
        label="Question *"
        outlined
        :rules="[val => !!val.trim() || 'La question est obligatoire']"
      />
    </q-card-section>

    <q-card-actions align="right">
      <q-btn flat label="Annuler" @click="handleCancel" />
      <q-btn color="primary" label="Créer" :disable="!question.trim()" @click="handleSubmit" />
    </q-card-actions>
  </q-card>
</template>
