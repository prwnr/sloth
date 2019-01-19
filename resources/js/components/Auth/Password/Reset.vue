<template>
    <div>
        <h2 class="text-center">Reset password</h2>
        <form @submit.prevent="submit">
            <div class="form-group">
                <input id="email"
                       type="email"
                       class="form-control"
                       :class="{ 'is-invalid': form.errors.has('email')}"
                       name="email"
                       autofocus
                       placeholder="E-Mail Address"
                       v-model="form.email">

                <form-error
                        :text="form.errors.get('email')"
                        :show="form.errors.has('email')">
                </form-error>
            </div>

            <div class="form-group">
                <input id="password"
                       type="password"
                       class="form-control"
                       :class="{ 'is-invalid': form.errors.has('password')}"
                       name="password"
                       placeholder="Password"
                       v-model="form.password">

                <form-error
                        :text="form.errors.get('password')"
                        :show="form.errors.has('password')">
                </form-error>
            </div>

            <div class="form-group">
                <input id="password-confirm"
                       type="password"
                       class="form-control"
                       :class="{ 'is-invalid': form.errors.has('password_confirmation')}"
                       name="password_confirmation"
                       placeholder="Confirm Password"
                       v-model="form.password_confirmation">
            </div>

            <button type="submit" class="btn btn-success btn-block">Reset Password</button>
        </form>

        <div class="copy-text">
            <div class="d-block">
                <router-link
                        :to="{ name: 'login' }"
                        class="mt-3"
                        tag="a">
                    Remember password? Log in!
                </router-link>
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
                    email: '',
                    password: '',
                    password_confirmation: '',
                    token: ''
                })
            }
        },

        methods: {
            submit() {
                this.form.token = this.$route.query.token;
                this.form.post('auth/password/reset').then(response => {
                    this.$router.push({ name: 'login' });
                    this.$awn.success('Password has been changed. You can now log in!');
                }).catch(error => {
                    this.$awn.alert(error.message);
                });
            }
        }
    }
</script>

<style scoped>

</style>