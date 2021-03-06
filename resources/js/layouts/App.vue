<template>
    <div>
        <transition name="fade">
            <div class="wrapper" v-if="userLoaded">

                    <nav class="main-header navbar navbar-expand bg-white navbar-light border-bottom">
                        <ul class="navbar-nav">
                            <li class="nav-item">
                                <a class="nav-link" data-widget="pushmenu" href="#"><i class="fa fa-bars"></i></a>
                            </li>
                        </ul>

                        <ul class="navbar-nav ml-auto">
                            <li class="nav-item dropdown">
                                <a class="nav-link" data-toggle="dropdown" href="#">
                                    <i class="fa fa-user-o"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu dropdown-menu-right">
                                    <router-link :to="{ name: 'profile' }" class="dropdown-item" tag="a">
                                        <i class="fa fa-folder mr-2"></i>My account
                                    </router-link>
                                    <div class="dropdown-divider"></div>
                                    <a href="#" class="dropdown-item" @click.prevent="logout">
                                        <i class="fa fa-sign-out mr-2"></i>Sign out
                                    </a>
                                </div>
                            </li>

                            <li class="nav-item">
                                <navbar-control-sidebar></navbar-control-sidebar>
                            </li>
                        </ul>
                    </nav>

                    <aside class="main-sidebar sidebar-dark-primary elevation-4">
                        <router-link :to="{ name: 'dashboard' }" class="brand-link">
                            <img src="/images/static/sloth.png" alt="" class="brand-image img-circle elevation-3"
                                 style="opacity: .8">
                            <span class="brand-text font-weight-normal">Sloth</span>
                        </router-link>

                        <sidebar></sidebar>
                    </aside>

                    <div class="content-wrapper">
                        <slot></slot>
                    </div>
                    <footer class="main-footer">
                        <strong>Copyright &copy; <a href="https://semicket.com">semicket.com</a>.</strong> All rights reserved.
                        <small class="float-right pt-1">v1.1</small>
                    </footer>
                    <aside class="control-sidebar control-sidebar-dark">
                        <control-sidebar></control-sidebar>
                    </aside>
            </div>
        </transition>
        <div v-if="!userLoaded" class="background-blank">
            <div>
                <img src="/images/static/sloth.png" alt="" class="img-lg float-none">
                <p class="text-light text-center pt-2">Slothfully loading <i class="fa fa-refresh fa-spin fa-1x fa-fw"></i></p>
            </div>
        </div>
    </div>
</template>

<script>
    import {mapActions, mapGetters} from 'vuex'

    export default {
        name: "App",
        data() {
            return {
                loaded: false
            }
        },

        async created() {
            await this.$store.dispatch('loadAuthUser')
            if (this.authUser.get('first_login')) {
                this.showPasswordAlert();
            }
        },

        computed: {
            ...mapGetters(['authUser']),
            userLoaded() {
                return typeof this.authUser.data !== 'undefined'
            }
        },

        methods: {
            ...mapActions(['logOut']),
            logout() {
                let self = this
                this.logOut().then(() => {
                    self.$cookie.delete('auth-token')
                    axios.defaults.headers.common['Authorization'] = ''
                    this.$router.push('/login')
                }).catch(error => {
                    this.$awn.alert(error.message)
                })
            },

            showPasswordAlert() {
                this.$swal({
                    title: 'Change your password!',
                    type: 'warning',
                    html: 'You are using auto generated password. <br/>Change your password to be secure',
                    input: 'password',
                    inputAttributes: {
                        autocapitalize: 'off',
                        autocomplete: 'off'
                    },
                    showCancelButton: false,
                    confirmButtonText: 'Change',
                    confirmButtonColor: '#28a745',
                    showLoaderOnConfirm: true,
                    preConfirm: (password) => {
                        return axios.put('auth/password/change', {
                            password: password
                        }).then(response => {
                            return Promise.resolve();
                        }).catch(error => {
                            if (error.response.status === 422) {
                                this.$swal.showValidationMessage(_.first(error.response.data.errors.password));
                                return;
                            }

                            this.$swal.showValidationMessage(error.response.data.message);
                        })
                    },
                    allowOutsideClick: () => false
                }).then(() => {
                    this.$awn.success('Password changed')
                });
            }
        }
    }
</script>

<style scoped>
    .background-blank div {
        margin-top: 15%;
        font-size: 20px;
    }

    .background-blank img {
        display: block;
        margin: 0 auto;
    }

    .fade-enter-active, .fade-leave-active {
        transition: opacity 1.0s;
    }
    .fade-enter, .fade-leave-to /* .fade-leave-active below version 2.1.8 */ {
        opacity: 0;
    }

    .content-wrapper {
        min-height: calc(100vh - 112px) !important;
    }
</style>