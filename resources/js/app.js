
require('./bootstrap');
window.Vue = require('vue');
require('./event-hub');

import store from './store'
import router from './routes'
import VueCookie from 'vue-cookie';
import VueSweetalert2 from 'vue-sweetalert2';
import Select2 from 'v-select2-component';
import Datatable from 'vue2-datatable-component';
import VueAWN from 'vue-awesome-notifications';
import Form from './utilities/Form';
import AuthUser from './components/AuthUser';
import FormError from './components/FormError';
import Loading from './components/Loading';
import Sidebar from './components/Sidebar';
import ControlSidebar from './components/ControlSidebar';
import NavbarControlSidebar from './components/Navbar/ControlSidebar';
import CardHeader from './components/Card/Header';
import BootstrapToggle from 'vue-bootstrap-toggle';
import BlankLayout from './layouts/Blank'
import AppLayout from './layouts/App'

window.Form = Form;

Vue.use(Datatable);
Vue.use(VueAWN, {
    position: 'bottom-right'
});
Vue.component('Select2', Select2);
Vue.use(VueSweetalert2);
Vue.component('auth-user', AuthUser);
Vue.component('form-error', FormError);
Vue.component('loading', Loading);
Vue.component('sidebar', Sidebar);
Vue.component('control-sidebar', ControlSidebar);
Vue.component('navbar-control-sidebar', NavbarControlSidebar);
Vue.component('card-header', CardHeader);
Vue.component('bootstrap-toggle', BootstrapToggle);
Vue.component('blank-layout', BlankLayout);
Vue.component('app-layout', AppLayout);
Vue.use(VueCookie);

const default_layout = 'app';
const app = new Vue({
    el: '#app',
    store,
    router,

    computed: {
        layout() {
            return (this.$route.meta.layout || default_layout) + '-layout';
        },

        layoutClass() {
            return this.$route.meta.layout ? 'auth-page' : 'sidebar-mini sticky-footer';
        }
    },

    created() {
        if (!this.$cookie.get('auth-token')) {
            this.$router.push({ name: 'login' });
            return;
        }

        this.$store.commit('setAuthToken', this.$cookie.get('auth-token'))
        this.mountInterpreters()
    },

    methods: {
        mountInterpreters() {
            let self = this;
            axios.interceptors.request.use(function (config) {
                if (self.$store.getters.authToken) {
                    config.headers = { Authorization: `Bearer ${self.$store.getters.authToken}`};
                }

                return config;
            }, function (error) {
                return Promise.reject(error);
            });

            axios.interceptors.response.use(function (response) {
                return response;
            }, function (error) {
                if (!error.response) {
                    return Promise.reject(error);
                }

                if (error.response.status == 401 || error.response.status == 403) {
                    self.$router.push({ name: 'dashboard' });
                    error.message = 'Access forbidden.';
                    return Promise.reject(error);
                }

                let message = error.message || error.response.data.message;
                error.message = message;
                return Promise.reject(error);
            });
        }
    },
})
