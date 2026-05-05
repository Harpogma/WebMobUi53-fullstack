import { ref } from "vue";
import { useFetchApi } from "../composables/useFetchApi";

export const polls = ref([]);

export function deletePoll(id) {
    // Delete the poll in the backend

    const { fetchApi } = useFetchApi();

    return fetchApi({ url: `/poll/${id}`, method: "DELETE" }).then(() => {
        // Delete poll in the frontend
        const index = polls.value.findIndex((poll) => poll.id === id);
        if (index !== -1) {
            polls.value.splice(index, 1);
        }
        console.log(`Poll with id ${id} deleted successfully.`);
    });
}
