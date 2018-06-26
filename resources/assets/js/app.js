
require('./bootstrap');

window.Vue = require('vue');

require('./classes/event-hub');

import router from './routes'
import VueSweetalert2 from 'vue-sweetalert2';
import Select2 from 'v-select2-component';
import Datatable from 'vue2-datatable-component';
import VueAWN from 'vue-awesome-notifications';
import 'vue-awesome-notifications/dist/styles/style.scss';

Vue.use(Datatable);
Vue.use(VueAWN, {
    position: 'bottom-right'
});
Vue.component('Select2', Select2);
Vue.use(VueSweetalert2);

Vue.component('auth-user', require('./components/blocks/AuthUser.vue'));
Vue.component('back-buttton', require('./components/blocks/BackButton.vue'));
Vue.component('form-error', require('./components/blocks/FormError.vue'));
Vue.component('loading', require('./components/blocks/Loading.vue'));
Vue.component('sidebar', require('./components/blocks/Sidebar.vue'));
Vue.component('card-header', require('./components/blocks/card/Header.vue'));
Vue.component('bootstrap-toggle', require('vue-bootstrap-toggle'));

const app = new Vue({
    el: '#app',

    created() {
        let self = this;        
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
    },

    router,
})
