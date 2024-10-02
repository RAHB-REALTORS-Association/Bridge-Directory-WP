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
                const card = `
                    <div class="bridge-directory-card">
                        <h3>${office.OfficeName}</h3>
                        <p><strong>Phone:</strong> ${office.OfficePhone || ''}</p>
                        <p><strong>Email:</strong> ${office.OfficeEmail || ''}</p>
                        <p><strong>Address:</strong> ${office.OfficeAddress1 || ''} ${office.OfficeAddress2 || ''}, ${office.OfficeCity || ''}, ${office.OfficeStateOrProvince || ''} ${office.OfficePostalCode || ''}</p>
                        ${office.SocialMediaWebsiteUrlOrId ? `<p><strong>Website:</strong> <a href="${office.SocialMediaWebsiteUrlOrId}" target="_blank">${office.SocialMediaWebsiteUrlOrId}</a></p>` : ''}
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
