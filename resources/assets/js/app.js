
require('./bootstrap');

window.Vue = require('vue');

import Form from './utilities/Form';
window.Form = Form;

require('./event-hub');

import router from './routes'
import VueSweetalert2 from 'vue-sweetalert2';
import Select2 from 'v-select2-component';
import Datatable from 'vue2-datatable-component';
import VueAWN from 'vue-awesome-notifications';
import VueMask from 'v-mask'

Vue.use(Datatable);
Vue.use(VueAWN, {
    position: 'bottom-right'
});
Vue.component('Select2', Select2);
Vue.use(VueSweetalert2);
Vue.use(VueMask);

Vue.component('auth-user', require('./components/AuthUser.vue'));
Vue.component('back-button', require('./components/BackButton.vue'));
Vue.component('form-error', require('./components/FormError.vue'));
Vue.component('loading', require('./components/Loading.vue'));
Vue.component('sidebar', require('./components/Sidebar.vue'));
Vue.component('card-header', require('./components/Card/Header.vue'));
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

            console.log(error);
            let message = error.message || error.response.data.message;
            error.message = message;
            return Promise.reject(error);
        });
    },

    router,
})
