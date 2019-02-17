export default {
    namespaced: true,
    state: {
        items: [],
        active: [],
        lastLogs: [],
        day: ''
    },

    getters: {
        all(state) {
            return state.items
        },

        active(state) {
            return state.active
        },

        lastLogs(state) {
            return state.lastLogs.slice(0, 3)
        },

        totalTime(state) {
            let total = 0
            for (let log of state.items) {
                total += log.duration
            }

            return total
        },

        currentDay(state) {
            return state.day
        },
    },

    actions: {
        nextDay({state, commit, dispatch}) {
            return new Promise((resolve, reject) => {
                let next = moment(state.day).add(1, 'days').format('YYYY-MM-DD')
                commit("setDay", next)
                dispatch("fetch", next).then(response => {
                    resolve(response)
                }).catch(error => {
                    reject(error)
                })
            })
        },

        previousDay({state, commit, dispatch}) {
            return new Promise((resolve, reject) => {
                let previous = moment(state.day).subtract(1, 'days').format('YYYY-MM-DD')
                commit("setDay", previous)
                dispatch("fetch", previous).then(response => {
                    resolve(response)
                }).catch(error => {
                    reject(error)
                })
            })
        },

        currentDay({state, commit, dispatch}, day) {
            return new Promise((resolve, reject) => {
                commit("setDay", day)
                dispatch("fetch", day).then(response => {
                    resolve(response)
                }).catch(error => {
                    reject(error)
                })
            })
        },

        fetch({commit, rootState}, day) {
            return new Promise((resolve, reject) => {
                axios.get(`users/${rootState.authUser.get('id')}/logs`, {
                    params: {
                        date: day,
                    }
                }).then(response => {
                    commit("setLogs", response.data.data)
                    resolve(response.data.data)
                }).catch(error => {
                    reject(error)
                })
            })
        },

        fetchLastLogs({commit, rootState}, limit) {
            return new Promise((resolve, reject) => {
                axios.get(`users/${rootState.authUser.get('id')}/logs`, {
                    params: {
                        last: limit,
                        active: false
                    }
                }).then(response => {
                    commit("setLastLogs", response.data.data)
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
                        active: true,
                    }
                }).then(response => {
                    commit("setActive", response.data.data)
                    resolve(response.data.data)
                }).catch(error => {
                    reject(error)
                })
            })
        },

        create({state, dispatch}, log) {
            return new Promise((resolve, reject) => {
                axios.post('time', log).then(response => {
                    if (response.data.data.start) {
                        response.data.data.start = response.data.data.start.date;
                    } else {
                        response.data.data.start = null;
                    }

                    if (state.day === log.created_at) {
                        dispatch("add", response.data.data)
                    }

                    if (state.day !== log.created_at && response.data.data.start) {
                        dispatch("addActive", response.data.data)
                    }

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
                    let log = state.items.find(item => id === item.id);
                    let logs = state.active.filter(item => item.id !== id);
                    commit("setActive", logs)
                    dispatch("update", {
                        id: id,
                        data: {
                            duration: response.data.data.duration,
                            start: null
                        }
                    })
                    let lastLogs = state.lastLogs
                    lastLogs.unshift(log)
                    commit("setLastLogs", lastLogs)
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

        add({commit, dispatch, state}, log) {
            if (log.start) {
                dispatch("addActive", log)
            }

            if (!_.isUndefined(state.items.find(item => log.id === item.id))) {
                return
            }

            commit("addToLogs", log)
        },

        addActive({commit, state}, log) {
            if (!_.isUndefined(state.active.find(item => log.id === item.id))) {
                return
            }

            commit("addToActive", log)
        },

        remove({state, commit}, id) {
            commit("setLogs", state.items.filter(item => item.id !== id))
            commit("setActive", state.active.filter(item => item.id !== id))
        },

        update({state, commit}, {id, data}) {
            let logsToUpdate = []
            const log = state.items.find(item => item.id === id)
            if (!_.isUndefined(log)) {
                logsToUpdate.push(log)
            }

            const activeLog = state.active.find(item => item.id === id)
            if (!_.isUndefined(activeLog)) {
                logsToUpdate.push(activeLog)
            }

            logsToUpdate.forEach(item => {
                for (let [field, value] of Object.entries(data)) {
                    commit("updateLogDetails", {
                        log: item, field, value
                    })
                }
            })
        }
    },

    mutations: {
        setDay(state, day) {
            state.day = day
        },

        setLogs(state, logs) {
            state.items = logs
        },

        setActive(state, logs) {
            state.active = logs
        },

        setLastLogs(state, logs) {
            state.lastLogs = logs
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