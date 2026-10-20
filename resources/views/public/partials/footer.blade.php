<footer class="hp-footer">
    <div class="hp-footer-brand">
        <img class="hp-footer-logo" src="{{ asset('images/milestone-logo.png') }}" alt="Milestone" width="640" height="149">
        <p>Clear projects. Stronger teams. Better outcomes.</p>
    </div>
    <nav class="hp-footer-nav" aria-label="Footer">
        <div>
            <h2>Product</h2>
            <a href="{{ route('home') }}#how">How it works</a>
            <a href="{{ route('home') }}#types">Project types</a>
            <a href="{{ route('home') }}#roles">Roles</a>
        </div>
        <div>
            <h2>Support</h2>
            <a href="{{ route('help') }}">Help center</a>
            <a href="{{ route('help') }}#report">Report a problem</a>
            <a href="{{ route('home') }}#faq">FAQ</a>
        </div>
        <div>
            <h2>Account</h2>
            @auth
                <a href="{{ $accountLinks['dashboard'] }}">Go to dashboard</a>
            @else
                <a href="{{ $accountLinks['login'] }}">Sign in</a>
                <a href="{{ $accountLinks['register'] }}">Create account</a>
            @endauth
        </div>
    </nav>
    <small>© {{ now()->year }} Milestone · Software Engineering 26F, Misr International University · Prototype</small>
</footer>
