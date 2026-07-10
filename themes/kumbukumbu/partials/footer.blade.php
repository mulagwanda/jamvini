<footer class="kmb-footer">
    <div class="kmb-container">
        <div class="kmb-footer-grid">
            <div>
                <a href="/" class="kmb-logo kmb-logo-light">
                    <span>{{ substr(\App\Models\Setting::get('company_name', 'J'), 0, 1) }}</span>
                    {{ \App\Models\Setting::get('company_name', 'JamVini Hosting') }}
                </a>
                <p>{{ \App\Models\Setting::get('site_tagline', 'Open hosting business management for builders.') }}</p>
            </div>
            <div>
                <h4>Services</h4>
                <a href="/hosting">Web Hosting</a>
                <a href="/domains">Domains</a>
                <a href="/contact">Support</a>
            </div>
            <div>
                <h4>Company</h4>
                <a href="/about">About</a>
                <a href="/blog">Blog</a>
                <a href="/contact">Contact</a>
            </div>
            <div>
                <h4>Client Area</h4>
                <a href="/login">Login</a>
                <a href="/register">Create Account</a>
                <a href="/cart">Cart</a>
            </div>
        </div>
        <div class="kmb-copyright">
            {{ jv_theme_setting('footer_text', 'Built with JamVini. Remember the work. Keep building.', 'public') }}
            @if(jv_theme_setting('show_powered_by', '1', 'public'))
                <span>Powered by JamVini.</span>
            @endif
        </div>
    </div>
</footer>
