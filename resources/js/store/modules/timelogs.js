export default {
    namespaced: true,
    state: {
        items: [],
        active: []
    },

    getters: {
        all(state) {
            return state.items
        },

        active(state) {
            return state.active
        },

        totalTime(state) {
            let total = 0
            for (let log of state.items) {
                total += log.duration
            }

            return total
        }
    },

    actions: {
        fetchDay({commit, rootState}, day) {
            return new Promise((resolve, reject) => {
                axios.get(`users/${rootState.authUser.get('id')}/logs`, {
                    params: {
                        date: day
                    }
                }).then(response => {
                    commit("setLogs", response.data.data)
                    resolve(response.data.data)
                }).catch(error => {
                    reject(error)
                })
            })
        },

        fetchActive({commit, rootState}) {
            return new Promise((resolve, reject) => {
                axios.get(`users/${rootState.authUser.get('id')}/logs`, {
                    params: {
                        active: true
                    }
                }).then(response => {
                    commit("setActive", response.data.data)
                    resolve(response.data.data)
                }).catch(error => {
                    reject(error)
                })
            })
        },

        start({state, commit, dispatch}, {id, duration}) {
            return new Promise((resolve, reject) => {
                axios.put(`time/${id}/duration`, {
                    duration: duration,
                    time: 'start'
                }).then(response => {
                    dispatch("update", {
                        id: id,
                        data: {
                            start: response.data.data.start.date
                        }
                    })
                    commit("addToActive", state.items.find(item => item.id === id))
                    resolve(response.data.data)
                }).catch(error => {
                    reject(error)
                })
            })
        },

        stop({state, commit, dispatch}, {id, duration}) {
            return new Promise((resolve, reject) => {
                axios.put(`time/${id}/duration`, {
                    duration: duration,
                    time: 'stop'
                }).then(response => {
                    dispatch("update", {
                        id: id,
                        data: {
                            duration: response.data.data.duration,
                            start: null
                        }
                    })
                    let logs = state.active.filter(item => item.id !== id);
                    commit("setActive", logs)
                    resolve(response.data.data)
                }).catch(error => {
                    reject(error)
                })
            })
        },

        updateTime({dispatch}, {id, duration}) {
            return new Promise((resolve, reject) => {
                axios.put(`time/${id}/duration`, {
                    duration: duration,
                }).then(response => {
                    dispatch("update", {
                        id: id,
                        data: {
                            duration: response.data.data.duration
                        }
                    })
                    resolve(response.data.data)
                }).catch(error => {
                    reject(error)
                })
            })
        },

        add({commit}, log) {
            if (log.start) {
                commit("addToActive", log)
            }

            commit("addToLogs", log)
        },

        remove({state, commit}, id) {
            commit("setLogs", state.items.filter(item => item.id !== id))
            commit("setActive", state.active.filter(item => item.id !== id))
        },

        update({state, commit}, {id, data}) {
            const log = state.items.find(item => item.id === id)
            for (let [field, value] of Object.entries(data)) {
                commit("updateLogDetails", {
                    log, field, value
                })
            }
        }
    },

    mutations: {
        setLogs(state, logs) {
            state.items = logs
        },

        setActive(state, logs) {
            state.active = logs
        },

        addToLogs(state, log) {
            state.items.push(log)
        },

        addToActive(state, log) {
            state.active.push(log)
        },

        updateLogDetails(state, {log, field, value}) {
            log[field] = value
        }
    }
}