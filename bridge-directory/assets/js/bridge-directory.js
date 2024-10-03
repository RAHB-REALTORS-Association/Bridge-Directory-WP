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

                const phone = office.OfficePhoneNormalized && office.OfficePhoneNormalized !== "null"
                    ? `<a href="tel:${office.OfficePhoneNormalized}">${office.OfficePhone}</a>`
                    : office.OfficePhone;
                parts.push(`<p>${phone}</p>`);

                if (office.OfficeEmail && office.OfficeEmail !== "none@onmls.ca" && office.OfficeEmail !== "null") {
                    parts.push(`<p><a href="mailto:${office.OfficeEmail}">${office.OfficeEmail}</a></p>`);
                }

                let address = `<p>${office.OfficeAddress1 !== "null" ? office.OfficeAddress1 : ""}</p>`;
                if (office.OfficeAddress2 && office.OfficeAddress2 !== "null") {
                    address += `${office.OfficeAddress2}`;
                }
                address += `${office.OfficeCity !== "null" ? office.OfficeCity : ""}, ${office.OfficeStateOrProvince !== "null" ? office.OfficeStateOrProvince : ""} ${office.OfficePostalCode !== "null" ? office.OfficePostalCode : ""}</p>`;
                parts.push(address);

                if (office.SocialMediaWebsiteUrlOrId && office.SocialMediaWebsiteUrlOrId !== "null") {
                    let url = office.SocialMediaWebsiteUrlOrId;
                    if (!/^https?:\/\//i.test(url)) {
                        url = `http://${url}`;
                    }
                    const displayUrl = url.replace(/^https?:\/\//i, '');
                    parts.push(`<p><a href="${url}" target="_blank">${displayUrl}</a></p>`);
                }

                const card = `
                    <div class="bridge-directory-card">
                        <h4>${office.OfficeName !== "null" ? office.OfficeName : ""}</h4>
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
