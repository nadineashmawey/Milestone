{{--
    Dark mode before the first paint, so a dark page never flashes light. Included once, in layouts/base (every page).
    The saved choice wins ("milestone-theme": dark or light); with none, the system setting decides.
    It sets class="dark" and data-theme on <html>; tokens.css switches every colour from there. The toggle is in navbar.js.
--}}
<script>
    (function () {
        var saved = null;
        try { saved = localStorage.getItem('milestone-theme'); } catch (e) {}
        var dark = saved ? saved === 'dark' : window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
        var root = document.documentElement;
        root.classList.toggle('dark', dark);
        root.dataset.theme = dark ? 'dark' : 'light';
    })();
</script>
