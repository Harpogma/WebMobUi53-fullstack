import { ref } from 'vue';

export const polls = ref([]);

export function deletePoll(id) {
   console.log('deletePoll called with id:', id);
}
