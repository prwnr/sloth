<template>
    <div>
        <h2 class="text-center">Reset password</h2>
        <form @submit.prevent="submit" @keydown="form.errors.clear($event.target.name)">
            <div class="text-center mt-4 mb-3">
                <h4>Forgot your password?</h4>
                <p>Enter your email address and we will send you instructions on how to reset your password.</p>
            </div>
            <div class="form-group">
                <input id="email" type="email" class="form-control" :class="{ 'is-invalid': form.errors.has('email')}"
                       name="email" v-model="form.email" placeholder="E-Mail Address">

                <form-error :text="form.errors.get('email')" :show="form.errors.has('email')"></form-error>
            </div>
            <button type="submit" class="btn btn-success btn-block">Send Password Reset Link</button>
        </form>
        <div class="copy-text">
            <div class="d-block">
                <router-link class="mt-3" :to="{ name: 'signup' }" tag="a">Dont have account yet?</router-link>
            </div>
            <div class="d-block">
                <router-link class="mt-3" :to="{ name: 'login' }" tag="a">Already have account? Log in!</router-link>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "PasswordReset",

        data() {
            return {
                form: new Form({
                    email: ''
                })
            }
        },

        methods: {
            submit() {
                this.form.post('/api/auth/password/reset').then(response => {
                    this.$router.push({ name: 'login' });
                    this.$awn.success('Password reset email has been sent to your address');
                }).catch(error => {
                    this.$awn.alert(error.message);
                });
            }
        }
    }
</script>

<style scoped>

</style>