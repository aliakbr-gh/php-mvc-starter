<div class="page-loader" data-page-loader role="status" aria-live="polite">
    <span class="page-loader-spinner" aria-hidden="true"></span>
    <span class="sr-only">Loading page</span>
</div>

<noscript>
    <style>
        .page-loader {
            display: none;
        }
    </style>
</noscript>

<script>
    var pageLoaderStartedAt = Date.now();

    document.addEventListener('DOMContentLoaded', function () {
        var loader = document.querySelector('[data-page-loader]');

        if (!loader) {
            return;
        }

        var minimumDisplayTime = 250;
        var remainingTime = Math.max(0, minimumDisplayTime - (Date.now() - pageLoaderStartedAt));

        function showPageLoader() {
            loader.classList.remove('is-hidden');
            loader.setAttribute('aria-hidden', 'false');
        }

        function hidePageLoader() {
            loader.classList.add('is-hidden');
            loader.setAttribute('aria-hidden', 'true');
        }

        window.setTimeout(function () {
            hidePageLoader();
        }, remainingTime);

        document.addEventListener('submit', function (event) {
            if (event.defaultPrevented || event.target.target === '_blank' || event.target.method === 'dialog') {
                return;
            }

            showPageLoader();
        });

        document.addEventListener('click', function (event) {
            var link = event.target.closest('a[href]');

            if (!link || event.defaultPrevented || event.button !== 0 ||
                event.metaKey || event.ctrlKey || event.shiftKey || event.altKey ||
                link.target === '_blank' || link.hasAttribute('download')) {
                return;
            }

            var destination = new URL(link.href, window.location.href);
            var sameDocumentHash = destination.origin === window.location.origin &&
                destination.pathname === window.location.pathname &&
                destination.search === window.location.search &&
                destination.hash !== '';

            if (!sameDocumentHash && (destination.protocol === 'http:' || destination.protocol === 'https:')) {
                showPageLoader();
            }
        });

        window.addEventListener('pageshow', hidePageLoader);
    });
</script>
