<template>
<div class="card mb-3">
    <div class="card-body">
        <form @submit.prevent="onSubmit"
            @keydown="form.errors.clear($event.target.name)">
            <div class="form-group">
                <label for="firstname">First name</label>
                <input id="firstname" v-model="form.firstname" type="text"
                    class="form-control"
                    name="firstname" required autofocus>
                <span class="help-block text-danger"
                    v-html="form.errors.get('firstname')" v-show="form.errors.has('firstname')"></span>
            </div>
            <div class="form-group">
                <label for="lastname">Last name</label>
                <input id="lastname" v-model="form.lastname" type="text"
                    class="form-control"
                    name="lastname" required autofocus>
                <span class="help-block text-danger"
                    v-html="form.errors.get('lastname')" v-show="form.errors.has('lastname')"></span>
            </div>
            <div class="form-group">
                <label for="email">E-mail address</label>
                <input id="email" v-model="form.email" type="email"
                    class="form-control"
                    name="email" required>
                <span class="help-block text-danger"
                    v-html="form.errors.get('email')" v-show="form.errors.has('email')"></span>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input id="password" v-model="form.password" type="password"
                    class="form-control"
                    name="password">
                <span class="help-block text-danger"
                    v-html="form.errors.get('password')" v-show="form.errors.has('password')"></span>
            </div>
            <div class="form-group">
                <label for="password-confirm">Confirm password</label>
                <input id="password-confirm" v-model="form.password_confirmation" type="password"
                    class="form-control"
                    name="password_confirmation">
                <span class="help-block text-danger"
                    v-html="form.errors.get('password_confirmation')"
                    v-show="form.errors.has('password_confirmation')"></span>
            </div>
            <div class="form-group">
                <label>Choose your theme skin:
                    <bootstrap-toggle v-model="checked" :disabled="true" :options="{
                        on: 'Dark skin',
                        off: 'Light skin',
                        onstyle: 'primary',
                        offstyle: 'default',
                        style: 'disabled',
                        size: 'small' }"/>
                </label>
            </div>

            <button type="submit" class="btn btn-success" :disabled="form.errors.any()">Save</button>
        </form>
    </div>
</div>
</template>

<script>
    export default {
        data() {
            return {
                user: {},
                loaded: false,
                checked: true,
                form: new Form({
                    skin: '',
                    firstname: '',
                    lastname: '',
                    email: '',
                    password: '',
                    password_confirmation: ''
                })
            }
        },

        created() {
            this.fetchUser();
        },

        destroyed() {
            if (this.user.skin !== this.form.skin) {
                this.toggleSkin();
            }
        },

        watch: {
            checked: function() {
                if (!this.loaded) {
                    return true;
                }

                this.form.skin = this.checked ? 'dark' : 'light';
                this.toggleSkin();
            }
        },

        methods: {
            toggleSkin() {
                jQuery('nav').toggleClass('navbar-dark navbar-light');
                jQuery('nav').toggleClass('bg-dark bg-light');
                jQuery('body').toggleClass('bg-dark bg-light');
                jQuery('.navbar-brand > div').toggleClass('skin-dark-logo skin-light-logo');
            },

            /**
             * Load user data
             */
            fetchUser() {
                this.loaded = false;
                axios.get('/api/users/active').then(response => {
                    this.user = response.data.data;
                    this.checked = this.user.skin === 'dark' ? true : false;

                    this.form.skin = this.user.skin;
                    this.form.firstname = this.user.firstname;
                    this.form.lastname = this.user.lastname;
                    this.form.email = this.user.email;
                }).catch(error => {
                    this.$awn.alert(error.message);
                }).finally(() => {
                    this.loaded = true;
                });
            },

            /**
             * Save user on form submit.
             */
            onSubmit() {
                this.$awn.async(
                    this.form.put('/api/users/' + this.user.id).then(response => {
                        this.user = response.data;                        
                        this.form.updateOriginalData();
                        this.resetPasswordData();
                        this.$awn.success('Your account has been updated.');                        
                    }).catch(error => {
                        this.$awn.alert('Failed to update acccount details. Check your form fields');
                    })    
                );
            },

            /**
             * Reset password data
             */
            resetPasswordData() {
                this.form.password = '';
                this.form.password_confirmation = '';
            }
        },
    }
</script>
