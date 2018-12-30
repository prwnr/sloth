<template>
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
                   name="password_confirmation"
                   @keydown="form.errors.clear('password')">
            <span class="help-block text-danger"
                  v-html="form.errors.get('password_confirmation')"
                  v-show="form.errors.has('password_confirmation')"></span>
        </div>

        <button type="submit" class="btn btn-success" :disabled="form.errors.any()">Save</button>
    </form>
</template>

<script>
    export default {
        name: "Settings",
        props: ['user'],

        data() {
            return {
                form: new Form({
                    firstname: this.user.firstname,
                    lastname: this.user.lastname,
                    email: this.user.email,
                    password: '',
                    password_confirmation: '',
                    skin: 'dark'//TODO handle skin change
                })
            }
        },

        methods: {

            /**
             * Save user on form submit.
             */
            onSubmit() {
                this.$awn.async(
                    this.form.put('/api/users/' + this.user.id).then(response => {
                        this.$emit('user-updated', response.data);
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
        }
    }
</script>

<style scoped>

</style>