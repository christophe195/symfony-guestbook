function fetchCollection(path) {
    const ENV_API_ENDPOINT =`https://127.0.0.1:8000/`
    return fetch(ENV_API_ENDPOINT + path).then(resp => resp.json()).then(json => json['hydra:member']);
}

export function findConferences() {
    return fetchCollection('api/conferences');
}

export function findComments(conference) {
    return fetchCollection('api/comments?conference='+conference.id);
}