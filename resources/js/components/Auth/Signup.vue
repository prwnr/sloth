<template>
    <div>
        <h2 class="text-center">Create your account</h2>
        <form @submit.prevent="submit" @keydown="form.errors.clear($event.target.name)">
            <div class="form-group">
                <div class="form-row">
                    <div class="col-md-6">
                        <input id="firstname" type="text" class="form-control" :class="{ 'is-invalid': form.errors.has('email')}"
                               name="firstname" v-model="form.firstname" autofocus placeholder="First name">
                    </div>
                    <div class="col-md-6">
                        <input id="lastname" type="text" class="form-control" :class="{ 'is-invalid': form.errors.has('email')}"
                               name="lastname" v-model="form.lastname" autofocus placeholder="Last name">
                    </div>

                    <form-error :text="form.errors.get('firstname')" :show="form.errors.has('firstname')"></form-error>
                    <form-error :text="form.errors.get('lastname')" :show="form.errors.has('lastname')"></form-error>
                </div>
            </div>

            <div class="form-group">
                <input id="team_name" type="text" class="form-control" :class="{ 'is-invalid': form.errors.has('email')}"
                       name="team_name" v-model="form.team_name" placeholder="Team name">
                <form-error :text="form.errors.get('team_name')" :show="form.errors.has('team_name')"></form-error>
            </div>

            <div class="form-group">
                <input id="email" type="email" class="form-control" :class="{ 'is-invalid': form.errors.has('email')}"
                       name="email" v-model="form.email" placeholder="E-mail address">
                <form-error :text="form.errors.get('email')" :show="form.errors.has('email')"></form-error>
            </div>
            <div class="form-group">
                <div class="form-row">
                    <div class="col-md-6">
                        <input id="password" type="password" class="form-control" :class="{ 'is-invalid': form.errors.has('email')}"
                               name="password" v-model="form.password" placeholder="Password">
                        <form-error :text="form.errors.get('password')" :show="form.errors.has('password')"></form-error>
                    </div>
                    <div class="col-md-6">
                        <input id="password-confirm" type="password" v-model="form.password_confirmation" :class="{ 'is-invalid': form.errors.has('email')}"
                               class="form-control" name="password_confirmation" placeholder="Confirm password">
                        <form-error :text="form.errors.get('password_confirmation')" :show="form.errors.has('password_confirmation')"></form-error>
                    </div>
                </div>
            </div>
            <button class="btn btn-success btn-block float-right">Register</button>
        </form>
        <div class="copy-text">
            <div class="d-block">
                <router-link class="mt-3" :to="{ name: 'login' }" tag="a">Already have account? Log in!</router-link>
            </div>
            <div class="d-block">
                <router-link class="mt-3" :to="{ name: 'password_forgot' }" tag="a">Forgot Password?</router-link>
            </div>
        </div>
    </div>
</template>

<script>
    import {mapActions} from 'vuex'

    export default {
        name: "Signup",

        data() {
            return {
                form: new Form({
                    firstname: '',
                    lastname: '',
                    email: '',
                    password: '',
                    password_confirmation: '',
                    team_name: ''
                })
            }
        },

        methods: {
            ...mapActions(['logIn']),
            async submit() {
                this.form.password_confirmation = this.form.password
                await this.form.post('auth/signup').then(async (response) => {
                    await this.logIn(this.form.data()).then(async (token) => {
                        this.$cookie.set('auth-token', token.access_token, { expires:  token.expires_at})
                        axios.defaults.headers.common['Authorization'] = `Bearer ${token.access_token}`
                        await this.loadAuthUser
                        this.$router.push('/')
                    }).catch(error => {
                        this.$awn.alert(error.message)
                    })
                }).catch(error => {
                    if (!error.response) {
                        this.$awn.alert(error.message);
                        return;
                    }

                    if (error.response.status === 401) {
                        this.$awn.alert(error.response.data.message);
                    }
                })
            }
        }
    }
</script>

<style scoped>

</style>