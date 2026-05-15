import { ref } from "vue";
import { useFetchApi } from "../composables/useFetchApi";

export const polls = ref([]);
export const showCreateDialog = ref(false);
export const showEditDialog = ref(false);
export const pollToEdit = ref(null);
export const showResultsDialog = ref(false);
export const pollToViewResults = ref(null);

export function fetchPolls() {
    const { fetchApi } = useFetchApi();
    return fetchApi({ url: "/api/v1/polls" }).then((data) => {
        polls.value = data;
    });
}

export function deletePoll(id) {
    const { fetchApi } = useFetchApi();

    return fetchApi({ url: `/poll/${id}`, method: "DELETE" }).then(() => {
        const index = polls.value.findIndex((poll) => poll.id === id);
        if (index !== -1) {
            polls.value.splice(index, 1);
        }
    });
}

export function createPoll(pollData) {
    const { fetchApi } = useFetchApi();

    return fetchApi({ url: "/polls", method: "POST", data: pollData }).then((data) => {
        polls.value.unshift(data);
        return data;
    });
}

export function updatePoll(id, pollData) {
    const { fetchApi } = useFetchApi();

    return fetchApi({ url: `/polls/${id}`, method: 'PATCH', data: pollData }).then((data) => {
        const index = polls.value.findIndex((p) => p.id === id);
        if (index !== -1) polls.value[index] = data;
        return data;
    });
}

export function startPoll(id) {
    const { fetchApi } = useFetchApi();

    return fetchApi({ url: `/polls/${id}/start`, method: "POST" }).then((data) => {
        const index = polls.value.findIndex((p) => p.id === id);
        if (index !== -1) polls.value[index] = data;
        return data;
    });
}
