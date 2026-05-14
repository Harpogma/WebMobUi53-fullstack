import { ref } from "vue";
import { useFetchApi } from "../composables/useFetchApi";

export const polls = ref([]);
export const showCreateDialog = ref(false);

export function fetchPolls() {
    const { fetchApi } = useFetchApi();
    return fetchApi({ url: "/api/v1/polls" }).then((data) => {
        polls.value = data;
    });
}

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

export function createPoll(pollData) {
    const { fetchApi } = useFetchApi();

    return fetchApi({ url: "/polls", method: "POST", data: pollData }).then((data) => {
        polls.value.unshift(data);
    });
}
