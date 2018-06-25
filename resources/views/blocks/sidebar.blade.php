<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <router-link :to="{ name: 'dashboard' }" class="brand-link">
        <img src="{{ asset('images/static/semicket_white.png') }}" alt="" class="brand-image img-circle elevation-3"
             style="opacity: .8">
        <span class="brand-text font-weight-normal">Sloth</span>
    </router-link>

    <sidebar></sidebar>
</aside>