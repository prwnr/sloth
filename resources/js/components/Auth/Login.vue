<template>
    <div>
        <h2 class="text-center">Sign in!</h2>
        <form @submit.prevent="submit" class="login-form" @keydown="form.errors.clear($event.target.name)">
            <div class="form-group">
                <input id="email" type="email" class="form-control" :class="{ 'is-invalid': form.errors.has('email')}"
                       name="email" v-model="form.email" autofocus placeholder="E-mail">
                <form-error :text="form.errors.get('email')" :show="form.errors.has('email')"></form-error>
            </div>
            <div class="form-group">
                <input id="password" type="password" class="form-control" :class="{ 'is-invalid': form.errors.has('password')}"
                       name="password"  v-model="form.password" placeholder="Password">
                <form-error :text="form.errors.get('password')" :show="form.errors.has('password')"></form-error>
            </div>

            <div class="form-check mb-3">
                <label class="form-check-label">
                    <input type="checkbox" class="form-check-input" name="remember" v-model="form.remember_me">
                    Remember Me
                </label>
                <form-error :text="form.errors.get('remember_me')" :show="form.errors.has('remember_me')"></form-error>
            </div>
            <button class="btn btn-success btn-block">Login</button>
        </form>

        <div class="copy-text">
            <div class="d-block">
                <router-link class="mt-3" :to="{ name: 'signup' }" tag="a">Dont have account yet?</router-link>
            </div>
            <div class="d-block">
                <router-link class="mt-3" :to="{ name: 'password_reset' }" tag="a">Forgot Password?</router-link>
            </div>
        </div>
    </div>
</template>

<script>
    import {mapActions} from 'vuex';

    export default {
        name: "Login",

        data() {
            return {
                form: new Form({
                    email: '',
                    password: '',
                    remember_me: false
                })
            }
        },

        methods: {
            ...mapActions(['logIn', 'loadAuthUser']),
            async submit() {
                await this.logIn(this.form.data()).then(async (token) => {
                    this.$cookie.set('auth-token', token.access_token, { expires:  token.expires_at})
                    axios.defaults.headers.common['Authorization'] = `Bearer ${token.access_token}`
                    await this.loadAuthUser
                    this.$router.push('/')
                }).catch(error => {
                    if (error.response.status === 422) {
                        this.form.onFail(error.response.data.errors)
                    }

                    if (error.response.status === 401) {
                        this.$awn.alert(error.response.data.message)
                    }
                });
            }
        }
    }
</script>

<style scoped>

</style>