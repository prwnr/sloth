
require('./bootstrap');
window.Vue = require('vue');
require('./event-hub');

import store from './store'
import router from './routes'
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

    store,
    router,
})
