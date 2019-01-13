import Vue from 'vue'
import Vuex from 'vuex'
import User from '../models/User'

import timelogs from './modules/timelogs'

Vue.use(Vuex);

export default new Vuex.Store({
    modules: {
        timelogs
    },

    state: {
        authToken: '',
        authUser: {}
    },

    getters: {
        authToken(state) {
            return state.authToken
        },
        authUser(state) {
            return state.authUser
        }
    },

    mutations: {
        setAuthToken(state, token) {
            state.authToken = token
        },
        setAuthUser(state, user) {
            state.authUser = user
        },
    },

    actions: {
        logIn({commit}, user) {
            return new Promise((resolve, reject) => {
                axios.post('api/auth/login', {
                    email: user.email,
                    password: user.password,
                    remember_me: user.remember_me
                }).then(response => {
                    let token = response.data;
                    commit('setAuthToken', token.access_token)
                    resolve(token)
                }).catch(error => {
                    reject(error)
                });
            });
        },

        logOut({commit}) {
            return new Promise((resolve, reject) => {
                axios.get('api/auth/logout').then(response => {
                    commit('setAuthToken', '')
                    commit('setAuthUser', {})
                    resolve()
                }).catch(error => {
                    reject(error)
                });
            });
        },

        async loadAuthUser({commit}) {
            return await axios.get('api/auth/user').then(response => {
                let user = new User(response.data)
                commit('setAuthUser', user)
                return Promise.resolve()
            }).catch(error => {
                return Promise.reject(error)
            })
        }
    }
})