(function ($) {
    let currentPage = 1;
    let isLoading = false;
    let noMoreResults = false;
    let lastQuery = '';
    let debounceTimeout;

    $(document).ready(function () {
        const $searchInput = $('#bridge-directory-search-input');
        const $cardsContainer = $('#bridge-directory-cards');
        const $loader = $('#bridge-directory-loader');

        function loadResults(reset = false) {
            if (isLoading || noMoreResults) return;
            isLoading = true;
            $loader.show();

            $.ajax({
                url: bridgeDirectory.ajax_url,
                type: 'POST',
                data: {
                    action: 'bridge_directory_load_offices',
                    nonce: bridgeDirectory.nonce,
                    page: currentPage,
                    query: lastQuery,
                },
                success: function (response) {
                    if (reset) {
                        $cardsContainer.empty();
                        currentPage = 1;
                        noMoreResults = false;
                    }
                    if (response.success) {
                        const offices = response.data.offices;
                        if (offices.length === 0) {
                            noMoreResults = true;
                        } else {
                            appendResultsToGrid(offices);
                            currentPage++;
                        }
                    }
                },
                complete: function () {
                    isLoading = false;
                    $loader.hide();
                },
            });
        }

        function appendResultsToGrid(offices) {
            offices.forEach(function (office) {
                const parts = [];
                
                if (office.OfficePhone) parts.push(`<p>${office.OfficePhone}</p>`);
                if (office.OfficeEmail) parts.push(`<p>${office.OfficeEmail}</p>`);
                
                const address = `${office.OfficeAddress1 || ''} ${office.OfficeAddress2 || ''}, ${office.OfficeCity || ''}, ${office.OfficeStateOrProvince || ''} ${office.OfficePostalCode || ''}`.trim();
                if (address) parts.push(`<p>${address}</p>`);
                
                if (office.SocialMediaWebsiteUrlOrId) {
                    let url = office.SocialMediaWebsiteUrlOrId;
                    if (!/^https?:\/\//i.test(url)) {
                        url = `http://${url}`;
                    }
                    parts.push(`<p><a href="${url}" target="_blank">${url}</a></p>`);
                }
        
                const card = `
                    <div class="bridge-directory-card">
                        <h4>${office.OfficeName}</h4>
                        ${parts.join('')}
                    </div>
                `;
                
                $cardsContainer.append(card);
            });
        }

        function debounceSearch() {
            clearTimeout(debounceTimeout);
            debounceTimeout = setTimeout(function () {
                lastQuery = $searchInput.val();
                currentPage = 1;
                noMoreResults = false;
                loadResults(true);
            }, 300);
        }

        $searchInput.on('input', debounceSearch);

        $(window).on('scroll', function () {
            if ($(window).scrollTop() + $(window).height() >= $(document).height() - 100) {
                loadResults();
            }
        });

        // Initial load
        loadResults();
    });
})(jQuery);
